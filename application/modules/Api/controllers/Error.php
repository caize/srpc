<?php
/**
 * @name ErrorController
 * @author l.gang06@yahoo.com
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
use \Yaf\Controller_Abstract;
use \Yaf\Registry;
use \Api\Log;

class ErrorController extends Controller_Abstract
{

    public function errorAction($e = null)
    {
        var_dump($e);
        $ext = $this->_request->getParam('exception');
        $arr = array(
            'errorcode' => -1,
            'errormsg' => $ext->getMessage()
        );
        $log = Registry::get('log');
        $log->setDisplay(false)->put($ext->getMessage(), $log::ERROR);
        echo json_encode($arr);
    }
}
