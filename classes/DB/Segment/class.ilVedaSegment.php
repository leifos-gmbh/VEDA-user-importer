<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Stores segment (train) info
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilVedaSegment implements ilVedaSegmentInterface
{
    protected string $type;
    protected string $oid;

    /**
     * ilVedaSegmentInfo constructor.
     */
    public function __construct(
        string $oid,
        string $type = ''
    ) {
        $this->type = $type;
        $this->oid = $oid;
    }

    public function isPracticalTraining() : bool
    {
        return $this->type === ilVedaSegmentType::PRAKTIKUM;
    }

    public function isSelfLearning() : bool
    {
        return $this->type === ilVedaSegmentType::SELF_LEARNING;
    }

    public function setOID(string $oid) : void
    {
        $this->oid = $oid;
    }

    public function getOID() : string
    {
        return $this->oid;
    }

    public function setType(string $type) : void
    {
        $this->type = $type;
    }

    public function getType() : string
    {
        return $this->type;
    }
}
