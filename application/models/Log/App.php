<?php
namespace Log;
use \Illuminate\Database\Capsule\Manager as DB;
class AppModel
{
	public function getAppInfoByid($appid)
	{
		$app = DB::table('app')->where('appid', $appid)->first();
		return (array)$app;
	}
}