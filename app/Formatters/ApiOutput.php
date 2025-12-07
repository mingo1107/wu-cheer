<?php

namespace App\Formatters;

use Illuminate\Http\Request;

class ApiOutput
{
    /**
     * @var bool
     */
    protected $status;
    /**
     * @var integer
     */
    protected $code = 200;
    /**
     * @var string
     */
    protected $message;
    protected $request;

    public function __construct()
    {
        $this->request = app(Request::class);

        $this->message = '執行成功';
    }

    /**
     * @param string $message
     * @param array $data
     * @param integer $code
     * @return array
     */
    public function successFormat($data = null, string $message = '操作成功', int $code = 200): array
    {
        $this->status = true;

        if ($message !== '') {
            $this->code = $code;
            $this->message = $message;
        }

        return [
            'status' => true,
            'message' => $message,
            'code' => $code,
            'data' => $data
        ];
    }

    /**
     * @param string $message
     * @param integer $code
     * @param array $data
     * @return array
     */
    public function failFormat($message = '', $data = [], $code = null)
    {
        $this->status = false;

        if ($message !== '') {
            $this->message = $message;
        }
        if ($code) {
            $this->code = $code;
        }

        return $this->formatting(['errors' => $data]);
    }

    /**
     * @param string $message
     * @param integer $code
     * @param array $data
     * @return array
     */
    public function exceptionFormat($message = '', $code = 500, $e = null)
    {
        if(!empty($e)){
            $error = [
                'file' =>  $e->getFile(),
                'code' =>  $e->getCode(),
                'line' => $e->getLine(),
                'error_message' => $e->getMessage(),
            ];
        }
        return [
            'status' =>  false,
            'message' => $message,
            'error' => $error ?? ''
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function formatting($data = [])
    {
        $default = $this->getDefaultFormat($this->request);

        return array_merge($default, $data);
    }

    /**
     * Get base format, url, params, method, status code and message.
     *
     * @param Request $request
     * @return array
     */
    public function getDefaultFormat(Request $request)
    {
        return [
            //'url' =>$request->fullUrl(),
            //'method' => $request->getMethod(),
            'status' => $this->status,
            'message' => $this->message,
            'code' => $this->code,
        ];
    }
}
