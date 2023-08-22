<?php

use OpenAPI\Client\Model\Dozentenkurszuordnung;

class ilVedaCourseTutorCollection implements ilVedaCourseTutorsCollectionInterface
{
    /**
     * @var Dozentenkurszuordnung[]
     */
    protected array $crs_tutors;
    protected int $index;

    /**
     * @param Dozentenkurszuordnung[] $crs_tutors
     */
    public function __construct(array $crs_tutors)
    {
        $this->crs_tutors = $crs_tutors;
        $this->index = 0;
    }

    public function containsTutorWithOID(string $oid): bool
    {
        foreach ($this->crs_tutors as $tutor) {
            if (
                !ilVedaUtils::compareOidsEqual($oid, $tutor->getElearningbenutzeraccountId()) ||
                !ilVedaUtils::isValidDate($tutor->getKursZugriffAb(), $tutor->getKursZugriffBis())
            ) {
                continue;
            }
            return true;
        }
        return false;
    }

    public function logContent(ilLogger $logger)
    {
        $logger->dump($this->crs_tutors, \ilLogLevel::DEBUG);
    }

    public function current() : Dozentenkurszuordnung
    {
        return $this->crs_tutors[$this->index];
    }

    public function next() : void
    {
        $this->index++;
    }

    public function key() : int
    {
        return $this->index;
    }

    public function valid() : bool
    {
        return 0 <= $this->index && $this->index < count($this->crs_tutors);
    }

    public function rewind() : void
    {
        $this->index = 0;
    }

    public function count() : int
    {
        return count($this->crs_tutors);
    }
}