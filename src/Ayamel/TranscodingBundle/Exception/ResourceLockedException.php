<?php

namespace Ayamel\TranscodingBundle\Exception;

/**
 * This is thrown by the TranscodeManager when a Resource is to be transcoded, but is already locked.
 *
 * @package AyamelTranscodingBundle
 * @author Evan Villemez
 */
class ResourceLockedException extends \LogicException {}
