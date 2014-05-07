<?php

namespace Ayamel\YouTubeBundle;

use Ayamel\ResourceBundle\Provider\ProviderInterface;
use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\Origin;
use Ayamel\ResourceBundle\Document\ContentCollection;
use Ayamel\ResourceBundle\Document\FileReference;
use Ayamel\ResourceBundle\Document\OEmbed;
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
        curl_setopt($call, CURLOPT_HEADER, 0);
        curl_setopt_array($call, array(
            CURLOPT_URL => $youtubeApiUrl,
            CURLOPT_RETURNTRANSFER => true, // return web page
            CURLOPT_HEADER => false, // don't return headers
            CURLOPT_FOLLOWLOCATION => true, // follow redirects
            CURLOPT_ENCODING => "", // handle compressed
            CURLOPT_USERAGENT => "test", // who am i
            CURLOPT_AUTOREFERER => true, // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
            CURLOPT_TIMEOUT => 120, // timeout on response
            CURLOPT_MAXREDIRS => 5
        ));
        $data = curl_exec($call);
        $code = curl_getinfo($call, CURLINFO_HTTP_CODE);
        curl_close($call);

        //check for error
        if (200 !== $code) {
            throw new HttpException($code, "Failed to create YouTube resource.");
        }

        $data = json_decode($data, true);

        //create resource
        $res = new Resource();
        $res->setStatus(Resource::STATUS_NORMAL);
        $res->setType('video');
        $res->content = new ContentCollection();

        //set title
        if (isset($data['entry']['title']['$t'])) {
            $res->setTitle($data['entry']['title']['$t']);
        } else if (isset($data['entry']['media$group']['media$description']['$t'])) {
            $res->setTitle($data['entry']['media$group']['media$description']['$t']);
        } else {
            $res->setTitle('Untitled YouTube Video');
        }


        if (isset($data['entry']['media$group']['media$description']['$t'])) {
            $res->setDescription($data['entry']['media$group']['media$description']['$t']);
        }
        
        // TODO: Use topics field for each category label that validates
        if (isset($data['entry']['category'])) {
            $subjectDomains = array();
            foreach ($data['entry']['category'] as $cat) {
                if (isset($cat['label'])) {
                    $subjectDomains[] = $cat['label'];
                }
            }

            $res->setSubjectDomains($subjectDomains);
        }

        //populate license field properly
        if (isset($data['entry']['media$group']['media$license']['$t'])) {
            switch ($data['entry']['media$group']['media$license']['$t']) {
                case 'cc': $res->setLicense('CC BY'); break;
                case 'youtube': $res->setLicense('youtube'); break;
                default: break;
            }
        }

        //create content
        if (isset($data['entry']['media$group']['media$player']['url'])) {
            $res->content->setCanonicalUri($data['entry']['media$group']['media$player']['url']);
        }
        if (isset($data['entry']['media$group']['media$content'])) {
            foreach ($data['entry']['media$group']['media$content'] as $item) {
                $ref = new FileReference();
                if (isset($item['isDefault'])) {
                    $ref->setRepresentation('original');
                } else {
                    $ref->setRepresentation('transcoding');
                }
                $ref->setMime($item['type']);
                $ref->setMimeType($item['type']);
                $ref->setStreamUri($item['url']);
                $res->content->addFile($ref);
            }

            if (isset($data['entry']['media$group']['media$thumbnail'])) {
                foreach ($data['entry']['media$group']['media$thumbnail'] as $item) {
                    $ref = new FileReference();
                    $ref->setMime('image/jpeg');
                    $ref->setMimeType('image/jpeg');
                    $ref->setDownloadUri($item['url']);
                    $ref->setRepresentation('summary');

                    $res->content->addFile($ref);
                }
            }
        }

        //create origin
        $o = new Origin();
        $o->setFormat('YouTube Video');
        $o->setUri($res->content->getCanonicalUri());
        if (isset($data['entry']['author'][0]['name']['$t'])) {
            $o->setCreator($data['entry']['author'][0]['name']['$t']);
        }
        if (isset($data['entry']['published']['$t'])) {
            $o->setDate($data['entry']['published']['$t']);
        }
        $res->origin = $o;

        //create oembed
        $oem = new OEmbed();
        $data = json_decode(
                    file_get_contents(
                        sprintf('http://www.youtube.com/oembed?url=%s&format=json', urlencode(sprintf('http://www.youtube.com/watch?v=%s', $videoId)))
                    ), true
                );
        if ($data) {
            foreach ($data as $key => $val) {
                $oem->$key = $val;
            }

            $res->content->setOembed($oem);
        }

        return $res;
    }
}
