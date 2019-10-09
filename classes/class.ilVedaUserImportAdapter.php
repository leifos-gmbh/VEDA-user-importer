<?php

use Swagger\Client\Model\TeilnehmerELearningPlattform;

/**
 * Class ilVedaUserImportAdapter
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilVedaUserImportAdapter
{
	private const AUTH_MODE = 'local';


	/**
	 * @var \ilLogger|null
	 */
	private $logger = null;

	/**
	 * @var \ilVedaConnectorSettings|null
	 */
	private $settings = null;

	/**
	 * @var \Swagger\Client\Model\TeilnehmerELearningPlattform[]
	 */
	private $participants = [];

	/**
	 * @var \ilXmlWriter|null
	 */
	private $writer = null;


	/**
	 * ilVedaUserImportAdapter constructor.
	 * @param TeilnehmerELearningPlattform[] $participants
	 */
	public function __construct(array $participants)
	{
		global $DIC;

		$this->logger = $DIC->logger()->vedaimp();
		$this->participants = $participants;
		$this->settings = \ilVedaConnectorSettings::getInstance();

		$this->writer = new \ilXmlWriter();
	}

	/**
	 * @return TeilnehmerELearningPlattform[]
	 */
	public function getParticipants()
	{
		return $this->participants;
	}


	/**
	 * Import users
	 * @throws \ilVedaUserImporterException
	 */
	public function import()
	{
		$this->transformParticipantsToXml();
		$this->importXml();
		$this->updateCreationFeedback();
	}


	/**
	 * Transform API participants to xml
	 * @throws \ilVedaUserImporterException
	 */
	protected function transformParticipantsToXml()
	{
		$this->writer->xmlStartTag('Users');

		$this->logger->info('Starting update of ' . count($this->getParticipants()) . ' participants. ');
		foreach($this->getParticipants() as $participant_container)
		{
			$usr_id = $this->fetchUserId($participant_container);

			if(!$this->validateParticipant($usr_id, $participant_container))
			{
				continue;
			}

			if($usr_id) {
				
				$new_login = '';
				if(!$this->updateLogin($usr_id, $participant_container, $new_login)) {
					continue;
				}

				$this->writer->xmlStartTag(
					'User',
					[
						'Id' => $usr_id,
						'Action' => 'Update',
						'ImportId' => $participant_container->getTeilnehmer()->getOid()
					]);

				$this->writer->xmlElement(
					'Login',
					[],
					$participant_container->getBenutzername()
				);
			}
			else {
				$this->writer->xmlStartTag('User',
					[
						'Action' => 'Insert',
						'ImportId' => $participant_container->getTeilnehmer()->getOid()
					]
				);
				$this->writer->xmlElement(
					'Login',
					[],
					$participant_container->getBenutzername()
				);
				$this->writer->xmlElement(
					'Password',
					[
						'Type' => 'PLAIN'
					],
					$participant_container->getInitialesPasswort()
				);
			}
			$this->writer->xmlElement(
				'Email',
				[],
				$participant_container->getEmail()
			);
			$this->writer->xmlElement(
				'Gender',
				[],
				strtolower($participant_container->getTeilnehmer()->getGeschlecht())
			);

			if($participant_container->getTeilnehmer()->getGeburtsdatum() instanceof DateTime) {
				$date_string = $participant_container->getTeilnehmer()->getGeburtsdatum()->format('Y-m-d');
				if($date_string) {
					$this->writer->xmlElement(
						'Birthday',
						[],
						$date_string
					);
				}
			}

			$this->writer->xmlElement(
				'AuthMode',
				[
					'type' => self::AUTH_MODE
				],
				null
			);
			$this->writer->xmlElement(
				'Active',
				[],
				$participant_container->getTeilnehmer()->getAktiv() ? 'true' : 'false'
				);

			$this->writer->xmlElement('TimeLimitOwner',[],USER_FOLDER_ID);
			$this->writer->xmlElement('TimeLimitUnlimited',[],1);
			$this->writer->xmlElement('TimeLimitFrom',[],time());
			$this->writer->xmlElement('TimeLimitUntil',[],time());

			// Role assignment
			$long_role_id = ('il_' . IL_INST_ID . '_role_'.$this->settings->getParticipantRole());
			$this->writer->xmlElement(
				'Role',
				[
					'Id' => $long_role_id,
					'Type' => 'Global',
					'Action' => 'Assign'
				],
				null
			);

			$this->writer->xmlElement('Firstname', [], $participant_container->getTeilnehmer()->getVorname());
			$this->writer->xmlElement('Lastname',[], $participant_container->getTeilnehmer()->getNachname());

			$this->writer->xmlEndTag('User');

			$this->storeUserStatusSuccess($participant_container, $usr_id);
		}

		$this->writer->xmlEndTag('Users');
	}

	/**
	 * import user xml
	 */
	protected function importXml()
	{
		$this->logger->info('Starting user update');
		$importParser = new \ilUserImportParser();
		$importParser->setUserMappingMode(IL_USER_MAPPING_ID);
		$importParser->setXMLContent($this->writer->xmlDumpMem(false));
		$importParser->setRoleAssignment(
			[
				$this->settings->getParticipantRole() => $this->settings->getParticipantRole()
			]
		);
		$importParser->setFolderId(USER_FOLDER_ID);
		$importParser->startParsing();
		$debug = $importParser->getProtocol();

		$this->logger->info('Finished update users, with protocol message.');
		$this->logger->dump($debug);
		$this->logger->debug($this->writer->xmlDumpMem(true));
	}

	/**
	 * udate creation feedback
	 */
	protected function updateCreationFeedback()
	{
		$pending_participants = \ilVedaUserStatus::getUsersWithPendingCreationStatus();
		foreach($pending_participants as $participant_status) {

			try {
				$this->logger->debug('Marked user with oid ' . $participant_status->getOid() . ' as imported.');
				$connector = \ilVedaConnector::getInstance();
				$connector->sendCreationMessage($participant_status->getOid());

				$participant_status->setCreationStatus(\ilVedaUserStatus::STATUS_SYNCHRONIZED);
				$this->logger->info('Update creation status');
				$participant_status->save();
			}
			catch(\ilVedaConnectionException $e) {

			}
		}
	}

	/**
	 * Fetch user id of already created user account
	 * @param \Swagger\Client\Model\TeilnehmerELearningPlattform $participant
	 * @return int
	 * @throws \ilVedaUserImporterException
	 */
	protected function fetchUserId(TeilnehmerELearningPlattform $participant)
	{
		$import_id = $participant->getTeilnehmer()->getOid();
		$obj_id = \ilObject::_lookupObjIdByImportId($import_id);
		if(!$obj_id)
		{
			return 0;
		}

		if(!ilObjUser::_exists($obj_id)) {
			$this->logger->error('Found invalid obj_data entry for import_id: ' . $import_id);
			throw new \ilVedaUserImporterException('Invalid db structure. Check log file. Aborting');
		}

		$user = \ilObjectFactory::getInstanceByObjId($obj_id, false);
		if(!$user instanceof \ilObjUser) {
			$this->logger->error('Found invalid obj_data entry for import_id: ' . $import_id);
			throw new \ilVedaUserImporterException('Invalid db structure. Check log file. Aborting');
		}
		return $user->getId();
	}

	/**
	 * @param int $usr_id
	 * @param \Swagger\Client\Model\TeilnehmerELearningPlattform $participant
	 * @return bool
	 */
	protected function validateParticipant(int $usr_id, TeilnehmerELearningPlattform $participant) : bool
	{
		if($usr_id) {
			$this->logger->debug('Existing usr_account with id: ' . $usr_id . ' is valid');
			return true;
		}
		// no usr_id given => usr is valid if login does not exist
		$login = $participant->getBenutzername();
		$generated_login = \ilAuthUtils::_generateLogin($login);

		if(strcmp($generated_login, $login) !== 0) {
			$this->logger->warning('User with login: ' . $login . ' already exists.');

			$user_status = new \ilVedaUserStatus($participant->getTeilnehmer()->getOid());
			$user_status->setLogin($participant->getBenutzername());
			$user_status->setCreationStatus(\ilVedaUserStatus::STATUS_NONE);
			$user_status->setPasswordStatus(\ilVedaUserStatus::STATUS_NONE);
			$user_status->setImportFailure(true);
			$user_status->save();
			return false;
		}
		return true;
	}

	/**
	 * @param int $usr_id
	 * @param \Swagger\Client\Model\TeilnehmerELearningPlattform $participant
	 * @param string $new_login
	 * @return bool
	 * @throws \ilDatabaseException
	 * @throws \ilObjectNotFoundException
	 */
	protected function updateLogin(int $usr_id, TeilnehmerELearningPlattform $participant, string &$new_login)
	{
		$user = \ilObjectFactory::getInstanceByObjId($usr_id, false);
		if(!$user instanceof \ilObjUser) {
			$this->logger->warning('Cannot find existing user with id: ' . $usr_id);
			return false;
		}

		$login = $participant->getBenutzername();
		if(strcmp($login,$user->getLogin()) === 0) {
			$this->logger->debug('User login name unchanged.');
			$new_login = $login;
			return true;
		}

		$generated_login = \ilAuthUtils::_generateLogin($login);
		if(strcmp($generated_login, $login) !== 0) {

			$this->logger->warning('User with login: ' . $login . ' already exists.');

			$user_status = new \ilVedaUserStatus($participant->getTeilnehmer()->getOid());
			$user_status->setLogin($participant->getBenutzername());
			$user_status->setImportFailure(true);
			$user_status->save();
			return false;
		}

		$new_login = $generated_login;
		return true;
	}

	/**
	 * @param \Swagger\Client\Model\TeilnehmerELearningPlattform $participant
	 */
	protected function storeUserStatusSuccess(TeilnehmerELearningPlattform $participant, int $usr_id)
	{
		$user_status = new \ilVedaUserStatus($participant->getTeilnehmer()->getOid());
		$user_status->setLogin($participant->getBenutzername());

		if(!$usr_id) {
			$user_status->setCreationStatus(\ilVedaUserStatus::STATUS_PENDING);
			$user_status->setPasswordStatus(\ilVedaUserStatus::STATUS_PENDING);
		}
		$user_status->setImportFailure(false);
		$user_status->save();
	}


}