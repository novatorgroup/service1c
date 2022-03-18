<?php

namespace novatorgroup\service1c;

/**
 * HTTP service response
 */
class Response
{
    /**
     * Error message
     */
    public string $error = '';

    /**
     * HTTP code
     */
    public int $code = 0;

    /**
     * Response content
     */
    public string $result = '';

    /**
     * Response headers
     * @var array
     */
    public array $headers = [];

    /**
     * Request result is success
     * @return bool
     */
    public function isOk(): bool
    {
        return $this->code == 200 && empty($this->error) && !empty($this->result);
    }

    /**
     * Get response header
     * @param string $name - header name
     * @return string|null
     */
    public function getHeader(string $name): ?string
    {
        return $this->headers[mb_strtolower($name)] ?? null;
    }
}