<?php
namespace app\index\model;
use	think\Model;
use	app\index\model\goods;
use	app\index\model\warehouse;
use	app\index\model\supplier;
use	app\index\model\customer;
class Summary extends Model{
    //数据汇总表
    
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
    
    //时间自动转换
	protected $type=['time'=>'timestamp:Y-m-d'];
	
	//company_单位信息_读取器
	protected function  getCompanyAttr ($val,$data){
	    $type=$data['type'];
	    //1:购货单|2:采购单|3:购货退货单|4:销货单|5:销货退货单|6:零售单|7:零售退货单|8:积分兑换单|9:调拨单|10:其他入库单|11:其他出库单
	    if(in_array($type,[1,2,3])){
	        //供应商
	        $tmp=supplier::get(['id'=>$data['company'],'noauth'=>'ape'])->toArray();
    	    $re['info']=$tmp;
    	    $re['ape']=$tmp['id'];
	    }elseif(in_array($type,[4,5,6,7,8])){
	        //客户
	        $tmp=customer::get(['id'=>$data['company'],'noauth'=>'ape'])->toArray();
    	    $re['info']=$tmp;
    	    $re['ape']=$tmp['id'];
	    }else{
	        //无需读取
	        $re['info']['name']='暂无';
    	    $re['ape']=$data['company'];
	    }
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
	
	//warehouse_默认仓库_读取器
	protected function  getWarehouseAttr ($val,$data){
	    if(empty($data['warehouse'])){
	        $re['info']['name']='暂无';
    	    $re['ape']=0;
	    }else{
	        $tmp=warehouse::get(['id'=>$data['warehouse'],'noauth'=>'ape'])->toArray();
    	    $re['info']=$tmp;
    	    $re['ape']=$tmp['id'];
	    }
		return $re;
	}
	
	//user_操作人_读取器
	protected function  getUserAttr ($val,$data){
        $tmp=user::get(['id'=>$data['user'],'noauth'=>'ape'])->toArray();
	    $re['info']=$tmp;
	    $re['ape']=$tmp['id'];
		return $re;
	}
	
	
	//price_商品单价_读取器
	protected function  getPriceAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//total_商品总价_读取器
	protected function  getTotalAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//查询排序
	protected static function base($query){
		$query->order('id desc');
	}
}
