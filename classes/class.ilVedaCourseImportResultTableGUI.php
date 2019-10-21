<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Table GUI for course import results
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilVedaCourseImportResultTableGUI extends \ilTable2GUI
{
	/**
	 * ilVedaCourseImportResultTableGUI constructor.
	 * @param $class
	 * @param string $method
	 */
	public function __construct($class, string $method)
	{
		$this->plugin = \ilVedaConnectorPlugin::getInstance();


		$this->setId('vedaimp_res_crs');
		parent::__construct($class, $method);
	}


	/**
	 * init table
	 */
	public function init()
	{
		$this->setTitle($this->plugin->txt('tbl_import_result_crs'));
		$this->setFormAction($this->getFormAction($this->getParentObject(), $this->getParentCmd()));

		$this->setRowTemplate(
			'tpl.crs_result_row.html',
			$this->plugin->getDirectory()
		);

		$this->addColumn(
			$this->lng->txt('title'),
			'title'
		);
		$this->addColumn(
			$this->plugin->txt('tbl_crs_result_created'),
			'created'
		);
		$this->addColumn(
			$this->plugin->txt('tbl_crs_result_pswitch'),
			'pswitch'
		);
		$this->addColumn(
			$this->plugin->txt('tbl_crs_result_tswitch'),
			'tswitch'
		);
	}

	/**
	 * Parse imported user data
	 * @throws \ilDatabaseException
	 */
	public function parse()
	{
		global $DIC;

		$tree = $DIC->repositoryTree();

		$courses = \ilVedaCourseStatus::getAllCourses();

		$rows = [];
		foreach($courses as $course) {

			$row = [];

			$row['obj_id'] = $course->getObjId();
			$row['title'] = \ilObject::_lookupTitle($course->getObjId());
			$row['oid'] = $course->getOid();
			$row['created'] = $course->getCreationStatus();
			$row['pswitch'] = $course->getPermanentSwitchRole();
			$row['tswitch'] = $course->getTemporarySwitchRole();
			$rows[] = $row;
		}
		$this->setData($rows);
	}

	/**
	 * @inheritdoc
	 */
	public function fillRow($row)
	{
		global $DIC;

		$tree = $DIC->repositoryTree();

		$obj_id = $row['obj_id'];
		$refs = \ilObject::_getAllReferences($obj_id);
		$ref = end($refs);
		if(!$ref || $tree->isDeleted($ref)) {
			$this->tpl->setCurrentBlock('is_deleted');
			$this->tpl->setVariable('TXT_DELETED', $this->lng->txt('deleted'));
			$this->tpl->parseCurrentBlock();
		}
		else {
			$link = \ilLink::_getLink($ref);
			$this->tpl->setCurrentBlock('with_title');
			$this->tpl->setVariable('TITLE_LINK',$link);
			$this->tpl->setVariable('TXT_TITLE', \ilObject::_lookupTitle($obj_id));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable('CREATED_IMG',
			$row['created']  == \ilVedaCourseStatus::STATUS_SYNCHRONIZED ?
				ilUtil::getImagePath('icon_ok.svg') :
				ilUtil::getImagePath('icon_not_ok.svg')
		);

		if(\ilObject::_exists($row['tswitch'])) {
			$this->tpl->setVariable('TXT_TAVAILABLE', $this->plugin->txt('role_available'));
		}
		else {
			$this->tpl->setVariable('TXT_TAVAILABLE', $this->plugin->txt('role_unavailable'));

		}
		if(\ilObject::_exists($row['pswitch'])) {
			$this->tpl->setVariable('TXT_PAVAILABLE', $this->plugin->txt('role_available'));
		}
		else {
			$this->tpl->setVariable('TXT_PAVAILABLE', $this->plugin->txt('role_unavailable'));

		}



		$this->tpl->setVariable('OID', $row['oid']);
	}
}