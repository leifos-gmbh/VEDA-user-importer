<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use GuzzleHttp\Client;
use Swagger\Client\Api\AusbildungsgngeApi;
use Swagger\Client\ApiException;
use Swagger\Client\Configuration;
use Swagger\Client\Api\ELearningPlattformenApi;

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
	 * @param int $ref_id
	 * @return string
	 */
	public function findTrainingCourseId(int $ref_id) : string
	{
		$obj_id = \ilObject::_lookupObjId($ref_id);

		$fields = $this->claiming->getFields();

		$query = 'select value from adv_md_values_text ' .
			'where field_id = ' . $this->db->quote($fields[\ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSGANG], \ilDBConstants::T_INTEGER) . ' '.
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
	 * @throws \ilDatabaseException
	 */
	public function findSegmentId(int $ref_id) : string
	{
		$obj_id = \ilObject::_lookupObjId($ref_id);

		$fields = $this->claiming->getFields();

		$query = 'select value from adv_md_values_text ' .
			'where field_id = ' . $this->db->quote($fields[\ilVedaMDClaimingPlugin::FIELD_AUSBILDUNGSGANGABSCHNITT], \ilDBConstants::T_INTEGER) . ' '.
			'and obj_id = ' . $this->db->quote($obj_id , \ilDBConstants::T_INTEGER);
		$res = $this->db->query($query);
		while($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
			return $row->value;
		}
		return '';

	}


}
