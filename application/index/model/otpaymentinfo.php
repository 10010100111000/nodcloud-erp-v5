<?php
namespace app\index\model;
use	think\Model;
use	app\index\model\account;
class Otpaymentinfo extends Model{
    //收款详情表
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
	
	//Account_资金账户_读取器
	protected function  getAccountAttr ($val,$data){
	    $tmp=account::get(['id'=>$data['account'],'noauth'=>'ape'])->toArray();
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
	
	//Total_结算金额_读取器
	protected function  getTotalAttr ($val,$data){
	    return opt_decimal($val);
	}

}
