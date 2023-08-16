<?php

use OpenAPI\Client\Api\AusbildungsgngeApi;
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

    public function getCourse(string $training_course_id) : \OpenAPI\Client\Model\Ausbildungsgang
    {
        try {
            return $this->api_training_course->getAusbildungsgangUsingGET($training_course_id);
        } catch (Exception $e) {
            $this->handleApiExceptions('getAusbildungsgangUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    protected function handleApiExceptions(
        string $api_call_name,
        Exception $e
    ) : void {
        $this->veda_logger->warning(
            ilVedaConnectorSettings::HEADER_TOKEN
            . ': '
            . $this->api_training_course->getConfig()->getAccessToken()
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
}
