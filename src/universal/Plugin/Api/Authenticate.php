<?php

namespace Universal\Plugin\Api;

use Illuminate\Support\Facades\Input;

/**
 * Class Authenticate
 *
 * @package Universal\Plugin\Api
 */
class Authenticate
{
    /** @var string $token */
    protected $token = 't4N7p0QbAKJDwGyJ';

    /**
     * Authenticate constructor.
     */
    public function __construct()
    {
        $token = Input::get('token');

        if($token !== $this->token) {

            echo json_encode(
                [
                    'exception' => [
                        'code' => 401,
                        'message' => 'Unauthorized error',
                    ],
                ]
            );

            exit;
        }
    }
}
