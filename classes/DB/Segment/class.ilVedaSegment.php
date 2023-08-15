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
    protected bool $is_self_learning;
    protected bool $is_practical_training;

    /**
     * ilVedaSegmentInfo constructor.
     */
    public function __construct(
        string $oid,
        string $type = '',
        bool $is_self_learning = false,
        bool $is_practical_training = false
    ) {
        $this->type = $type;
        $this->oid = $oid;
        $this->is_self_learning = $is_self_learning;
        $this->is_practical_training = $is_practical_training;
    }

    /**
     * @throws ilDatabaseException
     */
    public function isPracticalTraining() : bool
    {
        return $this->type === ilVedaSegmentType::PRAKTIKUM;
    }

    /**
     * @throws ilDatabaseException
     */
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
