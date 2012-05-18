<?php

namespace Ayamel\FilesystemBundle\Tests;

use Ayamel\FilesystemBundle\Filesystem\FilesystemInterface;
use Ayamel\FilesystemBundle\Filesystem\LocalFilesystem;
use Ayamel\ResourceBundle\Document\FileReference;


class LocalFilesystemTest extends \PHPUnit_Framework_TestCase {
    
    public function setUp() {
        $this->test_dir = __DIR__."/files/test_root";
        
        if(is_dir($this->test_dir)) {
            $this->rrmdir($this->test_dir);
            mkdir($this->test_dir, 0777);
        } else {
            mkdir($this->test_dir, 0777);
        }
    }
    
    protected function rrmdir($dir) {
        $fp = opendir($dir);
        if ( $fp ) {
            while ($f = readdir($fp)) {
                $file = $dir . DIRECTORY_SEPARATOR . $f;
                if ($f == "." || $f == "..") {
                    continue;
                }
                else if (is_dir($file) && !is_link($file)) {
                    $this->rrmdir($file);
                }
                else {
                    unlink($file);
                }
            }
            closedir($fp);
            rmdir($dir);
        }
    }        
    
    public function tearDown() {
        return $this->setUp();
    }
    
    //get test FS
    protected function getFs() {
        return new LocalFilesystem($this->test_dir, "secret");
    }
    
    //get test hash info for an id
    protected function getHashInfo($id) {
        $fs = $this->getFs();
        
        return array(
            'basePath' => $fs->generateBasePathForId($id),
            'baseFileName' => $fs->generateBaseFilenameForId($id),
            'baseDirectory' => $fs->generateBaseDirectoryForId($id),
            'secret' => $fs->createFileSecretForId($id),
        );
    }
    
    public function testInstantiate() {
        $fs = $this->getFs();
        $this->assertNotNull($fs);
        $this->assertTrue($fs instanceof LocalFilesystem);
    }
    
    public function testGetCount() {
        $fs = $this->getFs();
        $this->assertSame(0, $fs->getCount());
        $this->assertSame(0, $fs->getCount(FilesystemInterface::COUNT_ALL));
    }
    
    //add file and inherit name
    public function testAddFileForId1() {
        $id = "id";
        $info = $this->getHashInfo($id);
        $fs = $this->getFs();
        
        $newFilePath = $info['basePath']."foo.txt";
        
        $this->assertFalse(is_dir($info['baseDirectory']));
        $this->assertFalse(is_file($newFilePath));
        $this->assertSame(0, $fs->getCount());
        $this->assertSame(0, $fs->getCount(FilesystemInterface::COUNT_ALL));
        
        $ref = FileReference::createFromLocalPath(__DIR__."/files/foo.txt");
        
        $newRef = $fs->addFileForId($id, $ref, null, true);
        
        $this->assertTrue($newRef instanceof FileReference);
        $this->assertSame($newFilePath, $newRef->getInternalUri());
        $this->assertTrue(is_dir($info['baseDirectory']));
        $this->assertTrue(is_file($newFilePath));
        $this->assertSame(1, $fs->getCount());
        $this->assertSame(4, $fs->getCount(FilesystemInterface::COUNT_ALL));
    }
    
    //add file and change name
    public function testAddFileForId2() {
        $id = "id";
        $info = $this->getHashInfo($id);
        $fs = $this->getFs();
        
        $newFilePath = $info['basePath']."newname.txt";
        
        $this->assertFalse(is_dir($info['baseDirectory']));
        $this->assertFalse(is_file($newFilePath));

        $ref = FileReference::createFromLocalPath(__DIR__."/files/foo.txt");

        $newRef = $fs->addFileForId($id, $ref, 'newname.txt', true);

        $this->assertTrue($newRef instanceof FileReference);
        $this->assertSame($newFilePath, $newRef->getInternalUri());

        $this->assertTrue(is_dir($info['baseDirectory']));
        $this->assertTrue(is_file($newFilePath));
        $this->assertSame(1, $fs->getCount());
        $this->assertSame(4, $fs->getCount(FilesystemInterface::COUNT_ALL));
    }
    
    //add multiple files
    public function testAddFileForId3() {
        $id = "id";
        $info = $this->getHashInfo($id);
        $fs = $this->getFs();
        
        $this->assertFalse(is_dir($info['baseDirectory']));

        $ref = FileReference::createFromLocalPath(__DIR__."/files/foo.txt");

        $newRef = $fs->addFileForId($id, $ref, 'newname1.txt', true);
        $newRef = $fs->addFileForId($id, $ref, 'newname2.txt', true);
        $newRef = $fs->addFileForId($id, $ref, 'newname3.txt', true);
        $newRef = $fs->addFileForId($id, $ref, 'newname4.txt', true);

        $this->assertTrue(is_dir($info['baseDirectory']));
        $this->assertSame(4, $fs->getCount());
        $this->assertSame(7, $fs->getCount(FilesystemInterface::COUNT_ALL));
    }
    
    public function testRemoveFilesForId() {
        $this->testAddFileForId3();
        $fs = $this->getFs();
        $fs->removeFilesForId('id');
        $this->assertSame(0, $fs->getCount());
        $this->assertSame(3, $fs->getCount(FilesystemInterface::COUNT_ALL));
    }
    
    public function testRemoveFileForId() {
        $this->testAddFileForId3();
        $fs = $this->getFs();
        $this->assertSame(4, $fs->getCount());
        $this->assertSame(7, $fs->getCount(FilesystemInterface::COUNT_ALL));
        $fs->removeFileForId('id', 'newname4.txt');
        $this->assertSame(3, $fs->getCount());
        $this->assertSame(6, $fs->getCount(FilesystemInterface::COUNT_ALL));
    }
    
    public function testGetFilesForId() {
        $this->testAddFileForId3();
        $fs = $this->getFs();
        
        $files = $fs->getFilesForId('id');
        
        $i = 0;
        foreach($files as $file) {
            $i++;
            $this->assertTrue($file instanceof FileReference);
            $this->assertTrue(is_file($file->getInternalUri()));
            $this->assertSame("newname$i.txt", substr($file->getInternalUri(), -12, 12));
        }
        
        $this->assertSame(4, $i);
    }
    
}
