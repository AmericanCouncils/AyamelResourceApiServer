<?php

namespace Ayamel\ResourceBundle\Tests;

use Ayamel\ApiBundle\ApiTestCase;
use Ayamel\ResourceBundle\Document\Relation;

/**
 * Test that Relation attribute validators are being applied properly
 *
 * Note that as new relations are defined, a test for each with specific attributes
 * should be added to ensure proper configuration.
 */
class RelationValidationTest extends ApiTestCase
{
    public function testPassValidationOnUnmappedOrNullRelation()
    {
        $v = $this->getContainer()->get('validator');

        $rel = new Relation();
        $rel->setObjectId('324');
        $rel->setSubjectId('325');
        $rel->setType('requires');

        $errors = $v->validate($rel);

        $this->assertSame(0, count($errors));
    }

    public function testFailsValidationWithExtraKeys()
    {
        $v = $this->getContainer()->get('validator');

        //unmapped w/ extra key
        $rel = new Relation();
        $rel->setObjectId('324');
        $rel->setSubjectId('325');
        $rel->setType('requires');
        $rel->setAttributes(array(
            'foo' => 'bar'
        ));
        $errors = $v->validate($rel);
        $this->assertSame(1, count($errors));

        //mapped w/ extra key
        $rel = new Relation();
        $rel->setObjectId('324');
        $rel->setSubjectId('325');
        $rel->setType('part_of');
        $rel->setAttributes(array(
            'foo' => 'bar'
        ));
        $errors = $v->validate($rel);
        $this->assertSame(1, count($errors));
    }

    public function testValidatePartOfRelation()
    {
        $rel = new Relation();
        $rel->setObjectId('324');
        $rel->setSubjectId('325');
        $rel->setType('part_of');
        $rel->setAttributes(array(
            'index' => 3
        ));

        $v = $this->getContainer()->get('validator');
        $errors = $v->validate($rel);

        $this->assertSame(0, count($errors));
    }

    public function testValidateVersionOfRelation()
    {
        $rel = new Relation();
        $rel->setObjectId('324');
        $rel->setSubjectId('325');
        $rel->setType('version_of');
        $rel->setAttributes(array(
            'version' => "99.23.33"
        ));

        $v = $this->getContainer()->get('validator');
        $errors = $v->validate($rel);

        $this->assertSame(0, count($errors));
    }
}
