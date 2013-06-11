<?php

namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as JMS;

/**
 * OEmbed document.  For more on OEmbed, see the full spec at [http://oembed.com](http://oembed.com/).
 *
 * @MongoDB\EmbeddedDocument
 * 
 * @package AyamelResourceBundle
 * @author Evan Villemez
 */
class OEmbed
{
    /**
     * The resource type.
     *
     * @MongoDB\String
     * @JMS\Type("string")
     */
    public $type;

    /**
     * The oEmbed version number. This must be 1.0.
     *
     * @MongoDB\String
     * @JMS\Type("string")
     */
    public $version = "1.0";

    /**
     * A text title, describing the resource.
     *
     * @MongoDB\String
     * @JMS\Type("string")
     */
    public $title;

    /**
     * The name of the author/owner of the resource.
     *
     * @MongoDB\String
     * @JMS\Type("string")
     */
    public $author_name;

    /**
     * A URL for the author/owner of the resource.
     *
     * @MongoDB\String
     * @JMS\Type("string")
     */
    public $author_url;

    /**
     * The name of the resource provider.
     *
     * @MongoDB\String
     * @JMS\Type("string")
     */
    public $provider_name;

    /**
     * The url of the resource provider.
     *
     * @MongoDB\String
     * @JMS\Type("string")
     */
    public $provider_url;

    /**
     * A URL to an optional thumbnail image representing the resource.
     *
     * @MongoDB\String
     * @JMS\Type("string")
     */
    public $thumbnail_url;

    /**
     * The height in pixels of the optional thumbnail.
     *
     * @MongoDB\Int
     * @JMS\Type("integer")
     */
    public $thumbnail_height;

    /**
     * The height in pixels of the optional thumbnail.
     *
     * @MongoDB\Int
     * @JMS\Type("integer")
     */
    public $thumbnail_width;

    /**
     * The suggested cache lifetime for this resource, in seconds. Consumers may choose to use this value or not.
     *
     * @MongoDB\Int
     * @JMS\Type("integer")
     */
    public $cache_age;

    /**
     * The HTML required to embed a media player. The HTML should have no padding or margins. Consumers may wish
     * to load the HTML in an off-domain iframe to avoid XSS vulnerabilities.
     *
     * @MongoDB\String
     * @JMS\Type("string")
     */
    public $html;

    /**
     * The source URL of the image, if the embedded content is an image.
     *
     * @MongoDB\String
     * @JMS\Type("string")
     */
    public $url;

    /**
     * The height in pixels required to display the HTML.
     *
     * @MongoDB\Int
     * @JMS\Type("integer")
     */
    public $height;

    /**
     * The width in pixels required to display the HTML.
     *
     * @MongoDB\Int
     * @JMS\Type("integer")
     */
    public $width;
}
