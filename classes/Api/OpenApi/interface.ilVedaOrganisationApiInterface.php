<?php

use OpenAPI\Client\Model\Organisation;

interface ilVedaOrganisationApiInterface
{
    public function getOrganisation(string $orgr_oid) : Organisation;
}
