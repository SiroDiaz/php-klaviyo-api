<?php

namespace Siro\Klaviyo;

use GuzzleHttp\Client;

class KlaviyoList
{
    private $apiKey;
    private $client;
    private $baseUrl = 'https://a.klaviyo.com/api/v1';

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = new Client([
            'base_uri' => '',
            'timeout'  => 2.0,
        ]);
    }

    /**
     * GET /api/v1/lists
     */
    public function getLists($type = null, $page = 0, $count = 20)
    {
        $response = $this->client->get($this->baseUrl .'/lists', [
            'query' => [
                'api_key' => $this->apiKey,
                'type' => $type,
                'page' => $page,
                'count' => $count
            ]
        ]);

        if ($response->getStatusCode() === 200) {
            return json_decode((string) $response->getBody());
        }

        return null;
    }

    /**
     * POST /api/v1/lists
     */
    public function create($name, $listType = 'standard')
    {
        $requestParams = [
            'form_params' => [
                'api_key' => $this->apiKey,
                'name' => $name,
                'list_type' => $listType,
            ]
        ];
        $response = $this->client->post($this->baseUrl . '/lists', $requestParams);
        
        if ($response->getStatusCode() === 200) {
            return json_decode((string) $response->getBody());
        }
        
        return null;
    }

    /**
     * GET /api/v1/list/{{ LIST_ID }}
     */
    public function get($listId)
    {
        $response = $this->client->get("{$this->baseUrl}/list/{$listId}", [
            'query' => [
                'api_key' => $this->apiKey
            ]
        ]);

        if ($response->getStatusCode() === 200) {
            return json_decode((string) $response->getBody());
        }

        return null;
    }

    /**
     * PUT /api/v1/list/{{ LIST_ID }}
     */
    public function update($listId, $name)
    {
        $requestParams = [
            'form_params' => [
                'api_key' => $this->apiKey,
                'name' => $name
            ]
        ];
        $response = $this->client->put("{$this->baseUrl}/list/{$listId}", $requestParams);
        
        if ($response->getStatusCode() === 200) {
            return json_decode((string) $response->getBody());
        }
        
        return null;
    }

    /**
     * PUT /api/v1/list/{{ LIST_ID }}
     */
    public function delete($listId)
    {
        $response = $this->client->delete("{$this->baseUrl}/list/{$listId}", [
            'query' => [
                'api_key' => $this->apiKey
            ]
        ]);

        if ($response->getStatusCode() === 200) {
            return json_decode((string) $response->getBody());
        }

        return null;
    }

    /**
     * GET /api/v1/list/{{ LIST_ID }}/members
     */
    public function memberExistsInList($listId, $email)
    {
        $emailFormat = $email;
        if (is_array($email)) {
            $emailFormat = implode(',', $email);
        }
        $options = [
            'query' => [
                'api_key' => $this->apiKey,
                'email'   => $emailFormat
            ]
        ];
        $response = $this->client->get("{$this->baseUrl}/list/{$listId}/members", $options);

        if ($response->getStatusCode() === 200) {
            $resObj = json_decode((string) $response->getBody());
            if (is_array($email)) {
                return $resObj->total === count($email);
            }
            
            return (bool) $resObj->total;
        }

        return null;
    }

    /**
     * GET /api/v1/segment/{{ SEGMENT_ID }}/members
     */
    public function memberExistsInSegment($segmentId, $email)
    {
        $emailFormat = $email;
        if (is_array($email)) {
            $emailFormat = implode(',', $email);
        }
        $options = [
            'query' => [
                'api_key' => $this->apiKey,
                'email'   => $emailFormat
            ]
        ];
        $response = $this->client->get("{$this->baseUrl}/segment/{$segmentId}/members", $options);

        if ($response->getStatusCode() === 200) {
            $resObj = json_decode((string) $response->getBody());
            if (is_array($email)) {
                return $resObj->total === count($email);
            }
            
            return (bool) $resObj->total;
        }

        return null;
    }

    /**
     * Checks if the email/s are submited in a list or segment
     *
     * @param string $id
     * @param mixed $email
     * @param string $type List or segment
     *
     * @return bool True if all emails are inside the list or segment.
     */
    public function memberExists($id, $email, $type = 'list')
    {
        return true;
    }

    /**
     * POST /api/v1/list/{{ LIST_ID }}/members
     */
    public function addMember($listId, $email, array $properties, $confirm = true)
    {
        $formParams = [
            'form_params' => [
                'api_key' => $this->apiKey,
                'email' => $email,
                'confirm_optin' => $confirm,
            ]
        ];

        if (count($properties) > 0) {
            $formParams['form_params']['properties'] = json_encode($properties);
        }
        $response = $this->client->post("{$this->baseUrl}/list/{$listId}/members", $formParams);
        if ($response->getStatusCode() === 200) {
            return json_decode((string) $response->getBody());
        }

        return null;
    }

    /**
     * POST /api/v1/list/{{ LIST_ID }}/members/batch
     */
    public function addMembers($listId, array $users, $confirm = true)
    {
        if (!count($users)) {
            //TODO throw exception
            return null;
        }

        foreach ($users as $user) {
            if (!array_key_exists('email', $user)) {
                //TODO throw exception
                return null;
            }
        }

        $formParams = [
            'form_params' => [
                'api_key' => $this->apiKey,
                'batch' => json_encode($users),
                'confirm_optin' => $confirm,
            ]
        ];
        $response = $this->client->post("{$this->baseUrl}/list/{$listId}/members/batch", $formParams);
        if ($response->getStatusCode() === 200) {
            return json_decode((string) $response->getBody());
        }

        return null;
    }

    /**
     * DELETE /api/v1/list/{{ LIST_ID }}/members/batch
     */
    public function deleteMembers($listId, array $emails)
    {
        $emailsFormat = [];
        foreach ($emails as $email) {
            array_push($emailsFormat, ['email' => $email]);
        }
        
        $formParams = [
            'form_params' => [
                'api_key' => $this->apiKey,
                'batch'   => json_encode($emailsFormat)
            ]
        ];

        $response = $this->client->delete("{$this->baseUrl}/list/{$listId}/members", $formParams);
        if ($response->getStatusCode() === 200) {
            return json_decode((string) $response->getBody());
        }

        return null;
    }
}
