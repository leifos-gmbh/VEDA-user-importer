<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 * @author Jesus Lopez <lopez@leifos.com>
 */

class ilVEDAUserImporter
{
	protected static $instance = null;
	protected $users_id_login = array();
	protected $import_dir;
	protected $xml_file = "importFile.xml";
	protected $users_rest_limit = 500;
	protected $main_tag = "Users";


	/**
	 * Get import instance
	 *
	 * @return ilVEDAUserImporter
	 */
	public static function getInstance(): ilVedaUserImporter
	{
		if (self::$instance) {
			return self::$instance;
		}
		return self::$instance = new self();
	}

	/**
	 * @throws Exception
	 */
	function import()
	{
		ilVEDAUserImporterLogger::getLogger()->write("STARTING Users import");

		// Checking for import lock
		if (ilVEDAUserImporterSettings::getInstance()->isLocked()) {
			throw new ilException(ilVEDAUserImporterPlugin::getInstance()->txt('err_import_locked'));
		}

		try
		{
			$this->setCronLock();

			$this->createImportDirectory();

			$this->createImportXMLFile($this->getFilteredUsersToMigrate());

			// XML verification
			//TODO fix this
			$xml_parser = new ilUserImportParser($this->getXmlFile(),IL_VERIFY);
			$xml_parser->setUserMappingMode(IL_USER_MAPPING_ID);
			$xml_parser->setXMLContent($this->getXmlFile());
			$str = $xml_parser->getXMLContent();

			//ilVEDAUserImporterLogger::getLogger()->write("str= ".$str);
			//$xml_parser->startParsing();

			//TODO Manage the errors in a similar way
			/*if ($xml_parser->getErrorLevel() != IL_IMPORT_FAILURE)
			{
				$return = $xml_parser->getUserMapping();
				ilVEDAUserImporterLogger::getLogger()->write("if ",$return);
				//return $this->__getUserMappingAsXML ($xml_parser->getUserMapping());
			}
			else{
				$return = $xml_parser->getProtocol();
				ilVEDAUserImporterLogger::getLogger()->write("else ",$return);

			}*/

			//return $this->__getImportProtocolAsXML ($xml_parser->getProtocol());


			// XML import
			$xml_parser = new ilUserImportParser($this->getXmlFile());
			$xml_parser->startParsing();

			//TODO implement this feature
			//$users_rest_api->sendUserSucceed($users);

			$this->releaseCronLock();

		}
		catch (Exception $e) {
			ilVEDAUserImporterLogger::getLogger()->write("import() exception => " . $e->getMessage());
			$this->releaseCronLock();
			throw $e;
		}
	}

	/**
	 * Set import lock
	 */
	protected function setCronLock()
	{
		// Settings import lock
		ilVEDAUserImporterLogger::getLogger()->write('Setting import lock');
		ilVEDAUserImporterSettings::getInstance()->enableLock(true);
		ilVEDAUserImporterSettings::getInstance()->save();
	}

	/**
	 * Release lock
	 */
	protected function releaseCronLock()
	{
		// Settings import lock
		ilVEDAUserImporterLogger::getLogger()->write("Release import lock");
		ilVEDAUserImporterSettings::getInstance()->enableLock(false);
		ilVEDAUserImporterSettings::getInstance()->save();
	}

	/**
	 * Create directory inside backup folder
	 */
	public function createImportDirectory()
	{
		$this->import_dir = ilVEDAUserImporterPlugin::BACKUP_DIR.'/import_'.date('Y-m-d_H:i');

		//TODO use Filesystem for this.
		ilUtil::makeDir($this->import_dir);
		ilVEDAUserImporterLogger::getLogger()->write("Directory created->".$this->import_dir);

	}

	public function getXmlFile()
	{
		//TODO --> $this->import_dir does not working that is why I'm hardcoding the backup dir
		//use return $this->import_dir."/".$this->xml_file;
		ilVEDAUserImporterLogger::getLogger()->write("xml path => ".ilVEDAUserImporterPlugin::BACKUP_DIR.'/import_'.date('Y-m-d_H:i')."/".$this->xml_file);
		return ilVEDAUserImporterPlugin::BACKUP_DIR.'/import_'.date('Y-m-d_H:i')."/".$this->xml_file;
	}

	/**
	 * Gets an array of already validated users
	 * Create xml file in the backup/daily directory
	 * @param $users
	 */
	public function createImportXMLFile($users)
	{
		$xml = new ilVEDAImportXmlWriter();
		$xml->setMainTag($this->main_tag);
		$xml->fillData($users);
		$xml->createXMLFile();
	}

	/**
	 *
	 * @return array with \Swagger\Client\Model\TeilnehmerELearningplattform
	 * @throws Exception
	 */
	protected function getUsersFromVeda(): array
	{
		$users_rest_api = new ilVEDARestUser();

		$users = $users_rest_api->getUsers();

		return $users;
	}

	/**
	 * Perform all Filters to Feed the XML importer only with the necessary amount of users.
	 * @return array with filtered users \Swagger\Client\Model\TeilnehmerELearningplattform
	 * @throws
	 */
	public function getFilteredUsersToMigrate()
	{
		//TODO: Use this instead of the dummy data.
		//$users = $this->getUsersFromVeda();

		/**
		 * TODO: This is only a possible filter to apply.
		 */
		foreach($this->getDummyData() as $user)
		{
			ilVEDAUserImporterLogger::getLogger()->write("Model data: oid = ".$user->getOid());
		}

		die("STOP");
		return $users;
	}

	//TODO method for test purposes, delete it.
	public function getDummyData()
	{
		$container['oid'] = "bbbbbbbb-0bdf-425d-aa17-f6448f9f8124";
		$container['aktiv'] = false;
		$container['geburtsdatum'] = "1977-03-27";
		$container['geschaeftliche_e_mail_adresse'] = "leo.messi@example.com";
		$container['geschlecht'] = "M";
		$container['links'] = [];
		$container['nachname'] = "Leo";
		$container['personen_nr'] = "59X";
		$container['vorname'] = "Messi";

		return [new \Swagger\Client\Model\Teilnehmer($container)];
	}

}