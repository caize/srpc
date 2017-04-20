<?php

namespace Syar\Encoder;

/**
 * Class EncoderJson
 * @package syar\base
 */
class Json implements Iface
{
    function encode($message)
    {
        return json_encode($message);
    }

    function decode($message)
    {
        return json_decode($message, true);
    }
}