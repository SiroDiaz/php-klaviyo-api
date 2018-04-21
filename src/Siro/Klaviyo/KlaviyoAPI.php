<?php

namespace Siro\Klaviyo;

use Exception;
use GuzzleHttp\Client;

class KlaviyoAPI
{
    private $apiKey;
    private $client;
    private $eventApi = null;
    private $listApi = null;
    private $profileApi = null;
    private $metricApi = null;
    public static $baseUrl = 'https://a.klaviyo.com';

    private $apiClasses = [
        'eventApi'   => '\\Siro\\Klaviyo\\KlaviyoEvent',
        'listApi'    => '\\Siro\\Klaviyo\\KlaviyoList',
        'profileApi' => '\\Siro\\Klaviyo\\KlaviyoProfile',
        'metricApi'  => '\\Siro\\Klaviyo\\KlaviyoMetric',
    ];

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = new Client([
            'base_uri' => self::$baseUrl,
            'timeout'  => 2.0,
        ]);
    }

    public function __get($api)
    {
        $apiType = $api .'Api';
        if (property_exists($this, $apiType)) {
            if ($this->$apiType !== null) {
                return $this->$apiType;
            }

            $this->$apiType = new $this->apiClasses[$apiType]($this->apiKey, $this->client);
            return $this->$apiType;
        }
        // throw exception
        throw new Exception("$api api is not defined");
    }
}
