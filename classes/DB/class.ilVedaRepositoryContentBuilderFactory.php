<?php

class ilVedaRepositoryContentBuilderFactory implements ilVedaRepositoryContentBuilderFactoryInterface
{
    protected ilVedaSegmentBuilderFactoryInterface $sgmt_builder_factory;
    protected ilVedaMailSegmentBuilderFactoryInterface $ml_sgmt_builder_factory;
    protected ilVedaCourseBuilderFactoryInterface $crs_builder_factory;
    protected ilVedaUserBuilderFactoryInterface $usr_builder_factory;

    public function __construct(ilVedaRepositoryFactoryInterface $repo_factory, ilLogger $veda_logger)
    {
        $this->sgmt_builder_factory = new ilVedaSegmentBuilderFactory(
            $repo_factory->getSegmentRepository(),
            $veda_logger
        );
        $this->ml_sgmt_builder_factory = new ilVedaMailSegmentBuilderFactory(
            $repo_factory->getMailRepository(),
            $veda_logger
        );
        $this->crs_builder_factory = new ilVedaCourseBuilderFactory(
            $repo_factory->getCourseRepository(),
            $veda_logger
        );
        $this->usr_builder_factory = new ilVedaUserBuilderFactory(
            $repo_factory->getUserRepository(),
            $veda_logger
        );
    }

    public function getVedaSegmentBuilder() : ilVedaSegmentBuilderFactoryInterface
    {
        return $this->sgmt_builder_factory;
    }

    public function getMailSegmentBuilder() : ilVedaMailSegmentBuilderFactoryInterface
    {
        return $this->ml_sgmt_builder_factory;
    }

    public function getVedaCourseBuilder() : ilVedaCourseBuilderFactoryInterface
    {
        return $this->crs_builder_factory;
    }

    public function getVedaUserBuilder() : ilVedaUserBuilderFactoryInterface
    {
        return $this->usr_builder_factory;
    }
}
