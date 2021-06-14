<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use GuzzleHttp\Client;
use Swagger\Client\Api\AusbildungsgngeApi;
use Swagger\Client\Api\AusbildungszgeApi;
use Swagger\Client\Api\AusbildungszugabschnitteApi;
use Swagger\Client\ApiException;
use Swagger\Client\Configuration;
use Swagger\Client\Api\ELearningPlattformenApi;
use Swagger\Client\Api\OrganisationenApi;
use Swagger\Client\Model\Ausbildungsgang;
use Swagger\Client\Model\Ausbildungszug;
use Swagger\Client\Model\AusbildungszugTeilnehmer;
use Swagger\Client\Model\MeldeLernerfolgApiDto;
use Swagger\Client\Model\PraktikumsberichtEingegangenApiDto;
use Swagger\Client\Model\PraktikumsberichtKorrigiertApiDto;

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
	 * @var null | \Swagger\Client\Api\ELearningPlattformenApi
	 */
	private $api_elearning = null;


	/**
	 * @var null | AusbildungsgngeApi
	 */
	private $api_training_course = null;

	/**
	 * @var null | \Swagger\Client\Api\AusbildungszgeApi
	 */
	private $api_training_course_train = null;

	/**
	 * @var null | \Swagger\Client\Api\AusbildungszugabschnitteApi
	 */
	private $api_training_course_train_segment = null;



	/**
	 * @var null | OrganisationenApi
	 */
	private $api_organisation = null;




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
	 * @param string $segment_id
	 * @param string $participant_id
	 * @param \DateTime|null $confirmed
	 * @throws \ilVedaConnectionException
	 */
	public function sendExerciseSubmissionConfirmed(string $segment_id, string $participant_id, DateTime $confirmed = null)
	{
		if(!$this->api_training_course_train_segment instanceof AusbildungszugabschnitteApi)
		{
			list(
				$client,
				$config,
				$header
				) = $this->initApiParameters();
			$this->api_training_course_train_segment = new AusbildungszugabschnitteApi(
				$client,
				$config,
				$header
			);
		}

		try {

		    $info = new PraktikumsberichtKorrigiertApiDto();
		    if($confirmed) {
		        $info->setPraktikumsberichtKorrigiert(true);
		        $info->setPraktikumsberichtKorrigiertAm($confirmed);
            }
		    else {
		        $info->setPraktikumsberichtKorrigiert(false);
            }
			$this->logger->dump($info);

		    $response = $this->api_training_course_train_segment->meldePraktikumsberichtKorrigiertUsingPUT(
		        $segment_id,
                $participant_id,
                $info
            );
			return $response;
		}
		catch(ApiException $e) {
			$this->logger->error('meldePraktikumsberichtKorrigiertUsingPUT failed with message: ' . $e->getMessage());
			$this->logger->dump($e->getResponseHeaders(), \ilLogLevel::WARNING);
			$this->logger->dump($e->getTraceAsString(), \ilLogLevel::WARNING);
			$this->logger->warning($e->getResponseBody());

			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
		catch(Exception $e) {
			$this->logger->warning('meldePraktikumsberichtKorrigiertUsingPUT failed with message: ' . $e->getMessage());
			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
	}

	/**
	 * @param string $segment_id
	 * @param string $participant_id
	 * @param \DateTime|null $subdate
	 * @throws \ilVedaConnectionException
	 */
	public function sendExerciseSubmissionDate(string $segment_id, string $participant_id, DateTime $subdate = null)
	{
		if(!$this->api_training_course_train_segment instanceof AusbildungszugabschnitteApi)
		{
			list(
				$client,
				$config,
				$header
				) = $this->initApiParameters();
			$this->api_training_course_train_segment = new AusbildungszugabschnitteApi(
				$client,
				$config,
				$header
			);
		}

		try {
			$info = new PraktikumsberichtEingegangenApiDto();
			if($subdate) {
				$info->setPraktikumsberichtEingegangen(true);
				$info->setPraktikumsberichtEingegangenAm($subdate);
			}
			else {
				$info->setPraktikumsberichtEingegangen(false);
			}

			$this->logger->dump($info);

			$response =  $this->api_training_course_train_segment->meldePraktikumsberichtEingegangenUsingPUT(
				$segment_id,
				$participant_id,
				$info
			);
			return $response;
		}
		catch(ApiException $e) {
			$this->logger->error('meldePraktikumsberichtEingegangenUsingPUT failed with message: ' . $e->getMessage());
			$this->logger->dump($e->getResponseHeaders(), \ilLogLevel::WARNING);
			$this->logger->dump($e->getTraceAsString(), \ilLogLevel::WARNING);
			$this->logger->warning($e->getResponseBody());

			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
		catch(Exception $e) {
			$this->logger->warning('meldePraktikumsberichtEingegangenUsingPUT failed with message: ' . $e->getMessage());
			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
	}

	/**
	 * @param string $segment_id
	 * @param string $participant_id
	 * @throws \ilVedaConnectionException
	 */
	public function sendExerciseSuccess(string $segment_id, string $participant_id, \DateTime $dt)
	{
		if(!$this->api_training_course_train_segment instanceof AusbildungszugabschnitteApi)
		{
			list(
				$client,
				$config,
				$header
				) = $this->initApiParameters();
			$this->api_training_course_train_segment = new AusbildungszugabschnitteApi(
				$client,
				$config,
				$header
			);
		}

		try {
			$info = new MeldeLernerfolgApiDto();
			$info->setLernerfolg(true);
			$info->setLernerfolgGemeldetAm($dt);

			$this->logger->dump($info);

			$response = $this->api_training_course_train_segment->meldeLernerfolgUsingPUT(
				$segment_id,
				$participant_id,
				$info
			);
		}
		catch(ApiException $e) {
			$this->logger->error('meldeLernerfolgUsingPUT failed with message: ' . $e->getMessage());
			$this->logger->dump($e->getResponseHeaders(), \ilLogLevel::WARNING);
			$this->logger->dump($e->getTraceAsString(), \ilLogLevel::WARNING);
			$this->logger->warning($e->getResponseBody());

			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
		catch(Exception $e) {
			$this->logger->warning('meldeLernerfolgUsingPUT failed with message: ' . $e->getMessage());
			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}

	}


	/**
	 * @param string|null $oid
	 * @return \Swagger\Client\Model\AusbildungszugDozent[]
	 * @throws \ilVedaConnectionException
	 */
	public function readTrainingCourseTrainTutors(?string $oid)
	{
		if(!$this->api_training_course_train instanceof AusbildungszgeApi)
		{
			list(
				$client,
				$config,
				$header
				) = $this->initApiParameters();
			$this->api_training_course_train = new AusbildungszgeApi(
				$client,
				$config,
				$header
			);
		}

		try {
			$response =  $this->api_training_course_train->getBeteiligteDozentenVonAusbildungszugUsingGET($oid);
			return $response;
		}
		catch(ApiException $e) {
			$this->logger->error('getBeteiligteDozentenVonAusbildungszugUsingGET failed with message: ' . $e->getMessage());
			$this->logger->dump($e->getResponseHeaders(), \ilLogLevel::WARNING);
			$this->logger->dump($e->getTraceAsString(), \ilLogLevel::WARNING);
			$this->logger->warning($e->getResponseBody());
			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
		catch(Exception $e) {
			$this->logger->warning('getBeteiligteDozentenVonAusbildungszugUsingGET failed with message: ' . $e->getMessage());
			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
	}

	/**
	 * @param string|null $oid
	 * @return \Swagger\Client\Model\AusbildungszugLernbegleiter[]
	 * @throws \ilVedaConnectionException
	 */
	public function readTrainingCourseTrainCompanions(?string $oid)
	{
		if(!$this->api_training_course_train instanceof AusbildungszgeApi)
		{
			list(
				$client,
				$config,
				$header
				) = $this->initApiParameters();
			$this->api_training_course_train = new AusbildungszgeApi(
				$client,
				$config,
				$header
			);
		}

		try {
			$response =  $this->api_training_course_train->getLernbegleiterVonAusbildungszugUsingGET($oid);
			return $response;
		}
		catch(ApiException $e) {
			$this->logger->error('getLernbegleiterVonAusbildungszugUsingGET failed with message: ' . $e->getMessage());
			$this->logger->dump($e->getResponseHeaders(), \ilLogLevel::WARNING);
			$this->logger->dump($e->getTraceAsString(), \ilLogLevel::WARNING);
			$this->logger->warning($e->getResponseBody());

			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
		catch(Exception $e) {
			$this->logger->warning('getLernbegleiterVonAusbildungszugUsingGET failed with message: ' . $e->getMessage());
			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
	}

	/**
	 * @param string|null $oid
	 * @return AusbildungszugTeilnehmer[]
	 * @throws \ilVedaConnectionException
	 */
	public function readTrainingCourseTrainMembers(?string $oid)
	{
		if(!$this->api_training_course_train instanceof AusbildungszgeApi)
		{
			list(
				$client,
				$config,
				$header
				) = $this->initApiParameters();
			$this->api_training_course_train = new AusbildungszgeApi(
				$client,
				$config,
				$header
			);
		}

		try {
			$response =  $this->api_training_course_train->getTeilnehmerVonAusbildungszugUsingGET($oid);
			return $response;
		}
		catch(ApiException $e) {
			$this->logger->error('getTeilnehmerVonAusbildungszugUsingGET failed with message: ' . $e->getMessage());
			$this->logger->dump($e->getResponseHeaders(), \ilLogLevel::WARNING);
			$this->logger->dump($e->getTraceAsString(), \ilLogLevel::WARNING);
			$this->logger->warning($e->getResponseBody());

			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
		catch(Exception $e) {
			$this->logger->warning('getTeilnehmerVonAusbildungszugUsingGET failed with message: ' . $e->getMessage());
			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
	}

	/**
	 * @param string $oid
	 * @throws \ilVedaConnectionException
	 */
	public function sendTrainingCourseTrainCreated(string $oid)
	{
		if(!$this->api_training_course_train instanceof AusbildungszgeApi)
		{
			list(
				$client,
				$config,
				$header
				) = $this->initApiParameters();
			$this->api_training_course_train = new AusbildungszgeApi(
				$client,
				$config,
				$header
			);
		}

		try {
			$response =  $this->api_training_course_train->meldeAusbildungszugAlsExternExistierendUsingPOST($oid);
		}
		catch(ApiException $e) {
			$this->logger->error('meldeAusbildungszugAlsExternExistierendUsingPOST failed with message: ' . $e->getMessage());
			$this->logger->dump($e->getResponseHeaders(), \ilLogLevel::WARNING);
			$this->logger->dump($e->getTraceAsString(), \ilLogLevel::WARNING);
			$this->logger->warning($e->getResponseBody());

			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
		catch(Exception $e) {
			$this->logger->warning('meldeAusbildungszugAlsExternExistierendUsingPOST failed with message: ' . $e->getMessage());
			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
	}

	/**
	 * @param string $training_course_id
	 * @return Ausbildungszug[]
	 * @throws \ilVedaConnectionException
	 */
	public function getTrainingCourseTrains(string $training_course_id) : array
	{
		if(!$this->api_elearning instanceof ELearningPlattformenApi)
		{
			list(
				$client,
				$config,
				$header
				) = $this->initApiParameters();
			$this->api_elearning = new ELearningPlattformenApi(
				$client,
				$config,
				$header
			);
		}

		try {
			$response = $this->api_elearning->getFreigegebeneAusbildungszuegeFuerPlattformUndAusbildungsgangUsingGET(
				$this->settings->getPlatformId(),
				$training_course_id
			);
			return $response;
		}
		catch(ApiException $e) {

			$this->logger->warning(\ilVedaConnectorSettings::HEADER_TOKEN . ': ' . $this->settings->getAuthenticationToken());
			$this->logger->warning('getFreigegebeneAusbildungszuegeFuerPlattformUndAusbildungsgangUsingGET failed with message: ' . $e->getMessage());
			$this->logger->dump($e->getResponseHeaders(), \ilLogLevel::WARNING);
			$this->logger->dump($e->getTraceAsString(), \ilLogLevel::WARNING);
			$this->logger->warning($e->getResponseBody());

			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
		catch(Exception $e) {
			$this->logger->warning('getFreigegebeneAusbildungszuegeFuerPlattformUndAusbildungsgangUsingGET failed with message: ' . $e->getMessage());
			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}


	}

	/**
	 * @param string $orgr_oid
	 * @return \Swagger\Client\Model\Organisation
	 * @throws \ilVedaConnectionException
	 */
	public function getOrganisation(string $orgr_oid)
	{
		if(!$this->api_organisation instanceof OrganisationenApi) {
			list(
				$client,
				$config,
				$header
				) = $this->initApiParameters();
			$this->api_organisation = new OrganisationenApi(
				$client,
				$config,
				$header
			);
		}

		try {
			$response = $this->api_organisation->getOrganisationUsingGET($orgr_oid);
			$this->logger->dump($response);
			return $response;
		}
		catch(ApiException $e) {
			$this->logger->warning(\ilVedaConnectorSettings::HEADER_TOKEN . ': ' . $this->settings->getAuthenticationToken());
			$this->logger->warning('getOrganisationAPI failed with message: ' . $e->getMessage());
			$this->logger->dump($e->getResponseHeaders(), \ilLogLevel::WARNING);
			$this->logger->dump($e->getTraceAsString(), \ilLogLevel::WARNING);
			$this->logger->warning($e->getResponseBody());

			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
		catch(Exception $e) {
			$this->logger->warning('GetOrganisationAPI failed with message: ' . $e->getMessage());
			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
	}


	/**
	 * Get training courses for ausbildungsgang
	 * @param string $training_course_id
	 * @return \Swagger\Client\Model\Ausbildungsgang
	 * @throws \ilVedaConnectionException
	 */
	public function getTrainingCourseSegments(string $training_course_id)
	{
		if(!$this->api_training_course instanceof AusbildungsgngeApi) {
			list(
				$client,
				$config,
				$header
				) = $this->initApiParameters();
			$this->api_training_course = new AusbildungsgngeApi(
				$client,
				$config,
				$header
			);
		}

		try {
			$response = $this->api_training_course->getAusbildungsgangUsingGET($training_course_id);
			return $response;
		}
		catch(ApiException $e) {
			$this->logger->warning(\ilVedaConnectorSettings::HEADER_TOKEN . ': ' . $this->settings->getAuthenticationToken());
			$this->logger->warning('GetAusbildungsgang failed with message: ' . $e->getMessage());
			$this->logger->dump($e->getResponseHeaders(), \ilLogLevel::WARNING);
			$this->logger->dump($e->getTraceAsString(), \ilLogLevel::WARNING);
			$this->logger->warning($e->getResponseBody());

			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}
		catch(Exception $e) {
			$this->logger->warning('GetAusbildungsgang failed with message: ' . $e->getMessage());
			throw new \ilVedaConnectionException($e->getMessage(), \ilVedaConnectionException::ERR_API);
		}

	}


	/**
	 * @return \Swagger\Client\Model\TeilnehmerELearningPlattform[]
	 * @throws \ilVedaConnectionException
	 */
	public function getParticipants()
	{
		if(!$this->api_elearning instanceof ELearningPlattformenApi)
		{
			list(
				$client,
				$config,
				$header
				) = $this->initApiParameters();
			$this->api_elearning = new ELearningPlattformenApi(
				$client,
				$config,
				$header
			);
		}

		try {
			$response = $this->api_elearning->getTeilnehmerELearningPlattformUsingGET($this->settings->getPlatformId());
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
		if(!$this->api_elearning instanceof ELearningPlattformenApi)
		{
			list(
				$client,
				$config,
				$header
				) = $this->initApiParameters();
			$this->api_elearning = new ELearningPlattformenApi(
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
	 * send password notification, if required.
	 */
	public function handlePasswordChange(int $usr_id)
	{
		$import_id = \ilObjUser::_lookupImportId($usr_id);

		if(!$import_id) {
			$this->logger->debug('No import id for user ' . $usr_id);
			return false;
		}
		$user_status = new \ilVedaUserStatus($import_id);

		if(
			$user_status->isImportFailure() ||
			$user_status->getPasswordStatus() != \ilVedaUserStatus::STATUS_PENDING
		)
		{
			$this->logger->debug('No password notification required.');
		}

		if(!$this->api_elearning instanceof ELearningPlattformenApi)
		{
			list(
				$client,
				$config,
				$header
				) = $this->initApiParameters();
			$this->api_elearning = new ELearningPlattformenApi(
				$client,
				$config,
				$header
			);
		}
		try {
			$response = $this->api_elearning->meldeErstmaligErfolgreichEingeloggtUsingPOST(
				$this->settings->getPlatformId(),
				$user_status->getOid()
			);
			$user_status->setPasswordStatus(\ilVedaUserStatus::STATUS_SYNCHRONIZED);
			$user_status->save();
			$this->logger->info('Password notification sent.');
			return true;
		}
		catch(ApiException $e) {

			$this->logger->warning('meldeErstmaligErfolgreichEingeloggt failed with message: ' . $e->getMessage());
			$this->logger->dump($e->getResponseHeaders(), \ilLogLevel::WARNING);
			$this->logger->dump($e->getTraceAsString(), \ilLogLevel::WARNING);
			$this->logger->warning($e->getResponseBody());
		}
		catch(Exception $e) {
			$this->logger->warning('meldeErstmaligErfolgreichEingeloggt failed with message: ' . $e->getMessage());
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
		$config->setHost($this->settings->getRestUrl());
		$config->setAccessToken($this->settings->getAuthenticationToken());
		//$config->setDebug(true);

		$header = new \ilVedaConnectorHeaderSelector($config);

		return [
			$client,
			$config,
			$header
		];
	}
}