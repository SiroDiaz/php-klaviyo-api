<?php

namespace Siro\Klaviyo;

use GuzzleHttp\Client;

/**
 * https://www.klaviyo.com/docs/api/metrics
 */
class KlaviyoMetric extends ApiBase
{
    use KlaviyoResponse;

    /**
     * Checks if the string date has a valid format.
     * Format follows this format 'year-month-day'.
     *
     * @param  string $dateStr a date as an string. For example: 2018-01-29.
     * @return boolean true if is a valid date.
     */
    private function isValidDate($dateStr)
    {
        return preg_match('/^[0-9]{1,4}\-((0[1-9]?)|(11)|(12))\-(0[1-9]|([1-2][0-9])|(30|31))$/', $dateStr);
    }

    /**
     * GET /api/v1/metrics
     */
    public function get($page = 0, $count = 50)
    {
        $response = $this->client->get(
            '/api/v1/metrics',
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
     * GET /api/v1/metrics/timeline
     */
    public function getAllTimeline($page = 0, $since = null, $count = 100, $sort = 'desc')
    {
        $data = [
            'query' => [
                'api_key' => $this->apiKey,
                'page'    => $page,
                'count'   => $count
            ]
        ];
        if (!is_null($since)) {
            $data['query']['since'] = $since;
        }

        $response = $this->client->get('/api/v1/metrics/timeline', $data);
        return $this->sendResponseAsObject($response);
    }

    /**
     * GET /api/v1/metric/{{METRIC_ID}}/timeline
     */
    public function getMetricTimeline($metricId, $since = null, $count = 100, $sort = 'desc')
    {
        $data = [
            'query' => [
                'api_key' => $this->apiKey,
                'count'   => $count,
                'sort'    => $sort
            ]
        ];
        if (!is_null($since)) {
            $data['query']['since'] = $since;
        }

        $response = $this->client->get("/api/v1/metric/{$metricId}/timeline", $data);
        return $this->sendResponseAsObject($response);
    }

    /**
     * GET /api/v1/metric/{{METRIC_ID}}/export
     */
    public function export($metricId, $start = null, $end = null, $unit = 'day', $measurement = null, $where = null, $by = '', $count = 25)
    {
        $data = [
            'query' => [
                'api_key' => $this->apiKey,
                'count'   => $count,
                'unit'    => $unit
            ]
        ];
        if (!is_null($start) && $this->isValidDate($start)) {
            $data['query']['start_date'] = $start;
        }
        if (!is_null($end) && $this->isValidDate($end)) {
            $data['query']['end_date'] = $end;
        }
        if (is_null($where) && !is_empty($by)) {
            $data['query']['by'] = urlencode($by);
        } elseif (!is_null($where) && is_array($where) && is_empty($by)) {
            $data['query']['where'] = json_encode(array_values($where));
        }

        $response = $this->client->get("/api/v1/metric/{$metricId}/export", $data);
        return $this->sendResponseAsObject($response);
    }
}
