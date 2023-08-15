<?php

class ilVedaUserBuilderFactory implements ilVedaUserBuilderFactoryInterface
{
    protected ilVedaUserRepositoryInterface $usr_repo;
    protected ilLogger $veda_logger;

    public function __construct(
        ilVedaUserRepositoryInterface $usr_repo,
        ilLogger $veda_logger
    ) {
        $this->usr_repo = $usr_repo;
        $this->veda_logger = $veda_logger;
    }

    public function buildUser(): ilVedaUserBuilderInterface
    {
        return new ilVedaUserBuilder($this->usr_repo, $this->veda_logger);
    }
}