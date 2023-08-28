<?php

use GuzzleHttp\Client as GClient;
use OpenAPI\Client\Api\ELearningPlattformenApi;
use OpenAPI\Client\ApiException;
use OpenAPI\Client\Configuration;
use OpenAPI\Client\Model\FehlermeldungApiDto;

class ilVedaElearningPlattformApi implements ilVedaELearningPlattformApiInterface
{
    protected ELearningPlattformenApi $api_elearning;
    protected ilLogger $veda_logger;
    protected ilVedaMailSegmentBuilderFactoryInterface $mail_segment_builder_factory;
    protected string $plattform_id;

    public function __construct(
        string $plattform_id,
        Configuration $config,
        ilLogger $veda_logger,
        ilVedaMailSegmentBuilderFactoryInterface $mail_segment_builder_factory
    ) {
        $this->api_elearning = new ELearningPlattformenApi(
            new GClient(),
            $config,
            new ilVedaConnectorHeaderSelector($config)
        );
        $this->veda_logger = $veda_logger;
        $this->mail_segment_builder_factory = $mail_segment_builder_factory;
        $this->plattform_id = $plattform_id;
    }

    protected function handleApiExceptions(
        string $api_call_name,
        Exception $e
    ) : void {
        $this->veda_logger->warning(
            ilVedaConnectorSettings::HEADER_TOKEN
            . ': '
            . $this->api_elearning->getConfig()->getAccessToken()
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

    public function requestCourseMembers(string $crs_oid) : ilVedaCourseMemberCollectionInterface
    {
        try {
            $result = $this->api_elearning->getVonTeilnehmernDieAktivenKurszuordnungenUsingGET(
                $this->plattform_id,
                $crs_oid
            );
            $this->veda_logger->debug('Received course members of course with oid: ' . $crs_oid);
            $this->veda_logger->dump($result);
            return new ilVedaCourseMemberCollection($result);
        } catch (Exception $e) {
            $this->handleApiExceptions('getVonTeilnehmernDieAktivenKurszuordnungenUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function requestCourseSupervisors(string $crs_oid) : ilVedaCourseSupervisorCollectionInterface
    {
        try {
            $result = $this->api_elearning->getVonLernbegleiternDieAktivenKurszuordnungenUsingGET(
                $this->plattform_id,
                $crs_oid
            );
            $this->veda_logger->debug('Received course supervisors of course with oid: ' . $crs_oid);
            $this->veda_logger->dump($result);
            return new ilVedaCourseSupervisorCollection($result);
        } catch (Exception $e) {
            $this->handleApiExceptions('getVonLernbegleiternDieAktivenKurszuordnungenUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function requestCourseTutors(string $crs_oid) : ilVedaCourseTutorsCollectionInterface
    {
        try {
            $result = $this->api_elearning->getVonDozentenDieAktivenKurszuordnungenUsingGET(
                $this->plattform_id,
                $crs_oid
            );
            $this->veda_logger->debug('Received course tutors of course with oid: ' . $crs_oid);
            $this->veda_logger->dump($result);
            return new ilVedaCourseTutorCollection($result);
        } catch (Exception $e) {
            $this->handleApiExceptions('getVonDozentenDieAktivenKurszuordnungenUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function requestCourses() : ilVedaELearningCourseCollectionInterface
    {
        try {
            $result = $this->api_elearning->getAktiveELearningKurseUsingGET(
                $this->plattform_id
            );
            $this->veda_logger->debug('Received e-learning courses.');
            $this->veda_logger->dump($result);
            return new ilVedaELearningCourseCollection($result);
        } catch (Exception $e) {
            $this->handleApiExceptions('getAktiveELearningKurseUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function requestTrainingCourseTrains(string $training_course_id) : ilVedaEducationTrainCourseCollectionInterface
    {
        try {
            $result = $this->api_elearning->getFreigegebeneAusbildungszuegeFuerPlattformUndAusbildungsgangUsingGET(
                $this->plattform_id,
                $training_course_id
            );
            $this->veda_logger->debug('Received education trains with training course id: ' . $training_course_id);
            $this->veda_logger->dump($result);
            return new ilVedaEducationTrainCourseCollection($result);
        } catch (Exception $e) {
            $this->handleApiExceptions('getFreigegebeneAusbildungszuegeFuerPlattformUndAusbildungsgangUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function requestParticipants() : ilVedaELearningParticipantsCollectionInterface
    {
        try {
            $result = $this->api_elearning->getTeilnehmerELearningPlattformUsingGET($this->plattform_id);
            $this->veda_logger->debug('Received all participants.');
            $this->veda_logger->dump($result);
            return new ilVedaELearningParticipantsCollection($result);
        } catch (Exception $e) {
            $this->handleApiExceptions('getTeilnehmerELearningPlattformUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function sendCourseCopyStarted(string $crs_oid) : void
    {
        try {
            $this->api_elearning->meldeElearningkursExterneAnlageAngestossenUsingPOST(
                $this->plattform_id,
                $crs_oid
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeElearningkursExterneAnlageAngestossenUsingPOST', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function sendCourseCreationFailed(string $crs_oid, string $message) : void
    {
        try {
            $error_message = new FehlermeldungApiDto();
            $error_message->setFehlermeldung($message);
            $this->api_elearning->meldeElearningkursExterneAnlageFehlgeschlagenUsingPOST(
                $this->plattform_id,
                $crs_oid,
                $error_message
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeElearningkursExterneAnlageFehlgeschlagenUsingPOST', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function sendCourseCreated(string $crs_oid) : void
    {
        try {
            $this->api_elearning->meldeElearningkursExternExistierendUsingPOST(
                $this->plattform_id,
                $crs_oid
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeElearningkursExternExistierendUsingPOST', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function sendParticipantStartedCourseWork(string $crs_oid, string $usr_oid) : void
    {
        try {
            $this->api_elearning->meldeBearbeitungsstartFuerTeilnehmerAufKursUsingPOST(
                $this->plattform_id,
                $crs_oid,
                $usr_oid
            );
        } catch (ApiException $e) {
            $this->handleApiExceptions('meldeBearbeitungsstartFuerTeilnehmerAufKursUsingPOST', $e);
            if ($e->getCode() !== 422) {
                throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
            }
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeBearbeitungsstartFuerTeilnehmerAufKursUsingPOST', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function sendAccountCreated(string $participant_id) : void
    {
        try {
            $this->api_elearning->meldeElearningaccountAlsExternExistierendUsingPOST(
                $this->plattform_id,
                $participant_id
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeElearningaccountAlsExternExistierendUsingPOST', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function sendAccountCreationFailed(string $usr_oid, string $message) : void
    {
        try {
            $error_message = new FehlermeldungApiDto();
            $error_message->setFehlermeldung($message);
            $this->api_elearning->meldeElearningaccountAnlageAlsFehlgeschlagenUsingPOST(
                $this->plattform_id,
                $usr_oid,
                $error_message
            );
            $this->veda_logger->info('Send message: ' . $error_message->getFehlermeldung());
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeElearningaccountAnlageAlsFehlgeschlagen', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function sendCoursePassed(string $crs_oid, string $usr_oid) : void
    {
        try {
            $this->api_elearning->meldeKursabschlussMitErfolgUsingPOST(
                $this->plattform_id,
                $crs_oid,
                $usr_oid,
            );
        } catch (ApiException $e) {
            $this->handleApiExceptions('meldeKursabschlussMitErfolgUsingPOST', $e);
            if ($e->getCode() !== 422) {
                throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
            }
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeKursabschlussMitErfolgUsingPOST', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function sendCourseFailed(string $crs_oid, string $usr_oid) : void
    {
        try {
            $this->api_elearning->meldeKursabschlussOhneErfolgUsingPOST(
                $this->plattform_id,
                $crs_oid,
                $usr_oid
            );
        } catch (ApiException $e) {
            $this->handleApiExceptions('meldeKursabschlussOhneErfolgUsingPOST', $e);
            if ($e->getCode() !== 422) {
                throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
            }
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeKursabschlussOhneErfolgUsingPOST', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function sendFirstLoginSuccess(string $usr_oid) : void
    {
        try {
            $this->api_elearning->meldeErstmaligErfolgreichEingeloggtUsingPOST(
                $this->plattform_id,
                $usr_oid
            );
            $this->veda_logger->info('Password notification sent.');
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeErstmaligErfolgreichEingeloggt', $e);
        }
    }
}
