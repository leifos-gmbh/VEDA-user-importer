<?php
/**
 * REST users management
 * @author Jesus Lopez <lopez@leifos.com>
 */
class ilVEDARestUser
{
	protected $session_id;
	protected $rest_base_url;
	protected $last_execution;

	function __construct()
	{
		$client = new ilVEDARestClient();
		$client->connect();
	}

	//call the endpoint /v1/elearningplattform/{id}/teilnehmer
	public function getVEDAUsers(): array
	{
		return array();
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