<?php

namespace App\Http\Middleware;

use Closure;

use Log;
use Auth;

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
        $route = $request->route();
        $route_action = $request->route()->getAction();
        $controller_and_function_path = $route_action['controller'];
        $controller_and_function_path_array = explode("\\", $controller_and_function_path);
        $controller_and_function_name = $controller_and_function_path_array[count($controller_and_function_path_array) - 1];

        $user = 'unknown';
        try {
            $user_data = Auth::user();
            if ($user_data != null) {
                $user = $user_data['uuid'];
            }
            // $str_input = $request->ip() . ', ' . $request->method() . ", " . $request->path() . ", " . $user . ": " . json_encode(array("parameters" => $request->all(), "Page" => $controller_and_function_name)) . "\r\n";

            $log_data = array("ip" => $request->ip(), "method" => $request->method(), "user_uuid" => $user, "parameters" => $request->all, "page" => $controller_and_function_name);
            Log::useDailyFiles(storage_path() . "/logs/Request/request.log");
            Log::info(json_encode($log_data));
        } catch (\Exception $error) { }
        return $next($request);
    }
}
