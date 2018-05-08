<?php

use PHPUnit\Framework\TestCase;
use Siro\Klaviyo\KlaviyoAPI;
use GuzzleHttp\Client;

class KlaviyoTemplateTest extends TestCase
{
    private $klaviyo;

    public function setUp()
    {
        $this->klaviyo = new KlaviyoAPI('pk_7f0ccf9f003cfa6838556efbf44e318f4b');
    }

    public function testCreate()
    {
        $tmpl = "
        <html>
            <body>
                <h1>Welcome to our website {{name}}!</h1>
                <p>Ey, we are doing great stuffs.</p>
            </body>
        </html>
        ";
        $template = $this->klaviyo->template->create('Register template', $tmpl);
        $this->klaviyo->template->delete($template->id);

        $this->assertEquals('Register template', $template->name);
        $this->assertEquals('email-template', $template->object);
    }

    public function testClone()
    {
        $tmpl = "
        <html>
            <body>
                <h1>Welcome to our website {{name}}!</h1>
                <p>Ey, we are doing great stuffs.</p>
            </body>
        </html>
        ";
        $template = $this->klaviyo->template->create('Register template', $tmpl);
        $cloned = $this->klaviyo->template->clone($template->id, 'Register template clone');
        
        $this->assertEquals('Register template', $template->name);
        $this->assertEquals('email-template', $template->object);
        $this->assertEquals('Register template clone', $cloned->name);
        $this->assertEquals('email-template', $cloned->object);

        $this->klaviyo->template->delete($template->id);
        $this->klaviyo->template->delete($cloned->id);
    }

    public function testGetAll()
    {
        $tmpl = "
        <html>
            <body>
                <h1>Welcome to our website {{name}}!</h1>
                <p>Ey, we are doing great stuffs.</p>
            </body>
        </html>
        ";
        $template = $this->klaviyo->template->create('Register template', $tmpl);
        $cloned = $this->klaviyo->template->clone($template->id, 'Register template clone');
        $allTmpls = $this->klaviyo->template->getAll();

        $this->klaviyo->template->delete($template->id);
        $this->klaviyo->template->delete($cloned->id);

        $this->assertObjectHasAttribute('total', $allTmpls);
        $this->assertInternalType('array', $allTmpls->data);
    }

    public function testDelete()
    {
        $tmpl = "
        <html>
            <body>
                <h1>Welcome to our website {{name}}!</h1>
                <p>Ey, we are doing great stuffs.</p>
            </body>
        </html>
        ";
        $template = $this->klaviyo->template->create('Register template', $tmpl);
        $cloned = $this->klaviyo->template->clone($template->id, 'Register template clone');
        $allTmpls = $this->klaviyo->template->getAll();

        $this->assertObjectHasAttribute('total', $allTmpls);
        $this->assertInternalType('array', $allTmpls->data);

        $this->klaviyo->template->delete($template->id);
        $this->klaviyo->template->delete($cloned->id);
        $allTmpls = $this->klaviyo->template->getAll();

        $this->assertObjectHasAttribute('total', $allTmpls);
        $this->assertInternalType('array', $allTmpls->data);
    }

    public function testUpdateName()
    {
        $tmpl = "
        <html>
            <body>
                <h1>Welcome to our website {{name}}!</h1>
                <p>Ey, we are doing great stuffs.</p>
            </body>
        </html>
        ";
        $template = $this->klaviyo->template->create('Register template', $tmpl);
        $updated = $this->klaviyo->template->update($template->id, 'Register template updated', $tmpl);
        
        $this->klaviyo->template->delete($updated->id);
        $allTmpls = $this->klaviyo->template->getAll();

        $this->assertObjectHasAttribute('total', $allTmpls);
        $this->assertInternalType('array', $allTmpls->data);
    }

    /*
    public function testRender()
    {
        $tmpl = "
        <html>
            <body>
                <h1>Welcome to our website {{name}}!</h1>
                <p>Ey, we are doing great stuffs.</p>
            </body>
        </html>
        ";
        $template = $this->klaviyo->template->create('Register template', $tmpl);
        
        $this->klaviyo->template->delete($template->id);

        // $this->assertEquals(7, $allTmpls->total);
        // $this->assertInternalType('array', $allTmpls->data);
    }
    */
}
