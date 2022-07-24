<?php
namespace app\index\model;
use	think\Model;
class Account extends Model{
    //资金账户
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
    
    //时间自动转换
	protected $type=['create_time'=>'timestamp:Y-m-d'];
	
	//开账时间设置器
	protected function setCreatetimeAttr($value){
	    
		return strtotime($value);
	}
	
	//initial_期初余额_读取器
	protected function  getInitialAttr ($val,$data){
	    return opt_decimal($val);
		
	}
	
	//balance_资金余额_读取器
	protected function  getBalanceAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//查询排序
	protected static function base($query){
		$query->order('id desc');
	}
}
