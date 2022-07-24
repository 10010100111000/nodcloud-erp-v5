<?php
namespace app\index\model;
use	think\Model;
use	app\index\model\customer;
use	app\index\model\user;
class Exchangeclass extends Model{
    //积分兑换表
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
    
    //Time_单据日期_读取器
	protected function  getTimeAttr ($val,$data){
		return date('Y-m-d',$data['time']);
	}
	
	//Customer_客户_读取器
	protected function  getCustomerAttr ($val,$data){
	    $tmp=customer::get($data['customer'])->toArray();
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
	
	//account_结算账户_读取器
	protected function  getAccountAttr ($val,$data){
	    $tmp=account::get($data['account'])->toArray();
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
	
	//user_制单人_读取器
	protected function  getUserAttr ($val,$data){
	    if(session('user_noauth')){
        	$tmp=user::get(['id'=>$data['user'],'noauth'=>'ape'])->toArray();
        	session('user_noauth',false);
        }else{
        	$tmp=user::get($data['user'])->toArray();
        }
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
	
	//type_审核状态_读取器
	protected function  getTypeAttr ($val,$data){
	    $tmp=['0'=>'未审核','1'=>'已审核'];
	    $re['name']=$tmp[$data['type']];
	    $re['ape']=$data['type'];
		return $re;
	}
	
	//orderintegral_单据积分_读取器
	protected function  getOrderintegralAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//discount_优惠积分_读取器
	protected function  getDiscountAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//actual_integral_实付积分_读取器
	protected function  getActualintegralAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//Time_单据日期_设置器
	protected function  setTimeAttr ($val){
		return strtotime($val);
	}
	
	//查询排序
	protected static function base($query){
		$query->order('id desc');
	}
}
