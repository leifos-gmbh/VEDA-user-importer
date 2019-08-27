<?php

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilVedaConnectorConfigGUI extends ilPluginConfigGUI
{
	const TAB_SETTINGS = 'settings';
	const TAB_CREDENTIALS = 'credentials';

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
	 * @inheritdoc
	 */
	public function performCommand($cmd)
	{
		global $DIC;

		$ilCtrl = $DIC->ctrl();
		$ilTabs = $DIC->tabs();

		$ilTabs->addTab(
			self::TAB_SETTINGS,
			\ilVedaConnectorPlugin::getInstance()->txt('tab_settings'),
			$ilCtrl->getLinkTarget($this, 'configure')
		);

		$ilTabs->addTab(
			self::TAB_CREDENTIALS,
			\ilVedaConnectorPlugin::getInstance()->txt('tab_credentials'),
			$ilCtrl->getLinkTarget($this, 'credentials')
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

		$settings = \ilVedaConnectorSettings::getInstance();

		$form = new \ilPropertyFormGUI();
		$form->setTitle($this->getPluginObject()->txt('tbl_settings'));
		$form->setFormAction($ctrl->getFormAction($this));
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
				$settings->enableLock($form->getInput('lock'));
				$settings->setCronInterval($form->getInput('cron_interval'));
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

		$tabs->activateTab(self::TAB_CREDENTIALS);

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
		$pass->setInfo($this->getPluginObject()->txt("credentials_password_info"));
		$form->addItem($pass);

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
				$settings->setPlatformId($form->getInput('restplatformid'));
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

	protected function ping()
	{
		//TODO implement this method, call the
		/**
		 * ..
		 * ...
		 * ...
		 * $selector = new ilOpenTextAuthHeaderSelector();

		$config = new \Swagger\Client\Configuration();
		$config->setHost($settings->getUri());
		$config->setUsername($settings->getUsername());
		$config->setPassword($settings->getPassword());

		$api = new \Swagger\Client\Api\DefaultApi(
		null,
		$config,
		$selector
		);

		$res = $api->apiV1AuthPostWithHttpInfo(
		$settings->getUsername(),
		$settings->getPassword(),
		$settings->getDomain()
		);
		 * ...
		 * ....
		 * ...
		 */
	}

}
