<?php

use OpenAPI\Client\Model\Dozentenkurszuordnung;
use OpenAPI\Client\Model\Lernbegleiterkurszuordnung;
use OpenAPI\Client\Model\AusbildungszugTeilnehmer;
use OpenAPI\Client\Model\Teilnehmerkurszuordnung;

class ilVedaMemberStandardImportAdapter
{
    /**
     * @var string
     */
    protected const REGULAR = 'REGULAER';
    /**
     * @var string
     */
    protected const TEMPORARY = 'TEMPORAER';

    protected ilLogger $logger;
    protected ilRbacAdmin $rbac_admin;
    /**
     * @var array
     */
    protected $new_assignments = [];
    protected ilVedaConnector $veda_connector;
    protected ilVedaCourseRepositoryInterface $crs_repo;
    protected ilVedaRepositoryContentBuilderFactoryInterface $repo_content_builder_factory;

    public function __construct(
        ilLogger $veda_logger,
        ilRbacAdmin $rbac_admin,
        ilVedaConnector $veda_connector,
        ilVedaCourseRepositoryInterface  $crs_repo,
        ilVedaRepositoryContentBuilderFactoryInterface $repo_content_builder_factory
    ) {
        $this->logger = $veda_logger;
        $this->rbac_admin = $rbac_admin;
        $this->veda_connector = $veda_connector;
        $this->crs_repo = $crs_repo;
        $this->repo_content_builder_factory = $repo_content_builder_factory;
    }

    public function import() : void
    {
        $this->logger->debug('Reading "ELearning-Kurse" ...');
        $standard_courses = $this->crs_repo->lookupAllCourses()->getCoursesWithStatusAndType(
            ilVedaCourseStatus::SYNCHRONIZED,
            ilVedaCourseType::STANDARD
        );
        foreach ($standard_courses as $standard_cours) {
            $oid = $standard_cours->getOid();
            $obj_id = $standard_cours->getObjId();
            if (!$this->ensureCourseExists($obj_id)) {
                $this->logger->warning('Ignoring deleted course with id: ' . $obj_id);
                continue;
            }
            $this->synchronizeParticipants($oid, $obj_id);
        }
    }

    protected function synchronizeParticipants(string $oid, int $obj_id) : void
    {
        $elearning_api = $this->veda_connector->getElearningPlattformApi();
        $tutors = $elearning_api->requestCourseTutors($oid);
        $supervisors = $elearning_api->requestCourseSupervisors($oid);
        $members = $elearning_api->requestCourseMembers($oid);

        $this->logger->dump(array_merge($tutors, $supervisors, $members), \ilLogLevel::DEBUG);

        $participants = $this->initParticipants($obj_id);
        $course = $this->initCourse($obj_id);

        $this->removeDeprecatedMembers($participants, $course, $members);
        $this->removeDeprecatedTutors($participants, $course, $tutors, $supervisors);
        $this->addNewMembers($participants, $course, $members);
        $this->addNewTutors($participants, $course, $tutors, $supervisors);
    }

    /**
     * @param Teilnehmerkurszuordnung[] $members
     */
    protected function removeDeprecatedMembers(ilCourseParticipants $part, ilObjCourse $course, array $members) : void
    {
        $this->logger->debug('Removing deprecated members');
        foreach ($part->getMembers() as $user_id) {
            $import_id = \ilObject::_lookupImportId($user_id);
            if (!$import_id) {
                $this->logger->debug('Keep member assignment for non synchonised account.');
            }
            $found = false;
            foreach ($members as $member) {
                if (
                    !ilVedaUtils::compareOidsEqual($import_id, $member->getTeilnehmerId()) ||
                    !ilVedaUtils::isValidDate($member->getKursZugriffAb(), $member->getKursZugriffBis())
                ) {
                    continue;
                }
                $found = true;
                break;
            }
            if (!$found) {
                $message = 'Deassigning user: ' . $user_id . ' with oid ' . $import_id . ' from course: ' . $course->getTitle();
                $this->logger->info($message);
                $this->rbac_admin->deassignUser(
                    $course->getDefaultMemberRole(),
                    $user_id
                );
                $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                    ->withType(ilVedaMailSegmentType::MEMBERSHIP_UPDATED)
                    ->withMessage($message)
                    ->store();
            }
        }
    }

    /**
     * @param Lernbegleiterkurszuordnung[] $supervisors
     * @param Dozentenkurszuordnung[] $tutors
     */
    protected function removeDeprecatedTutors(
        ilCourseParticipants $part,
        ilObjCourse $course,
        array $tutors,
        array $supervisors
    ) : void {
        $this->logger->debug('Removing deprecated tutors');
        $combined_tutors = array_merge(
            $tutors,
            $supervisors
        );

        foreach ($part->getTutors() as $user_id) {
            $import_id = \ilObject::_lookupImportId($user_id);
            if (!$import_id) {
                $this->logger->debug('Keep member assignment for non synchonised account.');
            }
            $found = false;
            foreach ($combined_tutors as $tutor) {
                if (
                    !ilVedaUtils::compareOidsEqual($import_id, $tutor->getElearningbenutzeraccountId()) ||
                    !ilVedaUtils::isValidDate($tutor->getKursZugriffAb(), $tutor->getKursZugriffBis())
                ) {
                    continue;
                }
                $found = true;
                break;
            }
            if (!$found) {
                $message = 'Deassigning tutor: ' . $user_id . ' with oid ' . $import_id . ' from course: ' . $course->getTitle();
                $this->logger->info($message);
                $this->rbac_admin->deassignUser(
                    $course->getDefaultTutorRole(),
                    $user_id
                );
                $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                    ->withType(ilVedaMailSegmentType::MEMBERSHIP_UPDATED)
                    ->withMessage($message)
                    ->store();
            }
        }
    }

