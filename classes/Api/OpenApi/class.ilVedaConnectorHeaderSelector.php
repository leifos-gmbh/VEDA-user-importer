<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use OpenApi\Client\Configuration;
use OpenApi\Client\HeaderSelector;

/**
 * Custom header selector, to add authentication token header
 */
class ilVedaConnectorHeaderSelector extends HeaderSelector
{
    private ?Configuration $config = null;
    private ?ilVedaConnectorSettings $settings = null;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
        $this->settings = ilVedaConnectorSettings::getInstance();
    }

    public function selectHeaders(array $accept, string $contentType, bool $isMultipart) : array
    {
        $headers = parent::selectHeaders($accept, $contentType, $isMultipart);
        $headers[ilVedaConnectorSettings::HEADER_TOKEN] = $this->config->getAccessToken();
        if ($this->settings->isAddHeaderAuthEnabled()) {
            $headers[$this->settings->getAddHeaderName()] = $this->settings->getAddHeaderValue();
        }
        return $headers;
    }
}
