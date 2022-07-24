<?php
namespace app\index\model;
use	think\Model;
use	app\index\model\purchaseclass;
use	app\index\model\repurchaseclass;
use	app\index\model\otpurchaseclass;
use	app\index\model\saleclass;
use	app\index\model\resaleclass;
use	app\index\model\otsaleclass;
use	app\index\model\cashierclass;
use	app\index\model\recashierclass;
use	app\index\model\allocationclass;
use	app\index\model\rpurchaseclass;
use	app\index\model\exchangeclass;
class Serialinfo extends Model{
    //串号详情
	protected $resultSetType = 'collection';//返回数组,需使用->toArray()
	
	//Class_类ID_读取器
	protected function  getClassAttr ($val,$data){
	    session('user_noauth',true);
	    if($data['type']==1){
	        //购货单
	        $tmp=purchaseclass::get(['id'=>$data['class'],'noauth'=>'ape'])->toArray();
	    }elseif($data['type']==2){
	        //销货单
	        $tmp=saleclass::get(['id'=>$data['class'],'noauth'=>'ape'])->toArray();
	    }elseif($data['type']==3){
	        //购货退货单
	        $tmp=repurchaseclass::get(['id'=>$data['class'],'noauth'=>'ape'])->toArray();
	    }elseif($data['type']==4){
	        //销货退货单
	        $tmp=resaleclass::get(['id'=>$data['class'],'noauth'=>'ape'])->toArray();
	    }elseif($data['type']==5){
	        //调拨单
	        $tmp=allocationclass::get(['id'=>$data['class'],'noauth'=>'ape'])->toArray();
	    }elseif($data['type']==6){
	        //其他入库单
	        $tmp=otpurchaseclass::get(['id'=>$data['class'],'noauth'=>'ape'])->toArray();
	    }elseif($data['type']==7){
	        //其他出库单
	        $tmp=otsaleclass::get(['id'=>$data['class'],'noauth'=>'ape'])->toArray();
	    }elseif($data['type']==8){
	        //零售单
	        $tmp=cashierclass::get(['id'=>$data['class'],'noauth'=>'ape'])->toArray();
	    }elseif($data['type']==9){
	        //零售退货单
	        $tmp=recashierclass::get(['id'=>$data['class'],'noauth'=>'ape'])->toArray();
	    }elseif($data['type']==10){
	        //采购退货单
	        $tmp=rpurchaseclass::get(['id'=>$data['class'],'noauth'=>'ape'])->toArray();
	    }elseif($data['type']==11){
	        //积分兑换单
	        $tmp=exchangeclass::get(['id'=>$data['class'],'noauth'=>'ape'])->toArray();
	    }
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
	
	//type_类型_读取器
	protected function  getTypeAttr ($val,$data){
        $tmp=['1'=>'购货单','2'=>'销货单','3'=>'购货退货单','4'=>'销货退货单','5'=>'调拨单','6'=>'其他入库单','7'=>'其他出库单','8'=>'零售单','9'=>'零售退货单','10'=>'采购入库单','11'=>'积分兑换单'];
        $re['name']=$tmp[$data['type']];
        $re['ape']=$data['type'];
        return $re;
	}
}
