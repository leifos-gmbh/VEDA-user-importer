<?php

use OpenAPI\Client\Model\Teilnehmerkurszuordnung;

class ilVedaCourseMemberCollection implements ilVedaCourseMemberCollectionInterface
{
    protected array $crs_mmbrs;
    protected int $index;

    /**
     * @param Teilnehmerkurszuordnung[] $crs_mmbrs
     */
    public function __construct(array $crs_mmbrs)
    {
        $this->crs_mmbrs = $crs_mmbrs;
        $this->index = 0;
    }

    /**
     * @throws ilDateTimeException
     */
    public function containsMemberWithOID(string $oid) : bool
    {
        foreach ($this->crs_mmbrs as $member) {
            if (
                !ilVedaUtils::compareOidsEqual($oid, $member->getTeilnehmerId()) ||
                !ilVedaUtils::isValidDate($member->getKursZugriffAb(), $member->getKursZugriffBis())
            ) {
                continue;
            }
            return true;
        }
        return false;
    }

    public function logContent(ilLogger $logger)
    {
        $logger->dump($this->crs_mmbrs, ilLogLevel::DEBUG);
    }

    public function current() : Teilnehmerkurszuordnung
    {
        return $this->crs_mmbrs[$this->index];
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
        return 0 <= $this->index && $this->index < count($this->crs_mmbrs);
    }

    public function rewind() : void
    {
        $this->index = 0;
    }

    public function count() : int
    {
        return count($this->crs_mmbrs);
    }
}
