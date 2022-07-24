<?php
namespace app\index\model;
use	think\Model;
use	app\index\model\goodsclass;
use	app\index\model\unit;
use	app\index\model\warehouse;
use	app\index\model\brand;
class Goods extends Model{
    //商品表
	protected $resultSetType = 'collection';//返回数组,需使用->toArray()
	
	//imgs_图片_读取器
	protected function  getImgsAttr ($val,$data){
		return json_decode($data['imgs'],true);
	}
	//class_商品分类_读取器
	protected function  getClassAttr ($val,$data){
	    $tmp=goodsclass::get($data['class'])->toArray();
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
	//unit_商品单位_读取器
	protected function  getUnitAttr ($val,$data){
	    if(empty($data['unit'])){
	        $re['info']['name']='暂不关联';
    	    $re['ape']=0;
	    }else{
	        $tmp=unit::get($data['unit'])->toArray();
    	    $re['info']=$tmp;
    	    $re['ape']=$tmp['id'];
	    }
		return $re;
	}
	//brand_商品品牌_读取器
	protected function  getBrandAttr ($val,$data){
	    if(empty($data['brand'])){
	        $re['info']['name']='暂不关联';
    	    $re['ape']=0;
	    }else{
	        $tmp=brand::get($data['brand'])->toArray();
    	    $re['info']=$tmp;
    	    $re['ape']=$tmp['id'];
	    }
		return $re;
	}
	//warehouse_默认仓库_读取器
	protected function  getWarehouseAttr ($val,$data){
	    if(empty($data['warehouse'])){
	        $re['info']['name']='暂不关联';
    	    $re['ape']=0;
	    }else{
	        $tmp=warehouse::get(['id'=>$data['warehouse'],'noauth'=>'ape'])->toArray();
    	    $re['info']=$tmp;
    	    $re['ape']=$tmp['id'];
	    }
		return $re;
	}
	
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
	
	//Integral_积分_读取器
	protected function  getIntegralAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//查询排序
	protected static function base($query){
		$query->order('id desc');
	}
}
