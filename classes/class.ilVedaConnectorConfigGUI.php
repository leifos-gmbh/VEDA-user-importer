<?php

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_Calls ilVedaConnectorConfigGUI: ilPropertyFormGUI
 */
class ilVedaConnectorConfigGUI extends ilPluginConfigGUI
{
	protected const TAB_SETTINGS = 'settings';
	protected const TAB_CREDENTIALS = 'credentials';
	protected const TAB_IMPORT = 'import';

	protected const SUBTAB_IMPORT = 'import';
	protected const SUBTAB_IMPORT_USR = 'import_usr';
	protected const SUBTAB_IMPORT_CRS = 'import_crs';


	/**
	 * \ilLogger
	 */
	private $logger = null;


	/**
	 * \ilVedaConnectorConfigGUI constructor.
	 */
	public function __construct()
	{
		global $DIC;

		$this->logger = $DIC->logger()->vedaimp();
	}

	/**
	 * Forward to property form gui
	 */
	public function executeCommand()
	{
		global $DIC;

		$ctrl = $DIC->ctrl();
		$next_class = $ctrl->getNextClass();
		$this->logger->info($next_class);

		switch($next_class) {
			case strtolower(\ilPropertyFormGUI::class):
				$form = $this->initConfigurationForm();
				$ctrl->forwardCommand($form);
				break;
		}

		return parent::executeCommand();
	}


