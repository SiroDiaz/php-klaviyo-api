<?php

use PHPUnit\Framework\TestCase;
use Siro\Klaviyo\KlaviyoAPI;
use GuzzleHttp\Client;

class KlaviyoProfileTest extends TestCase
{
    private $klaviyo;

    public function setUp()
    {
        $this->klaviyo = new KlaviyoAPI('pk_7f0ccf9f003cfa6838556efbf44e318f4b');
    }

    public function testGet()
    {
        $person = $this->klaviyo->profile->get('N9mVQC');
        $this->assertEquals('example@mydomain.com', $person->email);
    }

    public function testEdit()
    {
        $person = $this->klaviyo->profile->edit('N9mVQC', [
            '$first_name' => 'Michael',
            'role' => 'client'
        ]);

        $this->assertEquals('Michael', ((array) $person)['$first_name']);
        $this->assertEquals('client', $person->role);
    }

    public function testGetMetrics()
    {
        $person = $this->klaviyo->profile->getMetrics('N9mVQC');
        $this->assertObjectHasAttribute('count', $person);
        $this->assertObjectHasAttribute('data', $person);
        $this->assertInternalType('array', $person->data);
    }

    public function testGetMetric()
    {
        $registerMetric = 'NTKpdq';
        $person = $this->klaviyo->profile->getMetric('N9mVQC', $registerMetric);
        $this->assertObjectHasAttribute('count', $person);
        $this->assertObjectHasAttribute('data', $person);
        $this->assertInternalType('array', $person->data);
    }
}
