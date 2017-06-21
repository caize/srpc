<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/10
 * Time: 13:34
 */
namespace Localapi\Rpcapi;
use Api\Controller\Rpc;
use Rpc\RpclocalModel;
use Hexin\FormulaCache;
class FormulacacheModel extends RpclocalModel
{
    /**
     * @param $stockcode
     * @param $formulaId
     * @param string $period
     * @return \Rpc\json|\Rpc\xml
     */
    public function getData($stockcode, $formulaId, $period = '16384')
    {
        return $this->_send(
            array($this, 'find'), array($stockcode, $formulaId, $period)
        );
    }

    /**
     * @param array $stockcodeArr
     *      股票列表
     *      array(
                300033,
     *          000001
     *      )
     * @param array $formulaIdArr
     *      array(
     *          11,
     *          10
     *      )
     *      公式列表
     * @param string $period
     *
     */
    public function getDatas($stockcodeArr, $formulaIdArr, $period = '16384')
    {
        return $this->_send(
            function ($stockcodeArr, $formulaIdArr, $period) {
                $returnData = array();
                foreach ($stockcodeArr as $stockcode) {
                    foreach ($formulaIdArr as $formulaId) {
                        $returnData[$stockcode][] = $this->find($stockcode, $formulaId, $period);
                    }
                }
                return $returnData;
            },
            array($stockcodeArr, $formulaIdArr, $period)
        );

    }

    protected function find($stockcode, $formulaId, $period = 16384)
    {
        $config = APPLICATION_ENV == 'development' ? '127.0.0.1:6379': null;
        $data = FormulaCache::find($stockcode, $formulaId, $period, $config);
        return array(
            'code' => $data->getCode(),
            'value' => $data->getValue(),
            'formulaId' => $data->getFormulaId(),
            'period' => $period,
            'mtime' => $data->getMtime()
        );
    }

}