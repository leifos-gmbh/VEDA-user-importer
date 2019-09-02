<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use GuzzleHttp\Client;
use Swagger\Client\Api\ELearningApi;
use Swagger\Client\ApiException;
use Swagger\Client\Configuration;
use Swagger\Client\Model\TeilnehmerELearningPlattform;


/**
 * Connector for all rest api calls.
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilVedaConnector
{
	/**
	 * @var null
	 */
	private static $instance = null;

	/**
	 * @var \ilLogger
	 */
	private $logger = null;

	/**
	 * @var \ilVedaConnectorSettings|null
	 */
	private $settings = null;

	/**
	 * @var bool
	 */
	private $initialized = false;

	/**
	 * @var ELearningApi
	 */
	private $api_elearning = null;



	/**
	 * ilOpenTextConnector constructor.
	 */
	private function __construct()
	{
		global $DIC;

		$this->logger = $DIC->logger()->vedaimp();
		$this->settings = \ilVedaConnectorSettings::getInstance();
	}

	/**
	 * @return \ilVedaConnector|null
	 */
	public static function getInstance()
	{
		if(!self::$instance instanceof \ilVedaConnector) {
			self::$instance  = new self();
		}
		return self::$instance;
	}


	/**
	 * @return \Swagger\Client\Model\TeilnehmerELearningPlattform[]
	 * @throws \ilVedaConnectionException
	 */
	public function getParticipants()
	{
		if(!$this->api_elearning instanceof ELearningApi)
		{
			list(
				$client,
				$config,
				$header
				) = $this->initApiParameters();
			$this->api_elearning = new ELearningApi(
				$client,
				$config,
				$header
			);
		}

		try {
			$response = $this->api_elearning->getTeilnehmerELearningPlattformUsingGET($this->settings->getPlatformId());
			$this->logger->dump($response, \ilLogLevel::DEBUG);
			return $response;
		}
		catch(ApiException $e) {

			$this->logger->warning(\ilVedaConnectorSettings::HEADER_TOKEN . ': ' . $this->settings->getAuthenticationToken());
			$this->logger->warning('GetTeilnehmer failed with message: ' . $e->getMessage());
			$this->logger->dump($e->getResponseHeaders(), \ilLogLevel::WARNING);
			$this->logger->dump($e->getTraceAsString(), \ilLogLevel::WARNING);
			$this->logger->warning($e->getResponseBody());

			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
		catch(Exception $e) {
			$this->logger->warning('GetTeilnehmer failed with message: ' . $e->getMessage());
			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
	}

	/**
	 * @param string $participant_id
	 * @throws \ilVedaConnectionException
	 */
	public function sendCreationMessage(string $participant_id)
	{
		if(!$this->api_elearning instanceof ELearningApi)
		{
			list(
				$client,
				$config,
				$header
				) = $this->initApiParameters();
			$this->api_elearning = new ELearningApi(
				$client,
				$config,
				$header
			);
		}
		try {
			$response = $this->api_elearning->meldeElearningaccountAlsExternExistierendUsingPOST(
				$this->settings->getPlatformId(),
				$participant_id
			);
			return true;
		}
		catch(ApiException $e) {

			$this->logger->warning('meldeTeilnehmerExistierend failed with message: ' . $e->getMessage());
			$this->logger->dump($e->getResponseHeaders(), \ilLogLevel::WARNING);
			$this->logger->dump($e->getTraceAsString(), \ilLogLevel::WARNING);
			$this->logger->warning($e->getResponseBody());

			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
		catch(Exception $e) {
			$this->logger->warning('meldeTEilnehmerExistierend failed with message: ' . $e->getMessage());
			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
	}

	/**
	 *
	 */
	protected function initApiParameters()
	{
		$client = new Client();
		$config = new Configuration();
		$config->setApiKey(
			\ilVedaConnectorSettings::HEADER_TOKEN,
			$this->settings->getAuthenticationToken()
		);
		$config->setAccessToken($this->settings->getAuthenticationToken());
		$config->setDebug(true);
		$config->setDebugFile('/tmp/swagger.txt');

		$header = new \ilVedaConnectorHeaderSelector($config);

		return [
			$client,
			$config,
			$header
		];
	}
}