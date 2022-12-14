<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use GuzzleHttp\Client;
use Swagger\Client\Api\AusbildungsgngeApi;
use Swagger\Client\ApiException;
use Swagger\Client\Configuration;
use Swagger\Client\Api\ELearningPlattformenApi;
use Swagger\Client\Model\Ausbildungszug;

/**
 * Search anf find veda specific md data
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilVedaMDHelper
{
	/**
	 * @var null | \ilVedaMDHelper
	 */
	public static $instance = null;

	/**
	 * @var null | \ilVedaConnectorPlugin
	 */
	private $plugin = null;

	/**
	 * @var null | \ilVedaMDClaimingPlugin
	 */
	private $claiming = null;

	/**
	 * @var null | \ilVedaConnectorSettings
	 */
	private $settings = null;


	/**
	 * @var null | \ilDBInterface
	 */
	private $db = null;

	/**
	 * @var null | \ilLogger
	 */
	private $logger = null;


	/**
	 * ilVedaMDHelper constructor.
	 */
	public function __construct()
	{
		global $DIC;

		$this->plugin = \ilVedaConnectorPlugin::getInstance();
		$this->claiming = $this->plugin->getClaimingPlugin();
		$this->logger = $this->plugin->getLogger();
		$this->db = $DIC->database();
	}

	/**
	 * @return \ilVedaMDHelper
	 */
	public static function getInstance()
	{
		if(!self::$instance instanceof \ilVedaMDHelper) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * @param int $source_id
	 * @param int $target_id
	 * @param \Swagger\Client\Model\Ausbildungszug $train
	 */
	public function migrateTrainingCourseToTrain(int $source_id, int $target_id, Ausbildungszug $train)
	{
		$this->deleteTrainingCourseId($target_id);
		$this->deleteTrainingCourseTrainId($target_id);

		$tc_oid = $train->getOid();
		$this->writeTrainingCourseTrainId($target_id, $tc_oid);

		$course_status = new \ilVedaCourseStatus($train->getOid());
		$course_status->setType(\ilVedaCourseStatus::TYPE_SIFA);
		$course_status->setModified(time());
		$course_status->setCreationStatus(\ilVedaCourseStatus::STATUS_PENDING);
		$course_status->save();


	}

	/**
	 * @param int $source_id
	 * @param int $target_id
	 * @param \Swagger\Client\Model\Ausbildungszug $train
	 */
	public function migrateTrainingCourseSegmentToTrain(int $source_id, int $target_id , Ausbildungszug $train, ?string $training_course_id)
	{
		$this->deleteTrainingCourseSegmentId($target_id);
		$this->deleteTrainingCourseSegmentTrainId($target_id);

		$course_segment_id = $this->findSegmentId($source_id);
		$segment_train_id = '';
		foreach($train->getAusbildungszugabschnitte() as $abschnitt) {

		    if (\ilVedaUtils::compareOidsEqual($abschnitt->getAusbildungsgangabschnittId(), $course_segment_id)) {
				$segment_train_id = $abschnitt->getOid();
				$this->writeTrainingCourseSegmentTrainId($target_id, $abschnitt->getOid());
				break;
			}
		}

		try {
			$connector = \ilVedaConnector::getInstance();
			$training_course = $connector->getTrainingCourseSegments($training_course_id);
			foreach($training_course->getAusbildungsgangabschnitte() as $training_course_segment) {
			    if (\ilVedaUtils::compareOidsEqual($training_course_segment->getOid(),$course_segment_id)) {
					$segment_info = new \ilVedaSegmentInfo($segment_train_id, $training_course_segment->getAusbildungsgangabschnittsart());
					$segment_info->update();
				}
			}
		}
		catch(Exception $e) {
			$this->logger->error('Update of getAusbildungsgangabschnittsart failed with message: ' . $e->getMessage());
		}
	}

	/**
	 * @param $target_id
	 * @param $tc_oid
	 */
	protected function writeTrainingCourseSegmentTrainId(int $target_id,string $tc_oid)
	{
		$obj_id = \ilObject::_lookupObjId($target_id);
		$fields = $this->claiming->getFields();

		$query = 'insert into adv_md_values_ltext (obj_id, field_id, value, disabled) ' .
			'values ( '.
			$this->db->quote($obj_id, \ilDBConstants::T_INTEGER). ', '.
			$this->db->quote($fields[\ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSZUGABSCHNITT], \ilDBConstants::T_TEXT). ', ' .
			$this->db->quote($tc_oid, \ilDBConstants::T_TEXT) . ', '.
			$this->db->quote(1, \ilDBConstants::T_INTEGER) .
			')';
		$this->db->manipulate($query);
	}

	/**
	 * @param $target_id
	 * @param $tc_oid
	 */
	protected function writeTrainingCourseTrainId(int $target_id,string $tc_oid)
	{
		$obj_id = \ilObject::_lookupObjId($target_id);
		$fields = $this->claiming->getFields();

		$query = 'insert into adv_md_values_ltext (obj_id, field_id, value, disabled) ' .
			'values ( '.
			$this->db->quote($obj_id, \ilDBConstants::T_INTEGER). ', '.
			$this->db->quote($fields[\ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSZUG], \ilDBConstants::T_TEXT). ', ' .
			$this->db->quote($tc_oid, \ilDBConstants::T_TEXT) . ', '.
			$this->db->quote(1, \ilDBConstants::T_INTEGER) .
			')';

		$this->logger->debug($query);

		$this->db->manipulate($query);
	}

	/**
	 * @param string $id
	 */
	protected function deleteTrainingCourseSegmentId(int $ref_id)
	{
		$obj_id = \ilObject::_lookupObjId($ref_id);
		$fields = $this->claiming->getFields();
		$query = 'delete from adv_md_values_ltext ' .
			'where field_id = ' . $this->db->quote(
				$fields[\ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSGANGABSCHNITT],
				\ilDBConstants::T_INTEGER). ' ' .
			'and obj_id = ' . $this->db->quote($obj_id, \ilDBConstants::T_INTEGER);
		$this->db->manipulate($query);
	}

	/**
	 * @param string $id
	 */
	protected function deleteTrainingCourseId(int $ref_id)
	{
		$obj_id = \ilObject::_lookupObjId($ref_id);
		$fields = $this->claiming->getFields();
		$query = 'delete from adv_md_values_ltext ' .
			'where field_id = ' . $this->db->quote(
				$fields[\ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSGANG],
				\ilDBConstants::T_INTEGER). ' ' .
			'and obj_id = ' . $this->db->quote($obj_id, \ilDBConstants::T_INTEGER);

		$this->db->manipulate($query);
	}

	/**
	 * @param string $id
	 */
	protected function deleteTrainingCourseTrainId(int $ref_id)
	{
		$obj_id = \ilObject::_lookupObjId($ref_id);
		$fields = $this->claiming->getFields();
		$query = 'delete from adv_md_values_ltext ' .
			'where field_id = ' . $this->db->quote(
				$fields[\ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSZUG],
				\ilDBConstants::T_INTEGER) . ' ' .
			'and obj_id = ' . $this->db->quote($obj_id, \ilDBConstants::T_INTEGER);
		$this->db->manipulate($query);
	}

	/**
	 * @param string $id
	 */
	protected function deleteTrainingCourseSegmentTrainId(int $ref_id)
	{
		$obj_id = \ilObject::_lookupObjId($ref_id);
		$fields = $this->claiming->getFields();
		$query = 'delete from adv_md_values_ltext ' .
			'where field_id = ' . $this->db->quote(
				$fields[\ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSZUGABSCHNITT],
				\ilDBConstants::T_INTEGER) . ' ' .
			'and obj_id = ' . $this->db->quote($obj_id, \ilDBConstants::T_INTEGER);
		$this->db->manipulate($query);
	}


	/**
	 * @param int $ref_id
	 * @return string
	 */
	public function findTrainingCourseId(int $ref_id) : string
	{
		$obj_id = \ilObject::_lookupObjId($ref_id);
		$fields = $this->claiming->getFields();

		$query = 'select value from adv_md_values_ltext ' .
			'where field_id = ' . $this->db->quote(
				$fields[\ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSGANG],
				\ilDBConstants::T_INTEGER) . ' '.
			'and obj_id = ' . $this->db->quote($obj_id , \ilDBConstants::T_INTEGER);
		$res = $this->db->query($query);
        $this->logger->dump($query);
		while($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
			return $row->value;
		}
		return '';
	}

	/**
	 * Get all "Ausbildungszüge"
	 * @return string[]
	 */
	public function findTrainingCourseTrains()
	{
		$fields = $this->claiming->getFields();

		$query = 'select value from adv_md_values_ltext ' .
			'where field_id = ' . $this->db->quote(
				$fields[\ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSZUG],
				\ilDBConstants::T_INTEGER);
		$res = $this->db->query($query);

		$oids = [];
		while($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
            if (!in_array($row->value, $oids)) {
                $oids[] = $row->value;
            }
		}
		return $oids;
	}

	/**
	 * @param int $oid
	 * @return int
	 * @throws \ilDatabaseException
	 * @throws \ilObjectNotFoundException
	 */
	public function findTrainingCourseTrain(?string $oid)
	{
		$fields = $this->claiming->getFields();

		$query = 'select obj_id from adv_md_values_ltext ' .
			'where field_id = ' . $this->db->quote(
				$fields[\ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSZUG],
				\ilDBConstants::T_INTEGER) . ' ' .
			'and value = ' . $this->db->quote($oid, \ilDBConstants::T_TEXT);
		$res = $this->db->query($query);

		$ref_id = 0;
		while($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
			// find ref_id
			$refs = \ilObject::_getAllReferences($row->obj_id);
			$ref = end($refs);
			$object = \ilObjectFactory::getInstanceByRefId($ref, false);
			if(!$object instanceof \ilObjCourse) {
				$this->logger->error('Found invalid "Ausbildungszug" with obj_id: ' . $row->obj_id);
				continue;
			}
			return $object->getRefId();
		}
		return 0;
	}

	/**
	 * @return array
	 * @throws \ilDatabaseException
	 */
	public function findTrainingCourseTemplates() : array
	{
		global $DIC;

		$tree = $DIC->repositoryTree();
		$fields = $this->claiming->getFields();

		$query = 'select obj_id from adv_md_values_ltext ' . ' ' .
			'where field_id = ' . $this->db->quote(
				$fields[\ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSGANG],
				\ilDBConstants::T_INTEGER) . ' ';
		$res = $this->db->query($query);

		$template_references = [];
        $obj_ids = [];
		while($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {

            if (in_array($row->obj_id, $obj_ids)) {
                continue;
            }
            $obj_ids[] = $row->obj_id;

			// find ref_id
			$refs = \ilObject::_getAllReferences($row->obj_id);
			$ref = end($refs);

			$object = \ilObjectFactory::getInstanceByRefId($ref, false);
			if(!$object instanceof \ilObjCourse) {

				$this->logger->error('Found invalid "Ausbildungsgang" with obj_id: ' . $row->obj_id);
				continue;
			}
			if($tree->isDeleted($object->getRefId())) {
				$this->logger->notice('Ignoring deleted course with obj_id: ' . $row->obj_id);
				continue;
			}
			$template_references[] = $object->getRefId();
		}

		return $template_references;
	}

	/**
	 * @param int $ref_id
	 * @return string
	 * @throws \ilDatabaseException
	 */
	public function findSegmentId(int $ref_id) : string
	{
		$obj_id = \ilObject::_lookupObjId($ref_id);

		$fields = $this->claiming->getFields();

		$query = 'select value from adv_md_values_ltext ' .
			'where field_id = ' . $this->db->quote($fields[\ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSGANGABSCHNITT], \ilDBConstants::T_INTEGER) . ' '.
			'and obj_id = ' . $this->db->quote($obj_id , \ilDBConstants::T_INTEGER);
		$res = $this->db->query($query);
		while($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
			return $row->value;
		}
		return '';

	}

	/**
	 * @param int $ref_id
	 * @return string
	 */
	public function findTrainSegmentId(int $ref_id) : string
	{
		$obj_id = \ilObject::_lookupObjId($ref_id);
		$fields = $this->claiming->getFields();

		$query = 'select value from adv_md_values_ltext ' .
			'where field_id = ' . $this->db->quote(
				$fields[\ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSZUGABSCHNITT],
				\ilDBConstants::T_INTEGER) . ' '.
			'and obj_id = ' . $this->db->quote(
				$obj_id ,
				\ilDBConstants::T_INTEGER);
		$res = $this->db->query($query);
		while($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
			return $row->value;
		}
		return '';

	}


}
