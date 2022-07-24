<?php
namespace app\index\model;
use	think\Model;
class Printcode extends Model{
    //报表打印模板
    protected $resultSetType = 'collection';//返回数组,需使用->toArray()
}
