<?php

namespace Siro\Klaviyo;

use GuzzleHttp\RequestOptions;
use Exception;

/**
 * https://www.klaviyo.com/docs/api/lists
 */
class KlaviyoList extends ApiBase
{
    use KlaviyoResponse;

    private static $unsubscribeReasons = [
        'unsubscribed',
        'bounced',
        'invalid_email',
        'reported_spam',
        'manually_excluded'
    ];

    /**
     * Checks if the reason provided is valid.
     *
     * @param  string $reason The type of reason
     * @return bool
     */
    private function isValidUnsubscribeReason($reason)
    {
        return in_array($reason, self::$unsubscribeReasons);
    }

    /**
     * List objects represent standard (e.g. not dynamic) lists of people.
     * With lists, you can send campaigns and manage individual subscriptions.
     * GET /api/v2/lists
     *
     * @return object null if there is an error or a object with the response based
     *  in the result expected in the documentation page.
     */
    public function getLists()
    {
        $response = $this->client->get(
            "/api/v2/lists",
            [
                'form_params' => [
                    'api_key' => $this->apiKey,
                ]
            ]
        );

        return $this->sendResponseAsObject($response);
    }

    /**
     * Create a new list. Currently this resources only supports creating standard lists.
     *
     * @param string $name
     * POST /api/v2/lists
     */
    public function create($name)
    {
        $requestParams = [
            'form_params' => [
                'api_key' => $this->apiKey,
                'list_name' => $name,
            ]
        ];
        $response = $this->client->post("/api/v2/lists", $requestParams);

        return $this->sendResponseAsObject($response);
    }

    /**
     * Summary information for the list specified that includes
     * the name, ID, type, number of members, when it was created and last updated.
     *
     * GET /api/v2/list/{{ LIST_ID }}
     *
     * @param string $listId
     */
    public function get($listId)
    {
        $response = $this->client->get(
            "/api/v2/list/{$listId}",
            [
                'query' => [
                    'api_key' => $this->apiKey
                ]
            ]
        );

        return $this->sendResponseAsObject($response);
    }

    /**
     * Update details of the list. Currently this only support updating the
     * name of the list.
     *
     * PUT /api/v2/list/{{ LIST_ID }}
     *
     * @param  string $listId The list ID.
     * @param  string $name   New name.
     * @return mixed Null if the request fails or an stdclass object if is successful.
     */
    public function update($listId, $name)
    {
        $requestParams = [
            'form_params' => [
                'api_key' => $this->apiKey,
                'list_name' => $name
            ]
        ];
        $response = $this->client->put("/api/v2/list/{$listId}", $requestParams);

        return $this->sendResponseAsObject($response);
    }

    /**
     * PUT /api/v2/list/{{ LIST_ID }}
     */
    public function delete($listId)
    {
        $response = $this->client->delete(
            "/api/v2/list/{$listId}",
            [
                'query' => [
                    'api_key' => $this->apiKey
                ]
            ]
        );

        return $this->sendResponseAsObject($response);
    }

