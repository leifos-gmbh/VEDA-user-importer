<?php

use GuzzleHttp\Client as GClient;
use OpenAPI\Client\Api\ELearningPlattformenApi;
use OpenAPI\Client\ApiException;
use OpenAPI\Client\Configuration;
use OpenAPI\Client\Model\FehlermeldungApiDto;

class ilVedaElearningPlattformApi implements ilVedaELearningPlattformApiInterface
{
    protected ELearningPlattformenApi $api_elearning;
    protected ilLogger $veda_logger;
    protected ilVedaMailSegmentBuilderFactoryInterface $mail_segment_builder_factory;
    protected string $plattform_id;

    public function __construct(
        string $plattform_id,
        Configuration $config,
        ilLogger $veda_logger,
        ilVedaMailSegmentBuilderFactoryInterface $mail_segment_builder_factory
    ) {
        $this->api_elearning = new ELearningPlattformenApi(
            new GClient(),
            $config,
            new ilVedaConnectorHeaderSelector($config)
        );
        $this->veda_logger = $veda_logger;
        $this->mail_segment_builder_factory = $mail_segment_builder_factory;
        $this->plattform_id = $plattform_id;
    }

    protected function handleException(string $api_call_name, Exception $e): void
    {
        $exception_handler = new ilVedaApiExceptionHandler(
            $api_call_name,
            $this->api_elearning->getConfig()->getAccessToken(),
            $e
        );
        $exception_handler->writeToLog($this->veda_logger);
        $exception_handler->storeAsMailSegment($this->mail_segment_builder_factory);
    }

    public function requestCourseMembers(string $crs_oid) : ?ilVedaCourseMemberCollectionInterface
    {
        try {
            $result = $this->api_elearning->getVonTeilnehmernDieAktivenKurszuordnungenUsingGET(
                $this->plattform_id,
                $crs_oid
            );
            $this->veda_logger->debug('Received course members of course with oid: ' . $crs_oid);
            $this->veda_logger->dump($result);
            return new ilVedaCourseMemberCollection($result);
        } catch (Exception $e) {
            $this->handleException('getVonTeilnehmernDieAktivenKurszuordnungenUsingGET', $e);
            return null;
        }
    }

    public function requestCourseSupervisors(string $crs_oid) : ?ilVedaCourseSupervisorCollectionInterface
    {
        try {
            $result = $this->api_elearning->getVonLernbegleiternDieAktivenKurszuordnungenUsingGET(
                $this->plattform_id,
                $crs_oid
            );
            $this->veda_logger->debug('Received course supervisors of course with oid: ' . $crs_oid);
            $this->veda_logger->dump($result);
            return new ilVedaCourseSupervisorCollection($result);
        } catch (Exception $e) {
            $this->handleException('getVonLernbegleiternDieAktivenKurszuordnungenUsingGET', $e);
            return null;
        }
    }

    public function requestCourseTutors(string $crs_oid) : ?ilVedaCourseTutorsCollectionInterface
    {
        try {
            $result = $this->api_elearning->getVonDozentenDieAktivenKurszuordnungenUsingGET(
                $this->plattform_id,
                $crs_oid
            );
            $this->veda_logger->debug('Received course tutors of course with oid: ' . $crs_oid);
            $this->veda_logger->dump($result);
            return new ilVedaCourseTutorCollection($result);
        } catch (Exception $e) {
            $this->handleException('getVonDozentenDieAktivenKurszuordnungenUsingGET', $e);
            return null;
        }
    }

    public function requestCourses() : ?ilVedaELearningCourseCollectionInterface
    {
        try {
            $result = $this->api_elearning->getAktiveELearningKurseUsingGET(
                $this->plattform_id
            );
            $this->veda_logger->debug('Received e-learning courses.');
            $this->veda_logger->dump($result);
            return new ilVedaELearningCourseCollection($result);
        } catch (Exception $e) {
            $this->handleException('getAktiveELearningKurseUsingGET', $e);
            return null;
        }
    }

    public function requestTrainingCourseTrains(string $training_course_id) : ?ilVedaEducationTrainCourseCollectionInterface
    {
        try {
            $result = $this->api_elearning->getFreigegebeneAusbildungszuegeFuerPlattformUndAusbildungsgangUsingGET(
                $this->plattform_id,
                $training_course_id
            );
            $this->veda_logger->debug('Received education trains with training course id: ' . $training_course_id);
            $this->veda_logger->dump($result);
            return new ilVedaEducationTrainCourseCollection($result);
        } catch (Exception $e) {
            $this->handleException('getFreigegebeneAusbildungszuegeFuerPlattformUndAusbildungsgangUsingGET', $e);
            return null;
        }
    }

