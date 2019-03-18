<?php

namespace Siro\Klaviyo;

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
     * GET /api/v1/lists
     *
     * @param mixed $type  null or string. Valid options are 'list' or 'segment'
     * @param int   $page  For pagination. By default 0 (first result page)
     * @param int   $count For pagination, the number of results to return.
     *                     The maximum number is 100.

     * @return object null if there is an error or a object with the response based
     *  in the result expected in the documentation page.
     */
    public function getLists($type = null, $page = 0, $count = 50)
    {
        $response = $this->client->get(
            "/api/v1/lists",
            [
            'query' => [
                'api_key' => $this->apiKey,
                'type' => $type,
                'page' => $page,
                'count' => $count
            ]
            ]
        );

        return $this->sendResponseAsObject($response);
    }

    /**
     * Create a new list. Currently this resources only supports creating standard lists.
     *
     * @param string $name
     * @param string $listType
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
     * Summary information for the list specified that includes
     * the name, ID, type, number of members, when it was created and last updated.
     *
     * GET /api/v1/list/{{ LIST_ID }}
     *
     * @param string $listId
     */
    public function get($listId)
    {
        $response = $this->client->get(
            "/api/v1/list/{$listId}",
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
     * PUT /api/v1/list/{{ LIST_ID }}
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
        $response = $this->client->delete(
            "/api/v1/list/{$listId}",
            [
                'query' => [
                    'api_key' => $this->apiKey
                ]
            ]
        );

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
     * POST /api/v1/list/{{ LIST_ID }}/members
     *
     * @param  string $listId     The id of the id.
     * @param  string $email      The user email to add.
     * @param  array  $properties An array of properties such as names.
     * @param  string $confirm    If true sends an email previous user submission.
     * @return mixed             Null if the request fails or an stdclass object if is successful
     */
    public function addMember($listId, $email, array $properties, $confirm = 'true')
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
     * Adds multiple people to the specified list. For each person,
     * if a person with that email address does not already exist,
     * a new person is first added to Klaviyo.
     * If someone is unsubscribed from the specified list, they will not be
     * subscribed. To re-subscribe someone, you must manually remove them
     * from the unsubscribe list in Klaviyo on the members page for the
     * specified list.
     * POST /api/v1/list/{{ LIST_ID }}/members/batch
     *
     * @param  string $listId  The id of the id.
     * @param  array  $users   An array with properties and email(required)
     * @param  string $confirm if 'true' sends a confirmation email.
     * @return mixed Null if the request fails or an stdclass object if is successful
     */
    public function addMembers($listId, array $users, $confirm = 'true')
    {
        if (!count($users)) {
            return false;
        }

        foreach ($users as $user) {
            if (!array_key_exists('email', $user)) {
                throw new Exception('"email" key not found');
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
     * Batch Removing People from a List
     * Removes multiple people from the specified list. For each person,
     * if a person with that email address is a member of that list,
     * they are removed.
     * DELETE /api/v1/list/{{ LIST_ID }}/members/batch
     *
     * @param  string $listId The list id.
     * @param  array  $emails The list of user emails to delete.
     * @return mixed Null if the request fails or an stdclass object if is successful.
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
     * Exclude or Unsubscribe Someone from a List
     * Marks a person as excluded from the specified list.
     * This has the same effect as unsubscribing someone from a list, except
     * we keep track of the fact that they did not use the unsubscribe link
     * in your campaigns or on your list preferences page.
     * This is equivalent to manually excluding someone on the list members page.
     * Someone who is excluded will no longer receive campaigns or flow
     * emails for this list.
     * Keep in mind, there is currently no API to un-exclude someone.
     * Re-adding them will not un-exclude them. In order to remove this block,
     * you must go to the members page for the list and manually change their status.
     * POST /api/v1/list/{{ LIST_ID }}/members/exclude
     *
     * @param  string  $listId    The list id.
     * @param  string  $email     The user email to unsubscribe.
     * @param  integer $timestamp The time in seconds. -1 as default value not specified date.
     * @return mixed   Null if the request fails or an stdclass object if is successful.
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
     * Get all the exclusions for the specified list.
     * This will include the person's email, the reason they
     * were excluded and the time they were excluded.
     * GET /api/v1/list/{{ LIST_ID }}/exclusions
     *
     * @param  string  $listId The list id
     * @param  string  $reason The possible values are unsubscribed, bounced,
     *                         invalid_email,reported_spam and manually_excluded.
     * @param  string  $sort   The possible values are asc or desc.
     * @param  integer $page   The page number. Defaults to 0.
     * @param  integer $count  The count per page.
     * @return mixed   Null if the request fails or an stdclass object if
     *                 is successful.
     */
    public function getListExclusions($listId, $reason = 'unsubscribed', $sort = 'asc', $page = 0, $count = 100)
    {
        if (!$this->isValidUnsubscribeReason($reason)) {
            throw new Exception('Invalid unsubscribe reason');
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
     * List Exclusions or Unsubscribes
     * Get global exclusions or unsubscribes. Global exclusions are distinct from list
     * exclusions in that these email addresses will not receive any emails from any list.
     * Typically, when someone unsubscribes from a campaign, they are only unsubscribed
     * from that list and are not globally unsubscribed.
     * GET /api/v1/people/exclusions
     *
     * @param  string  $reason
     * @param  string  $sort
     * @param  integer $page
     * @param  integer $count
     * @return mixed  Null if the request fails or an stdclass object if
     *                is successful.
     */
    public function getExclusions($reason = 'unsubscribed', $sort = 'asc', $page = 0, $count = 100)
    {
        if (!$this->isValidUnsubscribeReason($reason)) {
            throw new Exception('Invalid unsubscribe reason');
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
