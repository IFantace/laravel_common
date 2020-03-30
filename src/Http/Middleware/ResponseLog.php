<?php

/*
 * @Author: Austin
 * @Date: 2019-08-01 17:26:23
 * @LastEditors  : Austin
 * @LastEditTime : 2020-03-30 17:06:59
 */

namespace Ifantace\Common\Http\Middleware;

use Closure;
use Ifantace\Common\CommonTraits;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Throwable;

class ResponseLog
{
    use CommonTraits;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
    public function terminate($request, $response)
    {
        try {
            $response_array = json_decode($response->getContent(), true);
            $this->recordResponse($request, $response_array !== null ? $response_array : $response->getContent());
        } catch (Throwable $th) {
        }
    }
}
