<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * XML writer for Users.
 * @author Jesus Lopez <lopez@leifos.com>
 */
class ilVEDAImportXmlWriter
{
	protected static $instance = null;

	/**
	 * @var ilXmlWriter
	 */
	protected $writer;

	/**
	 * @var string
	 */
	protected $main_tag = "Users";


	function __construct()
	{
		ilVEDAUserImporterLogger::getLogger()->write("El path en el constructor = ".ilVEDAUserImporter::getInstance()->getXmlFile());
		$this->initWriter();
		$this->xmlHeading();
	}

	protected function initWriter()
	{
		$this->writer = new ilXmlWriter();
	}

	protected function xmlHeading()
	{
		$this->writer->xmlHeader();
		$this->writer->xmlStartTag($this->main_tag);

	}

	function setMainTag($a_tag)
	{
		$this->main_tag = $a_tag;
	}


	function fillData($a_data)
	{
		global $ilSetting;

		foreach ($a_data as $data)
		{
			$action = 'Insert';

			/**
			 * //DIscuss this with stefan Maybe XMl per user will be more flexible. if user already exist and has import id. Nothing to do.
			 * TODO Think about this process, if the user does not have import ID should be created?
			 * What happens if the user already exsists in ILIAS? we are updating everything again!!! we change the login, password etc..should define this better.
			 * LOGIN defines unique user so we can not use the email for this matter read about in point 3.3.3. So we have to create a custom login for this duplicates or avoid migrate users with same email.
			 *
			*/
			$ilias_user_id = ilObject::_lookupObjIdByImportId((string) $data->teilnehmer->oid);

			if($ilias_user_id)
			{
				$action = 'Update';
			}
			else
			{
				$ilias_user_id = "";
			}

			$this->writer->xmlStartTag(
				'User',
				array(
					"Id" =>  $ilias_user_id,
					"Login" => $data->teilnehmer->geschaeftlicheEMailAdresse,
					"Action" => $action
				)
			);

			$this->writer->xmlElement('Login', null, $data->teilnehmer->geschaeftlicheEMailAdresse);

			$ilias_client_id = $ilSetting->get("inst_id",0);


			//TODO this is not working properly
			//not working the following:
			//$ilias_client_id = "54vedaexercise";
			$this->writer->xmlElement('Role', array("Id"=>"il_".$ilias_client_id."_role_4", "Type"=>"Global", "Action" => "Assign"),'User');
			/*$this->writer->xmlElement(
				'Role',
				array(
					'Id' => 4,
					'Type' => 'Global',
					'Action' => 'Assign'
				),
				'User'
			);*/

			//$this->writer->xmlElement('Role', array("Id"=>"il_".$ilias_client_id."_role_4", "Type"=>"Global", "Action" => "Assign"),4);

			//TODO this is not working properly
			$this->writer->xmlElement('Import_id', null, $data->teilnehmer->oid);
			$this->writer->xmlElement('Firstname', null, $data->teilnehmer->vorname);
			$this->writer->xmlElement('Lastname', null, $data->teilnehmer->nachname);
			$this->writer->xmlElement('Gender', null, $data->teilnehmer->geschlecht);
			$this->writer->xmlElement('Email',null, $data->teilnehmer->geschaeftlicheEMailAdresse);

			//IF the user is new in ILIAS we send the dummy password
			if($action = 'Insert')
			{
				$this->writer->xmlElement('Password',array("Type"=>"PLAIN"), $data->initialesPasswort);
			}

			$this->writer->xmlEndTag('User');
		}
	}

	public function createXMLFile()
	{
		$this->xmlEnding();
		$this->writer->xmlDumpFile(ilVEDAUserImporter::getInstance()->getXmlFile(), false);
	}

	protected function xmlEnding()
	{
		$this->writer->xmlEndTag($this->main_tag);
	}
}