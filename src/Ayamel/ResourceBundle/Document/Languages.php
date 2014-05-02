<?php

namespace Ayamel\ResourceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as JMS;
use AC\ModelTraits\AutoGetterSetterTrait;

/**
 * Language standards supported.
 *
 * @MongoDB\EmbeddedDocument
 */
class Languages
{
    use AutoGetterSetterTrait;

    /**
     * Array of ISO 639-3 language codes.
     *
     * @MongoDB\Collection
     * @JMS\Type("array<string>")
     */
    protected $iso639_3;

    /**
     * Array of BCP 47 language codes.
     *
     * @MongoDB\Collection
     * @JMS\Type("array<string>")
     */
    protected $bcp47;

}
