<?php
namespace app\index\model;
use	think\Model;
class User extends Model{
    //用户表
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
    
	//Auth_数据授权_读取器
	protected function  getAuthAttr ($val,$data){
		return json_decode($data['auth'],true);
	}
	
	//Auth_数据授权_设置器
	protected function  setAuthAttr ($val){
		return json_encode($val);
	}
	
	//Root_功能设置_读取器
	protected function  getRootAttr ($val,$data){
		return json_decode($data['root'],true);
	}
	
	//Root_功能设置_设置器
	protected function  setRootAttr ($val){
		return json_encode($val);
	}
	
	//查询排序
	protected static function base($query){
		$query->order('id desc');
	}
}
