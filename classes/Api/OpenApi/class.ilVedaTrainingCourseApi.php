<?php

use OpenAPI\Client\Api\AusbildungsgngeApi;
use OpenAPI\Client\ApiException;
use OpenAPI\Client\Model\Ausbildungsgang;
use OpenAPI\Client\Configuration;
use GuzzleHttp\Client as GClient;

class ilVedaTrainingCourseApi implements ilVedaTrainingCourseApiInterface
{
    protected AusbildungsgngeApi $api_training_course;
    protected ilLogger $veda_logger;
    protected ilVedaMailSegmentBuilderFactoryInterface $mail_segment_builder_factory;

    public function __construct(
        Configuration $config,
        ilLogger $veda_logger,
        ilVedaMailSegmentBuilderFactoryInterface $mail_segment_builder_factory
    ) {
        $this->api_training_course = new AusbildungsgngeApi(
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
            $this->api_training_course->getConfig()->getAccessToken(),
            $e
        );
        $exception_handler->writeToLog($this->veda_logger);
        $exception_handler->storeAsMailSegment($this->mail_segment_builder_factory);
    }

    public function getCourse(string $training_course_id) : ?Ausbildungsgang
    {
        try {
            return $this->api_training_course->getAusbildungsgangUsingGET($training_course_id);
        } catch (Exception $e) {
            $this->handleException('getAusbildungsgangUsingGET', $e);
        }
        return null;
    }
}
