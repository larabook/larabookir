<?php

namespace App\Larabookir;

use App\Larabookir\Helpers\Array2xml;
use Illuminate\Support\Str;

class ResponseUtil
{
    /**
     * @return bool
     */
    static function wantsJson()
    {
        if (request()->route()->hasParameter('accept'))
            $acceptable = substr(request()->route()->parameter('accept'), 1);
        else
            $acceptable = request()->getAcceptableContentTypes();
        return isset($acceptable[0]) && Str::contains($acceptable[0], ['/json', '+json']);
    }

    /**
     * @return bool
     */
    static function wantsXML()
    {
        if (request()->route() && request()->route()->hasParameter('accept'))
            $acceptable = substr(request()->route()->parameter('accept'), 1);
        else
            $acceptable = request()->getAcceptableContentTypes();
        return isset($acceptable[0]) && Str::contains($acceptable[0], ['/xml']);
    }

    /**
     * @param $data
     * @param int $error
     * @param array $messages
     * @param array $headers
     * @param int $status
     *
     * @return \Illuminate\Http\Response
     */
    static function makeResponse($data, $error = 0, $messages = [], $headers = [], $status = 200)
    {
        $data = array_merge([
            'error'    => (int) $error,
            'messages' => $messages,
        ], $data);

        if (static::wantsXML()) {

            response()->macro('xml', function (array $vars, $status = 200, array $header = []) {
                try {
                    $xml = new Array2xml('result', 'item');
                    $xml->createNode($vars);
                    if (empty($header)) {
                        $header['Content-Type'] = 'application/xml';
                    }
                    return response()->make($xml, $status, $header);
                } catch (\Exception $e) {
                    die($e->getMessage());
                }
            });

            $headers = array_merge(['Content-type' => 'application/xml; charset=utf-8'], $headers);
            return response()->xml((array)$data, $status, $headers);
        }

        $headers = array_merge(['Content-type' => 'application/json; charset=utf-8'], $headers);
        return response()->json($data, $status, ['Content-type' => 'application/json; charset=utf-8'] + $headers, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param $data
     * @param array $messages
     * @param array $headers
     *
     * @return \Illuminate\Http\Response
     */
    static function response($data, $messages = [], $headers = [])
    {
        return static::makeResponse($data, 0, $messages, $headers);
    }

    /**
     * @param $messages
     * @param array $data
     * @param array $headers
     * @param int $status
     *
     * @param bool $long_message_display
     *
     * @return \Illuminate\Http\Response
     */
    static function error($messages, $data = [], $headers = [], $status = 500, $long_message_display = false)
    {
        $data = array_merge($data, [
            'long_message_display' => $long_message_display ? 1 : 0,
        ]);
        return static::makeResponse($data, 1, $messages, $headers, $status);
    }
}