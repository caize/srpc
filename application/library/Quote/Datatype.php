<?php
/**
 * Quote/Datatype.php
 * author:    何玮<away@myhexin.com>
 * create:    2009-10-14
 * note:
 *    行情数据项目对应表
 */
namespace Quote;
class Datatype
{
    /**
	* 默认的对应表
	*
	* var $_dataTypes
	*/
	static private $_dataTypes = array(
			'datetime'		=> 1,		// 时间
			'markettype'	=> 2,		// 市场类别
			'codetype'		=> 3,		// 证券类型
			'code'			=> 5,		// 代码
			'pre'			=> 6,		// 昨收
			'open'			=> 7,		// 开盘价
			'high'			=> 8,		// 最高价
			'low'			=> 9,		// 最低价
			'new'			=> 10,		// 现价
			'close'			=> 11,		// 收盘价
			'volclass'		=> 12,		// 成交量分类
			'vol'			=> 13,		// 总手
			'outvol'		=> 14,		// 外盘
			'invol'			=> 15,		// 内盘
			'openvol'		=> 17,		// 开盘量
			'money'			=> 19,		// 金额
			'buyprice'		=> 20,		// 买入价
			'sellprice'		=> 21,		// 卖出价
			'buycount'		=> 22,		// 委买
			'sellcount'		=> 23,		// 委卖
			'buyprice1'		=> 24,		// 买一价
			'buycount1'		=> 25,		// 买一量
			'buyprice2'		=> 26,		// 买二价
			'buycount2'		=> 27,		// 买二量
			'buyprice3'		=> 28,		// 买三价
			'buycount3'		=> 29,		// 买三量
			'sellprice1'	=> 30,		// 卖一价
			'sellcount1'	=> 31,		// 卖一量
			'sellprice2'	=> 32,		// 卖二价
			'sellcount2'	=> 33,		// 卖二量
			'sellprice3'	=> 34,		// 卖三价
			'sellcount3'	=> 35,		// 卖三量

			'zqmc'			=> 55,		// 证券名称
			'zgb'			=> 402,		// 总股本
			'shgzg'			=> 407,		// 流通股
			'ag'			=> 1674,	// A股
			'bg'			=> 410,		// B股
			'hg'			=> 411,		// H股
			'gdzs'			=> 1670,	// 股东总数
			'sgbl'			=> 454,		// 送股比率
			'pgbl'			=> 455,		// 配股比率
			'pgj'			=> 456,		// 配股价
			'zzbl'			=> 460,		// 转增比率
			'pxbl'			=> 463,		// 派息率
			'cqr'			=> 466,		// 除权日
			'djr'			=> 467,		// 登记日
			'pssr'			=> 468,		// 配股上市日

			'ldzchj'		=> 520,		// 流动资产合计
			'gdzchj'		=> 532,		// 股东资产合计
			'zczj'			=> 543,		// 资产总计
			'gdqyhj'		=> 575,		// 股东权益合计
			'jlr'			=> 619,		// 净利润

			'mgyl'			=> 1002,	// 每股盈利
			'mgjzc'			=> 1005,	// 每股净资产
			'hx_star'		=> 1110,	// 星级

			'zhangdie'		=> 264648,	// 涨跌
			'zhangdiefu'	=> 199112,	// 涨跌幅
			'upperlim'		=> 69,		// 涨停价
			'lowerlim'		=> 70,		// 跌停价
			'huanshou'		=> 1968584,	// 换手

			'zdmr'			=> 1340,	// 主动买入
			'zdmc'			=> 1342,	// 主动卖出
			'bdmr'			=> 1344,	// 被动买入
			'bdmc'			=> 1346,	// 被动卖出
		);

    /**
     * 周期map
     *
     * var array
     */
    protected static $_periodMap = array(
        'real' => 0,
        'now'		=> 0,
        'second'	=> 4096,
        'sec'		=> 4096,
        'minute'	=> 8192,
        'min'		=> 12289,
        '1mink'		=> 12289,
        '5mink'		=> 12293,
        '5min'		=> 12293,
        '15mink'	=> 12303,
        '15min'		=> 12303,
        '30mink'	=> 12318,
        '30min'		=> 12318,
        '60mink'	=> 12348,
        '60min'		=> 12348,
        'day'		=> 16384,
        'week'		=> 20481,
        'month'		=> 24577,
        'year'		=> 28673,
        'extra'		=> 57344,
    );

	/**
	* 根据名称取得数据项目Id
	*
	* @param	string	$dataType	字符串类型的名称
	* @return	string|number
	*/
	static public function getDataTypeByName($dataType)
	{
		if (isset(self::$_dataTypes[$dataType])) {
			return self::$_dataTypes[$dataType];
		} else {
			return $dataType;
		}
	}

    /**
     * 将传入的period转化为服务器可识别的周期
     *
     * @param string|number period
     * @return string|number
     */
    static public function getPeriod($period)
    {
        if (is_string($period)) {
            $period = strtolower($period);
            if (isset(self::$_periodMap[$period])) {
                $period = self::$_periodMap[$period];
            } else {
                $period = '';
            }
        }
        return $period;
    }
}
