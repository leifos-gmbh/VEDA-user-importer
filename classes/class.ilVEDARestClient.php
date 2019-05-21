<?php
/**
 * VEDA REST API client adapter
 * @author Jesus Lopez <lopez@leifos.com>
 */
class ilVEDARestClient
{
	protected $session_id;
	protected $last_execution;
	protected $swagger_configuration;

	function __construct()
	{
		$this->setSwaggerConfiguration();
	}

	protected function setSwaggerConfiguration()
	{

		$settings = ilVEDAUserImporterSettings::getInstance();
		ilVEDAUserImporterLogger::getLogger()->write("Go to create configuration object");
		$this->swagger_configuration = new Swagger\Client\Configuration();
		$this->swagger_configuration->setHost($settings->getRestUrl());
		$this->swagger_configuration->setUsername($settings->getRestUser());
		$this->swagger_configuration->setPassword($settings->getRestPassword());
	}

}