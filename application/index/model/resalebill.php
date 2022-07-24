<?php
namespace app\index\model;
use	think\Model;
use	app\index\model\account;
use	app\index\model\user;
class Resalebill extends Model{
    //销货退货单
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
    
    //时间自动转换
	protected $type=['time'=>'timestamp:Y-m-d H:i:s'];
	
    //Account_结算账户_读取器
	protected function  getAccountAttr ($val,$data){
        $tmp=account::get(['id'=>$data['account'],'noauth'=>'ape'])->toArray();
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
	
	//user_操作人_读取器
	protected function  getUserAttr ($val,$data){
        $tmp=user::get(['id'=>$data['user'],'noauth'=>'ape'])->toArray();
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
    
	//查询排序
	protected static function base($query){
		$query->order('id desc');
	}
}