	/**
	 * @inheritdoc
	 */
	public function performCommand($cmd)
	{
		global $DIC;

		$ilCtrl = $DIC->ctrl();
		$ilTabs = $DIC->tabs();

		$ilTabs->addTab(
			self::TAB_CREDENTIALS,
			\ilVedaConnectorPlugin::getInstance()->txt('tab_credentials'),
			$ilCtrl->getLinkTarget($this, 'credentials')
		);

		$ilTabs->addTab(
			self::TAB_SETTINGS,
			\ilVedaConnectorPlugin::getInstance()->txt('tab_settings'),
			$ilCtrl->getLinkTarget($this, 'configure')
		);

		$ilTabs->addTab(
			self::TAB_IMPORT,
			\ilVedaConnectorPlugin::getInstance()->txt('tab_import'),
			$ilCtrl->getLinkTarget($this,'import')
		);


		switch ($cmd)
		{
			default:
				$this->$cmd();
				break;
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function configure(ilPropertyFormGUI $form = null): void
	{
		global $DIC;

		$tpl = $DIC->ui()->mainTemplate();
		$tabs = $DIC->tabs();

		$tabs->activateTab(self::TAB_SETTINGS);

		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initConfigurationForm();
		}
		$tpl->setContent($form->getHTML());
	}

	/**
	 * @return \ilPropertyFormGUI
	 */
	protected function initConfigurationForm(): ilPropertyFormGUI
	{
		global $DIC;

		$lng = $DIC->language();
		$ctrl = $DIC->ctrl();
		$definition = $DIC['objDefinition'];

		$settings = \ilVedaConnectorSettings::getInstance();

		$form = new \ilPropertyFormGUI();
		$form->setTitle($this->getPluginObject()->txt('tbl_settings'));
		$form->setFormAction($ctrl->getFormAction($this));
		$form->addCommandButton('save', $lng->txt('save'));
		$form->setShowTopButtons(false);

		$lock = new \ilCheckboxInputGUI($this->getPluginObject()->txt('tbl_veda_settings_active'),'active');
		$lock->setValue(1);
		$lock->setChecked($settings->isActive());
		$form->addItem($lock);



		$lock = new ilCheckboxInputGUI($this->getPluginObject()->txt('tbl_veda_settings_lock'),'lock');
		$lock->setValue(1);
		$lock->setDisabled(!$settings->isLocked());
		$lock->setChecked($settings->isLocked());
		$lock->setInfo($this->getPluginObject()->txt('tbl_veda_settings_lock_info'));
		$form->addItem($lock);

		$lng->loadLanguageModule('log');
		$level = new ilSelectInputGUI($this->getPluginObject()->txt('tbl_veda_settings_loglevel'),'log_level');
		$level->setHideSubForm($settings->getLogLevel() == \ilLogLevel::OFF,'< 1000');
		$level->setOptions(\ilLogLevel::getLevelOptions());
		$level->setValue($settings->getLogLevel());
		$form->addItem($level);

		$log_file = new \ilTextInputGUI($this->getPluginObject()->txt('tbl_veda_settings_logfile'),'log_file');
		$log_file->setValue($settings->getLogFile());
		$log_file->setInfo($this->getPluginObject()->txt('tbl_veda_settings_logfile_info'));
		$level->addSubItem($log_file);



		// cron interval
		$cron_i = new ilNumberInputGUI($this->getPluginObject()->txt('cron'),'cron_interval');
		$cron_i->setMinValue(1);
		$cron_i->setSize(2);
		$cron_i->setMaxLength(3);
		$cron_i->setRequired(true);
		$cron_i->setValue($settings->getCronInterval());
		$cron_i->setInfo($this->getPluginObject()->txt('cron_interval'));
		#$form->addItem($cron_i);

		$user_sync = new \ilFormSectionHeaderGUI();
		$user_sync->setTitle($this->getPluginObject()->txt('tbl_settings_section_user_sync'));
		$form->addItem($user_sync);

		$roles = new ilSelectInputGUI(
			$this->getPluginObject()->txt('tbl_settings_participant_role'),
			'participant_role'
		);
		$roles->setValue($settings->getParticipantRole());
		$roles->setInfo($this->getPluginObject()->txt('tbl_settings_participant_role_info'));
		$roles->setOptions($this->prepareRoleSelection());
		$roles->setRequired(true);
		$form->addItem($roles);

		$course_sync = new \ilFormSectionHeaderGUI();
		$course_sync->setTitle($this->getPluginObject()->txt('tbl_settings_section_course_sync'));
		$form->addItem($course_sync);

		$import_dir = new \ilRepositorySelector2InputGUI(
			$this->getPluginObject()->txt('tbl_settings_course_import'),
			'crs_import',
			true
		);
		$import_dir->setRequired(true);
		$import_dir->setInfo($this->getPluginObject()->txt('tbl_settings_course_import_info'));
		$white_list[] = 'cat';
		$import_dir->getExplorerGUI()->setTypeWhiteList($white_list);
		$import_dir->setValue($settings->getImportDirectory());

		$form->addItem($import_dir);

		$switch = new \ilNumberInputGUI(
			$this->getPluginObject()->txt('tbl_settings_switch_permanent_role'),
			'switch_permanent'
		);
		$switch->setRequired(true);
		if($settings->getPermanentSwitchRole()) {
			$switch->setValue($settings->getPermanentSwitchRole());
			$switch->setSuffix(\ilObject::_lookupTitle($settings->getPermanentSwitchRole()));
		}
		$switch->setInfo($this->getPluginObject()->txt('tbl_settings_switch_permanent_role_info'));
		$form->addItem($switch);

		$switcht = new \ilNumberInputGUI(
			$this->getPluginObject()->txt('tbl_settings_switch_temp_role'),
			'switch_temp'
		);
		$switcht->setRequired(true);
		if($settings->getTemporarySwitchRole()) {
			$switcht->setValue($settings->getTemporarySwitchRole());
			$switcht->setSuffix(\ilObject::_lookupTitle($settings->getTemporarySwitchRole()));
		}
		$switcht->setInfo($this->getPluginObject()->txt('tbl_settings_switch_temp_role_info'));
		$form->addItem($switcht);

		return $form;
	}

	/**
	 * Save settings
	 */
	protected function save(): void
	{
		global $DIC;

		$lng = $DIC->language();
		$ctrl = $DIC->ctrl();

		$form = $this->initConfigurationForm();
		$settings = ilVedaConnectorSettings::getInstance();

		try
		{
			if($form->checkInput())
			{
				$settings->setActive($form->getInput('active'));
				$settings->setLogLevel($form->getInput('log_level'));
				$settings->setLogFile($form->getInput('log_file'));
				$settings->setParticipantRole($form->getInput('participant_role'));
				$settings->enableLock($form->getInput('lock'));

				$category_ref_ids = $form->getInput('crs_import');
				$settings->setImportDirectory((int) end($category_ref_ids));
				$settings->setPermanentSwitchRole($form->getInput('switch_permanent'));
				$settings->setTemporarySwitchRole($form->getInput('switch_temp'));
				$settings->save();

				ilUtil::sendSuccess($lng->txt('settings_saved'),true);
				$ctrl->redirect($this,'configure');
			}
			$error = $lng->txt('err_check_input');
		}
		catch(ilException $e)
		{
			$error = $e->getMessage();
			$this->logger->error('Configuration error: ' . $error);
		}
		$form->setValuesByPost();
		ilUtil::sendFailure($error);
		$this->configure($form);
	}

	/**
	 * @param \ilPropertyFormGUI|null $form
	 */
	protected function credentials(ilPropertyFormGUI $form = null): void
	{
		global $DIC;

		$tpl = $DIC->ui()->mainTemplate();
		$tabs = $DIC->tabs();
		$ctrl = $DIC->ctrl();

		$tabs->activateTab(self::TAB_CREDENTIALS);

		if(\ilVedaConnectorSettings::getInstance()->hasSettingsForConnectionTest()) {

			$button = ilLinkButton::getInstance();
			$button->setCaption($this->getPluginObject()->txt('connection_test'), false);
			$button->setUrl($ctrl->getLinkTarget($this, 'ping'));

			$toolbar = $DIC->toolbar();
			$toolbar->addButtonInstance($button);
		}


		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initCredentialsForm();
		}

		$tpl->setContent($form->getHTML());
	}

