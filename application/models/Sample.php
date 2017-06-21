<?php

/**
 * @name SampleModel
 * @desc sample数据获取类, 可以访问数据库，文件，其它系统等
 * @author root
 */
class SampleModel
{
    public function __construct()
    {
    }

    public function selectSample()
    {
        $test = 1;
        $test2 = 1;
        $test++;
        return 'Hello World!';
        $this->insertSample(1);
    }

    public function insertSample($arrInfo)
    {
        return true;
    }
}
