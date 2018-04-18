<?php

namespace Siro\Klaviyo;

use Exceptions\ApiNotFoundException;

class KlaviyoAPI {
    private $apiKey;
    private $listApi = null;
    private $profileApi = null;
    private $metricApi = null;

    private $apiClasses = [
        'listApi' => '\\Siro\\Klaviyo\\KlaviyoList',
        'profileApi' => '\\Siro\\Klaviyo\\KlaviyoProfile',
        'metricApi' => '\\Siro\\Klaviyo\\KlaviyoMetric',
    ];

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function __get($api)
    {
        $apiType = $api .'Api';
        if (property_exists($this, $apiType)) {
            if ($this->$apiType !== null) {
                return $this->$apiType;
            }

            $this->$apiType = new $this->apiClasses[$apiType]($this->apiKey);
            return $this->$apiType;
        }
        // throw exception
        throw new ApiNotFoundException();
    }

}