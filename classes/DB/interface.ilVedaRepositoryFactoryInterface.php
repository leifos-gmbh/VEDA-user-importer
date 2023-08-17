<?php

interface ilVedaRepositoryFactoryInterface
{
    public function getMailRepository() : ilVedaMailSegmentRepositoryInterface;

    public function getCourseRepository() : ilVedaCourseRepositoryInterface;

    public function getUserRepository() : ilVedaUserRepositoryInterface;

    public function getSegmentRepository() : ilVedaSegmentRepositoryInterface;

    public function getMDClaimingPluginRepository() : ilVedaMDClaimingPluginDBManagerInterface;
}