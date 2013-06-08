<?php

namespace Ayamel\YouTubeBundle;

use Ayamel\ResourceBundle\Provider\ProviderInterface;
use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\Origin;
use Ayamel\ResourceBundle\Document\ContentCollection;
use Ayamel\ResourceBundle\Document\FileReference;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Derives Resources from YouTube videos by querying its public API.  This currently depends on using
 * Version 2 of the YouTube API.
 *
 * @package AyamelYouTubeBundle
 * @author Evan Villemez
 */
class YouTubeResourceProvider implements ProviderInterface
{
    public function getKey()
    {
        return 'youtube';
    }
    
    public function handlesScheme($scheme)
    {
        return $scheme === 'youtube';
    }
    
    public function createResourceFromUri($uri)
    {
        $exp = explode('://', $uri);
        $videoId = $exp[1];
        
        //call youtube api
        $youtubeApiUrl = sprintf("https://gdata.youtube.com/feeds/api/videos/%s?v=2&alt=json", $videoId);
        $call = curl_init();
        $data = curl_exec($youtubeApiUrl);
        $code = curl_getinfo($call, CURLINFO_HTTP_CODE);
        curl_close($call);
        
        //check for error
        if (200 !== $code) {
            throw HttpException($code);
        }
        
        $data = json_decode($data, true);
        
        //create resource
        $res = new Resource();
        $res->content = new ContentCollection();
        $res->setType('video');
        if (isset($data['title']['$t'])) {
            $res->setTitle($data['title']['$t']);
        }
        if (isset($data['media$group']['media$description']['$t'])) {
            $res->setDescription($data['media$group']['media$description']['$t']);
        }
        if (isset($data['categories'])) {
            $subjectDomains = array();
            foreach ($data['categories'] as $cat) {
                if (isset($cat['label'])) {
                    $subjectDomains[] = $cat['label'];
                }
            }

            $res->setSubjectDomains($subjectDomains);
        }
        if (isset($data['media$group']['media$license']['$t'])) {
            $res->setLicense($data['media$group']['media$license']['$t']);
        }
        
        //create content
        if (isset($data['media$gruop']['media$player']['url'])) {
            $res->content->setCanonicalUri($data['media$gruop']['media$player']['url']);
        }
        if (isset($data['media$group']['media$content'])) {
            foreach ($data['media$group']['media$content'] as $item) {
                $ref = new FileReference();
                if (isset($item['isDefault'])) {
                    $ref->setRepresentation('original');
                } else {
                    $ref->setRepresentation('transcoding');
                }
                $ref->setMime($item['type']);
                $ref->setMimeType($item['type']);
                $ref->setDownloadUrl($item['url']);
                $res->content->addFile($ref);
            }
            
            if (isset($data['media$group']['media$thumbnail'])) {
                foreach ($data['media$group']['media$thumbnail'] as $item) {
                    $ref = new FileReference();
                    $ref->setMime('image/jpeg');
                    $ref->setMimeType('image/jpeg');
                    $ref->setDownloadUrl($item['url']);
                    $ref->setRepresentation('summary');

                    $res->content->addFile($ref);
                }
            }
        }
        
        //create origin
        $o = new Origin();
        $o->setFormat('YouTube Video');
        $o->setUri($resource->content->getCanonicalUri());
        if (isset($data['author']['name']['$t'])) {
            $o->setCreator($data['author']['name']['$t']);
        }
        if (isset($data['published']['$t'])) {
            $o->setDate($data['published']['$t']);
        }
        
        //TODO: OEmbed call
        
        return $res;
    }
}
