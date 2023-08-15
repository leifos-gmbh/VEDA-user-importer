<?php

class ilVedaCourseBuilderFactory implements ilVedaCourseBuilderFactoryInterface
{
    protected ilVedaCourseRepositoryInterface $crs_repo;
    protected ilLogger $veda_logger;

    public function __construct(
        ilVedaCourseRepositoryInterface $crs_repo,
        ilLogger $veda_logger
    ) {
        $this->crs_repo = $crs_repo;
        $this->veda_logger = $veda_logger;
    }

    public function buildCourse(): ilVedaCourseBuilderInterface
    {
        return new ilVedaCourseBuilder($this->crs_repo, $this->veda_logger);
    }
}