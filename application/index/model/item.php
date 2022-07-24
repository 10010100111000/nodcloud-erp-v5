<?php
namespace app\index\model;
use	think\Model;
class Item extends Model{
    //服务项目
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
	
	//price_服务价格_读取器
	protected function  getPriceAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//查询排序
	protected static function base($query){
		$query->order('id desc');
	}
}
