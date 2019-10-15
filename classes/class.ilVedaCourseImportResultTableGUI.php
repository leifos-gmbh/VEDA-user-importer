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
		$courses = \ilVedaCourseStatus::getAllCourses();

		$rows = [];
		foreach($courses as $course) {

			$row = [];
			$row['obj_id'] = $course->getObjId();
			$row['title'] = \ilObject::_lookupTitle($course->getObjId());
			$row['oid'] = $course->getOid();
			$row['created'] = $course->getCreationStatus();
			$row['pswitch'] = '';
			if($course->getPermanentSwitchRole()) {
				$row['pswitch'] = \ilObject::_lookupTitle($course->getPermanentSwitchRole());
			}
			$row['tswitch'] = '';
			if($course->getTemporarySwitchRole()) {
				$row['tswitch'] = \ilObject::_lookupTitle($course->getTemporarySwitchRole());
			}
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

		$this->tpl->setVariable('TXT_TITLE', $row['title']);
		$this->tpl->setVariable('OID', $row['oid']);
	}
}