	/**
	 * @return \ilPropertyFormGUI
	 */
	protected function initCredentialsForm(): ilPropertyFormGUI
	{
		global $DIC;

		$ctrl = $DIC->ctrl();
		$lng = $DIC->language();

		$settings = ilVedaConnectorSettings::getInstance();

		$form = new ilPropertyFormGUI();
		$form->setTitle($this->getPluginObject()->txt('tbl_settings'));
		$form->setFormAction($ctrl->getFormAction($this));

		$form->addCommandButton('saveCredentials', $lng->txt('save'));
		$form->setShowTopButtons(false);

		$url = new ilTextInputGUI($this->getPluginObject()->txt('credentials_url'), 'resturl');
		$url->setRequired(true);
		$url->setSize(120);
		$url->setMaxLength(512);
		$url->setValue($settings->getRestUrl());
		$form->addItem($url);

		$authentication_id = new ilTextInputGUI($this->getPluginObject()->txt('authentication_id'),'authentication_id');
		$authentication_id->setRequired(true);
		$authentication_id->setValue($settings->getAuthenticationToken());
		$authentication_id->setInfo($this->getPluginObject()->txt('authentication_id_info'));
		$form->addItem($authentication_id);

		$platform_id = new ilTextInputGUI($this->getPluginObject()->txt('platform_id'),'platform_id');
		$platform_id->setRequired(true);
		$platform_id->setValue($settings->getPlatformId());
		$platform_id->setInfo($this->getPluginObject()->txt('platform_id_info'));
		$form->addItem($platform_id);

		return $form;
	}

	/**
	 * Save credentials
	 */
	protected function saveCredentials(): void
	{
		global $DIC;

		$ctrl = $DIC->ctrl();
		$lng = $DIC->language();

		$form = $this->initCredentialsForm();
		$settings = ilVedaConnectorSettings::getInstance();

		try
		{
			if($form->checkInput())
			{
				$settings->setRestUrl($form->getInput('resturl'));
				$settings->setRestUser($form->getInput('restuser'));
				$settings->setRestPassword($form->getInput('restpassword'));
				$settings->setAuthenticationToken($form->getInput('authentication_id'));
				$settings->setPlatformId($form->getInput('platform_id'));
				$settings->save();

				ilUtil::sendSuccess($lng->txt('settings_saved'),true);
				$ctrl->redirect($this,'credentials');
			}
			$error = $lng->txt('err_check_input');
		}
		catch(ilException $e)
		{
			$error = $e->getMessage();
			\ilVedaConnectorPlugin::getInstance()->getLogger()->error('Error saving credentials: ' . $error);
		}
		$form->setValuesByPost();
		ilUtil::sendFailure($error);
		$this->credentials($form);
	}

