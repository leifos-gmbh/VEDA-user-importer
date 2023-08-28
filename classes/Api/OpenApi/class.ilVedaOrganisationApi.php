<?php

use OpenAPI\Client\Api\OrganisationenApi;
use OpenAPI\Client\Configuration;
use OpenAPI\Client\Model\Organisation;
use GuzzleHttp\Client as GClient;

class ilVedaOrganisationApi implements ilVedaOrganisationApiInterface
{
    protected OrganisationenApi $api_organisation;
    protected ilLogger $veda_logger;
    protected ilVedaMailSegmentBuilderFactoryInterface $mail_segment_builder_factory;

    public function __construct(
        Configuration $config,
        ilLogger $veda_logger,
        ilVedaMailSegmentBuilderFactoryInterface $mail_segment_builder_factory
    ) {
        $this->api_organisation = new OrganisationenApi(
            new GClient(),
            $config,
            new ilVedaConnectorHeaderSelector($config)
        );
        $this->veda_logger = $veda_logger;
        $this->mail_segment_builder_factory = $mail_segment_builder_factory;
    }

    public function getOrganisation(string $orgr_oid) : Organisation
    {
        try {
            $response = $this->api_organisation->getOrganisationUsingGET($orgr_oid);
            $this->veda_logger->dump($response);
            return $response;
        } catch (Exception $e) {
            $this->handleApiExceptions('getOrganisationUsingGET', $e);
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
            . $this->api_organisation->getConfig()->getAccessToken()
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
