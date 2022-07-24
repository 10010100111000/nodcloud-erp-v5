<?php
namespace app\index\model;
use	think\Model;
use	app\index\model\goods;
use	app\index\model\warehouse;
use	app\index\model\opurchaseinfo;
class Rpurchaseinfo extends Model{
    //采购入库详情表
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
    
    
    //oid_所属采购订单详情_读取器
	protected function  getOidAttr ($val,$data){
	    $tmp=opurchaseinfo::get($data['oid'])->toArray();
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
	
	
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
	
	//Price_单价_读取器
	protected function  getPriceAttr ($val,$data){
	    return opt_decimal($val);
	}
	//Total_总价格_读取器
	protected function  getTotalAttr ($val,$data){
	    return opt_decimal($val);
	}

}
