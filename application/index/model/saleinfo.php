<?php
namespace app\index\model;
use	think\Model;
use	app\index\model\room;
class Saleinfo extends Model{
    //销货详情表
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
	
	//room_仓库信息_读取器
	protected function  getRoomAttr ($val,$data){
	    session('room_noauth',true);
        $tmp=room::get(['id'=>$data['room'],'noauth'=>'ape'])->toArray();
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
	
	//Price_单价_读取器
	protected function  getPriceAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//Discount_折扣_读取器
	protected function  getDiscountAttr ($val,$data){
	    return opt_decimal($val);
	}
	//Total_总价格_读取器
	protected function  getTotalAttr ($val,$data){
	    return opt_decimal($val);
	}

}
