<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Thrown on connection failures.
 *
 * Class \ilVedaConnectorException
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilVedaConnectionException extends ilException
{
	public const ERR_LOGIN_FAILED = 1;
	public const ERR_API = 2;

	private static $code_messages = [
		self::ERR_LOGIN_FAILED => 'exception_login_failed',
		self::ERR_API => 'exception_api_call'
	];


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
		$plugin = ilVedaConnectorPlugin::getInstance();
		return $plugin->txt(self::exceptionCodeToString($code));
	}

}