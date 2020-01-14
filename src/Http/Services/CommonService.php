<?php
/*
 * @Author: Austin
 * @Date: 2020-01-09 18:18:25
 * @LastEditors  : Austin
 * @LastEditTime : 2020-01-14 18:57:40
 */

namespace Ifantace\Common\Http\Services;

use Ifantace\Common\CommonTraits;
use Illuminate\Http\Request;

class CommonService
{
    use CommonTraits;
    protected $input;

    public function initInput(Request $input)
    {
        $this->input = $input;
    }
}
