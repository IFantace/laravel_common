<?php

namespace Ifantace\Common;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Ifantace\Common\Objects\ResponseException;
use Throwable;

trait CommonTraits
{
    protected $event_uuid;

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
     * @return array|mixed
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
     * @return string|false
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
     * Use JSON_UNESCAPED_SLASHES and JSON_UNESCAPED_UNICODE to json_encode array.
     *
     * @param array $array Array which needs to .
     * @return string|false
     */
    public function jsonEncodeUnescaped(array $array)
    {
        return json_encode($array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function createLogString(string $event, array $data)
    {
        if ($this->event_uuid === null) {
            $this->event_uuid = $this->genUuid();
        }
        return $this->jsonEncodeUnescaped([
            "EVENT-NAME" => $event,
            "EVENT-CONTENT" => $data,
            "EVENT-UUID" => $this->event_uuid
        ]);
    }

    /**
     * Send request which type is post.
     *
     * @param string $url Url.
     * @param array $data Post data.
     * @param array $header Headers.
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
        ]
    ) {
        if ($this->event_uuid === null) {
            $this->event_uuid = $this->genUuid();
        }
        $request_id = $this->generateRandomKey(8);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->jsonEncodeUnescaped($data));
        foreach ($options as $key => $value) {
            curl_setopt($ch, $key, $value);
        }
        Log::info(
            $this->createLogString(
                "Curl-Send",
                [
                    "Url" => $url,
                    "Header" => $header,
                    "Body" => $this->jsonEncodeUnescaped($data),
                    "Option" => $options,
                    "RequestId" => $request_id
                ],
            )
        );
        $output = curl_exec($ch);
        $status_code = curl_errno($ch);
        Log::info(
            $this->createLogString(
                "Curl-Receive",
                [
                    "StatusCode" => $status_code,
                    "ResponseBody" => $status_code == 0 ? $output : null,
                    "RequestId" => $request_id
                ],
            )
        );
        if ($status_code == 0) {
            curl_close($ch);
            return $output;
        } else {
            $error = curl_error($ch);
            Log::warning(
                $this->createLogString(
                    "Curl-Error",
                    [
                        "ErrorMessage" => $error,
                        "RequestId" => $request_id
                    ],
                )
            );
            curl_close($ch);
            return $error;
        }
    }

    /**
     * Get random uuid.
     *
     * @return string
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
            // 16 bits for  "time_hi_and_version",
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
     * Valid date.
     *
     * @param string $date Date with time.
     * @param string $format Format.
     * @return boolean
     */
    public function validateDate(string $date, string $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    /**
     * Get the uuid of current user.
     *
     * @return string|null
     */
    public function getCurrentUserUuid()
    {
        $user = Auth::user();
        if ($user !== null) {
            return $user['uuid'];
        } else {
            return null;
        }
    }

    /**
     * generate response array
     *
     * @param integer $status Status_code: > 0 = success, < 0 = failed.
     * @param string $message Message for developer.
     * @param string $ui_message Message for user.
     * @param array $data  Apply data.
     * @return array
     */
    public function generateResponseArray(
        int $status,
        string $message,
        string $ui_message,
        array $data = []
    ) {
        $back_trace = debug_backtrace();
        $caller = array_shift($back_trace);
        $caller_source = array_shift($back_trace);
        $responseArray = [
            "status" => $status,
            "message" => $message,
            "ui_message" => $ui_message,
            "uuid" => $this->event_uuid,
            "file" => [
                "class" => isset($caller["class"]) ? $caller["class"] : null,
                "function" => isset($caller_source["function"]) ? $caller_source["function"] : null,
                "line" => isset($caller["line"]) ? $caller["line"] : null
            ]
        ];
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $responseArray[$key] = $value;
            }
        }
        ksort($responseArray);
        return $responseArray;
    }

    /**
     * record this time response.
     *
     * @param array|Request $input_data request data or array
     * @param array $return_data response array
     * @param Throwable $error response array
     * @return void
     */
    /** */
    public function recordResponse($input_data, $return_data, Throwable $error = null)
    {
        if (is_array($return_data)) {
            $line = isset($return_data["file"]["line"]) ? $return_data["file"]["line"] : null;
            $class = isset($return_data["file"]["class"]) ? $return_data["file"]["class"] : null;
            $function = isset($return_data["file"]["function"]) ? $return_data["file"]["function"] : null;
            unset($return_data["file"]);
        } else {
            $class = null;
            $function = null;
            $line = null;
        }
        $parameter = is_array($input_data) ? $input_data : $input_data->all();
        if (isset($parameter["event_uuid"])) {
            $this->event_uuid = $parameter["event_uuid"];
            unset($parameter["event_uuid"]);
        }
        $data_array =
            [
                "File" => $class,
                "Function" => $function,
                // "Receive" => $parameter,
                "Response" => $return_data,
                "Line" => $line,
                "User" => $this->getCurrentUserUuid(),
            ];
        if ($error !== null) {
            $data_array["Exception"] =  $error->getMessage() . " at line: " . $error->getLine();
        }
        Log::info(
            $this->createLogString(
                "Request-Response",
                $data_array,
            )
        );
    }

    /**
     * 用於中斷程式
     *
     * @param [type] $response_array
     * @return void
     */
    public function throwResponseException($response_array)
    {
        $this_exception = new ResponseException($response_array["message"]);
        $this_exception->setResponse($response_array);
        throw $this_exception;
    }
}
