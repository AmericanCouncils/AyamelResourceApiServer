<?php

namespace Ayamel\ApiBundle\Tests;

use Ayamel\ApiBundle\Validation\UploadTokenManager;
use Doctrine\Common\Cache\ArrayCache;

class UploadTokenManagerTest extends \PHPUnit_Framework_TestCase {
    
    protected function getManager() {
        return new UploadTokenManager(new ArrayCache());
    }
    
    public function testInstantiate() {
        $m = $this->getManager();
        $this->assertNotNull($m);
        $this->assertTrue($m instanceof UploadTokenManager);
    }
    
    public function testHasCreateGetRemoveTokenForId() {
        $m = $this->getManager();
        $id = "test_id";
        $this->assertFalse($m->hasTokenforId($id));
        $this->assertFalse($m->getTokenForId($id));
        $createdToken = $m->createTokenForId($id);
        $this->assertTrue(is_string($createdToken));
        $this->assertTrue($m->hasTokenForId($id));
        $retrievedToken = $m->getTokenForId($id);
        $this->assertTrue(is_string($retrievedToken));
        $this->assertSame($createdToken, $retrievedToken);
        $m->removeTokenForId($id);
        $this->assertFalse($m->hasTokenforId($id));
        $this->assertFalse($m->getTokenForId($id));
    }
    
    public function testUseToken1() {
        $m = $this->getManager();
        $token = "does_not_exist";
        $id = 'test_id';
        $this->setExpectedException('InvalidArgumentException');
        $m->useTokenForId($id, $token);
    }
    
    public function testUseToken2() {
        $m = $this->getManager();
        $id = 'test_id';
        $token = $m->createTokenForId("different_id");
        $this->setExpectedException('InvalidArgumentException');
        $m->useTokenForId($id, $token);
    }

    public function testUseToken3() {
        $m = $this->getManager();
        $id = 'test_id';
        $token = $m->createTokenForId($id);
        $this->assertTrue($m->hasTokenForId($id));
        $this->assertTrue($m->useTokenForId($id, $token));
        $this->assertFalse($m->hasTokenForId($id));
    }

    public function testClearTokens() {
        $m = $this->getManager();
        $m->createTokenForId('id1');
        $m->createTokenForId('id2');
        $m->createTokenForId('id3');
        $this->assertTrue($m->hasTokenForId('id1'));
        $this->assertTrue($m->hasTokenForId('id2'));
        $this->assertTrue($m->hasTokenForId('id3'));
        $m->clearTokens();
        $this->assertFalse($m->hasTokenForId('id1'));
        $this->assertFalse($m->hasTokenForId('id2'));
        $this->assertFalse($m->hasTokenForId('id3'));
    }
}