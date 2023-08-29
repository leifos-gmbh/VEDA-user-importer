<?php

class ilVedaUserBuilder implements ilVedaUserBuilderInterface
{
    protected const NULL_OID = '';

    protected ilVedaUser $veda_usr;
    protected ilVedaUserRepositoryInterface $usr_repo;
    protected ilLogger $veda_logger;

    public function __construct(
        ilVedaUserRepositoryInterface $usr_repo,
        ilLogger $veda_logger
    ) {
        $this->veda_usr = new ilVedaUser(self::NULL_OID);
        $this->usr_repo = $usr_repo;
        $this->veda_logger = $veda_logger;
    }

    public function withOID(string $oid, bool $load_from_db = true) : ilVedaUserBuilderInterface
    {
        $new_builder = new ilVedaUserBuilder($this->usr_repo, $this->veda_logger);

        $message = $load_from_db
            ? 'Looking for existing veda user with oid: ' . $oid
            : 'Skip looking for an existing veda user with oid: ' . $oid;
        $this->veda_logger->debug($message);

        $existing_crs = $load_from_db
            ? $this->usr_repo->lookupUserByOID($oid)
            : null;

        if (is_null($existing_crs)) {
            $this->veda_logger->debug('User with oid does not exist, or data base lookup skipped.');
            $new_builder->veda_usr = $this->veda_usr;
            $new_builder->veda_usr->setOid($oid);
        }
        if (!is_null($existing_crs)) {
            $this->veda_logger->debug('User with oid found');
            $new_builder->veda_usr = $existing_crs;
        }
        return $new_builder;
    }

    public function withLogin(string $login) : ilVedaUserBuilderInterface
    {
        $new_builder = new ilVedaUserBuilder($this->usr_repo, $this->veda_logger);
        $new_builder->veda_usr = $this->veda_usr;
        $new_builder->veda_usr->setLogin($login);
        return $new_builder;
    }

    public function withPasswordStatus(int $status) : ilVedaUserBuilderInterface
    {
        $new_builder = new ilVedaUserBuilder($this->usr_repo, $this->veda_logger);
        $new_builder->veda_usr = $this->veda_usr;
        $new_builder->veda_usr->setPasswordStatus($status);
        return $new_builder;
    }

    public function withCreationStatus(int $status) : ilVedaUserBuilderInterface
    {
        $new_builder = new ilVedaUserBuilder($this->usr_repo, $this->veda_logger);
        $new_builder->veda_usr = $this->veda_usr;
        $new_builder->veda_usr->setCreationStatus($status);
        return $new_builder;
    }

    public function withImportFailure(bool $value) : ilVedaUserBuilderInterface
    {
        $new_builder = new ilVedaUserBuilder($this->usr_repo, $this->veda_logger);
        $new_builder->veda_usr = $this->veda_usr;
        $new_builder->veda_usr->setImportFailure($value);
        return $new_builder;
    }

    public function get() : ilVedaUserInterface
    {
        return $this->veda_usr;
    }

    public function store() : void
    {
        $this->usr_repo->updateUser($this->veda_usr);
    }
}
