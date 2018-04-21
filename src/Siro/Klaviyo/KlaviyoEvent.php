<?php

namespace Siro\Klaviyo;

use GuzzleHttp\Client;

/**
 * https://www.klaviyo.com/docs/http-api
 */
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
     *
     * @param string $event The event name. For example, 'register'.
     * @param array $customerProperties An array containing the email (client email).
     * @param array $properties An array containing all extra data of the client, as
     *  name, surname, language, city, etc.
     * @param mixed $timestamp the time in UNIX timestamp format. null by default.
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

    /**
     * GET /api/identify
     */
    public function identify(array $properties)
    {
        if ((!array_key_exists('$email', $properties) || empty($properties['$email']))
            && (!array_key_exists('$id', $properties) || empty($properties['$id']))) {
            throw new \Exception('You must identify a user by email or ID.');
        }
        $data = [
            'token' => $this->apiKey,
            'properties' => $properties
        ];

        $response = $this->client->get('/api/indentify', [
            'query' => [
                'data' => base64_encode(json_encode($data))
            ]
        ]);

        return $this->sendResponseAsObject($response);
    }
}
