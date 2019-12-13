<?php

namespace Siro\Klaviyo;

use Exception;
use GuzzleHttp\Client;
use Siro\Klaviyo\Exceptions\ApiNotFoundException;

/**
 * Class KlaviyoAPI
 * @package Siro\Klaviyo
 *
 * @property KlaviyoEvent $event
 * @property KlaviyoList $list
 * @property KlaviyoProfile $profile
 * @property KlaviyoMetric $metric
 * @property KlaviyoTemplate $template
 * @property KlaviyoCampaign $campaign
 */
class KlaviyoAPI
{
    /**
     * @var
     */
    private $apiKey;

    /**
     * @var
     */
    private $client;
    const baseUrl = 'https://a.klaviyo.com';
    const apiBaseName = '\\Siro\\Klaviyo\\Klaviyo';

    /**
     * @var
     */
    private $apisAvailables = [
        'event',
        'list',
        'profile',
        'metric',
        'template',
        'campaign'
    ];

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = new Client(
            [
            'base_uri' => KlaviyoAPI::baseUrl,
            'timeout'  => 0,
            ]
        );
    }

    /**
     * @param $api
     * @return mixed
     * @throws ApiNotFoundException
     */
    public function __get($api)
    {
        if (property_exists($this, $api)) {
            return $this->$api;
        }

        if (!in_array($api, $this->apisAvailables)) {
            $message = 'Avaiable APIs: '. implode(', ', $this->apisAvailables);
            throw new ApiNotFoundException($message);
        }

        $apiClass = KlaviyoAPI::apiBaseName . ucfirst($api);
        $this->$api = new $apiClass($this->apiKey, $this->client);
        return $this->$api;
    }
}
