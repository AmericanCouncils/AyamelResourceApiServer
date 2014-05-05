<?php

namespace Ayamel\ApiBundle\Tests;

/**
 * Tests modifying individual Resource fields via the API - each field
 * should be tested with both success and failure cases.
 */
class ModifyResourceFieldsTest extends FixturedTestCase
{
    protected function modify($fieldName, $newValue, $expectedCode)
    {
        //get a resource
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-1');

        //pick a resource to modify
        $resource = $res['resources'][0];

        //store the old value
        $oldValue = isset($resource[$fieldName]) ? $resource[$fieldName] : null;
        $id = $resource['id'];

        //field we're about to modify should not already have the new value (if it exists)
        if (isset($resource[$fieldName])) {
            $this->assertFalse($resource[$fieldName] === $newValue);
        }

        //modify the resource
        $modified = $this->callJsonApi('PUT', '/api/v1/resources/'.$id.'?_key=key-for-test-client-1', [
            'content' => [$fieldName => $newValue],
            'expectedCode' => $expectedCode
        ]);

        //if modified, should have new value
        if (200 === $expectedCode) {
            $this->compareValues($fieldName, $newValue, $modified['resource']);

            //get the resource again, should still have modified value
            $res = $this->callJsonApi('GET', '/api/v1/resources/'.$id.'?_key=key-for-test-client-1');

            $this->compareValues($fieldName, $newValue, $res['resource']);

            return $res;
        }

        //if not modified, should have old value
        if (400 === $expectedCode) {
            //get the resource again, should still have old value
            $res = $this->callJsonApi('GET', '/api/v1/resources/'.$id.'?_key=key-for-test-client-1');
            $this->compareValues($fieldName, $oldValue, $res['resource']);

            return $res;
        }
    }

    private function compareValues($fieldName, $targetValue, $obj)
    {
        //target field set to null
        if (is_null($targetValue)) {
            return $this->assertTrue(!isset($obj[$fieldName]) || empty($obj[$fieldName]));
        }

        if (is_array($targetValue)) {
            //target field is empty array
            if (empty($targetValue)) {
                return $this->assertSame($targetValue, $obj[$fieldName]);
            }

            //target field is nested object
            if (is_string(array_keys($targetValue)[0])) {
                foreach ($targetValue as $nestedFieldName => $val) {
                    $this->compareValues($nestedFieldName, $targetValue[$nestedFieldName], $obj[$fieldName]);
                }

                return;
            }

            //target field is scalar array
            return $this->assertSame($targetValue, $obj[$fieldName]);
        }

        //target field is scalar value
        return $this->assertSame($targetValue, $obj[$fieldName]);
    }

    protected function good($fieldName, $newValue)
    {
        return $this->modify($fieldName, $newValue, 200);
    }

    protected function bad($fieldName, $newValue)
    {
        return $this->modify($fieldName, $newValue, 400);
    }

    public function testTitle()
    {
        $this->good('title', 'This is a better title.');
        $this->bad('title', ['foo','bar']);
        $this->bad('title', null);
    }

    public function testDescription()
    {
        $this->good('description', 'This is a better description, maybe.');
        $this->bad('description', ['fooo']);
        $this->good('description', null);
    }

    public function testType()
    {
        $this->good('type', 'audio');
        $this->bad('type', 'haha');
        $this->bad('type', null);
    }

    public function testKeywords()
    {
        $this->good('keywords', 'some,better,keywords,and,cats');
        $this->bad('keywords', ['foo','bar','baz']);
    }

    public function testLanguages()
    {
        $this->good('languages', [
            'iso639_3' => ['eng','rus'],
            'bcp47' => ['en','ru']
        ]);
        $res = $this->good('languages', [
            'bcp47' => ['en','jbo']
        ]);
        $this->assertSame(['eng','rus'], $res['resource']['languages']['iso639_3']);

        $this->bad('languages', [
            'bcp47' => 73
        ]);
        $this->good('languages', [
            'bcp47' => []
        ]);

        $this->good('languages', [
            'iso639_3' => ['eng','rus'],
            'bcp47' => ['en','ru']
        ]);
        $res = $this->good('languages', [
            'iso639_3' => ['jbo','tlh']
        ]);
        $this->assertSame(['en','ru'], $res['resource']['languages']['bcp47']);
        
        $this->bad('languages', [
            'iso639_3' => 86
        ]);

        $this->good('languages', [
            'iso639_3' => []
        ]);

        $this->good('languages', null);


        $this->bad('languages', 9001);
    }

    public function testTopics()
    {
        $this->good('topics', null);        
        $this->good('topics', ['arts','history','technology']);
        $this->bad('topics', 723484);
    }

    public function testFunctions()
    {
        $this->good('functions', null);
        $this->good('functions', ['request','response','persuasion']);
        $this->bad('functions', 723484);
    }

    public function testFormats()
    {
        $this->good('formats', null);
        $this->good('formats', ['interview','radio']);
        $this->bad('formats', 723484);
    }

    public function testAuthenticity()
    {
        $this->good('authenticity', null);
        $this->good('authenticity', ['native','other']);
        $this->bad('authenticity', 723484);
    }

    public function testGenres()
    {
        $this->good('genres', null);
        $this->good('genres', ['action','drama']);
        $this->bad('genres', 723484);
    }

    public function testRegisters()
    {
        $this->good('registers', null);
        $this->good('registers', ['formal','consultative']);
        $this->bad('registers', 723484);
    }

    public function testVisibility()
    {
        $this->good('visibility', null);
        $this->good('visibility', ['test-client']);
        $this->bad('visibility', 723484);
    }

    public function testCopyright()
    {
        $this->good('copyright', null);
        $this->good('copyright', 'Me, Contributers, 2059');
        $this->bad('copyright', ['puppies','kittens']);
    }

    public function testLicense()
    {
        $this->good('license', null);
        $this->good('license', 'CC BY');
        $this->bad('license', ['foo']);
    }

    public function testOrigin()
    {
        $res = $this->good('origin', [
            'creator' => 'Sir Longfellow',
            'location' => 'Elsewhere',
            'date' => 'Early 1700s',
            'format' => 'Oil paint on canvas',
            'note' => 'Blah blah blah',
            'uri' => "http://example.com/museum/foo.html"
        ]);

        $res = $this->good('origin', [
            'location' => 'Here',
            'note' => null
        ]);

        //I can't explain the behavior below this line....
        $this->markTestIncomplete();

        $this->assertSame('Sir Longfellow', $res['resource']['origin']['creator']);
        $this->assertSame('Here', $res['resource']['origin']['location']);
        $this->assertFalse(isset($res['resource']['origin']['note']));

        $this->bad('origin', 56);

        $this->good('origin', null);
    }

    public function testClientUser()
    {
        $this->good('clientUser', null);
        $this->good('clientUser', [
            'id' => 'user-23',
            'url' => 'http://example.com/users/23'
        ]);
        $res = $this->good('clientUser', ['url' => null]);
        $this->assertSame('user-23', $res['resource']['clientUser']['id']);
    }
}
