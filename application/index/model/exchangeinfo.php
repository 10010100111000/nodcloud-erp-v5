<?php
namespace app\index\model;
use	think\Model;
use	app\index\model\room;
class Exchangeinfo extends Model{
    //积分兑换详情表
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
	
	//room_仓库信息_读取器
	protected function  getRoomAttr ($val,$data){
	    session('room_noauth',true);
        $tmp=room::get(['id'=>$data['room'],'noauth'=>'ape'])->toArray();
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
	
	
	//integral_单价_读取器
	protected function  getIntegralAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//allintegral_总价格_读取器
	protected function  getAllintegralAttr ($val,$data){
	    return opt_decimal($val);
	}

}
