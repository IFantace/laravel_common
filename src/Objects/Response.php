<?php

/*
 * @Author       : Austin
 * @Date         : 2020-03-25 17:09:18
 * @LastEditors  : Austin
 * @LastEditTime : 2020-03-31 17:13:59
 * @Description  : {{Description this}}
 */

namespace Ifantace\Common\Objects;

use Ifantace\Common\CommonTraits;
use Throwable;
use Illuminate\Support\Facades\Log;

class Response
{
    use CommonTraits;

    /**
     * status
     *
     * @var int
     */
    private $status;

    /**
     * message
     *
     * @var string
     */
    private $message;

    /**
     * ui_message
     *
     * @var string
     */
    private $ui_message;

    /**
     * class
     *
     * @var string
     */
    private $class;

    /**
     * function
     *
     * @var string
     */
    private $function;

    /**
     * line
     *
     * @var int
     */
    private $line;

    /**
     * message
     *
     * @var string
     */
    private $event_uuid;

    /**
     * data
     *
     * @var array
     */
    private $data;

    /**
     * exception
     *
     * @var Throwable
     */
    private $error;

    /**
     * 建立並初始化event_uuid
     *
     * @param string $event_uuid
     */
    public function __construct(string $event_uuid)
    {
        $this->event_uuid = $event_uuid;
    }

    /**
     * 設定回應的status
     *
     * @param integer $status 目前設計，> 0: success, -1: 參數錯誤 -2:驗證錯誤 -3:執行錯誤 -4:非預期的錯誤
     * @return Response
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * 設定回應的message
     *
     * @param string $message RD看的message
     * @return Response
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
        return $this;
    }
    /**
     * 設定回應的ui_message
     *
     * @param string $ui_message 使用者看的ui_message
     * @return Response
     */
    public function setUIMessage(string $ui_message)
    {
        $this->ui_message = $ui_message;
        return $this;
    }

    /**
     * 批次設定必要值
     *
     * @param integer $status
     * @param string $message
     * @param string $ui_message
     * @return Response
     */
    public function setCommon(int $status, string $message, string $ui_message)
    {
        $this->setStatus($status);
        $this->setMessage($message);
        $this->setUIMessage($ui_message);
        return $this;
    }

    /**
     * 設定response夾帶的data
     *
     * @param array $data key=>value形式
     * @return Response
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 設定此回應的系統資料
     *
     * @return Response
     */
    public function setFile()
    {
        $back_trace = debug_backtrace();
        $caller = array_shift($back_trace);
        $caller_source = array_shift($back_trace);
        $this->setClass(isset($caller["class"]) ? $caller["class"] : null);
        $this->setLine(isset($caller["line"]) ? $caller["line"] : null);
        $this->setFunction(isset($caller_source["function"]) ? $caller_source["function"] : null);
        return $this;
    }

    /**
     * 設定回應的class
     *
     * @param string $class 回應的class
     * @return Response
     */
    public function setClass(string $class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * 設定此回應所在的function
     *
     * @param string $function function名稱
     * @return Response
     */
    public function setFunction(string $function)
    {
        $this->function = $function;
        return $this;
    }

    /**
     * 設定此回應所在的行數
     *
     * @param integer $line 行數
     * @return Response
     */
    public function setLine(int $line)
    {
        $this->line = $line;
        return $this;
    }

    /**
     * 發生意外狀況時，所夾帶的Exception檔案
     *
     * @param Throwable $error
     * @return Response
     */
    public function setError(Throwable $error)
    {
        $this->error = $error;
        return $this;
    }

    /**
     * 產生response array
     *
     * @return array
     */
    private function createResponseArray(): array
    {
        $response_array = [
            "status" => $this->status,
            "message" => $this->message,
            "ui_message" => $this->ui_message,
            "uuid" => $this->event_uuid,
            "file" => [
                "class" => $this->class,
                "function" => $this->function,
                "line" => $this->line
            ]
        ];
        if (is_array($this->data)) {
            foreach ($this->data as $key => $value) {
                $responseArray[$key] = $value;
            }
        }
        if (isset($this->error)) {
            $response_array["error"] = $this->error->getMessage();
        }
        ksort($response_array);
        return $response_array;
    }

    /**
     * get response array as return
     *
     * @param boolean $need_record record response at the same time
     * @return void
     */
    public function getResponseArray(bool $need_record = false)
    {
        $response_array = $this->createResponseArray();
        if ($need_record) {
            $this->recordResponse($response_array);
        }
        unset($response_array["file"]);
        return $response_array;
    }

    /**
     * record response array
     *
     * @param array $response_array
     * @return void
     */
    private function recordResponse(array $response_array)
    {
        Log::info(
            $this->createLogString(
                "Request-Response",
                $response_array,
                $this->event_uuid,
            )
        );
    }

    /**
     * 丟出一個exception，用於中斷程式
     *
     * @param boolean $need_record record response at the same time
     * @return void
     */
    public function throwResponseException($need_record = false)
    {
        $response_array = $this->getResponseArray($need_record);
        $this_exception = new ResponseException(isset($response_array["message"]) ? $response_array["message"] : "");
        $this_exception->setResponse($response_array);
        throw $this_exception;
    }
}
