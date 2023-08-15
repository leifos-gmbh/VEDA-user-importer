<?php

class ilVedaApiFactory
{
    public function getVedaClientApi(): ilVedaApiInterface
    {
        return new ilVedaOpenApi();
    }
}