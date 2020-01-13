<?php
/*
 * @Author: Austin
 * @Date: 2020-01-09 18:18:25
 * @LastEditors: Austin
 * @LastEditTime: 2020-01-13 18:09:40
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
