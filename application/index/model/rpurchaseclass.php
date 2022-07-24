<?php
namespace app\index\model;
use	think\Model;
use	app\index\model\supplier;
use	app\index\model\account;
use	app\index\model\user;
use	app\index\model\opurchaseclass;
class Rpurchaseclass extends Model{
    //采购入库表
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
    
    //Time_单据日期_读取器
	protected function  getTimeAttr ($val,$data){
		return date('Y-m-d',$data['time']);
	}
	
	//supplier_供应商_读取器
	protected function  getSupplierAttr ($val,$data){
	    $tmp=supplier::get($data['supplier'])->toArray();
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
	
	//total_单据金额_读取器
	protected function  getTotalAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//actual_实际金额_读取器
	protected function  getActualAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//money_优惠金额_读取器
	protected function  getMoneyAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//Time_单据日期_设置器
	protected function  setTimeAttr ($val){
		return strtotime($val);
	}
	
    //billtype_类型_读取器
	protected function  getBilltypeAttr ($val,$data){
	    $tmp=['-1'=>'未审核','0'=>'未结算','1'=>'部分结算','2'=>'已结算'];
	    $re['name']=$tmp[$data['billtype']];
	    $re['ape']=$data['billtype'];
		return $re;
	}
	
	//Oid_所属OID_读取器
	protected function  getOidAttr ($val,$data){
	    $tmp=opurchaseclass::get($data['oid'])->toArray();
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
    
	//查询排序
	protected static function base($query){
		$query->order('id desc');
	}
}
