<?php
namespace app\index\controller;
use think\Controller;
use think\Cache;
use think\Request;
use	app\index\model\user;
use	app\index\model\customer;
use app \index \model \customerinfo ;
use	app\index\model\supplier;
use	app\index\model\warehouse;
use	app\index\model\account;
use	app\index\model\accountinfo;
use	app\index\model\goodsclass;
use	app\index\model\unit;
use	app\index\model\brand;
use	app\index\model\code;
use	app\index\model\attribute;
use	app\index\model\log;
use	app\index\model\sys;
use	app\index\model\goods;
use	app\index\model\tabinfo;
use	app\index\model\printcode;
use	app\index\model\attr;
use	app\index\model\serial;
use	app\index\model\serialinfo;
use	app\index\model\purchaseclass;
use	app\index\model\purchaseinfo;
use	app\index\model\purchasebill;
use	app\index\model\room;
use	app\index\model\roominfo;
use	app\index\model\saleclass;
use	app\index\model\saleinfo;
use	app\index\model\salebill;
use app\index\model\repurchaseclass;
use app\index\model\repurchaseinfo;
use app\index\model\repurchasebill;
use app\index\model\resaleclass;
use app\index\model\resaleinfo;
use app\index\model\resalebill;
use app\index\model\allocationclass;
use app\index\model\allocationinfo;
use app\index\model\otpurchaseclass;
use app\index\model\otpurchaseinfo;
use app\index\model\otsaleclass;
use app\index\model\otsaleinfo;
use app\index\model\gatherclass;
use app\index\model\gatherinfo;
use app\index\model\paymentclass;
use app\index\model\paymentinfo;
use app\index\model\otgatherclass;
use app\index\model\otgatherinfo;
use app\index\model\otpaymentclass;
use app\index\model\otpaymentinfo;
use app\index\model\cashierclass;
use app\index\model\cashierinfo;
use app\index\model\recashierclass;
use app\index\model\recashierinfo;
use app\index\model\often;
use app\index\model\opurchaseclass;
use app\index\model\opurchaseinfo;
use app\index\model\rpurchaseclass;
use app\index\model\rpurchaseinfo;
use app\index\model\item;
use app\index\model\itemorderclass;
use app\index\model\itemorderinfo;
use app\index\model\itemorderbill;
use app\index\model\exchangeclass;
use app\index\model\exchangeinfo;
use app\index\model\eftclass;
use app\index\model\eftinfo;
class Main extends Controller{
    //访问控制
    public function _initialize() {
        if (checklogin()) {
            //功能设置 - 鉴权
            $user=user::get(['id'=>Session('is_user_id'),'noauth'=>'ape']);
            $root=$user['root'];
            //为空无需判断
            if(!empty($root)){
                //获取操作名称
                $action = Request::instance()->action();
                //判断是否需要鉴权
                $config=config('root_info');
                if(isset($config[$action])){
                    //需要鉴权
                    if(empty($root[$config[$action]])){
                        //无权访问
                        echo $this->fetch('aperoot');//直接输出HTML内容
                        exit;
                    }
                }
                
            }
        }else{
            header("Location: http://".$_SERVER['HTTP_HOST']);
            exit;
        }
    }
    //系统主页
    public function index(){
        $this->assign('sys',sys::all());
        $this->assign('user',user::get(['id',session('is_user_id')]));
        $this->assign('often',often::all());
        return $this->fetch();
    }
    //首页
    public function home(){
        $this->assign('sys',sys::all());
        return $this->fetch();
    }
    //客户管理
    public function customer(){
        return $this->fetch();
    }
    //客户积分详情表
    public function customer_form(){
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //供应商管理
    public function supplier(){
        return $this->fetch();
    }
    //仓库管理
    public function warehouse(){
        return $this->fetch();
    }
    //职员管理
    public function user(){
        return $this->fetch();
    }
    //资金账户
    public function account(){
        return $this->fetch();
    }
    //资金账户详情
    public function account_form(){
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //商品分类
    public function goodsclass(){
        $sql['name'] = ['like','%'.input('get.name').'%'];
        $list=goodsclass::where($sql)->select();
        if(empty(input('get.name'))){
            $list=treelist($list,0);
        }
        $this->assign('list',$list);
        return $this->fetch();
    }
    //商品进销详情表
    public function room_form(){
       
        return $this->fetch();
    }
    //计量单位
    public function unit(){
        return $this->fetch();
    }
    //品牌管理
    public function brand(){
        return $this->fetch();
    }
    //条码管理
    public function code(){
        del_code_tmp();//清除条码临时文件
        return $this->fetch();
    }
    //生成条码图像
    public function show_code(){
        del_code_tmp();//清除条码临时文件
        $code=code::get(input('get.id'));
        if(empty($code['type']['ape'])){
            //条形码
            txm($code['code']);
        }else{
            //二维码
            ewm($code['code']);
        }
        exit;
    }
    //辅助属性
    public function attribute(){
        return $this->fetch();
    }
    //操作日志
    public function log(){
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //数据库备份
    public function db_backup(){
		$dbinfo=config('database');
		$sql=new \org \Baksql ($dbinfo['hostname'],$dbinfo['username'],$dbinfo['password'],$dbinfo['database']);
        $this->assign('list',$sql->get_filelist ());
        return $this->fetch();
    }
    //系统设置
    public function sys(){
		$this->assign('user',user::where(['noauth'=>'ape'])->select());
		$this->assign('account',account::where(['noauth'=>'ape'])->select());
		$this->assign('sys',sys::all());
		$this->assign('customer',customer::where(['noauth'=>'ape'])->select());
		return $this->fetch();
    }
    //商品管理
    public function goods(){
        $this->assign('goodsclass',goodsclass::get(input('get.class')));//分类搜索条件赋值
        $this->assign('sys',sys::get(2));//默认分类
        $this->assign('brand',brand::all());
        $this->assign('warehouse',warehouse::all());
        $this->assign('unit',unit::all());
        return $this->fetch();
    }
    //购货单
    public function purchase(){
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //购货单报表
    public function purchase_form(){
        $this->assign('warehouse',warehouse::all());
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //购货单打印
    public function purchase_print(){
        $this->assign('sys',sys::where(['id'=>2])->find());
        $this->assign('printcode',printcode::where(['name'=>'purchase'])->find());
        $this->assign('class',purchaseclass::where(['id'=>input('get.id')])->find());
        $this->assign('info',purchaseinfo::where(['pid'=>input('get.id')])->select());
        return $this->fetch();
    }
    //购货单详情
    public function purchase_info(){
        $id=input('get.id');
        $tmp_info=purchaseinfo::where(['pid'=>$id])->select()->toArray();
        $info=[];
        //改造数组
        foreach ($tmp_info as $vo) {
            $arr=[];
            $arr['id']=$vo['goods']['ape'];
            $arr['serialtype']=$vo['goods']['info']['serialtype'];
            $arr['name']=$vo['goods']['info']['name'];
            $arr['attr']=attr::where(['pid'=>$vo['goods']['ape'],'enable'=>1])->field('ape,buy,sell')->select();
            if(!empty($vo['attr']['ape'])){
                $arr['attr_ape']=$vo['attr']['ape'];
                $arr['attr_name']=attr_name($vo['attr']['ape']);
            }elseif(!empty($arr['attr']) && empty($vo['attr']['ape'])){
                $arr['attr_ape']='0';
                $arr['attr_name']='未选择';
            }
            $arr['warehouse_id']=$vo['warehouse']['ape'];
            $arr['warehouse']=$vo['warehouse'];
            $arr['brand']=$vo['goods']['info']['brand'];
            $arr['number']=$vo['goods']['info']['number'];
            $arr['class']=$vo['goods']['info']['class'];
            $arr['spec']=$vo['goods']['info']['spec'];
            $arr['code']=$vo['goods']['info']['code'];
            $arr['unit']=$vo['goods']['info']['unit'];
            $arr['stocktip']=$vo['goods']['info']['stocktip'];
            $arr['location']=$vo['goods']['info']['location'];
            $arr['integral']=$vo['goods']['info']['integral'];
            $arr['serial']=$vo['serial'];
            $arr['nums']=$vo['nums'];
            $arr['price']=$vo['price'];
            $arr['total']=$vo['total'];
            $arr['batch']=$vo['batch'];
            $arr['data']=$vo['data'];
            array_push($info,$arr);
        }
        //数据鉴权
        $user_auth=json_decode(user_info('auth'),true);
        if(!empty($user_auth) && !empty($user_auth['warehouse'])){
            //需要鉴权
            $check_arr=arrayChange($info,'warehouse_id');
            if(arr_contain($user_auth['warehouse'],$check_arr)){
                $auth_info=true;
            }else{
                $auth_info=false;
            }
        }else{
            $auth_info=true;
        }
        $this->assign('auth_info',$auth_info);
        $this->assign('class',purchaseclass::get(['id'=>$id]));
        $this->assign('info',$info);
        //通用
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch('purchase');
    }
    //购货单对账单
    public function purchase_bill(){
        $this->assign('user',user::all());
        $this->assign('account',account::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //库存查询
    public function room(){
        $this->assign('goodsclass',goodsclass::get(input('get.class')));//分类搜索条件赋值
        $this->assign('warehouse',warehouse::all());
        $this->assign('unit',unit::all());
        $this->assign('brand',brand::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //销货单
    public function sale(){
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //销货单详情
    public function sale_info(){
        $id=input('get.id');
        $tmp_info=saleinfo::where(['pid'=>$id])->select()->toArray();
        $info=[];
        //改造数组
        foreach ($tmp_info as $vo) {
            $arr=[];
            $arr['set_id']=$vo['room']['ape'];
            $arr['goods']=$vo['goods'];
            $arr['serial_info']=implode(',',arrayChange(serial::where(['room'=>$arr['set_id'],'type'=>0])->field('code')->select()->toArray(),'code'));
            $arr['name']=$vo['room']['info']['goods']['info']['name'];
            $arr['attr']=$vo['room']['info']['attr']['name'];
            $arr['warehouse_id']=$vo['room']['info']['warehouse']['ape'];
            $arr['warehouse']=$vo['room']['info']['warehouse']['info']['name'];
            $arr['stock']=$vo['room']['info']['nums'];
            $arr['brand']=$vo['room']['info']['goods']['info']['brand']['info']['name'];
            $arr['number']=$vo['room']['info']['goods']['info']['number'];
            $arr['class']=$vo['room']['info']['goods']['info']['class']['info']['name'];
            $arr['spec']=$vo['room']['info']['goods']['info']['spec'];
            $arr['code']=$vo['room']['info']['goods']['info']['code'];
            $arr['unit']=$vo['room']['info']['goods']['info']['unit']['info']['name'];
            $arr['stocktip']=$vo['room']['info']['goods']['info']['stocktip'];
            $arr['location']=$vo['room']['info']['goods']['info']['location'];
            $arr['integral']=$vo['room']['info']['goods']['info']['integral'];
            $arr['batch']=$vo['room']['info']['batch'];
            $arr['serial']=$vo['serial'];
            $arr['nums']=$vo['nums'];
            $arr['price']=$vo['price'];
            $arr['discount']=$vo['discount'];
            $arr['total']=$vo['total'];
            $arr['data']=$vo['data'];
            array_push($info,$arr);
        }
        
        //数据鉴权
        $user_auth=json_decode(user_info('auth'),true);
        if(!empty($user_auth) && !empty($user_auth['warehouse'])){
            //需要鉴权
            $check_arr=arrayChange($info,'warehouse_id');
            if(arr_contain($user_auth['warehouse'],$check_arr)){
                $auth_info=true;
            }else{
                $auth_info=false;
            }
        }else{
            $auth_info=true;
        }
        $this->assign('auth_info',$auth_info);
        
        $this->assign('class',saleclass::get(['id'=>$id]));
        $this->assign('info',$info);
        //通用
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch('sale');
    }
    //销货详情单报表
    public function sale_form(){
        $this->assign('warehouse',warehouse::all());
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //销货单打印
    public function sale_print(){
        $this->assign('sys',sys::where(['id'=>2])->find());
        $this->assign('printcode',printcode::where(['name'=>'sale'])->find());
        $this->assign('class',saleclass::where(['id'=>input('get.id')])->find());
        $this->assign('info',saleinfo::where(['pid'=>input('get.id')])->select());
        return $this->fetch();
    }
    //销货单对账单
    public function sale_bill(){
        $this->assign('user',user::all());
        $this->assign('account',account::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //购货退货单
    public function repurchase(){
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //购货退货详情
    public function repurchase_info(){
        $id=input('get.id');
        $tmp_info=repurchaseinfo::where(['pid'=>$id])->select()->toArray();
        $info=[];
        //改造数组
        foreach ($tmp_info as $vo) {
            $arr=[];
            $arr['set_id']=$vo['room']['ape'];
            $arr['goods']=$vo['goods'];
            $arr['serial_info']=implode(',',arrayChange(serial::where(['room'=>$arr['set_id'],'type'=>0])->field('code')->select()->toArray(),'code'));
            $arr['name']=$vo['room']['info']['goods']['info']['name'];
            $arr['attr']=$vo['room']['info']['attr']['name'];
            $arr['warehouse_id']=$vo['room']['info']['warehouse']['ape'];
            $arr['warehouse']=$vo['room']['info']['warehouse']['info']['name'];
            $arr['stock']=$vo['room']['info']['nums'];
            $arr['brand']=$vo['room']['info']['goods']['info']['brand']['info']['name'];
            $arr['number']=$vo['room']['info']['goods']['info']['number'];
            $arr['class']=$vo['room']['info']['goods']['info']['class']['info']['name'];
            $arr['spec']=$vo['room']['info']['goods']['info']['spec'];
            $arr['code']=$vo['room']['info']['goods']['info']['code'];
            $arr['unit']=$vo['room']['info']['goods']['info']['unit']['info']['name'];
            $arr['stocktip']=$vo['room']['info']['goods']['info']['stocktip'];
            $arr['location']=$vo['room']['info']['goods']['info']['location'];
            $arr['integral']=$vo['room']['info']['goods']['info']['integral'];
            $arr['batch']=$vo['room']['info']['batch'];
            $arr['serial']=$vo['serial'];
            $arr['nums']=$vo['nums'];
            $arr['price']=$vo['price'];
            $arr['total']=$vo['total'];
            $arr['data']=$vo['data'];
            array_push($info,$arr);
        }
        //数据鉴权
        $user_auth=json_decode(user_info('auth'),true);
        if(!empty($user_auth) && !empty($user_auth['warehouse'])){
            //需要鉴权
            $check_arr=arrayChange($info,'warehouse_id');
            if(arr_contain($user_auth['warehouse'],$check_arr)){
                $auth_info=true;
            }else{
                $auth_info=false;
            }
        }else{
            $auth_info=true;
        }
        $this->assign('auth_info',$auth_info);
        $this->assign('class',repurchaseclass::get(['id'=>$id]));
        $this->assign('info',$info);
        //通用
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch('repurchase');
    }
    //购货退货单报表
    public function repurchase_form(){
        $this->assign('warehouse',warehouse::all());
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //购货退货单打印
    public function repurchase_print(){
        $this->assign('sys',sys::where(['id'=>2])->find());
        $this->assign('printcode',printcode::where(['name'=>'repurchase'])->find());
        $this->assign('class',repurchaseclass::where(['id'=>input('get.id')])->find());
        $this->assign('info',repurchaseinfo::where(['pid'=>input('get.id')])->select());
        return $this->fetch();
    }
    //购货退货对账单
    public function repurchase_bill(){
        $this->assign('user',user::all());
        $this->assign('account',account::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //销货退货单
    public function resale(){
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //销货退货详情
    public function resale_info(){
        $id=input('get.id');
        $tmp_info=resaleinfo::where(['pid'=>$id])->select()->toArray();
        $info=[];
        //改造数组
        foreach ($tmp_info as $vo) {
            $arr=[];
            $arr['set_id']=$vo['room']['ape'];
            $arr['goods']=$vo['goods'];
            $arr['serial_info']=implode(',',arrayChange(serial::where(['room'=>$arr['set_id'],'type'=>1])->field('code')->select()->toArray(),'code'));
            $arr['name']=$vo['room']['info']['goods']['info']['name'];
            $arr['attr']=$vo['room']['info']['attr']['name'];
            $arr['warehouse_id']=$vo['room']['info']['warehouse']['ape'];
            $arr['warehouse']=$vo['room']['info']['warehouse']['info']['name'];
            $arr['stock']=$vo['room']['info']['nums'];
            $arr['brand']=$vo['room']['info']['goods']['info']['brand']['info']['name'];
            $arr['number']=$vo['room']['info']['goods']['info']['number'];
            $arr['class']=$vo['room']['info']['goods']['info']['class']['info']['name'];
            $arr['spec']=$vo['room']['info']['goods']['info']['spec'];
            $arr['code']=$vo['room']['info']['goods']['info']['code'];
            $arr['unit']=$vo['room']['info']['goods']['info']['unit']['info']['name'];
            $arr['stocktip']=$vo['room']['info']['goods']['info']['stocktip'];
            $arr['location']=$vo['room']['info']['goods']['info']['location'];
            $arr['integral']=$vo['room']['info']['goods']['info']['integral'];
            $arr['batch']=$vo['room']['info']['batch'];
            $arr['serial']=$vo['serial'];
            $arr['nums']=$vo['nums'];
            $arr['price']=$vo['price'];
            $arr['total']=$vo['total'];
            $arr['data']=$vo['data'];
            array_push($info,$arr);
        }
        //数据鉴权
        $user_auth=json_decode(user_info('auth'),true);
        if(!empty($user_auth) && !empty($user_auth['warehouse'])){
            //需要鉴权
            $check_arr=arrayChange($info,'warehouse_id');
            if(arr_contain($user_auth['warehouse'],$check_arr)){
                $auth_info=true;
            }else{
                $auth_info=false;
            }
        }else{
            $auth_info=true;
        }
        $this->assign('auth_info',$auth_info);
        
        $this->assign('class',resaleclass::get(['id'=>$id]));
        $this->assign('info',$info);
        //通用
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch('resale');
    }
    //销货退货单报表
    public function resale_form(){
        $this->assign('warehouse',warehouse::all());
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //销货退货单打印
    public function resale_print(){
        $this->assign('sys',sys::where(['id'=>2])->find());
        $this->assign('printcode',printcode::where(['name'=>'resale'])->find());
        $this->assign('class',resaleclass::where(['id'=>input('get.id')])->find());
        $this->assign('info',resaleinfo::where(['pid'=>input('get.id')])->select());
        return $this->fetch();
    }
    //销货退货单对账单
    public function resale_bill(){
        $this->assign('user',user::all());
        $this->assign('account',account::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //调拨单
    public function allocation(){
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //调拨单详情
    public function allocation_info(){
        $id=input('get.id');
        $tmp_info=allocationinfo::where(['pid'=>$id])->select()->toArray();
        $info=[];
        //改造数组
        foreach ($tmp_info as $vo) {
            $arr=[];
            $arr['set_id']=$vo['room']['ape'];
            $arr['goods']=$vo['goods'];
            $arr['warehouse_id']=$vo['warehouse'];
            $arr['towarehouse_id']=$vo['towarehouse']['ape'];
            $arr['serial_info']=implode(',',arrayChange(serial::where(['room'=>$arr['set_id'],'type'=>0])->field('code')->select()->toArray(),'code'));
            $arr['name']=$vo['room']['info']['goods']['info']['name'];
            $arr['attr']=$vo['room']['info']['attr']['name'];
            $arr['warehouse_id']=$vo['room']['info']['warehouse']['ape'];
            $arr['warehouse']=$vo['room']['info']['warehouse']['info']['name'];
            $arr['stock']=$vo['room']['info']['nums'];
            $arr['brand']=$vo['room']['info']['goods']['info']['brand']['info']['name'];
            $arr['number']=$vo['room']['info']['goods']['info']['number'];
            $arr['class']=$vo['room']['info']['goods']['info']['class']['info']['name'];
            $arr['spec']=$vo['room']['info']['goods']['info']['spec'];
            $arr['code']=$vo['room']['info']['goods']['info']['code'];
            $arr['unit']=$vo['room']['info']['goods']['info']['unit']['info']['name'];
            $arr['stocktip']=$vo['room']['info']['goods']['info']['stocktip'];
            $arr['location']=$vo['room']['info']['goods']['info']['location'];
            $arr['integral']=$vo['room']['info']['goods']['info']['integral'];
            $arr['batch']=$vo['room']['info']['batch'];
            $arr['serial']=$vo['serial'];
            $arr['nums']=$vo['nums'];
            $arr['towarehouse']=$vo['towarehouse']['info']['name'];
            $arr['data']=$vo['data'];
            array_push($info,$arr);
        }
        //数据鉴权
        $user_auth=json_decode(user_info('auth'),true);
        if(!empty($user_auth) && !empty($user_auth['warehouse'])){
            //需要鉴权
            $check_arr=arrayChange($info,'warehouse_id');
            if(arr_contain($user_auth['warehouse'],$check_arr)){
                $auth_info=true;
            }else{
                $auth_info=false;
            }
        }else{
            $auth_info=true;
        }
        $this->assign('auth_info',$auth_info);
        $this->assign('class',allocationclass::get(['id'=>$id]));
        $this->assign('info',$info);
        //通用
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch('allocation');
    }
    //调拨单详情货单报表
    public function allocation_form(){
        $this->assign('warehouse',warehouse::all());
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //调拨单打印
    public function allocation_print(){
        $this->assign('sys',sys::where(['id'=>2])->find());
        $this->assign('printcode',printcode::where(['name'=>'allocation'])->find());
        $this->assign('class',allocationclass::where(['id'=>input('get.id')])->find());
        $this->assign('info',allocationinfo::where(['pid'=>input('get.id')])->select());
        return $this->fetch();
    }
    //其他入库单
    public function otpurchase(){
        if(!empty(input('get.checkinfo'))){
            $tmp_base64=base64_decode(input('get.checkinfo'));
            $tmp_arr=json_decode($tmp_base64,true);
            $info=[];
            foreach ($tmp_arr as $vo) {
                $arr=[];
                $tmp_goods=goods::get(['id'=>$vo['goods_id']]);
                
                $arr['id']=$vo['goods_id'];
                $arr['serialtype']=$tmp_goods['serialtype'];
                $arr['name']=$tmp_goods['name'];
                $arr['attr']=attr::where(['pid'=>$vo['goods_id'],'enable'=>1])->field('ape,buy,sell')->select()->toArray();
                if(!empty($vo['attr'])){
                    $arr['attr_ape']=$vo['attr'];
                    $arr['attr_name']=attr_name($vo['attr']);
                }
                $tmp_warehouse=warehouse::get(['id'=>$vo['warehouse_id']]);
                $warehouseinfo['info']['name']=$tmp_warehouse['name'];
                $warehouseinfo['ape']=$vo['warehouse_id'];
                $arr['warehouse']=$warehouseinfo;
                $arr['brand']=$tmp_goods['brand'];
                $arr['number']=$tmp_goods['number'];
                $arr['class']=$tmp_goods['class'];
                $arr['spec']=$tmp_goods['spec'];
                $arr['code']=$tmp_goods['code'];
                $arr['unit']=$tmp_goods['unit'];
                $arr['stocktip']=$tmp_goods['stocktip'];
                $arr['location']=$tmp_goods['location'];
                $arr['integral']=$tmp_goods['integral'];
                $arr['serial']='';
                $arr['nums']=$vo['nums'];
                $arr['batch']=$vo['batch'];
                $arr['data']='';
                array_push($info,$arr);
            }
            $this->assign('checkinfo',$info);
        }
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //其他入库单详情
    public function otpurchase_info(){
        $id=input('get.id');
        $tmp_info=otpurchaseinfo::where(['pid'=>$id])->select()->toArray();
        $info=[];
        //改造数组
        foreach ($tmp_info as $vo) {
            $arr=[];
            $arr['id']=$vo['goods']['ape'];
            $arr['serialtype']=$vo['goods']['info']['serialtype'];
            $arr['name']=$vo['goods']['info']['name'];
            $arr['attr']=attr::where(['pid'=>$vo['goods']['ape'],'enable'=>1])->field('ape,buy,sell')->select()->toArray();
            if(!empty($vo['attr']['ape'])){
                $arr['attr_ape']=$vo['attr']['ape'];
                $arr['attr_name']=attr_name($vo['attr']['ape']);
            }elseif(!empty($arr['attr']) && empty($vo['attr']['ape'])){
                $arr['attr_ape']='0';
                $arr['attr_name']='未选择';
            }
            $arr['warehouse_id']=$vo['warehouse']['ape'];
            $arr['warehouse']=$vo['warehouse'];
            $arr['brand']=$vo['goods']['info']['brand'];
            $arr['number']=$vo['goods']['info']['number'];
            $arr['class']=$vo['goods']['info']['class'];
            $arr['spec']=$vo['goods']['info']['spec'];
            $arr['code']=$vo['goods']['info']['code'];
            $arr['unit']=$vo['goods']['info']['unit'];
            $arr['stocktip']=$vo['goods']['info']['stocktip'];
            $arr['location']=$vo['goods']['info']['location'];
            $arr['integral']=$vo['goods']['info']['integral'];
            $arr['serial']=$vo['serial'];
            $arr['nums']=$vo['nums'];
            $arr['batch']=$vo['batch'];
            $arr['data']=$vo['data'];
            array_push($info,$arr);
        }
        //数据鉴权
        $user_auth=json_decode(user_info('auth'),true);
        if(!empty($user_auth) && !empty($user_auth['warehouse'])){
            //需要鉴权
            $check_arr=arrayChange($info,'warehouse_id');
            if(arr_contain($user_auth['warehouse'],$check_arr)){
                $auth_info=true;
            }else{
                $auth_info=false;
            }
        }else{
            $auth_info=true;
        }
        $this->assign('auth_info',$auth_info);
        $this->assign('class',otpurchaseclass::get(['id'=>$id]));
        $this->assign('info',$info);
        //通用
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch('otpurchase');
    }
    //其他入库单详情货单报表
    public function otpurchase_form(){
        $this->assign('warehouse',warehouse::all());
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //其他入库单打印
    public function otpurchase_print(){
        $this->assign('sys',sys::where(['id'=>2])->find());
        $this->assign('printcode',printcode::where(['name'=>'otpurchase'])->find());
        $this->assign('class',otpurchaseclass::where(['id'=>input('get.id')])->find());
        $this->assign('info',otpurchaseinfo::where(['pid'=>input('get.id')])->select());
        return $this->fetch();
    }
    //其他出库单
    public function otsale(){
        if(!empty(input('get.checkinfo'))){
            $tmp_base64=base64_decode(input('get.checkinfo'));
            $tmp_arr=json_decode($tmp_base64,true);
            $info=[];
            foreach ($tmp_arr as $vo) {
                $arr=[];
                $tmp_room=room::get(['id'=>$vo['room_id']]);
                $arr['set_id']=$vo['room_id'];
                $arr['goods']=$tmp_room['goods']['ape'];
                $arr['serial_info']=implode(',',arrayChange(serial::where(['room'=>$arr['set_id'],'type'=>0])->field('code')->select()->toArray(),'code'));
                $arr['name']=$tmp_room['goods']['info']['name'];
                $arr['attr']=$tmp_room['attr']['name'];
                $arr['warehouse']=$tmp_room['warehouse']['info']['name'];
                $arr['stock']=$tmp_room['nums'];
                $arr['brand']=$tmp_room['goods']['info']['brand']['info']['name'];
                $arr['number']=$tmp_room['goods']['info']['number'];
                $arr['class']=$tmp_room['goods']['info']['class']['info']['name'];
                $arr['spec']=$tmp_room['goods']['info']['spec'];
                $arr['code']=$tmp_room['goods']['info']['code'];
                $arr['unit']=$tmp_room['goods']['info']['unit']['info']['name'];
                $arr['stocktip']=$tmp_room['goods']['info']['stocktip'];
                $arr['location']=$tmp_room['goods']['info']['location'];
                $arr['integral']=$tmp_room['goods']['info']['integral'];
                $arr['batch']=$tmp_room['batch'];
                $arr['serial']='';
                $arr['nums']=abs($vo['nums']);
                $arr['data']='';
                array_push($info,$arr);
            }
            $this->assign('checkinfo',$info);
        }
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //其他出库单详情
    public function otsale_info(){
        $id=input('get.id');
        $tmp_info=otsaleinfo::where(['pid'=>$id])->select()->toArray();
        $info=[];
        //改造数组
        foreach ($tmp_info as $vo) {
            $arr=[];
            $arr['set_id']=$vo['room']['ape'];
            $arr['goods']=$vo['goods'];
            $arr['serial_info']=implode(',',arrayChange(serial::where(['room'=>$arr['set_id'],'type'=>0])->field('code')->select()->toArray(),'code'));
            $arr['name']=$vo['room']['info']['goods']['info']['name'];
            $arr['attr']=$vo['room']['info']['attr']['name'];
            $arr['warehouse_id']=$vo['room']['info']['warehouse']['ape'];
            $arr['warehouse']=$vo['room']['info']['warehouse']['info']['name'];
            $arr['stock']=$vo['room']['info']['nums'];
            $arr['brand']=$vo['room']['info']['goods']['info']['brand']['info']['name'];
            $arr['number']=$vo['room']['info']['goods']['info']['number'];
            $arr['class']=$vo['room']['info']['goods']['info']['class']['info']['name'];
            $arr['spec']=$vo['room']['info']['goods']['info']['spec'];
            $arr['code']=$vo['room']['info']['goods']['info']['code'];
            $arr['unit']=$vo['room']['info']['goods']['info']['unit']['info']['name'];
            $arr['stocktip']=$vo['room']['info']['goods']['info']['stocktip'];
            $arr['location']=$vo['room']['info']['goods']['info']['location'];
            $arr['integral']=$vo['room']['info']['goods']['info']['integral'];
            $arr['batch']=$vo['room']['info']['batch'];
            $arr['serial']=$vo['serial'];
            $arr['nums']=$vo['nums'];
            $arr['data']=$vo['data'];
            array_push($info,$arr);
        }
        //数据鉴权
        $user_auth=json_decode(user_info('auth'),true);
        if(!empty($user_auth) && !empty($user_auth['warehouse'])){
            //需要鉴权
            $check_arr=arrayChange($info,'warehouse_id');
            if(arr_contain($user_auth['warehouse'],$check_arr)){
                $auth_info=true;
            }else{
                $auth_info=false;
            }
        }else{
            $auth_info=true;
        }
        $this->assign('auth_info',$auth_info);
        
        $this->assign('class',otsaleclass::get(['id'=>$id]));
        $this->assign('info',$info);
        //通用
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch('otsale');
    }
    //其他出库单详情货单报表
    public function otsale_form(){
        $this->assign('warehouse',warehouse::all());
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //其他出库单打印
    public function otsale_print(){
        $this->assign('sys',sys::where(['id'=>2])->find());
        $this->assign('printcode',printcode::where(['name'=>'otsale'])->find());
        $this->assign('class',otsaleclass::where(['id'=>input('get.id')])->find());
        $this->assign('info',otsaleinfo::where(['pid'=>input('get.id')])->select());
        return $this->fetch();
    }
    //收款单
    public function gather(){
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //收款单详情
    public function gather_info(){
        $id=input('get.id');
        $tmp_info=gatherinfo::where(['pid'=>$id])->select()->toArray();
        $info=[];
        //改造数组
        foreach ($tmp_info as $vo) {
            $arr=[];
            $arr['set_id']=$vo['account']['ape'];
            $arr['account_id']=$vo['account']['ape'];
            $arr['account']=$vo['account']['info']['name'];
            $arr['total']=$vo['total'];
            $arr['data']=$vo['data'];
            array_push($info,$arr);
        }
        //数据鉴权
        $user_auth=json_decode(user_info('auth'),true);
        if(!empty($user_auth) && !empty($user_auth['account'])){
            //需要鉴权
            $check_arr=arrayChange($info,'account_id');
            if(arr_contain($user_auth['account'],$check_arr)){
                $auth_info=true;
            }else{
                $auth_info=false;
            }
        }else{
            $auth_info=true;
        }
        $this->assign('auth_info',$auth_info);
        $this->assign('class',gatherclass::get(['id'=>$id]));
        $this->assign('info',$info);
        //通用
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch('gather');
    }
    //库存盘点
    public function room_check(){
        $this->assign('goodsclass',goodsclass::get(input('get.class')));//分类搜索条件赋值
        $this->assign('warehouse',warehouse::all());
        $this->assign('unit',unit::all());
        $this->assign('brand',brand::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //收款单详情报表
    public function gather_form(){
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //收款单打印
    public function gather_print(){
        $this->assign('sys',sys::where(['id'=>2])->find());
        $this->assign('printcode',printcode::where(['name'=>'gather'])->find());
        $this->assign('class',gatherclass::where(['id'=>input('get.id')])->find());
        $this->assign('info',gatherinfo::where(['pid'=>input('get.id')])->select());
        return $this->fetch();
    }
    //付款单
    public function payment(){
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //付款单详情
    public function payment_info(){
        $id=input('get.id');
        $tmp_info=paymentinfo::where(['pid'=>$id])->select()->toArray();
        $info=[];
        //改造数组
        foreach ($tmp_info as $vo) {
            $arr=[];
            $arr['set_id']=$vo['account']['ape'];
            $arr['account_id']=$vo['account']['ape'];
            $arr['account']=$vo['account']['info']['name'];
            $arr['total']=$vo['total'];
            $arr['data']=$vo['data'];
            array_push($info,$arr);
        }
        //数据鉴权
        $user_auth=json_decode(user_info('auth'),true);
        if(!empty($user_auth) && !empty($user_auth['account'])){
            //需要鉴权
            $check_arr=arrayChange($info,'account_id');
            if(arr_contain($user_auth['account'],$check_arr)){
                $auth_info=true;
            }else{
                $auth_info=false;
            }
        }else{
            $auth_info=true;
        }
        $this->assign('auth_info',$auth_info);
        $this->assign('class',paymentclass::get(['id'=>$id]));
        $this->assign('info',$info);
        //通用
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch('payment');
    }
    //付款单详情报表
    public function payment_form(){
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //付款单打印
    public function payment_print(){
        $this->assign('sys',sys::where(['id'=>2])->find());
        $this->assign('printcode',printcode::where(['name'=>'payment'])->find());
        $this->assign('class',paymentclass::where(['id'=>input('get.id')])->find());
        $this->assign('info',paymentinfo::where(['pid'=>input('get.id')])->select());
        return $this->fetch();
    }
    //其他收入单
    public function otgather(){
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //其他收入单详情
    public function otgather_info(){
        $id=input('get.id');
        $tmp_info=otgatherinfo::where(['pid'=>$id])->select()->toArray();
        $info=[];
        //改造数组
        foreach ($tmp_info as $vo) {
            $arr=[];
            $arr['set_id']=$vo['account']['ape'];
            $arr['account_id']=$vo['account']['ape'];
            $arr['account']=$vo['account']['info']['name'];
            $arr['total']=$vo['total'];
            $arr['data']=$vo['data'];
            array_push($info,$arr);
        }
        //数据鉴权
        $user_auth=json_decode(user_info('auth'),true);
        if(!empty($user_auth) && !empty($user_auth['account'])){
            //需要鉴权
            $check_arr=arrayChange($info,'account_id');
            if(arr_contain($user_auth['account'],$check_arr)){
                $auth_info=true;
            }else{
                $auth_info=false;
            }
        }else{
            $auth_info=true;
        }
        $this->assign('auth_info',$auth_info);
        $this->assign('class',otgatherclass::get(['id'=>$id]));
        $this->assign('info',$info);
        //通用
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch('otgather');
    }
    //其他收入单详情报表
    public function otgather_form(){
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //其他收入单打印
    public function otgather_print(){
        $this->assign('sys',sys::where(['id'=>2])->find());
        $this->assign('printcode',printcode::where(['name'=>'otgather'])->find());
        $this->assign('class',otgatherclass::where(['id'=>input('get.id')])->find());
        $this->assign('info',otgatherinfo::where(['pid'=>input('get.id')])->select());
        return $this->fetch();
    }
    //其他支出单
    public function otpayment(){
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //其他支出单详情报表
    public function otpayment_form(){
        
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //其他支出单详情
    public function otpayment_info(){
        $id=input('get.id');
        $tmp_info=otpaymentinfo::where(['pid'=>$id])->select()->toArray();
        $info=[];
        //改造数组
        foreach ($tmp_info as $vo) {
            $arr=[];
            $arr['set_id']=$vo['account']['ape'];
            $arr['account_id']=$vo['account']['ape'];
            $arr['account']=$vo['account']['info']['name'];
            $arr['total']=$vo['total'];
            $arr['data']=$vo['data'];
            array_push($info,$arr);
        }
        //数据鉴权
        $user_auth=json_decode(user_info('auth'),true);
        if(!empty($user_auth) && !empty($user_auth['account'])){
            //需要鉴权
            $check_arr=arrayChange($info,'account_id');
            if(arr_contain($user_auth['account'],$check_arr)){
                $auth_info=true;
            }else{
                $auth_info=false;
            }
        }else{
            $auth_info=true;
        }
        $this->assign('auth_info',$auth_info);
        $this->assign('class',otpaymentclass::get(['id'=>$id]));
        $this->assign('info',$info);
        //通用
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch('otpayment');
    }
    //其他支出单打印
    public function otpayment_print(){
        $this->assign('sys',sys::where(['id'=>2])->find());
        $this->assign('printcode',printcode::where(['name'=>'otpayment'])->find());
        $this->assign('class',otpaymentclass::where(['id'=>input('get.id')])->find());
        $this->assign('info',otpaymentinfo::where(['pid'=>input('get.id')])->select());
        return $this->fetch();
    }
    //零售单
    public function cashier(){
        $this->assign('sys',sys::all());
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //零售单详情
    public function cashier_info(){
        $id=input('get.id');
        $tmp_info=cashierinfo::where(['pid'=>$id])->select()->toArray();
        $info=[];
        //改造数组
        foreach ($tmp_info as $vo) {
            $arr=[];
            $arr['set_id']=$vo['room']['ape'];
            $arr['goods']=$vo['goods'];
            $arr['serial_info']=implode(',',arrayChange(serial::where(['room'=>$arr['set_id'],'type'=>0])->field('code')->select()->toArray(),'code'));
            $arr['name']=$vo['room']['info']['goods']['info']['name'];
            $arr['attr']=$vo['room']['info']['attr']['name'];
            $arr['warehouse_id']=$vo['room']['info']['warehouse']['ape'];
            $arr['warehouse']=$vo['room']['info']['warehouse']['info']['name'];
            $arr['stock']=$vo['room']['info']['nums'];
            $arr['brand']=$vo['room']['info']['goods']['info']['brand']['info']['name'];
            $arr['number']=$vo['room']['info']['goods']['info']['number'];
            $arr['class']=$vo['room']['info']['goods']['info']['class']['info']['name'];
            $arr['spec']=$vo['room']['info']['goods']['info']['spec'];
            $arr['code']=$vo['room']['info']['goods']['info']['code'];
            $arr['unit']=$vo['room']['info']['goods']['info']['unit']['info']['name'];
            $arr['stocktip']=$vo['room']['info']['goods']['info']['stocktip'];
            $arr['location']=$vo['room']['info']['goods']['info']['location'];
            $arr['integral']=$vo['room']['info']['goods']['info']['integral'];
            $arr['batch']=$vo['room']['info']['batch'];
            $arr['serial']=$vo['serial'];
            $arr['nums']=$vo['nums'];
            $arr['price']=$vo['price'];
            $arr['discount']=$vo['discount'];
            $arr['total']=$vo['total'];
            $arr['data']=$vo['data'];
            array_push($info,$arr);
        }
        //数据鉴权
        $user_auth=json_decode(user_info('auth'),true);
        if(!empty($user_auth) && !empty($user_auth['warehouse'])){
            //需要鉴权
            $check_arr=arrayChange($info,'warehouse_id');
            if(arr_contain($user_auth['warehouse'],$check_arr)){
                $auth_info=true;
            }else{
                $auth_info=false;
            }
        }else{
            $auth_info=true;
        }
        $this->assign('auth_info',$auth_info);
        $this->assign('account',account::all());
        $this->assign('class',cashierclass::get(['id'=>$id]));
        $this->assign('info',$info);
        //通用
        $this->assign('sys',sys::all());
        $this->assign('user',user::all());
        return $this->fetch('cashierinfo');
    }
    //零售单详情货单报表
    public function cashier_form(){
        $this->assign('warehouse',warehouse::all());
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //零售单打印
    public function cashier_print(){
        $this->assign('sys',sys::where(['id'=>2])->find());
        $this->assign('printcode',printcode::where(['name'=>'cashier'])->find());
        $this->assign('class',cashierclass::where(['id'=>input('get.id')])->find());
        $this->assign('info',cashierinfo::where(['pid'=>input('get.id')])->select());
        return $this->fetch();
    }
    //零售单小票打印
    public function cashier_min_print(){
        $this->assign('sys',sys::where(['id'=>2])->find());
        $this->assign('printcode',printcode::where(['name'=>'cashiermin'])->find());
        $this->assign('class',cashierclass::where(['id'=>input('get.id')])->find());
        $this->assign('info',cashierinfo::where(['pid'=>input('get.id')])->select());
        return $this->fetch();
    }
    //零售退货单
    public function recashier(){
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //零售退货详情
    public function recashier_info(){
        $id=input('get.id');
        $tmp_info=recashierinfo::where(['pid'=>$id])->select()->toArray();
        $info=[];
        //改造数组
        foreach ($tmp_info as $vo) {
            $arr=[];
            $arr['set_id']=$vo['room']['ape'];
            $arr['goods']=$vo['goods'];
            $arr['serial_info']=implode(',',arrayChange(serial::where(['room'=>$arr['set_id'],'type'=>1])->field('code')->select()->toArray(),'code'));
            $arr['name']=$vo['room']['info']['goods']['info']['name'];
            $arr['attr']=$vo['room']['info']['attr']['name'];
            $arr['warehouse_id']=$vo['room']['info']['warehouse']['ape'];
            $arr['warehouse']=$vo['room']['info']['warehouse']['info']['name'];
            $arr['stock']=$vo['room']['info']['nums'];
            $arr['brand']=$vo['room']['info']['goods']['info']['brand']['info']['name'];
            $arr['number']=$vo['room']['info']['goods']['info']['number'];
            $arr['class']=$vo['room']['info']['goods']['info']['class']['info']['name'];
            $arr['spec']=$vo['room']['info']['goods']['info']['spec'];
            $arr['code']=$vo['room']['info']['goods']['info']['code'];
            $arr['unit']=$vo['room']['info']['goods']['info']['unit']['info']['name'];
            $arr['stocktip']=$vo['room']['info']['goods']['info']['stocktip'];
            $arr['location']=$vo['room']['info']['goods']['info']['location'];
            $arr['integral']=$vo['room']['info']['goods']['info']['integral'];
            $arr['batch']=$vo['room']['info']['batch'];
            $arr['serial']=$vo['serial'];
            $arr['nums']=$vo['nums'];
            $arr['price']=$vo['price'];
            $arr['total']=$vo['total'];
            $arr['data']=$vo['data'];
            array_push($info,$arr);
        }
        //数据鉴权
        $user_auth=json_decode(user_info('auth'),true);
        if(!empty($user_auth) && !empty($user_auth['warehouse'])){
            //需要鉴权
            $check_arr=arrayChange($info,'warehouse_id');
            if(arr_contain($user_auth['warehouse'],$check_arr)){
                $auth_info=true;
            }else{
                $auth_info=false;
            }
        }else{
            $auth_info=true;
        }
        $this->assign('auth_info',$auth_info);
        
        $this->assign('class',recashierclass::get(['id'=>$id]));
        $this->assign('info',$info);
        //通用
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch('recashier');
    }
    //零售退货单报表
    public function recashier_form(){
        $this->assign('warehouse',warehouse::all());
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //零售退货单打印
    public function recashier_print(){
        $this->assign('sys',sys::where(['id'=>2])->find());
        $this->assign('printcode',printcode::where(['name'=>'recashier'])->find());
        $this->assign('class',recashierclass::where(['id'=>input('get.id')])->find());
        $this->assign('info',recashierinfo::where(['pid'=>input('get.id')])->select());
        return $this->fetch();
    }    
    //数据授权
    public function auth(){
        return $this->fetch();
    }
    //权限设置
    public function root(){
        $this->assign('sys',sys::where(['id'=>4])->find());
        return $this->fetch();
    }
    //常用功能
    public function often(){
        $this->assign('info',often::all()->toArray());
        $this->assign('sys',sys::where(['id'=>4])->find());
        return $this->fetch();
    }
    //库存预警
    public function room_warning(){
        $this->assign('goodsclass',goodsclass::get(input('get.class')));//分类搜索条件赋值
        $this->assign('warehouse',warehouse::all());
        $this->assign('unit',unit::all());
        $this->assign('brand',brand::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //往来单位欠款表
    public function arrears_form(){
        return $this->fetch();
    }
    //销售利润表
    public function profit_form(){
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //升级
    public function up_sys(){
        return $this->fetch();
    }
    //采购订单
    public function opurchase(){
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //采购订单报表
    public function opurchase_form(){
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //采购订单详情
    public function opurchase_info(){
        $id=input('get.id');
        $tmp_info=opurchaseinfo::where(['pid'=>$id])->select()->toArray();
        $info=[];
        //改造数组
        foreach ($tmp_info as $vo) {
            $arr=[];
            $arr['id']=$vo['goods']['ape'];
            $arr['name']=$vo['goods']['info']['name'];
            $arr['attr']=attr::where(['pid'=>$vo['goods']['ape'],'enable'=>1])->field('ape,buy,sell')->select();
            if(!empty($vo['attr']['ape'])){
                $arr['attr_ape']=$vo['attr']['ape'];
                $arr['attr_name']=attr_name($vo['attr']['ape']);
            }elseif(!empty($arr['attr']) && empty($vo['attr']['ape'])){
                $arr['attr_ape']='0';
                $arr['attr_name']='未选择';
            }
            $arr['brand']=$vo['goods']['info']['brand'];
            $arr['number']=$vo['goods']['info']['number'];
            $arr['class']=$vo['goods']['info']['class'];
            $arr['spec']=$vo['goods']['info']['spec'];
            $arr['code']=$vo['goods']['info']['code'];
            $arr['unit']=$vo['goods']['info']['unit'];
            $arr['stocktip']=$vo['goods']['info']['stocktip'];
            $arr['location']=$vo['goods']['info']['location'];
            $arr['integral']=$vo['goods']['info']['integral'];
            $arr['nums']=$vo['nums'];
            $arr['data']=$vo['data'];
            array_push($info,$arr);
        }
        $this->assign('auth_info',true);
        $this->assign('class',opurchaseclass::get(['id'=>$id]));
        $this->assign('info',$info);
        //通用
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch('opurchase');
    }
    //采购订单打印
    public function opurchase_print(){
        $this->assign('sys',sys::where(['id'=>2])->find());
        $this->assign('printcode',printcode::where(['name'=>'opurchase'])->find());
        $this->assign('class',opurchaseclass::where(['id'=>input('get.id')])->find());
        $this->assign('info',opurchaseinfo::where(['pid'=>input('get.id')])->select());
        return $this->fetch();
    }
    //采购入库单-采购转入库
    public function orpurchase(){
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //采购入库详情单-报表入库
    public function orpurchase_info(){
        $id=input('get.id');
        $tmp_info=opurchaseinfo::where(['pid'=>$id])->select()->toArray();
        $info=[];
        //改造数组
        foreach ($tmp_info as $vo) {
            $arr=[];
            $arr['oid']=$vo['id'];
            $arr['id']=$vo['goods']['ape'];
            $arr['serialtype']=$vo['goods']['info']['serialtype'];
            $arr['name']=$vo['goods']['info']['name'];
            //判断是否存在辅助属性 
            if(empty($vo['attr']['ape'])){
                //不存在,取默认购货价格
                $arr['price']=$vo['goods']['info']['buy'];
                $arr['attr_type']=0;
            }else{
                //存在,取辅助属性购货价格
                $attr_info=attr::where(['pid'=>$vo['goods']['ape'],'ape'=>$vo['attr']['ape']])->find();
                if(empty($attr_info)){
                    $arr['price']=$vo['goods']['info']['buy'];//如果辅助属性被删除或更改,则取默认价格
                }else{
                    $arr['price']=$attr_info['buy'];
                }
                $arr['attr_ape']=$vo['attr']['ape'];//辅助属性标识
                $arr['attr_name']=attr_name($vo['attr']['ape']);//辅助属性名称
                $arr['attr_type']=1;
            }
            //判断是否存在默认仓库
            if(empty($vo['goods']['info']['warehouse']['ape'])){
                $arr['warehouse_type']=0;
            }else{
                $arr['warehouse_id']=$vo['goods']['info']['warehouse']['ape'];
                $arr['warehouse']=$vo['goods']['info']['warehouse']['info']['name'];
                $arr['warehouse_type']=1;
            }
            $arr['brand']=$vo['goods']['info']['brand'];
            $arr['number']=$vo['goods']['info']['number'];
            $arr['class']=$vo['goods']['info']['class'];
            $arr['spec']=$vo['goods']['info']['spec'];
            $arr['code']=$vo['goods']['info']['code'];
            $arr['unit']=$vo['goods']['info']['unit'];
            $arr['stocktip']=$vo['goods']['info']['stocktip'];
            $arr['location']=$vo['goods']['info']['location'];
            $arr['integral']=$vo['goods']['info']['integral'];
            $arr['nums']=$vo['nums'];//总数量
            $arr['apenums']=$vo['nums']-$vo['readynums'];//剩余数量
            $arr['data']=$vo['data'];
            array_push($info,$arr);
        }
        $this->assign('info',$info);
        //通用
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch('orpurchase_info');
    }
    //采购入库单详情打印
    public function orpurchase_print(){
        $this->assign('sys',sys::where(['id'=>2])->find());
        $this->assign('printcode',printcode::where(['name'=>'orpurchase'])->find());
        $this->assign('class',opurchaseclass::where(['id'=>input('get.id')])->find());
        $this->assign('info',opurchaseinfo::where(['pid'=>input('get.id')])->select());
        return $this->fetch();
    }
    //购货单报表
    public function rpurchase_form(){
        $this->assign('warehouse',warehouse::all());
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //购货入库单打印
    public function rpurchase_print(){
        $this->assign('sys',sys::where(['id'=>2])->find());
        $this->assign('printcode',printcode::where(['name'=>'rpurchase'])->find());
        $this->assign('class',rpurchaseclass::where(['id'=>input('get.id')])->find());
        $this->assign('info',rpurchaseinfo::where(['pid'=>input('get.id')])->select());
        return $this->fetch();
    }
    //采购入库单详情
    public function rpurchase_info(){
        $id=input('get.id');
        $tmp_info=rpurchaseinfo::where(['pid'=>$id])->select()->toArray();
        $info=[];
        //改造数组
        foreach ($tmp_info as $vo) {
            $arr=[];
            $arr['oid']=$vo['oid']['ape'];
            $arr['id']=$vo['goods']['ape'];
            $arr['serialtype']=$vo['goods']['info']['serialtype'];
            $arr['name']=$vo['goods']['info']['name'];
            $arr['attr']=attr::where(['pid'=>$vo['goods']['ape'],'enable'=>1])->field('ape,buy,sell')->select();
            //判断是否存在辅助属性 
            if(empty($vo['attr']['ape'])){
                //不存在,取默认购货价格
                $arr['price']=$vo['goods']['info']['buy'];
                $arr['attr_type']=0;
            }else{
                //存在,取辅助属性购货价格
                $attr_info=attr::where(['pid'=>$vo['goods']['ape'],'ape'=>$vo['attr']['ape']])->find();
                if(empty($attr_info)){
                    $arr['price']=$vo['goods']['info']['buy'];//如果辅助属性被删除或更改,则取默认价格
                }else{
                    $arr['price']=$attr_info['buy'];
                }
                $arr['attr_ape']=$vo['attr']['ape'];//辅助属性标识
                $arr['attr_name']=attr_name($vo['attr']['ape']);//辅助属性名称
                $arr['attr_type']=1;
            }
            $arr['warehouse_id']=$vo['warehouse']['ape'];
            $arr['warehouse']=$vo['warehouse'];
            $arr['brand']=$vo['goods']['info']['brand'];
            $arr['number']=$vo['goods']['info']['number'];
            $arr['class']=$vo['goods']['info']['class'];
            $arr['spec']=$vo['goods']['info']['spec'];
            $arr['code']=$vo['goods']['info']['code'];
            $arr['unit']=$vo['goods']['info']['unit'];
            $arr['stocktip']=$vo['goods']['info']['stocktip'];
            $arr['location']=$vo['goods']['info']['location'];
            $arr['integral']=$vo['goods']['info']['integral'];
            $arr['serial']=$vo['serial'];
            $arr['nums']=$vo['nums'];
            $arr['price']=$vo['price'];
            $arr['total']=$vo['total'];
            $arr['batch']=$vo['batch'];
            $arr['data']=$vo['data'];
            array_push($info,$arr);
        }
        //数据鉴权
        $user_auth=json_decode(user_info('auth'),true);
        if(!empty($user_auth) && !empty($user_auth['warehouse'])){
            //需要鉴权
            $check_arr=arrayChange($info,'warehouse_id');
            if(arr_contain($user_auth['warehouse'],$check_arr)){
                $auth_info=true;
            }else{
                $auth_info=false;
            }
        }else{
            $auth_info=true;
        }
        $this->assign('auth_info',$auth_info);
        $this->assign('class',rpurchaseclass::get(['id'=>$id]));
        $this->assign('info',$info);
        //通用
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch('rpurchase');
    }
    //采购入库对账单
    public function rpurchase_bill(){
        $this->assign('user',user::all());
        $this->assign('account',account::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //服务管理
    public function item(){
        return $this->fetch();
    }
    //服务订单
    public function itemorder(){
        $this->assign('user',user::all());
        $this->assign('account',account::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //服务订单报表
    public function itemorder_form(){
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //服务订单打印
    public function itemorder_print(){
        $this->assign('sys',sys::where(['id'=>2])->find());
        $this->assign('printcode',printcode::where(['name'=>'itemorder'])->find());
        $this->assign('class',itemorderclass::where(['id'=>input('get.id')])->find());
        $this->assign('info',itemorderinfo::where(['pid'=>input('get.id')])->select());
        return $this->fetch();
    }
    //服务订单详情
    public function itemorder_info(){
        $id=input('get.id');
        $tmp_info=itemorderinfo::where(['pid'=>$id])->select()->toArray();
        $info=[];
        //改造数组
        foreach ($tmp_info as $vo) {
            $arr=[];
            $arr['id']=$vo['item']['ape'];
            $arr['name']=$vo['item']['info']['name'];
            $arr['nums']=$vo['nums'];
            $arr['price']=$vo['price'];
            $arr['total']=$vo['total'];
            $arr['data']=$vo['data'];
            array_push($info,$arr);
        }
        $this->assign('class',itemorderclass::get(['id'=>$id]));
        $this->assign('info',$info);
        //通用
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch('itemorder');
    }
    //服务订单对账单
    public function itemorder_bill(){
        $this->assign('user',user::all());
        $this->assign('account',account::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //积分兑换单
    public function exchange(){
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //积分兑换单报表
    public function exchange_form(){
        $this->assign('warehouse',warehouse::all());
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //积分兑换单打印
    public function exchange_print(){
        $this->assign('sys',sys::where(['id'=>2])->find());
        $this->assign('printcode',printcode::where(['name'=>'exchange'])->find());
        $this->assign('class',exchangeclass::where(['id'=>input('get.id')])->find());
        $this->assign('info',exchangeinfo::where(['pid'=>input('get.id')])->select());
        return $this->fetch();
    }
    //积分兑换单详情
    public function exchange_info(){
        $id=input('get.id');
        $tmp_info=exchangeinfo::where(['pid'=>$id])->select()->toArray();
        $info=[];
        //改造数组
        foreach ($tmp_info as $vo) {
            $arr=[];
            $arr['set_id']=$vo['room']['ape'];
            $arr['goods']=$vo['goods'];
            $arr['serial_info']=implode(',',arrayChange(serial::where(['room'=>$arr['set_id'],'type'=>0])->field('code')->select()->toArray(),'code'));
            $arr['name']=$vo['room']['info']['goods']['info']['name'];
            $arr['attr']=$vo['room']['info']['attr']['name'];
            $arr['warehouse_id']=$vo['room']['info']['warehouse']['ape'];
            $arr['warehouse']=$vo['room']['info']['warehouse']['info']['name'];
            $arr['stock']=$vo['room']['info']['nums'];
            $arr['brand']=$vo['room']['info']['goods']['info']['brand']['info']['name'];
            $arr['number']=$vo['room']['info']['goods']['info']['number'];
            $arr['class']=$vo['room']['info']['goods']['info']['class']['info']['name'];
            $arr['spec']=$vo['room']['info']['goods']['info']['spec'];
            $arr['code']=$vo['room']['info']['goods']['info']['code'];
            $arr['unit']=$vo['room']['info']['goods']['info']['unit']['info']['name'];
            $arr['stocktip']=$vo['room']['info']['goods']['info']['stocktip'];
            $arr['location']=$vo['room']['info']['goods']['info']['location'];
            $arr['integral']=$vo['integral'];
            $arr['batch']=$vo['room']['info']['batch'];
            $arr['serial']=$vo['serial'];
            $arr['nums']=$vo['nums'];
            $arr['allintegral']=$vo['allintegral'];
            $arr['data']=$vo['data'];
            array_push($info,$arr);
        }
        
        //数据鉴权
        $user_auth=json_decode(user_info('auth'),true);
        if(!empty($user_auth) && !empty($user_auth['warehouse'])){
            //需要鉴权
            $check_arr=arrayChange($info,'warehouse_id');
            if(arr_contain($user_auth['warehouse'],$check_arr)){
                $auth_info=true;
            }else{
                $auth_info=false;
            }
        }else{
            $auth_info=true;
        }
        $this->assign('auth_info',$auth_info);
        $this->assign('class',exchangeclass::get(['id'=>$id]));
        $this->assign('info',$info);
        //通用
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch('exchange');
    }
    //资金调拨单
    public function eft(){
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //资金调拨单报表
    public function eft_form(){
        $this->assign('account',account::all());
        $this->assign('user',user::all());
        return $this->fetch();
    }
    //资金调拨单打印
    public function eft_print(){
        $this->assign('sys',sys::where(['id'=>2])->find());
        $this->assign('printcode',printcode::where(['name'=>'eft'])->find());
        $this->assign('class',eftclass::where(['id'=>input('get.id')])->find());
        $this->assign('info',eftinfo::where(['pid'=>input('get.id')])->select());
        return $this->fetch();
    }
    //调拨单详情
    public function eft_info(){
        $id=input('get.id');
        $tmp_info=eftinfo::where(['pid'=>$id])->select()->toArray();
        $info=[];
        //改造数组
        foreach ($tmp_info as $vo) {
            $arr=[];
            $arr['account_id']=$vo['account_id']['ape'];
            $arr['toaccount_id']=$vo['toaccount_id']['ape'];
            $arr['account']=$vo['account_id']['info']['name'];
            $arr['toaccount']=$vo['toaccount_id']['info']['name'];
            $arr['money']=$vo['money'];
            $arr['data']=$vo['data'];
            array_push($info,$arr);
        }
        //数据鉴权
        $user_auth=json_decode(user_info('auth'),true);
        if(!empty($user_auth) && !empty($user_auth['account'])){
            //需要鉴权
            $check_arr_one=arrayChange($info,'account_id');
            $check_arr_two=arrayChange($info,'toaccount_id');
            if(arr_contain($user_auth['account'],$check_arr_one) && arr_contain($user_auth['account'],$check_arr_two)){
                $auth_info=true;
            }else{
                $auth_info=false;
            }
        }else{
            $auth_info=true;
        }
        $this->assign('auth_info',$auth_info);
        $this->assign('class',eftclass::get(['id'=>$id]));
        $this->assign('info',$info);
        //通用
        $this->assign('user',user::all());
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch('eft');
    }
    //商品利润表
    public function ape_goods_form(){
        $this->assign('sys',sys::get(['id'=>2]));
        return $this->fetch();
    }
    //数据初始化
    public function summary(){
        return $this->fetch();
    }
    //串码跟踪表
    public function serial_form(){
        return $this->fetch();
    }
    //串码详情表
    public function serialinfo_form(){
        return $this->fetch();
    }
    
}