    public function requestParticipants() : ?ilVedaELearningParticipantsCollectionInterface
    {
        try {
            $result = $this->api_elearning->getTeilnehmerELearningPlattformUsingGET($this->plattform_id);
            $this->veda_logger->debug('Received all participants.');
            $this->veda_logger->dump($result);
            return new ilVedaELearningParticipantsCollection($result);
        } catch (Exception $e) {
            $this->handleException('getTeilnehmerELearningPlattformUsingGET', $e);
            return null;
        }
    }

    public function sendCourseCopyStarted(string $crs_oid) : bool
    {
        try {
            $this->api_elearning->meldeElearningkursExterneAnlageAngestossenUsingPOST(
                $this->plattform_id,
                $crs_oid
            );
            return true;
        } catch (Exception $e) {
            $this->handleException('meldeElearningkursExterneAnlageAngestossenUsingPOST', $e);
            return false;
        }
    }

    public function sendCourseCreationFailed(string $crs_oid, string $message) : bool
    {
        try {
            $error_message = new FehlermeldungApiDto();
            $error_message->setFehlermeldung($message);
            $this->api_elearning->meldeElearningkursExterneAnlageFehlgeschlagenUsingPOST(
                $this->plattform_id,
                $crs_oid,
                $error_message
            );
            return true;
        } catch (Exception $e) {
            $this->handleException('meldeElearningkursExterneAnlageFehlgeschlagenUsingPOST', $e);
            return false;
        }
    }

    public function sendCourseCreated(string $crs_oid) : bool
    {
        try {
            $this->api_elearning->meldeElearningkursExternExistierendUsingPOST(
                $this->plattform_id,
                $crs_oid
            );
            return true;
        } catch (Exception $e) {
            $this->handleException('meldeElearningkursExternExistierendUsingPOST', $e);
            return false;
        }
    }

    public function sendParticipantStartedCourseWork(string $crs_oid, string $usr_oid) : bool
    {
        try {
            $this->api_elearning->meldeBearbeitungsstartFuerTeilnehmerAufKursUsingPOST(
                $this->plattform_id,
                $crs_oid,
                $usr_oid
            );
            return true;
        } catch (Exception $e) {
            $this->handleException('meldeBearbeitungsstartFuerTeilnehmerAufKursUsingPOST', $e);
            return false;
        }
    }

    public function sendAccountCreated(string $participant_id) : bool
    {
        try {
            $this->api_elearning->meldeElearningaccountAlsExternExistierendUsingPOST(
                $this->plattform_id,
                $participant_id
            );
            return true;
        } catch (Exception $e) {
            $this->handleException('meldeElearningaccountAlsExternExistierendUsingPOST', $e);
            return false;
        }
    }

    public function sendAccountCreationFailed(string $usr_oid, string $message) : bool
    {
        try {
            $error_message = new FehlermeldungApiDto();
            $error_message->setFehlermeldung($message);
            $this->api_elearning->meldeElearningaccountAnlageAlsFehlgeschlagenUsingPOST(
                $this->plattform_id,
                $usr_oid,
                $error_message
            );
            $this->veda_logger->info('Send message: ' . $error_message->getFehlermeldung());
            return true;
        } catch (Exception $e) {
            $this->handleException('meldeElearningaccountAnlageAlsFehlgeschlagen', $e);
            return false;
        }
    }

    public function sendCoursePassed(string $crs_oid, string $usr_oid) : bool
    {
        try {
            $this->api_elearning->meldeKursabschlussMitErfolgUsingPOST(
                $this->plattform_id,
                $crs_oid,
                $usr_oid,
            );
            return true;
        } catch (Exception $e) {
            $this->handleException('meldeKursabschlussMitErfolgUsingPOST', $e);
            return false;
        }
    }

    public function sendCourseFailed(string $crs_oid, string $usr_oid) : bool
    {
        try {
            $this->api_elearning->meldeKursabschlussOhneErfolgUsingPOST(
                $this->plattform_id,
                $crs_oid,
                $usr_oid
            );
            return true;
        } catch (Exception $e) {
            $this->handleException('meldeKursabschlussOhneErfolgUsingPOST', $e);
            return false;
        }
    }

    public function sendFirstLoginSuccess(string $usr_oid) : bool
    {
        try {
            $this->api_elearning->meldeErstmaligErfolgreichEingeloggtUsingPOST(
                $this->plattform_id,
                $usr_oid
            );
            $this->veda_logger->info('Password notification sent.');
            return true;
        } catch (Exception $e) {
            $this->handleException('meldeErstmaligErfolgreichEingeloggt', $e);
            return false;
        }
    }
}
