<?php

/*
 * @Author: Austin
 * @Date: 2020-01-09 18:18:25
 * @LastEditors  : Austin
 * @LastEditTime : 2020-02-05 20:06:59
 */

namespace Ifantace\Common\Http\Controllers;

use App\Http\Controllers\Controller;
use Ifantace\Common\CommonTraits;
use Illuminate\Http\Request;

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
    public function download(Request $input, $type)
    {
        switch ($type) {
            case "log":
                if (!$input->has("path") || !$input->has("token")) {
                    return "error";
                }
                return $this->downloadLog($input->get("path"), $input->get("token"));
                break;
            default:
                break;
        }
    }
}
