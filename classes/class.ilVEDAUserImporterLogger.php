<?php

include_once './Services/Logging/classes/class.ilLog.php';

/**
 * Logger class
 *
 * @author Jesus Lopez <lopez@leifos.de>
 */
class ilVEDAUserImporterLogger extends ilLog
{
	const LOG_TAG = 'vedaimp_import';

	protected static $instance = null;

	protected function __construct()
	{
		include_once './Services/Calendar/classes/class.ilDateTime.php';
		$now = new ilDateTime(time(), IL_CAL_UNIX);

		parent::__construct(
			ilVEDAUserImporterSettings::getInstance()->getBackupDir(),
			$now->get(IL_CAL_FKT_DATE, 'Ymd_').'import.log',
			self::LOG_TAG
		);


	}
	/**
	 * Get logger
	 * @return ilFhoevLogger
	 */
	public static function getLogger()
	{
		if(self::$instance != null)
		{
			return self::$instance;
		}
		return self::$instance = new self();
	}


	/**
	 * Write message
	 * @param type $a_message
	 */
	public function write($a_message)
	{
		$this->setLogFormat(date('[Y-m-d H:i:s] '));
		parent::write($a_message);
	}
}
?>
