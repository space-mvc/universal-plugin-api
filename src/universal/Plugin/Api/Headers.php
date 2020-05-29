<?php

namespace Universal\Plugin\Api;

/**
 * Class Headers
 *
 * @package Universal\Plugin\Api
 */
class Headers
{
    /** @var array $headers */
    protected $headers = [
        'Cache-Control: no-cache, must-revalidate',
        'Expires: Mon, 26 Jul 1997 05:00:00 GMT',
        'Content-type: application/json',
    ];

    /**
     * Headers constructor.
     */
    public function __construct()
    {
        foreach($this->headers as $header) {
            header($header);
        }
    }
}
