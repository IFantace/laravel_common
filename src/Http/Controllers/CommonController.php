<?php

/*
 * @Author       : Austin
 * @Date         : 2019-07-30 17:50:14
 * @LastEditors  : Austin
 * @LastEditTime : 2020-10-23 16:34:38
 * @Description  : {{Description this}}
 */

namespace Ifantace\Common\Http\Controllers;

use App\Http\Controllers\Controller;
use Ifantace\Common\CommonTraits;

class CommonController extends Controller
{
    use CommonTraits;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $message = config("common.message");
        //Common $this->loadViewsFrom(__DIR__ . '/resources/views', 'Common');
        return view('Common::welcome', compact('message'));
    }
}
