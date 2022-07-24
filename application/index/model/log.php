<?php
namespace app\index\model;
use	think\Model;
use	app\index\model\user;
class Log extends Model{
    //操作日志
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
    
    //User_操作用户_读取器
	protected function  getUserAttr ($val,$data){
	    $user=user::get($data['user'])->toArray();
		return $user['name'];
	}
	
	//时间自动转换
	protected $type=['time'=>'timestamp:Y-m-d H:i:s'];
	
	//查询排序
	protected static function base($query){
		$query->order('id desc');
	}
}
