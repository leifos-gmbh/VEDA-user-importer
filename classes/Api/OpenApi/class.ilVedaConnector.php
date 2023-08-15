<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use GuzzleHttp\Client as GClient;
use OpenAPI\Client\ApiException;
use OpenApi\Client\Configuration;
use OpenApi\Client\Api\AusbildungszugabschnitteApi;
use OpenApi\Client\Api\AusbildungsgngeApi;
use OpenApi\Client\Api\AusbildungszgeApi;
use OpenApi\Client\Api\OrganisationenApi;
use OpenApi\Client\Api\ELearningPlattformenApi;
use OpenApi\Client\Model\FehlermeldungApiDto;
use OpenAPI\Client\Model\KursbearbeitungDto;
use OpenApi\Client\Model\MeldeLernerfolgApiDto;
use OpenApi\Client\Model\PraktikumsberichtEingegangenApiDto;
use OpenApi\Client\Model\PraktikumsberichtKorrigiertApiDto;
use OpenApi\Client\Model\Teilnehmerkurszuordnung;
use OpenApi\Client\Model\Lernbegleiterkurszuordnung;
use OpenApi\Client\Model\Dozentenkurszuordnung;
use OpenApi\Client\Model\AusbildungszugDozent;
use OpenApi\Client\Model\AusbildungszugLernbegleiter;
use OpenApi\Client\Model\AufsichtspersonKurszugriff;
use OpenApi\Client\Model\AusbildungszugTeilnehmer;
use OpenApi\Client\Model\Elearningkurs;
use OpenApi\Client\Model\Ausbildungszug;
use OpenApi\Client\Model\Organisation;
use OpenApi\Client\Model\Ausbildungsgang;
use OpenApi\Client\Model\TeilnehmerELearningPlattform;

