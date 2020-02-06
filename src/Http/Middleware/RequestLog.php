<?php

/*
 * @Author: Austin
 * @Date: 2019-08-01 17:26:23
 * @LastEditors  : Austin
 * @LastEditTime : 2020-02-06 16:16:30
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
        $event_uuid = $this->genUuid();
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
                $event_uuid
            )
        );
        $request->request->add(["event_uuid" => $event_uuid]);
        return $next($request);
    }
}
