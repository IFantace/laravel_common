<?php

/*
 * @Author       : Austin
 * @Date         : 2020-03-25 17:09:18
 * @LastEditors  : Austin
 * @LastEditTime : 2020-03-25 17:18:07
 * @Description  : {{Description this}}
 */

namespace Ifantace\Common\Objects;

use Exception;

class ResponseException extends \Exception
{
    /**
     * response
     *
     * @var array|string
     */
    private $response;

    public function __construct(
        $message,
        $code = 0,
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }
}
