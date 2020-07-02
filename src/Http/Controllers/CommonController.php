<?php

/*
 * @Author: Austin
 * @Date: 2020-01-09 18:18:25
 * @LastEditors  : Austin
 * @LastEditTime : 2020-07-02 18:00:44
 */

namespace Ifantace\Common\Http\Controllers;

use App\Http\Controllers\Controller;
use Ifantace\Common\CommonTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CommonController extends Controller
{
    use CommonTraits;

    /**
     * input
     *
     * @var Request
     */
    private $input;

    public function __construct(Request $input)
    {
        $this->input = $input;
    }

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


    /**
     * download file
     *
     * @param string $type download type
     * @return mixed
     */
    public function download(string $type)
    {
        switch ($type) {
            case "log":
                if (!$this->input->has("path") || !$this->input->has("token")) {
                    return "error";
                }
                return $this->downloadLog($this->input->get("path"), $this->input->get("token"));
                break;
            default:
                break;
        }
    }
}
