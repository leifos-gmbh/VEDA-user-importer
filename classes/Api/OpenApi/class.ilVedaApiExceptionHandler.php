<?php /** @noinspection ALL */

/** @noinspection SpellCheckingInspection */

use OpenAPI\Client\ApiException;

class ilVedaApiExceptionHandler
{
    protected string $api_call_name;
    protected string $access_token;
    protected Exception $e;
    protected ilVedaMailSegmentBuilderFactoryInterface $mail_segment_builder_factory;

    public function __construct(
        string $api_call_name,
        string $access_token,
        Exception $e
    ) {
        $this->api_call_name = $api_call_name;
        $this->access_token = $access_token;
        $this->e = $e;
    }

    public function writeToLog(ilLogger $veda_logger): void
    {
        $veda_logger->warning(ilVedaConnectorSettings::HEADER_TOKEN . ': ' . $this->access_token);
        $veda_logger->warning($this->api_call_name . ' failed with message: ' . $this->e->getMessage());
        if ($this->e instanceof ApiException) {
            $veda_logger->dump($this->e->getResponseHeaders(), ilLogLevel::WARNING);
            $veda_logger->dump($this->e->getTraceAsString(), ilLogLevel::WARNING);
            $veda_logger->warning($this->e->getResponseBody());
        }
        if (!($this->e instanceof ApiException)) {
            $veda_logger->dump($this->e->getTraceAsString(), ilLogLevel::WARNING);
        }
    }

    /**
     * @throws Exception
     */
    public function storeAsMailSegment(ilVedaMailSegmentBuilderFactory $mail_segment_builder_factory): void
    {
        $mail_segment_builder_factory->buildSegment()
            ->withType(ilVedaMailSegmentType::ERROR)
            ->withMessage('Verbindungsfehler beim Aufuf von: ' . $this->api_call_name)
            ->store();
    }
}