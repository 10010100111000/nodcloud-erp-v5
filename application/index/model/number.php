<?php
namespace app\index\model;
use	think\Model;
class Number extends Model{
    //编码规则表
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
    
	//查询排序
	protected static function base($query){
		$query->order('id asc');
	}
}
