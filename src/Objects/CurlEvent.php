<?php

/*
 * @Author       : Austin
 * @Date         : 2020-03-25 17:09:18
 * @LastEditors  : Austin
 * @LastEditTime : 2020-06-04 17:30:53
 * @Description  : {{Description this}}
 */

namespace Ifantace\Common\Objects;

use Ifantace\Common\CommonTraits;
use Illuminate\Support\Facades\Log;

class CurlEvent
{
    use CommonTraits;

    /**
     * the uuid of this event
     *
     * @var string
     */
    private $event_uuid;

    public function __construct(string $event_uuid)
    {
        $this->event_uuid = $event_uuid;
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
            CURLOPT_CONNECTTIMEOUT => 0,
            CURLOPT_TIMEOUT => 15
        ]
    ) {
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
                $this->event_uuid
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
                $this->event_uuid
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
                    $this->event_uuid
                )
            );
            curl_close($ch);
            return $error;
        }
    }
}
