<?php

namespace Ayamel\SearchBundle\Tests;

use Ayamel\SearchBundle\SearchTextConverter;

class SearchTextConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $c = new SearchTextConverter();
        $this->assertTrue($c instanceof SearchTextConverter);

        $c = new SearchTextConverter(['UTF-8','Windows-1251']);
        $this->assertTrue($c instanceof SearchTextConverter);
    }
    
    public function testDetectTextEncoding()
    {
        $c = new SearchTextConverter(['UTF-8', 'Windows-1251']);
        $this->assertSame('UTF-8', $c->detectTextEncoding(file_get_contents(__DIR__.'/files/hamlet.ru.txt')));
        $this->assertSame('UTF-8', $c->detectTextEncoding(file_get_contents(__DIR__.'/files/hamlet.en.txt')));
        $this->assertSame('Windows-1251', $c->detectTextEncoding(file_get_contents(__DIR__.'/files/hamlet.win1251.txt')));
        $this->assertFalse($c->detectTextEncoding(file_get_contents(__DIR__.'/files/not_utf8_text.txt')));
    }
    
    public function testConvertTextEncoding()
    {
        $c = new SearchTextConverter(['UTF-8', 'Windows-1251']);
        
        //convert 1251 to utf8
        $win1251 = file_get_contents(__DIR__.'/files/hamlet.win1251.txt');        
        $this->assertFalse(mb_check_encoding($win1251, 'UTF-8'));
        $utf8 = $c->convertTextEncoding($win1251);
        $this->assertTrue(mb_check_encoding($utf8, 'UTF-8'));
        $this->assertTrue(0 === strpos($utf8, "Быть иль не быть"));
        
        //convert utf8 to utf8, ensure not garbled
        $utf8 = file_get_contents(__DIR__.'/files/hamlet.en.txt');
        $this->assertTrue(mb_check_encoding($utf8, 'UTF-8'));
        $utf8_2 = $c->convertTextEncoding($utf8);
        $this->assertTrue(mb_check_encoding($utf8_2, 'UTF-8'));
        $this->assertTrue(0 === strpos($utf8_2, "To be, or not to be"));
    }
}
