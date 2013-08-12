<?php

use Ayamel\ApiBundle\ApiTestCase;

class VideoTranscodeTest extends ApiTestCase
{
    
    /**
     * high quality original, hits all presets
     *
     * @group transcoding
     */
    public function testTranscodeOfNewResourceWithHighQualityVideo()
    {
        $this->markTestSkipped('must determine proper presets first');
    }

    /**
     * low quality original, most presets filtered out
     *
     * @group transcoding
     */
    public function testTranscodeOfNewResourceWithLowQualityVideo()
    {
        $this->markTestSkipped('must determine proper presets first');
    }
}
