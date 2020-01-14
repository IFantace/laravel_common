<?php
/*
 * @Author: Austin
 * @Date: 2020-01-09 18:18:25
 * @LastEditors  : Austin
 * @LastEditTime : 2020-01-14 18:24:46
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
    public function checkDuplicate($obj)
    {
        if ($obj->findDuplicate()) {
            return $this->generateResponseArray(
                -2,
                'duplicate',
                trans('general.duplicate')
            );
        }
        return ["status" => 1];
    }
}
