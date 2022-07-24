<?php
namespace app\index\model;
use	think\Model;
class Sys extends Model{
    //条码表
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
    
    //info_配置_读取器
	protected function  getInfoAttr ($val,$data){
		return json_decode($data['info'],true);
	}
	//info_配置_设置器
	protected function  setInfoAttr ($val){
		return json_encode($val,JSON_UNESCAPED_UNICODE);
	}
	//查询排序
	protected static function base($query){
		$query->order('id asc');
	}
}
