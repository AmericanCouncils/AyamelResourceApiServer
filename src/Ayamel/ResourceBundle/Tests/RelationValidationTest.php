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
    public function testNoErrorsOnUnmappedOrNullRelation()
    {
        $v = $this->getContainer()->get('validator');

        $rel = new Relation();
        $rel->setObjectId('324');
        $rel->setType('requires');

        $errors = $v->validate($rel);

        $this->assertSame(0, count($errors));
    }

    public function testValidatePartOfAttributes()
    {
        $rel = new Relation();
        $rel->setObjectId('324');
        $rel->setType('part_of');
        $rel->setAttributes(array(
            'index' => 23.3
        ));

        $v = $this->getContainer()->get('validator');
        $errors = $v->validate($rel);

        $this->assertSame(1, count($errors));
        $this->assertSame('attributes', $errors[0]->getPropertyPath());
    }
}
