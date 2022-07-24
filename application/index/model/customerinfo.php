<?php
namespace app\index\model;
use	think\Model;
class Customerinfo extends Model{
    //客户积分表
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
    
    //时间自动转换
	protected $type=['time'=>'timestamp:Y-m-d'];
    
    //Integral_积分_读取器
	protected function  getIntegralAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//set_资金操作_读取器
	protected function  getSetAttr ($val,$data){
        $tmp=['0'=>'积分减少','1'=>'积分增加'];
        $re['name']=$tmp[$data['set']];
        $re['ape']=$data['set'];
        return $re;
	}
	
	//type_类型_读取器
	protected function  getTypeAttr ($val,$data){
        $tmp=['1'=>'零售单','2'=>'零售退货单','3'=>'人工操作','4'=>'积分兑换单'];
        $re['name']=$tmp[$data['type']];
        $re['ape']=$data['type'];
        return $re;
	}
	
	//number_编号_读取器
	protected function  getNumberAttr ($val,$data){
	    if($data['number'] == '-1'){
	        $re='无';
	    }else{
	        $re=$data['number'];
	    }
        return $re;
	}
}
