<?php
/**
 * Created by PhpStorm.
 * User: l.gang06@yahoo.com
 * Date: 2017/4/13
 * Time: 14:44
 */
namespace Syar;
class Pack
{
    const HEADER_SIZE = 90;
    const HEADER_STRUCT = "Nid/nVersion/NMagicNum/NReserved/a32Provider/a32Token/NBodyLen";
    const HEADER_PACK = "NnNNa32a32N";

    /**
     * @var array Encoder[]
     */
    protected static $_encoder = [];

    const ENCODE_JSON = 'JSON';
    const ENCODE_MSGPACK = 'MSGPACK';
    const ENCODE_PHP = 'PHP';

    protected static $_packagers = [
        self::ENCODE_JSON,
        self::ENCODE_MSGPACK,
        self::ENCODE_PHP,
    ];

    public function unpack($data)
    {
        $yar = new Yar();
        if (strlen($data) < 90) {
            $yar->response['e'] = "Invalid request";
            return $yar;
        }

        $header = substr($data, 0, 82);
        $header = unpack(self::HEADER_STRUCT, $header);

        if (strlen($data) - 82 != $header['BodyLen']) {
            $yar->response['e'] = "Invalid body";
            return $yar;
        }

        $packData = substr($data, 82, 8);
        $yar->packer['packData'] = $packData;

        $packName = $this->getPackName($packData);
        $yar->packer['packName'] = $packName;

        $encoder = $this->getEncoder($packName);
        $request = $encoder->decode(substr($data, 90));

        $yar->header = $header;
        $yar->request = $request;
        $yar->packer['encoder'] = $encoder;
        return $yar;
    }

    protected function getPackName($data)
    {
        foreach (self::$_packagers as $packer) {
            if (strncasecmp($packer, $data, strlen($packer)) == 0) {
                return $packer;
            }
        }
        return self::ENCODE_PHP;
    }

    /**
     * @param Yar $yar
     * @return string
     */
    public function pack($yar)
    {
        /** @var EncoderInterface $packer */
        $packer = $yar->packer['encoder'];
        $data = $packer->encode($yar->getResponse());

        $header =& $yar->header;
        $header['BodyLen'] = strlen($data) + 8;
        $packStr = pack(
            self::HEADER_PACK,
            $header['id'],
            $header['Version'],
            $header['MagicNum'],
            $header['Reserved'],
            $header['Provider'],
            $header['Token'],
            $header['BodyLen']
        );
        return $packStr . $yar->packer['packData'] . $data;
    }

    /**
     * @param string $type
     * @return \Syar\Encoder\Json|\Syar\Encoder\Msgpack|\Syar\Encoder\PHp
     */
    protected function getEncoder($type = self::ENCODE_JSON)
    {
        if (isset(self::$_encoder[$type])) {
            return self::$_encoder[$type];
        }

        switch ($type) {
            case  self::ENCODE_MSGPACK :
                $instance = new \Syar\Encoder\Msgpack();
                break;

            case  self::ENCODE_JSON :
                $instance = new \Syar\Encoder\Json();
                break;

            default :
                $instance = new \Syar\Encoder\Php();
        }

        self::$_encoder[$type] = $instance;
        return $instance;
    }
}