<?php

use GuzzleHttp\Client as GClient;
use OpenAPI\Client\Api\AusbildungszgeApi;
use OpenAPI\Client\Configuration;
use OpenAPI\Client\Model\FehlermeldungApiDto;

class ilVedaEducationTrainApi implements ilVedaEducationTrainApiInterface
{
    /**
     * @var string
     */
    public const COURSE_CREATION_FAILED = 'Synchronisierung des Ausbildungszugs fehlgeschlagen.';

    protected AusbildungszgeApi $api_training_course_train;
    protected ilLogger $veda_logger;
    protected ilVedaMailSegmentBuilderFactoryInterface $mail_segment_builder_factory;
    protected string $plattform_id;

    public function __construct(
        Configuration $config,
        ilLogger $veda_logger,
        ilVedaMailSegmentBuilderFactoryInterface $mail_segment_builder_factory
    ) {
        $this->api_training_course_train = new AusbildungszgeApi(
            new GClient(),
            $config,
            new ilVedaConnectorHeaderSelector($config)
        );
        $this->veda_logger = $veda_logger;
        $this->mail_segment_builder_factory = $mail_segment_builder_factory;
    }

    protected function handleApiExceptions(
        string $api_call_name,
        Exception $e
    ) : void {
        $this->veda_logger->warning(
            ilVedaConnectorSettings::HEADER_TOKEN
            . ': '
            . $this->api_training_course_train->getConfig()->getAccessToken()
        );
        $this->veda_logger->warning($api_call_name . ' failed with message: ' . $e->getMessage());
        $this->veda_logger->dump($e->getResponseHeaders(), ilLogLevel::WARNING);
        $this->veda_logger->dump($e->getTraceAsString(), ilLogLevel::WARNING);
        $this->veda_logger->warning($e->getResponseBody());
        $this->mail_segment_builder_factory->buildSegment()
            ->withType(ilVedaMailSegmentType::ERROR)
            ->withMessage('Verbindungsfehler beim Aufuf von: ' . $api_call_name)
            ->store();
    }

    public function requestTutors(?string $oid) : ilVedaEducationTrainTutorCollectionInterface
    {
        try {
            return new ilVedaEducationTrainTutorCollection(
                $this->api_training_course_train->getBeteiligteDozentenVonAusbildungszugUsingGET($oid)
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('getBeteiligteDozentenVonAusbildungszugUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function requestCompanions(?string $oid) : ilVedaEducationTrainCompanionCollectionInterface
    {
        try {
            return new ilVedaEducationTrainCompanionCollection(
                $this->api_training_course_train->getLernbegleiterVonAusbildungszugUsingGET($oid)
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('getLernbegleiterVonAusbildungszugUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function requestSupervisors(?string $oid) : ilVedaEducationTrainSupervisorCollectionInterface
    {
        try {
            return new ilVedaEducationTrainSupervisorCollection(
                $this->api_training_course_train->getAufsichtspersonenVonAusbildungszugUsingGET($oid)
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('getAufsichtspersonenVonAusbildungszugUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function requestMembers(?string $oid) : ilVedaEducationTrainMemberCollectionInterface
    {
        try {
            return new ilVedaEducationTrainMemberCollection(
                $this->api_training_course_train->getTeilnehmerVonAusbildungszugUsingGET($oid)
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('getTeilnehmerVonAusbildungszugUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function sendCourseCreationFailed(string $oid) : void
    {
        try {
            $error_message = new FehlermeldungApiDto();
            $error_message->setFehlermeldung(self::COURSE_CREATION_FAILED);
            $this->api_training_course_train->meldeAusbildungszugAnlageFehlgeschlagenUsingPOST(
                $oid,
                $error_message
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeAusbildungszugAnlageFehlgeschlagenUsingPOST', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function sendCourseCreated(string $oid) : void
    {
        try {
            $this->api_training_course_train->meldeAusbildungszugAlsExternExistierendUsingPOST($oid);
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeAusbildungszugAlsExternExistierendUsingPOST', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function sendCopyStarted(string $oid) : void
    {
        try {
            $this->api_training_course_train->meldeExterneAnlageAngestossenUsingPOST($oid);
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeExterneAnlageAngestossenUsingPOST', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }
}
