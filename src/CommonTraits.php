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
    /**
     * Download log file in storage.
     *
     * @param string $path String of path.
     * @param string $token String of token.
     * @return string|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadLog($path, $token)
    {
        if (strcmp(config("ifantace.common." . config("app.env") . ".token_log"), $token) == 0) {
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

    public function createLogString(string $event, array $data, string $event_uuid)
    {
        return $this->jsonEncodeUnescaped([
            "EVENT-NAME" => $event,
            "EVENT-CONTENT" => $data,
            "EVENT-UUID" => $event_uuid
        ]) . PHP_EOL;
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
}