/**
 * Connector for all rest api calls.
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilVedaConnector
{
    /**
     * @var string
     */
    public const COURSE_CREATION_FAILED = 'Synchronisierung des Ausbildungszugs fehlgeschlagen.';
    /**
     * @var string
     */
    public const COURSE_CREATION_FAILED_MASTER_COURSE_MISSING = 'Masterkurs-Id nicht vorhanden.';
    /**
     * @var string
     */
    public const COURSE_CREATION_FAILED_ELARNING = 'Synchronisierung des ELearning-Kurses fehlgeschlagen.';

    protected ilLogger $veda_logger;
    protected ilVedaConnectorSettings $settings;
    protected ELearningPlattformenApi $api_elearning;
    protected AusbildungsgngeApi $api_training_course;
    protected AusbildungszgeApi $api_training_course_train;
    protected AusbildungszugabschnitteApi $api_training_course_train_segment;
    protected OrganisationenApi $api_organisation;

    protected ilVedaMailSegmentBuilderFactoryInterface $mail_segment_builder_factory;

    public function __construct(
        ilLogger $veda_logger,
        ilVedaConnectorSettings $settings,
        ilVedaMailSegmentBuilderFactoryInterface $mail_segment_builder_factory
    ) {
        $this->veda_logger = $veda_logger;
        $this->settings =$settings;
        $this->mail_segment_builder_factory = $mail_segment_builder_factory;

        $config = $this->createApiConfiguration();
        $this->api_elearning = new ELearningPlattformenApi(
            new GClient(),
            $config,
            new ilVedaConnectorHeaderSelector($config)
        );

        $config = $this->createApiConfiguration();
        $this->api_training_course = new AusbildungsgngeApi(
            new GClient(),
            $config,
            new ilVedaConnectorHeaderSelector($config)
        );

        $config = $this->createApiConfiguration();
        $this->api_training_course_train = new AusbildungszgeApi(
            new GClient(),
            $config,
            new ilVedaConnectorHeaderSelector($config)
        );

        $config = $this->createApiConfiguration();
        $this->api_training_course_train_segment = new AusbildungszugabschnitteApi(
            new GClient(),
            $config,
            new ilVedaConnectorHeaderSelector($config)
        );

        $config = $this->createApiConfiguration();
        $this->api_organisation = new OrganisationenApi(
            new GClient(),
            $config,
            new ilVedaConnectorHeaderSelector($config)
        );
    }

    protected function createApiConfiguration(): Configuration
    {
        $config = new Configuration();
        $config->setApiKey(
            ilVedaConnectorSettings::HEADER_TOKEN,
            $this->settings->getAuthenticationToken()
        );
        $config->setHost($this->settings->getRestUrl());
        $config->setAccessToken($this->settings->getAuthenticationToken());
        return $config;
    }

    public function sendCourseCreationFailed(string $oid): void
    {
        try {
            $error_message = new FehlermeldungApiDto();
            $error_message->setFehlermeldung(self::COURSE_CREATION_FAILED);
            $this->api_training_course_train->meldeAusbildungszugAnlageFehlgeschlagenUsingPOST(
                $oid,
                $error_message
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeAusbildungszugAnlageFehlgeschlagenUsingPOST', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    /**
     * @param string $oid
     * @throws ilVedaConnectionException
     */
    public function sendStandardCourseCopyStarted(string $oid): void
    {
        try {
            $this->api_elearning->meldeElearningkursExterneAnlageAngestossenUsingPOST(
                $this->settings->getPlatformId(),
                $oid
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeElearningkursExterneAnlageAngestossenUsingPOST', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    /**
     * @throws ilVedaConnectionException
     */
    public function sendStandardCourseCreationFailed(string $oid, string $message): void
    {
        try {
            $error_message = new FehlermeldungApiDto();
            $error_message->setFehlermeldung($message);
            $this->api_elearning->meldeElearningkursExterneAnlageFehlgeschlagenUsingPOST(
                $this->settings->getPlatformId(),
                $oid,
                $error_message
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeElearningkursExterneAnlageFehlgeschlagenUsingPOST', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    /**
     * @throws ilVedaConnectionException
     */
    public function sendStandardCourseCreated(string $oid): void
    {
        try {
            $this->api_elearning->meldeElearningkursExternExistierendUsingPOST(
                $this->settings->getPlatformId(),
                $oid
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeElearningkursExternExistierendUsingPOST', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    /**
     * @throws ilVedaConnectionException
     */
    public function sendParticipantAssignedToCourse(string $crs_oid, string $usr_oid): void {
        try {
            $this->api_elearning->meldeBearbeitungsstartFuerTeilnehmerAufKursUsingPOST(
                $this->settings->getPlatformId(),
                $crs_oid,
                $usr_oid
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeBearbeitungsstartFuerTeilnehmerAufKursUsingPOST', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    /**
     * @throws ilVedaConnectionException
     */
    public function sendExerciseSubmissionConfirmed(string $segment_id, string $participant_id, DateTime $confirmed = null): void
    {
        try {
            $info = new PraktikumsberichtKorrigiertApiDto();
            if ($confirmed) {
                $info->setPraktikumsberichtKorrigiert(true);
                $info->setPraktikumsberichtKorrigiertAm($confirmed);
            } else {
                $info->setPraktikumsberichtKorrigiert(false);
            }
            $this->veda_logger->dump($info);

            $this->api_training_course_train_segment->meldePraktikumsberichtKorrigiertUsingPUT(
                $segment_id,
                $participant_id,
                $info
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('meldePraktikumsberichtKorrigiertUsingPUT', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    /**
     * @throws ilVedaConnectionException
     */
    public function sendExerciseSubmissionDate(string $segment_id, string $participant_id, ?DateTime $subdate = null): void
    {
        try {
            $info = new PraktikumsberichtEingegangenApiDto();
            if ($subdate) {
                $info->setPraktikumsberichtEingegangen(true);
                $info->setPraktikumsberichtEingegangenAm($subdate);
            } else {
                $info->setPraktikumsberichtEingegangen(false);
            }

            $this->veda_logger->dump($info);

            $this->api_training_course_train_segment->meldePraktikumsberichtEingegangenUsingPUT(
                $segment_id,
                $participant_id,
                $info
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('meldePraktikumsberichtEingegangenUsingPUT', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    /**
     * @throws ilVedaConnectionException
     */
    public function sendExerciseSuccess(string $segment_id, string $participant_id, \DateTime $dt): void
    {
        try {
            $info = new MeldeLernerfolgApiDto();
            $info->setLernerfolg(true);
            $info->setLernerfolgGemeldetAm($dt);

            $this->veda_logger->dump($info);

            $this->api_training_course_train_segment->meldeLernerfolgUsingPUT(
                $segment_id,
                $participant_id,
                $info
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('MeldeLernerfolgApiDto', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }


    /**
     * @return Teilnehmerkurszuordnung[]
     * @throws ilVedaConnectionException
     */
    public function readStandardCourseMembers(string $course_oid) : array
    {
        try {
            return $this->api_elearning->getVonTeilnehmernDieAktivenKurszuordnungenUsingGET(
                $this->settings->getPlatformId(),
                $course_oid
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('getVonTeilnehmernDieAktivenKurszuordnungenUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }


    /**
     * @return Lernbegleiterkurszuordnung[]
     * @throws ilVedaConnectionException
     */
    public function readStandardCourseSupervisors(string $course_oid) : array
    {
        try {
            return $this->api_elearning->getVonLernbegleiternDieAktivenKurszuordnungenUsingGET(
                $this->settings->getPlatformId(),
                $course_oid
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('getVonLernbegleiternDieAktivenKurszuordnungenUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    /**
     * @return Dozentenkurszuordnung[]
     * @throws ilVedaConnectionException
     */
    public function readStandardCourseTutors(string $course_oid) : array
    {
        try {
            $response = $this->api_elearning->getVonDozentenDieAktivenKurszuordnungenUsingGET(
                $this->settings->getPlatformId(),
                $course_oid
            );
            return $response;
        } catch (Exception $e) {
            $this->handleApiExceptions('getVonDozentenDieAktivenKurszuordnungenUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    /**
     * @return AusbildungszugDozent[]
     * @throws ilVedaConnectionException
     */
    public function readTrainingCourseTrainTutors(?string $oid): array
    {
        try {
            return $this->api_training_course_train->getBeteiligteDozentenVonAusbildungszugUsingGET($oid);
        } catch (Exception $e) {
            $this->handleApiExceptions('getBeteiligteDozentenVonAusbildungszugUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    /**
     * @return AusbildungszugLernbegleiter[]
     * @throws ilVedaConnectionException
     */
    public function readTrainingCourseTrainCompanions(?string $oid)
    {
        try {
            return $this->api_training_course_train->getLernbegleiterVonAusbildungszugUsingGET($oid);
        } catch (Exception $e) {
            $this->handleApiExceptions('getLernbegleiterVonAusbildungszugUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    /**
     * @return AufsichtspersonKurszugriff[]
     * @throws ilVedaConnectionException
     */
    public function readTrainingCourseTrainSupervisors(?string $oid): array
    {
        try {
            return $this->api_training_course_train->getAufsichtspersonenVonAusbildungszugUsingGET($oid);
        } catch (Exception $e) {
            $this->handleApiExceptions('getAufsichtspersonenVonAusbildungszugUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    /**
     * @return AusbildungszugTeilnehmer[]
     * @throws ilVedaConnectionException
     */
    public function readTrainingCourseTrainMembers(?string $oid): array
    {
        try {
            return $this->api_training_course_train->getTeilnehmerVonAusbildungszugUsingGET($oid);
        } catch (Exception $e) {
            $this->handleApiExceptions('getTeilnehmerVonAusbildungszugUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    /**
     * @throws ilVedaConnectionException
     */
    public function sendTrainingCourseTrainCreated(string $oid): void
    {
        try {
            $this->api_training_course_train->meldeAusbildungszugAlsExternExistierendUsingPOST($oid);
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeAusbildungszugAlsExternExistierendUsingPOST', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    /**
     * @param string $oid
     * @throws ilVedaConnectionException
     */
    public function sendTrainingCourseTrainCopyStarted(string $oid): void
    {
        try {
            $this->api_training_course_train->meldeExterneAnlageAngestossenUsingPOST($oid);
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeExterneAnlageAngestossenUsingPOST', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    /**
     * @return Elearningkurs[]
     * @throws ilVedaConnectionException
     */
    public function getStandardCourses() : array
    {
        try {
            $response = $this->api_elearning->getAktiveELearningKurseUsingGET(
                $this->settings->getPlatformId()
            );
            return $response;
        } catch (Exception $e) {
            $this->handleApiExceptions('getAktiveELearningKurseUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    /**
     * @return Ausbildungszug[]
     * @throws ilVedaConnectionException
     */
    public function getTrainingCourseTrains(string $training_course_id) : array
    {
        try {
            $response = $this->api_elearning->getFreigegebeneAusbildungszuegeFuerPlattformUndAusbildungsgangUsingGET(
                $this->settings->getPlatformId(),
                $training_course_id
            );
            return $response;
        } catch (Exception $e) {
            $this->handleApiExceptions('getFreigegebeneAusbildungszuegeFuerPlattformUndAusbildungsgangUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    /**
     * @param string $orgr_oid
     * @return Organisation
     * @throws ilVedaConnectionException
     */
    public function getOrganisation(string $orgr_oid): Organisation
    {
        try {
            $response = $this->api_organisation->getOrganisationUsingGET($orgr_oid);
            $this->veda_logger->dump($response);
            return $response;
        } catch (Exception $e) {
            $this->handleApiExceptions('getOrganisationUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }


    /**
     * Get training courses for ausbildungsgang
     * @param string $training_course_id
     * @return Ausbildungsgang
     * @throws ilVedaConnectionException
     */
    public function getTrainingCourse(string $training_course_id): Ausbildungsgang
    {
        try {
            return $this->api_training_course->getAusbildungsgangUsingGET($training_course_id);
        } catch (Exception $e) {
            $this->handleApiExceptions('getAusbildungsgangUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }


    /**
     * @return TeilnehmerELearningPlattform[]
     * @throws ilVedaConnectionException
     */
    public function getParticipants()
    {
        try {
            return $this->api_elearning->getTeilnehmerELearningPlattformUsingGET($this->settings->getPlatformId());
        } catch (Exception $e) {
            $this->handleApiExceptions('getTeilnehmerELearningPlattformUsingGET', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    /**
     * @throws ilVedaConnectionException
     */
    public function sendCreationMessage(string $participant_id) : void
    {
        try {
            $this->api_elearning->meldeElearningaccountAlsExternExistierendUsingPOST(
                $this->settings->getPlatformId(),
                $participant_id
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeElearningaccountAlsExternExistierendUsingPOST', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    /**
     * Send failure message to ws.
     * @throws ilVedaConnectionException
     */
    public function sendAccountCreationFailed(string $import_id, string $message) : void
    {
        try {
            $error_message = new FehlermeldungApiDto();
            $error_message->setFehlermeldung($message);
            $this->api_elearning->meldeElearningaccountAnlageAlsFehlgeschlagenUsingPOST(
                $this->settings->getPlatformId(),
                $import_id,
                $error_message
            );
            $this->veda_logger->info('Send message: ' . $error_message->getFehlermeldung());
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeElearningaccountAnlageAlsFehlgeschlagen', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function sendCoursePassed(string $crs_oid, string $usr_oid): void
    {
        try {
            $this->api_elearning->meldeKursabschlussMitErfolgUsingPOST(
                $this->settings->getPlatformId(),
                $crs_oid,
                $usr_oid
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeKursabschlussMitErfolgUsingPOST', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    public function sendCourseFailed(string $crs_oid, string $usr_oid): void
    {
        try {
            $this->api_elearning->meldeKursabschlussOhneErfolgUsingPOST(
                $this->settings->getPlatformId(),
                $crs_oid,
                $usr_oid
            );
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeKursabschlussOhneErfolgUsingPOST', $e);
            throw new ilVedaConnectionException($e->getMessage(), ilVedaConnectionException::ERR_API);
        }
    }

    /**
     * send password notification, if required.
     */
    public function handlePasswordChange(int $usr_id) : void
    {
        $import_id = ilObjUser::_lookupImportId($usr_id);

        if (!$import_id) {
            $this->veda_logger->debug('No import id for user ' . $usr_id);
            return;
        }
        $user_db_manager = (new ilVedaRepositoryFactory())->getUserRepository();
        $user_status = $user_db_manager->lookupUserByOID($import_id);
        $user_status = is_null($user_status) ? new ilVedaUser($import_id) : $user_status;

        if (
            $user_status->isImportFailure() ||
            $user_status->getPasswordStatus() != ilVedaUserStatus::PENDING
        ) {
            $this->veda_logger->debug('No password notification required.');
        }

        try {
            $this->api_elearning->meldeErstmaligErfolgreichEingeloggtUsingPOST(
                $this->settings->getPlatformId(),
                $user_status->getOid()
            );
            $user_status->setPasswordStatus(ilVedaUserStatus::SYNCHRONIZED);
            $user_db_manager->updateUser($user_status);
            $this->veda_logger->info('Password notification sent.');
        } catch (Exception $e) {
            $this->handleApiExceptions('meldeErstmaligErfolgreichEingeloggt', $e);
        }
    }

    protected function handleApiExceptions(
        string $api_call_name,
        Exception  $e
    ): void {
        $this->veda_logger->warning(ilVedaConnectorSettings::HEADER_TOKEN . ': ' . $this->settings->getAuthenticationToken());
        $this->veda_logger->warning($api_call_name . ' failed with message: ' . $e->getMessage());
        $this->veda_logger->dump($e->getResponseHeaders(), ilLogLevel::WARNING);
        $this->veda_logger->dump($e->getTraceAsString(), ilLogLevel::WARNING);
        $this->veda_logger->warning($e->getResponseBody());
        $this->mail_segment_builder_factory->buildSegment()
            ->withType(ilVedaMailSegmentType::ERROR)
            ->withMessage('Verbindungsfehler beim Aufuf von: ' . $api_call_name)
            ->store();
    }
}
