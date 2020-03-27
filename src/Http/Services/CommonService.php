<?php

/*
 * @Author: Austin
 * @Date: 2020-01-09 18:18:25
 * @LastEditors  : Austin
 * @LastEditTime : 2020-03-27 15:53:03
 */

namespace Ifantace\Common\Http\Services;

use Ifantace\Common\CommonTraits;
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

    /**
     * 設定service的response
     *
     * @param integer $status
     * @param string $message
     * @param string $ui_message
     * @param array $data
     * @return void
     */
    public function setResponseArray(int $status, string $message, string $ui_message, array $data = [])
    {
        $this->response_array = $this->generateResponseArray(
            $status,
            $message,
            $ui_message,
            $data
        );
    }
}
