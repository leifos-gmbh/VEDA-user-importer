<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\HTTP\Services as HttpServices;

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
     * @var ilToolbarGUI
     */
	private $toolbar;

    /**
     * @var ilLanguage
     */
	private $lng;

    /**
     * @var ilCtrl
     */
	private $ctrl;


    protected RefineryFactory $refinery;
    protected HttpServices $http;

    /**
	 * \ilVedaConnectorConfigGUI constructor.
	 */
	public function __construct()
	{
		global $DIC;

		$this->logger = $DIC->logger()->vedaimp();
		$this->toolbar = $DIC->toolbar();
		$this->lng = $DIC->language();
		$this->ctrl = $DIC->ctrl();

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
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



		$sifa_sync = new \ilFormSectionHeaderGUI();
		$sifa_sync->setTitle($this->getPluginObject()->txt('tbl_settings_section_sifa_sync'));
		$form->addItem($sifa_sync);

		$sifa_active = new \ilCheckboxInputGUI(
		    $this->getPluginObject()->txt('tbl_settings_sifa_active'),
            'sifa_active'
        );
		$sifa_active->setChecked($settings->isSifaActive());
		$form->addItem($sifa_active);

        $roles = new ilSelectInputGUI(
            $this->getPluginObject()->txt('tbl_settings_participant_role'),
            'sifa_participant_role'
        );
        $roles->setValue($settings->getSifaParticipantRole());
        $roles->setInfo($this->getPluginObject()->txt('tbl_settings_participant_role_info'));
        $roles->setOptions($this->prepareRoleSelection());
        $roles->setRequired(true);
        $sifa_active->addSubItem($roles);

		$import_dir = new \ilRepositorySelector2InputGUI(
			$this->getPluginObject()->txt('tbl_settings_course_import'),
			'sifa_crs_import',
			true
		);
		$import_dir->setRequired(true);
		$import_dir->setInfo($this->getPluginObject()->txt('tbl_settings_course_import_info'));
		$white_list[] = 'cat';
		$import_dir->getExplorerGUI()->setTypeWhiteList($white_list);
		$import_dir->setValue($settings->getSifaImportDirectory());
		$sifa_active->addSubItem($import_dir);

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
		$sifa_active->addSubItem($switch);

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
        $sifa_active->addSubItem($switcht);

        $sibe_sync = new \ilFormSectionHeaderGUI();
        $sibe_sync->setTitle($this->getPluginObject()->txt('tbl_settings_section_sibe_sync'));
        $form->addItem($sibe_sync);

        $sibe_active = new \ilCheckboxInputGUI(
            $this->getPluginObject()->txt('tbl_settings_sibe_active'),
            'sibe_active'
        );
        $sibe_active->setChecked($settings->isSibeActive());
        $form->addItem($sibe_active);

        $roles = new ilSelectInputGUI(
            $this->getPluginObject()->txt('tbl_settings_participant_role'),
            'sibe_participant_role'
        );
        $roles->setValue($settings->getSibeParticipantRole());
        $roles->setInfo($this->getPluginObject()->txt('tbl_settings_participant_role_info'));
        $roles->setOptions($this->prepareRoleSelection());
        $roles->setRequired(true);
        $sibe_active->addSubItem($roles);

        $import_dir = new \ilRepositorySelector2InputGUI(
            $this->getPluginObject()->txt('tbl_settings_course_import'),
            'sibe_crs_import',
            true
        );
        $import_dir->setRequired(true);
        $import_dir->setInfo($this->getPluginObject()->txt('tbl_settings_course_import_info'));
        $white_list[] = 'cat';
        $import_dir->getExplorerGUI()->setTypeWhiteList($white_list);
        $import_dir->setValue($settings->getSibeImportDirectory());
        $sibe_active->addSubItem($import_dir);

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
				$settings->setSifaParticipantRole($form->getInput('sifa_participant_role'));
                $settings->setSibeParticipantRole($form->getInput('sibe_participant_role'));
				$settings->enableLock($form->getInput('lock'));

				$category_ref_ids = $form->getInput('sifa_crs_import');
				$settings->setSifaImportDirectory((int) end($category_ref_ids));
                $category_ref_ids = $form->getInput('sibe_crs_import');
                $settings->setSibeImportDirectory((int) end($category_ref_ids));
				$settings->setPermanentSwitchRole($form->getInput('switch_permanent'));
				$settings->setTemporarySwitchRole($form->getInput('switch_temp'));

				$settings->setSibeActive($form->getInput('sibe_active'));
                $settings->setSiFaActive($form->getInput('sifa_active'));
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

        $add_header_auth = new ilCheckboxInputGUI($this->getPluginObject()->txt('additional_header_authentication'), 'add_header_auth');
        $add_header_auth->setChecked($settings->isAddHeaderAuthEnabled());

        $add_header_name = new ilTextInputGUI($this->getPluginObject()->txt('additional_header_name'), 'add_header_name');
        $add_header_name->setValue($settings->getAddHeaderName());

        $add_header_auth->addSubItem($add_header_name);

        $add_header_value = new ilTextInputGUI($this->getPluginObject()->txt('additional_header_value'), 'add_header_value');
        $add_header_value->setValue($settings->getAddHeaderValue());

        $add_header_auth->addSubItem($add_header_value);

        $form->addItem($add_header_auth);


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
				$settings->setAddHeaderAuth( (bool) $form->getInput('add_header_auth'));
				$settings->setAddHeaderName($form->getInput('add_header_name'));
				$settings->setAddHeaderValue($form->getInput('add_header_value'));
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

		$sifa = new ilFormSectionHeaderGUI();
		$sifa->setTitle($this->getPluginObject()->txt('section_import_sifa'));
		$form->addItem($sifa);

		// selection all or single elements
		$imp_type = new ilRadioGroupInputGUI($this->getPluginObject()->txt('import_selection'),'selection_' . ilVedaImporter::IMPORT_TYPE_SIFA);
		$imp_type->setValue(\ilVedaImporter::IMPORT_NONE);
		$form->addItem($imp_type);

        $none = new ilRadioOption($this->getPluginObject()->txt('import_selection_none'),\ilVedaImporter::IMPORT_NONE);
        $imp_type->addOption($none);

		$all = new ilRadioOption($this->getPluginObject()->txt('import_selection_all'),\ilVedaImporter::IMPORT_ALL);
		$imp_type->addOption($all);

		$sel = new ilRadioOption($this->getPluginObject()->txt('import_selection_selected'), \ilVedaImporter::IMPORT_SELECTED);
		$imp_type->addOption($sel);

		$usr = new ilCheckboxInputGUI($lng->txt('obj_usr'),'usr_' . \ilVedaImporter::IMPORT_TYPE_SIFA);
		$usr->setValue(\ilVedaImporter::IMPORT_USR);
		$sel->addSubItem($usr);


		$crs = new ilCheckboxInputGUI($lng->txt('objs_crs'),'crs_' . \ilVedaImporter::IMPORT_TYPE_SIFA);
		$crs->setValue(\ilVedaImporter::IMPORT_CRS);
		$sel->addSubItem($crs);

		$mem = new ilCheckboxInputGUI($this->getPluginObject()->txt('type_membership'),'mem_' . \ilVedaImporter::IMPORT_TYPE_SIFA);
		$mem->setValue(\ilVedaImporter::IMPORT_MEM);
		$sel->addSubItem($mem);

        $sibe = new ilFormSectionHeaderGUI();
        $sibe->setTitle($this->getPluginObject()->txt('section_import_sibe'));
        $form->addItem($sibe);

        // selection all or single elements
        $imp_type = new ilRadioGroupInputGUI($this->getPluginObject()->txt('import_selection'),'selection_' . ilVedaImporter::IMPORT_TYPE_SIBE);
        $imp_type->setValue(\ilVedaImporter::IMPORT_NONE);
        $form->addItem($imp_type);

        $none = new ilRadioOption($this->getPluginObject()->txt('import_selection_none'),\ilVedaImporter::IMPORT_NONE);
        $imp_type->addOption($none);

        $all = new ilRadioOption($this->getPluginObject()->txt('import_selection_all'),\ilVedaImporter::IMPORT_ALL);
        $imp_type->addOption($all);

        $sel = new ilRadioOption($this->getPluginObject()->txt('import_selection_selected'), \ilVedaImporter::IMPORT_SELECTED);
        $imp_type->addOption($sel);

        $usr = new ilCheckboxInputGUI($lng->txt('obj_usr'),'usr_' . \ilVedaImporter::IMPORT_TYPE_SIBE);
        $usr->setValue(\ilVedaImporter::IMPORT_USR);
        $sel->addSubItem($usr);


        $crs = new ilCheckboxInputGUI($lng->txt('objs_crs'),'crs_' . \ilVedaImporter::IMPORT_TYPE_SIBE);
        $crs->setValue(\ilVedaImporter::IMPORT_CRS);
        $sel->addSubItem($crs);

        $mem = new ilCheckboxInputGUI($this->getPluginObject()->txt('type_membership'),'mem_' . \ilVedaImporter::IMPORT_TYPE_SIBE);
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
		    foreach ([\ilVedaImporter::IMPORT_TYPE_SIFA, \ilVedaImporter::IMPORT_TYPE_SIBE] as $import_type) {
		        $import_type_selection = (int) $form->getInput('selection_' . (string) $import_type);
		        if ($import_type_selection == ilVedaImporter::IMPORT_NONE) {
		            continue;
                }
                $modes = [];
                foreach([
                    \ilVedaImporter::IMPORT_USR,
                    \ilVedaImporter::IMPORT_CRS,
                    \ilVedaImporter::IMPORT_MEM] as $mode) {
                    if($form->getInput($mode . '_' . (string) $import_type)) {
                        $modes[] = $mode;
                    }
                }
		        $importer = \ilVedaImporter::getInstance();
                $importer->setImportType($import_type);
                $this->logger->dump($import_type, \ilLogLevel::NOTICE);
                $this->logger->dump($import_type_selection, \ilLogLevel::NOTICE);
                $this->logger->dump($modes, \ilLogLevel::NOTICE);
		        $importer->setImportMode(
                    (bool) ($import_type_selection == \ilVedaImporter::IMPORT_ALL),
                    $modes
                );
		        $importer->import();
            }
		}
		catch(Exception $e) {
		    $this->logger->logStack(\ilLogLevel::WARNING);
			ilUtil::sendFailure('Import failed with message: ' . $e->getMessage());
			$this->import($form);
			return true;
		}

		ilUtil::sendSuccess($this->getPluginObject()->txt('success_import'));
		$form->setValuesByPost();
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

    protected function migrateUser()
    {
        $oid = $this->http->request()->getQueryParams()['oid'] ?? '';
        $login = $this->http->request()->getQueryParams()['login'] ?? '';
        if ($oid === '' || $login === '') {
            ilUtil::sendFailure($this->lng->txt('err_check_input'), true);
            $this->ctrl->redirect($this, 'importResultUser');
        }
        $obj_id_from_oid = ilObjUser::_getImportedUserId($oid);
        $type_from_oid = ilObject::_lookupType($obj_id_from_oid);
        $obj_id_from_login = ilObjUser::_loginExists($login);
        $import_id_from_login = ilObject::_lookupImportId($obj_id_from_login);

        if ($import_id_from_login != '') {
            $this->logger->warning('Migration failed: user already imported');
            ilUtil::sendFailure($this->lng->txt('err_check_input'), true);
            $this->ctrl->redirect($this, 'importResultUser');
        }
        if ($obj_id_from_login) {
            $this->logger->warning('Migration failed: user does not exist');
            ilUtil::sendFailure($this->lng->txt('err_check_input'), true);
            $this->ctrl->redirect($this, 'importResultUser');
        }
        if ($obj_id_from_oid > 0) {
            $this->logger->warning('Migration failed: user already imported');
            ilUtil::sendFailure($this->lng->txt('err_check_input'), true);
            $this->ctrl->redirect($this, 'importResultUser');
        }
        ilObjUser::_writeImportId($obj_id_from_login, $oid);
        $status = new ilVedaUserStatus($oid);
        $status->setImportFailure(false);
        $status->save();
        ilUtil::sendSuccess(ilVedaConnectorPlugin::getInstance()->txt('migrated_account'), true);
        $this->ctrl->redirect($this, 'importResultUser');
    }


}
