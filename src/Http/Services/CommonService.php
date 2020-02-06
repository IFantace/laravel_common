<?php

/*
 * @Author: Austin
 * @Date: 2020-01-09 18:18:25
 * @LastEditors  : Austin
 * @LastEditTime : 2020-02-06 17:35:55
 */

namespace Ifantace\Common\Http\Services;

use Ifantace\Common\CommonTraits;
use Illuminate\Http\Request;

class CommonService
{
    use CommonTraits;

    protected $input;

    /**
     * Service初始化input
     *
     * @param Request $input
     * @return void
     */
    public function initInput(Request $input)
    {
        $this->input = $input;
    }
}
