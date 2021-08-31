<?php

use Swagger\Client\Model\AusbildungszugTeilnehmer;
use Swagger\Client\Model\Teilnehmerkurszuordnung as TeilnehmerkurszuordnungAlias;
use Swagger\Client\Model\Dozentenkurszuordnung as DozentenkurszuordnungAlias;
use Swagger\Client\Model\Lernbegleiterkurszuordnung as LernbegleiterkurszuordnungAlias;

/**
 * Class ilVedaMemberImportAdapter
 */
class ilVedaMemberSibeImportAdapter
{
	private const REGULAR = 'REGULAER';
	private const TEMPORARY = 'TEMPORAER';


	/**
	 * @var null | \ilVedaConnectorPlugin
	 */
	private $plugin  = null;

	/**
	 * @var \ilLogger|null
	 */
	private $logger = null;

	/**
	 * @var \ilVedaConnectorSettings|null
	 */
	private $settings = null;

    /**
     * @var ilRbacAdmin
     */
	private $rbacadmin;

    /**
     * @var array
     */
	private $new_assignments = [];



	/**
	 * ilVedaMemberImportAdapter constructor.
	 */
	public function __construct()
	{
	    global $DIC;

		$this->plugin = \ilVedaConnectorPlugin::getInstance();
		$this->logger = $this->plugin->getLogger();
		$this->settings = \ilVedaConnectorSettings::getInstance();
		$this->rbacadmin = $DIC->rbac()->admin();
	}

	/**
	 * do import
	 */
	public function import()
	{
		$this->logger->debug('Reading "ELearning-Kurse" ...');
        $sibe_courses =  \ilVedaCourseStatus::lookupSynchronizedCourses(\ilVedaCourseStatus::TYPE_SIBE);
		foreach ($sibe_courses as $oid => $obj_id) {
		    if (!$this->ensureCourseExists($obj_id)) {
		        $this->logger->warning('Ignoring deleted course with id: ' . $obj_id);
		        continue;
            }
		    $this->synchronizeParticipants($oid, $obj_id);
        }
	}

	protected function synchronizeParticipants(string $oid, int $obj_id) : void
    {
        // Throws
        $connector = \ilVedaConnector::getInstance();
        $tutors = $connector->readSibeCourseTutors($oid);
        $supervisors = $connector->readSibeCourseSupervisors($oid);
        $members = $connector->readSibeCourseMembers($oid);

        $participants = $this->initParticipants($obj_id);
        $course = $this->initCourse($obj_id);

        $this->removeDeprecatedMembers($participants, $course, $members);
        $this->removeDeprecatedTutors($participants, $course, $tutors, $supervisors);
        $this->addNewMembers($participants, $course, $members);
        $this->addNewTutors($participants, $course, $tutors, $supervisors);
    }

    /**
     * @param ilCourseParticipants           $part
     * @param TeilnehmerkurszuordnungAlias[] $members
     */
    protected function removeDeprecatedMembers(ilCourseParticipants $part, ilObjCourse $course, array $members) : void
    {
        foreach ($part->getMembers() as $user_id) {
            $import_id = \ilObject::_lookupImportId($user_id);
            if (!$import_id) {
                $this->logger->debug('Keep member assignment for non synchonised account.');
            }
            $found = false;
            foreach ($members as $member) {
                if (!\ilVedaUtils::compareOidsEqual($import_id, $member->getElearningbenutzeraccountId())) {
                    continue;
                }
                // same oid, check date
                if (\ilVedaUtils::isValidDate($member->getKursZugriffAb(), $member->getKursZugriffBis())) {
                    $found = true;
                }
            }
            if (!$found) {
                $this->logger->info('Deassigning user: ' . $user_id . ' with oid '. $import_id .' from course: ' . $course->getTitle());
                $this->rbacadmin->deassignUser(
                    $course->getDefaultMemberRole(),
                    $user_id
                );
            }
        }
    }

    /**
     * @param ilCourseParticipants           $part
     * @param ilObjCourse
     * @param DozentenkurszuordnungAlias[]
     * @param LernbegleiterkurszuordnungAlias[]
     */
    protected function removeDeprecatedTutors(ilCourseParticipants $part, ilObjCourse $course, array $tutors, array $supervisors) : void
    {
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
                if (!\ilVedaUtils::compareOidsEqual($import_id, $tutor->getElearningbenutzeraccountId())) {
                    continue;
                }
                // same oid, check date
                if (\ilVedaUtils::isValidDate($tutor->getKursZugriffAb(), $tutor->getKursZugriffBis())) {
                    $found = true;
                }
            }
            if (!$found) {
                $this->logger->info('Deassigning tutor: ' . $user_id . ' with oid '. $import_id .' from course: ' . $course->getTitle());
                $this->rbacadmin->deassignUser(
                    $course->getDefaultTutorRole(),
                    $user_id
                );
            }
        }
    }

    /**
     * @param ilCourseParticipants $participants
     * @param ilObjCourse          $course
     * @param TeilnehmerkurszuordnungAlias[] $members
     */
    protected function addNewMembers(ilCourseParticipants $participants, ilObjCourse $course, array $members) : void
    {
        foreach ($members as $member) {
            $user_id = $this->getUserIdForImportId($member->getElearningbenutzeraccountId());
            if (!$user_id) {
                $this->logger->warning('Cannot find user id for import_id: ' . $member->getElearningbenutzeraccountId());
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
            }
        }
    }

    /**
     * @param ilCourseParticipants $participants
     * @param ilObjCourse          $course
     * @param DozentenkurszuordnungAlias[] $tutors
     * @param LernbegleiterkurszuordnungAlias[] $supervisors
     */
    protected function addNewTutors(ilCourseParticipants $participants, ilObjCourse $course, array $tutors, array $supervisors) : void
    {
        $combined_tutors = array_merge($tutors, $supervisors);

        foreach ($combined_tutors as $tutor) {
            $user_id = $this->getUserIdForImportId($tutor->getElearningbenutzeraccountId());
            if (!$user_id) {
                $this->logger->warning('Cannot find user id for import_id: ' . $tutors->getElearningbenutzeraccountId());
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
            throw new \InvalidArgumentException('Invalid course id given: ' . $obj_id);
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
            throw new \InvalidArgumentException('Invalid course id given: ' . $obj_id);
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
	 * @param int $role
	 * @param int $user
	 * @param array $assigned
	 * @param \ilCourseParticipants $part
	 */
	protected function assignUserToRole(int $role, int $user, array &$assigned, \ilCourseParticipants $part, \ilObjCourse $course)
	{
		global $DIC;

		$admin = $DIC->rbac()->admin();
		$admin->assignUser($role, $user);

		if(!in_array($user, $assigned)) {
			$this->logger->debug('Adding new user sending mail notification...');
			$part->sendNotification($part->NOTIFY_ACCEPT_USER, $user);
            $favourites = new ilFavouritesManager();
            $favourites->add(
                $user,
                $course->getRefId()
            );
            $assigned[] = $user;
		}
	}


	/**
	 * @return int
	 */
	protected function getUserIdForImportId(?string $oid)
	{
		return ilObject::_lookupObjIdByImportId($oid);
	}
}