<?php

namespace Ifantace\Common;

use Illuminate\Http\Request;
use Auth;
use Log;
// use Symfony\Component\Debug\Exception\FatalThrowableError;

trait CommonTraits
{
    public function loadConfigJson($file_name)
    {
        try {
            if (!is_string($file_name)) {
                $file_name = $file_name->get("file_name");
            }
            return json_decode(file_get_contents(config_path('JSON/' . $file_name . '.json')), true);
        } catch (\Exception $error) {
            return [];
        } finally { }
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
    public function sendCurlPostJSON($url, $data, $header = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($header, array('Content-Type: application/json')));
        $output = curl_exec($ch);
        $status_code = curl_errno($ch); //get status code
        if ($status_code == 0) {
            curl_close($ch);
            try {
                $try_decode_data = json_decode($output, true);
                return $try_decode_data;
            } catch (\Exception $error) { }
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
    public function returnResult($line, $API_name, $method, $input, $return_data, $file_name, $error = null)
    {
        try {
            if (is_array($input)) {
                $parameter = $input;
            } else {
                $parameter = $input->all();
            }
            $user = $this->getCurrentUser();
        } catch (Exception $error) { }
        $str_input = "REQUEST: " . json_encode(array("method" => $method,  "File" => $file_name, "Page" => $API_name, "Parameter" => $parameter, "user" => $user));
        $str_return = "RETURN: " . json_encode(array("File" => $file_name, "Page" => $API_name, "Result" => $return_data, "Line" => $line, "user" => $user)) . "\r\n";
        Log::useDailyFiles(storage_path() . "/logs/" . $file_name . "/" . $file_name . ".log");
        Log::info($str_input);
        Log::info($str_return);
        if ($error != null) {
            $str_error = "EXCEPTION: " . json_encode(array("File" => $file_name, "Page" => $API_name, 'line' => $error->getLine(), "Exception" => $error->getMessage(), "user" => $user)) . "\r\n";
            Log::error($str_error);
        }
        return $return_data;
    }
}
