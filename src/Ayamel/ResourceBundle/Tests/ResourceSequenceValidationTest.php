<?php

namespace Ayamel\ResourceBundle\Tests;

use Ayamel\ApiBundle\ApiTestCase;
use Ayamel\ResourceBundle\Document\Resource;

class ResourceSequenceValidationTest extends ApiTestCase
{
    public function testResourceSequenceValidation()
    {
        $v = $this->getContainer()->get('validator');

        //should pass
        $r = new Resource();
        $r->setTitle('foo');
        $r->setType('video');
        $r->setSequence(true);
        $errors = $v->validate($r);
        $this->assertSame(0, count($errors));

        //should fail
        $r = new Resource();
        $r->setTitle('foo');
        $r->setType('data');
        $r->setSequence(true);
        $errors = $v->validate($r);
        $this->assertSame(1, count($errors));
    }
}
