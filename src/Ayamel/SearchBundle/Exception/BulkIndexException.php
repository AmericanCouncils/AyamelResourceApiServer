<?php

namespace Ayamel\SearchBundle\Exception;

class BulkIndexException extends IndexException
{
    /**
     * A hash of IDs => exception messages
     *
     * @var hash
     */
    protected $messages = array();

    public function __construct(array $messages)
    {
        $this->messages = $messages;

        parent::__construct("Multiple Resources could not be indexed.");
    }

    public function getMessages()
    {
        return $this->messages;
    }
}
