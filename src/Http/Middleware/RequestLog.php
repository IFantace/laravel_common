<?php

/*
 * @Author: Austin
 * @Date: 2019-08-01 17:26:23
 * @LastEditors  : Austin
 * @LastEditTime : 2020-03-27 16:17:55
 */

namespace Ifantace\Common\Http\Middleware;

use Closure;
use Ifantace\Common\CommonTraits;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RequestLog
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
        $route = $request->route();
        $this->event_uuid = $this->genUuid();
        Log::info(
            $this->createLogString(
                "Request-Receive",
                [
                    "Ip" => $request->ip(),
                    "Method" => $request->method(),
                    "Url" => $route->uri,
                    "User" => $this->getCurrentUserUuid(),
                    "Parameters" => $request->all()
                ],
                $this->event_uuid
            )
        );
        $request->request->add(["event_uuid" => $this->event_uuid]);
        return $next($request);
    }
}
