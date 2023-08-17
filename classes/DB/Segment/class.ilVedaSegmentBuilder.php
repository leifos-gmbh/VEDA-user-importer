<?php

class ilVedaSegmentBuilder implements ilVedaSegmentBuilderInterface
{
    protected const NULL_OID = '';

    protected ilVedaSegmentRepositoryInterface $sgmt_repo;
    protected ilLogger $veda_logger;
    protected ilVedaSegment $veda_sgmt;

    public function __construct(
        ilVedaSegmentRepositoryInterface $sgmt_repo,
        ilLogger $veda_logger
    ) {
        $this->sgmt_repo = $sgmt_repo;
        $this->veda_logger = $veda_logger;
        $this->veda_sgmt = new ilVedaSegment(self::NULL_OID);
    }

    public function withOID(string $oid, bool $load_from_db = true): ilVedaSegmentBuilderInterface
    {
        $new_builder = new ilVedaSegmentBuilder($this->sgmt_repo, $this->veda_logger);

        $message = $load_from_db
            ? 'Looking for existing veda course with oid: ' . $oid
            : 'Skip looking for an existing veda cours with oid: ' . $oid;

        $this->veda_logger->debug($message);

        $existing_crs = $load_from_db
            ? $this->sgmt_repo->lookupSegmentInfo($oid)
            : null;

        if (is_null($existing_crs)) {
            $this->veda_logger->debug('Course with id does not exist, or data base lookup skipped.');
            $new_builder->veda_sgmt = $this->veda_sgmt;
            $new_builder->veda_sgmt->setOid($oid);
        }
        if (!is_null($existing_crs)) {
            $this->veda_logger->debug('Course with id found');
            $new_builder->veda_sgmt = $existing_crs;
        }
        return $new_builder;
    }

    public function withType(string $type): ilVedaSegmentBuilderInterface
    {
        $new_builder = new ilVedaSegmentBuilder($this->sgmt_repo, $this->veda_logger);
        $new_builder->veda_sgmt = $this->veda_sgmt;
        $new_builder->veda_sgmt->setType($type);
        return $new_builder;
    }

    public function get(): ilVedaSegmentInterface
    {
        return $this->veda_sgmt;
    }

    public function store(): void
    {
        $this->sgmt_repo->updateSegmentInfo($this->veda_sgmt);
    }
}