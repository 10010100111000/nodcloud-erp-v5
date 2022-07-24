<?php
namespace app\index\model;
use	think\Model;
class Customer extends Model{
    //客户表
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
    
    //Integral_积分_读取器
	protected function  getIntegralAttr ($val,$data){
	    return opt_decimal($val);
	}
	//查询排序
	protected static function base($query){
		$query->order('id desc');
	}
}
