<?php
/**
 *
 * author:	l.gang06@yahoo.com
 * create:	2017-05-16
 *	行情数据结果集
 *  根据 Quote/Result/Quote.php 改写
 */
namespace Quote\Result;
use Quote\Datatype;
class Quote extends Base
{

	/**
	* 数据项目和id映射表
	* 
	* var array
	*/
	protected $_dataTypeMap = array();

	/* 根据服务端返回的xml文件生成结果集
	* 
	* @param String $result 输入的字符串
	* @return boolean true成功;false失败
	*/
	public function fromResult($result)
	{
		$doc = $this->_parseResult($result);
		if (!$doc) {
			return false;
		}
		try {
            /**
             * order key
             */
            $this->_handleOrderResult($doc);

            /**
             * datamap key
             */
            $this->_handleStockIndex($doc);
            /**
             * data key
             */
            $this->_handleDataResult($doc);
        } catch (\Exception $e) {
		    $this->setError($e->getCode(), $e->getMessage());
		    return false;
        }
		return true;
	}

	/**
	* 获得$tagName指定的第一个子节点的值
	*
	* @param DOMElement $p			父节点
	* @param string		$tagName	子节点名称
	* @return string	返回的值
	*/
	protected function _getElementValue($p, $tagName)
	{
		$els = $p->getElementsByTagName($tagName);
		if ($els->length > 0) {
			return $this->_convertEncode(trim($els->item(0)->textContent));
		} else {
			return '';
		}
	}

    /**
     * @param $doc \DOMDocument
     */
	protected function _handleDataResult($doc)
    {
        $dataResults = $doc->getElementsByTagName('DataResult');
        foreach ($dataResults as $dataResult) {
            $period = $dataResult->getAttribute('Period');
            if ($period == Datatype::getPeriod('extra'))
                $period = Datatype::getPeriod('day');
            if (!isset($this->_result['data']) || !isset($this->_result['data'][$period])) {
                $this->_result['data'][$period] = array();
            }
            $codeIndex = $dataResult->getElementsByTagName('CodeIndex');
            if ($codeIndex->length > 0) {
                $this->_result['data'][$period] = $this->_getResultFromHandleCodeIndex($codeIndex);
            } else {
                $this->_result['data'][$period] = $this->_getResultFromHandleMultiRecords($doc);
            }
        }
    }
	/**
	* 处理排序选股结果集
	*
	* @param \DOMDocument $doc
	* @return void
	*/
	protected function _handleOrderResult($doc)
	{
        $orderResults = $doc->getElementsByTagName('OrderResult');
        if ($orderResults->length == 0) {
            return false;
        }
        $orderResult = $orderResults->item(0);
        if (0 != ($code = $this->_getElementValue($orderResult, 'OrderError'))) {
            throw new \Exception('OrderError', $code);
        }
        $order['begin'] = $this->_getElementValue($orderResult, 'SortBegin');
		$order['count'] = $this->_getElementValue($orderResult, 'SortCount');
		$order['total'] = $this->_getElementValue($orderResult, 'SortTotal');
		$order['list'] = array();

		$orderList = $orderResult->getElementsByTagName('OrderList');
        $len = $orderList->length;
		for ($i = 0; $i < $len; ++$i) {
			$orderItem = $orderList->item($i);
			$order['list'][] = array(
				'time'		=> $this->_getElementValue($orderItem, 'Time'),
				'market'	=> $this->_getElementValue($orderItem, 'Market'),
				'code'		=> $this->_getElementValue($orderItem, 'Code'),
				);
		}
		$this->_result['order'] = $order;
	}
	
	/**
	* 根据设置转换编码
	*
	* @param String $input 输入的字符串
	* @return String 解码后的字符串
	*/
	protected function _convertEncode($input)
    {
		if ($this->_convert) {
			return iconv('utf-8', $this->_encode, $input);
		} else {
			return $input;
		}
	}

	/**
	* 处理数据项目对应表
	*
	* @param DOMElement $stockIndex
	* @return void
	*/
	protected function _handleStockIndex($doc)
	{

        $stockIndexs = $doc->getElementsByTagName('StockIndex');
        $len = $stockIndexs->length;
        if (!$len) {
            return false;
        }
        for ($i = 0; $i < $len; ++$i) {
            $stockIndex = $stockIndexs->item($i);
            //$stockIndex
            $id = $stockIndex->getAttribute('Id');
            $orientName = $this->_convertEncode($stockIndex->getAttribute('Name'));
            $left = strpos($orientName, '(');
            if (false === $left) {
                $name 	= $orientName;
                $param 	= '';
            } else {
                $name 	= substr($orientName, 0, $left);
                $right 	= strpos($orientName, ')', $left);
                $param 	= substr($orientName, $left+1, $right-$left-1);
            }
            $name = strtolower($name);
            $this->_dataTypeMap[$id][$name] = 1;
            $items = $stockIndex->getElementsByTagName('Item');
            $subLen = $items->length;
            $this->_result['dataTypeMap'][$name]  = ['id' => $id, 'param' => $param];
            for ($j = 0; $j < $subLen; ++$j) {
                $item = $items->item($j);
                $itemId = $item->getAttribute('Id');
                $name = strtolower($this->_convertEncode(trim($item->textContent)));
                $this->_dataTypeMap[$itemId][$name] = 1;
                $this->_result['dataTypeMap'][$name]['id'] = $itemId;
            }
        }
	}

