<?php

namespace Ayamel\ResourceBundle\Tests;

use Ayamel\ApiBundle\ApiTestCase;
use Ayamel\ResourceBundle\Document\Resource;

class ResourceValidationTest extends ApiTestCase
{
    private $validator;

    public function setUp()
    {
        $this->validator = $this->getContainer()->get('validator');
    }

    private function getResource()
    {
        return (new Resource())->setTitle('foo')->setType('data');
    }

    private function validate($obj)
    {
        return $this->validator->validate($obj);
    }

    public function testValidateSequence()
    {
        //video can be a sequence
        $r = $this->getResource()->setType('video')->setSequence(true);
        $this->assertSame(0, count($this->validate($r)));

        //data can't
        $r = $this->getResource()->setType('data')->setSequence(true);
        $this->assertSame(1, count($this->validate($r)));
    }

    public function testValidateLicense()
    {
        $r = $this->getResource()->setLicense('CC BY');
        $this->assertSame(0, count($this->validate($r)));

        $r = $this->getResource()->setLicense('CC FOOOO');
        $this->assertSame(1, count($this->validate($r)));
    }
}
