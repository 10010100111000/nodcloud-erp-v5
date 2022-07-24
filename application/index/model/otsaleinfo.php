<?php
namespace app\index\model;
use	think\Model;
use	app\index\model\room;
class Otsaleinfo extends Model{
    //其他出库表
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
	
	//room_仓库信息_读取器
	protected function  getRoomAttr ($val,$data){
	    session('room_noauth',true);
        $tmp=room::get(['id'=>$data['room'],'noauth'=>'ape'])->toArray();
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
	
	//查询排序
	protected static function base($query){
		$query->order('id desc');
	}
}
