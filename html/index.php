<?php
require_once 'index_header.php';
$application = new Yaf\Application(APPLICATION_PATH_CONFIG . "/application.ini", APPLICATION_ENV);
$application->getDispatcher()->catchException(false);
try {
    $application->bootstrap()->run();
} catch ( \Exception $e ) {
    \Yaf\Registry::get('log')->put(
        ' url ' . $application->getDispatcher()->getRequest()->getRequestUri()
            . ' file:' . $e->getFile() . ' line:' . $e->getLine()
        . ' ' . $e->getMessage() . ' trace:' . $e->getTraceAsString(),
        \Api\Log::ERROR
    );
    $error = array(
        'errorcode' => $e->getCode(),
        'errormsg' => $e->getMessage()
    );
    echo json_encode($error);
}