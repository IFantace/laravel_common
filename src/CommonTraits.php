<?php

namespace Ifantace\Common;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

trait CommonTraits
{
    /**
     * Download log file in storage.
     *
     * @param string $path String of path.
     * @param string $token String of token.
     * @return string|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadLog($path, $token)
    {
        if (strcmp(config("common." . config("app.env") . ".token_log"), $token) == 0) {
            $headers = array(
                'Content-Type: application/txt',
            );
            return response()->download(storage_path('logs') . "/" . $path, null, $headers);
        }
        return "file not found";
    }

    /**
     * Load json file in config/JSON folder.
     *
     * @param Request|string $file_name String of file name or a request with file_name column.
     * @return array|boolean
     */
    public function loadConfigJson($file_name)
    {
        if (is_string($file_name)) {
            $file_name = $file_name;
        } else {
            $file_name = $file_name->get("file_name");
        }
        return json_decode(File::get(config_path('JSON/' . $file_name . '.json')), true);
    }

    /**
     * Check request parameter.
     *
     * @param Request $input Request.
     * @param array $column_array Array of required column_name.
     * @return boolean
     */
    public function checkParameter(Request $input, array $column_array)
    {
        foreach ($column_array as $each_column) {
            if (!$input->has($each_column)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Generate random string with number or character.
     *
     * @param integer $length Length of string.
     * @param integer $mode 0 ~ 7, binary 0 bit: with number, 1 bit: with upper case, 2 bit: with lower case.
     * @return string|boolean
     */
    public function generateRandomKey(int $length, int $mode = 7)
    {
        if ($mode === 0 || $mode > 7 || $length === 0) {
            return false;
        }
        $random_string = "";
        $threshold_of_number = ($mode & 1) ? 10 : 0;
        $threshold_of_uppercase = (($mode & 2) ? 26 : 0) + $threshold_of_number;
        $threshold_of_lowercase = (($mode & 4) ? 26 : 0) + $threshold_of_uppercase;
        for ($j = 0; $j < $length; $j++) {
            $random_number = mt_rand(0, $threshold_of_lowercase - 1);
            if ($random_number < $threshold_of_number) {
                $in = chr(48 + $random_number);
            } elseif ($random_number < $threshold_of_uppercase) {
                $in = chr(65 + $random_number - $threshold_of_number);
            } elseif ($random_number < $threshold_of_lowercase) {
                $in = chr(97 + $random_number - $threshold_of_uppercase);
            }
            $random_string = $random_string . $in;
        }
        return $random_string;
    }

    /**
     * Send request which type is post.
     *
     * @param string $url Url.
     * @param array $data Post data.
     * @param array $header Headers.
     * @param string $event_uuid This request event uuid
     * @return array|string
     */
    public function sendCurlPostJSON(
        string $url,
        array $data,
        array $header = [
            'Content-Type: application/json'
        ],
        array $options = [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_CONNECTTIMEOUT => 0,
            CURLOPT_TIMEOUT => 15
        ],
        string $event_uuid = null
    ) {
        if ($event_uuid === null) {
            $event_uuid = $this->gen_uuid();
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        foreach ($options as $key => $value) {
            curl_setopt($ch, $key, $value);
        }
        Log::info(json_encode(
            [
                "EVENT" => "Send request",
                "url" => $url,
                "body" => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                "header" => $header,
                "event_uuid" => $event_uuid
            ],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ));
        $output = curl_exec($ch);
        $status_code = curl_errno($ch);
        Log::info(json_encode(
            [
                "EVENT" => "Receive response",
                "status_code" => $status_code,
                "response_body" => $status_code == 0 ? $output : null,
                "event_uuid" => $event_uuid
            ],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ));
        if ($status_code == 0) {
            curl_close($ch);
            return $output;
        } else {
            $error = curl_error($ch);
            Log::info(json_encode(
                [
                    "EVENT" => "Error connection",
                    "error_message" => $error,
                    "event_uuid" => $event_uuid
                ],
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ));
            curl_close($ch);
            return $error;
        }
    }

    /**
     * Get random uuid.
     *
     * @return void
     */
    public function genUuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for  "time_lo w"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            // 16 bits for  "time_mi d"
            mt_rand(0, 0xffff),
            // 16 bits for  "time_hi_and_versio n",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for  "clk_seq_hi_re s",
            // 8 bits for  "clk_seq_lo w",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for  "nod e"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * generate response array
     *
     * @param integer $status Status_code: > 0 = success, < 0 = failed.
     * @param string $message Message for developer.
     * @param string $ui_message Message for user.
     * @param array $data  Apply data.
     * @return void
     */
    public function generateResponseArray(int $status, string $message, string $ui_message, array $data = null)
    {
        $responseArray = array();
        $responseArray["status"] = $status;
        $responseArray["message"] = $message;
        $responseArray["ui_message"] = $ui_message;
        $responseArray["uuid"] = $this->gen_uuid();
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $responseArray[$key] = $value;
            }
        }
        ksort($responseArray);
        return $responseArray;
    }

    /**
     * todo
     * Undocumented function
     *
     * @param [type] $date
     * @param string $format
     * @return void
     */
    public function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
    public function getCurrentUser()
    {
        $user = Auth::user();
        if ($user != null) {
            return $user;
        } else {
            return false;
        }
    }
    public function getCurrentUserUuid()
    {
        $user = Auth::user();
        if ($user != null) {
            return $user['uuid'];
        } else {
            return false;
        }
    }
    public function returnResult($method, $input, $return_data, $error = null)
    {
        $back_trace = debug_backtrace();
        $caller = array_shift($back_trace);
        $caller_source = array_shift($back_trace);

        $parameter = is_array($input) ? $input : $input->all();
        $user = $this->getCurrentUser();
        $line = isset($caller["line"]) ? $caller["line"] : null;
        $class = isset($caller["class"]) ? $caller["class"] : null;
        $function_name = isset($caller_source["function"]) ? $caller_source["function"] : null;
        $str_input = "REQUEST: " . json_encode(array(
            "Method" => $method,
            "File" => $class,
            "Page" => $function_name,
            "Parameter" => $parameter,
            "User" => $user ? $user->uuid : null
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $str_return = "RETURN: " . json_encode(array(
            "File" => $class,
            "Page" => $function_name,
            "Result" => $return_data,
            "Line" => $line,
            "User" => $user ? $user->uuid : null
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        Log::getMonolog()->popHandler();
        Log::useDailyFiles(storage_path("logs/laravel.log"));
        Log::info($str_input);
        if ($error != null) {
            $str_error = "EXCEPTION: " . json_encode(array(
                "File" => $class,
                "Page" => $function_name,
                'line' => $error->getLine(),
                "Exception" => $error->getMessage(),
                "user" => $user
            ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            Log::error($str_error);
        }
        Log::info($str_return);
        return $return_data;
    }
}
