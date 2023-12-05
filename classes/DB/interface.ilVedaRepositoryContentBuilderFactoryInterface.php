<?php

interface ilVedaRepositoryContentBuilderFactoryInterface
{
    public function getVedaSegmentBuilder() : ilVedaSegmentBuilderFactoryInterface;
    public function getMailSegmentBuilder() : ilVedaMailSegmentBuilderFactoryInterface;
    public function getVedaCourseBuilder() : ilVedaCourseBuilderFactoryInterface;
    public function getVedaUserBuilder() : ilVedaUserBuilderFactoryInterface;
}
