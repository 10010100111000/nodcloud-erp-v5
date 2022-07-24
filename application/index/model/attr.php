<?php
namespace app\index\model;
use	think\Model;
use	app\index\model\attribute;
class Attr extends Model{
    //商品规格属性表
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
    
    //Buy_购货价格_读取器
	protected function  getBuyAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//Sell_销货价格_读取器
	protected function  getSellAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//retail_零售价格_读取器
	protected function  getRetailAttr ($val,$data){
	    return opt_decimal($val);
	}
	
    //Ape_组合名称_读取器
	protected function  getApeAttr ($val,$data){
		$re['ape']=$data['ape'];
		$re['name']=attr_name($data['ape']);
		return $re;
	}
	
	//查询排序
	protected static function base($query){
		$query->order('id desc');
	}
}
