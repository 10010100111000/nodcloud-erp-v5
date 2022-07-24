<?php
namespace app\index\model;
use	think\Model;
class Goodsclass extends Model{
    //商品分类
	
	protected $resultSetType = 'collection';//返回数组,需使用->toArray()
	
	//Pid_所属分类_读取器
	protected function  getPidAttr ($val,$data){
	    if(empty($data['pid'])){
	        //顶级分类
	        $re['name']='顶级分类';
	        $re['ape']='0';
	    }else{
	        //下属分类
	        $tmp=goodsclass::get(['id'=>$data['pid']])->toArray();
	        $re['name']=$tmp['name'];
	        $re['ape']=$data['pid'];
	    }
		return $re;
	}
	
	//查询排序
	protected static function base($query){
		$query->order('id desc');
	}
}
