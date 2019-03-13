<?php

namespace Siro\Klaviyo;

use GuzzleHttp\Client;

trait KlaviyoResponse
{
    /**
     * Sends the response object if 200 status code is retrieved.
     * In case of 400 or 500 status codes returns null.
     *
     * @return object the Klaviyo response.
     */
    public function sendResponseAsObject($response)
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode === 200) {
            return json_decode((string) $response->getBody());
        }

        return null;
    }
}