	/**
	 * @param \ilPropertyFormGUI|null $form
	 */
	protected function import(\ilPropertyFormGUI $form = null)
	{
		global $DIC;

		$tpl = $DIC->ui()->mainTemplate();
		$tabs = $DIC->tabs();

		$this->setSubTabs();
		$tabs->activateTab(self::TAB_IMPORT);
		$tabs->activateSubTab(self::SUBTAB_IMPORT);

		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initImportForm();
		}
		$tpl->setContent($form->getHTML());
	}

	/**
	 * @return \ilPropertyFormGUI
	 */
	protected function initImportForm()
	{
		global $DIC;

		$ilCtrl = $DIC->ctrl();
		$lng = $DIC->language();

		$form = new ilPropertyFormGUI();
		$form->setTitle($this->getPluginObject()->txt('tbl_import'));
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->addCommandButton('doImport', $this->getPluginObject()->txt('btn_import'));

		// selection all or single elements
		$imp_type = new ilRadioGroupInputGUI($this->getPluginObject()->txt('import_selection'),'selection');
		$imp_type->setValue(\ilVedaImporter::IMPORT_SELECTED);
		$imp_type->setRequired(true);
		$form->addItem($imp_type);

		$all = new ilRadioOption($this->getPluginObject()->txt('import_selection_all'),\ilVedaImporter::IMPORT_ALL);
		$imp_type->addOption($all);

		$sel = new ilRadioOption($this->getPluginObject()->txt('import_selection_selected'), \ilVedaImporter::IMPORT_SELECTED);
		$imp_type->addOption($sel);

		$usr = new ilCheckboxInputGUI($lng->txt('obj_usr'),'usr');
		$usr->setValue(\ilVedaImporter::IMPORT_USR);
		$sel->addSubItem($usr);


		$crs = new ilCheckboxInputGUI($lng->txt('objs_crs'),'crs');
		$crs->setValue(\ilVedaImporter::IMPORT_CRS);
		$sel->addSubItem($crs);

		$mem = new ilCheckboxInputGUI($this->getPluginObject()->txt('type_membership'),'mem');
		$mem->setValue(\ilVedaImporter::IMPORT_MEM);
		$sel->addSubItem($mem);

		$form->setShowTopButtons(false);

		return $form;
	}

	/**
	 *
	 */
	protected function doImport()
	{
		global $DIC;

		$lng = $DIC->language();

		$form = $this->initImportForm();
		if(!$form->checkInput()) {
			ilUtil::sendFailure($lng->txt('err_check_input'));
			return $this->import($form);
		}

		try{
			$importer = \ilVedaImporter::getInstance();
			if($form->getInput('selection') == \ilVedaImporter::IMPORT_ALL) {
				$importer->setImportMode(true);
			}
			else {
				$modes = [];
				foreach(['usr', 'crs', 'mem'] as $mode) {
					if($form->getInput($mode)) {
						$modes[] = $mode;
					}
				}
				$importer->setImportMode(false, $modes);
			}

			$importer->import();
		}
		catch(Exception $e) {
			ilUtil::sendFailure('Import failed with message: ' . $e->getMessage());
			$this->import($form);
			return true;
		}

		ilUtil::sendSuccess($this->getPluginObject()->txt('success_import'));
		$this->import($form);
	}

	/**
	 * show user import result
	 */
	protected function importResultUser()
	{
		global $DIC;

		$tabs = $DIC->tabs();
		$tpl = $DIC->ui()->mainTemplate();

		$this->setSubTabs();
		$tabs->activateTab(self::TAB_IMPORT);
		$tabs->activateSubTab(self::SUBTAB_IMPORT_USR);

		$table = new ilVedaUserImportResultTableGUI($this, __FUNCTION__);
		$table->init();
		$table->parse();

		$tpl->setContent($table->getHTML());
	}

	/**
	 * @throws \ilDatabaseException
	 */
	protected function importResultCourse()
	{
		global $DIC;

		$tabs = $DIC->tabs();
		$tpl = $DIC->ui()->mainTemplate();

		$this->setSubTabs();
		$tabs->activateTab(self::TAB_IMPORT);
		$tabs->activateSubTab(self::SUBTAB_IMPORT_CRS);

		$table = new ilVedaCourseImportResultTableGUI($this, __FUNCTION__);
		$table->init();
		$table->parse();

		$tpl->setContent($table->getHTML());

	}


	/**
	 * Set subtabs
	 */
	protected function setSubTabs()
	{
		global $DIC;

		$ctrl = $DIC->ctrl();
		$tabs = $DIC->tabs();

		$tabs->addSubTab(
			self::SUBTAB_IMPORT,
			$this->getPluginObject()->txt('subtab_import'),
			$ctrl->getLinkTarget($this, 'import')
		);

		$tabs->addSubTab(
			self::SUBTAB_IMPORT_USR,
			$this->getPluginObject()->txt('subtab_import_usr'),
			$ctrl->getLinkTarget($this, 'importResultUser')
		);

		$tabs->addSubTab(
			self::SUBTAB_IMPORT_CRS,
			$this->getPluginObject()->txt('subtab_import_crs'),
			$ctrl->getLinkTarget($this, 'importResultCourse')
		);

	}
	/**
	 * Test connection
	 */
	protected function ping()
	{
		try {
			$settings = \ilVedaConnectorSettings::getInstance();
			$connection = \ilVedaConnector::getInstance();
			$response = $connection->getParticipants();

			$helper = \ilVedaMDHelper::getInstance();
			$id = $helper->findTrainingCourseId(70);

			$this->logger->notice($id . ' is the training course id');



			ilUtil::sendSuccess($this->getPluginObject()->txt('success_api_connect'));
		}
		catch(\Exception $e) {
			$this->logger->warning('Connection test failed with message: ' . $e->getMessage());
			ilUtil::sendFailure($e->getMessage());
		}
		$this->credentials();
	}

	/**
	 * @param bool $a_with_select_option
	 * @return mixed
	 */
	protected function prepareRoleSelection($a_with_select_option = true) : array
	{
		global $DIC;

		$lng = $DIC->language();
		$review = $DIC->rbac()->review();

		$global_roles = ilUtil::_sortIds(
			$review->getGlobalRoles(),
			'object_data',
			'title',
			'obj_id'
		);

		$select = [];
		if($a_with_select_option)
		{
			$select[0] = $lng->txt('links_select_one');
		}
		foreach($global_roles as $role_id)
		{
			if($role_id == ANONYMOUS_ROLE_ID)
			{
				continue;
			}
			$select[$role_id] = ilObject::_lookupTitle($role_id);
		}
		return $select;
	}


}
