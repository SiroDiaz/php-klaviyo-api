<?php

namespace Siro\Klaviyo;

use GuzzleHttp\Client;

class KlaviyoEvent extends KlaviyoResponse
{
    private $apiKey;
    private $client;
    private static $unsubscribeReasons = [
        'unsubscribed',
        'bounced',
        'invalid_email',
        'reported_spam',
        'manually_excluded'
    ];

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = new Client([
            'base_uri' => KlaviyoAPI::$baseUrl,
            'timeout'  => 2.0,
        ]);
    }

    /**
     * GET /api/track
     */
    public function track($event, array $customerProperties, array $properties, $timestamp = null)
    {
        $data = [
            'token' => $this->apiKey,
            'event' => $event,
            'customer_properties' => $customerProperties,
            'properties' => $properties,
            'time' => $timestamp
        ];

        $response = $this->client->get('/api/track', [
            'query' => [
                'data' => base64_encode(json_encode($data))
            ]
        ]);
        
        return $this->sendResponseAsObject($response);
    }
}
