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
	protected $users_api;
	protected $elearning_platform_id;

	function __construct()
	{
		parent::__construct();

		$this->users_api = new \Swagger\Client\Api\AusbildungApi(null,$this->swagger_configuration,null);

		$this->elearning_platform_id = ilVEDAUserImporterSettings::getInstance()->getPlatformId();
	}

	/**
	 * endpoint /v1/elearningplattform/{id}/teilnehmer
	 * @return array with \Swagger\Client\Model\TeilnehmerELearningplattform
	 * @throws \Swagger\Client\ApiException
	 */
	public function getUsers() : array
	{
		return $this->users_api->getTeilnehmerELearningPlattformUsingGET($this->elearning_platform_id);
		/*
		// TODO delete this line
		$endpoint = "/Users/xus/Sites/VEDA/ILIAS/Customizing/global/plugins/Services/Cron/CronHook/VEDAUserImporter/test_data/users.json";

		// TODO uncomment this line
		//$endpoint = $this->getAllUsersAPI();

		ilVEDAUserImporterLogger::getLogger()->write("GET users with endpoint => ".$endpoint);

		$response = file_get_contents($endpoint);

		$items = json_decode($response);

		return $items;
		*/
	}

	/**
	 *
	 */
	public function notifyImportSuccess()
	{
		//post to veda? notification? mail?
	}



}