	/**
	* 将数据结果放入结果池
	*
	* @param array $record	结果条目
	* @param string $code	代码
	* @param number $period 周期
	* @return boolean
	*/
	protected function _setRecordData($record, $code, $period)
	{
		$codeResult = $this->_getCodeResult($code);
		if (!$codeResult) {
			return false;
		}
		if (!isset($codeResult->result)) {
			$codeResult->result = array();
		}

		if (!isset($codeResult->result[$period])) {
			$codeResult->result[$period] = array();
		}
		$time = isset($record[1]) ? $record[1] : 0;
		foreach ($record as $type=>$value) {
			if ($type == 1 || $type == 5) {
				continue;
			}
			if (!isset($codeResult->result[$period][$type]))
				$codeResult->result[$period][$type] = array();
			$codeResult->result[$period][$type][] = array($time, $value);
		}
		return true;
	}

	/**
	* 根据代码获得对象
	*
	* @param string $code
	* @return Object
	*/
	protected function _getCodeResult($code)
    {
		return isset($this->_result[$code]) ? $this->_result[$code] : null;
	}


	/**
	* 以CodeIndex的方式解析数据
	*
     * @param DOMElement  $codeIndex
     * @param $result
	* @return number 返回处理的条目数
	*/
	protected function _getResultFromHandleCodeIndex($codeIndexs)
	{
	    $len = $codeIndexs->length;
        $result = array();
        for ($i = 0; $i < $len; ++$i) {
            // 使用codeIndex分块
            $codeIndex = $codeIndexs->item($i);
            $code = $codeIndex->getAttribute('Code');
            if (!isset($result[$code])) {
                $result[$code]['code'] = $code;
                $result[$code]['market'] = $codeIndex->getAttribute('Market');
                //$result[$code]['h'] = $codeIndex->getAttribute('H');
                $result[$code]['table'] = [];
            }

            $codeAppend = $codeIndex->getElementsByTagName('CodeAppend');
            if ($codeAppend->length > 0) {
                $codeAppend = $codeAppend->item(0);
                $result[$code]['append'] = array();
                $items = $codeAppend->getElementsByTagName('Item');
                for ($j = 0; $j < $items->length; ++$j) {
                    $item = $items->item($j);
                    $result[$code]['append'][$item->getAttribute('Id')] = trim($item->textContent);
                }
            }

            $records = $codeIndex->getElementsByTagName('Record');
            for ($j = 0; $j < $records->length; ++$j) {
                $record = $records->item($j);
                $record = $this->_handleRecord($record);
                $result[$code]['table'][] = $record;
            }
        }

		return $result;
	}


	/**
	* 处理一个Record标签
	*
	* @param DOMElement $record
	* @return array
	*/
	protected function _handleRecord($record)
	{
		$result = array();
		$items = $record->getElementsByTagName('Item');
        $len = $items->length;
		for ($i = 0; $i < $len; ++$i) {
			$item = $items->item($i);
			$id = $item->getAttribute('Id');
			$result[$id] = $this->_convertEncode(trim($item->textContent));
			if (isset($this->_dataTypeMap[$id])) {
			    foreach ($this->_result[$id] as $name => $v) {
			        $result[$name] = $result[$id];
                }
            }
		}
		return $result;
	}

    /**
     * 以Records方式返回的结果集
     *
     * @param \DOMDocument	$doc
     * @param number			$period
     * @return 处理的条目数
     */
    protected function _getResultFromHandleMultiRecords($doc)
    {
        $records = $doc->getElementsByTagName('Record');
        $len = $records->length;
        $returnData = array();
        for ($i = 0; $i < $len; ++$i) {
            $record = $records->item($i);
            $r = $this->_handleMultiRecord($record);
            $code = $r->code;
            if (!isset($returnData[$code])) {

                $returnData[$code]['code'] = $r->code;
                $returnData[$code]['market'] = $r->market;
                $returnData[$code]['h'] = $r->h;
                $returnData[$code]['table'] = [];
            }
            $returnData[$code]['table'][] = $r->result;
        }
        return $returnData;
    }

	/**
	* 处理一个MultiRecord
	*
	* @param DOMElmeent $record
	* @return array
	*/
	protected function _handleMultiRecord($record)
	{
		$return = new \stdClass();
		$result = array();
		$items = $record->getElementsByTagName('Item');
        $len = $items->length;
        $hField = 'H'; // 为了代码风格检查不得已而为之
		for ($i = 0; $i < $len; ++$i) {
			$item = $items->item($i);
			$id = $item->getAttribute('Id');
			$value = $this->_convertEncode(trim($item->textContent));
			$result[$id] = $value;
			if ($id == 5) {
				$return->code = $value;
				$return->market = $item->getAttribute('Market');
				$return->h = $item->getAttribute('H');
			}
		}
		$return->result = $result;
		return $return;
	}

	/**
	* 将周期转换为可用的数字格式
	*
	* @param string|number $period
	*/
	protected function _normalPeriod($period)
	{
		$period = Datatype::getPeriod($period);
		if ($period == Datatype::getPeriod('extra')) {
			$period = Datatype::getPeriod('day');
        }
		return $period;
	}

	/**
	* 将数据项转为确切的数字
	*
	* @param string|number $dataType
	* @return number
	*/
	protected function _normalDataType($dataType)
	{
		if (is_integer($dataType)) {
			return $dataType;
		} else if (is_string($dataType)) {
			$dataType = strtolower($dataType);
			if (isset($this->_dataTypeMap[$dataType])) {
				$dataType = $this->_dataTypeMap[$dataType]['id'];
			} else {
				$dataType = Datatype::getDataTypeByName($dataType);
			}
		}
		return $dataType;
	}
}


