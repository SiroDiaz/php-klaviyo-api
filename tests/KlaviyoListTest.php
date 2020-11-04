<?php

use PHPUnit\Framework\TestCase;
use Siro\Klaviyo\KlaviyoAPI;
use GuzzleHttp\Client;

class KlaviyoListTest extends TestCase
{
    private $klaviyo;

    public function setUp(): void
    {
        $this->klaviyo = new KlaviyoAPI('pk_7f0ccf9f003cfa6838556efbf44e318f4b');

        $allLists = $this->klaviyo->list->getLists();

        foreach ($allLists as $list) {
            $this->klaviyo->list->delete($list->list_id);
        }
    }

    public function testCreate()
    {
        $list = $this->klaviyo->list->create('Test list');

        $this->assertObjectHasAttribute('list_id', $list);
    }

    public function testList()
    {
        $this->klaviyo->list->create('Test list');
        $this->klaviyo->list->create('Another list');
        $allLists = $this->klaviyo->list->getLists();

        $this->assertEquals(2, count($allLists));
    }

    public function testGet()
    {
        $list = $this->klaviyo->list->create('List for get');
        $listInfo = $this->klaviyo->list->get($list->list_id);

        $this->assertEquals('List for get', $listInfo->list_name);
    }

    public function testUpdate()
    {
        $list = $this->klaviyo->list->create('List for get');
        $this->klaviyo->list->update($list->list_id, 'New list name');
        $listInfo = $this->klaviyo->list->get($list->list_id);

        $this->assertEquals('New list name', $listInfo->list_name);
    }

    public function testDelete()
    {
        $list = $this->klaviyo->list->create('List for get');
        $this->klaviyo->list->delete($list->list_id);

        $allLists = $this->klaviyo->list->getLists();
        $this->assertEquals(0, count($allLists));
    }

    public function testAddMember()
    {
        $list = $this->klaviyo->list->create('List for get');
        $member = $this->klaviyo->list->addMember(
            $list->list_id,
            [
              [
                'email' => 'example@mydomain.com',
              ]
            ]
        );

        $this->assertEquals('example@mydomain.com', $member[0]->email);
        $this->assertCount(1, $member);
    }

    public function testAddMembers()
    {
        $list = $this->klaviyo->list->create('List for get');
        $members = $this->klaviyo->list->addMember($list->list_id, [
            [
                'email' => 'example@mydomain.com', 'properties' => ['role' => 'client'],
            ],
            [
                'email' => 'jhondoe@mydomain.com'
            ]
        ], false);

        $this->assertEquals(2, count($members));
    }

    public function testMemberExistsInList()
    {
        $list = $this->klaviyo->list->create('List for get');
        $this->klaviyo->list->addMember($list->list_id, [
            [
                'email' => 'example@mydomain.com', 'properties' => ['role' => 'client'],
            ],
            [
                'email' => 'jhondoe@mydomain.com'
            ]
        ], false);
        $this->assertTrue($this->klaviyo->list->memberExistsInList($list->list_id, ['example@mydomain.com', 'jhondoe@mydomain.com']));
    }

    public function testMembersDontExistInList()
    {
        $list = $this->klaviyo->list->create('List for get');
        $this->klaviyo->list->addMember($list->list_id, [
            [
                'email' => 'example@mydomain.com', 'properties' => ['role' => 'client'],
            ],
            [
                'email' => 'jhondoe@mydomain.com'
            ]
        ]);
        $this->assertFalse($this->klaviyo->list->memberExistsInList($list->list_id, ['jaime@mydomain.com', 'jhondoe@mydomain.com']));
    }

    public function testDeleteMemberFromList()
    {
        $list = $this->klaviyo->list->create('List for get');
        $members = $this->klaviyo->list->addMember($list->list_id, [
            [
                'email' => 'example@mydomain.com', 'properties' => ['role' => 'client'],
            ],
            [
                'email' => 'jhondoe@mydomain.com'
            ]
        ]);

        $members = $this->klaviyo->list->deleteMembers($list->list_id, ['example@mydomain.com', 'jhondoe@mydomain.com']);
        $this->assertFalse($this->klaviyo->list->memberExistsInList($list->list_id, ['example@mydomain.com', 'jhondoe@mydomain.com']));
    }
}
