<?php

/**
 * @author Jesus Lopez <lopez@leifos.com>
 */
class ilVEDAUserImporterConfigGUI extends ilPluginConfigGUI
{
	/**
	 * Handles all commmands, default is "configure"
	 */
	public function performCommand($cmd): void
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
	 * @inheritdoc
	 * Show settings screen
	 */
	protected function configure(ilPropertyFormGUI $form = null): void
	{
		global $tpl, $ilTabs;

		$ilTabs->activateTab('settings');

		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initConfigurationForm();
		}
		$tpl->setContent($form->getHTML());
	}

	protected function initConfigurationForm(): ilPropertyFormGUI
	{
		global $ilCtrl, $lng;

		$settings = ilVEDAUserImporterSettings::getInstance();

		$form = new ilPropertyFormGUI();
		$form->setTitle($this->getPluginObject()->txt('tbl_settings'));
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->addCommandButton('save', $lng->txt('save'));
		$form->setShowTopButtons(false);

		$lock = new ilCheckboxInputGUI($this->getPluginObject()->txt('tbl_settting_lock'),'lock');
		$lock->setValue(1);
		$lock->setDisabled(!$settings->isLocked());
		$lock->setChecked($settings->isLocked());
		$form->addItem($lock);

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

	protected function save(): void
	{
		global $lng, $ilCtrl;

		$form = $this->initConfigurationForm();
		$settings = ilVEDAUserImporterSettings::getInstance();

		try
		{
			if($form->checkInput())
			{
				$settings->enableLock($form->getInput('lock'));
				$settings->setCronInterval($form->getInput('cron_interval'));
				$settings->save();

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

	/**
	 * Show credentials screen
	 */
	protected function credentials(ilPropertyFormGUI $form = null): void
	{
		global $tpl, $ilTabs;

		$ilTabs->activateTab('credentials');

		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initCredentialsForm();
		}

		$tpl->setContent($form->getHTML());
	}

	protected function initCredentialsForm(): ilPropertyFormGUI
	{
		global $ilCtrl, $lng;

		$settings = ilVEDAUserImporterSettings::getInstance();

		$form = new ilPropertyFormGUI();
		$form->setTitle($this->getPluginObject()->txt('tbl_settings'));
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

		$platform_id = new ilNumberInputGUI($this->getPluginObject()->txt('platform_id'),'restplatformid');
		$platform_id->setRequired(true);
		$platform_id->setMaxLength(6);
		$platform_id->setValue($settings->getPlatformId());
		$platform_id->setInfo($this->getPluginObject()->txt('platform_id_info'));
		$form->addItem($platform_id);

		$pass = new ilPasswordInputGUI($this->getPluginObject()->txt('credentials_password'), 'restpassword');
		$pass->setRequired(true);
		$pass->setRetype(false);
		$pass->setSize(120);
		$pass->setMaxLength(512);
		//$pass->setValue($settings->getRestPassword());
		$pass->setInfo($this->getPluginObject()->txt("credentials_password_info"));
		$form->addItem($pass);

		return $form;
	}

	protected function saveCredentials(): void
	{
		global $lng, $ilCtrl;

		$form = $this->initCredentialsForm();
		$settings = ilVEDAUserImporterSettings::getInstance();

		try
		{
			if($form->checkInput())
			{
				$settings->setRestUrl($form->getInput('resturl'));
				$settings->setRestUser($form->getInput('restuser'));
				$settings->setRestPassword($form->getInput('restpassword'));
				$settings->setPlatformId($form->getInput('restplatformid'));
				$settings->save();

				ilUtil::sendSuccess($lng->txt('settings_saved'),true);
				$ilCtrl->redirect($this,'credentials');
			}
			$error = $lng->txt('err_check_input');
		}
		catch(ilException $e)
		{
			$error = $e->getMessage();
			ilVEDAUserImporterLogger::getLogger()->write("saveCredentials() exception: ".$error);
		}
		$form->setValuesByPost();
		ilUtil::sendFailure($error);
		$this->credentials($form);
	}

}
