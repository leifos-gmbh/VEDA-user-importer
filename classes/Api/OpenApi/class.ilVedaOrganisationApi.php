<?php

use OpenAPI\Client\Api\OrganisationenApi;
use OpenAPI\Client\ApiException;
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

    protected function handleException(string $api_call_name, Exception $e): void
    {
        $exception_handler = new ilVedaApiExceptionHandler(
            $api_call_name,
            $this->api_organisation->getConfig()->getAccessToken(),
            $e
        );
        $exception_handler->writeToLog($this->veda_logger);
        $exception_handler->storeAsMailSegment($this->mail_segment_builder_factory);
    }

    public function getOrganisation(string $orgr_oid) : ?Organisation
    {
        try {
            $response = $this->api_organisation->getOrganisationUsingGET($orgr_oid);
            $this->veda_logger->dump($response);
            return $response;
        } catch (Exception $e) {
            $this->handleException('getOrganisationUsingGET', $e);
        }
        return null;
    }
}
