<?php

class ilVedaCourseBuilder implements ilVedaCourseBuilderInterface
{
    protected const NULL_OID = '';
    protected ilVedaCourseInterface $veda_crs;
    protected ilVedaCourseRepositoryInterface $crs_repo;
    protected ilLogger $veda_logger;

    public function __construct(
        ilVedaCourseRepositoryInterface $crs_repo,
        ilLogger $veda_logger
    ) {
        $this->veda_crs = new ilVedaCourse(self::NULL_OID);
        $this->crs_repo = $crs_repo;
        $this->veda_logger = $veda_logger;
    }

    public function withOID(string $oid, bool $load_from_db = true) : ilVedaCourseBuilderInterface
    {
        $new_builder = new ilVedaCourseBuilder($this->crs_repo, $this->veda_logger);

        $message = $load_from_db
            ? 'Looking for existing veda course with oid: ' . $oid
            : 'Skip looking for an existing veda cours with oid: ' . $oid;

        $this->veda_logger->debug($message);

        $existing_crs = $load_from_db
            ? $this->crs_repo->lookupCourseByOID($oid)
            : null;

        if (is_null($existing_crs)) {
            $this->veda_logger->debug('Course with id does not exist, or data base lookup skipped.');
            $new_builder->veda_crs = $this->veda_crs;
            $new_builder->veda_crs->setOid($oid);
        }
        if (!is_null($existing_crs)) {
            $this->veda_logger->debug('Course with id found');
            $new_builder->veda_crs = $existing_crs;
        }
        return $new_builder;
    }

    public function withObjID(int $obj_id) : ilVedaCourseBuilderInterface
    {
        $new_builder = new ilVedaCourseBuilder($this->crs_repo, $this->veda_logger);
        $new_builder->veda_crs = $this->veda_crs;
        $new_builder->veda_crs->setObjId($obj_id);
        return $new_builder;
    }

    public function withSwitchPermanentRole(int $role_id) : ilVedaCourseBuilderInterface
    {
        $new_builder = new ilVedaCourseBuilder($this->crs_repo, $this->veda_logger);
        $new_builder->veda_crs = $this->veda_crs;
        $new_builder->veda_crs->setPermanentSwitchRole($role_id);
        return $new_builder;
    }

    public function withSwithTemporaryRole(int $role_id) : ilVedaCourseBuilderInterface
    {
        $new_builder = new ilVedaCourseBuilder($this->crs_repo, $this->veda_logger);
        $new_builder->veda_crs = $this->veda_crs;
        $new_builder->veda_crs->setTemporarySwitchRole($role_id);
        return $new_builder;
    }

    public function withStatusCreated(int $status) : ilVedaCourseBuilderInterface
    {
        $new_builder = new ilVedaCourseBuilder($this->crs_repo, $this->veda_logger);
        $new_builder->veda_crs = $this->veda_crs;
        $new_builder->veda_crs->setCreationStatus($status);
        return $new_builder;
    }

    public function withModified(int $modified) : ilVedaCourseBuilderInterface
    {
        $new_builder = new ilVedaCourseBuilder($this->crs_repo, $this->veda_logger);
        $new_builder->veda_crs = $this->veda_crs;
        $new_builder->veda_crs->setModified($modified);
        return $new_builder;
    }

    public function withType(int $type) : ilVedaCourseBuilderInterface
    {
        $new_builder = new ilVedaCourseBuilder($this->crs_repo, $this->veda_logger);
        $new_builder->veda_crs = $this->veda_crs;
        $new_builder->veda_crs->setType($type);
        return $new_builder;
    }

    public function withDocumentSuccess(bool $value) : ilVedaCourseBuilderInterface
    {
        $new_builder = new ilVedaCourseBuilder($this->crs_repo, $this->veda_logger);
        $new_builder->veda_crs = $this->veda_crs;
        $new_builder->veda_crs->setDocumentSuccess($value);
        return $new_builder;
    }

    public function store() : void
    {
        $this->veda_logger->debug('Updating veda course');
        if ($this->veda_crs->getOid() === self::NULL_OID) {
            $this->veda_logger->debug('Cannot update veda course with null id');
            return;
        }
        $this->crs_repo->updateCourse($this->veda_crs);
    }

    public function get() : ilVedaCourseInterface
    {
        return $this->veda_crs;
    }
}
