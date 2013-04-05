<?php

namespace Ayamel\ApiBundle\Tests;

use Ayamel\ApiBundle\TestCase;
use AC\WebServicesBundle\Util\ClientObjectValidator;
use Symfony\Component\DependencyInjection\Container;
use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\Client;
use Ayamel\ResourceBundle\Document\ClientUser;
use Symfony\Component\HttpFoundation\Request;

/**
 * WARNING: This isn't the best way to test this - ideally it should be done via unit tests in the 
 * ACWebServicesBundle.  As that would require significant setup, and it needs to be tested now, I'm
 * cheating and writing an application specific test by pulling the service from the container.
 */
class ClientObjectValidatorTest extends TestCase
{
	public function testGetService()
    {
        $c = $this->getContainer();
        
        $this->assertTrue($c instanceof Container);
        $validator = $c->get('ac.webservices.object_validator');
        $this->assertNotNull($validator);
        $this->assertTrue($validator instanceof ClientObjectValidator);
    }
    
    public function testCreateObjectFromRequest()
    {
        $c = $this->getContainer();
        $validator = $c->get('ac.webservices.object_validator');
        $requestData = array(
            'title' => 'Fooooooooo',
            'description' => 'yayayaasd asd fadsf s',
            'keywords' => 'hi, there and, some stuff, hah',
            'categories' => array('foo','bar','baz'),
            'type' => 'document',
            'client' => array(
                'user' => array(
                    'id' => "Tester"
                )
            )
        );

        $request = Request::create('/foo/bar', 'POST', array(), array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($requestData));
        
        $resource = $validator->createObjectFromRequest('Ayamel\ResourceBundle\Document\Resource', $request);
        $this->assertTrue($resource instanceof Resource);
        $this->assertSame($resource->getTitle(), $requestData['title']);
        $this->assertSame($resource->getDescription(), $requestData['description']);
        $this->assertSame($resource->getKeywords(), $requestData['keywords']);
        $this->assertSame($resource->getCategories(), $requestData['categories']);
        $this->assertSame($resource->getType(), $requestData['type']);
        $this->assertSame($resource->getClient()->getUser()->getId(), $requestData['client']['user']['id']);
    }
    
    public function testModifyObjectFromRequest()
    {
        $resource = new Resource;
        $resource->setTitle('foo');
        $resource->setDescription('bar');
        $resource->setCategories(array('bar','baz'));
        $resource->setClient(new Client);
        $resource->getClient()->setUser(new ClientUser);
        $resource->getClient()->getUser()->setId('baz');
        
        $c = $this->getContainer();
        $validator = $c->get('ac.webservices.object_validator');
        $requestData = array(
            'title' => 'Fooooooooo',
            'keywords' => 'hi, there and, some stuff, hah',
            'categories' => array('foo','bar','baz'),
            'description' => null,
            'type' => 'document',
            'client' => array(
                'user' => array(
                    'id' => "Tester"
                )
            )
        );
        
        $request = Request::create('/foo/bar', 'POST', array(), array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($requestData));
        $validator->modifyObjectFromRequest('Ayamel\ResourceBundle\Document\Resource', $request, $resource);

        $this->assertTrue($resource instanceof Resource);
        $this->assertSame($resource->getTitle(), $requestData['title']);
        $this->assertNull($resource->getDescription());
        $this->assertSame($resource->getKeywords(), $requestData['keywords']);
        $this->assertSame($resource->getCategories(), $requestData['categories']);
        $this->assertSame($resource->getType(), $requestData['type']);
        $this->assertSame($resource->getClient()->getUser()->getId(), $requestData['client']['user']['id']);
        
        //modify again
        $changes = array(
            'client' => null
        );
        
        $request = Request::create('/foo/bar', 'POST', array(), array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($changes));
        $validator->modifyObjectFromRequest('Ayamel\ResourceBundle\Document\Resource', $request, $resource);

        $this->assertTrue($resource instanceof Resource);
        $this->assertSame($resource->getTitle(), $requestData['title']);
        $this->assertNull($resource->getDescription());
        $this->assertSame($resource->getKeywords(), $requestData['keywords']);
        $this->assertSame($resource->getCategories(), $requestData['categories']);
        $this->assertSame($resource->getType(), $requestData['type']);
        $this->assertNull($resource->getClient());
    }
    
    public function testThrowExceptionReadOnlyFields()
    {
        $c = $this->getContainer();
        $validator = $c->get('ac.webservices.object_validator');
        $requestData = array(
            'id' => 'should be readonly',
            'title' => 'Fooooooooo',
            'description' => 'yayayaasd asd fadsf s',
            'keywords' => 'hi, there and, some stuff, hah',
            'categories' => array('foo','bar','baz'),
            'type' => 'document',
            'client' => array(
                'user' => array(
                    'id' => "Tester"
                )
            )
        );

        $request = Request::create('/foo/bar', 'POST', array(), array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($requestData));
        
        $this->setExpectedException('Exception');
        $resource = $validator->createObjectFromRequest('Ayamel\ResourceBundle\Document\Resource', $request);
    }
    
    public function testThrowExceptionOnInvalidFieldName()
    {
        $c = $this->getContainer();
        $validator = $c->get('ac.webservices.object_validator');
        $requestData = array(
            'title' => 'Fooooooooo',
            'description' => 'yayayaasd asd fadsf s',
            'keywords' => 'hi, there and, some stuff, hah',
            'categories' => array('foo','bar','baz'),
            'type' => 'document',
            'client' => array(
                'user' => array(
                    'id' => "Tester",
                    'fooasdf' => 'bar',
                )
            )
        );

        $request = Request::create('/foo/bar', 'POST', array(), array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($requestData));
        
        $this->setExpectedException('Exception');
        $resource = $validator->createObjectFromRequest('Ayamel\ResourceBundle\Document\Resource', $request);
    }
    
    public function testIsset()
    {
        $test = array(
            'foo' => 23,
            'bar' => 'baz',
            'baz' => null,
            'qux' => 'chickens'
        );
        $this->assertTrue(array_key_exists('qux', $test));
    }
}
