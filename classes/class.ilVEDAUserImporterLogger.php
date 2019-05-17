<?php

/**
 * Logger class
 *
 * @author Jesus Lopez <lopez@leifos.de>
 */
class ilVEDAUserImporterLogger extends ilLog
{
	const LOG_TAG = 'vedaimp_import';
	const LOG_DIR = ilVEDAUserImporterPlugin::PLUGIN_DIR."/log";

	protected static $instance = null;

	protected function __construct()
	{
		$now = new ilDateTime(time(), IL_CAL_UNIX);

		parent::__construct(
			self::LOG_DIR,
			$now->get(IL_CAL_FKT_DATE,'Ymd_').'import.log',
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
