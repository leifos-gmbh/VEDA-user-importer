<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Table GUI for user import results
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilVedaUserImportResultTableGUI extends \ilTable2GUI
{
	/**
	 * @var \ilVedaConnectorPlugin|null
	 */
	private $plugin = null;


	/**
	 * ilVedaUserImportResultTableGUI constructor.
	 * @param object $class
	 * @param string $method
	 */
	public function __construct($class, string $method)
	{
		$this->plugin = \ilVedaConnectorPlugin::getInstance();


		$this->setId('vedaimp_res_usr');
		parent::__construct($class, $method);
	}


	/**
	 * init table
	 */
	public function init()
	{
		$this->setTitle($this->plugin->txt('tbl_import_result_usr'));
		$this->setFormAction($this->getFormAction($this->getParentObject(), $this->getParentCmd()));

		$this->setRowTemplate(
			'tpl.usr_result_row.html',
			$this->plugin->getDirectory()
		);

		$this->addColumn(
			$this->lng->txt('username'),
			'login'
		);
		$this->addColumn(
			$this->plugin->txt('tbl_usr_result_created'),
			'created'
		);
		$this->addColumn(
			$this->plugin->txt('tbl_usr_result_pwd_changed'),
			'pwd'
		);
		$this->addColumn(
			$this->plugin->txt('tbl_usr_result_import_failure'),
			'failure'
		);
        $this->addColumn(
            $this->lng->txt('actions'),
            ''
        );

	}

	/**
	 * Parse imported user data
	 * @throws \ilDatabaseException
	 */
	public function parse()
	{
		$users = \ilVedaUserStatus::getAllUsers();
		$rows = [];
		foreach($users as $user) {

			$row = [];
			$row['login'] = $user->getLogin();
			$row['oid'] = $user->getOid();
			$row['created'] = $user->getCreationStatus();
			$row['pwd'] = $user->getPasswordStatus();
			$row['failure'] = $user->isImportFailure();

			$rows[] = $row;
		}
		$this->setData($rows);
	}

	public function fillRow($row)
	{
		global $DIC;

		$this->tpl->setVariable('TXT_LOGIN', $row['login']);
		$this->tpl->setVariable('OID', $row['oid']);


		$this->tpl->setVariable('CREATED_IMG',
			$row['created']  == \ilVedaUserStatus::STATUS_SYNCHRONIZED ?
				ilUtil::getImagePath('icon_ok.svg') :
				ilUtil::getImagePath('icon_not_ok.svg')
		);
		$this->tpl->setVariable('PWD_IMG',
			$row['pwd']  == \ilVedaUserStatus::STATUS_SYNCHRONIZED ?
				ilUtil::getImagePath('icon_ok.svg') :
				ilUtil::getImagePath('icon_not_ok.svg')
		);

		if($row['failure']) {
			$this->tpl->setVariable('FAILURE_TXT', $this->plugin->txt('err_import_usr_duplicate'));
            $list = new ilAdvancedSelectionListGUI();
            $list->setId('veda_oid_' . $row['login'] . '_' . $row['oid']);
            $list->setListTitle($this->lng->txt('actions'));

            $this->ctrl->setParameter(
                $this->getParentObject(),
                'oid',
                $row['oid']
            );
            $this->ctrl->setParameter(
                $this->getParentObject(),
                'login',
                urlencode($row['login'])
            );
            $list->addItem(
                ilVedaConnectorPlugin::getInstance()->txt('migrate_user'),
                '',
                $this->ctrl->getLinkTarget(
                    $this->getParentObject(),
                    'migrateUser'
                )
            );
            $this->tpl->setVariable('SELECTION', $list->getHTML());
		}
	}

}