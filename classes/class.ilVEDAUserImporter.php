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
	 * @throws ilException
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

			$users_rest_api = new ilVEDARestUser();

			$users = $users_rest_api->getUsers();

			$this->createImportXMLFile($users->result);

			// XML verification
			//TODO Fix the XML Verification error Not well formed (invalid token)
			//$xml_parser = new ilUserImportParser($this->getXmlFile(),IL_VERIFY);
			//$xml_parser->setUserMappingMode(IL_USER_MAPPING_ID);
			//$xml_parser->setXMLContent($this->getXmlFile());
			//$xml_parser->startParsing();


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
		catch (ilException $e) {
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
		//create XML
		$xml = new ilVEDAImportXmlWriter();
		$xml->setMainTag($this->main_tag);
		$xml->fillData($users);
		$xml->createXMLFile();
	}

}