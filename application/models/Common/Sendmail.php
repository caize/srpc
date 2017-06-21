<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/5/9
 * Time: 14:36
 */
namespace Common;
use Hexin\MailProxy;
class SendmailModel
{
    protected $_stmp = null;
    protected $_content = null;
    public function __construct()
    {
        $this->_stmp = new MailProxy();
    }

    public function setSubject($subject)
    {
        $this->_stmp->setSubject($subject);
        return $this;
    }

    public function setBodyHtml($html)
    {
        $this->_content = $html;
        $this->_stmp->setBodyHtml((string) $html);
        return $this;
    }

    public function setBodyText($text)
    {
        $this->_content = $text;
        $this->_stmp->setBodyText((string) $text);
        return $this;
    }

    public function setFrom($from)
    {
        $this->_stmp->setFrom($from);
        return $this;
    }

    public function setProjectName($projectName)
    {
        $this->_stmp->setProjectName($projectName);
        return $this;
    }

    public function post($debug = 1)
    {
        if (APPLICATION_ENV == 'production' || !$debug) {
            $this->_stmp->post();
        } else {
            echo $this->_content ."\r\n";
        }
    }

    /**
     * 给一个或多个账号发送文本内容的邮件
     * @param $people  发送对象邮箱的数组
     * @param $subject 标题
     * @param $textBody 文本内容
     * @return mixed
     */
    public function sendTextMailToPeople($people, $subject, $textBody)
    {
        return $this->_stmp->addTo($people)
            ->setSubject($subject)
            ->setBodyText($textBody)
            ->send();
    }

    /**
     * 给一个或多个账号发送html内容的邮件
     * @param $people  发送对象邮箱的数组
     * @param $subject 标题
     * @param $textBody 文本内容
     * @return mixed
     */
    public function sendHtmlMailToPeople($people, $subject, $htmlBody)
    {
        return $this->_stmp->addTo($people)
            ->setSubject($subject)
            ->setBodyHtml($htmlBody)
            ->send();
    }
}