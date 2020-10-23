<?php

/*
 * @Author       : Austin
 * @Date         : 2019-07-31 14:13:36
 * @LastEditors  : Austin
 * @LastEditTime : 2020-10-23 16:34:28
 * @Description  : {{Description this}}
 */

namespace Ifantace\Common\Http\Services;

use Illuminate\Http\Request;

class CommonService
{
    protected $response_array;
    protected $input;

    public function initInput(Request $input)
    {
        $this->input = $input;
    }
}
