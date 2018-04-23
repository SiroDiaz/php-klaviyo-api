<?php

namespace Siro\Klaviyo;

use GuzzleHttp\Client;

class KlaviyoTemplate extends KlaviyoResponse
{
    private $apiKey;
    private $client;

    public function __construct($client)
    {
        $this->apiKey = $apiKey;
        $this->client = $client;
    }

    /**
     * GET /api/v1/email-templates
     */
    public function getAll()
    {
    }

    /**
     * POST /api/v1/email-templates
     */
    public function create()
    {
    }

    /**
     * PUT /api/v1/email-template/{{ TEMPLATE_ID }}
     */
    public function update()
    {
    }

    /**
     * DELETE /api/v1/email-template/{{ TEMPLATE_ID }}
     */
    public function delete()
    {
    }

    /**
     * POST /api/v1/email-template/{{ TEMPLATE_ID }}/clone
     */
    public function clone()
    {
    }

    /**
     * POST /api/v1/email-template/{{ TEMPLATE_ID }}/render
     */
    public function render()
    {
    }

    /**
     * POST /api/v1/email-template/{{ TEMPLATE_ID }}/send
     */
    public function renderAndSend()
    {
    }


    /**
     * GET /api/v1/lists
     */
    public function getLists($type = null, $page = 0, $count = 20)
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
    public function createNew($name, $listType = 'standard')
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
}
