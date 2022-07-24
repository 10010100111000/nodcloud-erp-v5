<?php
namespace app\index\model;
use	think\Model;
use	app\index\model\goods;
use	app\index\model\warehouse;
class Otpurchaseinfo extends Model{
    //其他入库表
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
    
    //goods_商品信息_读取器
	protected function  getGoodsAttr ($val,$data){
	    $tmp=goods::get($data['goods'])->toArray();
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
	
	//Attr_辅助属性_读取器
	protected function  getAttrAttr ($val,$data){
	    $re['ape']=$data['attr'];
	    if(empty($data['attr'])){
	        $re['name']='无';
	    }else{
	        $re['name']=attr_name($data['attr']);
	    }
	    return $re;
	}
	
	//warehouse_仓库信息_读取器
	protected function  getWarehouseAttr ($val,$data){
        $tmp=warehouse::get(['id'=>$data['warehouse'],'noauth'=>'ape'])->toArray();
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}

}
