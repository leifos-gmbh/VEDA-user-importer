<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use GuzzleHttp\Client as GClient;
use OpenAPI\Client\ApiException;
use OpenApi\Client\Configuration;
use OpenApi\Client\Api\AusbildungszugabschnitteApi;
use OpenApi\Client\Api\AusbildungsgngeApi;
use OpenApi\Client\Api\AusbildungszgeApi;
use OpenApi\Client\Api\OrganisationenApi;
use OpenApi\Client\Api\ELearningPlattformenApi;
use OpenApi\Client\Model\FehlermeldungApiDto;
use OpenAPI\Client\Model\KursbearbeitungDto;
use OpenApi\Client\Model\MeldeLernerfolgApiDto;
use OpenApi\Client\Model\PraktikumsberichtEingegangenApiDto;
use OpenApi\Client\Model\PraktikumsberichtKorrigiertApiDto;
use OpenApi\Client\Model\Teilnehmerkurszuordnung;
use OpenApi\Client\Model\Lernbegleiterkurszuordnung;
use OpenApi\Client\Model\Dozentenkurszuordnung;
use OpenApi\Client\Model\AusbildungszugDozent;
use OpenApi\Client\Model\AusbildungszugLernbegleiter;
use OpenApi\Client\Model\AufsichtspersonKurszugriff;
use OpenApi\Client\Model\AusbildungszugTeilnehmer;
use OpenApi\Client\Model\Elearningkurs;
use OpenApi\Client\Model\Ausbildungszug;
use OpenApi\Client\Model\Organisation;
use OpenApi\Client\Model\Ausbildungsgang;
use OpenApi\Client\Model\TeilnehmerELearningPlattform;

/**
 * Connector for all rest api calls.
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilVedaConnector
{
    protected ilLogger $veda_logger;
    protected Configuration $config;
    protected ilVedaMailSegmentBuilderFactoryInterface $mail_segment_builder_factory;
    protected string $plattform_id;

    public function __construct(
        ilLogger $veda_logger,
        ilVedaConnectorSettings $veda_settings,
        ilVedaMailSegmentBuilderFactoryInterface $mail_segment_builder_factory
    ) {
        $this->veda_logger = $veda_logger;
        $this->mail_segment_builder_factory = $mail_segment_builder_factory;
        $this->config = new Configuration();
        $this->config->setApiKey(
            ilVedaConnectorSettings::HEADER_TOKEN,
            $veda_settings->getAuthenticationToken()
        );
        $this->config->setHost($veda_settings->getRestUrl());
        $this->config->setAccessToken($veda_settings->getAuthenticationToken());
        $this->plattform_id = $veda_settings->getPlatformId();
    }

    public function getTrainingCourseApi() : ilVedaTrainingCourseApi
    {
        return new ilVedaTrainingCourseApi(
            $this->config,
            $this->veda_logger,
            $this->mail_segment_builder_factory
        );
    }

    public function getEducationTrainApi() : ilVedaEducationTrainApiInterface
    {
        return new ilVedaEducationTrainApi(
            $this->config,
            $this->veda_logger,
            $this->mail_segment_builder_factory
        );
    }

    public function getEducationTrainSegmentApi() : ilVedaEducationTrainSegmentApiInterface
    {
        return new ilVedaEducationTrainSegmentApi(
            $this->config,
            $this->veda_logger,
            $this->mail_segment_builder_factory
        );
    }

    public function getElearningPlattformApi() : ilVedaELearningPlattformApiInterface
    {
        return new ilVedaElearningPlattformApi(
            $this->plattform_id,
            $this->config,
            $this->veda_logger,
            $this->mail_segment_builder_factory
        );
    }

    public function getOrganisationApi() : ilVedaOrganisationApiInterface
    {
        return new ilVedaOrganisationApi(
            $this->config,
            $this->veda_logger,
            $this->mail_segment_builder_factory
        );
    }
}
