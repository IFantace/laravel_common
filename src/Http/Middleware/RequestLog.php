<?php

/*
 * @Author: Austin
 * @Date: 2019-08-01 17:26:23
 * @LastEditors  : Austin
 * @LastEditTime : 2020-10-23 16:31:09
 */

namespace Ifantace\Common\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RequestLog
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
        $user = 'unknown';
        $route = $request->route();
        Log::getMonolog()->popHandler();
        try {
            $user_data = Auth::user();
            if ($user_data != null) {
                $user = $user_data['uuid'];
            }
            $log_data = array(
                "ip" => $request->ip(),
                "method" => $request->method(),
                "url" => $request->path(),
                "user_uuid" => $user,
                "parameters" => $request->all()
            );
            Log::useDailyFiles(storage_path() . "/logs/Request/request.log");
            Log::info(json_encode($log_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } catch (\Exception $error) {
            Log::useDailyFiles(storage_path() . "/logs/Request/request_error.log");
            Log::error($error->getMessage());
        }
        Log::getMonolog()->popHandler();
        Log::useDailyFiles(storage_path() . "/logs/laravel.log");
        return $next($request);
    }
}
