<?php

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
		$now = new ilDateTime(time(), IL_CAL_UNIX);

		parent::__construct(
			ilVEDAUserImporterSettings::getInstance()->getBackupDir(),
			$now->get(IL_CAL_FKT_DATE, 'Ymd_').'import.log',
			self::LOG_TAG
		);
	}

	public static function getLogger(): ilVEDAUserImporterLogger
	{
		if(self::$instance != null)
		{
			return self::$instance;
		}
		return self::$instance = new self();
	}

	public function write(string $a_message): void
	{
		$this->setLogFormat(date('[Y-m-d H:i:s] '));
		parent::write($a_message);
	}
}
?>
