<?php

namespace Siro\Klaviyo;

/**
 * https://www.klaviyo.com/docs/http-api
 */
class KlaviyoProfile extends ApiBase
{
    use KlaviyoResponse;

    /**
     * GET /api/v1/person/{{ PERSON_ID }}
     */
    public function get($personId)
    {
        $response = $this->client->get(
            "/api/v1/person/{$personId}",
            [
            'query' => [
                'api_key' => $this->apiKey
            ]
            ]
        );

        return $this->sendResponseAsObject($response);
    }

    /**
     * PUT /api/v1/person/{{ PERSON_ID }}
     */
    public function edit($personId, array $properties)
    {
        $requestParams = [
            'form_params' => [
                'api_key' => $this->apiKey
            ]
        ];
        foreach ($properties as $property => $value) {
            $requestParams['form_params'][$property] = $value;
        }

        $response = $this->client->put("/api/v1/person/{$personId}", $requestParams);

        return $this->sendResponseAsObject($response);
    }

    /**
     * GET /api/v1/person/{{PERSON_ID}}/metrics/timeline
     */
    public function getMetrics($personId, $since = null, $count = 100, $sort = 'desc')
    {
        $requestParams = [
            'query' => [
                'api_key' => $this->apiKey,
                'count'   => $count,
                'sort'    => $sort
            ]
        ];
        if ($since !== null) {
            $requestParams['form_params']['since'] = $since;
        }

        $response = $this->client->get("/api/v1/person/{$personId}/metrics/timeline", $requestParams);

        return $this->sendResponseAsObject($response);
    }

    /**
     * GET /api/v1/person/{{PERSON_ID}}/metric/{{METRIC_ID}}/timeline
     */
    public function getMetric($personId, $metricId, $since = null, $count = 100, $sort = 'desc')
    {
        $requestParams = [
            'query' => [
                'api_key' => $this->apiKey,
                'count'   => $count,
                'sort'    => $sort
            ]
        ];
        if ($since !== null) {
            $requestParams['form_params']['since'] = $since;
        }

        $response = $this->client->get("/api/v1/person/{$personId}/metric/{$metricId}/timeline", $requestParams);

        return $this->sendResponseAsObject($response);
    }

	/**
	 * GET /api/v2/person/{{ PERSON_ID }}
	 *
	 * @param string $value
	 * @param string $identifier - can be 'email', 'phone_number' or 'external_id'
	 */
	public function search( $value, $identifier = 'email' )
	{
		$response = $this->client->get( "/api/v2/people/search",
			[
				'query' => [
					'api_key'   => $this->apiKey,
					$identifier => $value
				]
			]
		);

		return $this->sendResponseAsObject( $response );
	}
}
