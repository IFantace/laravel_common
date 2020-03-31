<?php

/*
 * @Author: Austin
 * @Date: 2020-01-09 18:18:25
 * @LastEditors  : Austin
 * @LastEditTime : 2020-03-31 16:25:39
 */

namespace Ifantace\Common\Http\Services;

use Ifantace\Common\CommonTraits;
use Ifantace\Common\Objects\Response;
use Illuminate\Http\Request;

class CommonService
{
    use CommonTraits;

    /**
     * request from route
     *
     * @var Illuminate\Http\Request
     */
    protected $input;

    /**
     * custom response object
     *
     * @var Ifantace\Common\Objects\Response
     */
    protected $response;

    /**
     * Service初始化
     *
     * @param Request $input
     * @return void
     */
    public function init(Request &$input, Response &$response)
    {
        $this->input = $input;
        $this->response = $response;
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
        $this->response->setStatus($status)->setMessage($message)->setUIMessage($ui_message)->setData($data);
    }
}
