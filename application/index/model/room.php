<?php
namespace app\index\model;
use	think\Model;
use	app\index\model\goods;
class Room extends Model{
    //仓库库存
    
	protected $resultSetType = 'collection';//返回数组,需使用->toArray()
	
	//goods_商品信息_读取器
	protected function  getGoodsAttr ($val,$data){
        $tmp=goods::get($data['goods'])->toArray();
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
	
	//warehouse_所属仓库_读取器
	protected function  getWarehouseAttr ($val,$data){
        if(session('room_noauth')){
            $tmp=warehouse::get(['id'=>$data['warehouse'],'noauth'=>'ape'])->toArray();
            session('room_noauth',false);
        }else{
            $tmp=warehouse::get($data['warehouse'])->toArray();
        }
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
	
	
	
	//查询排序
	protected static function base($query){
		$query->order('id desc');
	}
	
	
}
