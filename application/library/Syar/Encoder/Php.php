<?php
namespace Syar\Encoder;

/**
 * Class EncoderPHP
 */
class Php implements Iface
{
    function encode($message)
    {
        return serialize($message);
    }

    function decode($message)
    {
        return unserialize($message);
    }
}
