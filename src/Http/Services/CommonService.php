<?php

/*
 * @Author: Austin
 * @Date: 2020-01-09 18:18:25
 * @LastEditors  : Austin
 * @LastEditTime : 2020-03-25 17:41:46
 */

namespace Ifantace\Common\Http\Services;

use Ifantace\Common\CommonTraits;
use Ifantace\Common\Objects\ResponseException;
use Illuminate\Http\Request;

class CommonService
{
    use CommonTraits;

    protected $input;
    protected $response_array;

    /**
     * Service初始化input
     *
     * @param Request $input
     * @return void
     */
    public function initInput(Request $input)
    {
        $this->input = $input;
        if ($input->has("event_uuid") && $this->event_uuid === null) {
            $this->event_uuid = $input->get("event_uuid");
        }
    }

    public function setResponseArray(int $status, string $message, string $ui_message)
    {
        $this->response_array = $this->generateResponseArray(
            $status,
            $message,
            $ui_message
        );
    }

    public function throwResponseException()
    {
        $this_exception = new ResponseException($this->response_array["message"]);
        $this_exception->setResponse($this->response_array);
        throw $this_exception;
    }
}
