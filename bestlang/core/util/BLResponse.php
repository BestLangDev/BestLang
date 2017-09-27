<?php

namespace bestlang\core\util;

class BLResponse
{
    private $status = 200;

    private $contentType = 'text/html';

    private $body = '';

    /**
     * BLResponse constructor.
     * @param int $status
     * @param string $contentType
     * @param string $body
     */
    public function __construct($status, $contentType, $body)
    {
        $this->status = $status;
        $this->contentType = $contentType;
        $this->body = $body;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
}