    /**
     * GET /api/v2/list/{$listId}/members
     */
    public function memberExistsInList($listId, $email)
    {
        $emails = $email;
        if (!is_array($email)) {
            $emails = [$email];
        }
        $options = [
            'form_params' => [
                'api_key' => $this->apiKey,
                'emails'   => $emails
            ]
        ];
        $response = $this->client->get("/api/v2/list/{$listId}/members", [
          RequestOptions::JSON => $options['form_params'],
          $options
        ]);

        return count($this->sendResponseAsObject($response)) === count($emails);
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
        $response = $this->client->get("/api/v2/segment/{$segmentId}/members", $options);

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
     * @param  string $id    The segment or list ID.
     * @param  mixed  $email The user email to check if exists.
     * @param  string $type  List or segment
     * @return bool   True if all emails are inside the list or segment.
     */
    public function memberExists($id, $email, $type = 'list')
    {
        if ($type === 'list') {
            return $this->memberExistsInList($id, $email);
        } elseif ($type === 'segment') {
            return $this->memberExistsInSegment($id, $email);
        } else {
            throw new Exception('type not allowed. Only "list" and "segment"');
        }
    }

    /**
     * Adds a new person to the specified list. If a person with that
     * email address does not already exist, a new person is first added to Klaviyo.
     * If someone is unsubscribed from the specified list, they will not be subscribed.
     * To re-subscribe someone, you must manually remove them from the
     * unsubscribe list in Klaviyo on the members page for the specified list.
     * POST /api/v2/list/{{ LIST_ID }}/members
     *
     * @param  string $listId     The id of the id.
     * @param  array  $profiles An array of properties such as names.
     * @return mixed             Null if the request fails or an stdclass object if is successful
     */
    public function addMember($listId, array $profiles)
    {
        foreach ($profiles as $profile) {
            if (!array_key_exists('email', $profile)) {
                throw new Exception('"email" key not found');
            }
        }
        $formParams = [
            'form_params' => [
                'api_key' => $this->apiKey,
                'profiles' => $profiles
            ],
        ];

        $response = $this->client->post("/api/v2/list/{$listId}/members", [
          RequestOptions::JSON => $formParams['form_params'],
          $formParams
        ]);
        return $this->sendResponseAsObject($response);
    }

    /**
     * Batch Removing People from a List
     * Removes multiple people from the specified list. For each person,
     * if a person with that email address is a member of that list,
     * they are removed.
     * DELETE /api/v2/list/{{ LIST_ID }}/members/batch
     *
     * @param  string $listId The list id.
     * @param  array  $emails The list of user emails to delete.
     * @return mixed Null if the request fails or an stdclass object if is successful.
     */
    public function deleteMembers($listId, array $emails)
    {
        $options = [
            'form_params' => [
                'api_key' => $this->apiKey,
                'emails'   => $emails
            ]
        ];
        $response = $this->client->delete("/api/v2/list/{$listId}/members", [
          RequestOptions::JSON => $options['form_params'],
          $options
        ]);

        return $this->sendResponseAsObject($response);
    }

    /**
     * POST https://a.klaviyo.com/api/v2/list/{LIST_ID}/subscribe
     */
    public function subscribe($listId, array $profiles)
    {
        foreach ($profiles as $profile) {
            if (!array_key_exists('email', $profile)) {
                throw new Exception('"email" key not found');
            }
        }
        $options = [
            'form_params' => [
                'api_key' => $this->apiKey,
                'profiles' => $profiles
            ]
        ];
        $response = $this->client->post("/api/v2/list/{$listId}/subscribe", [
            RequestOptions::JSON => $options['form_params'],
            $options
        ]);

        return $this->sendResponseAsObject($response);
    }

    /**
     * GET https://a.klaviyo.com/api/v2/list/{LIST_ID}/subscribe
     */
    public function checkSubscriptions($listId, array $emails)
    {
        $options = [
            'form_params' => [
                'api_key' => $this->apiKey,
                'emails'   => $emails
            ]
        ];

        $response = $this->client->get(
            "/api/v2/list/{$listId}/subscribe",
            [
                RequestOptions::JSON => $options['form_params'],
                $options
            ]
        );

        return $this->sendResponseAsObject($response);
    }

    /**
     * Unsubscribe and remove profiles from a list.
     * DELETE https://a.klaviyo.com/api/v2/list/{LIST_ID}/subscribe
     */
    public function unsubscribe($listId, array $emails)
    {
        $options = [
            'form_params' => [
                'api_key' => $this->apiKey,
                'emails'   => $emails
            ]
        ];

        $response = $this->client->delete(
            "/api/v2/list/{$listId}/subscribe",
            [
                RequestOptions::JSON => $options['form_params'],
                $options
            ]
        );

        return $this->sendResponseAsObject($response);
    }

    /**
     * Get all of the emails that have been excluded from a list along
     * with the exclusion reason and exclusion time.
     * This endpoint uses batching to return the records, so for a large list
     * multiple calls will need to be made to get all of the records.
     *
     * GET https://a.klaviyo.com/api/v2/list/{LIST_ID}/exclusions/all
     */
    public function getAllExclusions($listId, $marker = null)
    {
        $options = [
            'form_params' => [
                'api_key' => $this->apiKey
            ]
        ];

        if (!is_null($marker)) {
            $options['form_params']['marker'] = (int) $marker;
        }

        $response = $this->client->get(
            "/api/v2/list/{$listId}/exclusions/all",
            [
                RequestOptions::JSON => $options['form_params'],
                $options
            ]
        );

        return $this->sendResponseAsObject($response);
    }

    /**
     * GET /api/v2/group/{LIST_ID or SEGMENT_ID}/members/all
     */
    public function getListMembers($listId, $marker = null)
    {
        $formParams = [
            'form_params' => [
                'api_key' => $this->apiKey
            ]
        ];

        if ($marker) {
            $formParams['form_params']['marker'] = $marker;
        }

        $response = $this->client->get(
            "/api/v2/group/{$listId}/members/all",
            $formParams
        );
        return $this->sendResponseAsObject($response);
    }
}
