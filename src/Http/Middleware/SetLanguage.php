<?php
/*
 * @Author: Austin
 * @Date: 2020-01-09 18:18:25
 * @LastEditors: Austin
 * @LastEditTime: 2020-01-13 18:03:51
 */

namespace Ifantace\Common\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->has("language")) {
            switch ($request->get("language")) {
                case "en":
                case "english":
                    App::setlocale("en");
                    break;

                case "chinese":
                case "zh":
                case "zh_tw":
                default:
                    App::setlocale("zh_TW");
                    break;
            }
        }
        return $next($request);
    }
}
