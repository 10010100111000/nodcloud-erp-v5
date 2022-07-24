<?php
namespace app\index\model;
use	think\Model;
class Often extends Model{
    //常用操作
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
}
