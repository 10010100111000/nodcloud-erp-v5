<?php
namespace app\index\controller;
use think\Controller;
use	app\index\model\sys;
use	app\index\model\tabinfo;
use	app\index\model\code;
use	app\index\model\account;
use	app\index\model\accountinfo;
use	app\index\model\customer;
use	app\index\model\customerinfo;
use	app\index\model\goods;
use	app\index\model\supplier;
use	app\index\model\purchaseclass;
use	app\index\model\purchaseinfo;
use	app\index\model\room;
use	app\index\model\roominfo;
use	app\index\model\serial;
use	app\index\model\serialinfo;
use	app\index\model\purchasebill;
use	app\index\model\saleclass;
use	app\index\model\saleinfo;
use	app\index\model\repurchaseclass;
use	app\index\model\repurchaseinfo;
use	app\index\model\resaleclass;
use	app\index\model\resaleinfo;
use	app\index\model\allocationclass;
use	app\index\model\allocationinfo;
use	app\index\model\otpurchaseclass;
use	app\index\model\otpurchaseinfo;
use	app\index\model\otsaleclass;
use	app\index\model\otsaleinfo;
use	app\index\model\gatherclass;
use	app\index\model\gatherinfo;
use	app\index\model\paymentclass;
use	app\index\model\paymentinfo;
use	app\index\model\otgatherclass;
use	app\index\model\otgatherinfo;
use	app\index\model\otpaymentclass;
use	app\index\model\otpaymentinfo;
use	app\index\model\cashierclass;
use	app\index\model\cashierinfo;
use	app\index\model\recashierclass;
use	app\index\model\recashierinfo;
use	app\index\model\attr;
use	app\index\model\opurchaseclass;
use	app\index\model\opurchaseinfo;
use	app\index\model\rpurchaseclass;
use	app\index\model\rpurchaseinfo;
use	app\index\model\item;
use	app\index\model\itemorderclass;
use	app\index\model\itemorderinfo;
use app\index\model\exchangeclass;
use app\index\model\exchangeinfo;
use app\index\model\eftclass;
use app\index\model\eftinfo;
use app\index\model\summary;

