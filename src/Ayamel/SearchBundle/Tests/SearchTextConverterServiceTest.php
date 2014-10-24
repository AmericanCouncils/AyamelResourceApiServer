<?php

namespace Ayamel\SearchBundle\Tests;

use Ayamel\ApiBundle\ApiTestCase;
use Ayamel\SearchBundle\SearchTextConverter;

/**
 * Make sure text conversion is configured properly for the application.  As configuration is changed in the app to support
 * new text formats, this case should be updated with new tests.  There should be a test per supported format.
 */
class SearchTextConverterServiceTest extends ApiTestCase
{
    
    protected function getService()
    {
        return $this->getContainer()->get('ayamel.search.text_converter');
    }
    
    protected function assertUtf8($string)
    {
        $this->assertTrue(mb_check_encoding($string, 'UTF-8'));
    }
    
    public function testLoadService()
    {
        $this->assertTrue($this->getService() instanceof SearchTextConverter);
    }
    
    public function testConvertAscii()
    {
        $this->assertUtf8($this->getService()->convertTextEncoding(file_get_contents(__DIR__.'/files/hamlet.en.txt')));
    }
    
    public function testConvertUtf8()
    {
        $this->assertUtf8($this->getService()->convertTextEncoding(file_get_contents(__DIR__.'/files/hamlet.ru.txt')));
    }
    
    public function testConvertWindows1251()
    {
        $this->assertUtf8($this->getService()->convertTextEncoding(file_get_contents(__DIR__.'/files/hamlet.win1251.txt')));
    }
}
