<?php
namespace app\index\model;
use	think\Model;
class Code extends Model{
    //条码表
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
    
    //Type_类型_读取器
	protected function  getTypeAttr ($val,$data){
	    $tmp=['0'=>'条形码','1'=>'二维码'];
	    $re['name']=$tmp[$data['type']];
	    $re['ape']=$data['type'];
		return $re;
	}
	
	//查询排序
	protected static function base($query){
		$query->order('id desc');
	}
}
