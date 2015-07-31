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
    private $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

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

        // make api call
        $result = $this->callApi($videoId);

        // check if there was a result at all
        if (count($result['items']) == 0) {
            throw new HttpException(404, "No video found by that id.");
        }

        $data = $result['items'][0];

        //create resource
        $res = new Resource();
        $res->setStatus(Resource::STATUS_NORMAL);
        $res->setType('video');
        $res->content = new ContentCollection();

        //set title
        if (isset($data['snippet']['title'])) {
            $res->setTitle($data['snippet']['title']);
        } else {
            $res->setTitle('Untitled YouTube Video');
        }

        if (isset($data['snippet']['description'])) {
            $res->setDescription($data['snippet']['description']);
        }

        // use tags as keywords, also check each tag for possible use in topics
        if (isset($data['snippet']['tags'])) {
            $res->setKeywords(implode(',', $data['snippet']['tags']));

            $topics = [];
            // foreach ($data['snippet']['tags'] as $tag) {
            //     // TODO: check available topics
            // }
            $res->setTopics($topics);
        }

        //populate license field properly
        if (isset($data['status']['license'])) {
            switch ($data['status']['license']) {
                case 'cc': $res->setLicense('CC BY'); break;
                case 'youtube': $res->setLicense('youtube'); break;
                default: break;
            }
        }

        //create oembed - some fields here used elsewhere
        $oem = new OEmbed();
        $oemData = json_decode(
            file_get_contents(
                sprintf('http://www.youtube.com/oembed?url=%s&format=json', urlencode(sprintf('http://www.youtube.com/watch?v=%s', $videoId)))
            ), true
        );

        if ($oemData) {
            foreach ($oemData as $key => $val) {
                $oem->$key = $val;
            }

            $res->content->setOembed($oem);
        }


        //create content
        $res->content->setCanonicalUri(sprintf('https://www.youtube.com/embed/%s', $videoId));

        // add thumbnails
        if (isset($data['snippet']['thumbnails'])) {
            foreach ($data['snippet']['thumbnails'] as $key => $item) {
                $ref = new FileReference();
                $ref->setMime('image/jpeg');
                $ref->setMimeType('image/jpeg');
                $ref->setDownloadUri($item['url']);
                $ref->setRepresentation('summary');
                $ref->setAttribute('frameSize', [
                    'height' => $item['height'],
                    'width' => $item['width']
                ]);
                $res->content->addFile($ref);
            }
        }

        //create origin
        $o = new Origin();
        $o->setFormat('YouTube Video');
        $o->setUri(sprintf("https://www.youtube.com/watch?v=%s", $videoId));

        if (isset($data['snippet']['channelTitle'])) {
            $o->setCreator($data['snippet']['channelTitle']);
        }
        if (isset($data['snippet']['publishedAt'])) {
            $o->setDate($data['snippet']['publishedAt']);
        }
        $res->origin = $o;

        return $res;
    }

    private function callApi($id)
    {
        try {
            $result = $this->client->videos->listVideos('snippet,status', [
              'id' => $id,
              'maxResults' => 1,
            ]);
        } catch (\Google_Service_Exception $e) {
            $code = $e->getCode() <= 200 ? $code : 500;
            $errors = $e->getErrors();
            $msg = count($errors) > 0 ? $errors[0]['message'] : "Request to YouTube failed.";
            throw new HttpException($code, $msg);
        }

        return $result;
    }
}
