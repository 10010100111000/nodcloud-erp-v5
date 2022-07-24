<?php
namespace app\index\model;
use	think\Model;
use	app\index\model\user;
class Accountinfo extends Model{
    //资金账户详情
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
    
    //时间自动转换
	protected $type=['time'=>'timestamp:Y-m-d'];
	
	//money_操作金额_读取器
	protected function  getMoneyAttr ($val,$data){
	    return opt_decimal($val);
	}
	
    //set_资金操作_读取器
	protected function  getSetAttr ($val,$data){
        $tmp=['0'=>'资金减少','1'=>'资金增加'];
        $re['name']=$tmp[$data['set']];
        $re['ape']=$data['set'];
        return $re;
	}
	//type_类型_读取器
	protected function  getTypeAttr ($val,$data){
        $tmp=['1'=>'购货对账单','2'=>'销货对账单','3'=>'购货退货对账单','4'=>'销货退货对账单','5'=>'收款单','6'=>'付款单','7'=>'其他收入单','8'=>'其他支出单','9'=>'零售单收款','10'=>'零售退货单','11'=>'采购入库单','12'=>'服务订单','13'=>'资金调拨单-出','14'=>'资金调拨单-入'];
        $re['name']=$tmp[$data['type']];
        $re['ape']=$data['type'];
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
