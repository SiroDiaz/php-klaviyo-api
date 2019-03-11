<?php

use PHPUnit\Framework\TestCase;
use Siro\Klaviyo\KlaviyoAPI;
use GuzzleHttp\Client;

class KlaviyoListTest extends TestCase
{
    private $klaviyo;

    public function setUp()
    {
        $this->klaviyo = new KlaviyoAPI('pk_7f0ccf9f003cfa6838556efbf44e318f4b');
    }

    public function testCreate()
    {
        $list = $this->klaviyo->list->create('Test list');
        $this->klaviyo->list->delete($list->id);
        $this->assertEquals(0, $list->person_count);
        $this->assertEquals('Test list', $list->name);
        $this->assertEquals('list', $list->object);
    }

    public function testList()
    {
        $list = $this->klaviyo->list->create('Test list');
        $list2 = $this->klaviyo->list->create('Another list');
        $allLists = $this->klaviyo->list->getLists();
        $this->klaviyo->list->delete($list->id);
        $this->klaviyo->list->delete($list2->id);
        $this->assertEquals(5, $allLists->total);
    }

    public function testGet()
    {
        $list = $this->klaviyo->list->create('List for get');
        $listInfo = $this->klaviyo->list->get($list->id);
        $this->klaviyo->list->delete($list->id);
        $this->assertEquals(0, $listInfo->person_count);
        $this->assertEquals('List for get', $listInfo->name);
        $this->assertEquals('list', $listInfo->object);
    }

    public function testUpdate()
    {
        $list = $this->klaviyo->list->create('List for get');

        $this->assertEquals(0, $list->person_count);
        $this->assertEquals('List for get', $list->name);
        $this->assertEquals('list', $list->object);

        $list = $this->klaviyo->list->update($list->id, 'New list name');

        $this->assertEquals(0, $list->person_count);
        $this->assertEquals('New list name', $list->name);
        $this->assertEquals('list', $list->object);

        $this->klaviyo->list->delete($list->id);
    }

    public function testDelete()
    {
        $list = $this->klaviyo->list->create('List for get');

        $this->assertEquals(0, $list->person_count);
        $this->assertEquals('List for get', $list->name);
        $this->assertEquals('list', $list->object);

        $this->klaviyo->list->delete($list->id);

        $allLists = $this->klaviyo->list->getLists();
        $this->assertEquals(3, $allLists->total);
    }

    public function testAddMember()
    {
        $list = $this->klaviyo->list->create('List for get');
        $member = $this->klaviyo->list->addMember(
            $list->id, 'example@mydomain.com',
            ['$first_name' => 'Siro', 'role' => 'client'],
            'false'
        );
        $this->klaviyo->list->delete($list->id);

        // $this->assertEquals(1, $member->list->person_count);
        $this->assertEquals(false, $member->already_member);
        $this->assertEquals('example@mydomain.com', $member->person->email);
    }

    public function testAddMembers()
    {
        $list = $this->klaviyo->list->create('List for get');
        $members = $this->klaviyo->list->addMembers($list->id, [
            [
                'email' => 'example@mydomain.com', 'properties' => ['role' => 'client'],
            ],
            [
                'email' => 'jhondoe@mydomain.com'
            ]
        ], false);
        $this->klaviyo->list->delete($list->id);

        $this->assertEquals(2, count($members->people));
    }
}
