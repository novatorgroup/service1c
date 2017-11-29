<?php

namespace novatorgroup\service1c;

/**
 * HTTP service response
 */
class Response
{
    /**
     * Error message
     * @var string
     */
    public $error;

    /**
     * HTTP code
     * @var int
     */
    public $code;

    /**
     * Response content
     * @var string
     */
    public $result;

    /**
     * Request result is success
     * @return bool
     */
    public function isOk()
    {
        return $this->code == 200 && empty($this->error) && !empty($this->result);
    }
}