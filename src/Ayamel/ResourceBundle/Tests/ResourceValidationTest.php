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

    public function testValidateType()
    {
        $r = $this->getResource();
        $this->assertSame(0, count($this->validate($r)));

        $r = $this->getResource()->setType('foo');
        $this->assertSame(1, count($this->validate($r)));
    }

    public function testValidateTopics()
    {
        $r = $this->getResource()->setTopics(['arts','history']);
        $this->assertSame(0, count($this->validate($r)));

        $r = $this->getResource()->setTopics(['arts','world domination']);
        $this->assertSame(1, count($this->validate($r)));
    }

    public function testValidateGenres()
    {
        $r = $this->getResource()->setGenres(['action','drama']);
        $this->assertSame(0, count($this->validate($r)));

        $r = $this->getResource()->setGenres(['action','movieslol']);
        $this->assertSame(1, count($this->validate($r)));
    }

    public function testValidateFormats()
    {
        $r = $this->getResource()->setFormats(['skit','interview']);
        $this->assertSame(0, count($this->validate($r)));

        $r = $this->getResource()->setFormats(['skit','interview','purple']);
        $this->assertSame(1, count($this->validate($r)));
    }

    public function testValidateFunctions()
    {
        $r = $this->getResource()->setFunctions(['apology','introduction']);
        $this->assertSame(0, count($this->validate($r)));

        $r = $this->getResource()->setFunctions(['foo']);
        $this->assertSame(1, count($this->validate($r)));
    }

    public function testValidateAuthenticity()
    {
        $r = $this->getResource()->setAuthenticity(['non-native','other']);
        $this->assertSame(0, count($this->validate($r)));

        $r = $this->getResource()->setAuthenticity(['no']);
        $this->assertSame(1, count($this->validate($r)));

    }

    public function testValidateRegisters()
    {
        $r = $this->getResource()->setRegisters(['formal', 'casual']);
        $this->assertSame(0, count($this->validate($r)));

        $r = $this->getResource()->setRegisters(['ok']);
        $this->assertSame(1, count($this->validate($r)));
    }

}
