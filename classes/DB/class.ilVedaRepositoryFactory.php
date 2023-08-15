<?php

class ilVedaRepositoryFactory
{
    protected ilDBInterface $il_db;
    protected ilLogger $veda_logger;

    public function __construct()
    {
        global $DIC;
        $this->il_db = $DIC->database();
        $this->veda_logger = $DIC->logger()->vedaimp();
    }

    public function getMailRepository() : ilVedaMailSegmentRepositoryInterface
    {
        return new ilVedaMailSegmentRepository($this->il_db, $this->veda_logger);
    }

    public function getCourseRepository() : ilVedaCourseRepositoryInterface
    {
        return new ilVedaCourseRepository($this->il_db, $this->veda_logger);
    }

    public function getUserRepository() : ilVedaUserRepositoryInterface
    {
        return new ilVedaUserRepository($this->il_db, $this->veda_logger);
    }

    public function getSegmentRepository() : ilVedaSegmentRepositoryInterface
    {
        return new ilVedaSegmentRepository($this->il_db);
    }

    public function getMDClaimingPluginRepository() : ilVedaMDClaimingPluginDBManagerInterface
    {
        return new ilVedaMDClaimingPluginDBManager(
            $this->veda_logger,
            $this->il_db,
            ilVedaConnectorPlugin::getInstance()->getClaimingPlugin()
        );
    }
}
