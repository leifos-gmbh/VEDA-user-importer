<?php

use OpenAPI\Client\Model\Lernbegleiterkurszuordnung;

class ilVedaCourseCompanionCollection implements ilVedaCourseCompanionCollectionInterface
{
    /**
     * @var Lernbegleiterkurszuordnung[]
     */
    protected array $crs_supervisors;
    protected int $index;

    /**
     * @param Lernbegleiterkurszuordnung[] $crs_supervisors
     */
    public function __construct(array $crs_supervisors)
    {
        $this->crs_supervisors = $crs_supervisors;
        $this->index = 0;
    }

    /**
     * @throws ilDateTimeException
     */
    public function containsCompanionWithOID(string $oid) : bool
    {
        foreach ($this->crs_supervisors as $supervisor) {
            if (
                !ilVedaUtils::compareOidsEqual($oid, $supervisor->getElearningbenutzeraccountId()) ||
                !ilVedaUtils::isValidDate($supervisor->getKursZugriffAb(), $supervisor->getKursZugriffBis())
            ) {
                continue;
            }
            return true;
        }
        return false;
    }

    public function logContent(ilLogger $logger)
    {
        $logger->dump($this->crs_supervisors, ilLogLevel::DEBUG);
    }

    public function current() : Lernbegleiterkurszuordnung
    {
        return $this->crs_supervisors[$this->index];
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
        return 0 <= $this->index && $this->index < count($this->crs_supervisors);
    }

    public function rewind() : void
    {
        $this->index = 0;
    }

    public function count() : int
    {
        return count($this->crs_supervisors);
    }
}
