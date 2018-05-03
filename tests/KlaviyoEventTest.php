<?php

use PHPUnit\Framework\TestCase;
use Siro\Klaviyo\KlaviyoAPI;
use GuzzleHttp\Client;

class KlaviyoEventTest extends TestCase
{
    private $klaviyo;

    public function setUp()
    {
        $this->klaviyo = new KlaviyoAPI('pk_7f0ccf9f003cfa6838556efbf44e318f4b');
    }

    public function testEventTrack()
    {
        $tracked = $this->klaviyo->event->track('register', [
            '$email' => 'example@mydomain.com',
        ], [
            'register_status' => 'success',
            'role'  => 'client'
        ]);
        $this->assertEquals($tracked, 1);
    }

    public function testEventTrackAsync()
    {
        $promise = $this->klaviyo->event->trackAsync('register', [
            '$email' => 'example@mydomain.com',
        ], [
            'register_status' => 'success',
            'role'  => 'client'
        ]);
        $res = $promise->wait();
        $this->assertEquals($res->getStatusCode(), 200);
        $this->assertEquals(json_decode((string) $res->getBody()), 1);
    }

    /*
    public function testEventIdentify()
    {
        $result = $this->klaviyo->event->identify([
            '$email' => 'sirocompani2@hotmail.com'
        ]);
        $this->assertEquals($result, 1);
    }
    
    public function testEventIdentifyAsync()
    {
        $promise = $this->klaviyo->event->identifyAsync([
            '$email' => 'example@mydomain.com',
            '$first_name' => 'Siro',
            '$last_name'  => 'Díaz'
        ]);
        $promise->then(function ($res) {
            $this->assertEquals($res->getStatusCode(), 200);
            $this->assertEquals(json_decode((string) $res->getBody()), 1);
        });
    }
    */
}
