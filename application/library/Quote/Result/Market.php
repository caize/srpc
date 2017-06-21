<?php

/**
 *
 * author:    l.gang06@yahoo.com
 * create:    2017-05-16
 *    请求市场列表返回的结果集
 *  根据 Quote/Requester.php 改写
 */
namespace Quote\Result;

class Market extends Base
{

	/**
	* 根据服务端返回的xml文件生成结果集
	* 
	* @param String $result 输入的字符串
	* @return boolean true成功;false失败
	*/
	public function fromResult($result)
	{
		$doc = $this->_parseResult($result);
		if (!$doc)
			return false;
		
		$recordList = $doc->getElementsByTagName('Record');
        for ($i = 0; $i < $recordList->length; ++$i) {
            $record = $recordList->item($i);
            $market = [];
            $market['id'] = $record->getAttribute('Market');
            $market['name'] = $this->_convertEncode($record->getAttribute('Name'));
            $itemList = $record->getElementsByTagName('Item');
            for ($j = 0; $j < $itemList->length; ++$j) {
                $item = $itemList->item($j);
                $submarket = [];
                $submarket['id'] = $item->getAttribute('Id');
                $submarket['name'] = $this->_convertEncode($item->textContent);
                $submarket['name'] = trim($submarket['name'], "\n");
                if (!isset($market->submarkets)) {
                    $market->submarkets = array();
                }
                $market['submarkets'][] = $submarket;
            }
            $this->_result[$market['id']] = $market;
		}
		return true;
	}
}