<?php

use OpenApi\Client\Model\Adresse;
use OpenApi\Client\Model\Organisation;
use OpenApi\Client\Model\TeilnehmerELearningPlattform;

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilVedaUserImportAdapter
{
    /**
     * @var string
     */
    protected const AUTH_MODE = 'local';
    /**
     * @var string
     */
    protected const ERR_LOGIN_EXIST_MSG = 'Ein ILIAS-Benutzerkonto mit dem Namen %s existiert bereits.';

    protected ilLogger $logger;
    protected ilVedaConnectorSettings $settings;
    /**
     * @var Organisation[]
     */
    protected $organisations = [];
    protected ilXmlWriter $writer;
    protected ilVedaConnector $veda_connector;
    protected ilVedaUserRepositoryInterface $usr_repo;
    protected ilVedaRepositoryContentBuilderFactoryInterface $repo_content_builder_factory;

    public function __construct(
        ilLogger $veda_logger,
        ilVedaConnectorSettings $veda_settings,
        ilVedaUserRepositoryInterface $usr_repo,
        ilVedaConnector $veda_connector,
        ilVedaRepositoryContentBuilderFactoryInterface $repo_content_builder_factory
    ) {
        $this->logger = $veda_logger;
        $this->settings = $veda_settings;
        $this->writer = new \ilXmlWriter();
        $this->usr_repo = $usr_repo;
        $this->veda_connector = $veda_connector;
        $this->repo_content_builder_factory = $repo_content_builder_factory;
    }

    /**
     * @throws ilVedaUserImporterException
     */
    public function import(ilVedaELearningParticipantsCollectionInterface $participants) : void
    {
        $this->transformParticipantsToXml($participants);
        $this->importXml();
        $this->updateCreationFeedback();
    }

    /**
     * Transform API participants to xml
     * @throws ilVedaUserImporterException
     */
    protected function transformParticipantsToXml(ilVedaELearningParticipantsCollectionInterface $participants) : void
    {
        $this->writer->xmlStartTag('Users');

        $this->logger->info('Starting update of ' . count($participants) . ' participants. ');
        foreach ($participants as $participant_container) {
            $usr_id = $this->fetchUserId($participant_container);

            if (!$this->validateParticipant($usr_id, $participant_container)) {
                continue;
            }

            $user = null;
            if ($usr_id) {
                try {
                    $user = \ilObjectFactory::getInstanceByObjId($usr_id);
                } catch (\ilObjectNotFoundException $e) {
                    $this->logger->warning('Cannot create user instance for: ' . $usr_id);
                    continue;
                }

                $new_login = '';
                if (!$this->updateLogin($usr_id, $participant_container, $new_login)) {
                    continue;
                }

                $this->writer->xmlStartTag(
                    'User',
                    [
                        'Id' => $usr_id,
                        'Action' => 'Update',
                        'ImportId' => $participant_container->getTeilnehmer()->getOid()
                    ]
                );

                $this->writer->xmlElement(
                    'Login',
                    [],
                    $participant_container->getBenutzername()
                );

                if (!$this->hasPasswordChanged($user)) {
                    $this->writer->xmlElement(
                        'Password',
                        [
                            'Type' => 'PLAIN'
                        ],
                        $participant_container->getInitialesPasswort()
                    );
                }
                if ($this->isGenderEmpty($user)) {
                    $this->writer->xmlElement(
                        'Gender',
                        [],
                        strtolower($participant_container->getTeilnehmer()->getGeschlecht())
                    );
                }
            } else {
                $this->writer->xmlStartTag(
                    'User',
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
                $this->writer->xmlElement(
                    'Gender',
                    [],
                    strtolower($participant_container->getTeilnehmer()->getGeschlecht())
                );
            }
            $this->writer->xmlElement(
                'Email',
                [],
                $participant_container->getEmail()
            );


            if ($participant_container->getTeilnehmer()->getGeburtsdatum() instanceof DateTime) {
                $date_string = $participant_container->getTeilnehmer()->getGeburtsdatum()->format('Y-m-d');
                if ($date_string) {
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
                ($this->isValidDate(
                    $participant_container->getGueltigAb(),
                    $participant_container->getGueltigBis()
                ) &&
                    $participant_container->getTeilnehmer()->getAktiv())
                    ?
                    'true' :
                    'false'
            );
            $this->updateTimeLimit($participant_container, $user);


            // Role assignment
            $long_role_id = ('il_' . IL_INST_ID . '_role_' . $this->settings->getSifaParticipantRole());
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
            $this->writer->xmlElement('Lastname', [], $participant_container->getTeilnehmer()->getNachname());

            $this->parseOrganisationInfo($participant_container->getTeilnehmer()->getGeschaeftlichOrganisationId());

            $this->writer->xmlEndTag('User');

            $this->storeUserStatusSuccess($participant_container, $usr_id);
        }

        $this->writer->xmlEndTag('Users');
    }

    protected function updateTimeLimit(TeilnehmerELearningPlattform $participant, ilObjUser $user = null) : void
    {
        $start = $end = 0;
        if ($participant->getGueltigAb() instanceof DateTime) {
            $start = $participant->getGueltigAb()->getTimestamp();
        }
        if ($participant->getGueltigBis() instanceof DateTime) {
            $end = $participant->getGueltigBis()->getTimestamp();
        }

        $this->writer->xmlElement('TimeLimitOwner', [], USER_FOLDER_ID);
        if (!$start || !$end) {
            $this->writer->xmlElement('TimeLimitUnlimited', [], 1);
        } else {
            $this->writer->xmlElement('TimeLimitUnlimited', [], 0);
        }
        if ($start && $end) {
            $this->writer->xmlElement('TimeLimitFrom', [], $start);
            $this->writer->xmlElement('TimeLimitUntil', [], $end);
        } else {
            $this->writer->xmlElement('TimeLimitFrom', [], 0);
            $this->writer->xmlElement('TimeLimitUntil', [], 0);
        }
    }

    protected function importXml() : void
    {
        $this->logger->info('Starting user update');
        $importParser = new ilUserImportParser();
        $importParser->setUserMappingMode(IL_USER_MAPPING_ID);
        $importParser->setXMLContent($this->writer->xmlDumpMem(false));
        $importParser->setRoleAssignment(
            [
                $this->settings->getSifaParticipantRole() => $this->settings->getSifaParticipantRole()
            ]
        );
        $importParser->setFolderId(USER_FOLDER_ID);
        $importParser->startParsing();
        $debug = $importParser->getProtocol();

        $message = 'Finished update users, with protocol message.';
        $this->logger->info($message);
        $this->logger->dump($debug);
        $this->logger->debug($this->writer->xmlDumpMem(true));
    }

    /**
     * udate creation feedback
     */
    protected function updateCreationFeedback() : void
    {
        $pending_participants = $this->usr_repo->lookupAllUsers()->getUsersWithPendingCreationStatus();
        foreach ($pending_participants as $participant_status) {
            try {
                $this->logger->debug('Marked user with oid ' . $participant_status->getOid() . ' as imported.');
                $this->veda_connector->getElearningPlattformApi()->sendAccountCreated($participant_status->getOid());
                $participant_status->setCreationStatus(ilVedaUserStatus::SYNCHRONIZED);
                $this->logger->info('Update creation status');
                $this->usr_repo->updateUser($participant_status);
            } catch (ilVedaConnectionException $e) {
            }
        }
    }

    /**
     * Fetch user id of already created user account
     * @throws ilVedaUserImporterException
     */
    protected function fetchUserId(TeilnehmerELearningPlattform $participant) : int
    {
        $import_id = $participant->getTeilnehmer()->getOid();
        $obj_id = \ilObject::_lookupObjIdByImportId($import_id);
        if (!$obj_id) {
            return 0;
        }

        if (!ilObjUser::_exists($obj_id)) {
            $this->logger->error('Found invalid obj_data entry for import_id: ' . $import_id);
            throw new ilVedaUserImporterException('Invalid db structure. Check log file. Aborting');
        }

        $user = \ilObjectFactory::getInstanceByObjId($obj_id, false);
        if (!$user instanceof \ilObjUser) {
            $this->logger->error('Found invalid obj_data entry for import_id: ' . $import_id);
            throw new ilVedaUserImporterException('Invalid db structure. Check log file. Aborting');
        }
        return $user->getId();
    }

    protected function validateParticipant(int $usr_id, TeilnehmerELearningPlattform $participant) : bool
    {
        if ($usr_id) {
            $this->logger->debug('Existing usr_account with id: ' . $usr_id . ' is valid');
            return true;
        }

        if (!$this->isValidDate($participant->getGueltigAb(), $participant->getGueltigBis())) {
            $this->logger->info('Ignoring participant outside valid time.');
            return false;
        }

        // no usr_id given => usr is valid if login does not exist
        $login = $participant->getBenutzername();
        $generated_login = \ilAuthUtils::_generateLogin($login);

        if (strcmp($generated_login, $login) !== 0) {
            $message = 'User with login: ' . $login . ' already exists.';
            $this->logger->warning($message);
            $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                ->withType(ilVedaMailSegmentType::ERROR)
                ->withMessage($message)
                ->store();
            try {
                $this->veda_connector->getElearningPlattformApi()->sendAccountCreationFailed(
                    $participant->getTeilnehmer()->getOid(),
                    sprintf(self::ERR_LOGIN_EXIST_MSG, $login)
                );
            } catch (Exception $e) {
                $this->logger->error('Sending creation feedback failed with message: ' . $e->getMessage());
            }

            $this->repo_content_builder_factory->getVedaUserBuilder()->buildUser()
                ->withOID($participant->getTeilnehmer()->getOid())
                ->withLogin($participant->getBenutzername())
                ->withCreationStatus(ilVedaUserStatus::NONE)
                ->withPasswordStatus(ilVedaUserStatus::NONE)
                ->withImportFailure(true)
                ->store();
            return false;
        }
        return true;
    }

    /**
     * @throws \ilDatabaseException
     * @throws \ilObjectNotFoundException
     */
    protected function updateLogin(int $usr_id, TeilnehmerELearningPlattform $participant, string &$new_login) : bool
    {
        $user = \ilObjectFactory::getInstanceByObjId($usr_id, false);
        if (!$user instanceof \ilObjUser) {
            $this->logger->warning('Cannot find existing user with id: ' . $usr_id);
            return false;
        }

        $login = $participant->getBenutzername();
        if (strcmp($login, $user->getLogin()) === 0) {
            $this->logger->debug('User login name unchanged.');
            $new_login = $login;
            return true;
        }

        $generated_login = \ilAuthUtils::_generateLogin($login);
        if (strcmp($generated_login, $login) !== 0) {
            $message = 'User with login: ' . $login . ' already exists.';
            $this->logger->warning($message);
            $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                ->withMessage($message)
                ->withType(ilVedaMailSegmentType::ERROR)
                ->store();
            try {
                $this->veda_connector->getElearningPlattformApi()->sendAccountCreationFailed(
                    $participant->getTeilnehmer()->getOid(),
                    sprintf(self::ERR_LOGIN_EXIST_MSG, $login)
                );
            } catch (Exception $e) {
                $this->logger->error('Sending creation feedback failed with message: ' . $e->getMessage());
            }

            $this->repo_content_builder_factory->getVedaUserBuilder()->buildUser()
                ->withOID($participant->getTeilnehmer()->getOid())
                ->withLogin($participant->getBenutzername())
                ->withImportFailure(true)
                ->store();
            return false;
        }

        $new_login = $generated_login;
        return true;
    }

    protected function hasPasswordChanged(\ilObjUser $user) : bool
    {
        return $user->getLastPasswordChangeTS() > 0;
    }

    protected function isGenderEmpty(\ilObjUser $user) : bool
    {
        return $user->getGender() == '';
    }

    protected function storeUserStatusSuccess(TeilnehmerELearningPlattform $participant, int $usr_id) : void
    {
        if (!$usr_id) {
            $this->repo_content_builder_factory->getVedaUserBuilder()->buildUser()
                ->withOID($participant->getTeilnehmer()->getOid())
                ->withLogin($participant->getBenutzername())
                ->withPasswordStatus(ilVedaUserStatus::NONE)
                ->withCreationStatus(ilVedaUserStatus::NONE)
                ->withImportFailure(false)
                ->store();
            $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                ->withType(ilVedaMailSegmentType::USER_IMPORTED)
                ->withMessage('Imported user with oid: ' . $participant->getTeilnehmer()->getOid())
                ->store();
        }
        if ($usr_id) {
            $this->repo_content_builder_factory->getVedaUserBuilder()->buildUser()
                ->withOID($participant->getTeilnehmer()->getOid())
                ->withLogin($participant->getBenutzername())
                ->withImportFailure(false)
                ->store();
            $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                ->withType(ilVedaMailSegmentType::USER_UPDATED)
                ->withMessage('Updated user with oid: ' . $participant->getTeilnehmer()->getOid())
                ->store();
        }
    }

    protected function parseOrganisationInfo(?string $orgoid) : bool
    {
        if (!$orgoid) {
            return false;
        }

        if (isset($this->organisations[$orgoid])) {
            $this->writeOrganisationInfo($this->organisations[$orgoid]);
            return true;
        }

        try {
            $org = $this->veda_connector->getOrganisationApi()->getOrganisation($orgoid);
            $this->organisations[$orgoid] = $org;
            $this->writeOrganisationInfo($this->organisations[$orgoid]);
        } catch (ilVedaConnectionException $e) {
            $this->logger->warning('Cannot read organisation info for org oid: ' . $orgoid);
        }
        return true;
    }

    protected function writeOrganisationInfo(Organisation $org) : void
    {
        $this->logger->dump($org);

        $org_parts = [];
        if (strlen(trim($org->getOrganisationsname1()))) {
            $org_parts[] = $org->getOrganisationsname1();
        }
        if (strlen(trim($org->getOrganisationsname2()))) {
            $org_parts[] = $org->getOrganisationsname2();
        }
        if (strlen(trim($org->getOrganisationsname3()))) {
            $org_parts[] = $org->getOrganisationsname3();
        }

        if (count($org_parts)) {
            $this->writer->xmlElement('Institution', [], implode(' ', $org_parts));
        }

        if ($org->getAdresse() instanceof Adresse) {
            $city = $org->getAdresse()->getOrt();
            if (strlen($city)) {
                $this->writer->xmlElement('City', null, $city);
            }
        }
        $plugin = ilVedaConnectorPlugin::getInstance();
        if (!$plugin->isUDFClaimingPluginAvailable()) {
            $this->logger->warning('Import of organisation information failed: no udf plugin found');
            return;
        }
        $udfclaiming = $plugin->getUDFClaimingPlugin();
        foreach ($udfclaiming->getFields() as $field_name => $field_id) {
            $value = '';
            $field_update_required = false;
            switch ($field_name) {

                case ilVedaUDFClaimingPlugin::FIELD_SUPERVISOR:
                    $field_update_required = true;
                    $value = $org->getAufsichtspersonName();
                    break;

                case ilVedaUDFClaimingPlugin::FIELD_SUPERVISOR_EMAIL:
                    $field_update_required = true;
                    $value = $org->getAufsichtspersonEMail();
                    break;

                case ilVedaUDFClaimingPlugin::FIELD_MEMBER_ID:
                    $field_update_required = true;
                    $value = $org->getMitgliedsnummer();
                    break;

                default:
                    $this->logger->error('Unknown field name given: ' . $field_name);
                    break;
            }
            if ($field_update_required) {
                $this->writer->xmlElement(
                    'UserDefinedField',
                    [
                        'Id' => 'il_' . IL_INST_ID . '_udf_' . $field_id,
                    ],
                    $value
                );
            }
        }
    }

    protected function isValidDate(?DateTime $start, ?DateTime $end) : bool
    {
        if ($start == null) {
            return true;
        }
        $now = new \ilDate(time(), IL_CAL_UNIX);
        $ilstart = new \ilDate($start->format('Y-m-d'), IL_CAL_DATE);

        if ($end == null) {

            // check starting time <= now
            if (\ilDateTime::_before($ilstart, $now, IL_CAL_DAY)) {
                $this->logger->debug('Starting date is valid');
                return true;
            }
            $this->logger->debug('Starting date is invalid');
            return false;
        }

        $ilend = new \ilDate($end->format('Y-m-d'), IL_CAL_DATE);

        if (
            \ilDateTime::_within(
                $now,
                $ilstart,
                $ilend,
                IL_CAL_DAY
            )
        ) {
            return true;
        }
        return false;
    }
}