class Export extends Controller{
    //访问控制
    public function _initialize() {
        if (!checklogin()) {
            echo 'Unauthorized access';
            exit;
        }
    }
    //导出客户积分详情
    public function customer_form(){
        //当前数据
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $sql['time']=[$egt,$elt];
        }
        //判断资金操作
        if(!empty(input('get.set'))){
            $sql['set']=input('get.set')-1;
        }
        //判断单据类型
        if(!empty(input('get.type'))){
            $sql['type']=input('get.type');
        }
        $sql['pid']=input('get.id');
        $class=customer::get(['id'=>$sql['pid']]);
        $tab_data = customerinfo::where($sql)->select()->toArray();
        foreach ($tab_data as $key=>$vo) {
            $tab_data[$key]['type']=$vo['type']['name'];
            $tab_data[$key]['set']=$vo['set']['name'];
        }
        //构造数据
        $tab_title=[
            'time'=>'操作时间',
            'type'=>'单据类型',
            'number'=>'单据编号',
            'set'=>'积分操作',
            'integral'=>'本次积分',
            'data'=>'备注信息'
        ];
        //表格内容
        $arr['shell']='客户积分详情';//Shell名称
        $arr['title']='客户积分详情'.' - '.$class['name'];//标题名称
        $arr['rows']=[
            'title'=>$tab_title,
            'data'=>$tab_data,
        ];
        $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
        $arr['colnums']=count($tab_title);//列数
        ExportExcel('客户积分详情'.' - '.$class['name'],[$arr]);
    }
    //导出账户资金详情
    public function account_form(){
        //当前数据
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $sql['time']=[$egt,$elt];
        }
        //判断用户
        if(!empty(input('get.user'))){
            $sql['user'] = input('get.user');
        }
        //判断资金操作
        if(!empty(input('get.set'))){
            $sql['set']=input('get.set')-1;
        }
        //判断单据类型
        if(!empty(input('get.type'))){
            $sql['type']=input('get.type');
        }
        $sql['pid']=input('get.id');
        $class=account::get(['id'=>$sql['pid']]);
        $tab_data = accountinfo::where($sql)->select()->toArray();
        foreach ($tab_data as $key=>$vo) {
            $tab_data[$key]['type']=$vo['type']['name'];
            $tab_data[$key]['set']=$vo['set']['name'];
            $tab_data[$key]['user']=$vo['user']['info']['name'];
        }
        //构造数据
        $tab_title=[
            'time'=>'操作时间',
            'type'=>'单据类型',
            'number'=>'单据编号',
            'set'=>'资金操作',
            'money'=>'资金数额',
            'user'=>'操作用户'
        ];
        //表格内容
        $arr['shell']='资金账户详情';//Shell名称
        $arr['title']='资金账户详情'.' - '.$class['name'];//标题名称
        $arr['rows']=[
            'title'=>$tab_title,
            'data'=>$tab_data,
        ];
        $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
        $arr['colnums']=count($tab_title);//列数
        ExportExcel('资金账户详情'.' - '.$class['name'],[$arr]);
    }
    //导出供应商信息
    public function supplier(){
        //当前数据
        $sql['name'] = ['like','%'.input('get.name').'%'];
        $sql['number'] = ['like','%'.input('get.number').'%'];
        $sql['contacts'] = ['like','%'.input('get.contacts').'%'];
        $sql['tel'] = ['like','%'.input('get.tel').'%'];
        $sql['data'] = ['like','%'.input('get.data').'%'];
        $tab_data = supplier::where($sql)->select()->toArray();
        foreach ($tab_data as $key=>$vo) {
            $tab_data[$key]['tel']=' '.$vo['tel'];
        }
        //构造数据
        $tab_title=[
            'name'=>'供应商名称',
            'number'=>'编号',
            'contacts'=>'联系人',
            'tel'=>'手机号',
            'add'=>'供应商地址',
            'accountname'=>'开户名',
            'openingbank'=>'开户行',
            'bankaccount'=>'银行账号',
            'data'=>'备注信息'
        ];
        //表格内容
        $arr['shell']='供应商信息';//Shell名称
        $arr['title']='供 应 商 信 息';//标题名称
        $arr['rows']=[
            'title'=>$tab_title,
            'data'=>$tab_data,
        ];
        $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
        $arr['colnums']=count($tab_title);//列数
        ExportExcel('供应商信息',[$arr]);
    }
    //导出商品信息
    public function goods(){
        //当前数据
        $sql['name'] = ['like','%'.input('get.name').'%'];
        $sql['number'] = ['like','%'.input('get.number').'%'];
        $sql['spec'] = ['like','%'.input('get.spec').'%'];
        $sql['location'] = ['like','%'.input('get.location').'%'];
        $sql['stocktip'] = ['like','%'.input('get.stocktip').'%'];
        $sql['integral'] = ['like','%'.input('get.integral').'%'];
        $sql['retail_name'] = ['like','%'.input('get.retail_name').'%'];
        if(!empty(input('get.class'))){
            $sql['class'] = ['in',goodsclass_more_arr(input('get.class')),'OR'];
        }
        //判断默认仓库
        if(!empty(input('get.warehouse'))){
            $sql['warehouse'] = input('get.warehouse');
        }
        //判断默认品牌
        if(!empty(input('get.brand'))){
            $sql['brand'] = input('get.brand');
        }
        //判断商品单位
        if(!empty(input('get.unit'))){
            $sql['unit'] = input('get.unit');
        }
        $sql['code'] = ['like','%'.input('get.code').'%'];
        $tab_data = goods::where($sql)->select()->toArray();
        foreach ($tab_data as $key=>$vo) {
            $tab_data[$key]['brand']=$vo['brand']['info']['name'];
            $tab_data[$key]['class']=$vo['class']['info']['name'];
            $tab_data[$key]['unit']=$vo['unit']['info']['name'];
            $tab_data[$key]['warehouse']=$vo['warehouse']['info']['name'];
            $tab_data[$key]['code']=' '.$vo['code'];
        }
        //构造数据
        $tab_title=[
            'name'=>'商品名称',
            'brand'=>'商品品牌',
            'number'=>'商品编号',
            'class'=>'商品分类',
            'spec'=>'规格型号',
            'code'=>'条形码',
            'warehouse'=>'默认仓库',
            'unit'=>'商品单位',
            'buy'=>'购货价格',
            'sell'=>'销货价格',
            'retail'=>'零售价格',
            'stocktip'=>'库存预警',
            'location'=>'商品货位',
            'integral'=>'赠送积分',
            'data'=>'备注信息'
        ];
        //表格内容
        $arr['shell']='商品信息';//Shell名称
        $arr['title']='商 品 信 息';//标题名称
        $arr['rows']=[
            'title'=>$tab_title,
            'data'=>$tab_data,
        ];
        $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
        $arr['colnums']=count($tab_title);//列数
        ExportExcel('商品信息',[$arr]);
    }
    //商品进出库详情
    public function room_form(){
        $sql['pid']=input('get.id');
        //判断单据类型
        if(!empty(input('get.type'))){
            $sql['type']=input('get.type');
        }
        //按照时间搜索
        $start_time=strtotime(input('get.start_time'));//开始时间
        $end_time=strtotime(input('get.end_time'));//结束时间
        if(!empty($start_time) || !empty($start_time)){
            $in_sql=[];
            $tmp_info=roominfo::where($sql)->select()->toArray();
            foreach ($tmp_info as $tmp_info_vo) {
                $tmp_time=strtotime($tmp_info_vo['class']['info']['time']);
                if(!empty($start_time) && empty($end_time)){
                    //开始时间不为空,结束时间为空
                    if($tmp_time>=$start_time){
                        array_push($in_sql,$tmp_info_vo['id']);
                    }
                }elseif(!empty($end_time) && empty($start_time)){
                    //结束时间不为空,开始时间为空
                    if($tmp_time<=$end_time){
                        array_push($in_sql,$tmp_info_vo['id']);
                    }
                }elseif(!empty($end_time) && !empty($start_time)){
                    //开始时间不为空,结束时间不为空
                    if($tmp_time>=$start_time && $tmp_time<=$end_time){
                        array_push($in_sql,$tmp_info_vo['id']);
                    }
                }
            }
        }
        if(isset($in_sql)){
            $tab_data=roominfo::where(['id'=>['in',$in_sql,'OR']])->select()->toArray();
        }else{
            $tab_data=roominfo::where($sql)->select()->toArray();
        }
        $class=room::get(['id'=>$sql['pid']]);
        foreach ($tab_data as $key=>$vo) {
            $tab_data[$key]['time']=$vo['class']['info']['time'];
            $tab_data[$key]['type']=$vo['type']['name'];
            $tab_data[$key]['number']=$vo['class']['info']['number'];
            $tab_data[$key]['nums']=$vo['type']['trend'].$vo['nums'];
        }
        //构造数据
        $tab_title=[
            'time'=>'单据时间',
            'type'=>'单据类型',
            'number'=>'单据编号',
            'nums'=>'商品数量',
        ];
        //表格内容
        $arr['shell']='商品进出库详情';//Shell名称
        $arr['title']='商品进出库详情'.' - '.$class['goods']['info']['name'];//标题名称
        $arr['rows']=[
            'title'=>$tab_title,
            'data'=>$tab_data,
        ];
        $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
        $arr['colnums']=count($tab_title);//列数
        ExportExcel('商品进出库详情'.' - '.$class['goods']['info']['name'],[$arr]);
    }
    //导出客户信息
    public function customer(){
        //当前数据
        $sql['name'] = ['like','%'.input('get.name').'%'];
        $sql['number'] = ['like','%'.input('get.number').'%'];
        $sql['tel'] = ['like','%'.input('get.tel').'%'];
        $sql['data'] = ['like','%'.input('get.data').'%'];
        $tab_data = customer::where($sql)->select()->toArray();
        foreach ($tab_data as $key=>$vo) {
            $tab_data[$key]['tel']=' '.$vo['tel'];//转字符串
        }
        //构造数据
        $tab_title=[
            'name'=>'客户名称',
            'contacts'=>'联系人',
            'number'=>'客户编号',
            'tel'=>'手机号',
            'birthday'=>'客户生日',
            'add'=>'客户地址',
            'integral'=>'客户积分',
            'accountname'=>'开户名',
            'openingbank'=>'开户行',
            'bankaccount'=>'银行账号',
            'tax'=>'税号',
            'other'=>'社交账号',
            'email'=>'邮箱地址',
            'data'=>'备注信息'
        ];
        //表格内容
        $arr['shell']='客户信息';//Shell名称
        $arr['title']='客 户 信 息';//标题名称
        $arr['rows']=[
            'title'=>$tab_title,
            'data'=>$tab_data,
        ];
        $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
        $arr['colnums']=count($tab_title);//列数
        ExportExcel('客户信息',[$arr]);
    }
    //导出条码信息
    public function code(){
        del_code_tmp();//清除条码临时文件
        //当前数据
        $sql['name'] = ['like','%'.input('get.name').'%'];
        $sql['code'] = ['like','%'.input('get.code').'%'];
        $sql['data'] = ['like','%'.input('get.data').'%'];
        //类型
        if(!empty(input('get.type'))){
            $sql['type']=input('get.type')-1;
        }
        $tab_data = code::where($sql)->select()->toArray();
        foreach ($tab_data as $key=>$vo) {
            $tab_data[$key]['type']=$vo['type']['name'];//恢复内容
            if(empty($vo['type']['ape'])){
                //条形码
                $tab_data[$key]['img']=txm($vo['code'],false).'||img||0';//读取条形码地址
            }else{
                //二维码
                $tab_data[$key]['img']=ewm($vo['code'],false).'||img||1';//读取二维码地址
            }
        }
        //构造数据
        $tab_title=[
            'name'=>'名称',
            'code'=>'条码',
            'img'=>'图像',
            'type'=>'类型',
            'data'=>'备注'
        ];
        //表格内容
        $arr['shell']='条形码';//Shell名称
        $arr['title']='条 形 码';//标题名称
        $arr['rows']=[
            'title'=>$tab_title,
            'data'=>$tab_data,
        ];
        $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
        $arr['colnums']=count($tab_title);//列数
        ExportExcel('条形码',[$arr]);
    }
    //导出购货单信息
    public function purchase(){
        //按照名称搜索
        if(!empty(input('get.goods'))){
            $goods_sql['name|py']=['like','%'.input('get.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $purchaseinfo_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('get.warehouse'))){
            $purchaseinfo_sql['warehouse']=input('get.warehouse');
        }
        
        if(isset($purchaseinfo_sql)){
            $purchaseinfo_arr=arrayChange(purchaseinfo::where($purchaseinfo_sql)->field('pid')->select()->toArray(),'pid');
            $sql['id']=['in',$purchaseinfo_arr,'OR'];
        }else{
            //默认数据鉴权处理
            $user_auth=json_decode(user_info('auth'),true);
            if(!empty($user_auth) && !empty($user_auth['warehouse'])){    
                $auth_sql['warehouse']=['in',$user_auth['warehouse'],'OR'];
                $sql['id'] = ['in',arrayChange(purchaseinfo::where($auth_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
            }
        }
        //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $sql['time']=[$egt,$elt];
        }
        //按照供应商搜索
        if(!empty(input('get.supplier'))){
            $sql['supplier']=input('get.supplier');
        }
        //按照制单人搜索
        if(!empty(input('get.user'))){
            $sql['user']=input('get.user');
        }
        //按照结算账户搜索
        if(!empty(input('get.account'))){
            $sql['account']=input('get.account');
        }
        //按照审核状态搜索
        if(!empty(input('get.type'))){
            $sql['type']=input('get.type')-1;
        }
        $sql['number'] = ['like','%'.input('get.number').'%'];
        $sql['data']=['like','%'.input('get.data').'%'];
        $arr = purchaseclass::where($sql)->select()->toArray();
        //判断报表类型
        $mode=input('get.mode');
        if(empty($mode)){
            //简易报表
            $tab_title=[
                'time'=>'单据日期',
                'number'=>'单据编号',
                'supplier'=>'供应商',
                'account'=>'结算账户',
                'user'=>'制单人',
                'type'=>'审核状态',
                'total'=>'单据金额',
                'actual'=>'实际金额',
                'money'=>'实付金额',
                'data'=>'单据备注'
            ];
            $tab_data=[];
            foreach ($arr as $key=>$vo) {
                $tmp_data=$arr[$key];
                $tmp_data['supplier']=$vo['supplier']['info']['name'];
                $tmp_data['account']=$vo['account']['info']['name'];
                $tmp_data['user']=$vo['user']['info']['name'];
                $tmp_data['type']=$vo['type']['name'];
                array_push($tab_data,$tmp_data);
            }
            $sum_arr=get_sums($tab_data,['total','actual','money']);
            //表格内容
            $arr['shell']='购货单报表';//Shell名称
            $arr['title']='购 货 单 报 表';//标题名称
            $arr['rows']=[
                'title'=>$tab_title,
                'data'=>$tab_data,
                'text_1'=>'单据总金额:'.$sum_arr['total'].' | '.'实际总金额:'.$sum_arr['actual'].' | '.'实付总金额:'.$sum_arr['money']
            ];
            $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
            $arr['colnums']=count($tab_title);//列数
        }else{
            //详细报表
            foreach ($arr as $key=>$vo) {
                //构造数据
                $tab_title=[
                    'name'=>'商品信息',
                    'attr'=>'辅助属性',
                    'warehouse'=>'所入仓库',
                    'brand'=>'商品品牌',
                    'number'=>'商品编号',
                    'class'=>'商品分类',
                    'spec'=>'规格型号',
                    'code'=>'条形码',
                    'unit'=>'商品单位',
                    'stocktip'=>'库存预警',
                    'location'=>'商品货位',
                    'integral'=>'赠送积分',
                    'serial'=>'商品串码',
                    'nums'=>'数量',
                    'price'=>'购货单价',
                    'total'=>'购货金额',
                    'batch'=>'商品批次',
                    'data'=>'备注信息'
                ];
                //字段设置
                $tabinfo=tabinfo::get(['name'=>'purchase']);
                $tabmain=json_decode($tabinfo['main'],true);
                foreach ($tabmain as $main_vo) {
                    if(empty($main_vo[key($main_vo)][1])){
                        unset($tab_title[key($main_vo)]);//删除
                    }
                }
                $sys=sys::get(['id'=>2]);
                if(empty($sys['info']['serial'])){
                    unset($tab_title['serial']);//删除串号
                }
                if(empty($sys['info']['batch'])){
                    unset($tab_title['batch']);//删除批次
                }
                //表格内容
                $tab_data=purchaseinfo::where(['pid'=>$vo['id']])->select()->toArray();
                foreach($tab_data as $k=>$data_vo){
                    $tab_data[$k]['name']=$data_vo['goods']['info']['name'];
                    $tab_data[$k]['attr']=$data_vo['attr']['name'];
                    $tab_data[$k]['warehouse']=$data_vo['warehouse']['info']['name'];
                    $tab_data[$k]['brand']=$data_vo['goods']['info']['brand']['info']['name'];
                    $tab_data[$k]['number']=$data_vo['goods']['info']['number'];
                    $tab_data[$k]['class']=$data_vo['goods']['info']['class']['info']['name'];
                    $tab_data[$k]['spec']=$data_vo['goods']['info']['spec'];
                    $tab_data[$k]['code']=' '.$data_vo['goods']['info']['code'];//转字符串
                    $tab_data[$k]['unit']=$data_vo['goods']['info']['unit']['info']['name'];
                    $tab_data[$k]['stocktip']=$data_vo['goods']['info']['stocktip'];
                    $tab_data[$k]['location']=$data_vo['goods']['info']['location'];
                    $tab_data[$k]['integral']=$data_vo['goods']['info']['integral'];
                    $tab_data[$k]['batch']=$data_vo['room']['info']['batch'];
                }
                $arr[$key]['shell']=$vo['number'];//Shell名称
                $arr[$key]['title']='购 货 单';//标题名称
                $arr[$key]['rows']=[
                    'text_1'=>'供应商:'.$vo['supplier']['info']['name'].'||'.'单据日期:'.$vo['time'].'||'.'单据编号:'.$vo['number'],
                    'title'=>$tab_title,
                    'data'=>$tab_data,
                    'text_2'=>'单据金额:'.$vo['total'].'||'.'实际金额:'.$vo['actual'].'||'.'实付金额:'.$vo['money'],
                    'text_3'=>'制单人:'.$vo['user']['info']['name'].'||'.'结算账户:'.$vo['account']['info']['name'].'||'.'备注信息:'.$vo['data'],
                ];
                $arr[$key]['rownums']=count($arr[$key]['rows'])+count($tab_data)-1;//行数
                $arr[$key]['colnums']=count($tab_title);//列数
            }
        }
        
        if(empty($mode)){
            ExportExcel('购货单',[$arr]);
        }else{
            if(count($arr)>32){
                $file_name_arr=[];
                $chunk_arr=array_chunk($arr,32);
                foreach ($chunk_arr as $key=>$chunk_arr_vo) {
                    array_push($file_name_arr,'skin/tmp_file/xls/'.ExportExcel('GHD',$chunk_arr_vo,[true,$key+1]).'.xls');
                }
                $zip_name=strtoupper('GHD');
                file_to_zip($zip_name,$file_name_arr);
            }else{
                ExportExcel('购货单',$arr);
            }
        }
    }
    //导出库存商品详情
    public function room(){
        //当前数据
        if(!empty(input('get.name'))){
            $goods_sql['name|py']=['like','%'.input('get.name').'%'];
        }
        if(!empty(input('get.number'))){
            $goods_sql['number']=['like','%'.input('get.number').'%'];
        }
        if(!empty(input('get.location'))){
            $goods_sql['location']=['like','%'.input('get.location').'%'];
        }
        if(!empty(input('get.spec'))){
            $goods_sql['spec'] = ['like','%'.input('get.spec').'%'];
        }
        //判断商品类型
        if(!empty(input('get.class'))){
            $goods_sql['class'] = ['in',goodsclass_more_arr(input('get.class')),'OR'];
        }
        //判断商品单位
        if(!empty(input('get.unit'))){
            $goods_sql['unit'] = input('get.unit');
        }
        //判断所属品牌
        if(!empty(input('get.brand'))){
            $goods_sql['brand'] = input('get.brand');
        }
        if(isset($goods_sql)){
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $sql['goods']=['in',$goods_arr,'OR'];
        }
        //判断所属仓库
        if(!empty(input('get.warehouse'))){
            $sql['warehouse'] = input('get.warehouse');
        }
        //判断零库存商品
        if(empty(input('get.eye'))){
            $sql['nums'] = ['neq',0];
        }
        
        if(isset($sql)){
            $tab_data=room::where($sql)->select()->toArray();
        }else{
            $tab_data=room::select()->toArray();
        }
        foreach ($tab_data as $key=>$vo) {
            $tab_data[$key]['name']=$vo['goods']['info']['name'];
            $tab_data[$key]['attr']=$vo['attr']['name'];
            $tab_data[$key]['warehouse']=$vo['warehouse']['info']['name'];
            $tab_data[$key]['unit']=$vo['goods']['info']['unit']['info']['name'];
            $tab_data[$key]['class']=$vo['goods']['info']['class']['info']['name'];
            $tab_data[$key]['spec']=$vo['goods']['info']['spec'];
            $tab_data[$key]['number']=$vo['goods']['info']['number'];
            $tab_data[$key]['location']=$vo['goods']['info']['location'];
            $tab_data[$key]['brand']=$vo['goods']['info']['brand']['info']['name'];
            $tab_data[$key]['serial']=implode('|',arrayChange(serial::where(['room'=>$vo['id'],'type'=>0])->field('code')->select()->toArray(),'code'));
        }
        //构造数据
        $tab_title=[
            'name'=>'商品名称',
            'attr'=>'辅助属性',
            'warehouse'=>'所属仓库',
            'nums'=>'库存数量',
            'unit'=>'商品单位',
            'serial'=>'商品串码',
            'batch'=>'商品批次',
            'class'=>'所属分类',
            'spec'=>'规格型号',
            'number'=>'商品编号',
            'location'=>'商品货位',
            'brand'=>'商品品牌',
        ];
        //字段设置
        $tabinfo=tabinfo::get(['name'=>'room']);
        $tabmain=json_decode($tabinfo['main'],true);
        foreach ($tabmain as $main_vo) {
            if(empty($main_vo[key($main_vo)][1])){
                unset($tab_title[key($main_vo)]);//删除
            }
        }
        $sys=sys::get(['id'=>2]);
        if(empty($sys['info']['serial'])){
            unset($tab_title['serial']);//删除串号
        }
        if(empty($sys['info']['batch'])){
            unset($tab_title['batch']);//删除批次
        }
        $sum_arr=get_sums($tab_data,['nums']);
        //表格内容
        $arr['shell']='库存详情';//Shell名称
        $arr['title']='库存详情';//标题名称
        $arr['rows']=[
            'title'=>$tab_title,
            'data'=>$tab_data,
            'text_1'=>'库存总数量:'.$sum_arr['nums']
        ];
        $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
        $arr['colnums']=count($tab_title);//列数
        ExportExcel('库存详情',[$arr]);
    }
    //导出购货对账单详情
    public function purchase_bill(){
       //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $sql['time']=[$egt,$elt];
        }
        //判断用户
        if(!empty(input('get.user'))){
            $sql['user'] = input('get.user');
        }
        //判断供应商
        if(!empty(input('get.supplier'))){
            $sql['supplier'] = input('get.supplier');
        }
        //判断结算账户
        if(!empty(input('get.account'))){
            $sql['account'] = input('get.account');
        }
        //判断付款状态
        if(empty(input('get.billtype'))){
            $sql['billtype']=['neq',-1];
        }else{
            $sql['billtype']=[
                ['neq',-1],
                ['eq',input('get.billtype')-1]
            ];
        }
        $sql['number'] = ['like','%'.input('get.number').'%'];
        $tab_data = purchaseclass::where($sql)->select()->toArray();
        foreach ($tab_data as $key=>$vo) {
               $tab_data[$key]['account']=$vo['account']['info']['name'];
               $tab_data[$key]['user']=$vo['user']['info']['name'];
               $tab_data[$key]['supplier']=$vo['supplier']['info']['name'];
               $tab_data[$key]['billtype']=$vo['billtype']['name'];
        }
        //构造数据
        $tab_title=[
            'time'=>'单据日期',
            'number'=>'单据编号',
            'supplier'=>'供应商',
            'account'=>'结算账户',
            'total'=>'单据金额',
            'actual'=>'实际金额',
            'money'=>'实付金额',
            'user'=>'制单人',
            'billtype'=>'付款状态',
        ];
        $sum_arr=get_sums($tab_data,['total','actual','money']);
        //表格内容
        $arr['shell']='购货对账单详情';//Shell名称
        $arr['title']='购货对账单详情';//标题名称
        $arr['rows']=[
            'title'=>$tab_title,
            'data'=>$tab_data,
            'text_1'=>'单据总金额:'.$sum_arr['total'].' | '.'实际总金额:'.$sum_arr['actual'].' | '.'实付总金额:'.$sum_arr['money']
        ];
        $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
        $arr['colnums']=count($tab_title);//列数
        ExportExcel('购货对账单详情',[$arr]);
    }
    //导出销货单信息
    public function sale(){
        //按照名称搜索
        if(!empty(input('get.goods'))){
            $goods_sql['name|py']=['like','%'.input('get.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $info_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('get.warehouse'))){
            $info_sql['warehouse']=input('get.warehouse');
        }
        
        //info条件转class条件
        if(isset($info_sql)){
            $class_sql['id'] = ['in',arrayChange(saleinfo::where($info_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
        }else{
            //默认数据鉴权处理
            $user_auth=json_decode(user_info('auth'),true);
            if(!empty($user_auth) && !empty($user_auth['warehouse'])){    
                $auth_sql['warehouse']=['in',$user_auth['warehouse'],'OR'];
                $class_sql['id'] = ['in',arrayChange(saleinfo::where($auth_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
            }
        }
        
        //按照编号搜索
        $class_sql['number']=['like','%'.input('get.number').'%'];
        $class_sql['data']=['like','%'.input('get.data').'%'];
        //按照客户搜索
        if(!empty(input('get.customer'))){
            $class_sql['customer']=input('get.customer');
        }
        //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $class_sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $class_sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $class_sql['time']=[$egt,$elt];
        }
        //按照制单人搜索
        if(!empty(input('get.user'))){
            $class_sql['user']=input('get.user');
        }
        //按照结算账户搜索
        if(!empty(input('get.account'))){
            $class_sql['account']=input('get.account');
        }
        //按照审核状态搜索
        if(!empty(input('get.type'))){
            $class_sql['type']=input('get.type')-1;
        }
        $arr = saleclass::where($class_sql)->select()->toArray();
        //判断报表类型
        $mode=input('get.mode');
        if(empty($mode)){
            //简易报表
            $tab_title=[
                'time'=>'单据日期',
                'number'=>'单据编号',
                'customer'=>'购买客户',
                'account'=>'结算账户',
                'user'=>'制单人',
                'type'=>'审核状态',
                'total'=>'单据金额',
                'discount'=>'优惠金额',
                'money'=>'实收金额',
                'data'=>'单据备注'
            ];
            $tab_data=[];
            foreach ($arr as $key=>$vo) {
                $tmp_data=$arr[$key];
                $tmp_data['customer']=$vo['customer']['info']['name'];
                $tmp_data['account']=$vo['account']['info']['name'];
                $tmp_data['user']=$vo['user']['info']['name'];
                $tmp_data['type']=$vo['type']['name'];
                array_push($tab_data,$tmp_data);
            }
            $sum_arr=get_sums($tab_data,['total','discount','money']);
            //表格内容
            $arr['shell']='销货单报表';//Shell名称
            $arr['title']='销 货 单 报 表';//标题名称
            $arr['rows']=[
                'title'=>$tab_title,
                'data'=>$tab_data,
                'text_1'=>'单据总金额:'.$sum_arr['total'].' | '.'优惠总金额:'.$sum_arr['discount'].' | '.'实收总金额:'.$sum_arr['money']
            ];
            $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
            $arr['colnums']=count($tab_title);//列数
        }else{
            foreach ($arr as $key=>$vo) {
                //构造数据
                $tab_title=[
                        'name'=>'商品信息',
                        'attr'=>'辅助属性',
                        'warehouse'=>'所属仓库',
                        'brand'=>'商品品牌',
                        'number'=>'商品编号',
                        'class'=>'商品分类',
                        'spec'=>'规格型号',
                        'code'=>'条形码',
                        'unit'=>'商品单位',
                        'stocktip'=>'库存预警',
                        'location'=>'商品货位',
                        'integral'=>'赠送积分',
                        'serial'=>'商品串码',
                        'nums'=>'数量',
                        'price'=>'销货单价',
                        'discount'=>'折扣额',
                        'total'=>'销货金额',
                        'batch'=>'商品批次',
                        'data'=>'备注信息'
                ];
                //字段设置
                $tabinfo=tabinfo::get(['name'=>'sale']);
                $tabmain=json_decode($tabinfo['main'],true);
                foreach ($tabmain as $main_vo) {
                    if(empty($main_vo[key($main_vo)][1])){
                        unset($tab_title[key($main_vo)]);//删除
                    }
                }
                $sys=sys::get(['id'=>2]);
                if(empty($sys['info']['serial'])){
                    unset($tab_title['serial']);//删除串号
                }
                if(empty($sys['info']['batch'])){
                    unset($tab_title['batch']);//删除批次
                }
                //表格内容
                $tab_data=saleinfo::where(['pid'=>$vo['id']])->select()->toArray();
                foreach($tab_data as $k=>$data_vo){
                    $tab_data[$k]['name']=$data_vo['room']['info']['goods']['info']['name'];
                    $tab_data[$k]['attr']=$data_vo['room']['info']['attr']['name'];
                    $tab_data[$k]['warehouse']=$data_vo['room']['info']['warehouse']['info']['name'];
                    $tab_data[$k]['brand']=$data_vo['room']['info']['goods']['info']['brand']['info']['name'];
                    $tab_data[$k]['number']=$data_vo['room']['info']['goods']['info']['number'];
                    $tab_data[$k]['class']=$data_vo['room']['info']['goods']['info']['class']['info']['name'];
                    $tab_data[$k]['spec']=$data_vo['room']['info']['goods']['info']['spec'];
                    $tab_data[$k]['code']=' '.$data_vo['room']['info']['goods']['info']['code'];//转字符串
                    $tab_data[$k]['unit']=$data_vo['room']['info']['goods']['info']['unit']['info']['name'];
                    $tab_data[$k]['stocktip']=$data_vo['room']['info']['goods']['info']['stocktip'];
                    $tab_data[$k]['location']=$data_vo['room']['info']['goods']['info']['location'];
                    $tab_data[$k]['integral']=$data_vo['room']['info']['goods']['info']['integral'];
                    $tab_data[$k]['batch']=$data_vo['room']['info']['batch'];
                }
                $arr[$key]['shell']=$vo['number'];//Shell名称
                $arr[$key]['title']='销 货 单';//标题名称
                $arr[$key]['rows']=[
                    'text_1'=>'购买客户:'.$vo['customer']['info']['name'].'||'.'单据日期:'.$vo['time'].'||'.'单据编号:'.$vo['number'],
                    'title'=>$tab_title,
                    'data'=>$tab_data,
                    'text_2'=>'单据金额:'.$vo['total'].'||'.'优惠金额:'.$vo['discount'].'||'.'实收金额:'.$vo['money'],
                    'text_3'=>'制单人:'.$vo['user']['info']['name'].'||'.'结算账户:'.$vo['account']['info']['name'].'||'.'备注信息:'.$vo['data'],
                ];
                $arr[$key]['rownums']=count($arr[$key]['rows'])+count($tab_data)-1;//行数
                $arr[$key]['colnums']=count($tab_title);//列数
                
            }
        }
        if(empty($mode)){
            ExportExcel('销货单',[$arr]);
        }else{
            if(count($arr)>32){
                $file_name_arr=[];
                $chunk_arr=array_chunk($arr,32);
                foreach ($chunk_arr as $key=>$chunk_arr_vo) {
                    array_push($file_name_arr,'skin/tmp_file/xls/'.ExportExcel('XHD',$chunk_arr_vo,[true,$key+1]).'.xls');
                }
                $zip_name=strtoupper('XHD');
                file_to_zip($zip_name,$file_name_arr);
            }else{
                ExportExcel('销货单',$arr);
            }
        }
        
        
    }
    //导出销货对账单详情
    public function sale_bill(){
       //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $sql['time']=[$egt,$elt];
        }
        //判断用户
        if(!empty(input('get.user'))){
            $sql['user'] = input('get.user');
        }
        //判断购买客户
        if(!empty(input('get.customer'))){
            $sql['customer'] = input('get.customer');
        }
        //判断结算账户
        if(!empty(input('get.account'))){
            $sql['account'] = input('get.account');
        }
        //判断付款状态
        if(empty(input('get.billtype'))){
            $sql['billtype']=['neq',-1];
        }else{
            $sql['billtype']=[
                ['neq',-1],
                ['eq',input('get.billtype')-1]
            ];
        }
        $sql['number'] = ['like','%'.input('get.number').'%'];
        $tab_data = saleclass::where($sql)->select()->toArray();
        foreach ($tab_data as $key=>$vo) {
               $tab_data[$key]['account']=$vo['account']['info']['name'];
               $tab_data[$key]['user']=$vo['user']['info']['name'];
               $tab_data[$key]['customer']=$vo['customer']['info']['name'];
               $tab_data[$key]['billtype']=$vo['billtype']['name'];
        }
        //构造数据
        $tab_title=[
            'time'=>'单据日期',
            'number'=>'单据编号',
            'customer'=>'购买客户',
            'account'=>'结算账户',
            'total'=>'单据金额',
            'discount'=>'优惠金额',
            'money'=>'实收金额',
            'user'=>'制单人',
            'billtype'=>'付款状态',
        ];
        $sum_arr=get_sums($tab_data,['total','discount','money']);
        //表格内容
        $arr['shell']='销货对账单详情';//Shell名称
        $arr['title']='销货对账单详情';//标题名称
        $arr['rows']=[
            'title'=>$tab_title,
            'data'=>$tab_data,
            'text_1'=>'单据总金额:'.$sum_arr['total'].' | '.'优惠总金额:'.$sum_arr['discount'].' | '.'实收总金额:'.$sum_arr['money']
        ];
        $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
        $arr['colnums']=count($tab_title);//列数
        ExportExcel('销货对账单详情',[$arr]);
    }
    //导出购货退货单信息
    public function repurchase(){
        //按照名称搜索
        if(!empty(input('get.goods'))){
            $goods_sql['name|py']=['like','%'.input('get.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $repurchaseinfo_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('get.warehouse'))){
            $repurchaseinfo_sql['warehouse']=input('get.warehouse');
        }
        
        if(isset($repurchaseinfo_sql)){
            $repurchaseinfo_arr=arrayChange(repurchaseinfo::where($repurchaseinfo_sql)->field('pid')->select()->toArray(),'pid');
            $sql['id']=['in',$repurchaseinfo_arr,'OR'];
        }else{
            //默认数据鉴权处理
            $user_auth=json_decode(user_info('auth'),true);
            if(!empty($user_auth) && !empty($user_auth['warehouse'])){    
                $auth_sql['warehouse']=['in',$user_auth['warehouse'],'OR'];
                $sql['id'] = ['in',arrayChange(repurchaseinfo::where($auth_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
            }
        }
        
        //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $sql['time']=[$egt,$elt];
        }
        //按照供应商搜索
        if(!empty(input('get.supplier'))){
            $sql['supplier']=input('get.supplier');
        }
        //按照制单人搜索
        if(!empty(input('get.user'))){
            $sql['user']=input('get.user');
        }
        //按照结算账户搜索
        if(!empty(input('get.account'))){
            $sql['account']=input('get.account');
        }
        //按照审核状态搜索
        if(!empty(input('get.type'))){
            $sql['type']=input('get.type')-1;
        }
        $sql['number'] = ['like','%'.input('get.number').'%'];
        $sql['data'] = ['like','%'.input('get.data').'%'];
        $arr = repurchaseclass::where($sql)->select()->toArray();
        //判断报表类型
        $mode=input('get.mode');
        if(empty($mode)){
            //简易报表
            $tab_title=[
                'time'=>'单据日期',
                'number'=>'单据编号',
                'supplier'=>'供应商',
                'account'=>'结算账户',
                'user'=>'制单人',
                'type'=>'审核状态',
                'total'=>'单据金额',
                'actual'=>'实际金额',
                'money'=>'实付金额',
                'data'=>'单据备注'
            ];
            $tab_data=[];
            foreach ($arr as $key=>$vo) {
                $tmp_data=$arr[$key];
                $tmp_data['supplier']=$vo['supplier']['info']['name'];
                $tmp_data['account']=$vo['account']['info']['name'];
                $tmp_data['user']=$vo['user']['info']['name'];
                $tmp_data['type']=$vo['type']['name'];
                array_push($tab_data,$tmp_data);
            }
            $sum_arr=get_sums($tab_data,['total','actual','money']);
            //表格内容
            $arr['shell']='购货退货单表';//Shell名称
            $arr['title']='购货退货单表';//标题名称
            $arr['rows']=[
                'title'=>$tab_title,
                'data'=>$tab_data,
                'text_1'=>'单据总金额:'.$sum_arr['total'].' | '.'实际总金额:'.$sum_arr['actual'].' | '.'实付总金额:'.$sum_arr['money']
            ];
            $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
            $arr['colnums']=count($tab_title);//列数
        }else{
            foreach ($arr as $key=>$vo) {
                //构造数据
                $tab_title=[
                        'name'=>'商品信息',
                        'attr'=>'辅助属性',
                        'warehouse'=>'所属仓库',
                        'brand'=>'商品品牌',
                        'number'=>'商品编号',
                        'class'=>'商品分类',
                        'spec'=>'规格型号',
                        'code'=>'条形码',
                        'unit'=>'商品单位',
                        'stocktip'=>'库存预警',
                        'location'=>'商品货位',
                        'integral'=>'赠送积分',
                        'serial'=>'商品串码',
                        'nums'=>'数量',
                        'price'=>'退货单价',
                        'total'=>'退货金额',
                        'batch'=>'商品批次',
                        'data'=>'备注信息'
                ];
                //字段设置
                $tabinfo=tabinfo::get(['name'=>'repurchase']);
                $tabmain=json_decode($tabinfo['main'],true);
                foreach ($tabmain as $main_vo) {
                    if(empty($main_vo[key($main_vo)][1])){
                        unset($tab_title[key($main_vo)]);//删除
                    }
                }
                $sys=sys::get(['id'=>2]);
                if(empty($sys['info']['serial'])){
                    unset($tab_title['serial']);//删除串号
                }
                if(empty($sys['info']['batch'])){
                    unset($tab_title['batch']);//删除批次
                }
                //表格内容
                $tab_data=repurchaseinfo::where(['pid'=>$vo['id']])->select()->toArray();
                foreach($tab_data as $k=>$data_vo){
                    $tab_data[$k]['name']=$data_vo['room']['info']['goods']['info']['name'];
                    $tab_data[$k]['attr']=$data_vo['room']['info']['attr']['name'];
                    $tab_data[$k]['warehouse']=$data_vo['room']['info']['warehouse']['info']['name'];
                    $tab_data[$k]['brand']=$data_vo['room']['info']['goods']['info']['brand']['info']['name'];
                    $tab_data[$k]['number']=$data_vo['room']['info']['goods']['info']['number'];
                    $tab_data[$k]['class']=$data_vo['room']['info']['goods']['info']['class']['info']['name'];
                    $tab_data[$k]['spec']=$data_vo['room']['info']['goods']['info']['spec'];
                    $tab_data[$k]['code']=' '.$data_vo['room']['info']['goods']['info']['code'];//转字符串
                    $tab_data[$k]['unit']=$data_vo['room']['info']['goods']['info']['unit']['info']['name'];
                    $tab_data[$k]['stocktip']=$data_vo['room']['info']['goods']['info']['stocktip'];
                    $tab_data[$k]['location']=$data_vo['room']['info']['goods']['info']['location'];
                    $tab_data[$k]['integral']=$data_vo['room']['info']['goods']['info']['integral'];
                    $tab_data[$k]['batch']=$data_vo['room']['info']['batch'];
                }
                $arr[$key]['shell']=$vo['number'];//Shell名称
                $arr[$key]['title']='购 货 退 货 单';//标题名称
                $arr[$key]['rows']=[
                    'text_1'=>'供应商:'.$vo['supplier']['info']['name'].'||'.'单据日期:'.$vo['time'].'||'.'单据编号:'.$vo['number'],
                    'title'=>$tab_title,
                    'data'=>$tab_data,
                    'text_2'=>'单据金额:'.$vo['total'].'||'.'实际金额:'.$vo['actual'].'||'.'实收金额:'.$vo['money'],
                    'text_3'=>'制单人:'.$vo['user']['info']['name'].'||'.'结算账户:'.$vo['account']['info']['name'].'||'.'备注信息:'.$vo['data'],
                ];
                $arr[$key]['rownums']=count($arr[$key]['rows'])+count($tab_data)-1;//行数
                $arr[$key]['colnums']=count($tab_title);//列数
                
            }
        }
        if(empty($mode)){
            ExportExcel('购货退货单',[$arr]);
        }else{
            if(count($arr)>32){
                $file_name_arr=[];
                $chunk_arr=array_chunk($arr,32);
                foreach ($chunk_arr as $key=>$chunk_arr_vo) {
                    array_push($file_name_arr,'skin/tmp_file/xls/'.ExportExcel('GHTHD',$chunk_arr_vo,[true,$key+1]).'.xls');
                }
                $zip_name=strtoupper('GHTHD');
                file_to_zip($zip_name,$file_name_arr);
            }else{
                ExportExcel('购货退货单',$arr);
            }
        }
        
    }
    //导出购货退货对账单详情
    public function repurchase_bill(){
       //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $sql['time']=[$egt,$elt];
        }
        //判断用户
        if(!empty(input('get.user'))){
            $sql['user'] = input('get.user');
        }
        //判断购买客户
        if(!empty(input('get.supplier'))){
            $sql['supplier'] = input('get.supplier');
        }
        //判断结算账户
        if(!empty(input('get.account'))){
            $sql['account'] = input('get.account');
        }
        //判断付款状态
        if(empty(input('get.billtype'))){
            $sql['billtype']=['neq',-1];
        }else{
            $sql['billtype']=[
                ['neq',-1],
                ['eq',input('get.billtype')-1]
            ];
        }
        $sql['number'] = ['like','%'.input('get.number').'%'];
        $tab_data = repurchaseclass::where($sql)->select()->toArray();
        foreach ($tab_data as $key=>$vo) {
               $tab_data[$key]['account']=$vo['account']['info']['name'];
               $tab_data[$key]['user']=$vo['user']['info']['name'];
               $tab_data[$key]['supplier']=$vo['supplier']['info']['name'];
               $tab_data[$key]['billtype']=$vo['billtype']['name'];
        }
        //构造数据
        $tab_title=[
            'time'=>'单据日期',
            'number'=>'单据编号',
            'supplier'=>'供应商',
            'account'=>'结算账户',
            'total'=>'单据金额',
            'actual'=>'实际金额',
            'money'=>'实收金额',
            'user'=>'制单人',
            'billtype'=>'付款状态',
        ];
        $sum_arr=get_sums($tab_data,['total','actual','money']);
        //表格内容
        $arr['shell']='购货退货对账单详情';//Shell名称
        $arr['title']='购货退货对账单详情';//标题名称
        $arr['rows']=[
            'title'=>$tab_title,
            'data'=>$tab_data,
            'text_1'=>'单据总金额:'.$sum_arr['total'].' | '.'实际总金额:'.$sum_arr['actual'].' | '.'实收总金额:'.$sum_arr['money']
        ];
        $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
        $arr['colnums']=count($tab_title);//列数
        ExportExcel('购货退货对账单详情',[$arr]);
    }
    //导出销货退货单信息
    public function resale(){
        //按照名称搜索
        if(!empty(input('get.goods'))){
            $goods_sql['name|py']=['like','%'.input('get.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $resaleinfo_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('get.warehouse'))){
            $resaleinfo_sql['warehouse']=input('get.warehouse');
        }
        
        if(isset($resaleinfo_sql)){
            $resaleinfo_arr=arrayChange(resaleinfo::where($resaleinfo_sql)->field('pid')->select()->toArray(),'pid');
            $sql['id']=['in',$resaleinfo_arr,'OR'];
        }else{
            //默认数据鉴权处理
            $user_auth=json_decode(user_info('auth'),true);
            if(!empty($user_auth) && !empty($user_auth['warehouse'])){    
                $auth_sql['warehouse']=['in',$user_auth['warehouse'],'OR'];
                $sql['id'] = ['in',arrayChange(resaleinfo::where($auth_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
            }
        }
        //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $sql['time']=[$egt,$elt];
        }
        //按照购买客户搜索
        if(!empty(input('get.customer'))){
            $sql['customer']=input('get.customer');
        }
        //按照制单人搜索
        if(!empty(input('get.user'))){
            $sql['user']=input('get.user');
        }
        //按照结算账户搜索
        if(!empty(input('get.account'))){
            $sql['account']=input('get.account');
        }
        //按照审核状态搜索
        if(!empty(input('get.type'))){
            $sql['type']=input('get.type')-1;
        }
        $sql['number'] = ['like','%'.input('get.number').'%'];
        $sql['data'] = ['like','%'.input('get.data').'%'];
        $arr = resaleclass::where($sql)->select()->toArray();
        //判断报表类型
        $mode=input('get.mode');
        if(empty($mode)){
            //简易报表
            $tab_title=[
                'time'=>'单据日期',
                'number'=>'单据编号',
                'customer'=>'购买客户',
                'account'=>'结算账户',
                'user'=>'制单人',
                'type'=>'审核状态',
                'total'=>'单据金额',
                'actual'=>'实际金额',
                'money'=>'实付金额',
                'data'=>'单据备注'
            ];
            $tab_data=[];
            foreach ($arr as $key=>$vo) {
                $tmp_data=$arr[$key];
                $tmp_data['customer']=$vo['customer']['info']['name'];
                $tmp_data['account']=$vo['account']['info']['name'];
                $tmp_data['user']=$vo['user']['info']['name'];
                $tmp_data['type']=$vo['type']['name'];
                array_push($tab_data,$tmp_data);
            }
            $sum_arr=get_sums($tab_data,['total','actual','money']);
            //表格内容
            $arr['shell']='销货退货单报表';//Shell名称
            $arr['title']='销 货 退 货 单 报 表';//标题名称
            $arr['rows']=[
                'title'=>$tab_title,
                'data'=>$tab_data,
                'text_1'=>'单据总金额:'.$sum_arr['total'].' | '.'实际总金额:'.$sum_arr['actual'].' | '.'实付总金额:'.$sum_arr['money']
            ];
            $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
            $arr['colnums']=count($tab_title);//列数
        }else{
            foreach ($arr as $key=>$vo) {
                //构造数据
                $tab_title=[
                        'name'=>'商品信息',
                        'attr'=>'辅助属性',
                        'warehouse'=>'所入仓库',
                        'brand'=>'商品品牌',
                        'number'=>'商品编号',
                        'class'=>'商品分类',
                        'spec'=>'规格型号',
                        'code'=>'条形码',
                        'unit'=>'商品单位',
                        'stocktip'=>'库存预警',
                        'location'=>'商品货位',
                        'integral'=>'赠送积分',
                        'serial'=>'商品串码',
                        'nums'=>'数量',
                        'price'=>'退货单价',
                        'total'=>'退货金额',
                        'batch'=>'商品批次',
                        'data'=>'备注信息'
                ];
                //字段设置
                $tabinfo=tabinfo::get(['name'=>'resale']);
                $tabmain=json_decode($tabinfo['main'],true);
                foreach ($tabmain as $main_vo) {
                    if(empty($main_vo[key($main_vo)][1])){
                        unset($tab_title[key($main_vo)]);//删除
                    }
                }
                $sys=sys::get(['id'=>2]);
                if(empty($sys['info']['serial'])){
                    unset($tab_title['serial']);//删除串号
                }
                if(empty($sys['info']['batch'])){
                    unset($tab_title['batch']);//删除批次
                }
                //表格内容
                $tab_data=resaleinfo::where(['pid'=>$vo['id']])->select()->toArray();
                foreach($tab_data as $k=>$data_vo){
                    
                    $tab_data[$k]['name']=$data_vo['room']['info']['goods']['info']['name'];
                    $tab_data[$k]['attr']=$data_vo['room']['info']['attr']['name'];
                    $tab_data[$k]['warehouse']=$data_vo['room']['info']['warehouse']['info']['name'];
                    $tab_data[$k]['brand']=$data_vo['room']['info']['goods']['info']['brand']['info']['name'];
                    $tab_data[$k]['number']=$data_vo['room']['info']['goods']['info']['number'];
                    $tab_data[$k]['class']=$data_vo['room']['info']['goods']['info']['class']['info']['name'];
                    $tab_data[$k]['spec']=$data_vo['room']['info']['goods']['info']['spec'];
                    $tab_data[$k]['code']=' '.$data_vo['room']['info']['goods']['info']['code'];//转字符串
                    $tab_data[$k]['unit']=$data_vo['room']['info']['goods']['info']['unit']['info']['name'];
                    $tab_data[$k]['stocktip']=$data_vo['room']['info']['goods']['info']['stocktip'];
                    $tab_data[$k]['location']=$data_vo['room']['info']['goods']['info']['location'];
                    $tab_data[$k]['integral']=$data_vo['room']['info']['goods']['info']['integral'];
                    $tab_data[$k]['batch']=$data_vo['room']['info']['batch'];
                }
                $arr[$key]['shell']=$vo['number'];//Shell名称
                $arr[$key]['title']='销 货 退 货 单';//标题名称
                $arr[$key]['rows']=[
                    'text_1'=>'购买客户:'.$vo['customer']['info']['name'].'||'.'单据日期:'.$vo['time'].'||'.'单据编号:'.$vo['number'],
                    'title'=>$tab_title,
                    'data'=>$tab_data,
                    'text_2'=>'单据金额:'.$vo['total'].'||'.'实际金额:'.$vo['actual'].'||'.'实付金额:'.$vo['money'],
                    'text_3'=>'制单人:'.$vo['user']['info']['name'].'||'.'结算账户:'.$vo['account']['info']['name'].'||'.'备注信息:'.$vo['data'],
                ];
                $arr[$key]['rownums']=count($arr[$key]['rows'])+count($tab_data)-1;//行数
                $arr[$key]['colnums']=count($tab_title);//列数
                
            }
        }
        if(empty($mode)){
            ExportExcel('销货退货单',[$arr]);
        }else{
            if(count($arr)>32){
                $file_name_arr=[];
                $chunk_arr=array_chunk($arr,32);
                foreach ($chunk_arr as $key=>$chunk_arr_vo) {
                    array_push($file_name_arr,'skin/tmp_file/xls/'.ExportExcel('XHTHD',$chunk_arr_vo,[true,$key+1]).'.xls');
                }
                $zip_name=strtoupper('XHTHD');
                file_to_zip($zip_name,$file_name_arr);
            }else{
                ExportExcel('销货退货单',$arr);
            }
        }
        
    }
    //导出销货退货对账单详情
    public function resale_bill(){
       //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $sql['time']=[$egt,$elt];
        }
        //判断用户
        if(!empty(input('get.user'))){
            $sql['user'] = input('get.user');
        }
        //判断供应商
        if(!empty(input('get.customer'))){
            $sql['customer'] = input('get.customer');
        }
        //判断结算账户
        if(!empty(input('get.account'))){
            $sql['account'] = input('get.account');
        }
        //判断付款状态
        if(empty(input('get.billtype'))){
            $sql['billtype']=['neq',-1];
        }else{
            $sql['billtype']=[
                ['neq',-1],
                ['eq',input('get.billtype')-1]
            ];
        }
        $sql['number'] = ['like','%'.input('get.number').'%'];
        $tab_data = resaleclass::where($sql)->select()->toArray();
        foreach ($tab_data as $key=>$vo) {
               $tab_data[$key]['account']=$vo['account']['info']['name'];
               $tab_data[$key]['user']=$vo['user']['info']['name'];
               $tab_data[$key]['customer']=$vo['customer']['info']['name'];
               $tab_data[$key]['billtype']=$vo['billtype']['name'];
        }
        //构造数据
        $tab_title=[
            'time'=>'单据日期',
            'number'=>'单据编号',
            'customer'=>'购买客户',
            'account'=>'结算账户',
            'total'=>'单据金额',
            'actual'=>'实际金额',
            'money'=>'实付金额',
            'user'=>'制单人',
            'billtype'=>'付款状态',
        ];
        $sum_arr=get_sums($tab_data,['total','actual','money']);
        //表格内容
        $arr['shell']='销货退货对账单详情';//Shell名称
        $arr['title']='销货退货对账单详情';//标题名称
        $arr['rows']=[
            'title'=>$tab_title,
            'data'=>$tab_data,
            'text_1'=>'单据总金额:'.$sum_arr['total'].' | '.'实际总金额:'.$sum_arr['actual'].' | '.'实付总金额:'.$sum_arr['money']
        ];
        $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
        $arr['colnums']=count($tab_title);//列数
        ExportExcel('销货退货对账单详情',[$arr]);
    }
    //导出调拨单信息
    public function allocation(){
        //按照名称搜索
        if(!empty(input('get.goods'))){
            $goods_sql['name|py']=['like','%'.input('get.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $info_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('get.warehouse'))){
            $info_sql['towarehouse']=input('get.warehouse');
        }
        //info条件转class条件
        if(isset($info_sql)){
            $class_sql['id'] = ['in',arrayChange(allocationinfo::where($info_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
        }
        //按照编号搜索
        $class_sql['number']=['like','%'.input('get.number').'%'];
        //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $class_sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $class_sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $class_sql['time']=[$egt,$elt];
        }
        //按照制单人搜索
        if(!empty(input('get.user'))){
            $class_sql['user']=input('get.user');
        }
        //按照审核状态搜索
        if(!empty(input('get.type'))){
            $class_sql['type']=input('get.type')-1;
        }
        $arr = allocationclass::where($class_sql)->select()->toArray();
        //判断报表类型
        $mode=input('get.mode');
        if(empty($mode)){
            //简易报表
            $tab_title=[
                'time'=>'单据日期',
                'number'=>'单据编号',
                'user'=>'制单人',
                'type'=>'审核状态',
                'data'=>'单据备注'
            ];
            $tab_data=[];
            foreach ($arr as $key=>$vo) {
                $tmp_data=$arr[$key];
                $tmp_data['user']=$vo['user']['info']['name'];
                $tmp_data['type']=$vo['type']['name'];
                array_push($tab_data,$tmp_data);
            }
            //表格内容
            $arr['shell']='调拨单报表';//Shell名称
            $arr['title']='调 拨 单 报 表';//标题名称
            $arr['rows']=[
                'title'=>$tab_title,
                'data'=>$tab_data
            ];
            $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
            $arr['colnums']=count($tab_title);//列数
        }else{
            foreach ($arr as $key=>$vo) {
                //构造数据
                $tab_title=[
                        'name'=>'商品信息',
                        'attr'=>'辅助属性',
                        'warehouse'=>'所属仓库',
                        'brand'=>'商品品牌',
                        'number'=>'商品编号',
                        'class'=>'商品分类',
                        'spec'=>'规格型号',
                        'code'=>'条形码',
                        'unit'=>'商品单位',
                        'stocktip'=>'库存预警',
                        'location'=>'商品货位',
                        'integral'=>'赠送积分',
                        'serial'=>'商品串码',
                        'nums'=>'数量',
                        'towarehouse'=>'调拨仓库',
                        'batch'=>'商品批次',
                        'data'=>'备注信息'
                ];
                //字段设置
                $tabinfo=tabinfo::get(['name'=>'allocation']);
                $tabmain=json_decode($tabinfo['main'],true);
                foreach ($tabmain as $main_vo) {
                    if(empty($main_vo[key($main_vo)][1])){
                        unset($tab_title[key($main_vo)]);//删除
                    }
                }
                $sys=sys::get(['id'=>2]);
                if(empty($sys['info']['serial'])){
                    unset($tab_title['serial']);//删除串号
                }
                if(empty($sys['info']['batch'])){
                    unset($tab_title['batch']);//删除批次
                }
                //表格内容
                $tab_data=allocationinfo::where(['pid'=>$vo['id']])->select()->toArray();
                foreach($tab_data as $k=>$data_vo){
                    $tab_data[$k]['name']=$data_vo['room']['info']['goods']['info']['name'];
                    $tab_data[$k]['attr']=$data_vo['room']['info']['attr']['name'];
                    $tab_data[$k]['warehouse']=$data_vo['room']['info']['warehouse']['info']['name'];
                    $tab_data[$k]['brand']=$data_vo['room']['info']['goods']['info']['brand']['info']['name'];
                    $tab_data[$k]['number']=$data_vo['room']['info']['goods']['info']['number'];
                    $tab_data[$k]['class']=$data_vo['room']['info']['goods']['info']['class']['info']['name'];
                    $tab_data[$k]['spec']=$data_vo['room']['info']['goods']['info']['spec'];
                    $tab_data[$k]['code']=' '.$data_vo['room']['info']['goods']['info']['code'];//转字符串
                    $tab_data[$k]['unit']=$data_vo['room']['info']['goods']['info']['unit']['info']['name'];
                    $tab_data[$k]['stocktip']=$data_vo['room']['info']['goods']['info']['stocktip'];
                    $tab_data[$k]['location']=$data_vo['room']['info']['goods']['info']['location'];
                    $tab_data[$k]['integral']=$data_vo['room']['info']['goods']['info']['integral'];
                    $tab_data[$k]['towarehouse']=$data_vo['towarehouse']['info']['name'];
                    $tab_data[$k]['batch']=$data_vo['room']['info']['batch'];
                }
                $arr[$key]['shell']=$vo['number'];//Shell名称
                $arr[$key]['title']='调 拨 单';//标题名称
                $arr[$key]['rows']=[
                    'text_1'=>'单据日期:'.$vo['time'].'||'.'单据编号:'.$vo['number'],
                    'title'=>$tab_title,
                    'data'=>$tab_data,
                    'text_2'=>'制单人:'.$vo['user']['info']['name'].'||'.'备注信息:'.$vo['data'],
                ];
                $arr[$key]['rownums']=count($arr[$key]['rows'])+count($tab_data)-1;//行数
                $arr[$key]['colnums']=count($tab_title);//列数
                
            }
        }
        if(empty($mode)){
            ExportExcel('调拨单',[$arr]);
        }else{
            if(count($arr)>32){
                $file_name_arr=[];
                $chunk_arr=array_chunk($arr,32);
                foreach ($chunk_arr as $key=>$chunk_arr_vo) {
                    array_push($file_name_arr,'skin/tmp_file/xls/'.ExportExcel('DBD',$chunk_arr_vo,[true,$key+1]).'.xls');
                }
                $zip_name=strtoupper('DBD');
                file_to_zip($zip_name,$file_name_arr);
            }else{
                ExportExcel('调拨单',$arr);
            }
        }
        
    }
    //导出其他入库单信息
    public function otpurchase(){
        //按照名称搜索
        if(!empty(input('get.goods'))){
            $goods_sql['name|py']=['like','%'.input('get.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $info_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('get.warehouse'))){
            $info_sql['warehouse']=input('get.warehouse');
        }
        //info条件转class条件
        if(isset($info_sql)){
            $class_sql['id'] = ['in',arrayChange(otpurchaseinfo::where($info_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
        }else{
            //默认数据鉴权处理
            $user_auth=json_decode(user_info('auth'),true);
            if(!empty($user_auth) && !empty($user_auth['warehouse'])){    
                $auth_sql['warehouse']=['in',$user_auth['warehouse'],'OR'];
                $class_sql['id'] = ['in',arrayChange(otpurchaseinfo::where($auth_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
            }
        }
        //按照编号搜索
        $class_sql['number']=['like','%'.input('get.number').'%'];
        $class_sql['data']=['like','%'.input('get.data').'%'];
        //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $class_sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $class_sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $class_sql['time']=[$egt,$elt];
        }
        //按照制单人搜索
        if(!empty(input('get.user'))){
            $class_sql['user']=input('get.user');
        }
        //按照审核状态搜索
        if(!empty(input('get.type'))){
            $class_sql['type']=input('get.type')-1;
        }
         //按照单据类型搜索
        if(!empty(input('get.pagetype'))){
            $class_sql['pagetype']=input('get.pagetype')-1;
        }
        $arr = otpurchaseclass::where($class_sql)->select()->toArray();
        //判断报表类型
        $mode=input('get.mode');
        if(empty($mode)){
            //简易报表
            $tab_title=[
                'time'=>'单据日期',
                'number'=>'单据编号',
                'user'=>'制单人',
                'pagetype'=>'单据类型',
                'type'=>'审核状态',
                'data'=>'单据备注'
            ];
            $tab_data=[];
            foreach ($arr as $key=>$vo) {
                $tmp_data=$arr[$key];
                $tmp_data['user']=$vo['user']['info']['name'];
                $tmp_data['pagetype']=$vo['pagetype']['name'];
                $tmp_data['type']=$vo['type']['name'];
                array_push($tab_data,$tmp_data);
            }
            //表格内容
            $arr['shell']='其他入库单报表';//Shell名称
            $arr['title']='其他入库单报表';//标题名称
            $arr['rows']=[
                'title'=>$tab_title,
                'data'=>$tab_data
            ];
            $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
            $arr['colnums']=count($tab_title);//列数
        }else{
            foreach ($arr as $key=>$vo) {
                //构造数据
                $tab_title=[
                        'name'=>'商品信息',
                        'attr'=>'辅助属性',
                        'brand'=>'商品品牌',
                        'number'=>'商品编号',
                        'class'=>'商品分类',
                        'spec'=>'规格型号',
                        'code'=>'条形码',
                        'unit'=>'商品单位',
                        'stocktip'=>'库存预警',
                        'location'=>'商品货位',
                        'integral'=>'赠送积分',
                        'serial'=>'商品串码',
                        'nums'=>'数量',
                        'warehouse'=>'所入仓库',
                        'batch'=>'商品批次',
                        'data'=>'备注信息'
                ];
                //字段设置
                $tabinfo=tabinfo::get(['name'=>'otpurchase']);
                $tabmain=json_decode($tabinfo['main'],true);
                foreach ($tabmain as $main_vo) {
                    if(empty($main_vo[key($main_vo)][1])){
                        unset($tab_title[key($main_vo)]);//删除
                    }
                }
                $sys=sys::get(['id'=>2]);
                if(empty($sys['info']['serial'])){
                    unset($tab_title['serial']);//删除串号
                }
                if(empty($sys['info']['batch'])){
                    unset($tab_title['batch']);//删除批次
                }
                //表格内容
                $tab_data=otpurchaseinfo::where(['pid'=>$vo['id']])->select()->toArray();
                foreach($tab_data as $k=>$data_vo){
                    $tab_data[$k]['name']=$data_vo['goods']['info']['name'];
                    $tab_data[$k]['attr']=$data_vo['attr']['name'];
                    $tab_data[$k]['warehouse']=$data_vo['warehouse']['info']['name'];
                    $tab_data[$k]['brand']=$data_vo['goods']['info']['brand']['info']['name'];
                    $tab_data[$k]['number']=$data_vo['goods']['info']['number'];
                    $tab_data[$k]['class']=$data_vo['goods']['info']['class']['info']['name'];
                    $tab_data[$k]['spec']=$data_vo['goods']['info']['spec'];
                    $tab_data[$k]['code']=' '.$data_vo['goods']['info']['code'];//转字符串
                    $tab_data[$k]['unit']=$data_vo['goods']['info']['unit']['info']['name'];
                    $tab_data[$k]['stocktip']=$data_vo['goods']['info']['stocktip'];
                    $tab_data[$k]['location']=$data_vo['goods']['info']['location'];
                    $tab_data[$k]['integral']=$data_vo['goods']['info']['integral'];
                    $tab_data[$k]['batch']=$data_vo['room']['info']['batch'];
                }
                $arr[$key]['shell']=$vo['number'];//Shell名称
                $arr[$key]['title']='其 他 入 库 单';//标题名称
                $arr[$key]['rows']=[
                    'text_1'=>'单据日期:'.$vo['time'].'||'.'单据编号:'.$vo['number'].'||'.'单据类型:'.$vo['pagetype']['name'],
                    'title'=>$tab_title,
                    'data'=>$tab_data,
                    'text_2'=>'制单人:'.$vo['user']['info']['name'].'||'.'备注信息:'.$vo['data'],
                ];
                $arr[$key]['rownums']=count($arr[$key]['rows'])+count($tab_data)-1;//行数
                $arr[$key]['colnums']=count($tab_title);//列数
                
            }
        }
        if(empty($mode)){
            ExportExcel('其他入库单',[$arr]);
        }else{
            if(count($arr)>32){
                $file_name_arr=[];
                $chunk_arr=array_chunk($arr,32);
                foreach ($chunk_arr as $key=>$chunk_arr_vo) {
                    array_push($file_name_arr,'skin/tmp_file/xls/'.ExportExcel('QTRKD',$chunk_arr_vo,[true,$key+1]).'.xls');
                }
                $zip_name=strtoupper('QTRKD');
                file_to_zip($zip_name,$file_name_arr);
            }else{
                ExportExcel('其他入库单',$arr);
            }
        }
        
        
    }
    //导出收款单信息
    public function gather(){
        //按照结算账户搜索
        if(!empty(input('get.account'))){
            $info_sql['account']=input('get.account');
        }
        //info条件转class条件
        if(isset($info_sql)){
            $class_sql['id'] = ['in',arrayChange(gatherinfo::where($info_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
        }else{
            //默认数据鉴权处理
            $user_auth=json_decode(user_info('auth'),true);
            if(!empty($user_auth) && !empty($user_auth['account'])){    
                $auth_sql['account']=['in',$user_auth['account'],'OR'];
                $class_sql['id'] = ['in',arrayChange(gatherinfo::where($auth_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
            }
        }
        //按照编号搜索
        $class_sql['number']=['like','%'.input('get.number').'%'];
        //按照备注信息搜索
        $class_sql['data']=['like','%'.input('get.data').'%'];
        //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $class_sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $class_sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $class_sql['time']=[$egt,$elt];
        }
        //按照制单人搜索
        if(!empty(input('get.user'))){
            $class_sql['user']=input('get.user');
        }
        //按照收款客户搜索
        if(!empty(input('get.customer'))){
            $class_sql['customer']=input('get.customer');
        }
        //按照审核状态搜索
        if(!empty(input('get.type'))){
            $class_sql['type']=input('get.type')-1;
        }
        $arr = gatherclass::where($class_sql)->select()->toArray();
        //判断报表类型
        $mode=input('get.mode');
        if(empty($mode)){
            //简易报表
            $tab_title=[
                'time'=>'单据日期',
                'number'=>'单据编号',
                'customer'=>'客户',
                'user'=>'制单人',
                'money'=>'单据金额',
                'type'=>'审核状态',
                'data'=>'单据备注'
            ];
            $tab_data=[];
            foreach ($arr as $key=>$vo) {
                $tmp_data=$arr[$key];
                $tmp_data['customer']=$vo['customer']['info']['name'];
                $tmp_data['user']=$vo['user']['info']['name'];
                $tmp_data['type']=$vo['type']['name'];
                $tmp_data['money']=gatherinfo::where(['pid'=>$vo['id']])->sum('total');
                array_push($tab_data,$tmp_data);
            }
            $sum_arr=get_sums($tab_data,['money']);
            //表格内容
            $arr['shell']='收款单报表';//Shell名称
            $arr['title']='收款单报表';//标题名称
            $arr['rows']=[
                'title'=>$tab_title,
                'data'=>$tab_data,
                'text_1'=>'单据总金额:'.$sum_arr['money']
            ];
            $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
            $arr['colnums']=count($tab_title);//列数
        }else{
            foreach ($arr as $key=>$vo) {
                //构造数据
                $tab_title=[
                    'account'=>'结算账户',
                    'total'=>'结算金额',
                    'data'=>'备注信息'
                ];
                //表格内容
                $tab_data=gatherinfo::where(['pid'=>$vo['id']])->select()->toArray();
                foreach($tab_data as $k=>$data_vo){
                    $tab_data[$k]['account']=$data_vo['account']['info']['name'];
                }
                $arr[$key]['shell']=$vo['number'];//Shell名称
                $arr[$key]['title']='收 款 单';//标题名称
                $arr[$key]['rows']=[
                    'text_1'=>'客户:'.$vo['customer']['info']['name'].'||'.'单据日期:'.$vo['time'].'||'.'单据编号:'.$vo['number'],
                    'title'=>$tab_title,
                    'data'=>$tab_data,
                    'text_2'=>'制单人:'.$vo['user']['info']['name'].'||'.'备注信息:'.$vo['data'],
                ];
                $arr[$key]['rownums']=count($arr[$key]['rows'])+count($tab_data)-1;//行数
                $arr[$key]['colnums']=count($tab_title);//列数
            }
        }
        if(empty($mode)){
            ExportExcel('收款单',[$arr]);
        }else{
            if(count($arr)>32){
                $file_name_arr=[];
                $chunk_arr=array_chunk($arr,32);
                foreach ($chunk_arr as $key=>$chunk_arr_vo) {
                    array_push($file_name_arr,'skin/tmp_file/xls/'.ExportExcel('SKD',$chunk_arr_vo,[true,$key+1]).'.xls');
                }
                $zip_name=strtoupper('SKD');
                file_to_zip($zip_name,$file_name_arr);
            }else{
                ExportExcel('收款单',$arr);
            }
        }
        
    }
    //盘点数据
    public function room_check(){
        if(!empty(input('get.name'))){
            $goods_sql['name|py']=['like','%'.input('get.name').'%'];
        }
        if(!empty(input('get.number'))){
            $goods_sql['number']=['like','%'.input('get.number').'%'];
        }
        if(!empty(input('get.location'))){
            $goods_sql['location']=['like','%'.input('get.location').'%'];
        }
        if(!empty(input('get.spec'))){
            $goods_sql['spec'] = ['like','%'.input('get.spec').'%'];
        }
        //判断商品类型
        if(!empty(input('get.class'))){
            $goods_sql['class'] = ['in',goodsclass_more_arr(input('get.class')),'OR'];
        }
        //判断商品单位
        if(!empty(input('get.unit'))){
            $goods_sql['unit'] = input('get.unit');
        }
        //判断所属品牌
        if(!empty(input('get.brand'))){
            $goods_sql['brand'] = input('get.brand');
        }
        if(isset($goods_sql)){
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $sql['goods']=['in',$goods_arr,'OR'];
        }
        //判断所属仓库
        if(!empty(input('get.warehouse'))){
            $sql['warehouse'] = input('get.warehouse');
        }
        if(isset($sql)){
            $tab_data=room::where($sql)->select()->toArray();
        }else{
            $tab_data=room::select()->toArray();
        }
        foreach ($tab_data as $key=>$vo) {
            $tab_data[$key]['warehouse_name']=$vo['warehouse']['info']['name'];
            $tab_data[$key]['goods_name']=$vo['goods']['info']['name'];
            $tab_data[$key]['attr_name']=$vo['attr']['name'];
            $tab_data[$key]['brand']=$vo['goods']['info']['brand']['info']['name'];
            $tab_data[$key]['number']=$vo['goods']['info']['number'];
            $tab_data[$key]['class']=$vo['goods']['info']['class']['info']['name'];
            $tab_data[$key]['spec']=$vo['goods']['info']['spec'];
            $tab_data[$key]['code']=$vo['goods']['info']['code'];
            $tab_data[$key]['unit']=$vo['goods']['info']['unit']['info']['name'];
            $tab_data[$key]['stocktip']=$vo['goods']['info']['stocktip'];
            $tab_data[$key]['location']=$vo['goods']['info']['location'];
            $tab_data[$key]['integral']=$vo['goods']['info']['integral'];
            $tab_data[$key]['retail_name']=$vo['goods']['info']['retail_name'];
            $tab_data[$key]['batch']=$vo['batch'];
            $tab_data[$key]['serial']=implode('|',arrayChange(serial::where(['room'=>$vo['id'],'type'=>0])->field('code')->select()->toArray(),'code'));
            $tab_data[$key]['stock']=$vo['nums'];
            $tab_data[$key]['nums']='';
        }
        //构造数据
        $tab_title=[
            'warehouse_name'=>'所属仓库',
            'goods_name'=>'商品名称',
            'attr_name'=>'辅助属性',
            'brand'=>'商品品牌',
            'number'=>'商品编号',
            'class'=>'商品分类',
            'spec'=>'规格型号',
            'code'=>'条形码',
            'unit'=>'商品单位',
            'stocktip'=>'库存预警',
            'location'=>'商品货位',
            'integral'=>'赠送积分',
            'retail_name'=>'零售名称',
            'batch'=>'商品批次',
            'serial'=>'商品串码',
            'stock'=>'当前库存',
            'nums'=>'盘点库存'
        ];
        //字段设置
        $tabinfo=tabinfo::get(['name'=>'room_check']);
        $tabmain=json_decode($tabinfo['main'],true);
        foreach ($tabmain as $main_vo) {
            if(empty($main_vo[key($main_vo)][1])){
                unset($tab_title[key($main_vo)]);//删除
            }
        }
        $sys=sys::get(['id'=>2]);
        if(empty($sys['info']['serial'])){
            unset($tab_title['serial']);//删除串号
        }
        if(empty($sys['info']['batch'])){
            unset($tab_title['batch']);//删除批次
        }
        //表格内容
        $arr['shell']='盘点表';//Shell名称
        $arr['title']='盘点表';//标题名称
        $arr['rows']=[
            'title'=>$tab_title,
            'data'=>$tab_data,
        ];
        $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
        $arr['colnums']=count($tab_title);//列数
        ExportExcel('盘点表',[$arr]);
    }
    //导出收款单信息
    public function payment(){
        //按照结算账户搜索
        if(!empty(input('get.account'))){
            $info_sql['account']=input('get.account');
        }
        //info条件转class条件
        if(isset($info_sql)){
            $class_sql['id'] = ['in',arrayChange(paymentinfo::where($info_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
        }else{
            //默认数据鉴权处理
            $user_auth=json_decode(user_info('auth'),true);
            if(!empty($user_auth) && !empty($user_auth['account'])){    
                $auth_sql['account']=['in',$user_auth['account'],'OR'];
                $class_sql['id'] = ['in',arrayChange(paymentinfo::where($auth_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
            }
        }
        //按照编号搜索
        $class_sql['number']=['like','%'.input('get.number').'%'];
        //按照备注信息搜索
        $class_sql['data']=['like','%'.input('get.data').'%'];
        //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $class_sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $class_sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $class_sql['time']=[$egt,$elt];
        }
        //按照制单人搜索
        if(!empty(input('get.user'))){
            $class_sql['user']=input('get.user');
        }
        //按照收款客户搜索
        if(!empty(input('get.supplier'))){
            $class_sql['supplier']=input('get.supplier');
        }
        //按照审核状态搜索
        if(!empty(input('get.type'))){
            $class_sql['type']=input('get.type')-1;
        }
        $arr = paymentclass::where($class_sql)->select()->toArray();
        //判断报表类型
        $mode=input('get.mode');
        if(empty($mode)){
            //简易报表
            $tab_title=[
                'time'=>'单据日期',
                'number'=>'单据编号',
                'supplier'=>'供应商',
                'user'=>'制单人',
                'money'=>'单据金额',
                'type'=>'审核状态',
                'data'=>'单据备注'
            ];
            $tab_data=[];
            foreach ($arr as $key=>$vo) {
                $tmp_data=$arr[$key];
                $tmp_data['supplier']=$vo['supplier']['info']['name'];
                $tmp_data['user']=$vo['user']['info']['name'];
                $tmp_data['type']=$vo['type']['name'];
                $tmp_data['money']=paymentinfo::where(['pid'=>$vo['id']])->sum('total');
                array_push($tab_data,$tmp_data);
            }
            $sum_arr=get_sums($tab_data,['money']);
            //表格内容
            $arr['shell']='付款单报表';//Shell名称
            $arr['title']='付款单报表';//标题名称
            $arr['rows']=[
                'title'=>$tab_title,
                'data'=>$tab_data,
                'text_1'=>'单据总金额:'.$sum_arr['money']
            ];
            $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
            $arr['colnums']=count($tab_title);//列数
        }else{
            foreach ($arr as $key=>$vo) {
                //构造数据
                $tab_title=[
                    'account'=>'结算账户',
                    'total'=>'结算金额',
                    'data'=>'备注信息'
                ];
                //表格内容
                $tab_data=paymentinfo::where(['pid'=>$vo['id']])->select()->toArray();
                foreach($tab_data as $k=>$data_vo){
                    $tab_data[$k]['account']=$data_vo['account']['info']['name'];
                }
                $arr[$key]['shell']=$vo['number'];//Shell名称
                $arr[$key]['title']='付 款 单';//标题名称
                $arr[$key]['rows']=[
                    'text_1'=>'供应商:'.$vo['supplier']['info']['name'].'||'.'单据日期:'.$vo['time'].'||'.'单据编号:'.$vo['number'],
                    'title'=>$tab_title,
                    'data'=>$tab_data,
                    'text_2'=>'制单人:'.$vo['user']['info']['name'].'||'.'备注信息:'.$vo['data'],
                ];
                $arr[$key]['rownums']=count($arr[$key]['rows'])+count($tab_data)-1;//行数
                $arr[$key]['colnums']=count($tab_title);//列数
                
            }
        }
        if(empty($mode)){
            ExportExcel('付款单',[$arr]);
        }else{
            if(count($arr)>32){
                $file_name_arr=[];
                $chunk_arr=array_chunk($arr,32);
                foreach ($chunk_arr as $key=>$chunk_arr_vo) {
                    array_push($file_name_arr,'skin/tmp_file/xls/'.ExportExcel('FKD',$chunk_arr_vo,[true,$key+1]).'.xls');
                }
                $zip_name=strtoupper('FKD');
                file_to_zip($zip_name,$file_name_arr);
            }else{
                ExportExcel('付款单',$arr);;
            }
        }
    }
    //导出其他收入单信息
    public function otgather(){
        //按照结算账户搜索
        if(!empty(input('get.account'))){
            $info_sql['account']=input('get.account');
        }
        //info条件转class条件
        if(isset($info_sql)){
            $class_sql['id'] = ['in',arrayChange(otgatherinfo::where($info_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
        }else{
            //默认数据鉴权处理
            $user_auth=json_decode(user_info('auth'),true);
            if(!empty($user_auth) && !empty($user_auth['account'])){    
                $auth_sql['account']=['in',$user_auth['account'],'OR'];
                $class_sql['id'] = ['in',arrayChange(otgatherinfo::where($auth_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
            }
        }
        //按照编号搜索
        $class_sql['number']=['like','%'.input('get.number').'%'];
        $class_sql['data']=['like','%'.input('get.data').'%'];
        //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $class_sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $class_sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $class_sql['time']=[$egt,$elt];
        }
        //按照制单人搜索
        if(!empty(input('get.user'))){
            $class_sql['user']=input('get.user');
        }
        //按照审核状态搜索
        if(!empty(input('get.type'))){
            $class_sql['type']=input('get.type')-1;
        }
        $arr = otgatherclass::where($class_sql)->select()->toArray();
        //判断报表类型
        $mode=input('get.mode');
        if(empty($mode)){
            //简易报表
            $tab_title=[
                'time'=>'单据日期',
                'number'=>'单据编号',
                'user'=>'制单人',
                'money'=>'单据金额',
                'type'=>'审核状态',
                'data'=>'单据备注'
            ];
            $tab_data=[];
            foreach ($arr as $key=>$vo) {
                $tmp_data=$arr[$key];
                $tmp_data['user']=$vo['user']['info']['name'];
                $tmp_data['type']=$vo['type']['name'];
                $tmp_data['money']=otgatherinfo::where(['pid'=>$vo['id']])->sum('total');
                array_push($tab_data,$tmp_data);
            }
            $sum_arr=get_sums($tab_data,['money']);
            //表格内容
            $arr['shell']='其他收入单报表';//Shell名称
            $arr['title']='其他收入单报表';//标题名称
            $arr['rows']=[
                'title'=>$tab_title,
                'data'=>$tab_data,
                'text_1'=>'单据总金额:'.$sum_arr['money']
            ];
            $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
            $arr['colnums']=count($tab_title);//列数
        }else{
            foreach ($arr as $key=>$vo) {
                //构造数据
                $tab_title=[
                    'account'=>'结算账户',
                    'total'=>'结算金额',
                    'data'=>'备注信息'
                ];
                //表格内容
                $tab_data=otgatherinfo::where(['pid'=>$vo['id']])->select()->toArray();
                foreach($tab_data as $k=>$data_vo){
                    $tab_data[$k]['account']=$data_vo['account']['info']['name'];
                }
                $arr[$key]['shell']=$vo['number'];//Shell名称
                $arr[$key]['title']='其 他 收 入 单';//标题名称
                $arr[$key]['rows']=[
                    'text_1'=>'单据日期:'.$vo['time'].'||'.'单据编号:'.$vo['number'],
                    'title'=>$tab_title,
                    'data'=>$tab_data,
                    'text_2'=>'制单人:'.$vo['user']['info']['name'].'||'.'备注信息:'.$vo['data'],
                ];
                $arr[$key]['rownums']=count($arr[$key]['rows'])+count($tab_data)-1;//行数
                $arr[$key]['colnums']=count($tab_title);//列数
            }
        }
        if(empty($mode)){
            ExportExcel('其他收入单',[$arr]);
        }else{
            if(count($arr)>32){
                $file_name_arr=[];
                $chunk_arr=array_chunk($arr,32);
                foreach ($chunk_arr as $key=>$chunk_arr_vo) {
                    array_push($file_name_arr,'skin/tmp_file/xls/'.ExportExcel('QTSRD',$chunk_arr_vo,[true,$key+1]).'.xls');
                }
                $zip_name=strtoupper('QTSRD');
                file_to_zip($zip_name,$file_name_arr);
            }else{
                ExportExcel('其他收入单',$arr);
            }
        }
        
        
    }
    //导出其他支出单信息
    public function otpayment(){
        //按照结算账户搜索
        if(!empty(input('get.account'))){
            $info_sql['account']=input('get.account');
        }
        //info条件转class条件
        if(isset($info_sql)){
            $class_sql['id'] = ['in',arrayChange(otpaymentinfo::where($info_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
        }else{
            //默认数据鉴权处理
            $user_auth=json_decode(user_info('auth'),true);
            if(!empty($user_auth) && !empty($user_auth['account'])){    
                $auth_sql['account']=['in',$user_auth['account'],'OR'];
                $class_sql['id'] = ['in',arrayChange(otpaymentinfo::where($auth_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
            }
        }
        //按照编号搜索
        $class_sql['number']=['like','%'.input('get.number').'%'];
        $class_sql['data']=['like','%'.input('get.data').'%'];
        //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $class_sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $class_sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $class_sql['time']=[$egt,$elt];
        }
        //按照制单人搜索
        if(!empty(input('get.user'))){
            $class_sql['user']=input('get.user');
        }
        //按照审核状态搜索
        if(!empty(input('get.type'))){
            $class_sql['type']=input('get.type')-1;
        }
        $arr = otpaymentclass::where($class_sql)->select()->toArray();
        //判断报表类型
        $mode=input('get.mode');
        if(empty($mode)){
            //简易报表
            $tab_title=[
                'time'=>'单据日期',
                'number'=>'单据编号',
                'user'=>'制单人',
                'money'=>'单据金额',
                'type'=>'审核状态',
                'data'=>'单据备注'
            ];
            $tab_data=[];
            foreach ($arr as $key=>$vo) {
                $tmp_data=$arr[$key];
                $tmp_data['user']=$vo['user']['info']['name'];
                $tmp_data['type']=$vo['type']['name'];
                $tmp_data['money']=otpaymentinfo::where(['pid'=>$vo['id']])->sum('total');
                array_push($tab_data,$tmp_data);
            }
            $sum_arr=get_sums($tab_data,['money']);
            //表格内容
            $arr['shell']='其他支出单报表';//Shell名称
            $arr['title']='其他支出单报表';//标题名称
            $arr['rows']=[
                'title'=>$tab_title,
                'data'=>$tab_data,
                'text_1'=>'单据总金额:'.$sum_arr['money']
            ];
            $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
            $arr['colnums']=count($tab_title);//列数
        }else{
            foreach ($arr as $key=>$vo) {
                //构造数据
                $tab_title=[
                    'account'=>'结算账户',
                    'total'=>'结算金额',
                    'data'=>'备注信息'
                ];
                //表格内容
                $tab_data=otpaymentinfo::where(['pid'=>$vo['id']])->select()->toArray();
                foreach($tab_data as $k=>$data_vo){
                    $tab_data[$k]['account']=$data_vo['account']['info']['name'];
                }
                $arr[$key]['shell']=$vo['number'];//Shell名称
                $arr[$key]['title']='其 他 支 出 单';//标题名称
                $arr[$key]['rows']=[
                    'text_1'=>'单据日期:'.$vo['time'].'||'.'单据编号:'.$vo['number'],
                    'title'=>$tab_title,
                    'data'=>$tab_data,
                    'text_2'=>'制单人:'.$vo['user']['info']['name'].'||'.'备注信息:'.$vo['data'],
                ];
                $arr[$key]['rownums']=count($arr[$key]['rows'])+count($tab_data)-1;//行数
                $arr[$key]['colnums']=count($tab_title);//列数
                
            }
        }
        if(empty($mode)){
            ExportExcel('其他支出单',[$arr]);
        }else{
            if(count($arr)>32){
                $file_name_arr=[];
                $chunk_arr=array_chunk($arr,32);
                foreach ($chunk_arr as $key=>$chunk_arr_vo) {
                    array_push($file_name_arr,'skin/tmp_file/xls/'.ExportExcel('QTZCD',$chunk_arr_vo,[true,$key+1]).'.xls');
                }
                $zip_name=strtoupper('QTZCD');
                file_to_zip($zip_name,$file_name_arr);
            }else{
                ExportExcel('其他支出单',$arr);
            }
        }
    }
    //导出零售销货单信息
    public function cashier(){
        //按照名称搜索
        if(!empty(input('get.goods'))){
            $goods_sql['name|py']=['like','%'.input('get.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $info_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('get.warehouse'))){
            $info_sql['warehouse']=input('get.warehouse');
        }
        //info条件转class条件
        if(isset($info_sql)){
            $class_sql['id'] = ['in',arrayChange(cashierinfo::where($info_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
        }else{
            //默认数据鉴权处理
            $user_auth=json_decode(user_info('auth'),true);
            if(!empty($user_auth) && !empty($user_auth['warehouse'])){    
                $auth_sql['warehouse']=['in',$user_auth['warehouse'],'OR'];
                $class_sql['id'] = ['in',arrayChange(cashierinfo::where($auth_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
            }
        }
        //按照编号搜索
        $class_sql['number']=['like','%'.input('get.number').'%'];
        $class_sql['data']=['like','%'.input('get.data').'%'];
        //按照客户搜索
        if(!empty(input('get.customer'))){
            $class_sql['customer']=input('get.customer');
        }
        //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $class_sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $class_sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $class_sql['time']=[$egt,$elt];
        }
        //按照制单人搜索
        if(!empty(input('get.user'))){
            $class_sql['user']=input('get.user');
        }
        //按照结算账户搜索
        if(!empty(input('get.account'))){
            $class_sql['account']=input('get.account');
        }
        //按照审核状态搜索
        if(!empty(input('get.type'))){
            $class_sql['type']=input('get.type')-1;
        }
        $arr = cashierclass::where($class_sql)->select()->toArray();
        //判断报表类型
        $mode=input('get.mode');
        if(empty($mode)){
            //简易报表
            $tab_title=[
                'time'=>'单据日期',
                'number'=>'单据编号',
                'customer'=>'购买客户',
                'account'=>'结算账户',
                'user'=>'制单人',
                'type'=>'审核状态',
                'total'=>'单据金额',
                'discount'=>'优惠金额',
                'money'=>'实收金额',
                'data'=>'单据备注'
            ];
            $tab_data=[];
            foreach ($arr as $key=>$vo) {
                $tmp_data=$arr[$key];
                $tmp_data['customer']=$vo['customer']['info']['name'];
                $tmp_data['account']=$vo['account']['info']['name'];
                $tmp_data['user']=$vo['user']['info']['name'];
                $tmp_data['type']=$vo['type']['name'];
                array_push($tab_data,$tmp_data);
            }
            $sum_arr=get_sums($tab_data,['total','discount','money']);
            //表格内容
            $arr['shell']='零售销货单报表';//Shell名称
            $arr['title']='零售销货单报表';//标题名称
            $arr['rows']=[
                'title'=>$tab_title,
                'data'=>$tab_data,
                'text_1'=>'单据总金额:'.$sum_arr['total'].' | '.'优惠总金额:'.$sum_arr['discount'].' | '.'实收总金额:'.$sum_arr['money']
            ];
            $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
            $arr['colnums']=count($tab_title);//列数
        }else{
            foreach ($arr as $key=>$vo) {
                //构造数据
                $tab_title=[
                        'name'=>'商品信息',
                        'attr'=>'辅助属性',
                        'warehouse'=>'所属仓库',
                        'brand'=>'商品品牌',
                        'number'=>'商品编号',
                        'class'=>'商品分类',
                        'spec'=>'规格型号',
                        'code'=>'条形码',
                        'unit'=>'商品单位',
                        'stocktip'=>'库存预警',
                        'location'=>'商品货位',
                        'integral'=>'赠送积分',
                        'serial'=>'商品串码',
                        'nums'=>'数量',
                        'price'=>'销货单价',
                        'discount'=>'折扣额',
                        'total'=>'销货金额',
                        'batch'=>'商品批次',
                        'data'=>'备注信息'
                ];
                //字段设置
                $tabinfo=tabinfo::get(['name'=>'cashier']);
                $tabmain=json_decode($tabinfo['main'],true);
                foreach ($tabmain as $main_vo) {
                    if(empty($main_vo[key($main_vo)][1])){
                        unset($tab_title[key($main_vo)]);//删除
                    }
                }
                $sys=sys::get(['id'=>2]);
                if(empty($sys['info']['serial'])){
                    unset($tab_title['serial']);//删除串号
                }
                if(empty($sys['info']['batch'])){
                    unset($tab_title['batch']);//删除批次
                }
                //表格内容
                $tab_data=cashierinfo::where(['pid'=>$vo['id']])->select()->toArray();
                foreach($tab_data as $k=>$data_vo){
                    $tab_data[$k]['name']=$data_vo['room']['info']['goods']['info']['name'];
                    $tab_data[$k]['attr']=$data_vo['room']['info']['attr']['name'];
                    $tab_data[$k]['warehouse']=$data_vo['room']['info']['warehouse']['info']['name'];
                    $tab_data[$k]['brand']=$data_vo['room']['info']['goods']['info']['brand']['info']['name'];
                    $tab_data[$k]['number']=$data_vo['room']['info']['goods']['info']['number'];
                    $tab_data[$k]['class']=$data_vo['room']['info']['goods']['info']['class']['info']['name'];
                    $tab_data[$k]['spec']=$data_vo['room']['info']['goods']['info']['spec'];
                    $tab_data[$k]['code']=' '.$data_vo['room']['info']['goods']['info']['code'];//转字符串
                    $tab_data[$k]['unit']=$data_vo['room']['info']['goods']['info']['unit']['info']['name'];
                    $tab_data[$k]['stocktip']=$data_vo['room']['info']['goods']['info']['stocktip'];
                    $tab_data[$k]['location']=$data_vo['room']['info']['goods']['info']['location'];
                    $tab_data[$k]['integral']=$data_vo['room']['info']['goods']['info']['integral'];
                    $tab_data[$k]['batch']=$data_vo['room']['info']['batch'];
                }
                $arr[$key]['shell']=$vo['number'];//Shell名称
                $arr[$key]['title']='零 售 销 货 单';//标题名称
                $arr[$key]['rows']=[
                    'text_1'=>'购买客户:'.$vo['customer']['info']['name'].'||'.'单据日期:'.$vo['time'].'||'.'单据编号:'.$vo['number'],
                    'title'=>$tab_title,
                    'data'=>$tab_data,
                    'text_2'=>'单据金额:'.$vo['total'].'||'.'优惠金额:'.$vo['discount'].'||'.'实收金额:'.$vo['money'].'||'.'本次积分:'.$vo['integral'],
                    'text_3'=>'制单人:'.$vo['user']['info']['name'].'||'.'结算账户:'.$vo['account']['info']['name'].'||'.'备注信息:'.$vo['data'],
                ];
                $arr[$key]['rownums']=count($arr[$key]['rows'])+count($tab_data)-1;//行数
                $arr[$key]['colnums']=count($tab_title);//列数
                
            }
        }
        if(empty($mode)){
            ExportExcel('零售销货单',[$arr]);
        }else{
            if(count($arr)>32){
                $file_name_arr=[];
                $chunk_arr=array_chunk($arr,32);
                foreach ($chunk_arr as $key=>$chunk_arr_vo) {
                    array_push($file_name_arr,'skin/tmp_file/xls/'.ExportExcel('LSXHD',$chunk_arr_vo,[true,$key+1]).'.xls');
                }
                $zip_name=strtoupper('LSXHD');
                file_to_zip($zip_name,$file_name_arr);
            }else{
                ExportExcel('零售销货单',$arr);
            }
        }
    }
    //导出零售退货单信息
    public function recashier(){
        //按照名称搜索
        if(!empty(input('get.goods'))){
            $goods_sql['name|py']=['like','%'.input('get.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $recashierinfo_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('get.warehouse'))){
            $recashierinfo_sql['warehouse']=input('get.warehouse');
        }
        
        if(isset($recashierinfo_sql)){
            $recashierinfo_arr=arrayChange(recashierinfo::where($recashierinfo_sql)->field('pid')->select()->toArray(),'pid');
            $sql['id']=['in',$recashierinfo_arr,'OR'];
        }else{
            //默认数据鉴权处理
            $user_auth=json_decode(user_info('auth'),true);
            if(!empty($user_auth) && !empty($user_auth['warehouse'])){    
                $auth_sql['warehouse']=['in',$user_auth['warehouse'],'OR'];
                $sql['id'] = ['in',arrayChange(recashierinfo::where($auth_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
            }
        }
        
        //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $sql['time']=[$egt,$elt];
        }
        //按照购买客户搜索
        if(!empty(input('get.customer'))){
            $sql['customer']=input('get.customer');
        }
        //按照制单人搜索
        if(!empty(input('get.user'))){
            $sql['user']=input('get.user');
        }
        //按照结算账户搜索
        if(!empty(input('get.account'))){
            $sql['account']=input('get.account');
        }
        //按照审核状态搜索
        if(!empty(input('get.type'))){
            $sql['type']=input('get.type')-1;
        }
        $sql['number'] = ['like','%'.input('get.number').'%'];
        $sql['data'] = ['like','%'.input('get.data').'%'];
        $arr = recashierclass::where($sql)->select()->toArray();
        //判断报表类型
        $mode=input('get.mode');
        if(empty($mode)){
            //简易报表
            $tab_title=[
                'time'=>'单据日期',
                'number'=>'单据编号',
                'customer'=>'购买客户',
                'account'=>'结算账户',
                'user'=>'制单人',
                'type'=>'审核状态',
                'total'=>'单据金额',
                'money'=>'实付金额',
                'data'=>'单据备注'
            ];
            $tab_data=[];
            foreach ($arr as $key=>$vo) {
                $tmp_data=$arr[$key];
                $tmp_data['customer']=$vo['customer']['info']['name'];
                $tmp_data['account']=$vo['account']['info']['name'];
                $tmp_data['user']=$vo['user']['info']['name'];
                $tmp_data['type']=$vo['type']['name'];
                array_push($tab_data,$tmp_data);
            }
            $sum_arr=get_sums($tab_data,['total','money']);
            //表格内容
            $arr['shell']='零售退货单报表';//Shell名称
            $arr['title']='零售退货单报表';//标题名称
            $arr['rows']=[
                'title'=>$tab_title,
                'data'=>$tab_data,
                'text_1'=>'单据总金额:'.$sum_arr['total'].' | '.'实付总金额:'.$sum_arr['money']
            ];
            $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
            $arr['colnums']=count($tab_title);//列数
        }else{
            foreach ($arr as $key=>$vo) {
                //构造数据
                $tab_title=[    
                        'name'=>'商品信息',
                        'attr'=>'辅助属性',
                        'warehouse'=>'所属仓库',
                        'brand'=>'商品品牌',
                        'number'=>'商品编号',
                        'class'=>'商品分类',
                        'spec'=>'规格型号',
                        'code'=>'条形码',
                        'unit'=>'商品单位',
                        'stocktip'=>'库存预警',
                        'location'=>'商品货位',
                        'integral'=>'赠送积分',
                        'serial'=>'商品串码',
                        'nums'=>'数量',
                        'price'=>'销货单价',
                        'total'=>'销货金额',
                        'batch'=>'商品批次',
                        'data'=>'备注信息'
                ];
                //字段设置
                $tabinfo=tabinfo::get(['name'=>'recashier']);
                $tabmain=json_decode($tabinfo['main'],true);
                foreach ($tabmain as $main_vo) {
                    if(empty($main_vo[key($main_vo)][1])){
                        unset($tab_title[key($main_vo)]);//删除
                    }
                }
                $sys=sys::get(['id'=>2]);
                if(empty($sys['info']['serial'])){
                    unset($tab_title['serial']);//删除串号
                }
                if(empty($sys['info']['batch'])){
                    unset($tab_title['batch']);//删除批次
                }
                //表格内容
                $tab_data=recashierinfo::where(['pid'=>$vo['id']])->select()->toArray();
                foreach($tab_data as $k=>$data_vo){
                    $tab_data[$k]['name']=$data_vo['room']['info']['goods']['info']['name'];
                    $tab_data[$k]['attr']=$data_vo['room']['info']['attr']['name'];
                    $tab_data[$k]['warehouse']=$data_vo['room']['info']['warehouse']['info']['name'];
                    $tab_data[$k]['brand']=$data_vo['room']['info']['goods']['info']['brand']['info']['name'];
                    $tab_data[$k]['number']=$data_vo['room']['info']['goods']['info']['number'];
                    $tab_data[$k]['class']=$data_vo['room']['info']['goods']['info']['class']['info']['name'];
                    $tab_data[$k]['spec']=$data_vo['room']['info']['goods']['info']['spec'];
                    $tab_data[$k]['code']=' '.$data_vo['room']['info']['goods']['info']['code'];//转字符串
                    $tab_data[$k]['unit']=$data_vo['room']['info']['goods']['info']['unit']['info']['name'];
                    $tab_data[$k]['stocktip']=$data_vo['room']['info']['goods']['info']['stocktip'];
                    $tab_data[$k]['location']=$data_vo['room']['info']['goods']['info']['location'];
                    $tab_data[$k]['integral']=$data_vo['room']['info']['goods']['info']['integral'];
                    $tab_data[$k]['batch']=$data_vo['room']['info']['batch'];
                }
                $arr[$key]['shell']=$vo['number'];//Shell名称
                $arr[$key]['title']='零 售 退 货 单';//标题名称
                $arr[$key]['rows']=[
                    'text_1'=>'购买客户:'.$vo['customer']['info']['name'].'||'.'单据日期:'.$vo['time'].'||'.'单据编号:'.$vo['number'],
                    'title'=>$tab_title,
                    'data'=>$tab_data,
                    'text_2'=>'单据金额:'.$vo['total'].'||'.'实付金额:'.$vo['money'].'||'.'扣除积分:'.$vo['integral'],
                    'text_3'=>'制单人:'.$vo['user']['info']['name'].'||'.'结算账户:'.$vo['account']['info']['name'].'||'.'备注信息:'.$vo['data'],
                ];
                $arr[$key]['rownums']=count($arr[$key]['rows'])+count($tab_data)-1;//行数
                $arr[$key]['colnums']=count($tab_title);//列数
                
            }
        }
        if(empty($mode)){
            ExportExcel('零售退货单',[$arr]);
        }else{
            if(count($arr)>32){
                $file_name_arr=[];
                $chunk_arr=array_chunk($arr,32);
                foreach ($chunk_arr as $key=>$chunk_arr_vo) {
                    array_push($file_name_arr,'skin/tmp_file/xls/'.ExportExcel('LSTHD',$chunk_arr_vo,[true,$key+1]).'.xls');
                }
                $zip_name=strtoupper('LSTHD');
                file_to_zip($zip_name,$file_name_arr);
            }else{
                ExportExcel('零售退货单',$arr);
            }
        }
    }
    //导出其他入库单信息
    public function otsale(){
        //按照名称搜索
        if(!empty(input('get.goods'))){
            $goods_sql['name|py']=['like','%'.input('get.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $info_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('get.warehouse'))){
            $info_sql['warehouse']=input('get.warehouse');
        }
        //info条件转class条件
        if(isset($info_sql)){
            $class_sql['id'] = ['in',arrayChange(otsaleinfo::where($info_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
        }else{
            //默认数据鉴权处理
            $user_auth=json_decode(user_info('auth'),true);
            if(!empty($user_auth) && !empty($user_auth['warehouse'])){    
                $auth_sql['warehouse']=['in',$user_auth['warehouse'],'OR'];
                $class_sql['id'] = ['in',arrayChange(otsaleinfo::where($auth_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
            }
        }
        //按照编号搜索
        $class_sql['number']=['like','%'.input('get.number').'%'];
        $class_sql['data']=['like','%'.input('get.data').'%'];
        //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $class_sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $class_sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $class_sql['time']=[$egt,$elt];
        }
        //按照制单人搜索
        if(!empty(input('get.user'))){
            $class_sql['user']=input('get.user');
        }
        //按照审核状态搜索
        if(!empty(input('get.type'))){
            $class_sql['type']=input('get.type')-1;
        }
         //按照单据类型搜索
        if(!empty(input('get.pagetype'))){
            $class_sql['pagetype']=input('get.pagetype')-1;
        }
        $arr = otsaleclass::where($class_sql)->select()->toArray();
        //判断报表类型
        $mode=input('get.mode');
        if(empty($mode)){
            //简易报表
            $tab_title=[
                'time'=>'单据日期',
                'number'=>'单据编号',
                'user'=>'制单人',
                'pagetype'=>'单据类型',
                'type'=>'审核状态',
                'data'=>'单据备注'
            ];
            $tab_data=[];
            foreach ($arr as $key=>$vo) {
                $tmp_data=$arr[$key];
                $tmp_data['user']=$vo['user']['info']['name'];
                $tmp_data['pagetype']=$vo['pagetype']['name'];
                $tmp_data['type']=$vo['type']['name'];
                array_push($tab_data,$tmp_data);
            }
            //表格内容
            $arr['shell']='其他入库单报表';//Shell名称
            $arr['title']='其他入库单报表';//标题名称
            $arr['rows']=[
                'title'=>$tab_title,
                'data'=>$tab_data
            ];
            $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
            $arr['colnums']=count($tab_title);//列数
        }else{
            foreach ($arr as $key=>$vo) {
                //构造数据
                $tab_title=[
                        'name'=>'商品信息',
                        'attr'=>'辅助属性',
                        'brand'=>'商品品牌',
                        'number'=>'商品编号',
                        'class'=>'商品分类',
                        'spec'=>'规格型号',
                        'code'=>'条形码',
                        'unit'=>'商品单位',
                        'stocktip'=>'库存预警',
                        'location'=>'商品货位',
                        'integral'=>'赠送积分',
                        'serial'=>'商品串码',
                        'nums'=>'数量',
                        'warehouse'=>'所出仓库',
                        'batch'=>'商品批次',
                        'data'=>'备注信息'
                ];
                //字段设置
                $tabinfo=tabinfo::get(['name'=>'otsale']);
                $tabmain=json_decode($tabinfo['main'],true);
                foreach ($tabmain as $main_vo) {
                    if(empty($main_vo[key($main_vo)][1])){
                        unset($tab_title[key($main_vo)]);//删除
                    }
                }
                $sys=sys::get(['id'=>2]);
                if(empty($sys['info']['serial'])){
                    unset($tab_title['serial']);//删除串号
                }
                if(empty($sys['info']['batch'])){
                    unset($tab_title['batch']);//删除批次
                }
                //表格内容
                $tab_data=otsaleinfo::where(['pid'=>$vo['id']])->select()->toArray();
                foreach($tab_data as $k=>$data_vo){
                    $tab_data[$k]['name']=$data_vo['room']['info']['goods']['info']['name'];
                    $tab_data[$k]['attr']=$data_vo['room']['info']['attr']['name'];
                    $tab_data[$k]['warehouse']=$data_vo['room']['info']['warehouse']['info']['name'];
                    $tab_data[$k]['brand']=$data_vo['room']['info']['goods']['info']['brand']['info']['name'];
                    $tab_data[$k]['number']=$data_vo['room']['info']['goods']['info']['number'];
                    $tab_data[$k]['class']=$data_vo['room']['info']['goods']['info']['class']['info']['name'];
                    $tab_data[$k]['spec']=$data_vo['room']['info']['goods']['info']['spec'];
                    $tab_data[$k]['code']=' '.$data_vo['room']['info']['goods']['info']['code'];//转字符串
                    $tab_data[$k]['unit']=$data_vo['room']['info']['goods']['info']['unit']['info']['name'];
                    $tab_data[$k]['stocktip']=$data_vo['room']['info']['goods']['info']['stocktip'];
                    $tab_data[$k]['location']=$data_vo['room']['info']['goods']['info']['location'];
                    $tab_data[$k]['integral']=$data_vo['room']['info']['goods']['info']['integral'];
                    $tab_data[$k]['batch']=$data_vo['room']['info']['batch'];
                }
                $arr[$key]['shell']=$vo['number'];//Shell名称
                $arr[$key]['title']='其 他 出 库 单';//标题名称
                $arr[$key]['rows']=[
                    'text_1'=>'单据日期:'.$vo['time'].'||'.'单据编号:'.$vo['number'].'||'.'单据类型:'.$vo['pagetype']['name'],
                    'title'=>$tab_title,
                    'data'=>$tab_data,
                    'text_2'=>'制单人:'.$vo['user']['info']['name'].'||'.'备注信息:'.$vo['data'],
                ];
                $arr[$key]['rownums']=count($arr[$key]['rows'])+count($tab_data)-1;//行数
                $arr[$key]['colnums']=count($tab_title);//列数
                
            }
        }
        if(empty($mode)){
            ExportExcel('其他出库单',[$arr]);
        }else{
            if(count($arr)>32){
                $file_name_arr=[];
                $chunk_arr=array_chunk($arr,32);
                foreach ($chunk_arr as $key=>$chunk_arr_vo) {
                    array_push($file_name_arr,'skin/tmp_file/xls/'.ExportExcel('QTCKD',$chunk_arr_vo,[true,$key+1]).'.xls');
                }
                $zip_name=strtoupper('QTCKD');
                file_to_zip($zip_name,$file_name_arr);
            }else{
                ExportExcel('其他出库单',$arr);
            }
        }
        
        
    }
    //导出库存预警
    public function room_warning(){
        if(!empty(input('get.name'))){
            $goods_sql['name|py']=['like','%'.input('get.name').'%'];
        }
        if(!empty(input('get.number'))){
            $goods_sql['number']=['like','%'.input('get.number').'%'];
        }
        if(!empty(input('get.location'))){
            $goods_sql['location']=['like','%'.input('get.location').'%'];
        }
        if(!empty(input('get.spec'))){
            $goods_sql['spec'] = ['like','%'.input('get.spec').'%'];
        }
        //判断商品类型
        if(!empty(input('get.class'))){
            $goods_sql['class'] = ['in',goodsclass_more_arr(input('get.class')),'OR'];
        }
        //判断商品单位
        if(!empty(input('get.unit'))){
            $goods_sql['unit'] = input('get.unit');
        }
        //判断所属品牌
        if(!empty(input('get.brand'))){
            $goods_sql['brand'] = input('get.brand');
        }
        if(isset($goods_sql)){
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $sql['goods']=['in',$goods_arr,'OR'];
        }
        //判断所属仓库
        if(!empty(input('get.warehouse'))){
            $sql['warehouse'] = input('get.warehouse');
        }
        if(isset($sql)){
            $tab_data=room::where($sql)->select()->toArray();
        }else{
            $tab_data=room::select()->toArray();
        }
        foreach ($tab_data as $key=>$vo) {
            $tab_data[$key]['name']=$vo['goods']['info']['name'];
            $tab_data[$key]['attr']=$vo['attr']['name'];
            $tab_data[$key]['warehouse']=$vo['warehouse']['info']['name'];
            $tab_data[$key]['unit']=$vo['goods']['info']['unit']['info']['name'];
            $tab_data[$key]['class']=$vo['goods']['info']['class']['info']['name'];
            $tab_data[$key]['spec']=$vo['goods']['info']['spec'];
            $tab_data[$key]['number']=$vo['goods']['info']['number'];
            $tab_data[$key]['location']=$vo['goods']['info']['location'];
            $tab_data[$key]['brand']=$vo['goods']['info']['brand']['info']['name'];
            $tab_data[$key]['serial']=implode('|',arrayChange(serial::where(['room'=>$vo['id'],'type'=>0])->field('code')->select()->toArray(),'code'));
            
            if(empty($vo['attr']['ape'])){
                //取默认-预警阈值
                $tab_data[$key]['stocktip']=$vo['goods']['info']['stocktip'];
            }else{
                //取辅助属性-预警阈值
                $attr=attr::where(['pid'=>$vo['goods']['ape'],'ape'=>$vo['attr']['ape'],'enable'=>1])->find();
                //兼容辅助属性被修改
                if($attr){
                    $tab_data[$key]['stocktip']=$attr['stocktip'];
                }else{
                    $tab_data[$key]['stocktip']=$vo['goods']['info']['stocktip'];
                }
            }
            //判断预警阈值
            if($vo['nums']>=$tab_data[$key]['stocktip']){
                unset($tab_data[$key]);
            }
        }
        //构造数据
        $tab_title=[
            'name'=>'商品名称',
            'attr'=>'辅助属性',
            'warehouse'=>'所属仓库',
            'unit'=>'商品单位',
            'serial'=>'商品串码',
            'batch'=>'商品批次',
            'class'=>'所属分类',
            'spec'=>'规格型号',
            'number'=>'商品编号',
            'location'=>'商品货位',
            'brand'=>'商品品牌',
            'stocktip'=>'预警阈值',
            'nums'=>'库存数量',
        ];
        //字段设置
        $tabinfo=tabinfo::get(['name'=>'room_warning']);
        $tabmain=json_decode($tabinfo['main'],true);
        foreach ($tabmain as $main_vo) {
            if(empty($main_vo[key($main_vo)][1])){
                unset($tab_title[key($main_vo)]);//删除
            }
        }
        $sys=sys::get(['id'=>2]);
        if(empty($sys['info']['serial'])){
            unset($tab_title['serial']);//删除串号
        }
        if(empty($sys['info']['batch'])){
            unset($tab_title['batch']);//删除批次
        }
        $sum_arr=get_sums($tab_data,['nums']);
        //表格内容
        $arr['shell']='库存预警详情';//Shell名称
        $arr['title']='库存预警详情';//标题名称
        $arr['rows']=[
            'title'=>$tab_title,
            'data'=>$tab_data,
            'text_1'=>'库存总数量:'.$sum_arr['nums']
        ];
        $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
        $arr['colnums']=count($tab_title);//列数
        ExportExcel('库存预警详情',[$arr]);
    }
    //导出往来单位欠款表
    public function arrears_form(){
        //判断单位类型
        if(empty(input('get.type'))){
            //客户
            $type_name='客户';
            $tab_data=[];
            $sql['name']=['like','%'.input('get.name').'%'];
            $sql['number']=['like','%'.input('get.number').'%'];
            $customer=customer::where($sql)->select();
            foreach ($customer as $customer_vo) {
                $money=0;
                $class_sql['billtype']=['in',[0,1],'OR'];
                $class_sql['customer']=$customer_vo['id'];
                $class_arr=saleclass::where($class_sql)->select();
                foreach ($class_arr as $class_vo) {
                    $money+=$class_vo['total']-$class_vo['discount']-$class_vo['money'];
                }
                //转存数据
                $tmp['number']=$customer_vo['number'];
                $tmp['name']=$customer_vo['name'];
                $tmp['type']='客户';
                $tmp['money']=$money;
                array_push($tab_data,$tmp);
            }
        }else{
            //供应商
            $type_name='供应商';
            $tab_data=[];
            $sql['name']=['like','%'.input('get.name').'%'];
            $sql['number']=['like','%'.input('get.number').'%'];
            $supplier=supplier::where($sql)->select();
            foreach ($supplier as $supplier_vo) {
                $money=0;
                $class_sql['billtype']=['in',[0,1],'OR'];
                $class_sql['supplier']=$supplier_vo['id'];
                $class_arr=purchaseclass::where($class_sql)->select();
                foreach ($class_arr as $class_vo) {
                    $money+=$class_vo['actual']-$class_vo['money'];
                }
                //转存数据
                $tmp['number']=$supplier_vo['number'];
                $tmp['name']=$supplier_vo['name'];
                $tmp['type']='供应商';
                $tmp['money']=$money;
                array_push($tab_data,$tmp);
            }
        }
        foreach($tab_data as $k=>$data_vo){
            $tab_data[$k]['number']=' '.$data_vo['number'];//转字符串
        }
        //构造数据
        $tab_title=[
            'number'=>'单位编号',
            'name'=>'单位名称',
            'type'=>'单位类型',
            'money'=>'欠款金额',
        ];
        //表格内容
        $arr['shell']='往来单位欠款表 - '.$type_name;//Shell名称
        $arr['title']='往来单位欠款表 - '.$type_name;//标题名称
        $sum_arr=get_sums($tab_data,['money']);
        $arr['rows']=[
            'title'=>$tab_title,
            'data'=>$tab_data,
            'text_1'=>'欠款总金额:'.$sum_arr['money']
        ];
        $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
        $arr['colnums']=count($tab_title);//列数
        ExportExcel('往来单位欠款表 - '.$type_name,[$arr]);
    }
    //导出销售利润表
    public function profit_form(){
        //单据类型
        $type=input('get.type');
        if(empty($type)){
            //全部
            $summary_sql['type']=['in',[4,6],'OR'];
        }elseif($type=='1'){
            //销货单
            $summary_sql['type']=4;
        }elseif($type=='2'){
            //零售单
            $summary_sql['type']=6;
        }
        //客户|数据鉴权
        $summary_sql['company']=summary_customer_sql(input('get.customer'));
        //制单人
        if(!empty(input('get.user'))){
            $summary_sql['user']=input('get.user');
        }
        //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $summary_sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $summary_sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $summary_sql['time']=[$egt,$elt];
        }
        $summary=summary::where($summary_sql)->group('number')->select()->toArray();//去除重复订单
        $list=[];
        foreach ($summary as $vo) {
            $tmp=[];
            $tmp['time']=$vo['time'];
            $type_name=[4=>'销售单',6=>'零售单'];
            $tmp['type']=$type_name[$vo['type']];
            $tmp['number']=$vo['number'];
            $tmp['customer']=$vo['company']['info']['name'];
            $tmp['user']=$vo['user']['info']['name'];
            $infos=db('summary')->where($summary_sql)->where(['number'=>$vo['number']])->select();//查询该订单下所有的info数据
            $sum_arr=get_sums($infos,['nums','total']);
            $tmp['nums']=$sum_arr['nums'];//总数量
            $tmp['sales_revenue']=opt_decimal($sum_arr['total']);//销售收入
            $tmp['selling_cost']=0;//默认销售成本
            foreach ($infos as $vo) {
                $avg=db('summary')->where(['type'=>['in',[1,2],'OR'],'room'=>$vo['room'],'price'=>['gt',0]])->avg('price');//购货单|采购单|不为零的平均价
                $tmp['selling_cost']+=$avg*$vo['nums'];
            }
            $tmp['gross_margin']=$tmp['sales_revenue']-$tmp['selling_cost'];//销售毛利=(销售收入-销售成本)
            $tmp['gross_profit_margin']=@round(($tmp['gross_margin']/$tmp['sales_revenue'])*100,2).'%';//毛利率=(销售毛利/销售收入)*100
            if($vo['type']==4){$class=db('saleclass')->find($vo['class']);
            }elseif($vo['type']==6){$class=db('cashierclass')->find($vo['class']);
            }else{exit('ERROR');}
            $tmp['discount']=opt_decimal($class['discount']);//优惠金额
            $tmp['net_profit']=$tmp['gross_margin']-$tmp['discount'];//销售净利润=(销售毛利-优惠金额)
            $tmp['net_profit_margin']=@round(($tmp['net_profit']/$tmp['sales_revenue'])*100,2).'%';//净利润率=(销售净利润/销售收入)*100
            $tmp['receivable']=$class['total']-$class['discount'];//应收金额
            $tmp['money']=opt_decimal($class['money']);//实收金额
            $tmp['data']=$vo['data'];//备注信息
            array_push($list,$tmp);
        }
        $tab_data=$list;
        //构造数据
        $tab_title=[
            'time'=>'单据日期',
            'type'=>'单据类型',
            'number'=>'单据编号',
            'customer'=>'客户',
            'user'=>'制单人',
            'nums'=>'数量',
            'sales_revenue'=>'销售收入',
            'selling_cost'=>'销售成本',
            'gross_margin'=>'销售毛利',
            'gross_profit_margin'=>'毛利率',
            'discount'=>'优惠金额',
            'net_profit'=>'销售净利润',
            'net_profit_margin'=>'净利润率',
            'receivable'=>'应收金额',
            'money'=>'实收金额',
            'data'=>'单据备注',
        ];
        //表格内容
        $arr['shell']='销售利润表';//Shell名称
        $arr['title']='销售利润表';//标题名称
        $sum_arr=get_sums($tab_data,['sales_revenue','selling_cost','gross_margin','discount','net_profit','receivable','money']);
        $arr['rows']=[
            'title'=>$tab_title,
            'data'=>$tab_data,
            'text_1'=>'销售总收入:'.$sum_arr['sales_revenue'].' | '.'销售总成本:'.$sum_arr['selling_cost'].' | '.'销售总毛利:'.$sum_arr['gross_margin'].'优惠总金额:'.$sum_arr['discount'].'销售总净利润:'.$sum_arr['net_profit'].'应收总金额:'.$sum_arr['receivable'].'实收总金额:'.$sum_arr['money']
        ];
        $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
        $arr['colnums']=count($tab_title);//列数
        ExportExcel('销售利润表',[$arr]);
    }
    //导出采购订单信息
    public function opurchase(){
        //按照名称搜索
        if(!empty(input('get.goods'))){
            $goods_sql['name|py']=['like','%'.input('get.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $opurchaseinfo_sql['goods']=['in',$goods_arr,'OR'];
        }
        if(isset($opurchaseinfo_sql)){
            $opurchaseinfo_arr=arrayChange(opurchaseinfo::where($opurchaseinfo_sql)->field('pid')->select()->toArray(),'pid');
            $sql['id']=['in',$opurchaseinfo_arr,'OR'];
        }
        //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $sql['time']=[$egt,$elt];
        }
        //按照供应商搜索
        if(!empty(input('get.supplier'))){
            $sql['supplier']=input('get.supplier');
        }
        //按照制单人搜索
        if(!empty(input('get.user'))){
            $sql['user']=input('get.user');
        }
        //按照审核状态搜索
        if(!empty(input('get.type'))){
            $sql['type']=input('get.type')-1;
        }
        $sql['number'] = ['like','%'.input('get.number').'%'];
        $sql['data']=['like','%'.input('get.data').'%'];
        $arr = opurchaseclass::where($sql)->select()->toArray();
        //判断报表类型
        $mode=input('get.mode');
        if(empty($mode)){
            //简易报表
            $tab_title=[
                'time'=>'单据日期',
                'number'=>'单据编号',
                'user'=>'制单人',
                'type'=>'审核状态',
                'data'=>'单据备注'
            ];
            $tab_data=[];
            foreach ($arr as $key=>$vo) {
                $tmp_data=$arr[$key];
                $tmp_data['user']=$vo['user']['info']['name'];
                $tmp_data['type']=$vo['type']['name'];
                array_push($tab_data,$tmp_data);
            }
            //表格内容
            $arr['shell']='采购订单报表';//Shell名称
            $arr['title']='采 购 订 单 报 表';//标题名称
            $arr['rows']=[
                'title'=>$tab_title,
                'data'=>$tab_data
            ];
            $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
            $arr['colnums']=count($tab_title);//列数
        }else{
            foreach ($arr as $key=>$vo) {
                //构造数据
                $tab_title=[
                        'name'=>'商品信息',
                        'attr'=>'辅助属性',
                        'brand'=>'商品品牌',
                        'number'=>'商品编号',
                        'class'=>'商品分类',
                        'spec'=>'规格型号',
                        'code'=>'条形码',
                        'unit'=>'商品单位',
                        'stocktip'=>'库存预警',
                        'location'=>'商品货位',
                        'integral'=>'赠送积分',
                        'nums'=>'数量',
                        'data'=>'备注信息'
                ];
                //字段设置
                $tabinfo=tabinfo::get(['name'=>'opurchase']);
                $tabmain=json_decode($tabinfo['main'],true);
                foreach ($tabmain as $main_vo) {
                    if(empty($main_vo[key($main_vo)][1])){
                        unset($tab_title[key($main_vo)]);//删除
                    }
                }
                //表格内容
                $tab_data=opurchaseinfo::where(['pid'=>$vo['id']])->select()->toArray();
                foreach($tab_data as $k=>$data_vo){
                    $tab_data[$k]['name']=$data_vo['goods']['info']['name'];
                    $tab_data[$k]['attr']=$data_vo['attr']['name'];
                    $tab_data[$k]['brand']=$data_vo['goods']['info']['brand']['info']['name'];
                    $tab_data[$k]['number']=$data_vo['goods']['info']['number'];
                    $tab_data[$k]['class']=$data_vo['goods']['info']['class']['info']['name'];
                    $tab_data[$k]['spec']=$data_vo['goods']['info']['spec'];
                    $tab_data[$k]['code']=' '.$data_vo['goods']['info']['code'];//转字符串
                    $tab_data[$k]['unit']=$data_vo['goods']['info']['unit']['info']['name'];
                    $tab_data[$k]['stocktip']=$data_vo['goods']['info']['stocktip'];
                    $tab_data[$k]['location']=$data_vo['goods']['info']['location'];
                    $tab_data[$k]['integral']=$data_vo['goods']['info']['integral'];
                }
                $arr[$key]['shell']=$vo['number'];//Shell名称
                $arr[$key]['title']='采 购 订 单';//标题名称
                $arr[$key]['rows']=[
                    'text_1'=>'单据日期:'.$vo['time'].'||'.'单据编号:'.$vo['number'],
                    'title'=>$tab_title,
                    'data'=>$tab_data,
                    'text_2'=>'制单人:'.$vo['user']['info']['name'].'||'.'备注信息:'.$vo['data'],
                ];
                $arr[$key]['rownums']=count($arr[$key]['rows'])+count($tab_data)-1;//行数
                $arr[$key]['colnums']=count($tab_title);//列数
            }
        }
        if(empty($mode)){
            ExportExcel('采购订单',[$arr]);
        }else{
            if(count($arr)>32){
                $file_name_arr=[];
                $chunk_arr=array_chunk($arr,32);
                foreach ($chunk_arr as $key=>$chunk_arr_vo) {
                    array_push($file_name_arr,'skin/tmp_file/xls/'.ExportExcel('CGDD',$chunk_arr_vo,[true,$key+1]).'.xls');
                }
                $zip_name=strtoupper('CGDD');
                file_to_zip($zip_name,$file_name_arr);
            }else{
                ExportExcel('采购订单',$arr);
            }
        }
        
    }
    //导出采购入库单已审核报表
    public function orpurchase(){
        //按照名称搜索
        if(!empty(input('get.goods'))){
            $goods_sql['name|py']=['like','%'.input('get.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $opurchaseinfo_sql['goods']=['in',$goods_arr,'OR'];
        }
        if(isset($opurchaseinfo_sql)){
            $opurchaseinfo_arr=arrayChange(opurchaseinfo::where($opurchaseinfo_sql)->field('pid')->select()->toArray(),'pid');
            $sql['id']=['in',$opurchaseinfo_arr,'OR'];
        }
        //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $sql['time']=[$egt,$elt];
        }
        //按照供应商搜索
        if(!empty(input('get.supplier'))){
            $sql['supplier']=input('get.supplier');
        }
        //按照制单人搜索
        if(!empty(input('get.user'))){
            $sql['user']=input('get.user');
        }
        //按照入库状态搜索
        if(!empty(input('get.storage'))){
            $sql['storage']=input('get.storage')-1;
        }
        $sql['number'] = ['like','%'.input('get.number').'%'];
        $sql['type'] = '1';
        $arr = opurchaseclass::where($sql)->select()->toArray();
        foreach ($arr as $key=>$vo) {
            //构造数据
            $tab_title=[
                    'name'=>'商品信息',
                    'attr'=>'辅助属性',
                    'brand'=>'商品品牌',
                    'number'=>'商品编号',
                    'class'=>'商品分类',
                    'spec'=>'规格型号',
                    'code'=>'条形码',
                    'unit'=>'商品单位',
                    'stocktip'=>'库存预警',
                    'location'=>'商品货位',
                    'integral'=>'赠送积分',
                    'nums'=>'总数量',
                    'readynums'=>'已入数量',
                    'apenums'=>'未入数量',
                    'data'=>'备注信息'
            ];
            //字段设置
            $tabinfo=tabinfo::get(['name'=>'orpurchase']);
            $tabmain=json_decode($tabinfo['main'],true);
            foreach ($tabmain as $main_vo) {
                if(empty($main_vo[key($main_vo)][1])){
                    unset($tab_title[key($main_vo)]);//删除
                }
            }
            //表格内容
            $tab_data=opurchaseinfo::where(['pid'=>$vo['id']])->select()->toArray();
            foreach($tab_data as $k=>$data_vo){
                $tab_data[$k]['name']=$data_vo['goods']['info']['name'];
                $tab_data[$k]['attr']=$data_vo['attr']['name'];
                $tab_data[$k]['brand']=$data_vo['goods']['info']['brand']['info']['name'];
                $tab_data[$k]['number']=$data_vo['goods']['info']['number'];
                $tab_data[$k]['class']=$data_vo['goods']['info']['class']['info']['name'];
                $tab_data[$k]['spec']=$data_vo['goods']['info']['spec'];
                $tab_data[$k]['code']=' '.$data_vo['goods']['info']['code'];//转字符串
                $tab_data[$k]['unit']=$data_vo['goods']['info']['unit']['info']['name'];
                $tab_data[$k]['stocktip']=$data_vo['goods']['info']['stocktip'];
                $tab_data[$k]['location']=$data_vo['goods']['info']['location'];
                $tab_data[$k]['integral']=$data_vo['goods']['info']['integral'];
                $tab_data[$k]['apenums']=$data_vo['nums']-$data_vo['readynums'];
            }
            $arr[$key]['shell']=$vo['number'];//Shell名称
            $arr[$key]['title']='采 购 入 库 详 情 单';//标题名称
            $arr[$key]['rows']=[
                'text_1'=>'单据日期:'.$vo['time'].'||'.'单据编号:'.$vo['number'],
                'title'=>$tab_title,
                'data'=>$tab_data,
                'text_2'=>'制单人:'.$vo['user']['info']['name'].'||'.'备注信息:'.$vo['data'],
            ];
            $arr[$key]['rownums']=count($arr[$key]['rows'])+count($tab_data)-1;//行数
            $arr[$key]['colnums']=count($tab_title);//列数
        }
        if(count($arr)>32){
            $file_name_arr=[];
            $chunk_arr=array_chunk($arr,32);
            foreach ($chunk_arr as $key=>$chunk_arr_vo) {
                array_push($file_name_arr,'skin/tmp_file/xls/'.ExportExcel('CGRKXQ',$chunk_arr_vo,[true,$key+1]).'.xls');
            }
            $zip_name=strtoupper('CGRKXQ');
            file_to_zip($zip_name,$file_name_arr);
        }else{
            ExportExcel('采购入库详情单',$arr);
        }
    }
    //导出采购入库单信息
    public function rpurchase(){
        //按照名称搜索
        if(!empty(input('get.goods'))){
            $goods_sql['name|py']=['like','%'.input('get.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $rpurchaseinfo_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('get.warehouse'))){
            $rpurchaseinfo_sql['warehouse']=input('get.warehouse');
        }
        if(isset($rpurchaseinfo_sql)){
            $rpurchaseinfo_arr=arrayChange(rpurchaseinfo::where($rpurchaseinfo_sql)->field('pid')->select()->toArray(),'pid');
            $sql['id']=['in',$rpurchaseinfo_arr,'OR'];
        }else{
            //默认数据鉴权处理
            $user_auth=json_decode(user_info('auth'),true);
            if(!empty($user_auth) && !empty($user_auth['warehouse'])){    
                $auth_sql['warehouse']=['in',$user_auth['warehouse'],'OR'];
                $sql['id'] = ['in',arrayChange(rpurchaseinfo::where($auth_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
            }
        }
        //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $sql['time']=[$egt,$elt];
        }
        //按照供应商搜索
        if(!empty(input('get.supplier'))){
            $sql['supplier']=input('get.supplier');
        }
        //按照制单人搜索
        if(!empty(input('get.user'))){
            $sql['user']=input('get.user');
        }
        //按照结算账户搜索
        if(!empty(input('get.account'))){
            $sql['account']=input('get.account');
        }
        //按照审核状态搜索
        if(!empty(input('get.type'))){
            $sql['type']=input('get.type')-1;
        }
        $sql['number'] = ['like','%'.input('get.number').'%'];
        $sql['data'] = ['like','%'.input('get.data').'%'];
        if(!empty(input('get.oidnumber'))){
            $opurchaseclass_arr=arrayChange(opurchaseclass::where(['number'=>['like','%'.input('get.oidnumber').'%']])->select()->toArray(),'id');
            $sql['oid'] = ['in',$opurchaseclass_arr,'OR'];
        }
        $arr = rpurchaseclass::where($sql)->select()->toArray();
        //判断报表类型
        $mode=input('get.mode');
        if(empty($mode)){
            //简易报表
            $tab_title=[
                'time'=>'单据日期',
                'onumber'=>'采购编号',
                'number'=>'单据编号',
                'supplier'=>'供应商',
                'account'=>'结算账户',
                'user'=>'制单人',
                'type'=>'审核状态',
                'total'=>'单据金额',
                'actual'=>'实际金额',
                'money'=>'实付金额',
                'data'=>'单据备注'
            ];
            $tab_data=[];
            foreach ($arr as $key=>$vo) {
                $tmp_data=$arr[$key];
                $tmp_data['onumber']=$vo['oid']['info']['number'];
                $tmp_data['supplier']=$vo['supplier']['info']['name'];
                $tmp_data['account']=$vo['account']['info']['name'];
                $tmp_data['user']=$vo['user']['info']['name'];
                $tmp_data['type']=$vo['type']['name'];
                array_push($tab_data,$tmp_data);
            }
            $sum_arr=get_sums($tab_data,['total','actual','money']);
            //表格内容
            $arr['shell']='采购入库单报表';//Shell名称
            $arr['title']='采 购 入 库 单 报 表';//标题名称
            $arr['rows']=[
                'title'=>$tab_title,
                'data'=>$tab_data,
                'text_1'=>'单据总金额:'.$sum_arr['total'].' | '.'实际总金额:'.$sum_arr['actual'].' | '.'实付总金额:'.$sum_arr['money']
            ];
            $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
            $arr['colnums']=count($tab_title);//列数
        }else{
            foreach ($arr as $key=>$vo) {
                //构造数据
                $tab_title=[
                        'name'=>'商品信息',
                        'attr'=>'辅助属性',
                        'warehouse'=>'所入仓库',
                        'brand'=>'商品品牌',
                        'number'=>'商品编号',
                        'class'=>'商品分类',
                        'spec'=>'规格型号',
                        'code'=>'条形码',
                        'unit'=>'商品单位',
                        'stocktip'=>'库存预警',
                        'location'=>'商品货位',
                        'integral'=>'赠送积分',
                        'serial'=>'商品串码',
                        'nums'=>'数量',
                        'price'=>'采购单价',
                        'total'=>'采购金额',
                        'batch'=>'商品批次',
                        'data'=>'备注信息'
                ];
                //字段设置
                $tabinfo=tabinfo::get(['name'=>'rpurchase']);
                $tabmain=json_decode($tabinfo['main'],true);
                foreach ($tabmain as $main_vo) {
                    if(empty($main_vo[key($main_vo)][1])){
                        unset($tab_title[key($main_vo)]);//删除
                    }
                }
                $sys=sys::get(['id'=>2]);
                if(empty($sys['info']['serial'])){
                    unset($tab_title['serial']);//删除串号
                }
                if(empty($sys['info']['batch'])){
                    unset($tab_title['batch']);//删除批次
                }
                //表格内容
                $tab_data=rpurchaseinfo::where(['pid'=>$vo['id']])->select()->toArray();
                foreach($tab_data as $k=>$data_vo){
                    $tab_data[$k]['name']=$data_vo['goods']['info']['name'];
                    $tab_data[$k]['attr']=$data_vo['attr']['name'];
                    $tab_data[$k]['warehouse']=$data_vo['warehouse']['info']['name'];
                    $tab_data[$k]['brand']=$data_vo['goods']['info']['brand']['info']['name'];
                    $tab_data[$k]['number']=$data_vo['goods']['info']['number'];
                    $tab_data[$k]['class']=$data_vo['goods']['info']['class']['info']['name'];
                    $tab_data[$k]['spec']=$data_vo['goods']['info']['spec'];
                    $tab_data[$k]['code']=' '.$data_vo['goods']['info']['code'];//转字符串
                    $tab_data[$k]['unit']=$data_vo['goods']['info']['unit']['info']['name'];
                    $tab_data[$k]['stocktip']=$data_vo['goods']['info']['stocktip'];
                    $tab_data[$k]['location']=$data_vo['goods']['info']['location'];
                    $tab_data[$k]['integral']=$data_vo['goods']['info']['integral'];
                    $tab_data[$k]['batch']=$data_vo['room']['info']['batch'];
                }
                $arr[$key]['shell']=$vo['number'];//Shell名称
                $arr[$key]['title']='采 购 入 库 单';//标题名称
                $arr[$key]['rows']=[
                    'text_1'=>'供应商:'.$vo['supplier']['info']['name'].'||'.'单据日期:'.$vo['time'].'||'.'单据编号:'.$vo['number'],
                    'title'=>$tab_title,
                    'data'=>$tab_data,
                    'text_2'=>'单据金额:'.$vo['total'].'||'.'实际金额:'.$vo['actual'].'||'.'实付金额:'.$vo['money'],
                    'text_3'=>'制单人:'.$vo['user']['info']['name'].'||'.'结算账户:'.$vo['account']['info']['name'].'||'.'备注信息:'.$vo['data'],
                ];
                $arr[$key]['rownums']=count($arr[$key]['rows'])+count($tab_data)-1;//行数
                $arr[$key]['colnums']=count($tab_title);//列数
            }
        }
        if(empty($mode)){
            ExportExcel('采购入库单',[$arr]);
        }else{
            if(count($arr)>32){
                $file_name_arr=[];
                $chunk_arr=array_chunk($arr,32);
                foreach ($chunk_arr as $key=>$chunk_arr_vo) {
                    array_push($file_name_arr,'skin/tmp_file/xls/'.ExportExcel('CGRK',$chunk_arr_vo,[true,$key+1]).'.xls');
                }
                $zip_name=strtoupper('CGRK');
                file_to_zip($zip_name,$file_name_arr);
            }else{
                ExportExcel('采购入库单',$arr);
            }
        }
        
    }
    //导出采购入库对账单详情
    public function rpurchase_bill(){
       //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $sql['time']=[$egt,$elt];
        }
        //判断用户
        if(!empty(input('get.user'))){
            $sql['user'] = input('get.user');
        }
        //判断供应商
        if(!empty(input('get.supplier'))){
            $sql['supplier'] = input('get.supplier');
        }
        //判断结算账户
        if(!empty(input('get.account'))){
            $sql['account'] = input('get.account');
        }
        //判断付款状态
        if(empty(input('get.billtype'))){
            $sql['billtype']=['neq',-1];
        }else{
            $sql['billtype']=[
                ['neq',-1],
                ['eq',input('get.billtype')-1]
            ];
        }
        $sql['number'] = ['like','%'.input('get.number').'%'];
        $tab_data = rpurchaseclass::where($sql)->select()->toArray();
        foreach ($tab_data as $key=>$vo) {
           $tab_data[$key]['account']=$vo['account']['info']['name'];
           $tab_data[$key]['user']=$vo['user']['info']['name'];
           $tab_data[$key]['supplier']=$vo['supplier']['info']['name'];
           $tab_data[$key]['billtype']=$vo['billtype']['name'];
        }
        //构造数据
        $tab_title=[
            'time'=>'单据日期',
            'number'=>'单据编号',
            'supplier'=>'供应商',
            'account'=>'结算账户',
            'total'=>'单据金额',
            'actual'=>'实际金额',
            'money'=>'实付金额',
            'user'=>'制单人',
            'billtype'=>'付款状态',
        ];
        $sum_arr=get_sums($tab_data,['total','actual','money']);
        //表格内容
        $arr['shell']='采购入库对账单详情';//Shell名称
        $arr['title']='采购入库对账单详情';//标题名称
        $arr['rows']=[
            'title'=>$tab_title,
            'data'=>$tab_data,
            'text_1'=>'单据总金额:'.$sum_arr['total'].' | '.'实际总金额:'.$sum_arr['actual'].' | '.'实付总金额:'.$sum_arr['money']
        ];
        $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
        $arr['colnums']=count($tab_title);//列数
        ExportExcel('采购入库对账单详情',[$arr]);
    }
    //导出服务订单信息
    public function itemorder(){
        //按照名称搜索
        if(!empty(input('get.item'))){
            $item_sql['name|py']=['like','%'.input('get.item').'%'];
            $item_arr=arrayChange(item::where($item_sql)->field('id')->select()->toArray(),'id');
            $info_sql['item']=['in',$item_arr,'OR'];
        }
        //info条件转class条件
        if(isset($info_sql)){
            $class_sql['id'] = ['in',arrayChange(itemorderinfo::where($info_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
        }
        
        //按照编号搜索
        $class_sql['number']=['like','%'.input('get.number').'%'];
        //按照客户搜索
        if(!empty(input('get.customer'))){
            $class_sql['customer']=input('get.customer');
        }
        //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $class_sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $class_sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $class_sql['time']=[$egt,$elt];
        }
        //按照制单人搜索
        if(!empty(input('get.user'))){
            $class_sql['user']=input('get.user');
        }
        //按照结算账户搜索
        if(!empty(input('get.account'))){
            $class_sql['account']=input('get.account');
        }
        //按照审核状态搜索
        if(!empty(input('get.type'))){
            $class_sql['type']=input('get.type')-1;
        }
        $class_sql['data']=['like','%'.input('get.data').'%'];;
        $arr = itemorderclass::where($class_sql)->select()->toArray();
        //判断报表类型
        $mode=input('get.mode');
        if(empty($mode)){
            //简易报表
            $tab_title=[
                'time'=>'单据日期',
                'number'=>'单据编号',
                'customer'=>'购买客户',
                'account'=>'结算账户',
                'user'=>'制单人',
                'type'=>'审核状态',
                'total'=>'单据金额',
                'discount'=>'优惠金额',
                'money'=>'实收金额',
                'data'=>'单据备注'
            ];
            $tab_data=[];
            foreach ($arr as $key=>$vo) {
                $tmp_data=$arr[$key];
                $tmp_data['customer']=$vo['customer']['info']['name'];
                $tmp_data['account']=$vo['account']['info']['name'];
                $tmp_data['user']=$vo['user']['info']['name'];
                $tmp_data['type']=$vo['type']['name'];
                array_push($tab_data,$tmp_data);
            }
            $sum_arr=get_sums($tab_data,['total','discount','money']);
            //表格内容
            $arr['shell']='服务单报表';//Shell名称
            $arr['title']='服 务 单 报 表';//标题名称
            $arr['rows']=[
                'title'=>$tab_title,
                'data'=>$tab_data,
                'text_1'=>'单据总金额:'.$sum_arr['total'].' | '.'优惠总金额:'.$sum_arr['discount'].' | '.'实收总金额:'.$sum_arr['money']
            ];
            $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
            $arr['colnums']=count($tab_title);//列数
        }else{
            foreach ($arr as $key=>$vo) {
                //构造数据
                $tab_title=[
                        'item'=>'服务项目',
                        'nums'=>'服务数量',
                        'price'=>'服务价格',
                        'total'=>'总金额',
                        'data'=>'备注信息',
                ];
                //表格内容
                $tab_data=itemorderinfo::where(['pid'=>$vo['id']])->select()->toArray();
                foreach($tab_data as $k=>$data_vo){
                    $tab_data[$k]['item']=$data_vo['item']['info']['name'];
                }
                $arr[$key]['shell']=$vo['number'];//Shell名称
                $arr[$key]['title']='服 务 订 单';//标题名称
                $arr[$key]['rows']=[
                    'text_1'=>'购买客户:'.$vo['customer']['info']['name'].'||'.'单据日期:'.$vo['time'].'||'.'单据编号:'.$vo['number'],
                    'title'=>$tab_title,
                    'data'=>$tab_data,
                    'text_2'=>'单据金额:'.$vo['total'].'||'.'优惠金额:'.$vo['discount'].'||'.'实收金额:'.$vo['money'],
                    'text_3'=>'制单人:'.$vo['user']['info']['name'].'||'.'结算账户:'.$vo['account']['info']['name'].'||'.'备注信息:'.$vo['data'],
                ];
                $arr[$key]['rownums']=count($arr[$key]['rows'])+count($tab_data)-1;//行数
                $arr[$key]['colnums']=count($tab_title);//列数
            }
        }
        if(empty($mode)){
            ExportExcel('服务订单',[$arr]);
        }else{
            if(count($arr)>32){
                $file_name_arr=[];
                $chunk_arr=array_chunk($arr,32);
                foreach ($chunk_arr as $key=>$chunk_arr_vo) {
                    array_push($file_name_arr,'skin/tmp_file/xls/'.ExportExcel('FW',$chunk_arr_vo,[true,$key+1]).'.xls');
                }
                $zip_name=strtoupper('FW');
                file_to_zip($zip_name,$file_name_arr);
            }else{
                ExportExcel('服务订单',$arr);
            }
        }
        
    }
    //导出服务对账单详情
    public function itemorder_bill(){
       //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $sql['time']=[$egt,$elt];
        }
        //判断用户
        if(!empty(input('get.user'))){
            $sql['user'] = input('get.user');
        }
        //判断购买客户
        if(!empty(input('get.customer'))){
            $sql['customer'] = input('get.customer');
        }
        //判断结算账户
        if(!empty(input('get.account'))){
            $sql['account'] = input('get.account');
        }
        //判断付款状态
        if(empty(input('get.billtype'))){
            $sql['billtype']=['neq',-1];
        }else{
            $sql['billtype']=[
                ['neq',-1],
                ['eq',input('get.billtype')-1]
            ];
        }
        $sql['number'] = ['like','%'.input('get.number').'%'];
        $tab_data = itemorderclass::where($sql)->select()->toArray();
        foreach ($tab_data as $key=>$vo) {
               $tab_data[$key]['account']=$vo['account']['info']['name'];
               $tab_data[$key]['user']=$vo['user']['info']['name'];
               $tab_data[$key]['customer']=$vo['customer']['info']['name'];
               $tab_data[$key]['billtype']=$vo['billtype']['name'];
        }
        //构造数据
        $tab_title=[
            'time'=>'单据日期',
            'number'=>'单据编号',
            'customer'=>'购买客户',
            'account'=>'结算账户',
            'total'=>'单据金额',
            'discount'=>'优惠金额',
            'money'=>'实收金额',
            'user'=>'制单人',
            'billtype'=>'付款状态',
        ];
        $sum_arr=get_sums($tab_data,['total','discount','money']);
        //表格内容
        $arr['shell']='服务对账单详情';//Shell名称
        $arr['title']='服务对账单详情';//标题名称
        $arr['rows']=[
            'title'=>$tab_title,
            'data'=>$tab_data,
            'text_1'=>'单据总金额:'.$sum_arr['total'].' | '.'优惠总金额:'.$sum_arr['discount'].' | '.'实收总金额:'.$sum_arr['money']
        ];
        $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
        $arr['colnums']=count($tab_title);//列数
        ExportExcel('服务对账单详情',[$arr]);
    }
    //导出积分兑换单信息
    public function exchange(){
        //按照名称搜索
        if(!empty(input('get.goods'))){
            $goods_sql['name|py']=['like','%'.input('get.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $info_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('get.warehouse'))){
            $info_sql['warehouse']=input('get.warehouse');
        }
        
        //info条件转class条件
        if(isset($info_sql)){
            $class_sql['id'] = ['in',arrayChange(exchangeinfo::where($info_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
        }else{
            //默认数据鉴权处理
            $user_auth=json_decode(user_info('auth'),true);
            if(!empty($user_auth) && !empty($user_auth['warehouse'])){    
                $auth_sql['warehouse']=['in',$user_auth['warehouse'],'OR'];
                $class_sql['id'] = ['in',arrayChange(exchangeinfo::where($auth_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
            }
        }
        
        //按照编号搜索
        $class_sql['number']=['like','%'.input('get.number').'%'];
        //按照客户搜索
        if(!empty(input('get.customer'))){
            $class_sql['customer']=input('get.customer');
        }
        //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $class_sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $class_sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $class_sql['time']=[$egt,$elt];
        }
        //按照制单人搜索
        if(!empty(input('get.user'))){
            $class_sql['user']=input('get.user');
        }
        //按照审核状态搜索
        if(!empty(input('get.type'))){
            $class_sql['type']=input('get.type')-1;
        }
        $class_sql['data']=['like','%'.input('get.data').'%'];
        $arr = exchangeclass::where($class_sql)->select()->toArray();
        //判断报表类型
        $mode=input('get.mode');
        if(empty($mode)){
            //简易报表
            $tab_title=[
                'time'=>'单据日期',
                'number'=>'单据编号',
                'customer'=>'购买客户',
                'user'=>'制单人',
                'type'=>'审核状态',
                'order_integral'=>'单据积分',
                'discount'=>'优惠积分',
                'actual_integral'=>'实付积分',
                'data'=>'单据备注'
            ];
            $tab_data=[];
            foreach ($arr as $key=>$vo) {
                $tmp_data=$arr[$key];
                $tmp_data['customer']=$vo['customer']['info']['name'];
                $tmp_data['user']=$vo['user']['info']['name'];
                $tmp_data['type']=$vo['type']['name'];
                array_push($tab_data,$tmp_data);
            }
            $sum_arr=get_sums($tab_data,['order_integral','discount','actual_integral']);
            //表格内容
            $arr['shell']='积分兑换单报表';//Shell名称
            $arr['title']='积分兑换单报表';//标题名称
            $arr['rows']=[
                'title'=>$tab_title,
                'data'=>$tab_data,
                'text_1'=>'单据总积分:'.$sum_arr['order_integral'].' | '.'优惠总积分:'.$sum_arr['discount'].' | '.'实付总积分:'.$sum_arr['actual_integral']
            ];
            $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
            $arr['colnums']=count($tab_title);//列数
        }else{
            foreach ($arr as $key=>$vo) {
                //构造数据
                $tab_title=[
                        'name'=>'商品信息',
                        'attr'=>'辅助属性',
                        'warehouse'=>'所属仓库',
                        'brand'=>'商品品牌',
                        'number'=>'商品编号',
                        'class'=>'商品分类',
                        'spec'=>'规格型号',
                        'code'=>'条形码',
                        'unit'=>'商品单位',
                        'stocktip'=>'库存预警',
                        'location'=>'商品货位',
                        'integral'=>'所需积分',
                        'batch'=>'商品批次',
                        'serial'=>'商品串码',
                        'nums'=>'数量',
                        'allintegral'=>'总积分',
                        'data'=>'备注信息'
                ];
                //字段设置
                $tabinfo=tabinfo::get(['name'=>'exchange']);
                $tabmain=json_decode($tabinfo['main'],true);
                foreach ($tabmain as $main_vo) {
                    if(empty($main_vo[key($main_vo)][1])){
                        unset($tab_title[key($main_vo)]);//删除
                    }
                }
                $sys=sys::get(['id'=>2]);
                if(empty($sys['info']['serial'])){
                    unset($tab_title['serial']);//删除串号
                }
                if(empty($sys['info']['batch'])){
                    unset($tab_title['batch']);//删除批次
                }
                //表格内容
                $tab_data=exchangeinfo::where(['pid'=>$vo['id']])->select()->toArray();
                foreach($tab_data as $k=>$data_vo){
                    $tab_data[$k]['name']=$data_vo['room']['info']['goods']['info']['name'];
                    $tab_data[$k]['attr']=$data_vo['room']['info']['attr']['name'];
                    $tab_data[$k]['warehouse']=$data_vo['room']['info']['warehouse']['info']['name'];
                    $tab_data[$k]['brand']=$data_vo['room']['info']['goods']['info']['brand']['info']['name'];
                    $tab_data[$k]['number']=$data_vo['room']['info']['goods']['info']['number'];
                    $tab_data[$k]['class']=$data_vo['room']['info']['goods']['info']['class']['info']['name'];
                    $tab_data[$k]['spec']=$data_vo['room']['info']['goods']['info']['spec'];
                    $tab_data[$k]['code']=' '.$data_vo['room']['info']['goods']['info']['code'];//转字符串
                    $tab_data[$k]['unit']=$data_vo['room']['info']['goods']['info']['unit']['info']['name'];
                    $tab_data[$k]['stocktip']=$data_vo['room']['info']['goods']['info']['stocktip'];
                    $tab_data[$k]['location']=$data_vo['room']['info']['goods']['info']['location'];
                    $tab_data[$k]['batch']=$data_vo['room']['info']['batch'];
                }
                $arr[$key]['shell']=$vo['number'];//Shell名称
                $arr[$key]['title']='积 分 兑 换 单';//标题名称
                $arr[$key]['rows']=[
                    'text_1'=>'购买客户:'.$vo['customer']['info']['name'].'||'.'单据日期:'.$vo['time'].'||'.'单据编号:'.$vo['number'],
                    'title'=>$tab_title,
                    'data'=>$tab_data,
                    'text_2'=>'单据积分:'.$vo['order_integral'].'||'.'优惠积分:'.$vo['discount'].'||'.'实付积分:'.$vo['actual_integral'],
                    'text_3'=>'制单人:'.$vo['user']['info']['name'].'||'.'备注信息:'.$vo['data'],
                ];
                $arr[$key]['rownums']=count($arr[$key]['rows'])+count($tab_data)-1;//行数
                $arr[$key]['colnums']=count($tab_title);//列数
                
            }
        }
        if(empty($mode)){
            ExportExcel('积分兑换单',[$arr]);
        }else{
            if(count($arr)>32){
                $file_name_arr=[];
                $chunk_arr=array_chunk($arr,32);
                foreach ($chunk_arr as $key=>$chunk_arr_vo) {
                    array_push($file_name_arr,'skin/tmp_file/xls/'.ExportExcel('JFDH',$chunk_arr_vo,[true,$key+1]).'.xls');
                }
                $zip_name=strtoupper('JFDH');
                file_to_zip($zip_name,$file_name_arr);
            }else{
                ExportExcel('积分兑换单',$arr);
            }
        }
    }
    //导出资金调拨单信息
    public function eft(){
        if(!empty(input('get.account'))){
            $info_sql['account_id']=input('get.account');
        }
        if(!empty(input('get.toaccount'))){
            $info_sql['toaccount_id']=input('get.toaccount');
        }
        //info条件转class条件
        if(isset($info_sql)){
            $class_sql['id'] = ['in',arrayChange(eftinfo::where($info_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
        }
        //按照编号搜索
        $class_sql['number']=['like','%'.input('get.number').'%'];
        //按照时间搜索
        $start_time=input('get.start_time');//开始时间
        $end_time=input('get.end_time');//结束时间
        $egt=['egt',strtotime($start_time)];//大于等于
        $elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
        if(!empty($start_time) && empty($end_time)){
            //开始时间不为空,结束时间为空
            $class_sql['time']=$egt;
        }elseif(!empty($end_time) && empty($start_time)){
            //结束时间不为空,开始时间为空
            $class_sql['time']=$elt;
        }elseif(!empty($end_time) && !empty($start_time)){
            //开始时间不为空,结束时间不为空
            $class_sql['time']=[$egt,$elt];
        }
        //按照制单人搜索
        if(!empty(input('get.user'))){
            $class_sql['user']=input('get.user');
        }
        //按照审核状态搜索
        if(!empty(input('get.type'))){
            $class_sql['type']=input('get.type')-1;
        }
        $class_sql['data']=['like','%'.input('get.data').'%'];
        $arr = eftclass::where($class_sql)->select()->toArray();
        //判断报表类型
        $mode=input('get.mode');
        if(empty($mode)){
            //简易报表
            $tab_title=[
                'time'=>'单据日期',
                'number'=>'单据编号',
                'user'=>'制单人',
                'money'=>'单据金额',
                'type'=>'审核状态',
                'data'=>'单据备注'
            ];
            $tab_data=[];
            foreach ($arr as $key=>$vo) {
                $tmp_data=$arr[$key];
                $tmp_data['user']=$vo['user']['info']['name'];
                $tmp_data['type']=$vo['type']['name'];
                $tmp_data['money']=eftinfo::where(['pid'=>$vo['id']])->sum('money');
                array_push($tab_data,$tmp_data);
            }
            $sum_arr=get_sums($tab_data,['money']);
            //表格内容
            $arr['shell']='资金调拨单报表';//Shell名称
            $arr['title']='资金调拨单报表';//标题名称
            $arr['rows']=[
                'title'=>$tab_title,
                'data'=>$tab_data,
                'text_1'=>'单据总金额:'.$sum_arr['money']
            ];
            $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
            $arr['colnums']=count($tab_title);//列数
        }else{
            foreach ($arr as $key=>$vo) {
                //构造数据
                $tab_title=[
                    'account'=>'调出账户',
                    'toaccount'=>'调入账户',
                    'money'=>'金额',
                    'data'=>'备注信息'
                ];
                //表格内容
                $tab_data=eftinfo::where(['pid'=>$vo['id']])->select()->toArray();
                foreach($tab_data as $k=>$data_vo){
                    $tab_data[$k]['account']=$data_vo['account_id']['info']['name'];
                    $tab_data[$k]['toaccount']=$data_vo['toaccount_id']['info']['name'];
                }
                $arr[$key]['shell']=$vo['number'];//Shell名称
                $arr[$key]['title']='资 金 调 拨 单';//标题名称
                $arr[$key]['rows']=[
                    'text_1'=>'单据日期:'.$vo['time'].'||'.'单据编号:'.$vo['number'],
                    'title'=>$tab_title,
                    'data'=>$tab_data,
                    'text_2'=>'制单人:'.$vo['user']['info']['name'].'||'.'备注信息:'.$vo['data'],
                ];
                $arr[$key]['rownums']=count($arr[$key]['rows'])+count($tab_data)-1;//行数
                $arr[$key]['colnums']=count($tab_title);//列数
            }
        }
        if(empty($mode)){
            ExportExcel('资金调拨单',[$arr]);
        }else{
            if(count($arr)>32){
                $file_name_arr=[];
                $chunk_arr=array_chunk($arr,32);
                foreach ($chunk_arr as $key=>$chunk_arr_vo) {
                    array_push($file_name_arr,'skin/tmp_file/xls/'.ExportExcel('ZZDB',$chunk_arr_vo,[true,$key+1]).'.xls');
                }
                $zip_name=strtoupper('ZZDB');
                file_to_zip($zip_name,$file_name_arr);
            }else{
                ExportExcel('资金调拨单',$arr);
            }
        }
    }
    //商品利润表
    public function ape_goods_form(){
        //按照名称搜索
    	if(!empty(input('get.name'))){
    		$goods_sql['name|py']=['like','%'.input('get.name').'%'];
    		$goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
    		$summary_sql['goods']=['in',$goods_arr,'OR'];
    	}
    	//按照时间搜索
    	$start_time=input('get.start_time');//开始时间
    	$end_time=input('get.end_time');//结束时间
    	$egt=['egt',strtotime($start_time)];//大于等于
    	$elt=['elt',strtotime($end_time)+86399];//小于等于(结束加一天)
    	if(!empty($start_time) && empty($end_time)){
    		//开始时间不为空,结束时间为空
    		$summary_sql['time']=$egt;
    	}elseif(!empty($end_time) && empty($start_time)){
    		//结束时间不为空,开始时间为空
    		$summary_sql['time']=$elt;
    	}elseif(!empty($end_time) && !empty($start_time)){
    		//开始时间不为空,结束时间不为空
    		$summary_sql['time']=[$egt,$elt];
    	}
    	//按照单据类型搜索销货单|零售单
    	$summary_sql['type']=['in',[4,6],'OR'];
    	$summary=summary::where($summary_sql)->group('room')->select()->toArray();//去除重复商品
    	$tab_data=[];
    	foreach ($summary as $vo) {
    		$tmp=[];
    		$tmp['name']=$vo['goods']['info']['name'];
    		$tmp['attr']=$vo['attr']['name'];
    		$tmp['warehouse']=$vo['warehouse']['info']['name'];
    		$tmp['batch']=$vo['batch'];
    		$tmp['number']=$vo['goods']['info']['number'];
    		$tmp['class']=$vo['goods']['info']['class']['info']['name'];
    		$tmp['unit']=$vo['goods']['info']['unit']['info']['name'];
    		$tmp['brand']=$vo['goods']['info']['brand']['info']['name'];
    		$tmp['spec']=$vo['goods']['info']['spec'];
    		$tmp['location']=$vo['goods']['info']['location'];
    		$tmp['stocktip']=$vo['goods']['info']['stocktip'];
    		$tmp['sale']=db('summary')->where($summary_sql)->where(['type'=>4,'room'=>$vo['room']])->sum('total');
    		$tmp['cashier']=db('summary')->where($summary_sql)->where(['type'=>6,'room'=>$vo['room']])->sum('total');
    		$tmp['sales_revenue']=$tmp['sale']+$tmp['cashier'];
    		$avg=db('summary')->where(['type'=>['in',[1,2],'OR'],'room'=>$vo['room'],'total'=>['gt',0]])->avg('price');//购货单|采购单|不为零的平均价
    		$allnums=db('summary')->where($summary_sql)->where(['room'=>$vo['room']])->sum('nums');
    		$tmp['sales_cost']=$avg*$allnums;
    		$tmp['sales_maori']=$tmp['sales_revenue']-$tmp['sales_cost'];
    		$tmp['gross_interest_rate']=@round(($tmp['sales_maori']/$tmp['sales_revenue'])*100,2).'%';
    		array_push($tab_data,$tmp);
    	}
        //构造数据
        $tab_title=[
            'name'=>'商品名称',
            'attr'=>'辅助属性',
            'warehouse'=>'所属仓库',
            'batch'=>'商品批次',
            'number'=>'商品编号',
            'class'=>'商品分类',
            'unit'=>'商品单位',
            'brand'=>'商品品牌',
            'spec'=>'规格型号',
            'location'=>'商品货位',
            'stocktip'=>'库存预警',
            'sale'=>'销货金额',
            'cashier'=>'零售金额',
            'sales_revenue'=>'销售收入',
            'sales_cost'=>'销售成本',
            'sales_maori'=>'销售毛利',
            'gross_interest_rate'=>'销售毛利率'
        ];
        //字段设置
        $tabinfo=tabinfo::get(['name'=>'ape_goods_form']);
        $tabmain=json_decode($tabinfo['main'],true);
        foreach ($tabmain as $main_vo) {
            if(empty($main_vo[key($main_vo)][1])){
                unset($tab_title[key($main_vo)]);//删除
            }
        }
        $sys=sys::get(['id'=>2]);
        if(empty($sys['info']['batch'])){
            unset($tab_title['batch']);//删除批次
        }
        $sum_arr=get_sums($tab_data,['sale','cashier','sales_revenue','sales_cost','sales_maori']);
        //表格内容
        $arr['shell']='商品利润表';//Shell名称
        $arr['title']='商品利润表';//标题名称
        $arr['rows']=[
            'title'=>$tab_title,
            'data'=>$tab_data,
            'text_1'=>'销货总金额:'.$sum_arr['sale'].' | '.'零售总金额:'.$sum_arr['cashier'].' | '.'销售总收入:'.$sum_arr['sales_revenue'].'销售总成本:'.$sum_arr['sales_cost'].'销售总毛利:'.$sum_arr['sales_maori']
        ];
        $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
        $arr['colnums']=count($tab_title);//列数
        ExportExcel('商品利润表',[$arr]);
    }
    //导出串码跟踪报表
    public function serial(){
        $info=input('get.');
        if(!empty($info['name'])){
            $goods_sql['name|py']=['like','%'.$info['name'].'%'];
            $goods_arr=arrayChange(db('goods')->where($goods_sql)->field('id')->select(),'id');
            $room_sql['goods']=['in',$goods_arr,'OR'];
            $room_arr=arrayChange(db('room')->where($room_sql)->field('id')->select(),'id');
            $sql['room']=['in',$room_arr,'OR'];
        }
        $sql['code']=['like','%'.$info['code'].'%'];
        if(!empty($info['type'])){
            $sql['type']=$info['type']-1;
        }
        $arr = serial::where($sql)->select()->toArray();
        //判断报表类型
        $mode=input('get.mode');
        $type_name=['0'=>'未销售','1'=>'已销售','2'=>'不在库'];
        if(empty($mode)){
            //简易报表
            $tab_title=[
                'name'=>'商品名称',
                'attr'=>'辅助属性',
                'code'=>'串码',
                'type'=>'串码状态'
            ];
            $tab_data=[];
            foreach ($arr as $key=>$vo) {
                $tmp_data=$arr[$key];
                
                //标题数据
                $room=db('room')->where(['id'=>$vo['room']])->find();
                $goods=db('goods')->where(['id'=>$room['goods']])->find();
                if(empty($room['attr'])){
                    $arr_name='无';
                }else{
                    $arr_name=attr_name($room['attr']);
                }
                $tmp_data['name']=$goods['name'];
                $tmp_data['attr']=$arr_name;
                $tmp_data['code']=' '.$vo['code'];
                $tmp_data['type']=$type_name[$vo['type']];
                array_push($tab_data,$tmp_data);
            }
            //表格内容
            $arr['shell']='串码跟踪报表';//Shell名称
            $arr['title']='串 码 跟 踪 报 表';//标题名称
            $arr['rows']=[
                'title'=>$tab_title,
                'data'=>$tab_data,
            ];
            $arr['rownums']=count($arr['rows'])+count($tab_data)-1;//行数
            $arr['colnums']=count($tab_title);//列数
        }else{
            //详细报表
            foreach ($arr as $key=>$vo) {
                //构造数据
                $tab_title=[
                    'time'=>'单据时间',
                    'type'=>'单据类型',
                    'number'=>'单据编号'
                ];
                //表格内容
                $tab_data=serialinfo::where(['pid'=>$vo['id']])->select()->toArray();
                foreach($tab_data as $k=>$data_vo){
                    $tab_data[$k]['time']=$data_vo['class']['info']['time'];
                    $tab_data[$k]['type']=$data_vo['type']['name'];
                    $tab_data[$k]['number']=$data_vo['class']['info']['number'];
                }
                //标题数据
                $room=db('room')->where(['id'=>$vo['room']])->find();
                $goods=db('goods')->where(['id'=>$room['goods']])->find();
                if(empty($room['attr'])){
                    $arr_name='无';
                }else{
                    $arr_name=attr_name($room['attr']);
                }
                $arr[$key]['shell']=$vo['code'];//Shell名称
                $arr[$key]['title']='串码出入库详情表';//标题名称
                $arr[$key]['rows']=[
                    'text_1'=>'商品名称:'.$goods['name'].'||'.'串码:'.$vo['code'],
                    'text_2'=>'辅助属性:'.$arr_name.'||'.'状态:'.$type_name[$vo['type']],
                    'title'=>$tab_title,
                    'data'=>$tab_data
                ];
                $arr[$key]['rownums']=count($arr[$key]['rows'])+count($tab_data)-1;//行数
                $arr[$key]['colnums']=count($tab_title);//列数
            }
        }
        if(empty($mode)){
            ExportExcel('串码跟踪表',[$arr]);
        }else{
            if(count($arr)>32){
                $file_name_arr=[];
                $chunk_arr=array_chunk($arr,32);
                foreach ($chunk_arr as $key=>$chunk_arr_vo) {
                    array_push($file_name_arr,'skin/tmp_file/xls/'.ExportExcel('serial',$chunk_arr_vo,[true,$key+1]).'.xls');
                }
                $zip_name=strtoupper('serial');
                file_to_zip($zip_name,$file_name_arr);
            }else{
                ExportExcel('串码跟踪表',$arr);
            }
        }
    }
}