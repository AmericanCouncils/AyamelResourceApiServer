<?php

namespace Ayamel\ResourceBundle\Tests;

use Ayamel\ResourceBundle\Validation\AbstractAttributes;

class FooAttributes extends AbstractAttributes
{
    public $foo;
}

class AbstractAttributesTest extends \PHPUnit_Framework_TestCase
{
    
    public function testInstantiateWithData()
    {
        $obj = FooAttributes::createFromArray(array('foo' => 'bar'));
        
        $this->assertTrue($obj instanceof AbstractAttributes);
        $this->assertTrue($obj instanceof FooAttributes);
        $this->assertSame('bar', $obj->foo);
        $extras = $obj->getExtraFields();
        $this->assertTrue(empty($extras));
    }
    
    public function testInstantiateWithExtraFields()
    {
        $obj = FooAttributes::createFromArray(array('foo' => 'bar', 'baz' => 23));
        $extras = $obj->getExtraFields();
        $this->assertSame(1, count($extras));
        $this->assertSame('baz', $extras[0]);
    }
    
}