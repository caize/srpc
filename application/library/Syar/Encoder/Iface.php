<?php

namespace Syar\Encoder;
interface Iface
{
    function encode($message);

    function decode($message);
}
