<?php
namespace app\index\model;
use	think\Model;
use	app\index\model\item;
class Itemorderinfo extends Model{
    //服务订单详情表
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
	
	//item_服务信息_读取器
	protected function  getItemAttr ($val,$data){
        $tmp=item::get(['id'=>$data['item']])->toArray();
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
	
	//Price_单价_读取器
	protected function  getPriceAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//Total_总价格_读取器
	protected function  getTotalAttr ($val,$data){
	    return opt_decimal($val);
	}

}
