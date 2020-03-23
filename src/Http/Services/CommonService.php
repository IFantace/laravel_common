<?php

/*
 * @Author: Austin
 * @Date: 2020-01-09 18:18:25
 * @LastEditors  : Austin
 * @LastEditTime : 2020-03-23 11:53:38
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
        if ($input->has("event_uuid") && $this->event_uuid === null) {
            $this->event_uuid = $input->get("event_uuid");
        }
    }
}
