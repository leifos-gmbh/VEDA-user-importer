<?php
/**
 * REST users management
 * @author Jesus Lopez <lopez@leifos.com>
 */
class ilVEDARestUser extends ilVEDARestClient
{
	protected $session_id;
	protected $rest_base_url;
	protected $last_execution;

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * VEDA ENDPOINT: /v1/elearningplattform/{id}/teilnehmer
	 *
	 * This interface retrieves all subscribers who have access to the specified eLearning platform.
	 * THIS INTERFACE IS STILL NOT IMPLEMENTED.
	 */
	public function getUsersEndPoint(): string
	{
		$settings = ilVEDAUserImporterSettings::getInstance();

		return $this->getBaseUrl().
			DIRECTORY_SEPARATOR.
			"elearningplattform".
			DIRECTORY_SEPARATOR.
			$settings->getPlatformId().
			"teilnehmer";
	}

	//call the endpoint /v1/elearningplattform/{id}/teilnehmer
	public function getVEDAUsers(): array
	{
		$endpoint = $this->getUsersEndPoint();

		$response = file_get_contents($endpoint);

		$items = json_decode($response, true);

		return $items;
	}

	//check if the user exists in ILIAS and store it if not.
	public function storeNewUsersToDB(): void
	{

	}

	// add import id to already assigned user. gets a data object.
	public function updateUserWithImportID()
	{

	}

	// validate the object data
	public function isUserObjectValid($user): bool
	{
		return false;
	}



}