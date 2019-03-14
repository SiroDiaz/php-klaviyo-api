<?php

namespace Siro\Klaviyo;

use GuzzleHttp\Client;

abstract class ApiBase
{
    /**
     * @var
     */
    protected $apiKey;

    /**
     * @var
     */
    protected $client;

    /**
     * ApiBase constructor.
     *
     * @param $apiKey
     * @param Client $client
     */
    public function __construct($apiKey, Client $client)
    {
        $this->apiKey = $apiKey;
        $this->client = $client;
    }
}
