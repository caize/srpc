<?php
/**
 * Created by  System auto tools..
 * User: System;l.gang06@yahoo.com
 * Date: {DATE}
 * Time: {TIME}
 */
use \Api\Controller\Rpc;

class {CONTROLLER}Controller extends Rpc
{
    public function {ACTION}Action()
    {
        $this->_rpcService->start(new \Externalapi\Rpcapi\{MODEL_NAME}Model());
        return false;
    }
}