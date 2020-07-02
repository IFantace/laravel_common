<?php

/*
 * @Author       : Austin
 * @Date         : 2020-03-25 17:09:18
 * @LastEditors  : Austin
 * @LastEditTime : 2020-07-02 18:27:07
 * @Description  : {{Description this}}
 */

namespace Ifantace\Common\Objects;

use Exception;

class ResponseException extends \Exception
{
    /**
     * response array
     *
     * @var array
     */
    private $response;

    public function __construct(
        $message,
        $code = 0,
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * get response object
     *
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * set response object
     *
     * @param array $response
     * @return Ifantace\Common\Objects\ResponseException
     */
    public function setResponse(array $response)
    {
        $this->response = $response;
        return $this;
    }
}
