<?php

namespace Siro\Klaviyo;

use GuzzleHttp\Client;

class KlaviyoCampaign extends ApiBase
{
    use KlaviyoResponse;

    /**
     * GET /api/v1/campaigns
     */
    public function getAll($type = null, $page = 0, $count = 50)
    {
        $response = $this->client->get(
            "/api/v1/campaigns",
            [
            'query' => [
                'api_key' => $this->apiKey,
                'page'    => $page,
                'count'   => $count
            ]
            ]
        );

        return $this->sendResponseAsObject($response);
    }

    /**
     * POST /api/v1/campaigns
     */
    public function create($listId, $templateId, $fromEmail, $fromName, $subject, $name = '', $useSmartSending = true, $addGoogleAnalytics = false)
    {
        $requestParams = [
            'form_params' => [
                'api_key' => $this->apiKey,
                'list_id' => $listId,
                'template_id' => $templateId,
                'from_email'  => $fromEmail,
                'from_name'   => $fromName,
                'subject'     => $subject,
                'use_smart_sending' => $useSmartSending,
                'add_google_analytics' => $addGoogleAnalytics,
            ]
        ];

        if (!empty($name)) {
            $requestParams['form_params']['name'] = $name;
        }

        $response = $this->client->post("/api/v1/campaigns", $requestParams);

        return $this->sendResponseAsObject($response);
    }

    /**
     * GET /api/v1/campaign/{{ CAMPAIGN_ID }}
     */
    public function get($campaignId)
    {
        $response = $this->client->get(
            "/api/v1/campaign/{$campaignId}",
            [
            'query' => [
                'api_key' => $this->apiKey
            ]
            ]
        );

        return $this->sendResponseAsObject($response);
    }

    /**
     * PUT /api/v1/campaign/{{ CAMPAIGN_ID }}
     */
    public function update($campaignId, $listId = null, $templateId = null, $fromEmail = null, $fromName = null, $subject = null, $name = null, $useSmartSending = null, $addGoogleAnalytics = null)
    {
        $requestParams = [
            'form_params' => [
                'api_key' => $this->apiKey,
            ]
        ];
        if (!is_null($listId)) {
            $requestParams['form_params']['list_id'] = $listId;
        }
        if (!is_null($templateId)) {
            $requestParams['form_params']['template_id'] = $templateId;
        }
        if (!is_null($fromEmail)) {
            $requestParams['form_params']['from_email'] = $fromEmail;
        }
        if (!is_null($fromName)) {
            $requestParams['form_params']['from_name'] = $fromName;
        }
        if (!is_null($subject)) {
            $requestParams['form_params']['subject'] = $subject;
        }
        if (!is_null($useSmartSending)) {
            $requestParams['form_params']['use_smart_sending'] = $useSmartSending;
        }
        if (!is_null($name)) {
            $requestParams['form_params']['name'] = $name;
        }
        if (!is_null($addGoogleAnalytics)) {
            $requestParams['form_params']['add_google_analytics'] = $addGoogleAnalytics;
        }

        $response = $this->client->put("/api/v1/campaign/{$campaignId}", $requestParams);

        return $this->sendResponseAsObject($response);
    }

    /**
     * POST /api/v1/campaign/{{ CAMPAIGN_ID }}/send
     */
    public function sendNow($campaignId)
    {
        $response = $this->client->post(
            "/api/v1/campaign/{$campaignId}/send",
            [
            'query' => [
                'api_key' => $this->apiKey
            ]
            ]
        );

        return $this->sendResponseAsObject($response);
    }

    /**
     * POST /api/v1/campaign/{{ CAMPAIGN_ID }}/schedule
     */
    public function schedule($campaignId, $sendTime)
    {
        $options = [
            'form_params' => [
                'api_key' => $this->apiKey,
            ]
        ];
        if (is_int($sendTime)) {
            $options['form_params']['send_time'] = date("Y-m-d H:i:s", $sendTime);
        } else {
            $options['form_params']['send_time'] = $sendTime;
        }

        $response = $this->client->post("/api/v1/campaign/{$campaignId}/schedule", $options);
        return $this->sendResponseAsObject($response);
    }

    /**
     * POST /api/v1/campaign/{{ CAMPAIGN_ID }}/cancel
     */
    public function cancel($campaignId)
    {
        $options = [
            'form_params' => [
                'api_key' => $this->apiKey
            ]
        ];
        $response = $this->client->post("/api/v1/campaign/{$campaignId}/cancel", $options);
        return $this->sendResponseAsObject($response);
    }

    /**
     * POST /api/v1/campaign/{{ CAMPAIGN_ID }}/clone
     */
    public function clone($campaignId, $newName, $listId)
    {
        $options = [
            'form_params' => [
                'api_key' => $this->apiKey,
                'name'    => $newName,
                'list_id' => $listId
            ]
        ];
        $response = $this->client->post("/api/v1/campaign/{$campaignId}/clone", $options);
        return $this->sendResponseAsObject($response);
    }

    /**
     * GET /api/v1/campaign/{{ CAMPAIGN_ID }}/recipients
     */
    public function getRecipients($campaignId, $count = 5000, $sort = 'asc', $offset = '')
    {
        $options = [
            'query' => [
                'count' => $count,
                'api_key' => $this->apiKey,
                'sort'    => $sort,
            ]
        ];
        if (!empty($offset)) {
            $options['query']['offset'] = $offset;
        }
        $response = $this->client->get("/api/v1/campaign/{$campaignId}/recipients", $options);

        return $this->sendResponseAsObject($response);
    }
}
