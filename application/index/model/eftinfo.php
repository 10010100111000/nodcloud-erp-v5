<?php
namespace app\index\model;
use	think\Model;
use	app\index\model\account;
class Eftinfo extends Model{
    //资金调拨详情表
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
	
	//account_id_调出账户信息_读取器
	protected function  getAccountidAttr ($val,$data){
        $tmp=account::get($data['account_id'])->toArray();
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
	
	//toaccount_id_调出账户信息_读取器
	protected function  getToaccountidAttr ($val,$data){
        $tmp=account::get($data['toaccount_id'])->toArray();
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
	
	//money_金额_读取器
	protected function  getMoneyAttr ($val,$data){
	    return opt_decimal($val);
	}

}
