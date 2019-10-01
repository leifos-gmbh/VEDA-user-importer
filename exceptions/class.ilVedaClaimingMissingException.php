<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Thrown if claiming plugin is not configure or available
 *
 * Class \ilVedaClaimingMissingException
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilVedaClaimingMissingException extends ilException
{
	public const ERR_MISSING = 1;

	private static $code_messages = [
		self::ERR_MISSING => 'err_claiming_missing'
	];

	/**
	 * ilVedaClaimingMissingException constructor.
	 * @param $a_message
	 * @param int $a_code
	 */
	public function __construct($a_message, int $a_code)
	{
		$message = static::translateExceptionCode($a_code);
		parent::__construct($message, $a_code);
	}


	/**
	 * @return string
	 */
	public function exceptionCodeToString()
	{
		return self::$code_messages[$this->getCode()];
	}

	/**
	 * @param int $code
	 * @return string
	 */
	public static function getMessageForCode(int $code)
	{
		return self::$code_messages[$code];
	}


	/**
	 * @param int $code
	 * @return string
	 */
	public static function translateExceptionCode(int $code) : string
	{
		$plugin = \ilVedaConnectorPlugin::getInstance();
		return $plugin->txt(self::getMessageForCode($code));
	}

}