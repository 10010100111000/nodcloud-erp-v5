<?php
namespace app\index\model;
use	think\Model;
use	app\index\model\room;
use	app\index\model\warehouse;
class Allocationinfo extends Model{
    //调拨详情表
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
	
	//room_仓库信息_读取器
	protected function  getRoomAttr ($val,$data){
        $tmp=room::get($data['room'])->toArray();
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
	
	//Towarehouse_调入仓库名称_读取器
	protected function  getTowarehouseAttr ($val,$data){
        $tmp=warehouse::get(['id'=>$data['towarehouse'],'noauth'=>'ape'])->toArray();   
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
	
	//查询排序
	protected static function base($query){
		$query->order('id desc');
	}
}
