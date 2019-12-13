<?php

namespace Siro\Klaviyo;

use GuzzleHttp\Client;

/**
 * https://www.klaviyo.com/docs/http-api
 */
class KlaviyoEvent extends ApiBase
{
    use KlaviyoResponse;

    /**
     * The main Events API endpoint is /api/track, which is used to track
     * when someone takes an action or does something. It encodes the following data in a dictionary or hash.
     * GET /api/track
     *
     * @param string $event              The event name. For example, 'register'.
     * @param array  $customerProperties An array containing the email (client email).
     * @param array  $properties         An array containing all extra data of the client, as
     *                                   name, surname, language, city, etc.

     * @param  mixed  $timestamp          the time in UNIX timestamp format. null by default.
     * @return object
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

        $response = $this->client->get(
            '/api/track',
            [
            'query' => [
                'data' => base64_encode(json_encode($data))
            ]
            ]
        );
        
        return $this->sendResponseAsObject($response);
    }

    /**
     * Asynchronous track event version.
     * GET /api/track
     *
     * @param string $event The event name. For example, 'register'.
     * @param array $customerProperties An array containing the email (client email).
     * @param array $properties An array containing all extra data of the client, as
     *                                   name, surname, language, city, etc.
     * @param  mixed $timestamp the time in UNIX timestamp format. null by default.
     * @return \GuzzleHttp\Promise\PromiseInterface a promise than must be treated.
     */
    public function trackAsync($event, array $customerProperties, array $properties, $timestamp = null)
    {
        $data = [
            'token' => $this->apiKey,
            'event' => $event,
            'customer_properties' => $customerProperties,
            'properties' => $properties,
            'time' => $timestamp
        ];

        return $this->client->getAsync(
            '/api/track',
            [
            'query' => [
                'data' => base64_encode(json_encode($data))
            ]
            ]
        );
    }

    /**
     * The Identify API endpoint is /api/identify, which is used to track properties
     * about an individual without tracking an associated event.
     * It encodes the following data in a dictionary or hash.
     * GET /api/identify
     *
     * @param  array $properties
     * @return object
     * @throws \Exception
     */
    public function identify(array $properties)
    {
        if ((!array_key_exists('$email', $properties) || empty($properties['$email']))
            && (!array_key_exists('$id', $properties) || empty($properties['$id']))
        ) {
            throw new \Exception('You must identify a user by email or ID.');
        }
        $data = [
            'token' => $this->apiKey,
            'properties' => $properties
        ];

        $response = $this->client->get(
            '/api/identify',
            [
            'query' => [
                'data' => base64_encode(json_encode($data))
            ]
            ]
        );

        return $this->sendResponseAsObject($response);
    }

    /**
     * The Identify API endpoint in a asynchronous version.
     * GET /api/identify.
     *
     * @param  array $properties an array of properties listed in https://www.klaviyo.com/docs/http-api.
     * @return GuzzleHttp\Promise\PromiseInterface a promise than must be treated.
     * @throws \Exception
     */
    public function identifyAsync(array $properties)
    {
        if ((!array_key_exists('$email', $properties) || empty($properties['$email']))
            && (!array_key_exists('$id', $properties) || empty($properties['$id']))
        ) {
            throw new \Exception('You must identify a user by email or ID.');
        }
        $data = [
            'token' => $this->apiKey,
            'properties' => $properties
        ];

        return $this->client->getAsync(
            '/api/identify',
            [
            'query' => [
                'data' => base64_encode(json_encode($data))
            ]
            ]
        );
    }
}
