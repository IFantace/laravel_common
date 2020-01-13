<?php
/*
 * @Author: Austin
 * @Date: 2020-01-09 18:18:25
 * @LastEditors: Austin
 * @LastEditTime: 2020-01-13 18:17:32
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
