<?php

namespace Ayamel\ApiBundle\Tests;

use Ayamel\ApiBundle\ApiTestCase;

class UITest extends ApiTestCase
{
    public function testHomePage()
    {
        $res = $this->getResponse('GET', '/');
        $this->assertSame(200, $res->getStatusCode());
        $this->assertTrue(0 === strpos($res->getContent(), '<!DOCTYPE html>'));
    }

    public function testDocsPage()
    {
        $res = $this->getResponse('GET', '/api/v1/docs/');
        $this->assertSame(200, $res->getStatusCode());
        $this->assertTrue(0 === strpos($res->getContent(), '<!DOCTYPE html>'));
    }
}
