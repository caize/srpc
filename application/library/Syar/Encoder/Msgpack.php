<?php

namespace Syar\Encoder;

/**
 * Class EncoderMsgpack
 */
class Msgpack implements Iface
{
    function encode($message)
    {
        return msgpack_pack($message);
    }

    function decode($message)
    {
        return msgpack_unpack($message);
    }
}