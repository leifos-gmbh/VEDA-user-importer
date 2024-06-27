<?php

use OpenAPI\Client\Api\AusbildungszugabschnitteApi;
use OpenAPI\Client\ApiException;
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

    protected function handleException(string $api_call_name, Exception $e): void
    {
        $exception_handler = new ilVedaApiExceptionHandler(
            $api_call_name,
            $this->api_training_course_train_segment->getConfig()->getAccessToken(),
            $e
        );
        $exception_handler->writeToLog($this->veda_logger);
        $exception_handler->storeAsMailSegment($this->mail_segment_builder_factory);
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
            $this->veda_logger->dump($info, ilLogLevel::DEBUG);
            $this->api_training_course_train_segment->meldePraktikumsberichtKorrigiertUsingPUT(
                $segment_oid,
                $participant_oid,
                $info
            );
            return true;
        } catch (Exception $e) {
            $this->handleException('meldePraktikumsberichtKorrigiertUsingPUT', $e);
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
            $this->veda_logger->dump($info, ilLogLevel::DEBUG);
            $this->api_training_course_train_segment->meldePraktikumsberichtEingegangenUsingPUT(
                $segment_oid,
                $participant_oid,
                $info
            );
            return true;
        } catch (Exception $e) {
            $this->handleException('meldePraktikumsberichtEingegangenUsingPUT', $e);
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
            $this->veda_logger->dump($info, ilLogLevel::DEBUG);
            $this->api_training_course_train_segment->meldeLernerfolgUsingPUT(
                $segment_oid,
                $participant_oid,
                $info
            );
            return true;
        } catch (Exception $e) {
            $this->handleException('MeldeLernerfolgApiDto', $e);
            return false;
        }
    }
}