    /**
     * @param Teilnehmerkurszuordnung[] $members
     */
    protected function addNewMembers(ilCourseParticipants $participants, ilObjCourse $course, array $members) : void
    {
        $this->logger->debug('Adding new members');
        foreach ($members as $member) {
            $this->logger->debug('Validating ' . $member->getTeilnehmerId());
            $user_id = $this->getUserIdForImportId($member->getTeilnehmerId());
            $this->logger->debug('Found usr_id: ' . $user_id);
            if (!$user_id) {
                $this->logger->warning('Cannot find user id for import_id: ' . $member->getTeilnehmerId());
                continue;
            }
            if ($participants->isMember($user_id)) {
                $this->logger->debug('User with id: ' . $user_id . ' is already assigned to course: ' . $course->getTitle());
                continue;
            }
            if (ilVedaUtils::isValidDate($member->getKursZugriffAb(), $member->getKursZugriffBis())) {
                $this->assignUserToRole(
                    $course->getDefaultMemberRole(),
                    $user_id,
                    $this->new_assignments,
                    $participants,
                    $course
                );
            } else {
                $this->logger->info('Ignoring user with access: ' . $member->getKursZugriffAb()->format('Y-m-d') . ', ' . $member->getKursZugriffBis()->format('Y-m-d'));
            }
        }
    }

    /**
     * @param Dozentenkurszuordnung[] $tutors
     * @param Lernbegleiterkurszuordnung[] $supervisors
     */
    protected function addNewTutors(
        ilCourseParticipants $participants,
        ilObjCourse $course,
        array $tutors,
        array $supervisors
    ) : void {
        $this->logger->debug('Adding new tutors');
        $combined_tutors = array_merge($tutors, $supervisors);

        foreach ($combined_tutors as $tutor) {
            $user_id = $this->getUserIdForImportId($tutor->getElearningbenutzeraccountId());
            if (!$user_id) {
                //$this->logger->warning('Cannot find user id for import_id: ' . $tutors->getElearningbenutzeraccountId());
                continue;
            }
            if ($participants->isMember($user_id)) {
                $this->logger->debug('User with id: ' . $user_id . ' is already assigned to course: ' . $course->getTitle());
                continue;
            }
            if (ilVedaUtils::isValidDate($tutor->getKursZugriffAb(), $tutor->getKursZugriffBis())) {
                $this->assignUserToRole(
                    $course->getDefaultTutorRole(),
                    $user_id,
                    $this->new_assignments,
                    $participants,
                    $course
                );
            }
        }
    }

    protected function initCourse(int $obj_id) : ilObjCourse
    {
        $refs = \ilObject::_getAllReferences($obj_id);
        $ref_id = end($refs);
        // throws
        $course = ilObjectFactory::getInstanceByRefId($ref_id, false);
        if (!$course instanceof ilObjCourse) {
            $message = 'Invalid course id given: ' . $obj_id;
            $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                ->withMessage($message)
                ->withType(ilVedaMailSegmentType::ERROR)
                ->store();
            throw new \InvalidArgumentException($message);
        }
        return $course;
    }

    protected function initParticipants(int $obj_id) : ilCourseParticipants
    {
        $refs = \ilObject::_getAllReferences($obj_id);
        $ref_id = end($refs);
        // throws
        $participants = ilParticipants::getInstance($ref_id);
        if (!$participants instanceof ilCourseParticipants) {
            $message = 'Invalid participant id given: ' . $obj_id;
            $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                ->withMessage($message)
                ->withType(ilVedaMailSegmentType::ERROR)
                ->store();
            throw new \InvalidArgumentException($message);
        }
        return $participants;
    }

    protected function ensureCourseExists(int $obj_id) : bool
    {
        $refs = \ilObject::_getAllReferences($obj_id);
        $ref_id = end($refs);
        try {
            $course = \ilObjectFactory::getInstanceByRefId($ref_id, false);
            if ($course instanceof ilObjCourse) {
                return true;
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return false;
    }

    /**
     * @param int[] $assigned
     */
    protected function assignUserToRole(
        int $role,
        int $user,
        array &$assigned,
        \ilCourseParticipants $part,
        \ilObjCourse $course
    ) : void {
        $this->rbac_admin->assignUser($role, $user);
        if (!in_array($user, $assigned)) {
            $this->logger->debug('Adding new user sending mail notification...');
            $part->sendNotification($part->NOTIFY_ACCEPT_USER, $user);
            $favourites = new ilFavouritesManager();
            $favourites->add(
                $user,
                $course->getRefId()
            );
            $assigned[] = $user;
            $this->repo_content_builder_factory->getMailSegmentBuilder()->buildSegment()
                ->withType(ilVedaMailSegmentType::MEMBERSHIP_UPDATED)
                ->withMessage('Adding new user with role_id ' . $role . ' user_id ' . $user . ' to course with ref_id ' . $course->getRefId())
                ->store();
        }
    }

    protected function getUserIdForImportId(?string $oid) : int
    {
        return ilObject::_lookupObjIdByImportId($oid);
    }
}
