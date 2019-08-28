<?php

class ilVedaImporter
{
	public const IMPORT_USR = 'usr';
	public const IMPORT_CRS = 'crs';
	public const IMPORT_MEM = 'mem';

	public const IMPORT_ALL = 1;
	public const IMPORT_SELECTED = 2;

	/**
	 * @var \ilVedaImporter
	 */
	private static $instance = null;

	/**
	 * @var \ilLogger|null
	 */
	private $logger = null;

	/**
	 * @var \ilVedaConnectorSettings|null
	 */
	private $settings = null;

	/**
	 * @var string[]
	 */
	private $import_modes = [];

	/**
	 * ilVedaImporter constructor.
	 */
	public function __construct()
	{
		global $DIC;

		$this->logger = $DIC->logger()->vedaimp();
		$this->settings = \ilVedaConnectorSettings::getInstance();
	}

	/**
	 * @return \ilVedaImporter
	 */
	public static function getInstance() : \ilVedaImporter
	{
		if(!self::$instance instanceof \ilVedaImporter) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @param bool $all
	 * @param array $types
	 */
	public function setImportMode(bool $all, array $types = null)
	{
		if($all) {
			$this->import_modes = [
				self::IMPORT_USR,
				self::IMPORT_CRS,
				self::IMPORT_MEM,
			];
		}
		elseif(is_array($types)) {
			$this->import_modes = $types;
		}
	}

	/**
	 * @param string $mode
	 * @return bool
	 */
	protected function isImportModeEnabled(string $mode)
	{
		return in_array($mode, $this->import_modes);
	}

	/**
	 * Import selected types
	 *
	 * @throws \ilVedaImporterLockedException
	 * @throws \ilVedaConnectionException
	 */
	public function import()
	{
		if($this->settings->isLocked()) {
			throw new \ilVedaImporterLockedException();
		}

		$this->logger->info('Settings import lock');
		$this->settings->enableLock(true);
		$this->settings->save();

		try {
			if($this->isImportModeEnabled(self::IMPORT_USR)) {
					$this->importUsers();
			}
		}
		catch (ilVedaConnectionException $e) {
			throw $e;
		}

		// no error release lock
		$this->logger->info('Releasing import lock');
		$this->settings->enableLock(false);
		$this->settings->save();
	}

	/**
	 *
	 * @throws \ilVedaConnectionException
	 */
	protected function importUsers()
	{
		try {
			$connector = \ilVedaConnector::getInstance();
			$participants = $connector->getParticipants();

			$this->logger->dump($participants, \ilLogLevel::DEBUG);

		}
		catch (ilVedaConnectionException $e) {
			throw $e;
		}
	}
}