<?php

namespace Ayamel\SearchBundle\Exception;

class BulkIndexException extends IndexException
{
    /**
     * A hash of IDs => exception messages
     *
     * @var hash
     */
    protected $messages = [];

    public function __construct(array $messages)
    {
        $this->messages = $messages;

        parent::__construct("Multiple Resources (".$this->getCount().") could not be indexed.");
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function getCount()
    {
        return count($this->messages);
    }
}
