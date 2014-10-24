<?php

namespace Ayamel\ApiBundle\Tests;

use Ayamel\ApiBundle\ApiTestCase;

class RelationsIntegrationTest extends ApiTestCase
{

    protected function createTestResource($id = '45678isafgd56789asfgdhf4567')
    {
        $json = $this->getJson('POST', '/api/v1/resources?_key='.$id, [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'title' => 'testing',
            'type' => 'data',
            'description' => '...and more testing'
        )));

        if (!isset($json['resource'])) {
            throw new \RuntimeException(sprintf("Failed creating test Resource: (%s - %s)", $json['response']['code'], $json['response']['message']));
        }

        return $json;
    }

    protected function createTestRelation($relationData, $id = '45678isafgd56789asfgdhf4567')
    {
        $json = $this->getJson('POST', '/api/v1/relations?_key='.$id, [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($relationData));

        if (!isset($json['relation'])) {
            throw new \RuntimeException(sprintf("Failed creating test Relation: (%s - %s)", $json['response']['code'], $json['response']['message']));
        }

        return $json;
    }

    protected function createTestResourcesWithRelations()
    {
        $res1 = $this->createTestResource();
        $res2 = $this->createTestResource();

        $rel1 = array(
            'subjectId' => $res2['resource']['id'],
            'objectId' => $res1['resource']['id'],
            'type' => 'requires'
        );

        $rel2 = array(
            'subjectId' => $res1['resource']['id'],
            'objectId' => $res2['resource']['id'],
            'type' => 'part_of'
        );

        $this->createTestRelation($rel1);
        $this->createTestRelation($rel2);

        //retrieve both resources
        return array(
            'subject' => $res1['resource'],
            'object' => $res2['resource']
        );
    }

    public function testCreateRelations()
    {
        $r1 = $this->createTestResource();
        $r2 = $this->createTestResource();

        $this->assertTrue(empty($r1['resource']['relations']));
        $this->assertTrue(empty($r2['resource']['relations']));
        $subjectId = $r1['resource']['id'];
        $objectId = $r2['resource']['id'];

        $relationData = array(
            'subjectId' => $subjectId,
            'objectId' => $objectId,
            'type' => 'version_of',
            'attributes' => array(
                'version' => '12.23.45'
            ),
            'clientUser' => array(
                'id' => 'user1',
                'url' => 'http://example.com/users/user1'
            )
        );

        $expectedClient = array(
            'id' => 'test_client',
            'name' => 'The Test Client',
        );

        $response = $this->getResponse('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($relationData));

        $this->assertSame(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);

        //check the relation
        $this->assertTrue(is_string($data['relation']['id']));
        $this->assertSame($subjectId, $data['relation']['subjectId']);
        $this->assertSame($objectId, $data['relation']['objectId']);
        $this->assertSame($relationData['type'], $data['relation']['type']);
        $this->assertSame($relationData['attributes'], $data['relation']['attributes']);
        $this->assertSame($relationData['clientUser'], $data['relation']['clientUser']);
        $this->assertSame($expectedClient, $data['relation']['client']);
        $this->assertTrue(isset($data['relation']['client']['id']));

        //check subject resource
        $res1 = $this->getJson('GET', '/api/v1/resources/'.$subjectId.'?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));

        $this->assertTrue(isset($res1['resource']['relations']));
        $this->assertSame(1, count($res1['resource']['relations']));
        $this->assertSame($subjectId, $res1['resource']['relations'][0]['subjectId']);
        $this->assertSame($objectId, $res1['resource']['relations'][0]['objectId']);
        $this->assertSame($relationData['type'], $res1['resource']['relations'][0]['type']);
        $this->assertSame($relationData['attributes'], $res1['resource']['relations'][0]['attributes']);
        $this->assertSame($relationData['clientUser'], $res1['resource']['relations'][0]['clientUser']);
        $this->assertSame($data['relation']['client']['id'], $res1['resource']['relations'][0]['client']['id']);

        //check object resource
        $res2 = $this->getJson('GET', '/api/v1/resources/'.$objectId.'?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));

        $this->assertFalse(empty($res2['resource']['relations']));
        $this->assertSame(1, count($res2['resource']['relations']));
        $this->assertSame($subjectId, $res2['resource']['relations'][0]['subjectId']);
        $this->assertSame($objectId, $res2['resource']['relations'][0]['objectId']);
        $this->assertSame($relationData['type'], $res2['resource']['relations'][0]['type']);
        $this->assertSame($relationData['attributes'], $res2['resource']['relations'][0]['attributes']);
        $this->assertSame($relationData['clientUser'], $res2['resource']['relations'][0]['clientUser']);
        $this->assertSame($data['relation']['client']['id'], $res2['resource']['relations'][0]['client']['id']);
    }

    public function testCreateRelationForNonExistingObject()
    {
        //non-existing subject
        $response = $this->getResponse('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'subjectId' => '33333335',
            'objectId' => '33333334',
            'type' => 'requires',
            'attributes' => array(
                'foo' => 'bar'
            ),
        )));
        $this->assertSame(404, $response->getStatusCode());

        //non-existing object
        $subject = $this->createTestResource();
        $subId = $subject['resource']['id'];

        $response = $this->getResponse('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'subjectId' => $subId,
            'objectId' => '33333334',
            'type' => 'requires',
            'attributes' => array(
                'foo' => 'bar'
            ),
        )));

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testCreateRelationForHiddenObject()
    {
        //create private Resource as client1
        $json = $this->getJson("POST", '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array('title'=>'foo', 'type'=>'data', 'visibility' => array('test_client'))));
        $this->assertSame(201, $json['response']['code']);
        $subId = $json['resource']['id'];

        //create resource is 2nd client
        $json = $this->getJson("POST", '/api/v1/resources?_key=55678isafgd56789asfgdhf4568', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array('title'=>'foo', 'type'=>'data')));
        $this->assertSame(201, $json['response']['code']);
        $objId = $json['resource']['id'];

        //create relation as client 2 to hidden resource - fails
        $json = $this->getJson('POST', '/api/v1/relations?_key=55678isafgd56789asfgdhf4568', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'subjectId' => $subId,
            'objectId' => $objId,
            'type' => 'based_on'
        )));
        $this->assertSame(403, $json['response']['code']);

        //as client one, create relation to public resource
        $json = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'subjectId' => $subId,
            'objectId' => $objId,
            'type' => 'based_on'
        )));
        $this->assertSame(201, $json['response']['code']);
    }

    public function testCreatingRelationsRequiresSubjectOwner()
    {
        //create subject/object resources
        $json = $this->createTestResource('45678isafgd56789asfgdhf4567');
        $subId = $json['resource']['id'];
        $json = $this->createTestResource('55678isafgd56789asfgdhf4568');
        $objId = $json['resource']['id'];

        //requires
        $data = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'subjectId' => $subId,
            'objectId' => $objId,
            'type' => 'requires'
        )));
        $this->assertSame(201, $data['response']['code']);
        $data = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'subjectId' => $objId,
            'objectId' => $subId,
            'type' => 'requires'
        )));
        $this->assertSame(403, $data['response']['code']);

        //transcript_of
        $data = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'subjectId' => $subId,
            'objectId' => $objId,
            'type' => 'transcript_of'
        )));
        $this->assertSame(201, $data['response']['code']);
        $data = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'subjectId' => $objId,
            'objectId' => $subId,
            'type' => 'transcript_of'
        )));
        $this->assertSame(403, $data['response']['code']);

        //references
        $data = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'subjectId' => $subId,
            'objectId' => $objId,
            'type' => 'references'
        )));
        $this->assertSame(201, $data['response']['code']);
        $data = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'subjectId' => $objId,
            'objectId' => $subId,
            'type' => 'references'
        )));
        $this->assertSame(403, $data['response']['code']);

        //based_on
        $data = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'subjectId' => $subId,
            'objectId' => $objId,
            'type' => 'based_on'
        )));
        $this->assertSame(201, $data['response']['code']);
        $data = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'subjectId' => $objId,
            'objectId' => $subId,
            'type' => 'based_on'
        )));
        $this->assertSame(403, $data['response']['code']);

        //translation_of
        $data = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'subjectId' => $subId,
            'objectId' => $objId,
            'type' => 'translation_of'
        )));
        $this->assertSame(201, $data['response']['code']);
        $data = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'subjectId' => $objId,
            'objectId' => $subId,
            'type' => 'translation_of'
        )));
        $this->assertSame(403, $data['response']['code']);

        //search
        $data = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'subjectId' => $subId,
            'objectId' => $objId,
            'type' => 'search'
        )));
        $this->assertSame(201, $data['response']['code']);
        $data = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'subjectId' => $objId,
            'objectId' => $subId,
            'type' => 'search'
        )));
        $this->assertSame(403, $data['response']['code']);
    }

    public function testCreatingRelationsRequiresOwnerOfSubjectAndObject()
    {
        $json = $this->createTestResource('45678isafgd56789asfgdhf4567');
        $r1 = $json['resource']['id'];
        $json = $this->createTestResource('45678isafgd56789asfgdhf4567');
        $r2 = $json['resource']['id'];
        $json = $this->createTestResource('55678isafgd56789asfgdhf4568');
        $r3 = $json['resource']['id'];

        //version_of
        $data = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'subjectId' => $r1,
            'objectId' => $r2,
            'type' => 'version_of'
        )));
        $this->assertSame(201, $data['response']['code']);
        $data = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'subjectId' => $r1,
            'objectId' => $r3,
            'type' => 'version_of'
        )));
        $this->assertSame(403, $data['response']['code']);
        $data = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'subjectId' => $r3,
            'objectId' => $r2,
            'type' => 'version_of'
        )));
        $this->assertSame(403, $data['response']['code']);

        //part_of
        $data = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'subjectId' => $r1,
            'objectId' => $r2,
            'type' => 'part_of'
        )));
        $this->assertSame(201, $data['response']['code']);
        $data = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'subjectId' => $r1,
            'objectId' => $r3,
            'type' => 'part_of'
        )));
        $this->assertSame(403, $data['response']['code']);
        $data = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'subjectId' => $r3,
            'objectId' => $r2,
            'type' => 'part_of'
        )));
        $this->assertSame(403, $data['response']['code']);
    }

    public function testCreateRelationWithInvalidAttributesFails()
    {
        $r1 = $this->createTestResource();
        $r2 = $this->createTestResource();

        //relation that has no attributes
        $data = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'subjectId' => $r1['resource']['id'],
            'objectId' => $r2['resource']['id'],
            'type' => 'based_on',
            'attributes' => array(
                'foo' => 'bar'
            )
        )));
        $this->assertSame(400, $data['response']['code']);

        //relation that does have attributes
        $data = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'subjectId' => $r1['resource']['id'],
            'objectId' => $r2['resource']['id'],
            'type' => 'transcript_of',
            'attributes' => array(
                'foo' => 'bar'
            )
        )));
        $this->assertSame(400, $data['response']['code']);
    }

    public function testGetResourceRelations()
    {
        $stubs = $this->createTestResourcesWithRelations();
        $subjectId = $stubs['subject']['id'];
        $objectId = $stubs['object']['id'];

        $data = $this->getJson('GET', '/api/v1/resources/'.$subjectId.'/relations?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertSame(200, $data['response']['code']);
        $this->assertSame(2, count($data['relations']));
        $rel1Id = $data['relations'][0]['id'];
        $rel2Id = $data['relations'][1]['id'];

        $data = $this->getJson('GET', '/api/v1/resources/'.$objectId.'/relations?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertSame(200, $data['response']['code']);
        $this->assertSame(2, count($data['relations']));
        $this->assertSame($rel1Id, $data['relations'][0]['id']);
        $this->assertSame($rel2Id, $data['relations'][1]['id']);

        //get relations when none exist
        $response = $this->createTestResource();
        $id = $response['resource']['id'];
        $data = $this->getJson('GET', '/api/v1/resources/'.$id.'/relations?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertTrue(isset($data['relations']));
        $this->assertTrue(is_array($data['relations']));
        $this->assertTrue(empty($data['relations']));
    }

    public function testFilterResourceRelationsByType()
    {
        $stubs = $this->createTestResourcesWithRelations();
        $subjectId = $stubs['subject']['id'];
        $objectId = $stubs['object']['id'];

        //filter by type on subject
        $data = $this->getJson('GET', '/api/v1/resources/'.$subjectId.'/relations?_key=45678isafgd56789asfgdhf4567&type=nonexisting,part_of', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertSame(200, $data['response']['code']);
        $this->assertSame(1, count($data['relations']));

        //filter by type on object
        $data = $this->getJson('GET', '/api/v1/resources/'.$objectId.'/relations?_key=45678isafgd56789asfgdhf4567&type=requires,nonexisting', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertSame(1, count($data['relations']));
    }

    public function testFilterRelations()
    {
        $stubs = $this->createTestResourcesWithRelations();
        $subjectId = $stubs['subject']['id'];
        $objectId = $stubs['object']['id'];

        //no ids specified
        $data = $this->getJson('GET', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));

        $this->assertTrue(in_array('total', $data));
        $this->assertSame(200, $data['response']['code']);
        $this->assertTrue(count($data['relations']) > 0);

        //filter subject OR object id
        $data = $this->getJson('GET', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567&id='.$subjectId, [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertSame(200, $data['response']['code']);
        $this->assertSame(2, count($data['relations']));

        //filter subjectId
        $data = $this->getJson('GET', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567&subjectId='.$subjectId, [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertSame(200, $data['response']['code']);
        $this->assertSame(1, count($data['relations']));

        //filter objectId
        $data = $this->getJson('GET', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567&objectId='.$subjectId, [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertSame(200, $data['response']['code']);
        $this->assertSame(1, count($data['relations']));

        //filter type
        $data = $this->getJson('GET', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567&id='.$subjectId.'&type=requires', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertSame(200, $data['response']['code']);
        $this->assertSame(1, count($data['relations']));
    }

    public function testDeleteRelation()
    {
        $res1 = $this->createTestResource();
        $res2 = $this->createTestResource();
        $subjectId = $res1['resource']['id'];
        $objectId = $res2['resource']['id'];
        $relationData = array(
            'subjectId' => $subjectId,
            'objectId' => $objectId,
            'type' => 'requires',
            'clientUser' => array(
                'id' => 'user1',
                'url' => 'http://example.com/users/user1'
            )
        );

        //create and check relation
        $rel = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($relationData));
        $this->assertSame(201, $rel['response']['code']);
        $this->assertTrue(isset($rel['relation']['id']));
        $relationId = $rel['relation']['id'];

        //check both resources, should both have the relation
        $subject = $this->getJson('GET', '/api/v1/resources/'.$subjectId.'?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $object = $this->getJson('GET', '/api/v1/resources/'.$objectId.'?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertTrue(isset($subject['resource']['relations'][0]['id']));
        $this->assertTrue(isset($object['resource']['relations'][0]['id']));
        $this->assertSame(
            $subject['resource']['relations'][0]['id'],
            $object['resource']['relations'][0]['id']
        );

        //delete the relation
        $response = $this->getJson('DELETE', '/api/v1/relations/'.$relationId.'?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));
        // $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(200, $response['response']['code']);

        //check resources again, neither should have relations
        $subject = $this->getJson('GET', '/api/v1/resources/'.$subjectId.'?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $object = $this->getJson('GET', '/api/v1/resources/'.$objectId.'?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));

        $this->assertTrue(empty($subject['resource']['relations']));
        $this->assertTrue(empty($object['resource']['relations']));
    }

    public function testDeleteResourceAlsoDeletesRelations()
    {
        $stubs = $this->createTestResourcesWithRelations();
        $subjectId = $stubs['subject']['id'];
        $objectId = $stubs['object']['id'];

        $data = $this->getJson('GET', '/api/v1/resources/'.$subjectId.'/relations?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertSame(200, $data['response']['code']);
        $this->assertSame(2, count($data['relations']));
        $rel1Id = $data['relations'][0]['id'];
        $rel2Id = $data['relations'][1]['id'];

        $data = $this->getJson('GET', '/api/v1/resources/'.$objectId.'/relations?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertSame(200, $data['response']['code']);
        $this->assertSame(2, count($data['relations']));
        $this->assertSame($rel1Id, $data['relations'][0]['id']);
        $this->assertSame($rel2Id, $data['relations'][1]['id']);

        //get raw object field, assert relations field
        $data = $this->getJson('GET', '/api/v1/resources/'.$objectId.'?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertSame(200, $data['response']['code']);
        $this->assertTrue(isset($data['resource']['relations']));
        $this->assertSame(2, count($data['resource']['relations']));

        //delete the subject resource
        $data = $this->getJson('DELETE', '/api/v1/resources/'.$subjectId.'?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));

        $this->assertSame(200, $data['response']['code']);

        //check the relations for both subject and object
        $data = $this->getJson('GET', '/api/v1/resources/'.$subjectId.'/relations?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertSame(410, $data['response']['code']);
        $this->assertFalse(isset($data['relations']));

        $data = $this->getJson('GET', '/api/v1/resources/'.$objectId.'/relations?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertSame(200, $data['response']['code']);
        $this->assertTrue(isset($data['relations']));
        $this->assertTrue(is_array($data['relations']));
        $this->assertTrue(empty($data['relations']));

        //get raw object, assert no relations field
        //get raw object field, assert relations field
        $data = $this->getJson('GET', '/api/v1/resources/'.$objectId.'?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertSame(200, $data['response']['code']);
        $this->assertTrue(empty($data['resource']['relations']));
    }
}
