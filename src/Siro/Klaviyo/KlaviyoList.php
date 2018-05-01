<?php

namespace Siro\Klaviyo;

use GuzzleHttp\Client;

/**
 *
 */
class KlaviyoList extends KlaviyoResponse
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

    public function __construct($apiKey, $client)
    {
        $this->apiKey = $apiKey;
        $this->client = $client;
    }

    /**
     * Checks if the reason provided is valid.
     *
     * @param string $reason The type of reason
     */
    private function isValidUnsubscribeReason($reason)
    {
        return in_array($reason, self::$unsubscribeReasons);
    }

    /**
     * List objects represent standard (e.g. not dynamic) lists of people.
     * With lists, you can send campaigns and manage individual subscriptions.
     * GET /api/v1/lists
     *
     * @param mixed $type null or string. Valid options are 'list' or 'segment'
     * @param int $page For pagination. By default 0 (first result page)
     * @param int $count For pagination, the number of results to return.
     *  The maximum number is 100.
     * @return object null if there is an error or a object with the response based
     *  in the result expected in the documentation page.
     */
    public function getLists($type = null, $page = 0, $count = 50)
    {
        $response = $this->client->get("/api/v1/lists", [
            'query' => [
                'api_key' => $this->apiKey,
                'type' => $type,
                'page' => $page,
                'count' => $count
            ]
        ]);

        return $this->sendResponseAsObject($response);
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
        $response = $this->client->post("/api/v1/lists", $requestParams);
        
        return $this->sendResponseAsObject($response);
    }

    /**
     * GET /api/v1/list/{{ LIST_ID }}
     */
    public function get($listId)
    {
        $response = $this->client->get("/api/v1/list/{$listId}", [
            'query' => [
                'api_key' => $this->apiKey
            ]
        ]);

        return $this->sendResponseAsObject($response);
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
        $response = $this->client->put("/api/v1/list/{$listId}", $requestParams);
        
        return $this->sendResponseAsObject($response);
    }

    /**
     * PUT /api/v1/list/{{ LIST_ID }}
     */
    public function delete($listId)
    {
        $response = $this->client->delete("/api/v1/list/{$listId}", [
            'query' => [
                'api_key' => $this->apiKey
            ]
        ]);

        return $this->sendResponseAsObject($response);
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
        $response = $this->client->get("/api/v1/list/{$listId}/members", $options);

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
        $response = $this->client->get("/api/v1/segment/{$segmentId}/members", $options);

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
        if ($type === 'list') {
            return $this->memberExistsInList($id, $email);
        } else if ($type === 'segment') {
            return $this->memberExistsInSegment($id, $email);
        } else {
            throw new \Exception('type not allowed. Only "list" and "segment"');
        }
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
        $response = $this->client->post("/api/v1/list/{$listId}/members", $formParams);
        return $this->sendResponseAsObject($response);
    }

    /**
     * POST /api/v1/list/{{ LIST_ID }}/members/batch
     */
    public function addMembers($listId, array $users, $confirm = true)
    {
        if (!count($users)) {
            return false;
        }

        foreach ($users as $user) {
            if (!array_key_exists('email', $user)) {
                throw new \Exception('"email" key not found');
            }
        }

        $formParams = [
            'form_params' => [
                'api_key' => $this->apiKey,
                'batch' => json_encode($users),
                'confirm_optin' => $confirm,
            ]
        ];
        $response = $this->client->post("/api/v1/list/{$listId}/members/batch", $formParams);
        return $this->sendResponseAsObject($response);
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

        $response = $this->client->delete("/api/v1/list/{$listId}/members/batch", $formParams);
        return $this->sendResponseAsObject($response);
    }

    /**
     * POST /api/v1/list/{{ LIST_ID }}/members/exclude
     */
    public function unsubscribe($listId, $email, $timestamp = -1)
    {
        $formParams = [
            'form_params' => [
                'api_key' => $this->apiKey,
                'email'   => $email
            ]
        ];

        if ($timestamp !== -1) {
            $formParams['form_params']['timestamp'] = $timestamp;
        }

        $response = $this->client->post(
            "/api/v1/list/{$listId}/members/exclude",
            $formParams
        );
        return $this->sendResponseAsObject($response);
    }

    /**
     * GET /api/v1/list/{{ LIST_ID }}/exclusions
     */
    public function getListExclusions($listId, $reason = 'unsubscribed', $sort = 'asc', $page = 0, $count = 100)
    {
        if (!$this->isValidUnsubscribeReason($reason)) {
            throw new \Exception('Invalid unsubscribe reason');
        }
        
        $options = [
            'query' => [
                'api_key' => $this->apiKey,
                'reason'  => $reason,
                'sort'    => $sort,
                'page'    => $page,
                'count'   => $count
            ]
        ];
        $response = $this->client->get("/api/v1/list/{$listId}/exclusions", $options);

        return $this->sendResponseAsObject($response);
    }

    /**
     * GET /api/v1/people/exclusions
     */
    public function getExclusions($reason = 'unsubscribed', $sort = 'asc', $page = 0, $count = 100)
    {
        if (!$this->isValidUnsubscribeReason($reason)) {
            throw new \Exception('Invalid unsubscribe reason');
        }
        
        $options = [
            'query' => [
                'api_key' => $this->apiKey,
                'reason'  => $reason,
                'sort'    => $sort,
                'page'    => $page,
                'count'   => $count
            ]
        ];
        $response = $this->client->get("/api/v1/people/exclusions", $options);

        return $this->sendResponseAsObject($response);
    }

    /**
     * POST /api/v1/people/exclusions
     */
    public function excludeFromAll($email, $timestamp = -1)
    {
        $formParams = [
            'form_params' => [
                'api_key' => $this->apiKey,
                'email'   => $email
            ]
        ];

        if ($timestamp !== -1) {
            $formParams['form_params']['timestamp'] = $timestamp;
        }

        $response = $this->client->post(
            "/api/v1/list/{$listId}/people/exclusions",
            $formParams
        );
        return $this->sendResponseAsObject($response);
    }
}
