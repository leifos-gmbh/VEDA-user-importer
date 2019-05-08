<?php
include_once './Services/Component/classes/class.ilPluginConfigGUI.php';

/**
 * @author Jesus Lopez <lopez@leifos.com>
 */
class ilVEDAUserImporterConfigGUI extends ilPluginConfigGUI
{
	/**
	 * Handles all commmands, default is "configure"
	 */
	public function performCommand($cmd)
	{
		global $ilCtrl;
		global $ilTabs;

		$ilTabs->addTab(
			'settings',
			ilVEDAUserImporterPlugin::getInstance()->txt('tab_settings'),
			$GLOBALS['ilCtrl']->getLinkTarget($this,'configure')
		);

		$ilTabs->addTab(
			'credentials',
			ilVEDAUserImporterPlugin::getInstance()->txt('tab_credentials'),
			$GLOBALS['ilCtrl']->getLinkTarget($this, 'credentials')
		);

		$ilCtrl->saveParameter($this, "menu_id");

		switch ($cmd)
		{
			default:
				$this->$cmd();
				break;
		}
	}

	/**
	 * Show settings screen
	 * @param ilPropertyFormGUI $form
	 * @global $tpl
	 * @global $ilTabs
	 */
	protected function configure(ilPropertyFormGUI $form = null)
	{
		global $tpl, $ilTabs;

		$ilTabs->activateTab('settings');

		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initConfigurationForm();
		}
		$tpl->setContent($form->getHTML());
	}

	/**
	 * Init configuration form
	 * @global $ilCtrl
	 * @return ilPropertyFormGUI form
	 */
	protected function initConfigurationForm()
	{
		global $ilCtrl, $lng;

		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';

		$settings = ilVEDAUserImporterSettings::getInstance();

		$form = new ilPropertyFormGUI();
		$form->setTitle($this->getPluginObject()->txt('tbl_veda_settings'));
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->addCommandButton('save', $lng->txt('save'));
		$form->setShowTopButtons(false);

		$lock = new ilCheckboxInputGUI($this->getPluginObject()->txt('tbl_settting_lock'),'lock');
		$lock->setValue(1);
		$lock->setDisabled(!$settings->isLocked());
		$lock->setChecked($settings->isLocked());
		$form->addItem($lock);

		$backup = new ilTextInputGUI($this->getPluginObject()->txt('tbl_settings_backup'),'backup');
		$backup->setRequired(true);
		$backup->setSize(120);
		$backup->setMaxLength(512);
		$backup->setValue($settings->getBackupDir());
		$form->addItem($backup);

		// cron interval
		$cron_i = new ilNumberInputGUI($this->getPluginObject()->txt('cron'),'cron_interval');
		$cron_i->setMinValue(1);
		$cron_i->setSize(2);
		$cron_i->setMaxLength(3);
		$cron_i->setRequired(true);
		$cron_i->setValue($settings->getCronInterval());
		$cron_i->setInfo($this->getPluginObject()->txt('cron_interval'));
		$form->addItem($cron_i);

		return $form;
	}

	/**
	 * Save settings
	 */
	protected function save()
	{
		global $lng, $ilCtrl;

		$form = $this->initConfigurationForm();
		$settings = ilVEDAUserImporterSettings::getInstance();

		try {

			if($form->checkInput())
			{
				$settings->enableLock($form->getInput('lock'));
				//$settings->setImportDir($form->getInput('import'));
				//todo backup dir
				$settings->setBackupDir($form->getInput('backup'));
				$settings->setCronInterval($form->getInput('cron_interval'));
				$settings->save();

				//todo directories
				$settings->createDirectories();

				ilUtil::sendSuccess($lng->txt('settings_saved'),true);
				$ilCtrl->redirect($this,'configure');
			}
			$error = $lng->txt('err_check_input');
		}
		catch(ilException $e)
		{
			$error = $e->getMessage();
			ilVEDAUserImporterLogger::getLogger()->write("save() exception: ".$error);
		}
		$form->setValuesByPost();
		ilUtil::sendFailure($error);
		$this->configure($form);
	}

	/* CREDENTIALS SECTION */
	/**
	 * Show credentials screen
	 * @param ilPropertyFormGUI $form
	 * @global $tpl
	 * @global $ilTabs
	 */
	protected function credentials(ilPropertyFormGUI $form = null)
	{
		global $tpl, $ilTabs;

		$ilTabs->activateTab('credentials');

		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initCredentialsForm();
		}

		$tpl->setContent($form->getHTML());
	}

	/**
	 * Init credentials form
	 * @global $ilCtrl
	 * @return ilPropertyFormGUI form
	 */
	protected function initCredentialsForm()
	{
		global $ilCtrl, $lng;

		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';

		/** @TODO  WORKING HERE */
		$settings = ilAfPSettings::getInstance();

		$form = new ilPropertyFormGUI();
		$form->setTitle($this->getPluginObject()->txt('tbl_afp_settings'));
		$form->setFormAction($ilCtrl->getFormAction($this));

		$form->addCommandButton('saveCredentials', $lng->txt('save'));
		$form->setShowTopButtons(false);

		$url = new ilTextInputGUI($this->getPluginObject()->txt('credentials_url'), 'resturl');
		$url->setRequired(true);
		$url->setSize(120);
		$url->setMaxLength(512);
		$url->setValue($settings->getRestUrl());
		$form->addItem($url);

		$user = new ilTextInputGUI($this->getPluginObject()->txt('credentials_user'),'restuser');
		$user->setRequired(true);
		$user->setSize(120);
		$user->setMaxLength(512);
		$user->setValue($settings->getRestUser());
		$form->addItem($user);

		$pass = new ilPasswordInputGUI($this->getPluginObject()->txt('credentials_password'), 'restpassword');
		$pass->setRequired(true);
		$pass->setRetype(false);
		$pass->setSize(120);
		$pass->setMaxLength(512);
		//$pass->setValue($settings->getRestPass());
		$form->addItem($pass);

		return $form;
	}

	protected function saveCredentials()
	{
		global $lng, $ilCtrl;

		$form = $this->initCredentialsForm();
		$settings = ilAfPSettings::getInstance();

		try
		{
			if($form->checkInput())
			{
				$settings->setRestUrl($form->getInput('resturl'));
				$settings->setRestUser($form->getInput('restuser'));
				$settings->setRestPassword($form->getInput('restpassword'));
				$settings->save();

				ilUtil::sendSuccess($lng->txt('settings_saved'),true);
				$ilCtrl->redirect($this,'credentials');
			}
			$error = $lng->txt('err_check_input');
		}
		catch(ilException $e)
		{
			$error = $e->getMessage();
			ilAfPLogger::getLogger()->write("saveCredentials() exception: ".$error);
		}
		$form->setValuesByPost();
		ilUtil::sendFailure($error);
		$this->credentials($form);
	}

}
