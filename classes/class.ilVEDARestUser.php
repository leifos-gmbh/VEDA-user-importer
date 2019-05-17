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
	public function getAllUsersEndPoint(): string
	{
		$settings = ilVEDAUserImporterSettings::getInstance();

		return $this->getBaseUrl().
			DIRECTORY_SEPARATOR.
			"elearningplattform".
			DIRECTORY_SEPARATOR.
			$settings->getPlatformId().
			"teilnehmer";
	}

	//TODO call a method from ..\lib\Api\ausbildung.php --> call the endpoint /v1/elearningplattform/{id}/teilnehmer
	public function getUsers()
	{
		// TODO delete this line
		$endpoint = "/Users/xus/Sites/VEDA/ILIAS/Customizing/global/plugins/Services/Cron/CronHook/VEDAUserImporter/test_data/users.json";

		// TODO uncomment this line
		//$endpoint = $this->getAllUsersEndPoint();

		ilVEDAUserImporterLogger::getLogger()->write("GET users with endpoint => ".$endpoint);

		$response = file_get_contents($endpoint);

		$items = json_decode($response);

		return $items;
	}

	/**
	 *
	 */
	public function notifyImportSuccess()
	{
		//post to veda? notification? mail?
	}



}