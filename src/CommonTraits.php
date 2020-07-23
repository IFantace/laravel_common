<?php

namespace Ifantace\Common;

use Illuminate\Http\Request;
use Auth;
use Log;

trait CommonTraits
{
    public function downloadLog(Request $input)
    {
        try {
            if ($input->has("path") && $input->has("token")) {
                if (strcmp(config("common." . config("app.env") . ".token_log"), $input->get("token")) == 0) {
                    $headers = array(
                        'Content-Type: application/txt',
                    );
                    return response()->download(storage_path('logs') . "/" . $input->get("path"), null, $headers);
                }
            }
        } catch (\Exception $error) {
        }
        return "error";
    }
    public function loadConfigJson($file_name)
    {
        try {
            if (!is_string($file_name)) {
                $file_name = $file_name->get("file_name");
            }
            return json_decode(file_get_contents(config_path('JSON/' . $file_name . '.json')), true);
        } catch (\Exception $error) {
            return [];
        } finally {
        }
    }
    public function checkPostParameter(Request $input, $column_array)
    {
        foreach ($column_array as $each_column) {
            if (!$input->has($each_column)) {
                return false;
            }
        }
        return true;
    }
    public function generate_random_key($length)
    {
        $randoma = "";
        $random = $length;
        for ($j = 0; $j < $random; $j++) {
            switch (mt_rand(0, 1)) {
                case 0:
                    $in = chr(mt_rand(65, 90));
                    break;
                case 1:
                    $in = mt_rand(0, 9);
                    break;
            }
            $randoma = $randoma . $in;
        }
        return $randoma;
    }
    public function generateRandomKey($length)
    {
        $randoma = "";
        $random = $length;
        for ($j = 0; $j < $random; $j++) {
            switch (mt_rand(0, 1)) {
                case 0:
                    $in = chr(mt_rand(65, 90));
                    break;
                case 1:
                    $in = mt_rand(0, 9);
                    break;
            }
            $randoma = $randoma . $in;
        }
        return $randoma;
    }
    public function sendCurlPostJSON($url, $data, $header = [])
    {
        $event_uuid = $this->gen_uuid();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data)
            ? json_encode(
                $data,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ) : $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($header, array('Content-Type: application/json')));
        $monolog = Log::getMonolog();
        $monolog->popHandler();
        Log::useDailyFiles(storage_path() . "/logs/curl.log");
        Log::info("SEND: " . json_encode(
            array("url" => $url, "body" => $data, "header" => array_merge($header, array('Content-Type: application/json')), "event_uuid" => $event_uuid),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ));
        $output = curl_exec($ch);
        $status_code = curl_errno($ch); //get status code
        Log::info("RESPONSE: " . json_encode(
            array("status_code" => $status_code, "response_body" => $status_code == 0 ? $output : null, "event_uuid" => $event_uuid),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ));
        Log::getMonolog()->popHandler();
        Log::useDailyFiles(storage_path() . "/logs/laravel.log");
        if ($status_code == 0) {
            curl_close($ch);
            try {
                $try_decode_data = json_decode($output, true);
                if ($try_decode_data != null) {
                    return $try_decode_data;
                }
            } catch (\Exception $error) {
            }
            return $output;
        } else {
            $error = curl_error($ch);
            curl_close($ch);
            return $error;
        }
    }
    public function gen_uuid()
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
    public function generate_responseArray($status, $message, $UI_message, $data = null)
    {
        $responseArray = array();
        $responseArray["status"] = $status;
        $responseArray["message"] = $message;
        $responseArray["ui_message"] = $UI_message;
        $responseArray["uuid"] = $this->gen_uuid();
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $responseArray[$key] = $value;
            }
        }
        ksort($responseArray);
        return $responseArray;
    }
    public function generateResponseArray($status, $message, $UI_message, $data = null)
    {
        $responseArray = array();
        $responseArray["status"] = $status;
        $responseArray["message"] = $message;
        $responseArray["ui_message"] = $UI_message;
        $responseArray["uuid"] = $this->gen_uuid();
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $responseArray[$key] = $value;
            }
        }
        ksort($responseArray);
        return $responseArray;
    }
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
    public function returnResult($line, $API_name, $method, $input, $return_data, $file_name, $error = null)
    {
        try {
            if (is_array($input)) {
                $parameter = $input;
            } else {
                $parameter = $input->all();
            }
            $user = $this->getCurrentUser();
        } catch (Exception $error) {
        }
        $str_input = "REQUEST: " . json_encode(
            array("method" => $method,  "File" => $file_name, "Page" => $API_name, "Parameter" => $parameter, "user" => $user),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $str_return = "RETURN: " . json_encode(
            array("File" => $file_name, "Page" => $API_name, "Result" => $return_data, "Line" => $line, "user" => $user),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ) . "\r\n";
        $monolog = Log::getMonolog();
        $monolog->popHandler();
        Log::useDailyFiles(storage_path() . "/logs/laravel.log");
        Log::info($str_input);
        Log::info($str_return);
        if ($error != null) {
            $str_error = "EXCEPTION: " . json_encode(
                array("File" => $file_name, "Page" => $API_name, 'line' => $error->getLine(), "Exception" => $error->getMessage(), "user" => $user),
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ) . "\r\n";
            Log::error($str_error);
        }
        return $return_data;
    }
}
