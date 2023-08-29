<?php

use OpenAPI\Client\Api\AusbildungszugabschnitteApi;
use OpenAPI\Client\Configuration;
use GuzzleHttp\Client as GClient;
use OpenAPI\Client\Model\MeldeLernerfolgApiDto;
use OpenAPI\Client\Model\PraktikumsberichtEingegangenApiDto;
use OpenAPI\Client\Model\PraktikumsberichtKorrigiertApiDto;

class ilVedaEducationTrainSegmentApi implements ilVedaEducationTrainSegmentApiInterface
{
    protected AusbildungszugabschnitteApi $api_training_course_train_segment;
    protected ilLogger $veda_logger;
    protected ilVedaMailSegmentBuilderFactoryInterface $mail_segment_builder_factory;
    protected string $plattform_id;

    public function __construct(
        Configuration $config,
        ilLogger $veda_logger,
        ilVedaMailSegmentBuilderFactoryInterface $mail_segment_builder_factory
    ) {
        $this->api_training_course_train_segment = new AusbildungszugabschnitteApi(
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
            . $this->api_training_course_train_segment->getConfig()->getAccessToken()
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

    public function sendExerciseSubmissionConfirmed(
        string $segment_oid,
        string $participant_oid,
        DateTime $confirmed = null
    ) : bool {
        try {
            $info = new PraktikumsberichtKorrigiertApiDto();
            if ($confirmed) {
                $info->setPraktikumsberichtKorrigiert(true);
                $info->setPraktikumsberichtKorrigiertAm($confirmed);
            } else {
                $info->setPraktikumsberichtKorrigiert(false);
            }
            $this->veda_logger->dump($info);
            $this->api_training_course_train_segment->meldePraktikumsberichtKorrigiertUsingPUT(
                $segment_oid,
                $participant_oid,
                $info
            );
            return true;
        } catch (Exception $e) {
            $this->handleApiExceptions('meldePraktikumsberichtKorrigiertUsingPUT', $e);
            return false;
        }
    }

    public function sendExerciseSubmissionDate(
        string $segment_oid,
        string $participant_oid,
        ?DateTime $subdate = null
    ) : bool {
        try {
            $info = new PraktikumsberichtEingegangenApiDto();
            if ($subdate) {
                $info->setPraktikumsberichtEingegangen(true);
                $info->setPraktikumsberichtEingegangenAm($subdate);
            } else {
                $info->setPraktikumsberichtEingegangen(false);
            }
            $this->veda_logger->dump($info);
            $this->api_training_course_train_segment->meldePraktikumsberichtEingegangenUsingPUT(
                $segment_oid,
                $participant_oid,
                $info
            );
            return true;
        } catch (Exception $e) {
            $this->handleApiExceptions('meldePraktikumsberichtEingegangenUsingPUT', $e);
            return false;
        }
    }

    public function sendExerciseSuccess(
        string $segment_oid,
        string $participant_oid,
        DateTime $dt
    ) : bool {
        try {
            $info = new MeldeLernerfolgApiDto();
            $info->setLernerfolg(true);
            $info->setLernerfolgGemeldetAm($dt);
            $this->veda_logger->dump($info);
            $this->api_training_course_train_segment->meldeLernerfolgUsingPUT(
                $segment_oid,
                $participant_oid,
                $info
            );
            return true;
        } catch (Exception $e) {
            $this->handleApiExceptions('MeldeLernerfolgApiDto', $e);
            return false;
        }
    }
}
