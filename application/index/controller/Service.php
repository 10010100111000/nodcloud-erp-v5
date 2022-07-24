<?php
namespace app \index \controller ;
use think \Controller ;
use think \Request ;
use think \Cache ;
use app \index \model \user ;
use app \index \model \customer ;
use app \index \model \customerinfo ;
use app \index \model \supplier ;
use app \index \model \warehouse ;
use app \index \model \account ;
use app \index \model \accountinfo ;
use app \index \model \goodsclass ;
use app \index \model \unit ;
use app \index \model \brand ;
use app \index \model \code ;
use app \index \model \attribute ;
use app \index \model \log ;
use app \index \model \sys ;
use app \index \model \goods ;
use app \index \model \attr ;
use app \index \model \tabinfo ;
use app \index \model \printcode ;
use app \index \model \room ;
use app \index \model \roominfo ;
use app \index \model \serial ;
use app \index \model \serialinfo ;
use app \index \model \purchaseclass ;
use app \index \model \purchaseinfo ;
use app \index \model \purchasebill ;
use app \index \model \saleclass ;
use app \index \model \saleinfo ;
use app \index \model \salebill ;
use app \index \model \repurchaseclass ;
use app \index \model \repurchaseinfo ;
use app \index \model \repurchasebill ;
use app \index \model \resaleclass ;
use app \index \model \resaleinfo ;
use app \index \model \resalebill ;
use app \index \model \allocationclass ;
use app \index \model \allocationinfo ;
use app \index \model \otpurchaseclass ;
use app \index \model \otpurchaseinfo ;
use app \index \model \otsaleclass ;
use app \index \model \otsaleinfo ;
use app \index \model \gatherclass ;
use app \index \model \gatherinfo ;
use app \index \model \paymentclass ;
use app \index \model \paymentinfo ;
use app \index \model \otgatherclass ;
use app \index \model \otgatherinfo ;
use app \index \model \otpaymentclass ;
use app \index \model \otpaymentinfo ;
use app \index \model \cashierclass ;
use app \index \model \cashierinfo ;
use app \index \model \recashierclass ;
use app \index \model \recashierinfo ;
use app \index \model \often ;
use app \index \model \number ;
use app \index \model \opurchaseclass ;
use app \index \model \opurchaseinfo ;
use app \index \model \rpurchaseclass ;
use app \index \model \rpurchaseinfo ;
use app \index \model \rpurchasebill ;
use app \index \model \item ;
use app \index \model \itemorderclass ;
use app \index \model \itemorderinfo ;
use app \index \model \itemorderbill ;
use app \index \model \exchangeclass ;
use app \index \model \exchangeinfo ;
use app \index \model \eftclass ;
use app \index \model \eftinfo ;
use app \index \model \summary ;
class Service extends Controller {
    
    //控制器单文件版
    
	//访问控制
	public function  _initialize (){
		if (!checklogin ()){
			echo 'Unauthorized access';
			exit ;
		}
	}
	//删除客户信息
	public function  del_customer (){
	    $id=input ('post.id');
		//删除判断
		$sale=saleclass::get(['customer'=>$id,'noauth'=>'ape']);
		$resale=resaleclass::get(['customer'=>$id,'noauth'=>'ape']);
		$cashier=cashierclass::get(['customer'=>$id,'noauth'=>'ape']);
		$recashier=recashierclass::get(['customer'=>$id,'noauth'=>'ape']);
		$itemorder=itemorderclass::get(['customer'=>$id,'noauth'=>'ape']);
		$exchange=exchangeclass::get(['customer'=>$id,'noauth'=>'ape']);
		if($sale || $resale || $cashier || $recashier || $itemorder || $exchange){
		    return json ('error');
		}else{
		    $customer=customer::get (['id'=>$id]);
		    push_log ('删除客户信息-'.$customer['name']);
    		customer::destroy (input ('post.id'));
    		return json ('success');
		}
	}
	//获取客户信息
	public function  customer_info (){
		return json (customer::get (input ('post.id')));
	}
	//新增|保存客户信息
	public function  save_customer (){
	    $info=input ('post.');
		if (empty(input ('post.id'))){
			push_log ('创建客户信息-'.$info['name']);
			//判断自定义编码
			if(empty($info['number'])){
    	        $info['number']=get_number('customer');
    	        $set_number=true;
    	    }
			customer::create ($info);
			if(isset($set_number)){set_number('customer');}
		}else {
			push_log ('修改客户信息-'.$info['name']);
			customer::update ($info);
		}
		return json ('success');
	}
	//导入客户信息
	public function  import_customer (Request $request){
		//获取表单上传文件
		$file=$request->file('file');
		if (empty($file)){
			$this->error ('请选择上传文件');
		}
		//移动文件
		$info=$file->validate (['ext'=>'xls'])->rule ('uniqid')->move (ROOT_PATH .'skin'.DS .'upload'.DS .'xls');
		if ($info){
			$tmp=$_SERVER['DOCUMENT_ROOT'].'/skin/upload/xls/'.$info->getSaveName ();
			//解析XLS数据
			$xls_info=xls_info ($tmp);
			$nums=0;
			foreach ($xls_info as $vo){
				if (!empty($vo['A'])){
				    $sql=[];
					$sql['name']=$vo['A'];
					if(!empty($vo['B'])){$sql['contacts']=$vo['B'];}
					if(!empty($vo['C'])){$sql['number']=$vo['C'];}
					if(!empty($vo['D'])){$sql['tel']=$vo['D'];}
					if(!empty($vo['E'])){$sql['birthday']=$vo['E'];}
					if(!empty($vo['F'])){$sql['add']=$vo['F'];}
					if(!empty($vo['G'])){$sql['accountname']=$vo['G'];}
					if(!empty($vo['H'])){$sql['openingbank']=$vo['H'];}
					if(!empty($vo['I'])){$sql['bankaccount']=$vo['I'];}
					if(!empty($vo['J'])){$sql['tax']=$vo['J'];}
					if(!empty($vo['K'])){$sql['other']=$vo['K'];}
					if(!empty($vo['L'])){$sql['email']=$vo['L'];}
					if(!empty($vo['M'])){$sql['data']=$vo['M'];}
					customer::create ($sql);
					$nums++;
				}
			}
			push_log ('批量导入客户信息-'.$nums.'条');
			//返回信息
			$re["msg"]="success";
			$re["nums"]=$nums;
			return json ($re);
		}else {
			//返回信息
			$re["msg"]=$file->getError ();
			return json ($re);
		}
	}
	//删除供应商信息
	public function  del_supplier (){
	    $id=input ('post.id');
	    //删除判断
    	$purchase=purchaseclass::get(['supplier'=>$id,'noauth'=>'ape']);
    	$repurchase=repurchaseclass::get(['supplier'=>$id,'noauth'=>'ape']);
    	$rpurchase=rpurchaseclass::get(['supplier'=>$id,'noauth'=>'ape']);
    	if($purchase || $repurchase || $rpurchase){
    	    return json ('error');
    	}else{
    	    $supplier=supplier::get (['id'=>$id]);
    		push_log ('删除供应商信息-'.$supplier['name']);
    		supplier::destroy (input ('post.id'));
    		return json ('success');
    	}
	}
	//获取供应商信息
	public function  supplier_info (){
		return json (supplier::get (input ('post.id')));
	}
	//新增|保存供应商信息
	public function  save_supplier (){
	    $info=input ('post.');
		if (empty(input ('post.id'))){
			push_log ('创建供应商信息-'.input ('post.name'));
			//判断自定义编码
    		if(empty($info['number'])){
    			$info['number']=get_number('supplier');
    			$set_number=true;
    		}
			supplier::create ($info);
			if(isset($set_number)){set_number('supplier');}
		}else {
			push_log ('修改供应商信息-'.input ('post.name'));
			supplier::update ($info);
		}
		return json ('success');
	}
	//导入供应商信息
	public function  import_supplier (Request $request){
		//获取表单上传文件
		$file=$request->file('file');
		if (empty($file)){
			$this->error ('请选择上传文件');
		}
		//移动文件
		$info=$file->validate (['ext'=>'xls'])->rule ('uniqid')->move (ROOT_PATH .'skin'.DS .'upload'.DS .'xls');
		if ($info){
			$tmp=$_SERVER['DOCUMENT_ROOT'].'/skin/upload/xls/'.$info->getSaveName ();
			//解析XLS数据
			$xls_info=xls_info ($tmp);
			$nums=0;
			foreach ($xls_info as $vo){
				if (!empty($vo['A'])){
				    $sql=[];
					$sql['name']=$vo['A'];
					if(!empty($vo['B'])){$sql['number']=$vo['B'];}
					if(!empty($vo['C'])){$sql['add']=$vo['C'];}
					if(!empty($vo['D'])){$sql['accountname']=$vo['D'];}
					if(!empty($vo['E'])){$sql['openingbank']=$vo['E'];}
					if(!empty($vo['F'])){$sql['bankaccount']=$vo['F'];}
					if(!empty($vo['G'])){$sql['tax']=$vo['G'];}
					if(!empty($vo['H'])){$sql['contacts']=$vo['H'];}
					if(!empty($vo['I'])){$sql['tel']=$vo['I'];}
					if(!empty($vo['J'])){$sql['other']=$vo['J'];}
					if(!empty($vo['K'])){$sql['email']=$vo['K'];}
					if(!empty($vo['L'])){$sql['data']=$vo['L'];}
					supplier::create ($sql);
					$nums++;
				}
			}
			push_log ('批量导入供应商信息-'.$nums.'条');
			//返回信息
			$re["msg"]="success";
			$re["nums"]=$nums;
			return json ($re);
		}else {
			//返回信息
			$re["msg"]=$file->getError ();
			return json ($re);
		}
	}
	//删除仓库信息
	public function  del_warehouse (){
	    $id=input ('post.id');
	    $purchase=purchaseinfo::get(['warehouse'=>$id]);
	    $otpurchase=otpurchaseinfo::get(['warehouse'=>$id]);
	    $rpurchase=rpurchaseinfo::get(['warehouse'=>$id]);
	    $room=room::get(['warehouse'=>$id,'noauth'=>'ape']);
	    if($purchase || $otpurchase || $room || $rpurchase){
    	    return json ('error');
    	}else{
    	    $warehouse=warehouse::get (['id'=>$id]);
    		push_log ('删除仓库信息-'.$warehouse['name']);
    		warehouse::destroy (input ('post.id'));
    		return json ('success');
    	}
	}
	//获取仓库信息
	public function  warehouse_info (){
		return json (warehouse::get (input ('post.id')));
	}
	//新增|保存仓库信息
	public function  save_warehouse (){
	    $info=input ('post.');
		if (empty(input ('post.id'))){
			push_log ('创建仓库信息-'.input ('post.name'));
			//判断自定义编码
    		if(empty($info['number'])){
    			$info['number']=get_number('warehouse');
    			$set_number=true;
    		}
			warehouse::create ($info);
			if(isset($set_number)){set_number('warehouse');}
		}else {
			push_log ('修改仓库信息-'.input ('post.name'));
			warehouse::update ($info);
		}
		return json ('success');
	}
	//删除职员信息
	public function  del_user (){
	    $id=input ('post.id');
	    
	    $accountinfo=accountinfo::get(['user'=>$id,'noauth'=>'ape']);
        $allocation=allocationclass::get(['user|auditinguser'=>$id,'noauth'=>'ape']);
        
        $gather=gatherclass::get(['user|auditinguser'=>$id,'noauth'=>'ape']);
        $otgather=otgatherclass::get(['user|auditinguser'=>$id,'noauth'=>'ape']);
         
        $payment=paymentclass::get(['user|auditinguser'=>$id,'noauth'=>'ape']);
        $otpayment=otpaymentclass::get(['user|auditinguser'=>$id,'noauth'=>'ape']);
        
        $cashier=cashierclass::get(['user'=>$id,'noauth'=>'ape']);
        $recashier=recashierclass::get(['user|auditinguser'=>$id,'noauth'=>'ape']);
        
        $purchase=purchaseclass::get(['user|auditinguser'=>$id,'noauth'=>'ape']);
        $repurchase=repurchaseclass::get(['user|auditinguser'=>$id,'noauth'=>'ape']);
        $otpurchase=otpurchaseclass::get(['user|auditinguser'=>$id,'noauth'=>'ape']);
        
        $opurchase=opurchaseclass::get(['user|auditinguser'=>$id,'noauth'=>'ape']);
        $rpurchase=rpurchaseclass::get(['user|auditinguser'=>$id,'noauth'=>'ape']);
        
        $itemorder=itemorderclass::get(['user|auditinguser'=>$id,'noauth'=>'ape']);
        $exchange=exchangeclass::get(['user|auditinguser'=>$id,'noauth'=>'ape']);
        
        $sale=saleclass::get(['user|auditinguser'=>$id,'noauth'=>'ape']);
        $resale=resaleclass::get(['user|auditinguser'=>$id,'noauth'=>'ape']);
        $otsale=otsaleclass::get(['user|auditinguser'=>$id,'noauth'=>'ape']);
        
        $purchasebill=purchasebill::get(['user'=>$id]);
        $repurchasebill=repurchasebill::get(['user'=>$id]);
        $salebill=salebill::get(['user'=>$id]);
        $resalebill=resalebill::get(['user'=>$id]);
        $rpurchasebill=rpurchasebill::get(['user'=>$id]);
        $itemorderbill=itemorderbill::get(['user'=>$id]);
        
        $eft=eftclass::get(['user|auditinguser'=>$id,'noauth'=>'ape']);
        
        $log=log::get(['user'=>$id]);
        
        if($accountinfo || $allocation || $gather || $otgather || $payment || $otpayment || $cashier || $recashier || $purchase || $repurchase || $otpurchase || $sale || $resale || $otsale || $purchasebill || $repurchasebill || $salebill || $resalebill || $log || $opurchase || $rpurchase || $itemorder || $exchange || $rpurchasebill || $itemorderbill || $eft){
            return json ('error');
        }else{
            $user=user::get (['id'=>$id]);
    		push_log ('删除职员信息-'.$user['name']);
    		user::destroy (['id'=>$id]);
    		return json ('success');
        }
	}
	//获取职员信息
	public function  user_info (){
		return json (user::get (input ('post.')));
	}
	//保存职员信息
	public function  save_user (){
		$info=input ('post.');
		if (empty($info['id'])){
			push_log ('创建职员信息-'.$info['name']);
			$info['pwd']=md5($info['pwd']);
			user::create ($info);
		}else {
			if (empty($info['pwd'])){
				unset($info['pwd']);
			}else {
				$info['pwd']=md5($info['pwd']);
				$info['token']=user_token();
			}
			push_log ('修改职员信息-'.$info['name']);
			user::update ($info);
		}
		return json ('success');
	}
	//删除账户信息
	public function  del_account (){
		$id=input ('post.id');
		$purchase=purchaseclass::get(['account'=>$id,'noauth'=>'ape']);
		$repurchase=repurchaseclass::get(['account'=>$id,'noauth'=>'ape']);
		$sale=saleclass::get(['account'=>$id,'noauth'=>'ape']);
		$resale=resaleclass::get(['account'=>$id,'noauth'=>'ape']);
		$cashier=cashierclass::get(['account'=>$id,'noauth'=>'ape']);
		$recashier=recashierclass::get(['account'=>$id,'noauth'=>'ape']);
		$gather=gatherinfo::get(['account'=>$id]);
		$otgather=otgatherinfo::get(['account'=>$id]);
		$payment=paymentinfo::get(['account'=>$id]);
		$otpayment=otpaymentinfo::get(['account'=>$id]);
		$accountinfo=accountinfo::get(['pid'=>$id,'noauth'=>'ape']);
		$rpurchase=rpurchaseclass::get(['account'=>$id,'noauth'=>'ape']);
		$itemorder=itemorderclass::get(['account'=>$id,'noauth'=>'ape']);
		$eft=eftinfo::get(['account_id|toaccount_id'=>$id]);
		if($purchase || $repurchase || $sale || $resale || $cashier || $recashier || $gather || $otgather || $payment|| $otpayment || $accountinfo || $rpurchase || $itemorder || $eft){
		    return json('error');
		}else{
		    $account=account::get (['id'=>$id]);
    		push_log ('删除账户信息-'.$account['name']);
    		account::destroy (input ('post.id'));
    		return json('success');
		}
	}
	//获取账户信息
	public function  account_info (){
		return json (account::get (input ('post.id')));
	}
	//新增|保存账户信息
	public function  save_account (){
		if (empty(input ('post.id'))){
			push_log ('创建账户信息-'.input ('post.name'));
			account::create (input ('post.'));
		}else {
			push_log ('修改账户信息-'.input ('post.name'));
			account::update (input ('post.'));
		}
		return json ('success');
	}
	//删除商品分类信息
	public function  del_goodsclass (){
	    $id=input ('post.id');
        if (goodsclass::get (['pid'=>$id])){
			return json ('error');
		}else {
		    $goods=goods::get(['class'=>$id]);
    	    if($goods){
    	        return json ('ape_error');
    	    }else{
    	        $goodsclass=goodsclass::get (['id'=>$id]);
    			push_log ('删除商品分类信息-'.$goodsclass['name']);
    			goodsclass::destroy (['id'=>$id]);
    			return json ('success');
    	    }
		}
	}
	//获取商品分类信息
	public function  goodsclass_info (){
		return json (goodsclass::get (input ('post.id')));
	}
	//新增|保存商品分类信息
	public function  save_goodsclass (){
		if (empty(input ('post.id'))){
			push_log ('创建商品分类信息-'.input ('post.name'));
			goodsclass::create (input ('post.'));
		}else {
			push_log ('修改商品分类信息-'.input ('post.name'));
			goodsclass::update (input ('post.'));
		}
		return json ('success');
	}
	//删除计量单位信息
	public function  del_unit (){
	    $id=input ('post.id');
	    $goods=goods::get(['unit'=>$id]);
	    if($goods){
	        return json ('error');
	    }else{
	        $unit=unit::get (['id'=>$id]);
    		push_log ('删除计量单位信息-'.$unit['name']);
    		unit::destroy (['id'=>$id]);
    		return json ('success');
	    }
	}
	//获取计量单位信息
	public function  unit_info (){
		return json (unit::get (input ('post.id')));
	}
	//新增|保存计量单位信息
	public function  save_unit (){
	    $info=input ('post.');
		if (empty(input ('post.id'))){
			push_log ('创建计量单位信息-'.input ('post.name'));
			//判断自定义编码
    		if(empty($info['number'])){
    			$info['number']=get_number('unit');
    			$set_number=true;
    		}
			unit::create (input ('post.'));
			if(isset($set_number)){set_number('unit');}
		}else {
			push_log ('修改计量单位信息-'.input ('post.name'));
			unit::update (input ('post.'));
		}
		return json ('success');
	}
	//删除品牌信息
	public function  del_brand (){
	    $id=input ('post.id');
	    $goods=goods::get(['brand'=>$id]);
	    if($goods){
	        return json ('error');
	    }else{
	        $brand=brand::get (['id'=>$id]);
    		push_log ('删除品牌信息-'.$brand['name']);
    		brand::destroy (['id'=>$id]);
    		return json ('success');
	    }
	}
	//获取品牌信息
	public function  brand_info (){
		return json (brand::get (input ('post.id')));
	}
	//新增|保存品牌信息
	public function  save_brand (){
	    $info=input ('post.');
		if (empty(input ('post.id'))){
			push_log ('创建品牌信息-'.input ('post.name'));
			//判断自定义编码
    		if(empty($info['number'])){
    			$info['number']=get_number('brand');
    			$set_number=true;
    		}
			brand::create ($info);
			if(isset($set_number)){set_number('brand');}
		}else {
			push_log ('修改品牌信息-'.input ('post.name'));
			brand::update ($info);
		}
		return json ('success');
	}
	//删除条码信息
	public function  del_code (){
		$code=code::get (input ('post.id'));
		push_log ('删除条码信息-'.$code['name']);
		code::destroy (input ('post.id'));
		return json ('success');
	}
	//获取条码信息
	public function  code_info (){
		return json (code::get (input ('post.id')));
	}
	//新增|保存条码信息
	public function  save_code (){
		if (empty(input ('post.id'))){
			push_log ('创建条码信息-'.input ('post.name'));
			code::create (input ('post.'));
		}else {
			push_log ('修改条码信息-'.input ('post.name'));
			code::update (input ('post.'));
		}
		return json ('success');
	}
	//导入条码信息
	public function  import_code (Request $request){
		//获取表单上传文件
		$file=$request->file('file');
		if (empty($file)){
			$this->error ('请选择上传文件');
		}
		//移动文件
		$info=$file->validate (['ext'=>'xls'])->rule ('uniqid')->move (ROOT_PATH .'skin'.DS .'upload'.DS .'xls');
		if ($info){
			$tmp=$_SERVER['DOCUMENT_ROOT'].'/skin/upload/xls/'.$info->getSaveName ();
			//解析XLS数据
			$xls_info=xls_info ($tmp);
			$nums=0;
			foreach ($xls_info as $vo){
				if (!empty($vo['A']) && !empty($vo['B']) && !empty($vo['C'])){
					if ($vo['C']=="条形码" || $vo['C']=="二维码"){
						$sql['name']=$vo['A'];
						$sql['code']=$vo['B'];
						if ($vo['C']=="条形码"){
							$sql['type']=0;
						}else {
							$sql['type']=1;
						}
						if(!empty($vo['D'])){$sql['data']=$vo['D'];}
						code::create ($sql);
						$nums++;
					}
				}
			}
			push_log ('批量导入条码信息-'.$nums.'条');
			//返回信息
			$re["msg"]="success";
			$re["nums"]=$nums;
			return json ($re);
		}else {
			//返回信息
			$re["msg"]=$file->getError ();
			return json ($re);
		}
	}
	//删除属性信息
	//type =>1主属性 |0副属性
	public function  del_attribute (){
		$id=input ('post.id');
		$type=input ('post.type');
		$one=attribute::get (['pid'=>$id]);
		if (empty($one)){
		    $check_del=true;
		    //判断主副属性
		    if(empty($type)){
		        //副属性-此处每个表进行三次查询-防止数据错误
		        $attr_one=attr::get(['ape'=>['like','%'.$id.'_%']]);
		        $attr_two=attr::get(['ape'=>['like','%_'.$id.'%']]);
		        $attr_three=attr::get(['ape'=>$id]);
		        
		        $room_one=room::get(['attr'=>['like','%'.$id.'_%'],'noauth'=>'ape']);
		        $room_two=room::get(['attr'=>['like','%_'.$id.'%'],'noauth'=>'ape']);
		        $room_three=room::get(['attr'=>$id,'noauth'=>'ape']);
		        
		        if($attr_one || $attr_two || $attr_three || $room_one || $room_two || $room_three){
		            $check_del=false;
		            $re='ape_error';
		        }
		    }
		    if($check_del){
		        //删除
    			$attribute=attribute::get (['id'=>$id]);
    			push_log ('删除属性信息-'.$attribute['name']);
    			attribute::destroy (['id'=>$id]);
    			$re='success';
		    }
		}else {
			//不可删除
			$re='error';
		}
		return json ($re);
	}
	//获取属性信息
	public function  attribute_info (){
		$re['one']=attribute::get (input ('post.id'));
		$re['two']=attribute::all (['pid'=>input ('post.id')]);
		return json ($re);
	}
	//新增|保存属性信息
	public function  save_attribute (){
		if (empty(input ('post.id'))){
			push_log ('创建属性信息-'.input ('post.name'));
			$attribute=attribute::create (input ('post.'));
		}else {
			push_log ('修改属性信息-'.input ('post.name'));
			$attribute=attribute::update (input ('post.'));
		}
		$re['state']='success';
		$re['ape']=$attribute['id'];
		return json ($re);
	}
	//清空日志
	public function  empty_log (){
		$sql['id']=['neq',0];
		log::destroy ($sql);
		push_log ('清空日志信息');
		return json ('success');
	}
	//备份数据库
	public function  back_db (){
		$dbinfo=config('database');
		$sql=new \org \Baksql ($dbinfo['hostname'],$dbinfo['username'],$dbinfo['password'],$dbinfo['database']);
		$db_re=$sql->backup ();
		//最多备份12份文件,超出后删除最早的文件
		//1.获取所有文件
		$file_arr=$sql->get_filelist();
		if(count($file_arr) > 12){
			//删除最早的文件
			for ($i = 12; $i < count($file_arr); $i++) {
				$sql->delfilename($file_arr[$i]['name']);
			}
		}
		push_log ('备份系统数据');
		return json ('success');
	}
	//删除备份数据库
	public function  del_sn_db (){
		$file=input ('post.file');
		$dbinfo=config('database');
		$sql=new \org \Baksql ($dbinfo['hostname'],$dbinfo['username'],$dbinfo['password'],$dbinfo['database']);
		$sql->delfilename($file);
		push_log ('删除备份数据');
		return json ('success');
	}
	//恢复备份数据库
	public function  re_sn_db (){
		$file=input ('post.file');
		$dbinfo=config('database');
		$sql=new \org \Baksql ($dbinfo['hostname'],$dbinfo['username'],$dbinfo['password'],$dbinfo['database']);
		$sql->restore($file);
		push_log ('恢复备份数据');
		return json ('success');
	}
	//保存系统基础数据
	public function  save_sys_info (){
		sys::update (['id'=>1,'info'=>input ('post.')]);
		push_log ('修改系统基础数据');
		return json ('success');
	}
	//保存系统基础数据
	public function  save_sys_set (){
		sys::update (['id'=>2,'info'=>input ('post.')]);
		push_log ('修改系统功能设置');
		return json ('success');
	}
	//保存系统短信设置
	public function  save_sys_sms (){
		$info=input ('post.');
		sys::update (['id'=>3,'info'=>$info['sms_ini']]);
		push_log ('修改系统短信设置');
		return json ('success');
	}
	//保存系统收银设置
	public function  save_sys_cashier (){
		$info=input ('post.');
		sys::update (['id'=>4,'info'=>$info['info']]);
		push_log ('修改系统收银设置');
		return json ('success');
	}
	//上传商品图片
	public function  up_goods_img (Request $request){
		$file=$request->file('file');
		//默认通用的上传
		if (empty($file)){
			$file=$request->file('upfile');
			//检查是不是百度编辑器上传的
			if (empty($file)){
				$this->error ('请选择上传文件');
			}
		}
		//百度编辑器会带上get数据，常规的不会
		if (empty(input ('get.'))){
			$type=1;
			//常规上传
		}else {
			$type=0;
			//百度上传
		}
		//限制单图500KB，
		$info=$file->validate (['size'=>513000,'ext'=>'jpg,png,gif'])->rule ('uniqid')->move (ROOT_PATH .'skin'.DS .'upload'.DS .'goods');
		if ($info){
			$file_name=$info->getSaveName ();
			$url_info='/skin/upload/goods/'.$file_name;
			//此处把常规的和百度编辑器的都组合到一起，也不冲突，就是有点麻烦。
			//常规返回
			$re["code"]="success";
			$re["img"]=$url_info;
			//远程图片地址
			//百度编辑器返回
			$re["state"]='SUCCESS';
			$re["url"]=$url_info;
			//返回信息,此处兼容百度编辑器
		}else {
			//返回信息
			$re["state"]=$file->getError ();
			//百度编辑器返回用
			$re["code"]=$re["state"];
			//常规返回用
		}
		if ($type){
			//常规返回json
			return json ($re);
		}else {
			//百度编辑器返回,不带http_json头
			echo json_encode($re);
		}
	}
	//获取商品信息
	public function  goods_info (){
		$id=input ('post.id');
		$goods=goods::get ($id);
		$goods['more']=attr::all (['pid'=>$id]);
		//带上辅助属性
		return json ($goods);
	}
	//保存商品详情
	public function  save_goods (){
		$tmp=input ('post.');
		$info=$tmp['goods_info'];
		if (empty($info['id'])){
			//新增
			//商品图片不为空时编码
			if (empty($info['more']['imgs'])){
			    $info['more']['imgs']='';
			}else{
			    $info['more']['imgs']=json_encode($info['more']['imgs']);
			}
			$sql=array_merge($info['main'],$info['more']);//合并数组
			$sql['py']=text_to_py ($sql['name']);//加入拼音首拼
			//判断自定义编码
    		if(empty($sql['number'])){
    			$sql['number']=get_number('goods');
    			$set_number=true;
    		}
			$add_id=goods::create ($sql);
			if(isset($set_number)){set_number('goods');}
			if (!empty($info['attr'])){
				foreach ($info['attr'] as $vo){
					$vo['pid']=$add_id['id'];
					attr::create ($vo);
				}
			}
			push_log ('新增商品信息-'.$info['main']['name']);
		}else {
			//修改
			//商品图片不为空时编码
			if (empty($info['more']['imgs'])){
				$info['more']['imgs']='';
			}else{
			    $info['more']['imgs']=json_encode($info['more']['imgs']);
			}
			$sql=array_merge($info['main'],$info['more']);
			//合并数组
			$sql['id']=$info['id'];
			$sql['py']=text_to_py ($sql['name']);
			//加入拼音首拼
			goods::update ($sql);
			attr::destroy (['pid'=>$sql['id']]);
			//删除原有辅助属性
			if (!empty($info['attr'])){
				foreach ($info['attr'] as $vo){
					$vo['pid']=$sql['id'];
					attr::create ($vo);
				}
			}
			push_log ('修改商品信息-'.$info['main']['name']);
		}
		return json ('success');
	}
	//删除商品信息
	public function  del_goods (){
		$id=input ('post.id');
		$purchase=purchaseinfo::get(['goods'=>$id]);
		$repurchase=repurchaseinfo::get(['goods'=>$id]);
		$otpurchase=otpurchaseinfo::get(['goods'=>$id]);
		$sale=saleinfo::get(['goods'=>$id]);
		$resale=resaleinfo::get(['goods'=>$id]);
		$otsale=otsaleinfo::get(['goods'=>$id]);
		$cashier=cashierinfo::get(['goods'=>$id]);
		$recashier=recashierinfo::get(['goods'=>$id]);
		$allocation=allocationinfo::get(['goods'=>$id]);
		$opurchase=opurchaseinfo::get(['goods'=>$id]);
		$rpurchase=rpurchaseinfo::get(['goods'=>$id]);
		if($purchase || $repurchase || $otpurchase || $sale || $resale || $otsale || $cashier || $recashier || $allocation || $opurchase || $rpurchase){
		    return json ('error');
		}else{
		    $goods=goods::get ($id);
    		attr::destroy (['pid'=>$goods['id']]);//删除该商品的辅助属性
    		push_log ('删除商品信息-'.$goods['name']);
    		//判断是否存在商品图片
    		if(!empty($goods['imgs'])){
    		    foreach ($goods['imgs'] as $img_vo) {
    		        @unlink($_SERVER['DOCUMENT_ROOT'].$img_vo);
    		    }
    		}
    		goods::destroy ($id);
    		return json ('success');
		}
	}
	//导入商品信息
	public function  import_goods (Request $request){
		//获取表单上传文件
		$file=$request->file('file');
		if (empty($file)){
			$this->error ('请选择上传文件');
		}
		//移动文件
		$info=$file->validate (['ext'=>'xls'])->rule ('uniqid')->move (ROOT_PATH .'skin'.DS .'upload'.DS .'xls');
		if ($info){
			$tmp=$_SERVER['DOCUMENT_ROOT'].'/skin/upload/xls/'.$info->getSaveName ();
			//解析XLS数据
			$xls_info=xls_info ($tmp);
			$nums=0;
			foreach ($xls_info as $vo){
				if (!empty($vo['A']) && !empty($vo['D']) && is_numeric($vo['I']) && is_numeric($vo['J']) && is_numeric($vo['K'])){
				    $sql=[];
					$sql['name']=$vo['A'];
					$sql['py']=text_to_py($vo['A']);
					//品牌处理
					if ($vo['B']==="暂不关联"){
						$sql['brand']=0;
					}else {
						$brand=brand::get (['name'=>$vo['B']]);
						if ($brand){
							$sql['brand']=$brand['id'];
						}else {
							$tmp_brand=brand::create (['name'=>$vo['B'],'data'=>'导入自动创建']);
							$sql['brand']=$tmp_brand['id'];
						}
					}
					if(!empty($vo['C'])){$sql['number']=$vo['C'];}
					//分类处理
					$class=goodsclass::get (['name'=>$vo['D']]);
					if ($class){
						$sql['class']=$class['id'];
					}else {
						$tmp_class=goodsclass::create (['name'=>$vo['D'],'pid'=>0,'data'=>'导入自动创建']);
						$sql['class']=$tmp_class['id'];
					}
					if(!empty($vo['E'])){$sql['spec']=$vo['E'];}
					if(!empty($vo['F'])){$sql['code']=$vo['F'];}
					//默认仓库处理
					if ($vo['G']==="暂不关联"){
						$sql['warehouse']=0;
					}else {
						$warehouse=warehouse::get (['name'=>$vo['G']]);
						if ($warehouse){
							$sql['warehouse']=$warehouse['id'];
						}else {
							$tmp_warehouse=warehouse::create (['name'=>$vo['G'],'data'=>'导入自动创建']);
							$sql['warehouse']=$tmp_warehouse['id'];
						}
					}
					//单位处理
					if ($vo['H']==="暂不关联"){
						$sql['unit']=0;
					}else {
						$unit=unit::get (['name'=>$vo['H']]);
						if ($unit){
							$sql['unit']=$unit['id'];
						}else {
							$tmp_unit=unit::create (['name'=>$vo['H'],'data'=>'导入自动创建']);
							$sql['unit']=$tmp_unit['id'];
						}
					}
					$sql['buy']=$vo['I'];
					$sql['sell']=$vo['J'];
					$sql['retail']=$vo['K'];
					if(!empty($vo['L'])){$sql['stocktip']=$vo['L'];}
					if(!empty($vo['M'])){$sql['location']=$vo['M'];}
					if(!empty($vo['N'])){$sql['integral']=$vo['N'];}
					if(!empty($vo['O'])){$sql['data']=$vo['O'];}
					goods::create ($sql);
					$nums++;
				}
			}
			push_log ('批量导入商品信息-'.$nums.'条');
			//返回信息
			$re["msg"]="success";
			$re["nums"]=$nums;
			return json ($re);
		}else {
			//返回信息
			$re["msg"]=$file->getError ();
			return json ($re);
		}
	}
	//弹框-商品信息
	public function  goods_info_list (){
		$info=input ('post.');
		$sql['name|py']=['like','%'.$info['name'].'%'];
		$sql['code']=['like','%'.$info['code'].'%'];
		$sql['number']=['like','%'.$info['number'].'%'];
		$sql['spec']=['like','%'.$info['spec'].'%'];
		$sql['data']=['like','%'.$info['data'].'%'];
		if (!empty($info['class'])){
			$sql['class']=['in',goodsclass_more_arr ($info['class']),'OR'];
		}
		if (!empty($info['warehouse'])){
			$sql['warehouse']=$info['warehouse'];
		}
		if (!empty($info['brand'])){
			$sql['brand']=$info['brand'];
		}
		if (!empty($info['unit'])){
			$sql['unit']=$info['unit'];
		}
		$count=goods::where ($sql)->count();//获取总条数
		$arr=goods::where ($sql)->field ('id,imgs,name,number,class,unit,brand,warehouse,buy,sell,retail,code,spec,stocktip,location,integral,data,serialtype')->page($info['page'].','.$info['limit'])->select ()->toArray ();//查询分页数据
		//补充辅助属性数组
		foreach ($arr as $key=>$vo){
		    if(empty($vo['imgs'])){
		        $arr[$key]['img']='/skin/images/main/none.png';
		    }else{
		        $arr[$key]['img']=$vo['imgs'][0];
		    }
			$arr[$key]['attr']=attr::where (['enable'=>1,'pid'=>$vo['id']])->field ('ape,buy,sell')->select ();
		}
		$re['code']=0;
		$re['msg']='获取成功';
		$re['count']=$count;
		$re['data']=$arr;
		return json ($re);
	}
	//扫码录入-商品表
	public function  goods_scan (){
		$info=input ('post.');
		if (empty($info['type'])){
			//名称|拼音
			$sql['name|py']=['like','%'.$info['val'].'%'];
			//包含查询
		}else {
			//条形码
			$sql['code']=$info['val'];
		}
		//查询数据
		$arr=goods::where ($sql)->field ('id,name,number,class,unit,brand,warehouse,buy,sell,retail,code,spec,stocktip,location,integral,data,serialtype')->select ();
		if (count($arr)===0){
			//无数据
			$re['type']=0;
		}
		elseif (count($arr)===1){
			//只有一条
			$re['type']=1;
			$arr[0]['attr']=attr::where (['enable'=>1,'pid'=>$arr[0]['id']])->field ('ape,buy,sell')->select ();
			$re['info']=$arr[0];
		}else {
			//有多条|弹框处理
			$re['type']=2;
		}
		return json ($re);
	}
	//设置表格
	public function  set_tabinfo (){
		$info=input ('post.');
		$sql['name']=$info['name'];
		$save['main']=json_encode($info['main'],JSON_UNESCAPED_UNICODE);
		tabinfo::where ($sql)->update ($save);
		return json ('success');
	}
	//上传单据文件
	public function  up_file (Request $request){
		$file=$request->file('file');
		if (empty($file)){
			$this->error ('请选择上传文件');
		}
		//单文件限制2MB
		$info=$file->validate (['size'=>2097152,'ext'=>'png,gif,jpg,jpeg,zip,rar,pdf,doc,docx,xls,txt'])->rule ('uniqid')->move (ROOT_PATH .'skin'.DS .'upload'.DS .'file');
		if ($info){
			$file_name=$info->getSaveName ();
			$file_url='/skin/upload/file/'.$file_name;
			$re["code"]="success";
			$re["file"]=$file_url;
		}else {
			//返回信息
			$re["code"]=$file->getError ();
		}
		return json ($re);
	}
	//保存购货单
	public function  save_purchase (){
		$input=input ('post.');
		$info=$input['info'];
		//预先判断串号重复情况
		foreach ($info as $key=>$check_vo){
			//如果为串码商品,并且串号存在
			if (!empty($check_vo['serialtype']) && array_key_exists('serial',$check_vo)){
				//判断 - 查找串码状态为未销售的
				if (serial::get (['code'=>['in',explode(',',$check_vo['serial']),'OR'],'type'=>['neq',2]])){
				    //找到重复串码
					return json (['state'=>'serial_repeat','row'=>$key+1]);
					exit ;
				}
			}
		}
		$class_sql['supplier']=$input['supplier'];
		$class_sql['time']=$input['time'];
		$class_sql['number']=$input['number'];
		$class_sql['total']=$input['total'];
		$class_sql['actual']=$input['actual'];
		$class_sql['money']=$input['money'];
		$class_sql['user']=$input['user'];
		$class_sql['account']=$input['account'];
		$class_sql['billtype']=-1;
		if (!empty($input['file'])){
			$class_sql['file']=$input['file'];
		}
		$class_sql['data']=$input['data'];
		if (!isset($input['id'])){
			//新增
			$class=purchaseclass::create ($class_sql);
			set_number('purchase');
		}else {
			//更新
			$class_sql['id']=$input['id'];
			$class=purchaseclass::update ($class_sql);
			purchaseinfo::destroy (['pid'=>$input['id']]);
			//删除旧info数据
		}
		$timemark=time();//时间标识
		//详情
		foreach ($info as $vo){
			$info_sql=[];
			$info_sql['pid']=$class['id'];
			$info_sql['goods']=$vo['goods'];
			if ($vo['attr']!=="-1"){
				$info_sql['attr']=$vo['attr'];
			}
			$info_sql['warehouse']=$vo['warehouse'];
			//判断串号
			if (array_key_exists('serial',$vo) && !empty($vo['serialtype'])){
				$info_sql['serial']=$vo['serial'];
			}
			$info_sql['nums']=$vo['nums'];
			$info_sql['price']=$vo['price'];
			$info_sql['total']=$vo['total'];
			if (array_key_exists('batch',$vo) && !empty($vo['batch'])){
				$info_sql['batch']=$vo['batch'];
			}
			if (array_key_exists('data',$vo)){
				$info_sql['data']=$vo['data'];
			}
			$info_sql['timemark']=0;//时间标识
			purchaseinfo::create ($info_sql);
		}
		push_log ('提交购货单-'.$input['number']);
		//判断自动审核
		$sys=sys::all ();
		if (empty($sys['1']['info']['auditing'])){
			$this->auditing_purchase ($class['id'],true);
		}
		return json (['state'=>'success']);
	}
	//删除购货单
	public function  del_purchase (){
		$id=input ('post.id');
		$purchaseclass=purchaseclass::where (['id'=>$id])->find ();
		if (empty($purchaseclass['type']['ape'])){
			//未审核可删除
			purchaseclass::destroy (['id'=>$id]);
			//删除class
			purchaseinfo::destroy (['pid'=>$id]);
			//删除info
		}
		push_log ('删除购货单-'.$purchaseclass['number']);
		return json ('success');
	}
	//保存打印模板
	public function  save_printcode (){
		$input=input ('post.');
		printcode::where (['name'=>$input['name']])->update ([$input['type']=>$input['main']]);
		push_log ('保存打印模板');
		return json ('success');
	}
	//恢复打印默认模板
	public function  default_printcode (){
		$input=input ('post.');
		$info=printcode::where (['name'=>$input['name']])->find ();
		//获取默认值
		printcode::where (['id'=>$info['id']])->update ([$input['type']=>$info[$input['type'].'default']]);
		//恢复默认
		push_log ('恢复默认打印模板');
		return json ('success');
	}
	//审核|反审核购货单
	public function  auditing_purchase ($id,$auth=false){
		//兼容自动审核
		if (empty($id)){
			$id=input ('post.id');
		}
		$purchaseclass=purchaseclass::get ($id);
		$purchaseinfo=purchaseinfo::where (['pid'=>$id])->select ()->toArray ();
		//读取info
		if (empty($purchaseclass['type']['ape'])){
			//审核
			//预先判断串号重复情况
			foreach ($purchaseinfo as $key=>$check_vo){
				//判断串号是否设置
				if (!empty($check_vo['serial'])){
				    //查找串码状态为在库和销售
					if (serial::get (['code'=>['in',explode(',',$check_vo['serial']),'OR'],'type'=>['neq',2]])){
						if ($auth){
							//自动审核
							push_log ('自动审核购货单-'.$purchaseclass['number'].'失败，原因：串码重复');
							exit ;
						}else {
							//手动审核
							return json (['state'=>'serial_repeat','row'=>$key+1]);
							exit ;
						}
					}
				}
			}
			$timemark=time();//时间标识
			$goods_repeat_arr=[];
			foreach ($purchaseinfo as $purchaseinfo_vo){
				//循环保存数据
				$room_sql=[];
				$room_sql['warehouse']=$purchaseinfo_vo['warehouse']['ape'];
				$room_sql['goods']=$purchaseinfo_vo['goods']['ape'];
				$room_sql['attr']=$purchaseinfo_vo['attr']['ape'];
				$room_sql['batch']=$purchaseinfo_vo['batch'];
				$room=room::get($room_sql);
				//判断仓储
				if (empty($room)){
					//新增仓储数据
					$room_sql['nums']=$purchaseinfo_vo['nums'];
					$room_sql['timemark']=$timemark;//时间标识
					$room=room::create ($room_sql);//时间标识
					$room_oldtimemark=0;//初始化旧时间标识
				} else {
					//更新仓储数据
					room::where (['id'=>$room['id']])->update([
					    'nums'=>$room['nums']+$purchaseinfo_vo['nums'],
					    'timemark'=>$timemark
					]);
					//增加库存数量
					$room_oldtimemark=$room['timemark'];//转存仓储旧时间标识
				}
				purchaseinfo::update (['id'=>$purchaseinfo_vo['id'],'room'=>$room['id'],'timemark'=>$timemark]);//info保存仓储ID以及时间标识
				//判断是否有重复商品
				if(array_key_exists($room['id'],$goods_repeat_arr)){
				    $room_oldtimemark=$goods_repeat_arr[$room['id']];//获取仓储旧时间标识
				}else{
				    $goods_repeat_arr[$room['id']]=$room['timemark'];
				}
				//新增仓储详情
				$roominfo_sql['pid']=$room['id'];
				$roominfo_sql['type']=1;
				$roominfo_sql['class']=$id;
				$roominfo_sql['info']=$purchaseinfo_vo['id'];
				$roominfo_sql['nums']=$purchaseinfo_vo['nums'];
				$roominfo_sql['timemark']=$timemark;//时间标识
				$roominfo_sql['oldtimemark']=$room_oldtimemark;//时间标识
				roominfo::create ($roominfo_sql);
				//判断批次
				if (!empty($purchaseinfo_vo['serial'])){
					//串号存在
					$tmp_serial=explode(',',$purchaseinfo_vo['serial']);
					foreach ($tmp_serial as $tmp_serial_vo){
					    //判断串码多次录入情况
					    $serial_ape=serial::get(['code'=>$tmp_serial_vo]);
					    if(empty($serial_ape)){
					        //新录入
					        $oldroom=0;//初始化旧仓库ID
					        $serial_oldtimemark=0;//初始化串码旧时间标识
				        	$serial_info=serial::create ([
				        	    'room'=>$room['id'],
				        	    'code'=>$tmp_serial_vo,
				        	    'type'=>0,
				        	    'timemark'=>$timemark,
				        	]);//增加串号
					    }else{
					        //多次录入
					        $oldroom=$serial_ape['room'];//旧ROOM_ID
					        $serial_oldtimemark=$serial_ape['timemark'];//转存旧串码时间标识
					        $serial_info=serial::update([
					            'id'=>$serial_ape['id'],
					            'room'=>$room['id'],
					            'type'=>0,
					            'timemark'=>$timemark,
					        ]);//更新串号
					    }
						serialinfo::create ([
						    'pid'=>$serial_info['id'],
						    'type'=>1,
						    'class'=>$id,
						    'oldroom'=>$oldroom,//旧仓储ID
						    'timemark'=>$timemark,//新时间标识
						    'oldtimemark'=>$serial_oldtimemark//旧时间标识
						]);//增加串号详情
					}
				}
			}
			//获取资金状态
			if (empty($purchaseclass['money'])){
				$billtype=0;
				//未结算
			}
			elseif ($purchaseclass['money']==$purchaseclass['actual']){
				$billtype=2;
				//已结算
			}else {
				$billtype=1;
				//部分结算
			}
			//更新class审核状态和对账单状态
			purchaseclass::update (['id'=>$id,'type'=>1,'auditinguser'=>Session ('is_user_id'),'auditingtime'=>time(),'billtype'=>$billtype]);
			//资金非零
			if (!empty($purchaseclass['money'])){
				//增加对账单
				$bill_info=purchasebill::create (['pid'=>$id,'account'=>$purchaseclass['account']['ape'],'money'=>$purchaseclass['money'],'data'=>'系统自动生成','user'=>Session ('is_user_id'),'time'=>time()]);
				account::where (['id'=>$purchaseclass['account']['ape']])->setDec ('balance',$purchaseclass['money']);
				//操作资金-减
				accountinfo::create (['pid'=>$purchaseclass['account']['ape'],'set'=>0,'money'=>$purchaseclass['money'],'type'=>1,'time'=>time(),'user'=>Session ('is_user_id'),'class'=>$id,'number'=>$purchaseclass['number'],'only'=>$bill_info['id']]);
				//资金操作-详情
			}
			set_summary('purchaseclass',$id,true);//更新Summary
			if ($auth){
				push_log ('自动审核购货单-'.$purchaseclass['number']);
			}else {
				//手动
				push_log ('审核购货单-'.$purchaseclass['number']);
			}
		}else {
			//反审核
			//判断库存中的是否够反审核|串码是否已经使用
			$tmp_arr=room::where (['id'=>['in',array_column ($purchaseinfo,'room'),'OR']])->select ()->toArray();//取出所有商品的仓储数据
			foreach ($purchaseinfo as $key=>$check_vo){
			    $tmp_val=find_arr_key_val ($tmp_arr,'id',$check_vo['room']);//获取当前仓储ID的数据
			    //判断逆操作
			    //判断当前详情的时间标识与仓储ID中的时间标识是否相同
			    if(!empty($tmp_val['timemark']!==$check_vo['timemark'])){
			        return json (['state'=>'set_error','row'=>$key+1]);
			        exit;
			    }else{
			        //开始判断当前时间标识是否存在后续操作
    			    if(roominfo::get([
    			        'pid'=>$check_vo['room'],
    			        'timemark'=>['gt',$check_vo['timemark']]
    			     ])){
    			        return json (['state'=>'set_error','row'=>$key+1]);
                        exit;
    			    }
			    }
			}
			foreach ($purchaseinfo as $purchaseinfo_vo){
			    room::where (['id'=>$purchaseinfo_vo['room']])->setDec ('nums',$purchaseinfo_vo['nums']);//减少库存
				$room_info=roominfo::get([
				    'pid'=>$purchaseinfo_vo['room'],
				    'type'=>1,
				    'info'=>$purchaseinfo_vo['id']
				    ]);//获取仓储详情
				room::update([
				    'id'=>$purchaseinfo_vo['room'],
				    'timemark'=>$room_info['oldtimemark']
				    ]);//还原旧时间标识
				purchaseinfo::update (['id'=>$purchaseinfo_vo['id'],'room'=>0,'timemark'=>0]);//info删除仓储ID还原时间标识
				if(!empty($purchaseinfo_vo['serial'])){
				    $serial_arr=serial::where (['code'=>['in',explode(',',$purchaseinfo_vo['serial']),'OR']])->select();//获取串号详情
				    foreach ($serial_arr as $serial_vo) {
				        $serial_info=serialinfo::get(['pid'=>$serial_vo['id'],'type'=>1,'timemark'=>$serial_vo['timemark']]);//获取当前时间标识的串码详情
				        serial::update([
				            'id'=>$serial_vo['id'],
				            'room'=>$serial_info['oldroom'],
				            'type'=>2,
				            'timemark'=>$serial_info['oldtimemark']
				        ]);//还原旧仓储ID|设置状态|还原旧时间标识
				        serialinfo::destroy(['id'=>$serial_info['id']]);//删除串码详情
				    }
				}
			}
			roominfo::destroy (['type'=>1,'class'=>$id]);//删除仓储详情
			purchaseclass::update (['id'=>$id,'type'=>0,'auditinguser'=>0,'auditingtime'=>0,'billtype'=>-1]);
			//复原class审核状态和对账单状态
			//资金非零
			if (!empty($purchaseclass['money'])){
				$purchasebill=purchasebill::where (['pid'=>$id])->select ();
				//获取对账单操作记录
				//复原资金
				foreach ($purchasebill as $purchasebill_vo){
					account::where (['id'=>$purchasebill_vo['account']['ape']])->setInc ('balance',$purchasebill_vo['money']);
					//增加金额
				}
				purchasebill::destroy (['pid'=>$id]);
				//删除对账单
				accountinfo::destroy (['type'=>1,'class'=>$id]);
				//删除资金详情
			}
			set_summary('purchaseclass',$id,false);//更新Summary
			push_log ('反审核购货单-'.$purchaseclass['number']);
		}
		return json (['state'=>'success']);
	}
	//购货对账单详情
	public function  purchasebill_info (){
		$id=input ('post.id');
		$re['class']=purchaseclass::get (['id'=>$id]);
		$re['bill']=purchasebill::where (['pid'=>$id])->select ();
		//数据鉴权
		$user_auth=json_decode(user_info('auth'),true);
		foreach ($re['bill'] as $key => $vo) {
		    if(!empty($user_auth) && !empty($user_auth['account'])){
		        if(in_array($vo['account']['ape'],$user_auth['account'])){
		            $re['bill'][$key]['auth_info']=true;
		        }else{
		            $re['bill'][$key]['auth_info']=false;
		        }
		    }else{
		        $re['bill'][$key]['auth_info']=true;
		    }
		}
		return json ($re);
	}
	//保存购货对账单操作
	public function  save_purchasebill (){
		$input=input ('post.');
		$purchaseclass=purchaseclass::get (['id'=>$input['id']]);
		//判断合法性
		if ($input['sum']<=$purchaseclass['actual']-$purchaseclass['money']){
			//获取资金状态
			if ($purchaseclass['money']+$input['sum']==$purchaseclass['actual']){
				$billtype=2;
				//已结算
			}else {
				$billtype=1;
				//部分结算
			}
			purchaseclass::update (['id'=>$input['id'],'money'=>$purchaseclass['money']+$input['sum'],//金额增加
			'billtype'=>$billtype//资金状态
			]);
			$bill_info=purchasebill::create (['pid'=>$input['id'],'account'=>$input['account'],'money'=>$input['sum'],'data'=>input ('post.data'),'user'=>Session ('is_user_id'),'time'=>time()]);
			
			account::where (['id'=>$input['account']])->setDec ('balance',$input['sum']);
			//操作资金-减
			accountinfo::create (['pid'=>$input['account'],'set'=>0,'money'=>$input['sum'],'type'=>1,'time'=>time(),'user'=>Session ('is_user_id'),'class'=>$purchaseclass['id'],'number'=>$purchaseclass['number'],'only'=>$bill_info['id']]);
			//资金操作-详情
		}
		push_log ('增加购货对账单详情-'.$purchaseclass['number']);
		return json ('success');
	}
	//删除购货对账单详情
	public function  del_purchasebill (){
		$id=input ('post.id');
		$purchasebill=purchasebill::get (['id'=>$id]);
		$purchaseclass=purchaseclass::get (['id'=>$purchasebill['pid']]);
		//获取资金状态
		if ($purchasebill['money']==$purchaseclass['money']){
			$billtype=0;
			//未结算
		}else {
			$billtype=1;
			//部分结算
		}
		purchaseclass::update (['id'=>$purchasebill['pid'],'money'=>$purchaseclass['money']-$purchasebill['money'],//金额减少
		'billtype'=>$billtype//资金状态 
		]);
		account::where (['id'=>$purchasebill['account']['ape']])->setInc ('balance',$purchasebill['money']);
		//操作资金-加
		accountinfo::destroy (['pid'=>$purchasebill['account']['ape'],'type'=>1,'only'=>$id]);
		//删除资金操作-详情
		purchasebill::destroy (['id'=>$id]);
		push_log ('删除购货对账单详情-'.$purchaseclass['number']);
		return json ('success');
	}
	//弹框-商品信息-仓库
	public function  room_info_list (){
		if (!empty(input ('post.name'))){
			$goods_sql['name|py']=['like','%'.input ('post.name').'%'];
		}
		if (!empty(input ('post.code'))){
			$goods_sql['code']=['like','%'.input ('post.code').'%'];
		}
		//判断商品类型
		if (!empty(input ('post.class'))){
			$goods_sql['class']=['in',goodsclass_more_arr (input ('post.class')),'OR'];
		}
		if (!empty(input ('post.number'))){
			$goods_sql['number']=['like','%'.input ('post.number').'%'];
		}
		if (!empty(input ('post.spec'))){
			$goods_sql['spec']=['like','%'.input ('post.spec').'%'];
		}
		//判断所属品牌
		if (!empty(input ('post.warehouse'))){
			$goods_sql['warehouse']=input ('post.warehouse');
		}
		//判断所属品牌
		if (!empty(input ('post.brand'))){
			$goods_sql['brand']=input ('post.brand');
		}
		if (isset($goods_sql)){
			$goods_arr=arrayChange (goods::where ($goods_sql)->field ('id')->select ()->toArray (),'id');
			$sql['goods']=['in',$goods_arr,'OR'];
		}
		if (!empty(input ('post.serial'))){
			$sql['id']=['in',arrayChange (serial::where (['code'=>['like','%'.input ('post.serial').'%','type'=>0]])->field ('room')->select ()->toArray (),'room'),'OR'];
		}
		if (!empty(input ('post.batch'))){
			$sql['batch']=['like','%'.input ('post.batch').'%'];
		}
		//此处的zero作为判断单据类型 0:正常流程 1:反向流程（零售退货。销货退货）
		if(empty(input ('post.zero'))){
		    //正常流程不查找零库存
		    $sql['nums']=['neq',0];
		}else{
		    //反向流程查找零库存
		    $sql['nums']=['egt',0];
		}
		$count=room::where ($sql)->count();//获取总条数
		$arr=room::where ($sql)->page(input ('post.page').','.input ('post.limit'))->select ()->toArray ();//查询分页数据
		$new_arr=[];
		//补充辅助属性数组
		foreach ($arr as $vo){
			$tmp['id']=$vo['id'];
			$tmp['goods']=$vo['goods']['ape'];
			$tmp['name']=$vo['goods']['info']['name'];
			$tmp['warehouse_id']=$vo['warehouse']['ape'];
			$tmp['warehouse']=$vo['warehouse']['info']['name'];
			$tmp['nums']=$vo['nums'];
			$tmp['attr']=$vo['attr']['name'];
			//此处兼容正反流程
			if(empty(input ('post.zero'))){
    		    //正常流程查询未销售串码
    		    $tmp['serial']=implode(',',arrayChange (serial::where (['room'=>$vo['id'],'type'=>0])->field ('code')->select ()->toArray (),'code'));
    		}else{
    		    //反向流程查询已销售串码
    		    //判断是否为串码商品
        		if(!empty($vo['goods']['info']['serialtype'])){
        		    $tmp['serial']=implode(',',arrayChange (serial::where (['room'=>$vo['id'],'type'=>1])->field ('code')->select ()->toArray (),'code'));
        		    //判断串码是否为空
        		    if(empty($tmp['serial'])){
        		        continue;
        		    }
        		}
    		}
			$tmp['batch']=$vo['batch'];
			$tmp['number']=$vo['goods']['info']['number'];
			$tmp['class']=$vo['goods']['info']['class']['info']['name'];
			$tmp['unit']=$vo['goods']['info']['unit']['info']['name'];
			$tmp['brand']=$vo['goods']['info']['brand']['info']['name'];
			//判断价格是否存在辅助属性
			if(empty($vo['attr']['ape'])){
			    $tmp['buy']=$vo['goods']['info']['buy'];
			    $tmp['sell']=$vo['goods']['info']['sell'];
			    $tmp['retail']=$vo['goods']['info']['retail'];
			    $tmp['stocktip']=$vo['goods']['info']['stocktip'];
			}else{
			    $attr_price=attr::get(['pid'=>$vo['goods']['ape'],'ape'=>$vo['attr']['ape'],'enable'=>1]);
			    //兼容辅助属性倍修改的默认价格问题
			    if(empty($attr_price)){
			        //读取无属性价格
			        $tmp['buy']=$vo['goods']['info']['buy'];
    			    $tmp['sell']=$vo['goods']['info']['sell'];
    			    $tmp['retail']=$vo['goods']['info']['retail'];
    			    $tmp['stocktip']=$vo['goods']['info']['stocktip'];
			    }else{
			        $tmp['buy']=$attr_price['buy'];
    			    $tmp['sell']=$attr_price['sell'];
    			    $tmp['retail']=$attr_price['retail'];
    			    $tmp['stocktip']=$attr_price['stocktip'];
			    }
			}
			$tmp['code']=$vo['goods']['info']['code'];
			$tmp['spec']=$vo['goods']['info']['spec'];
			$tmp['location']=$vo['goods']['info']['location'];
			$tmp['integral']=$vo['goods']['info']['integral'];
			$tmp['data']=$vo['goods']['info']['data'];
			if(empty($vo['goods']['info']['imgs'])){
			    $tmp['img']='/skin/images/main/none.png';
			}else{
			    $tmp['img']=$vo['goods']['info']['imgs'][0];
			}
			array_push($new_arr,$tmp);
		}
		$re['code']=0;
		$re['msg']='获取成功';
		$re['count']=$count;
		$re['data']=$new_arr;
		return json ($re);
	}
	//扫码录入-库存表
	public function  room_scan (){
		$info=input ('post.');
		if (empty($info['type'])){
			//名称|拼音
			$goods_sql['name|py']=['like','%'.$info['val'].'%'];
			//包含查询
		}else {
			//条形码
			$goods_sql['code']=$info['val'];
		}
		//查询数据
		$arr=arrayChange (goods::where ($goods_sql)->field ('id')->select ()->toArray (),'id');
		$scan_sql['goods']=['in',$arr,'OR'];
		if(empty(input('post.zero'))){
		    $scan_sql['nums']=['neq',0];
		}else{
		    $scan_sql['nums']=['egt',0];
		}
		$room=room::where ($scan_sql)->select ()->toArray ();
		if (count($room)===0){
			//无数据
			$re['type']=0;
		}elseif (count($room)===1){
			//只有一条
			$re['type']=1;
			//还原数据
			$re['info']=[];
			foreach ($room as $vo){
				$tmp['id']=$vo['id'];
				$tmp['goods']=$vo['goods']['ape'];
				$tmp['name']=$vo['goods']['info']['name'];
				$tmp['warehouse_id']=$vo['warehouse']['ape'];
				$tmp['warehouse']=$vo['warehouse']['info']['name'];
				$tmp['nums']=$vo['nums'];
				$tmp['attr']=$vo['attr']['name'];
				if(empty(input('post.zero'))){
        		    $tmp['serial']=implode(',',arrayChange (serial::where (['room'=>$vo['id'],'type'=>0])->field ('code')->select ()->toArray (),'code'));
        		}else{
        		    //判断是否为串码商品
            		if(!empty($vo['goods']['info']['serialtype'])){
            		    $tmp['serial']=implode(',',arrayChange (serial::where (['room'=>$vo['id'],'type'=>1])->field ('code')->select ()->toArray (),'code'));
            		    //判断串码是否为空
            		    if(empty($tmp['serial'])){
            		        continue;
            		    }
            		}
        		}
				$tmp['batch']=$vo['batch'];
				$tmp['number']=$vo['goods']['info']['number'];
				$tmp['class']=$vo['goods']['info']['class']['info']['name'];
				$tmp['unit']=$vo['goods']['info']['unit']['info']['name'];
				$tmp['brand']=$vo['goods']['info']['brand']['info']['name'];
				//判断价格是否存在辅助属性
    			if(empty($vo['attr']['ape'])){
    			    $tmp['buy']=$vo['goods']['info']['buy'];
    			    $tmp['sell']=$vo['goods']['info']['sell'];
    			    $tmp['retail']=$vo['goods']['info']['retail'];
    			}else{
    			    $attr_price=attr::get(['pid'=>$vo['goods']['ape'],'ape'=>$vo['attr']['ape'],'enable'=>1]);
    			    $tmp['buy']=$attr_price['buy'];
    			    $tmp['sell']=$attr_price['sell'];
    			    $tmp['retail']=$attr_price['retail'];
    			}
				$tmp['code']=$vo['goods']['info']['code'];
				$tmp['spec']=$vo['goods']['info']['spec'];
				$tmp['stocktip']=$vo['goods']['info']['stocktip'];
				$tmp['location']=$vo['goods']['info']['location'];
				$tmp['integral']=$vo['goods']['info']['integral'];
				$tmp['data']=$vo['goods']['info']['data'];
				array_push($re['info'],$tmp);
			}
		}else {
			//有多条|弹框处理
			$re['type']=2;
		}
		return json ($re);
	}
	//保存销货单
	public function  save_sale (){
		$input=input ('post.');
		$info=$input['info'];
		//预先判断串号使用情况
		$tmp_arr=room::where (['id'=>['in',array_column ($info,'room'),'OR']])->select ()->toArray();//取出所有商品的仓储数据
		foreach ($info as $key=>$check_vo){
		    //先判断库存
		    $tmp_val=find_arr_key_val ($tmp_arr,'id',$check_vo['room']);//获取当前仓储ID的数据
			if ($check_vo['nums']>$tmp_val['nums']){
				return json (['state'=>'stock_error','row'=>$key+1]);
				exit ;
			}
			//判断串码是否存在,此处加判断高并发可能出问题,待验证
			if(array_key_exists('serial',$check_vo) && !empty($check_vo['serial'])){
			    //查找系统未销售串码
    			$tmp_serial=arrayChange (serial::where (['room'=>$check_vo['room'],'type'=>0])->field ('code')->select ()->toArray (),'code');
				$serial_arr=explode(',',$check_vo['serial']);
				foreach ($serial_arr as $arr_vo){
					if (!in_array($arr_vo,$tmp_serial)){
						return json (['state'=>'serial_error','row'=>$key+1]);
						exit ;
					}
				}
    		    $info[$key]['serial_type']=true;//是串码商品
			}else{
			    $info[$key]['serial_type']=false;//非串码商品
			}
		}
		$class_sql['customer']=$input['customer'];
		$class_sql['time']=$input['time']; 
		$class_sql['number']=$input['number'];
		$class_sql['total']=$input['total'];
		$class_sql['discount']=$input['discount'];
		//优惠金额
		$class_sql['money']=$input['money'];
		$class_sql['user']=$input['user'];
		$class_sql['account']=$input['account'];
		$class_sql['billtype']=-1;
		if (!empty($input['file'])){
			$class_sql['file']=$input['file'];
		}
		$class_sql['data']=$input['data'];
		if (!isset($input['id'])){
			//新增
			$class=saleclass::create ($class_sql);
			set_number('sale');
		}else {
			//更新
			$class_sql['id']=$input['id'];
			$class=saleclass::update ($class_sql);
			saleinfo::destroy (['pid'=>$input['id']]);//删除旧info数据
		}
		//详情
		foreach ($info as $vo){
			$info_sql=[];
			$info_sql['pid']=$class['id'];
			$info_sql['room']=$vo['room'];
			$tmp_warehouse=room::get (['id'=>$vo['room']]);
			$info_sql['warehouse']=$tmp_warehouse['warehouse']['ape'];
			$info_sql['goods']=$vo['goods'];
			//判断串号
			if ($vo['serial_type'] && array_key_exists('serial',$vo)){
				$info_sql['serial']=$vo['serial'];
			}
			$info_sql['nums']=$vo['nums'];
			$info_sql['price']=$vo['price'];
			$info_sql['discount']=$vo['discount'];
			$info_sql['total']=$vo['total'];
			if (array_key_exists('data',$vo)){
				$info_sql['data']=$vo['data'];
			}
			$info_sql['timemark']=0;//时间标识
			saleinfo::create ($info_sql);
		}
		push_log ('提交销货单-'.$input['number']);
		//判断自动审核
		$sys=sys::all();
		if(empty($sys['1']['info']['auditing'])){
		    $this->auditing_sale($class['id'],true);
		}
		return json (['state'=>'success']);
	}
	//删除销货单
	public function  del_sale(){
		$id=input ('post.id');
		$saleclass=saleclass::where (['id'=>$id])->find ();
		if (empty($saleclass['type']['ape'])){
			//未审核可删除
			saleclass::destroy (['id'=>$id]);
			//删除class
			saleinfo::destroy (['pid'=>$id]);
			//删除info
		}
		push_log ('删除销货单-'.$saleclass['number']);
		return json ('success');
	}
	//审核|反审核销货单
	public function  auditing_sale ($id,$auth=false){
		//兼容自动审核
		if (empty($id)){
			$id=input ('post.id');
		}
		$saleclass=saleclass::get ($id);
		$saleinfo=saleinfo::where (['pid'=>$id])->select ()->toArray ();
		//读取info
		if (empty($saleclass['type']['ape'])){
			//审核
			//预先判断库存以及串码使用情况
			foreach ($saleinfo as $key=>$check_vo){
				//判断库存
				if ($check_vo['nums']>$check_vo['room']['info']['nums']){
					if ($auth){
						push_log ('自动审核销货单-'.$check_vo['number'].'失败，原因：商品库存不足');
						exit ;
					}else {
						return json (['state'=>'stock_error','row'=>$key+1]);
						exit ;
					}
				}
				//判断串号是否使用
				if (!empty($check_vo['serial'])){
					$tmp_serial_arr=explode(',',$check_vo['serial']);
					//判断当前商品串码个数与串码表中这些串码(状态未销售)的个数是否相同
					if (count($tmp_serial_arr)!==serial::where (['code'=>['in',$tmp_serial_arr,'OR'],'type'=>0])->count()){
						if ($auth){
							//自动审核
							push_log ('自动审核销货单-'.$check_vo['number'].'失败，原因：商品串码已销售');
						    exit ;
						}else {
							//手动审核
							return json (['state'=>'serial_error','row'=>$key+1]);
							exit ;
						}
					}
				}
			}
			$timemark=time();//时间标识
			foreach ($saleinfo as $saleinfo_vo){
				//循环保存数据
				room::update([
				    'id'=>$saleinfo_vo['room']['ape'],
				    'nums'=>$saleinfo_vo['room']['info']['nums']-$saleinfo_vo['nums'],
				    'timemark'=>$timemark//时间标识
				]);//更新仓储信息
				//新增仓储详情
				$roominfo_sql['pid']=$saleinfo_vo['room']['ape'];
				$roominfo_sql['type']=2;
				$roominfo_sql['class']=$id;
				$roominfo_sql['info']=$saleinfo_vo['id'];
				$roominfo_sql['nums']=$saleinfo_vo['nums'];
				$roominfo_sql['timemark']=$timemark;//时间标识
				$roominfo_sql['oldtimemark']=$saleinfo_vo['room']['info']['timemark'];//旧时间标识
				roominfo::create ($roominfo_sql);
				saleinfo::update(['id'=>$saleinfo_vo['id'],'timemark'=>$timemark]);//更新详情次数
				//判断批次
				if (!empty($saleinfo_vo['serial'])){
					//串号存在
					$tmp_serial=explode(',',$saleinfo_vo['serial']);
					foreach ($tmp_serial as $tmp_serial_vo){
					    $serial_info=serial::get(['code'=>$tmp_serial_vo]);//获取串号详情
					    serial::update(['id'=>$serial_info['id'],'type'=>1,'timemark'=>$timemark]);//修改串号状态
					    serialinfo::create ([
					        'pid'=>$serial_info['id'],
					        'type'=>2,
					        'class'=>$id,
					        'oldroom'=>$serial_info['room'],
					        'timemark'=>$timemark,
					        'oldtimemark'=>$serial_info['timemark']
					    ]);//增加串号使用详情
					}
				}
			}
			//获取资金状态
			if (empty($saleclass['money'])){
				$billtype=0;
				//未结算
			}elseif ($saleclass['money']==$saleclass['total']-$saleclass['discount']){
				$billtype=2;
				//已结算
			}else {
				$billtype=1;
				//部分结算
			}
			//更新class审核状态和对账单状态
			saleclass::update (['id'=>$id,'type'=>1,'auditinguser'=>Session ('is_user_id'),'auditingtime'=>time(),'billtype'=>$billtype]);
			//资金非零
			if (!empty($saleclass['money'])){
				//增加对账单
				$bill_info=salebill::create (['pid'=>$id,'account'=>$saleclass['account']['ape'],'money'=>$saleclass['money'],'data'=>'系统自动生成','user'=>Session ('is_user_id'),'time'=>time()]);
				account::where (['id'=>$saleclass['account']['ape']])->setInc ('balance',$saleclass['money']);//操作资金-增
				accountinfo::create (['pid'=>$saleclass['account']['ape'],'set'=>1,'money'=>$saleclass['money'],'type'=>2,'time'=>time(),'user'=>Session ('is_user_id'),'class'=>$id,'number'=>$saleclass['number'],'only'=>$bill_info['id']]);
				//资金操作-详情
			}
			set_summary('saleclass',$id,true);//更新Summary
			if ($auth){
				push_log ('自动审核销货单-'.$saleclass['number']);
			}else {
				//手动
				push_log ('审核销货单-'.$saleclass['number']);
			}
		}else {
			//反审核
			//判断逆操作以及串码状态
			foreach ($saleinfo as $key=>$check_vo){
				//判断逆操作
				//判断当前详情的时间标识与仓储ID中的时间标识是否相同
				if($check_vo['timemark']!==$check_vo['room']['info']['timemark']){
				    return json (['state'=>'set_error','row'=>$key+1]);
                    exit;
				}else{
				    //开始判断当前时间标识是否存在后续操作
				    if(roominfo::get([
            	        'pid'=>$check_vo['room']['ape'],
            	        'timemark'=>['gt',$check_vo['timemark']]
            	     ])){
            	        return json (['state'=>'set_error','row'=>$key+1]);
                        exit;
            	    }
				}
			}
			foreach ($saleinfo as $saleinfo_vo){
				room::where (['id'=>$saleinfo_vo['room']['ape']])->setInc('nums',$saleinfo_vo['nums']);//增加对应库存数量
				$room_info=roominfo::get([
				    'pid'=>$saleinfo_vo['room']['ape'],
				    'type'=>2,
				    'info'=>$saleinfo_vo['id']
				    ]);//获取仓储详情
				room::update([
				    'id'=>$saleinfo_vo['room']['ape'],
				    'timemark'=>$room_info['oldtimemark']
				    ]);//还原旧时间标识
				saleinfo::update (['id'=>$saleinfo_vo['id'],'timemark'=>0]);//详情还原时间标识
				if(!empty($saleinfo_vo['serial'])){
				    $serial_arr=serial::where (['code'=>['in',explode(',',$saleinfo_vo['serial']),'OR']])->select();//获取串号详情
				    foreach ($serial_arr as $serial_vo) {
				        $serial_info=serialinfo::get(['pid'=>$serial_vo['id'],'type'=>2,'timemark'=>$serial_vo['timemark']]);//获取当前时间标识的串码详情
				        serial::update([
				            'id'=>$serial_vo['id'],
				            'room'=>$serial_info['oldroom'],
				            'type'=>0,
				            'timemark'=>$serial_info['oldtimemark']
				        ]);//还原旧仓储ID|设置状态
				        serialinfo::destroy(['id'=>$serial_info['id']]);//删除串码详情
				    }
				}
			}
			roominfo::destroy (['type'=>2,'class'=>$id]);//删除仓储详情
			saleclass::update (['id'=>$id,'type'=>0,'auditinguser'=>0,'auditingtime'=>0,'billtype'=>-1]);
			//复原class审核状态和对账单状态
			//资金非零
			if (!empty($saleclass['money'])){
				$salebill=salebill::where (['pid'=>$id])->select ();
				//获取对账单操作记录
				//复原资金
				foreach ($salebill as $salebill_vo){
					account::where (['id'=>$salebill_vo['account']['ape']])->setDec ('balance',$salebill_vo['money']);
					//减少金额
				}
				salebill::destroy (['pid'=>$id]);
				//删除对账单
				accountinfo::destroy (['type'=>2,'class'=>$id]);
				//删除资金详情
			}
			set_summary('saleclass',$id,false);//更新Summary
			push_log ('反审核销货单-'.$saleclass['number']);
		}
		return json (['state'=>'success']);
	}
    //销货对账单详情
	public function  salebill_info (){
		$id=input ('post.id');
		$re['class']=saleclass::get (['id'=>$id]);
		$re['bill']=salebill::where (['pid'=>$id])->select ()->toArray();
		//数据鉴权
		$user_auth=json_decode(user_info('auth'),true);
		foreach ($re['bill'] as $key => $vo) {
		    if(!empty($user_auth) && !empty($user_auth['account'])){
		        if(in_array($vo['account']['ape'],$user_auth['account'])){
		            $re['bill'][$key]['auth_info']=true;
		        }else{
		            $re['bill'][$key]['auth_info']=false;
		        }
		    }else{
		        $re['bill'][$key]['auth_info']=true;
		    }
		}
		return json ($re);
	}
	//保存销货对账单操作
	public function  save_salebill (){
		$input=input ('post.');
		$saleclass=saleclass::get (['id'=>$input['id']]);
		//判断合法性
		if ($input['sum']<=$saleclass['total']-$saleclass['discount']-$saleclass['money']){
			//获取资金状态
			if ($saleclass['money']+$input['sum']==$saleclass['total']-$saleclass['discount']){
				$billtype=2;
				//已结算
			}else {
				$billtype=1;
				//部分结算
			}
			saleclass::update (['id'=>$input['id'],'money'=>$saleclass['money']+$input['sum'],//金额增加
			'billtype'=>$billtype//资金状态
			]);
			$bill_info=salebill::create (['pid'=>$input['id'],'account'=>$input['account'],'money'=>$input['sum'],'data'=>input ('post.data'),'user'=>Session ('is_user_id'),'time'=>time()]);
			account::where (['id'=>$input['account']])->setInc ('balance',$input['sum']);
			//操作资金-增
			accountinfo::create (['pid'=>$input['account'],'set'=>1,'money'=>$input['sum'],'type'=>2,'time'=>time(),'user'=>Session ('is_user_id'),'class'=>$saleclass['id'],'number'=>$saleclass['number'],'only'=>$bill_info['id']]);
			//资金操作-详情
		}
		push_log ('增加销货对账单详情-'.$saleclass['number']);
		return json ('success');
	}
	//删除销货对账单详情
	public function  del_salebill (){
		$id=input ('post.id');
		$salebill=salebill::get (['id'=>$id]);
		$saleclass=saleclass::get (['id'=>$salebill['pid']]);
		//获取资金状态
		if ($salebill['money']==$saleclass['money']){
			$billtype=0;
			//未结算
		}else {
			$billtype=1;
			//部分结算
		}
		saleclass::update (['id'=>$salebill['pid'],'money'=>$saleclass['money']-$salebill['money'],'billtype'=>$billtype]);
		account::where (['id'=>$salebill['account']['ape']])->setDec ('balance',$salebill['money']);
		//操作资金-减
		accountinfo::destroy (['pid'=>$salebill['account']['ape'],'type'=>2,'only'=>$id]);
		//删除资金操作-详情
		salebill::destroy (['id'=>$id]);
		push_log ('删除销货对账单详情-'.$saleclass['number']);
		return json ('success');
	}
	//保存购货退货单
	public function  save_repurchase (){
		$input=input ('post.');
		$info=$input['info'];
		//预先判断串号使用情况
		$tmp_arr=room::where (['id'=>['in',array_column ($info,'room'),'OR']])->select ()->toArray();//取出所有商品的仓储数据
		foreach ($info as $key=>$check_vo){
		    //先判断库存
		    $tmp_val=find_arr_key_val ($tmp_arr,'id',$check_vo['room']);//获取当前仓储ID的数据
			if ($check_vo['nums']>$tmp_val['nums']){
				return json (['state'=>'stock_error','row'=>$key+1]);
				exit ;
			}
			//判断串码是否存在,此处加判断高并发可能出问题,待验证
			if(array_key_exists('serial',$check_vo) && !empty($check_vo['serial'])){
			    //查找系统未销售串码
    			$tmp_serial=arrayChange (serial::where (['room'=>$check_vo['room'],'type'=>0])->field ('code')->select ()->toArray (),'code');
				$serial_arr=explode(',',$check_vo['serial']);
				foreach ($serial_arr as $arr_vo){
					if (!in_array($arr_vo,$tmp_serial)){
						return json (['state'=>'serial_error','row'=>$key+1]);
						exit ;
					}
				}
    		    $info[$key]['serial_type']=true;//是串码商品
			}else{
			    $info[$key]['serial_type']=false;//非串码商品
			}
		}
		$class_sql['supplier']=$input['supplier'];
		$class_sql['time']=$input['time']; 
		$class_sql['number']=$input['number'];
		$class_sql['total']=$input['total'];
		$class_sql['actual']=$input['actual'];//实际金额
		$class_sql['money']=$input['money'];
		$class_sql['user']=$input['user'];
		$class_sql['account']=$input['account'];
		$class_sql['billtype']=-1;
		if (!empty($input['file'])){
			$class_sql['file']=$input['file'];
		}
		$class_sql['data']=$input['data'];
		if (!isset($input['id'])){
			//新增
			$class=repurchaseclass::create ($class_sql);
			set_number('repurchase');
		}else {
			//更新
			$class_sql['id']=$input['id'];
			$class=repurchaseclass::update ($class_sql);
			repurchaseinfo::destroy (['pid'=>$input['id']]);//删除旧info数据
		}
		//详情
		foreach ($info as $vo){
			$info_sql=[];
			$info_sql['pid']=$class['id'];
			$info_sql['room']=$vo['room'];
			$tmp_warehouse=room::get (['id'=>$vo['room']]);
			$info_sql['warehouse']=$tmp_warehouse['warehouse']['ape'];
			$info_sql['goods']=$vo['goods'];
			//判断串号
			if (array_key_exists('serial',$vo) && !empty($vo['serial'])){
				$info_sql['serial']=$vo['serial'];
			}
			$info_sql['nums']=$vo['nums'];
			$info_sql['price']=$vo['price'];
			$info_sql['total']=$vo['total'];
			if (array_key_exists('data',$vo)){
				$info_sql['data']=$vo['data'];
			}
			$info_sql['timemark']=0;//时间标识
			repurchaseinfo::create ($info_sql);
		}
		push_log ('提交购货退货单-'.$input['number']);
		//判断自动审核
		$sys=sys::all();
		if(empty($sys['1']['info']['auditing'])){
		    $this->auditing_repurchase($class['id'],true);
		}
		
		return json (['state'=>'success']);
	}
	//删除购货退货单
	public function  del_repurchase(){
		$id=input ('post.id');
		$repurchaseclass=repurchaseclass::where (['id'=>$id])->find ();
		if (empty($repurchaseclass['type']['ape'])){
			//未审核可删除
			repurchaseclass::destroy (['id'=>$id]);
			//删除class
			repurchaseinfo::destroy (['pid'=>$id]);
			//删除info
		}
		push_log ('删除购货退货单-'.$repurchaseclass['number']);
		return json ('success');
	}
	//审核|反审核购货退货单
	public function  auditing_repurchase ($id,$auth=false){
		//兼容自动审核
		if (empty($id)){
			$id=input ('post.id');
		}
		$repurchaseclass=repurchaseclass::get ($id);
		$repurchaseinfo=repurchaseinfo::where (['pid'=>$id])->select ()->toArray ();
		//读取info
		if (empty($repurchaseclass['type']['ape'])){
			//审核
			//预先判断库存以及串码使用情况
			foreach ($repurchaseinfo as $key=>$check_vo){
				//判断库存
				if ($check_vo['nums']>$check_vo['room']['info']['nums']){
					if ($auth){
						push_log ('自动审核购货退货单-'.$purchaseclass['number'].'失败，原因：商品库存不足');
						exit ;
					}else {
						return json (['state'=>'stock_error','row'=>$key+1]);
						exit ;
					}
				}
				//判断串号是否使用
				if (!empty($check_vo['serial'])){
					$tmp_serial_arr=explode(',',$check_vo['serial']);
					//判断当前商品串码个数与串码表中这些串码(状态未销售)的个数是否相同
					if (count($tmp_serial_arr)!==serial::where (['code'=>['in',$tmp_serial_arr,'OR'],'type'=>0])->count()){
						if ($auth){
							//自动审核
							push_log ('自动审核购货退货单-'.$purchaseclass['number'].'失败，原因：商品串码已销售');
						    exit ;
						}else {
							//手动审核
							return json (['state'=>'serial_error','row'=>$key+1]);
							exit ;
						}
					}
				}
			}
			$timemark=time();//时间标识
			foreach ($repurchaseinfo as $repurchaseinfo_vo){
				//循环保存数据
				room::update([
				    'id'=>$repurchaseinfo_vo['room']['ape'],
				    'nums'=>$repurchaseinfo_vo['room']['info']['nums']-$repurchaseinfo_vo['nums'],
				    'timemark'=>$timemark//时间标识
				]);//更新仓储信息
				//新增仓储详情
				$roominfo_sql['pid']=$repurchaseinfo_vo['room']['ape'];
				$roominfo_sql['type']=3;
				$roominfo_sql['class']=$id;
				$roominfo_sql['info']=$repurchaseinfo_vo['id'];
				$roominfo_sql['nums']=$repurchaseinfo_vo['nums'];
				$roominfo_sql['timemark']=$timemark;//时间标识
				$roominfo_sql['oldtimemark']=$repurchaseinfo_vo['room']['info']['timemark'];//旧时间标识！
				roominfo::create ($roominfo_sql);
				repurchaseinfo::update(['id'=>$repurchaseinfo_vo['id'],'timemark'=>$timemark]);//更新详情时间标识
				//判断批次
				if (!empty($repurchaseinfo_vo['serial'])){
					//串号存在
					$tmp_serial=explode(',',$repurchaseinfo_vo['serial']);
					foreach ($tmp_serial as $tmp_serial_vo){
					    $serial_info=serial::get(['code'=>$tmp_serial_vo]);//获取串号详情
					    serial::update(['id'=>$serial_info['id'],'type'=>2,'timemark'=>$timemark]);//修改串号状态以及时间标识
					    serialinfo::create ([
					        'pid'=>$serial_info['id'],
					        'type'=>3,
					        'class'=>$id,
					        'oldroom'=>$serial_info['room'],
					        'timemark'=>$timemark,
					        'oldtimemark'=>$serial_info['timemark']
					    ]);//增加串号使用详情
					}
				}
			}
			//获取资金状态
			if (empty($repurchaseclass['money'])){
				$billtype=0;
				//未结算
			}elseif ($repurchaseclass['money']==$repurchaseclass['actual']){
				$billtype=2;
				//已结算
			}else {
				$billtype=1;
				//部分结算
			}
			//更新class审核状态和对账单状态
			repurchaseclass::update (['id'=>$id,'type'=>1,'auditinguser'=>Session ('is_user_id'),'auditingtime'=>time(),'billtype'=>$billtype]);
			//资金非零
			if (!empty($repurchaseclass['money'])){
				//增加对账单
				$bill_info=repurchasebill::create (['pid'=>$id,'account'=>$repurchaseclass['account']['ape'],'money'=>$repurchaseclass['money'],'data'=>'系统自动生成','user'=>Session ('is_user_id'),'time'=>time()]);
				account::where (['id'=>$repurchaseclass['account']['ape']])->setInc ('balance',$repurchaseclass['money']);//操作资金-增
				accountinfo::create (['pid'=>$repurchaseclass['account']['ape'],'set'=>1,'money'=>$repurchaseclass['money'],'type'=>3,'time'=>time(),'user'=>Session ('is_user_id'),'class'=>$id,'number'=>$repurchaseclass['number'],'only'=>$bill_info['id']]);
				//资金操作-详情
			}
			set_summary('repurchaseclass',$id,true);//更新Summary
			if ($auth){
				push_log ('自动审核购货退货-'.$repurchaseclass['number']);
			}else {
				//手动
				push_log ('审核购货退货-'.$repurchaseclass['number']);
			}
		}else {
			//反审核
			//判断逆操作以及串码状态
			foreach ($repurchaseinfo as $key=>$check_vo){
				//判断逆操作
				//判断当前详情的时间标识与仓储ID中的时间标识是否相同
				if($check_vo['timemark']!==$check_vo['room']['info']['timemark']){
				    return json (['state'=>'set_error','row'=>$key+1]);
                    exit;
				}else{
				    //开始判断当前时间标识是否存在后续操作
				    if(roominfo::get([
            	        'pid'=>$check_vo['room']['ape'],
            	        'timemark'=>['gt',$check_vo['timemark']]
            	     ])){
            	        return json (['state'=>'set_error','row'=>$key+1]);
                        exit;
            	    }
				}
				//判断串号状态
				if (!empty($check_vo['serial'])){
					$tmp_serial_arr=explode(',',$check_vo['serial']);
					//串码状态必须为不在库
					if(serial::get(['code'=>['in',$tmp_serial_arr,'OR'],'type'=>['neq',2]])){
					    //手动审核
						return json (['state'=>'serial_error','row'=>$key+1]);
						exit ;
					}
				}
			}
			foreach ($repurchaseinfo as $repurchaseinfo_vo){
				room::where (['id'=>$repurchaseinfo_vo['room']['ape']])->setInc('nums',$repurchaseinfo_vo['nums']);//增加对应库存数量
				$room_info=roominfo::get([
				    'pid'=>$repurchaseinfo_vo['room']['ape'],
				    'type'=>3,
				    'info'=>$repurchaseinfo_vo['id']
				    ]);//获取仓储详情
				room::update([
				    'id'=>$repurchaseinfo_vo['room']['ape'],
				    'timemark'=>$room_info['oldtimemark']
				    ]);//还原旧时间标识
				repurchaseinfo::update (['id'=>$repurchaseinfo_vo['id'],'timemark'=>0]);//详情还原时间标识
				if(!empty($repurchaseinfo_vo['serial'])){
				    $serial_arr=serial::where (['code'=>['in',explode(',',$repurchaseinfo_vo['serial']),'OR']])->select();//获取串号详情
				    foreach ($serial_arr as $serial_vo) {
				        $serial_info=serialinfo::get(['pid'=>$serial_vo['id'],'type'=>3,'timemark'=>$serial_vo['timemark']]);//获取当前时间标识的串码详情
				        serial::update([
				            'id'=>$serial_vo['id'],
				            'room'=>$serial_info['oldroom'],
				            'type'=>0,
				            'timemark'=>$serial_info['oldtimemark']
				        ]);//还原旧仓储ID|设置状态
				        serialinfo::destroy(['id'=>$serial_info['id']]);//删除串码详情
				    }
				}
			}
			roominfo::destroy (['type'=>3,'class'=>$id]);//删除仓储详情
			repurchaseclass::update (['id'=>$id,'type'=>0,'auditinguser'=>0,'auditingtime'=>0,'billtype'=>-1]);
			//复原class审核状态和对账单状态
			//资金非零
			if (!empty($repurchaseclass['money'])){
				$repurchasebill=repurchasebill::where (['pid'=>$id])->select ();
				//获取对账单操作记录
				//复原资金
				foreach ($repurchasebill as $repurchasebill_vo){
					account::where (['id'=>$repurchasebill_vo['account']['ape']])->setDec ('balance',$repurchasebill_vo['money']);
					//减少金额
				}
				repurchasebill::destroy (['pid'=>$id]);
				//删除对账单
				accountinfo::destroy (['type'=>3,'class'=>$id]);
				//删除资金详情
			}
			set_summary('repurchaseclass',$id,false);//更新Summary
			push_log ('反审核购货退货-'.$repurchaseclass['number']);
		}
		return json (['state'=>'success']);
	}
	//购货退货对账单详情
	public function  repurchasebill_info (){
		$id=input ('post.id');
		$re['class']=repurchaseclass::get (['id'=>$id]);
		$re['bill']=repurchasebill::where (['pid'=>$id])->select ();
		//数据鉴权
		$user_auth=json_decode(user_info('auth'),true);
		foreach ($re['bill'] as $key => $vo) {
		    if(!empty($user_auth) && !empty($user_auth['account'])){
		        if(in_array($vo['account']['ape'],$user_auth['account'])){
		            $re['bill'][$key]['auth_info']=true;
		        }else{
		            $re['bill'][$key]['auth_info']=false;
		        }
		    }else{
		        $re['bill'][$key]['auth_info']=true;
		    }
		}
		return json ($re);
	}
	//保存购货退货对账单操作
	public function  save_repurchasebill (){
		$input=input ('post.');
		$repurchaseclass=repurchaseclass::get (['id'=>$input['id']]);
		//判断合法性
		if ($input['sum']<=$repurchaseclass['actual']-$repurchaseclass['money']){
			//获取资金状态
			if ($repurchaseclass['money']+$input['sum']==$repurchaseclass['actual']){
				$billtype=2;
				//已结算
			}else {
				$billtype=1;
				//部分结算
			}
			repurchaseclass::update (['id'=>$input['id'],'money'=>$repurchaseclass['money']+$input['sum'],//金额增加
			'billtype'=>$billtype//资金状态
			]);
			$bill_info=repurchasebill::create (['pid'=>$input['id'],'account'=>$input['account'],'money'=>$input['sum'],'data'=>input ('post.data'),'user'=>Session ('is_user_id'),'time'=>time()]);
			account::where (['id'=>$input['account']])->setInc('balance',$input['sum']);
			//操作资金-增
			accountinfo::create (['pid'=>$input['account'],'set'=>1,'money'=>$input['sum'],'type'=>3,'time'=>time(),'user'=>Session ('is_user_id'),'class'=>$repurchaseclass['id'],'number'=>$repurchaseclass['number'],'only'=>$bill_info['id']]);
			//资金操作-详情
		}
		push_log ('增加购货退货对账单详情-'.$repurchaseclass['number']);
		return json ('success');
	}
	//删除购货退货对账单详情
	public function  del_repurchasebill (){
		$id=input ('post.id');
		$repurchasebill=repurchasebill::get (['id'=>$id]);
		$repurchaseclass=repurchaseclass::get (['id'=>$repurchasebill['pid']]);
		//获取资金状态
		if ($repurchasebill['money']==$repurchaseclass['money']){
			$billtype=0;
			//未结算
		}else {
			$billtype=1;
			//部分结算
		}
		repurchaseclass::update (['id'=>$repurchasebill['pid'],'money'=>$repurchaseclass['money']-$repurchasebill['money'],'billtype'=>$billtype]);
		account::where (['id'=>$repurchasebill['account']['ape']])->setDec ('balance',$repurchasebill['money']);
		//操作资金-减
		accountinfo::destroy (['pid'=>$repurchasebill['account']['ape'],'type'=>3,'only'=>$id]);
		//删除资金操作-详情
		repurchasebill::destroy (['id'=>$id]);
		push_log ('删除购货退货对账单详情-'.$repurchaseclass['number']);
		return json ('success');
	}
	//保存销货退货单
	public function  save_resale (){
		$input=input ('post.');
		$info=$input['info'];
		//预先判断串号使用情况
		foreach ($info as $key=>$check_vo){
			//判断串码是否存在,此处加判断高并发可能出问题,待验证
			if(array_key_exists('serial',$check_vo) && !empty($check_vo['serial'])){
			    //查找系统已销售串码
    			$tmp_serial=arrayChange (serial::where (['room'=>$check_vo['room'],'type'=>1])->field ('code')->select ()->toArray (),'code');
				$serial_arr=explode(',',$check_vo['serial']);
				foreach ($serial_arr as $arr_vo){
					if (!in_array($arr_vo,$tmp_serial)){
						return json (['state'=>'serial_error','row'=>$key+1]);
						exit ;
					}
				}
    		    $info[$key]['serial_type']=true;//是串码商品
			}else{
			    $info[$key]['serial_type']=false;//非串码商品
			}
		}
		$class_sql['customer']=$input['customer'];
		$class_sql['time']=$input['time']; 
		$class_sql['number']=$input['number'];
		$class_sql['total']=$input['total'];
		$class_sql['actual']=$input['actual'];//实际金额
		$class_sql['money']=$input['money'];
		$class_sql['user']=$input['user'];
		$class_sql['account']=$input['account'];
		$class_sql['billtype']=-1;
		if (!empty($input['file'])){
			$class_sql['file']=$input['file'];
		}
		$class_sql['data']=$input['data'];
		if (!isset($input['id'])){
			//新增
			$class=resaleclass::create ($class_sql);
			set_number('resale');
		}else {
			//更新
			$class_sql['id']=$input['id'];
			$class=resaleclass::update ($class_sql);
			resaleinfo::destroy (['pid'=>$input['id']]);//删除旧info数据
		}
		//详情
		foreach ($info as $vo){
			$info_sql=[];
			$info_sql['pid']=$class['id'];
			$info_sql['room']=$vo['room'];
			$tmp_warehouse=room::get (['id'=>$vo['room']]);
			$info_sql['warehouse']=$tmp_warehouse['warehouse']['ape'];
			$info_sql['goods']=$vo['goods'];
			//判断串号
			if (array_key_exists('serial',$vo) && !empty($vo['serial'])){
				$info_sql['serial']=$vo['serial'];
			}
			$info_sql['nums']=$vo['nums'];
			$info_sql['price']=$vo['price'];
			$info_sql['total']=$vo['total'];
			if (array_key_exists('data',$vo)){
				$info_sql['data']=$vo['data'];
			}
			$info_sql['timemark']=0;//时间标识
			resaleinfo::create ($info_sql);
		}
		push_log ('提交销货退货单-'.$input['number']);
		//判断自动审核
		$sys=sys::all();
		if(empty($sys['1']['info']['auditing'])){
		    $this->auditing_resale($class['id'],true);
		}
		return json (['state'=>'success']);
	}
	//审核|反审核销货退货单
	public function  auditing_resale ($id,$auth=false){
		//兼容自动审核
		if (empty($id)){
			$id=input ('post.id');
		}
		$resaleclass=resaleclass::get ($id);
		$resaleinfo=resaleinfo::where (['pid'=>$id])->select ()->toArray ();
		//读取info
		if (empty($resaleclass['type']['ape'])){
			//审核
			//预先判断库存以及串码使用情况
			foreach ($resaleinfo as $key=>$check_vo){
				//判断串号是否存在未销售
				if (!empty($check_vo['serial'])){
					$tmp_serial_arr=explode(',',$check_vo['serial']);
					//判断当前商品串码个数与串码表中这些串码(状态未销售)的个数是否相同
					if (count($tmp_serial_arr)!==serial::where (['code'=>['in',$tmp_serial_arr,'OR'],'type'=>1])->count()){
						if ($auth){
							//自动审核
							push_log ('自动审核销货退货单-'.$purchaseclass['number'].'失败，原因：商品串码未销售');
						    exit ;
						}else {
							//手动审核
							return json (['state'=>'serial_error','row'=>$key+1]);
							exit ;
						}
					}
				}
			}
			$timemark=time();//时间标识
			foreach ($resaleinfo as $resaleinfo_vo){
				//循环保存数据
				room::update([
				    'id'=>$resaleinfo_vo['room']['ape'],
				    'nums'=>$resaleinfo_vo['room']['info']['nums']+$resaleinfo_vo['nums'],
				    'timemark'=>$timemark//时间标识
				]);//更新仓储信息
				//新增仓储详情
				$roominfo_sql['pid']=$resaleinfo_vo['room']['ape'];
				$roominfo_sql['type']=4;
				$roominfo_sql['class']=$id;
				$roominfo_sql['info']=$resaleinfo_vo['id'];
				$roominfo_sql['nums']=$resaleinfo_vo['nums'];
				$roominfo_sql['timemark']=$timemark;//时间标识
				$roominfo_sql['oldtimemark']=$resaleinfo_vo['room']['info']['timemark'];//旧时间标识
				roominfo::create ($roominfo_sql);
				resaleinfo::update(['id'=>$resaleinfo_vo['id'],'timemark'=>$timemark]);//更新详情时间标识
				if (!empty($resaleinfo_vo['serial'])){
					//串号存在
					$tmp_serial=explode(',',$resaleinfo_vo['serial']);
					foreach ($tmp_serial as $tmp_serial_vo){
					    $serial_info=serial::get(['code'=>$tmp_serial_vo]);//获取串号详情
					    serial::update(['id'=>$serial_info['id'],'type'=>0,'timemark'=>$timemark]);//修改串号状态以及时间标识
					    serialinfo::create ([
					        'pid'=>$serial_info['id'],
					        'type'=>4,
					        'class'=>$id,
					        'oldroom'=>$serial_info['room'],
					        'timemark'=>$timemark,
					        'oldtimemark'=>$serial_info['timemark']
					    ]);//增加串号使用详情
					}
				}
			}
			//获取资金状态
			if (empty($resaleclass['money'])){
				$billtype=0;
				//未结算
			}elseif ($resaleclass['money']==$resaleclass['actual']){
				$billtype=2;
				//已结算
			}else {
				$billtype=1;
				//部分结算
			}
			//更新class审核状态和对账单状态
			resaleclass::update (['id'=>$id,'type'=>1,'auditinguser'=>Session ('is_user_id'),'auditingtime'=>time(),'billtype'=>$billtype]);
			//资金非零
			if (!empty($resaleclass['money'])){
				//增加对账单
				$bill_info=resalebill::create (['pid'=>$id,'account'=>$resaleclass['account']['ape'],'money'=>$resaleclass['money'],'data'=>'系统自动生成','user'=>Session ('is_user_id'),'time'=>time()]);
				account::where (['id'=>$resaleclass['account']['ape']])->setDec ('balance',$resaleclass['money']);//操作资金-减
				accountinfo::create (['pid'=>$resaleclass['account']['ape'],'set'=>0,'money'=>$resaleclass['money'],'type'=>4,'time'=>time(),'user'=>Session ('is_user_id'),'class'=>$id,'number'=>$resaleclass['number'],'only'=>$bill_info['id']]);
				//资金操作-详情
			}
			set_summary('resaleclass',$id,true);//更新Summary
			if ($auth){
				push_log ('自动审核销货退货-'.$resaleclass['number']);
			}else {
				//手动
				push_log ('审核销货退货-'.$resaleclass['number']);
			}
		}else {
			//反审核
			//判断逆操作以及串码状态
			foreach ($resaleinfo as $key=>$check_vo){
				//判断逆操作
				//判断当前详情的时间标识与仓储ID中的时间标识是否相同
				if($check_vo['timemark']!==$check_vo['room']['info']['timemark']){
				    return json (['state'=>'set_error','row'=>$key+1]);
                    exit;
				}else{
				    //开始判断当前时间标识是否存在后续操作
				    if(roominfo::get([
            	        'pid'=>$check_vo['room']['ape'],
            	        'timemark'=>['gt',$check_vo['timemark']]
            	     ])){
            	        return json (['state'=>'set_error','row'=>$key+1]);
                        exit;
            	    }
				}
			}
			foreach ($resaleinfo as $resaleinfo_vo){
				room::where (['id'=>$resaleinfo_vo['room']['ape']])->setDec('nums',$resaleinfo_vo['nums']);//减少对应库存数量
				$room_info=roominfo::get([
				    'pid'=>$resaleinfo_vo['room']['ape'],
				    'type'=>4,
				    'info'=>$resaleinfo_vo['id']
				    ]);//获取仓储详情
				room::update([
				    'id'=>$resaleinfo_vo['room']['ape'],
				    'timemark'=>$room_info['oldtimemark']
				    ]);//还原旧时间标识
				resaleinfo::update (['id'=>$resaleinfo_vo['id'],'timemark'=>0]);//详情还原时间标识
				if(!empty($resaleinfo_vo['serial'])){
				    $serial_arr=serial::where (['code'=>['in',explode(',',$resaleinfo_vo['serial']),'OR']])->select();//获取串号详情
				    foreach ($serial_arr as $serial_vo) {
				        $serial_info=serialinfo::get(['pid'=>$serial_vo['id'],'type'=>4,'timemark'=>$serial_vo['timemark']]);//获取当前时间标识的串码详情
				        serial::update([
				            'id'=>$serial_vo['id'],
				            'room'=>$serial_info['oldroom'],
				            'type'=>1,
				            'timemark'=>$serial_info['oldtimemark']
				        ]);//还原旧仓储ID|设置状态
				        serialinfo::destroy(['id'=>$serial_info['id']]);//删除串码详情
				    }
				}
			}
			roominfo::destroy (['type'=>4,'class'=>$id]);//删除仓储详情
			resaleclass::update (['id'=>$id,'type'=>0,'auditinguser'=>0,'auditingtime'=>0,'billtype'=>-1]);
			//复原class审核状态和对账单状态
			//资金非零
			if (!empty($resaleclass['money'])){
				$resalebill=resalebill::where (['pid'=>$id])->select ();
				//获取对账单操作记录
				//复原资金
				foreach ($resalebill as $resalebill_vo){
					account::where (['id'=>$resalebill_vo['account']['ape']])->setInc ('balance',$resalebill_vo['money']);
					//增加金额
				}
				resalebill::destroy (['pid'=>$id]);
				//删除对账单
				accountinfo::destroy (['type'=>4,'class'=>$id]);
				//删除资金详情
			}
			set_summary('resaleclass',$id,false);//更新Summary
			push_log ('反审核销货退货-'.$resaleclass['number']);
		}
		return json (['state'=>'success']);
	}
	//删除销货退货单
	public function  del_resale(){
		$id=input ('post.id');
		$resaleclass=resaleclass::where (['id'=>$id])->find ();
		if (empty($resaleclass['type']['ape'])){
			//未审核可删除
			resaleclass::destroy (['id'=>$id]);
			//删除class
			resaleinfo::destroy (['pid'=>$id]);
			//删除info
		}
		push_log ('删除销货退货单-'.$resaleclass['number']);
		return json ('success');
	}
	//销货退货对账单详情
	public function  resalebill_info (){
		$id=input ('post.id');
		$re['class']=resaleclass::get (['id'=>$id]);
		$re['bill']=resalebill::where (['pid'=>$id])->select ();
		//数据鉴权
		$user_auth=json_decode(user_info('auth'),true);
		foreach ($re['bill'] as $key => $vo) {
		    if(!empty($user_auth) && !empty($user_auth['account'])){
		        if(in_array($vo['account']['ape'],$user_auth['account'])){
		            $re['bill'][$key]['auth_info']=true;
		        }else{
		            $re['bill'][$key]['auth_info']=false;
		        }
		    }else{
		        $re['bill'][$key]['auth_info']=true;
		    }
		}
		return json ($re);
	}
	//保存销货退货对账单操作
	public function  save_resalebill (){
		$input=input ('post.');
		$resaleclass=resaleclass::get (['id'=>$input['id']]);
		//判断合法性
		if ($input['sum']<=$resaleclass['actual']-$resaleclass['money']){
			//获取资金状态
			if ($resaleclass['money']+$input['sum']==$resaleclass['actual']){
				$billtype=2;
				//已结算
			}else {
				$billtype=1;
				//部分结算
			}
			resaleclass::update (['id'=>$input['id'],'money'=>$resaleclass['money']+$input['sum'],//金额减少
			'billtype'=>$billtype//资金状态
			]);
			$bill_info=resalebill::create (['pid'=>$input['id'],'account'=>$input['account'],'money'=>$input['sum'],'data'=>input ('post.data'),'user'=>Session ('is_user_id'),'time'=>time()]);
			
			account::where (['id'=>$input['account']])->setDec ('balance',$input['sum']);
			//操作资金-减
			accountinfo::create (['pid'=>$input['account'],'set'=>0,'money'=>$input['sum'],'type'=>4,'time'=>time(),'user'=>Session ('is_user_id'),'class'=>$resaleclass['id'],'number'=>$resaleclass['number'],'only'=>$bill_info['id']]);
			//资金操作-详情
		}
		push_log ('增加销货退货对账单详情-'.$resaleclass['number']);
		return json ('success');
	}
	//删除销货退货对账单详情
	public function  del_resalebill (){
		$id=input ('post.id');
		$resalebill=resalebill::get (['id'=>$id]);
		$resaleclass=resaleclass::get (['id'=>$resalebill['pid']]);
		//获取资金状态
		if ($resalebill['money']==$resaleclass['money']){
			$billtype=0;
			//未结算
		}else {
			$billtype=1;
			//部分结算
		}
		resaleclass::update (['id'=>$resalebill['pid'],'money'=>$resaleclass['money']-$resalebill['money'],//金额减少
		'billtype'=>$billtype//资金状态 
		]);
		account::where (['id'=>$resalebill['account']['ape']])->setInc ('balance',$resalebill['money']);
		//操作资金-加
		accountinfo::destroy (['pid'=>$resalebill['account']['ape'],'type'=>4,'only'=>$id]);
		//删除资金操作-详情
		resalebill::destroy (['id'=>$id]);
		push_log ('删除销货退货对账单详情-'.$resaleclass['number']);
		return json ('success');
	}
	//保存调拨单
	public function  save_allocation (){
		$input=input ('post.');
		$info=$input['info'];
		//预先判断串号使用情况
		$tmp_arr=room::where (['id'=>['in',array_column ($info,'room'),'OR']])->select ()->toArray();//取出所有商品的仓储数据
		foreach ($info as $key=>$check_vo){
		    //先判断库存
		    $tmp_val=find_arr_key_val ($tmp_arr,'id',$check_vo['room']);//获取当前仓储ID的数据
			if ($check_vo['nums']>$tmp_val['nums']){
				return json (['state'=>'stock_error','row'=>$key+1]);
				exit ;
			}
			//判断串码是否存在,此处加判断高并发可能出问题,待验证
			if(array_key_exists('serial',$check_vo) && !empty($check_vo['serial'])){
			    //查找系统未销售串码
    			$tmp_serial=arrayChange (serial::where (['room'=>$check_vo['room'],'type'=>0])->field ('code')->select ()->toArray (),'code');
				$serial_arr=explode(',',$check_vo['serial']);
				foreach ($serial_arr as $arr_vo){
					if (!in_array($arr_vo,$tmp_serial)){
						return json (['state'=>'serial_error','row'=>$key+1]);
						exit ;
					}
				}
    		    $info[$key]['serial_type']=true;//是串码商品
			}else{
			    $info[$key]['serial_type']=false;//非串码商品
			}
		}
		$class_sql['time']=$input['time']; 
		$class_sql['number']=$input['number'];
		$class_sql['user']=$input['user'];
		$class_sql['data']=$input['data'];
		if (!isset($input['id'])){
			//新增
			$class=allocationclass::create ($class_sql);
			set_number('allocation');
		}else {
			//更新
			$class_sql['id']=$input['id'];
			$class=allocationclass::update ($class_sql);
			allocationinfo::destroy (['pid'=>$input['id']]);//删除旧info数据
		}
		//详情
		foreach ($info as $vo){
			$info_sql=[];
			$info_sql['pid']=$class['id'];
			$info_sql['room']=$vo['room'];
			$tmp_warehouse=room::get (['id'=>$vo['room']]);
			$info_sql['warehouse']=$tmp_warehouse['warehouse']['ape'];
			$info_sql['goods']=$vo['goods'];
			//判断串号
			if ($vo['serial_type'] && array_key_exists('serial',$vo)){
				$info_sql['serial']=$vo['serial'];
			}
			$info_sql['nums']=$vo['nums'];
			$info_sql['towarehouse']=$vo['towarehouse'];
			if (array_key_exists('data',$vo)){
				$info_sql['data']=$vo['data'];
			}
			$info_sql['timemark']=0;//时间标识
			allocationinfo::create ($info_sql);
		}
		push_log ('提交调拨单-'.$input['number']);
		//判断自动审核
		$sys=sys::all();
		if(empty($sys['1']['info']['auditing'])){
		    $this->auditing_allocation($class['id'],true);
		}
		return json (['state'=>'success']);
	}
	//审核|反审核调拨单
	public function  auditing_allocation ($id,$auth=false){
		//兼容自动审核
		if (empty($id)){
			$id=input ('post.id');
		}
		$allocationclass=allocationclass::get ($id);
		$allocationinfo=allocationinfo::where (['pid'=>$id])->select ()->toArray ();
		//读取info
		if (empty($allocationclass['type']['ape'])){
			//审核
			//预先判断库存以及串码使用情况
			foreach ($allocationinfo as $key=>$check_vo){
				//判断库存
				if ($check_vo['nums']>$check_vo['room']['info']['nums']){
					if ($auth){
						push_log ('自动审核调拨单-'.$check_vo['number'].'失败，原因：商品库存不足');
						exit ;
					}else {
						return json (['state'=>'stock_error','row'=>$key+1]);
						exit ;
					}
				}
				//判断串号是否使用
				if (!empty($check_vo['serial'])){
					$tmp_serial_arr=explode(',',$check_vo['serial']);
					//判断当前商品串码个数与串码表中这些串码(状态未销售)的个数是否相同
					if (count($tmp_serial_arr)!==serial::where (['code'=>['in',$tmp_serial_arr,'OR'],'type'=>0])->count()){
						if ($auth){
							//自动审核
							push_log ('自动审核调拨单-'.$check_vo['number'].'失败，原因：商品串码已销售');
						    exit ;
						}else {
							//手动审核
							return json (['state'=>'serial_error','row'=>$key+1]);
							exit ;
						}
					}
				}
			}
			$timemark=time();//时间标识
			foreach ($allocationinfo as $allocationinfo_vo){
				//循环保存数据
				//先处理调出仓库
				room::update([
				    'id'=>$allocationinfo_vo['room']['ape'],
				    'nums'=>$allocationinfo_vo['room']['info']['nums']-$allocationinfo_vo['nums'],
				    'timemark'=>$timemark//时间标识
				]);//更新仓储信息
				
				//新增调出仓储详情
				$one_roominfo_sql['pid']=$allocationinfo_vo['room']['ape'];
				$one_roominfo_sql['type']=5;//调拨-出
				$one_roominfo_sql['class']=$id;
				$one_roominfo_sql['info']=$allocationinfo_vo['id'];
				$one_roominfo_sql['nums']=$allocationinfo_vo['nums'];
				$one_roominfo_sql['timemark']=$timemark;//时间标识
				$one_roominfo_sql['oldtimemark']=$allocationinfo_vo['room']['info']['timemark'];//旧时间标识
				roominfo::create ($one_roominfo_sql);
				
				//开始处理调入仓库数据
				//判断仓储是否存在
				$check_room_sql['warehouse']=$allocationinfo_vo['towarehouse']['ape'];
				$check_room_sql['goods']=$allocationinfo_vo['goods'];
				$check_room_sql['attr']=$allocationinfo_vo['room']['info']['attr']['ape'];
				$check_room_sql['batch']=$allocationinfo_vo['room']['info']['batch'];
				$two_room=room::get($check_room_sql);
				if(empty($two_room)){
				   //不存在 
				   $check_room_sql['nums']=$allocationinfo_vo['nums'];
				   $check_room_sql['timemark']=$timemark;//时间标识
				   $two_room=room::create($check_room_sql);
				   $room_oldtimemark=0;//初始化旧时间标识
				}else{
				    //存在
				    //更新仓储数据
					room::update([
					    'id'=>$two_room['id'],
					    'nums'=>$two_room['nums']+$allocationinfo_vo['nums'],
					    'timemark'=>$timemark
					]);//增加库存数量
					$room_oldtimemark=$two_room['timemark'];//转存仓储旧时间标识
				}
				
				//新增调入仓储详情
				$two_roominfo_sql['pid']=$two_room['id'];
				$two_roominfo_sql['type']=6;//调拨-出
				$two_roominfo_sql['class']=$id;
				$two_roominfo_sql['info']=$allocationinfo_vo['id'];
				$two_roominfo_sql['nums']=$allocationinfo_vo['nums'];
				$two_roominfo_sql['timemark']=$timemark;//时间标识
				$two_roominfo_sql['oldtimemark']=$room_oldtimemark;//旧时间标识
				roominfo::create ($two_roominfo_sql);
				allocationinfo::update (['id'=>$allocationinfo_vo['id'],'toroom'=>$two_room['id'],'timemark'=>$timemark]);//info保存调拨仓储ID以及时间标识
				//此处串码做pid转移处理
				if (!empty($allocationinfo_vo['serial'])){
					//串号存在
					$tmp_serial=explode(',',$allocationinfo_vo['serial']);
					foreach ($tmp_serial as $tmp_serial_vo){
					    $serial_info=serial::get(['code'=>$tmp_serial_vo]);//获取串号详情
					    serial::update(['id'=>$serial_info['id'],'room'=>$two_room['id'],'type'=>0,'timemark'=>$timemark]);//修改串号状态
					    serialinfo::create ([
					        'pid'=>$serial_info['id'],
					        'type'=>5,
					        'class'=>$id,
					        'oldroom'=>$serial_info['room'],
					        'timemark'=>$timemark,
					        'oldtimemark'=>$serial_info['timemark']
					    ]);//增加串号使用详情
					}
				}
			}
			//更新class审核状态和对账单状态
			allocationclass::update (['id'=>$id,'type'=>1,'auditinguser'=>Session ('is_user_id'),'auditingtime'=>time()]);
			set_summary('allocationclass',$id,true);//更新Summary
			if ($auth){
				push_log ('自动审核调拨单-'.$allocationclass['number']);
			}else {
				//手动
				push_log ('审核调拨单-'.$allocationclass['number']);
			}
		}else {
			//反审核
			//判断逆操作以及串码状态
			foreach ($allocationinfo as $key=>$check_vo){
				//判断逆操作
				//判断当前详情的时间标识与仓储ID中的时间标识是否相同
				if($check_vo['timemark']!==$check_vo['room']['info']['timemark']){
				    return json (['state'=>'set_error','row'=>$key+1]);
                    exit;
				}else{
				    //开始判断当前时间标识是否存在后续操作
				    if(roominfo::get([
            	        'pid'=>$check_vo['room']['ape'],
            	        'timemark'=>['gt',$check_vo['timemark']]
            	     ])){
            	        return json (['state'=>'set_error','row'=>$key+1]);
                        exit;
            	    }
				}
			}
			foreach ($allocationinfo as $allocationinfo_vo){
			    //先处理调入仓库
			    room::where (['id'=>$allocationinfo_vo['toroom']])->setDec('nums',$allocationinfo_vo['nums']);//减少调入仓储库存数量
			    $two_roominfo=roominfo::get([
				    'pid'=>$allocationinfo_vo['toroom'],
				    'type'=>6,
				    'info'=>$allocationinfo_vo['id']
				    ]);//获取调入仓储详情
				room::update([
				    'id'=>$allocationinfo_vo['toroom'],
				    'timemark'=>$two_roominfo['oldtimemark']
				    ]);//还原旧时间标识
			    //处理调出仓库
			    room::where (['id'=>$allocationinfo_vo['room']['ape']])->setInc('nums',$allocationinfo_vo['nums']);//增加对应库存数量
				$one_roominfo=roominfo::get([
				    'pid'=>$allocationinfo_vo['room']['ape'],
				    'type'=>5,
				    'info'=>$allocationinfo_vo['id']
				    ]);//获取调出仓储详情
				room::update([
				    'id'=>$allocationinfo_vo['room']['ape'],
				    'timemark'=>$one_roominfo['oldtimemark']
				    ]);//还原旧时间标识
				allocationinfo::update (['id'=>$allocationinfo_vo['id'],'toroom'=>0,'timemark'=>0]);//详情还原所入仓储ID|时间标识
				//处理串码
				if(!empty($allocationinfo_vo['serial'])){
				    $serial_arr=serial::where (['code'=>['in',explode(',',$allocationinfo_vo['serial']),'OR']])->select();//获取串号详情
				    foreach ($serial_arr as $serial_vo) {
				        $serial_info=serialinfo::get(['pid'=>$serial_vo['id'],'type'=>5,'timemark'=>$serial_vo['timemark']]);//获取当前时间标识的串码详情
				        serial::update([
				            'id'=>$serial_vo['id'],
				            'room'=>$serial_info['oldroom'],
				            'type'=>0,
				            'timemark'=>$serial_info['oldtimemark']
				        ]);//还原旧仓储ID|设置状态
				        serialinfo::destroy(['id'=>$serial_info['id']]);//删除串码详情
				    }
				}
			}
			roominfo::destroy (['type'=>['in',[5,6],'OR'],'class'=>$id]);//删除调入调出仓储详情
			allocationclass::update (['id'=>$id,'type'=>0,'auditinguser'=>0,'auditingtime'=>0]);//复原class审核状态和对账单状态
			set_summary('allocationclass',$id,false);//更新Summary
			push_log ('反审核调拨单-'.$allocationclass['number']);
		}
		return json (['state'=>'success']);
	}
	//删除调拨单
	public function  del_allocation(){
		$id=input ('post.id');
		$allocationclass=allocationclass::where (['id'=>$id])->find ();
		if (empty($allocationclass['type']['ape'])){
			//未审核可删除
			allocationclass::destroy (['id'=>$id]);
			//删除class
			allocationinfo::destroy (['pid'=>$id]);
			//删除info
		}
		push_log ('删除调拨单-'.$allocationclass['number']);
		return json ('success');
	}
	//保存其他入库单
	public function  save_otpurchase (){
		$input=input ('post.');
		$info=$input['info'];
		//预先判断串号重复情况
		foreach ($info as $key=>$check_vo){
			//如果为串码商品,并且串号存在
			if (!empty($check_vo['serialtype']) && array_key_exists('serial',$check_vo)){
				//判断 - 查找串码状态为未销售的
				if (serial::get (['code'=>['in',explode(',',$check_vo['serial']),'OR'],'type'=>['neq',2]])){
				    //找到重复串码
					return json (['state'=>'serial_repeat','row'=>$key+1]);
					exit ;
				}
			}
		}
		$class_sql['time']=$input['time'];
		$class_sql['number']=$input['number'];
		$class_sql['pagetype']=$input['pagetype'];
		$class_sql['user']=$input['user'];
		$class_sql['data']=$input['data'];
		if (!isset($input['id'])){
			//新增
			$class=otpurchaseclass::create ($class_sql);
			set_number('otpurchase');
		}else {
			//更新
			$class_sql['id']=$input['id'];
			$class=otpurchaseclass::update ($class_sql);
			otpurchaseinfo::destroy (['pid'=>$input['id']]);
			//删除旧info数据
		}
		$timemark=time();//时间标识
		//详情
		foreach ($info as $vo){
			$info_sql=[];
			$info_sql['pid']=$class['id'];
			$info_sql['goods']=$vo['goods'];
			if ($vo['attr']!=="-1"){
				$info_sql['attr']=$vo['attr'];
			}
			$info_sql['warehouse']=$vo['warehouse'];
			//判断串号
			if (array_key_exists('serial',$vo) && !empty($vo['serialtype'])){
				$info_sql['serial']=$vo['serial'];
			}
			$info_sql['nums']=$vo['nums'];
			if (array_key_exists('batch',$vo) && !empty($vo['batch'])){
				$info_sql['batch']=$vo['batch'];
			}
			if (array_key_exists('data',$vo)){
				$info_sql['data']=$vo['data'];
			}
			$info_sql['timemark']=0;//时间标识
			otpurchaseinfo::create ($info_sql);
		}
		push_log ('提交其他入库单-'.$input['number']);
		//判断自动审核
		$sys=sys::all ();
		if (empty($sys['1']['info']['auditing'])){
			$this->auditing_otpurchase ($class['id'],true);
		}
		return json (['state'=>'success']);
	}
	//审核|反审核其他入库单
	public function  auditing_otpurchase ($id,$auth=false){
		//兼容自动审核
		if (empty($id)){
			$id=input ('post.id');
		}
		$otpurchaseclass=otpurchaseclass::get ($id);
		$otpurchaseinfo=otpurchaseinfo::where (['pid'=>$id])->select ()->toArray ();
		//读取info
		if (empty($otpurchaseclass['type']['ape'])){
			//审核
			//预先判断串号重复情况
			foreach ($otpurchaseinfo as $key=>$check_vo){
				//判断串号是否设置
				if (!empty($check_vo['serial'])){
				    //查找串码状态为在库和销售
					if (serial::get (['code'=>['in',explode(',',$check_vo['serial']),'OR'],'type'=>['neq',2]])){
						if ($auth){
							//自动审核
							push_log ('自动审核其他入库单-'.$otpurchaseclass['number'].'失败，原因：串码重复');
							exit ;
						}else {
							//手动审核
							return json (['state'=>'serial_repeat','row'=>$key+1]);
							exit ;
						}
					}
				}
			}
			$timemark=time();//时间标识
			$goods_repeat_arr=[];
			foreach ($otpurchaseinfo as $otpurchaseinfo_vo){
				//循环保存数据
				$room_sql=[];
				$room_sql['warehouse']=$otpurchaseinfo_vo['warehouse']['ape'];
				$room_sql['goods']=$otpurchaseinfo_vo['goods']['ape'];
				$room_sql['attr']=$otpurchaseinfo_vo['attr']['ape'];
				$room_sql['batch']=$otpurchaseinfo_vo['batch'];
				$room=room::get($room_sql);
				//判断仓储
				if (empty($room)){
					//新增仓储数据
					$room_sql['nums']=$otpurchaseinfo_vo['nums'];
					$room_sql['timemark']=$timemark;//时间标识
					$room=room::create ($room_sql);//时间标识
					$room_oldtimemark=0;//初始化旧时间标识
				} else {
					//更新仓储数据
					room::where (['id'=>$room['id']])->update([
					    'nums'=>$room['nums']+$otpurchaseinfo_vo['nums'],
					    'timemark'=>$timemark
					]);
					//增加库存数量
					$room_oldtimemark=$room['timemark'];//转存仓储旧时间标识
				}
				otpurchaseinfo::update (['id'=>$otpurchaseinfo_vo['id'],'room'=>$room['id'],'timemark'=>$timemark]);//info保存仓储ID以及时间标识
				//判断是否有重复商品
				if(array_key_exists($room['id'],$goods_repeat_arr)){
				    $room_oldtimemark=$goods_repeat_arr[$room['id']];//获取仓储旧时间标识
				}else{
				    $goods_repeat_arr[$room['id']]=$room['timemark'];
				}
				//新增仓储详情
				$roominfo_sql['pid']=$room['id'];
				$roominfo_sql['type']=7;
				$roominfo_sql['class']=$id;
				$roominfo_sql['info']=$otpurchaseinfo_vo['id'];
				$roominfo_sql['nums']=$otpurchaseinfo_vo['nums'];
				$roominfo_sql['timemark']=$timemark;//时间标识
				$roominfo_sql['oldtimemark']=$room_oldtimemark;//时间标识
				roominfo::create ($roominfo_sql);
				//判断批次
				if (!empty($otpurchaseinfo_vo['serial'])){
					//串号存在
					$tmp_serial=explode(',',$otpurchaseinfo_vo['serial']);
					foreach ($tmp_serial as $tmp_serial_vo){
					    //判断串码多次录入情况
					    $serial_ape=serial::get(['code'=>$tmp_serial_vo]);
					    if(empty($serial_ape)){
					        //新录入
					        $oldroom=0;//初始化旧仓库ID
					        $serial_oldtimemark=0;//初始化串码旧时间标识
				        	$serial_info=serial::create ([
				        	    'room'=>$room['id'],
				        	    'code'=>$tmp_serial_vo,
				        	    'type'=>0,
				        	    'timemark'=>$timemark,
				        	]);//增加串号
					    }else{
					        //多次录入
					        $oldroom=$serial_ape['room'];//旧ROOM_ID
					        $serial_oldtimemark=$serial_ape['timemark'];//转存旧串码时间标识
					        $serial_info=serial::update([
					            'id'=>$serial_ape['id'],
					            'room'=>$room['id'],
					            'type'=>0,
					            'timemark'=>$timemark,
					        ]);//更新串号
					    }
						serialinfo::create ([
						    'pid'=>$serial_info['id'],
						    'type'=>6,
						    'class'=>$id,
						    'oldroom'=>$oldroom,//旧仓储ID
						    'timemark'=>$timemark,//新时间标识
						    'oldtimemark'=>$serial_oldtimemark//旧时间标识
						]);//增加串号详情
					}
				}
			}
			//更新class审核状态
			otpurchaseclass::update (['id'=>$id,'type'=>1,'auditinguser'=>Session ('is_user_id'),'auditingtime'=>time()]);
			set_summary('otpurchaseclass',$id,true);//更新Summary
			if ($auth){
				push_log ('自动审核其他入库单-'.$otpurchaseclass['number']);
			}else {
				//手动
				push_log ('审核其他入库单-'.$otpurchaseclass['number']);
			}
		}else {
			//反审核
			//判断库存中的是否够反审核|串码是否已经使用
			$tmp_arr=room::where (['id'=>['in',array_column ($otpurchaseinfo,'room'),'OR']])->select ()->toArray();//取出所有商品的仓储数据
			foreach ($otpurchaseinfo as $key=>$check_vo){
			    $tmp_val=find_arr_key_val ($tmp_arr,'id',$check_vo['room']);//获取当前仓储ID的数据
			    //判断逆操作
			    //判断当前详情的时间标识与仓储ID中的时间标识是否相同
			    if(!empty($tmp_val['timemark']!==$check_vo['timemark'])){
			        return json (['state'=>'set_error','row'=>$key+1]);
			        exit;
			    }else{
			        //开始判断当前时间标识是否存在后续操作
    			    if(roominfo::get([
    			        'pid'=>$check_vo['room'],
    			        'timemark'=>['gt',$check_vo['timemark']]
    			     ])){
    			        return json (['state'=>'set_error','row'=>$key+1]);
                        exit;
    			    }
			    }
			}
			foreach ($otpurchaseinfo as $otpurchaseinfo_vo){
			    room::where (['id'=>$otpurchaseinfo_vo['room']])->setDec ('nums',$otpurchaseinfo_vo['nums']);//减少库存
				$room_info=roominfo::get([
				    'pid'=>$otpurchaseinfo_vo['room'],
				    'type'=>7,
				    'info'=>$otpurchaseinfo_vo['id']
				    ]);//获取仓储详情
				room::update([
				    'id'=>$otpurchaseinfo_vo['room'],
				    'timemark'=>$room_info['oldtimemark']
				    ]);//还原旧时间标识
				otpurchaseinfo::update (['id'=>$otpurchaseinfo_vo['id'],'room'=>0,'timemark'=>0]);//info删除仓储ID还原时间标识
				if(!empty($otpurchaseinfo_vo['serial'])){
				    $serial_arr=serial::where (['code'=>['in',explode(',',$otpurchaseinfo_vo['serial']),'OR']])->select();//获取串号详情
				    foreach ($serial_arr as $serial_vo) {
				        $serial_info=serialinfo::get(['pid'=>$serial_vo['id'],'type'=>6,'timemark'=>$serial_vo['timemark']]);//获取当前时间标识的串码详情
				        serial::update([
				            'id'=>$serial_vo['id'],
				            'room'=>$serial_info['oldroom'],
				            'type'=>2,
				            'timemark'=>$serial_info['oldtimemark']
				        ]);//还原旧仓储ID|设置状态|还原旧时间标识
				        serialinfo::destroy(['id'=>$serial_info['id']]);//删除串码详情
				    }
				}
			}
			roominfo::destroy (['type'=>7,'class'=>$id]);//删除仓储详情
			otpurchaseclass::update (['id'=>$id,'type'=>0,'auditinguser'=>0,'auditingtime'=>0]);
			//复原class审核状态
			set_summary('otpurchaseclass',$id,false);//更新Summary
			push_log ('反审核其他入库单-'.$otpurchaseclass['number']);
		}
		return json (['state'=>'success']);
	}
	//删除其他入库单
	public function  del_otpurchase(){
		$id=input ('post.id');
		$otpurchaseclass=otpurchaseclass::where (['id'=>$id])->find ();
		if (empty($otpurchaseclass['type']['ape'])){
			//未审核可删除
			otpurchaseclass::destroy (['id'=>$id]);
			//删除class
			otpurchaseinfo::destroy (['pid'=>$id]);
			//删除info
		}
		push_log ('删除其他入库单-'.$otpurchaseclass['number']);
		return json ('success');
	}
	//保存其他出库单
	public function  save_otsale (){
		$input=input ('post.');
		$info=$input['info'];
		//预先判断串号使用情况
		$tmp_arr=room::where (['id'=>['in',array_column ($info,'room'),'OR']])->select ()->toArray();//取出所有商品的仓储数据
		foreach ($info as $key=>$check_vo){
		    //先判断库存
		    $tmp_val=find_arr_key_val ($tmp_arr,'id',$check_vo['room']);//获取当前仓储ID的数据
			if ($check_vo['nums']>$tmp_val['nums']){
				return json (['state'=>'stock_error','row'=>$key+1]);
				exit ;
			}
			//判断串码是否存在,此处加判断高并发可能出问题,待验证
			if(array_key_exists('serial',$check_vo) && !empty($check_vo['serial'])){
			    //查找系统未销售串码
    			$tmp_serial=arrayChange (serial::where (['room'=>$check_vo['room'],'type'=>0])->field ('code')->select ()->toArray (),'code');
				$serial_arr=explode(',',$check_vo['serial']);
				foreach ($serial_arr as $arr_vo){
					if (!in_array($arr_vo,$tmp_serial)){
						return json (['state'=>'serial_error','row'=>$key+1]);
						exit ;
					}
				}
    		    $info[$key]['serial_type']=true;//是串码商品
			}else{
			    $info[$key]['serial_type']=false;//非串码商品
			}
		}
		
		$class_sql['time']=$input['time']; 
		$class_sql['number']=$input['number'];
		$class_sql['pagetype']=$input['pagetype'];
		$class_sql['user']=$input['user'];
		$class_sql['data']=$input['data'];
		if (!isset($input['id'])){
			//新增
			$class=otsaleclass::create ($class_sql);
			set_number('otsale');
		}else {
			//更新
			$class_sql['id']=$input['id'];
			$class=otsaleclass::update ($class_sql);
			otsaleinfo::destroy (['pid'=>$input['id']]);//删除旧info数据
		}
		//详情
		foreach ($info as $vo){
			$info_sql=[];
			$info_sql['pid']=$class['id'];
			$info_sql['room']=$vo['room'];
			$tmp_warehouse=room::get (['id'=>$vo['room']]);
			$info_sql['warehouse']=$tmp_warehouse['warehouse']['ape'];
			$info_sql['goods']=$vo['goods'];
			//判断串号
			if ($vo['serial_type'] && array_key_exists('serial',$vo)){
				$info_sql['serial']=$vo['serial'];
			}
			$info_sql['nums']=$vo['nums'];
			if (array_key_exists('data',$vo)){
				$info_sql['data']=$vo['data'];
			}
			$info_sql['timemark']=0;//时间标识
			otsaleinfo::create ($info_sql);
		}
		push_log ('提交其他出库单-'.$input['number']);
		//判断自动审核
		$sys=sys::all();
		if(empty($sys['1']['info']['auditing'])){
		    $this->auditing_otsale($class['id'],true);
		}
		return json (['state'=>'success']);
	}
	//审核|反审核其他出库单
	public function  auditing_otsale ($id,$auth=false){
		//兼容自动审核
		if (empty($id)){
			$id=input ('post.id');
		}
		$otsaleclass=otsaleclass::get ($id);
		$otsaleinfo=otsaleinfo::where (['pid'=>$id])->select ()->toArray ();
		//读取info
		if (empty($otsaleclass['type']['ape'])){
			//审核
			//预先判断库存以及串码使用情况
			foreach ($otsaleinfo as $key=>$check_vo){
				//判断库存
				if ($check_vo['nums']>$check_vo['room']['info']['nums']){
					if ($auth){
						push_log ('自动审核其他出库单-'.$check_vo['number'].'失败，原因：商品库存不足');
						exit ;
					}else {
						return json (['state'=>'stock_error','row'=>$key+1]);
						exit ;
					}
				}
				//判断串号是否使用
				if (!empty($check_vo['serial'])){
					$tmp_serial_arr=explode(',',$check_vo['serial']);
					//判断当前商品串码个数与串码表中这些串码(状态未销售)的个数是否相同
					if (count($tmp_serial_arr)!==serial::where (['code'=>['in',$tmp_serial_arr,'OR'],'type'=>0])->count()){
						if ($auth){
							//自动审核
							push_log ('自动审核其他出库单-'.$check_vo['number'].'失败，原因：商品串码已销售');
						    exit ;
						}else {
							//手动审核
							return json (['state'=>'serial_error','row'=>$key+1]);
							exit ;
						}
					}
				}
			}
			$timemark=time();//时间标识
			foreach ($otsaleinfo as $otsaleinfo_vo){
				//循环保存数据
				room::update([
				    'id'=>$otsaleinfo_vo['room']['ape'],
				    'nums'=>$otsaleinfo_vo['room']['info']['nums']-$otsaleinfo_vo['nums'],
				    'timemark'=>$timemark//时间标识
				]);//更新仓储信息
				//新增仓储详情
				$roominfo_sql['pid']=$otsaleinfo_vo['room']['ape'];
				$roominfo_sql['type']=8;
				$roominfo_sql['class']=$id;
				$roominfo_sql['info']=$otsaleinfo_vo['id'];
				$roominfo_sql['nums']=$otsaleinfo_vo['nums'];
				$roominfo_sql['timemark']=$timemark;//时间标识
				$roominfo_sql['oldtimemark']=$otsaleinfo_vo['room']['info']['timemark'];//旧时间标识
				roominfo::create ($roominfo_sql);
				otsaleinfo::update(['id'=>$otsaleinfo_vo['id'],'timemark'=>$timemark]);//更新详情次数
				//判断批次
				if (!empty($otsaleinfo_vo['serial'])){
					//串号存在
					$tmp_serial=explode(',',$otsaleinfo_vo['serial']);
					foreach ($tmp_serial as $tmp_serial_vo){
					    $serial_info=serial::get(['code'=>$tmp_serial_vo]);//获取串号详情
					    serial::update(['id'=>$serial_info['id'],'type'=>1,'timemark'=>$timemark]);//修改串号状态
					    serialinfo::create ([
					        'pid'=>$serial_info['id'],
					        'type'=>7,
					        'class'=>$id,
					        'oldroom'=>$serial_info['room'],
					        'timemark'=>$timemark,
					        'oldtimemark'=>$serial_info['timemark']
					    ]);//增加串号使用详情
					}
				}
			}
			//更新class审核状态和对账单状态
			otsaleclass::update (['id'=>$id,'type'=>1,'auditinguser'=>Session ('is_user_id'),'auditingtime'=>time()]);
			set_summary('otsaleclass',$id,true);//更新Summary
			if ($auth){
				push_log ('自动审核其他出库单-'.$otsaleclass['number']);
			}else {
				//手动
				push_log ('审核其他出库单-'.$otsaleclass['number']);
			}
		}else {
			//反审核
			//判断逆操作以及串码状态
			foreach ($otsaleinfo as $key=>$check_vo){
				//判断逆操作
				//判断当前详情的时间标识与仓储ID中的时间标识是否相同
				if($check_vo['timemark']!==$check_vo['room']['info']['timemark']){
				    return json (['state'=>'set_error','row'=>$key+1]);
                    exit;
				}else{
				    //开始判断当前时间标识是否存在后续操作
				    if(roominfo::get([
            	        'pid'=>$check_vo['room']['ape'],
            	        'timemark'=>['gt',$check_vo['timemark']]
            	     ])){
            	        return json (['state'=>'set_error','row'=>$key+1]);
                        exit;
            	    }
				}
			}
			foreach ($otsaleinfo as $otsaleinfo_vo){
				room::where (['id'=>$otsaleinfo_vo['room']['ape']])->setInc('nums',$otsaleinfo_vo['nums']);//增加对应库存数量
				$room_info=roominfo::get([
				    'pid'=>$otsaleinfo_vo['room']['ape'],
				    'type'=>8,
				    'info'=>$otsaleinfo_vo['id']
				    ]);//获取仓储详情
				room::update([
				    'id'=>$otsaleinfo_vo['room']['ape'],
				    'timemark'=>$room_info['oldtimemark']
				    ]);//还原旧时间标识
				otsaleinfo::update (['id'=>$otsaleinfo_vo['id'],'timemark'=>0]);//详情还原时间标识
				if(!empty($otsaleinfo_vo['serial'])){
				    $serial_arr=serial::where (['code'=>['in',explode(',',$otsaleinfo_vo['serial']),'OR']])->select();//获取串号详情
				    foreach ($serial_arr as $serial_vo) {
				        $serial_info=serialinfo::get(['pid'=>$serial_vo['id'],'type'=>7,'timemark'=>$serial_vo['timemark']]);//获取当前时间标识的串码详情
				        serial::update([
				            'id'=>$serial_vo['id'],
				            'room'=>$serial_info['oldroom'],
				            'type'=>0,
				            'timemark'=>$serial_info['oldtimemark']
				        ]);//还原旧仓储ID|设置状态
				        serialinfo::destroy(['id'=>$serial_info['id']]);//删除串码详情
				    }
				}
			}
			roominfo::destroy (['type'=>8,'class'=>$id]);//删除仓储详情
			otsaleclass::update (['id'=>$id,'type'=>0,'auditinguser'=>0,'auditingtime'=>0]);
			//复原class审核状态
			set_summary('otsaleclass',$id,false);//更新Summary
			push_log ('反审核其他出库单-'.$otsaleclass['number']);
		}
		return json (['state'=>'success']);
	}
	//删除其他出库单
	public function  del_otsale(){
		$id=input ('post.id');
		$otsaleclass=otsaleclass::where (['id'=>$id])->find ();
		if (empty($otsaleclass['type']['ape'])){
			//未审核可删除
			otsaleclass::destroy (['id'=>$id]);
			//删除class
			otsaleinfo::destroy (['pid'=>$id]);
			//删除info
		}
		push_log ('删除其他出库单-'.$otsaleclass['number']);
		return json ('success');
	}
	//保存收款单
	public function  save_gather (){
		$input=input ('post.');
		$class_sql['customer']=$input['customer'];
		$class_sql['time']=$input['time']; 
		$class_sql['number']=$input['number'];
		$class_sql['user']=$input['user'];
		if (!empty($input['file'])){
			$class_sql['file']=$input['file'];
		}
		$class_sql['data']=$input['data'];
		if (!isset($input['id'])){
			//新增
			$class=gatherclass::create ($class_sql);
			set_number('gather');
		}else {
			//更新
			$class_sql['id']=$input['id'];
			$class=gatherclass::update ($class_sql);
			gatherinfo::destroy (['pid'=>$input['id']]);//删除旧info数据
		}
		$info=$input['info'];//详情
		foreach ($info as $vo){
			$info_sql=[];
			$info_sql['pid']=$class['id'];
			$info_sql['account']=$vo['account'];
			$info_sql['total']=$vo['total'];
			if (array_key_exists('data',$vo)){
				$info_sql['data']=$vo['data'];
			}
			gatherinfo::create ($info_sql);
		}
		push_log ('提交收款单-'.$input['number']);
		//判断自动审核
		$sys=sys::all();
		if(empty($sys['1']['info']['auditing'])){
		    $this->auditing_gather($class['id'],true);
		}
		return json (['state'=>'success']);
	}
	//审核|反审核收款单
	public function  auditing_gather ($id,$auth=false){
		//兼容自动审核
		if (empty($id)){
			$id=input ('post.id');
		}
		$gatherclass=gatherclass::get ($id);
		$gatherinfo=gatherinfo::where (['pid'=>$id])->select ()->toArray ();
		//读取info
		if (empty($gatherclass['type']['ape'])){
			//审核
			foreach ($gatherinfo as $gatherinfo_vo){
				//循环增加金额
				account::where (['id'=>$gatherinfo_vo['account']['ape']])->setInc ('balance',$gatherinfo_vo['total']);//操作资金-增
				accountinfo::create (['pid'=>$gatherinfo_vo['account']['ape'],'set'=>1,'money'=>$gatherinfo_vo['total'],'type'=>5,'time'=>time(),'user'=>Session ('is_user_id'),'class'=>$id,'number'=>$gatherclass['number'],'only'=>$gatherinfo_vo['id']]);
				//资金操作-详情
			}
			//更新class审核状态
			gatherclass::update (['id'=>$id,'type'=>1,'auditinguser'=>Session ('is_user_id'),'auditingtime'=>time()]);
			if ($auth){
				push_log ('自动审核收款单-'.$gatherclass['number']);
			}else {
				//手动
				push_log ('审核收款单-'.$gatherclass['number']);
			}
		}else {
			//反审核
			foreach ($gatherinfo as $gatherinfo_vo){
			    account::where (['id'=>$gatherinfo_vo['account']['ape']])->setDec ('balance',$gatherinfo_vo['total']);//操作资金-减
			}
			accountinfo::destroy (['type'=>5,'class'=>$id]);
			gatherclass::update (['id'=>$id,'type'=>0,'auditinguser'=>0,'auditingtime'=>0]);//复原class审核状态
			push_log ('反审核收款单-'.$gatherclass['number']);
		}
		return json (['state'=>'success']);
	}
	//库存盘点数据
	public function room_check_info(){
	    if(!empty(input('post.name'))){
            $goods_sql['name|py']=['like','%'.input('post.name').'%'];
        }
        if(!empty(input('post.number'))){
            $goods_sql['number']=['like','%'.input('post.number').'%'];
        }
        if(!empty(input('post.location'))){
            $goods_sql['location']=['like','%'.input('post.location').'%'];
        }
        if(!empty(input('post.spec'))){
            $goods_sql['spec'] = ['like','%'.input('post.spec').'%'];
        }
        //判断商品类型
        if(!empty(input('post.class'))){
            $goods_sql['class'] = ['in',goodsclass_more_arr(input('post.class')),'OR'];
        }
        //判断商品单位
        if(!empty(input('post.unit'))){
            $goods_sql['unit'] = input('post.unit');
        }
        //判断所属品牌
        if(!empty(input('post.brand'))){
            $goods_sql['brand'] = input('post.brand');
        }
        if(isset($goods_sql)){
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $sql['goods']=['in',$goods_arr,'OR'];
        }
        //判断所属仓库
        if(!empty(input('post.warehouse'))){
            $sql['warehouse'] = input('post.warehouse');
        }
        if(isset($sql)){
            $all_nums=room::where($sql)->count();
            $list=room::where($sql)->page(input('post.page'),input('post.rows'))->select()->toArray();
        }else{
            $all_nums=room::count();
            $list=room::page(input('post.page'),input('post.rows'))->select()->toArray();
        }
        foreach ($list as $key=>$vo) {
            $list[$key]['room_id']=$vo['id'];
            $list[$key]['goods_id']=$vo['goods']['ape'];
            $list[$key]['attr']=$vo['attr']['ape'];
            $list[$key]['warehouse_id']=$vo['warehouse']['ape'];
            $list[$key]['warehouse_name']=$vo['warehouse']['info']['name'];
            $list[$key]['goods_name']=$vo['goods']['info']['name'];
            $list[$key]['attr_name']=$vo['attr']['name'];
            $list[$key]['brand']=$vo['goods']['info']['brand']['info']['name'];
            $list[$key]['number']=$vo['goods']['info']['number'];
            $list[$key]['class']=$vo['goods']['info']['class']['info']['name'];
            $list[$key]['spec']=$vo['goods']['info']['spec'];
            $list[$key]['code']=$vo['goods']['info']['code'];
            $list[$key]['unit']=$vo['goods']['info']['unit']['info']['name'];
            $list[$key]['stocktip']=$vo['goods']['info']['stocktip'];
            $list[$key]['location']=$vo['goods']['info']['location'];
            $list[$key]['integral']=$vo['goods']['info']['integral'];
            $list[$key]['retail_name']=$vo['goods']['info']['retail_name'];
            $list[$key]['batch']=$vo['batch'];
            $list[$key]['serial']=implode('|',arrayChange(serial::where(['room'=>$vo['id'],'type'=>0])->field('code')->select()->toArray(),'code'));
            $list[$key]['stock']=$vo['nums'];
            unset($list[$key]['nums']);
            
        }
        if(empty($list)){
            $main['total'] = '0';
			$main['page'] = '0';
			$main['records'] = '0';
        }else{
			$main['total'] = ceil($all_nums / input('post.rows'));//总页数=总条数/每页个数
			$main['page'] = input('post.page');//当前页
			$main['records'] = $all_nums;//总条数
			$main['rows'] = $list; //当前页数据
        }
        return json($main);
	}
	//删除收款单
	public function  del_gather(){
		$id=input ('post.id');
		$gatherclass=gatherclass::where (['id'=>$id])->find ();
		if (empty($gatherclass['type']['ape'])){
			//未审核可删除
			gatherclass::destroy (['id'=>$id]);
			//删除class
			gatherinfo::destroy (['pid'=>$id]);
			//删除info
		}
		push_log ('删除收款单-'.$gatherclass['number']);
		return json ('success');
	}
	//保存付款单
	public function  save_payment (){
		$input=input ('post.');
		$class_sql['supplier']=$input['supplier'];
		$class_sql['time']=$input['time']; 
		$class_sql['number']=$input['number'];
		$class_sql['user']=$input['user'];
		if (!empty($input['file'])){
			$class_sql['file']=$input['file'];
		}
		$class_sql['data']=$input['data'];
		if (!isset($input['id'])){
			//新增
			$class=paymentclass::create ($class_sql);
			set_number('payment');
		}else {
			//更新
			$class_sql['id']=$input['id'];
			$class=paymentclass::update ($class_sql);
			paymentinfo::destroy (['pid'=>$input['id']]);//删除旧info数据
		}
		$info=$input['info'];//详情
		foreach ($info as $vo){
			$info_sql=[];
			$info_sql['pid']=$class['id'];
			$info_sql['account']=$vo['account'];
			$info_sql['total']=$vo['total'];
			if (array_key_exists('data',$vo)){
				$info_sql['data']=$vo['data'];
			}
			paymentinfo::create ($info_sql);
		}
		push_log ('提交付款单-'.$input['number']);
		//判断自动审核
		$sys=sys::all();
		if(empty($sys['1']['info']['auditing'])){
		    $this->auditing_payment($class['id'],true);
		}
		return json (['state'=>'success']);
	}
	//删除付款单
	public function  del_payment(){
		$id=input ('post.id');
		$paymentclass=paymentclass::where (['id'=>$id])->find ();
		if (empty($paymentclass['type']['ape'])){
			//未审核可删除
			paymentclass::destroy (['id'=>$id]);
			//删除class
			paymentinfo::destroy (['pid'=>$id]);
			//删除info
		}
		push_log ('删除付款单-'.$paymentclass['number']);
		return json ('success');
	}
	//审核|反审核付款单
	public function  auditing_payment ($id,$auth=false){
		//兼容自动审核
		if (empty($id)){
			$id=input ('post.id');
		}
		$paymentclass=paymentclass::get ($id);
		$paymentinfo=paymentinfo::where (['pid'=>$id])->select ()->toArray ();
		//读取info
		if (empty($paymentclass['type']['ape'])){
			//审核
			foreach ($paymentinfo as $paymentinfo_vo){
				//循环减少金额
				account::where (['id'=>$paymentinfo_vo['account']['ape']])->setDec ('balance',$paymentinfo_vo['total']);//操作资金-减
				accountinfo::create (['pid'=>$paymentinfo_vo['account']['ape'],'set'=>0,'money'=>$paymentinfo_vo['total'],'type'=>6,'time'=>time(),'user'=>Session ('is_user_id'),'class'=>$id,'number'=>$paymentclass['number'],'only'=>$paymentinfo_vo['id']]);
				//资金操作-详情
			}
			//更新class审核状态
			paymentclass::update (['id'=>$id,'type'=>1,'auditinguser'=>Session ('is_user_id'),'auditingtime'=>time()]);
			if ($auth){
				push_log ('自动审核付款单-'.$paymentclass['number']);
			}else {
				//手动
				push_log ('审核付款单-'.$paymentclass['number']);
			}
		}else {
			//反审核
			foreach ($paymentinfo as $paymentinfo_vo){
			    account::where (['id'=>$paymentinfo_vo['account']['ape']])->setInc ('balance',$paymentinfo_vo['total']);//操作资金-减
			}
			accountinfo::destroy (['type'=>6,'class'=>$id]);
			paymentclass::update (['id'=>$id,'type'=>0,'auditinguser'=>0,'auditingtime'=>0]);//复原class审核状态
			push_log ('反审核付款单-'.$paymentclass['number']);
		}
		return json (['state'=>'success']);
	}
	//保存收款单
	public function  save_otgather (){
		$input=input ('post.');
		$class_sql['time']=$input['time']; 
		$class_sql['number']=$input['number'];
		$class_sql['user']=$input['user'];
		$class_sql['data']=$input['data'];
		if (!isset($input['id'])){
			//新增
			$class=otgatherclass::create ($class_sql);
			set_number('otgather');
		}else {
			//更新
			$class_sql['id']=$input['id'];
			$class=otgatherclass::update ($class_sql);
			otgatherinfo::destroy (['pid'=>$input['id']]);//删除旧info数据
		}
		$info=$input['info'];//详情
		foreach ($info as $vo){
			$info_sql=[];
			$info_sql['pid']=$class['id'];
			$info_sql['account']=$vo['account'];
			$info_sql['total']=$vo['total'];
			if (array_key_exists('data',$vo)){
				$info_sql['data']=$vo['data'];
			}
			otgatherinfo::create ($info_sql);
		}
		push_log ('提交其他收入单-'.$input['number']);
		//判断自动审核
		$sys=sys::all();
		if(empty($sys['1']['info']['auditing'])){
		    $this->auditing_otgather($class['id'],true);
		}
		return json (['state'=>'success']);
	}
	//审核|反审核收款单
	public function  auditing_otgather ($id,$auth=false){
		//兼容自动审核
		if (empty($id)){
			$id=input ('post.id');
		}
		$otgatherclass=otgatherclass::get ($id);
		$otgatherinfo=otgatherinfo::where (['pid'=>$id])->select ()->toArray ();
		//读取info
		if (empty($otgatherclass['type']['ape'])){
			//审核
			foreach ($otgatherinfo as $otgatherinfo_vo){
				//循环增加金额
				account::where (['id'=>$otgatherinfo_vo['account']['ape']])->setInc ('balance',$otgatherinfo_vo['total']);//操作资金-增
				accountinfo::create (['pid'=>$otgatherinfo_vo['account']['ape'],'set'=>1,'money'=>$otgatherinfo_vo['total'],'type'=>7,'time'=>time(),'user'=>Session ('is_user_id'),'class'=>$id,'number'=>$otgatherclass['number'],'only'=>$otgatherinfo_vo['id']]);
				//资金操作-详情
			}
			//更新class审核状态
			otgatherclass::update (['id'=>$id,'type'=>1,'auditinguser'=>Session ('is_user_id'),'auditingtime'=>time()]);
			if ($auth){
				push_log ('自动审核其他收入单-'.$otgatherclass['number']);
			}else {
				//手动
				push_log ('审核其他收入单-'.$otgatherclass['number']);
			}
		}else {
			//反审核
			foreach ($otgatherinfo as $otgatherinfo_vo){
			    account::where (['id'=>$otgatherinfo_vo['account']['ape']])->setDec ('balance',$otgatherinfo_vo['total']);//操作资金-减
			}
			accountinfo::destroy (['type'=>7,'class'=>$id]);
			otgatherclass::update (['id'=>$id,'type'=>0,'auditinguser'=>0,'auditingtime'=>0]);//复原class审核状态
			push_log ('反审核其他收入单-'.$otgatherclass['number']);
		}
		return json (['state'=>'success']);
	}
	//删除其他收入单
	public function  del_otgather(){
		$id=input ('post.id');
		$otgatherclass=otgatherclass::where (['id'=>$id])->find ();
		if (empty($otgatherclass['type']['ape'])){
			//未审核可删除
			otgatherclass::destroy (['id'=>$id]);
			//删除class
			otgatherinfo::destroy (['pid'=>$id]);
			//删除info
		}
		push_log ('删除其他收入单-'.$otgatherclass['number']);
		return json ('success');
	}
	//保存其他支出单
	public function  save_otpayment (){
		$input=input ('post.');
		$class_sql['time']=$input['time']; 
		$class_sql['number']=$input['number'];
		$class_sql['user']=$input['user'];
		$class_sql['data']=$input['data'];
		if (!isset($input['id'])){
			//新增
			$class=otpaymentclass::create ($class_sql);
			set_number('otpayment');
		}else {
			//更新
			$class_sql['id']=$input['id'];
			$class=otpaymentclass::update ($class_sql);
			otpaymentinfo::destroy (['pid'=>$input['id']]);//删除旧info数据
		}
		$info=$input['info'];//详情
		foreach ($info as $vo){
			$info_sql=[];
			$info_sql['pid']=$class['id'];
			$info_sql['account']=$vo['account'];
			$info_sql['total']=$vo['total'];
			if (array_key_exists('data',$vo)){
				$info_sql['data']=$vo['data'];
			}
			otpaymentinfo::create ($info_sql);
		}
		push_log ('提交其他支出单-'.$input['number']);
		//判断自动审核
		$sys=sys::all();
		if(empty($sys['1']['info']['auditing'])){
		    $this->auditing_otpayment($class['id'],true);
		}
		return json (['state'=>'success']);
	}
	//审核|反审核其他支出单
	public function  auditing_otpayment ($id,$auth=false){
		//兼容自动审核
		if (empty($id)){
			$id=input ('post.id');
		}
		$otpaymentclass=otpaymentclass::get ($id);
		$otpaymentinfo=otpaymentinfo::where (['pid'=>$id])->select ()->toArray ();
		//读取info
		if (empty($otpaymentclass['type']['ape'])){
			//审核
			foreach ($otpaymentinfo as $otpaymentinfo_vo){
				//循环减少金额
				account::where (['id'=>$otpaymentinfo_vo['account']['ape']])->setDec ('balance',$otpaymentinfo_vo['total']);//操作资金-减
				accountinfo::create (['pid'=>$otpaymentinfo_vo['account']['ape'],'set'=>0,'money'=>$otpaymentinfo_vo['total'],'type'=>8,'time'=>time(),'user'=>Session ('is_user_id'),'class'=>$id,'number'=>$otpaymentclass['number'],'only'=>$otpaymentinfo_vo['id']]);
				//资金操作-详情
			}
			//更新class审核状态
			otpaymentclass::update (['id'=>$id,'type'=>1,'auditinguser'=>Session ('is_user_id'),'auditingtime'=>time()]);
			if ($auth){
				push_log ('自动审核其他支出单-'.$otpaymentclass['number']);
			}else {
				//手动
				push_log ('审核其他支出单-'.$otpaymentclass['number']);
			}
		}else {
			//反审核
			foreach ($otpaymentinfo as $otpaymentinfo_vo){
			    account::where (['id'=>$otpaymentinfo_vo['account']['ape']])->setInc ('balance',$otpaymentinfo_vo['total']);//操作资金-减
			}
			accountinfo::destroy (['type'=>8,'class'=>$id]);
			otpaymentclass::update (['id'=>$id,'type'=>0,'auditinguser'=>0,'auditingtime'=>0]);//复原class审核状态
			push_log ('反审核其他支出单-'.$otpaymentclass['number']);
		}
		return json (['state'=>'success']);
	}
	//删除其他支出单
	public function  del_otpayment(){
		$id=input ('post.id');
		$otpaymentclass=otpaymentclass::where (['id'=>$id])->find ();
		if (empty($otpaymentclass['type']['ape'])){
			//未审核可删除
			otpaymentclass::destroy (['id'=>$id]);
			//删除class
			otpaymentinfo::destroy (['pid'=>$id]);
			//删除info
		}
		push_log ('删除其他支出单-'.$otpaymentclass['number']);
		return json ('success');
	}
	//保存零售单
	public function save_cashier(){
	    $input=input ('post.');
	    $time=time();
	    //判断是否处理商品数据
	    if(array_key_exists('goods_info',$input)){
	        $goodsinfo=$input['goods_info'];
    	    //预先判断商品情况
    		$tmp_arr=room::where (['id'=>['in',array_column ($goodsinfo,'id'),'OR']])->select ()->toArray();//取出所有商品的仓储数据
    		foreach ($goodsinfo as $key=>$check_vo){
    		    //先判断库存
    		    $tmp_val=find_arr_key_val ($tmp_arr,'id',$check_vo['id']);//获取当前仓储ID的数据
    			if ($check_vo['set_nums']>$tmp_val['nums']){
    				return json (['state'=>'stock_error','row'=>$key+1]);
    				exit ;
    			}
    			//判断串码是否存在,此处加判断高并发可能出问题,待验证
    			if(array_key_exists('set_serial',$check_vo) && !empty($check_vo['serial'])){
    			    //查找系统未销售串码
        			$tmp_serial=arrayChange (serial::where (['room'=>$check_vo['id'],'type'=>0])->field ('code')->select ()->toArray (),'code');
    				$serial_arr=explode(',',$check_vo['serial']);
    				foreach ($serial_arr as $arr_vo){
    					if (!in_array($arr_vo,$tmp_serial)){
    						return json (['state'=>'serial_error','row'=>$key+1]);
    						exit ;
    					}
    				}
        		    $goodsinfo[$key]['serial_type']=true;//是串码商品
    			}else{
    			    $goodsinfo[$key]['serial_type']=false;//非串码商品
    			}
    		}
    		//处理商品CLASS数据
    		$cashier_class_sql['time']=strtotime(date('Y-m-d',$time));
    		$cashier_class_sql['number']=get_number('cashier');
    		$cashier_class_sql['total']=$input['settle_goods_total'];
    		$cashier_class_sql['discount']=$input['settle_goods_discount'];
    		$cashier_class_sql['integral']=$input['integral'];
    		$cashier_class_sql['customer']=$input['customer'];
    		$cashier_class_sql['user']=Session('is_user_id');
    		$cashier_class_sql['money']=$input['settle_goods_money'];
    		$cashier_class_sql['account']=$input['account'];
    		$cashier_class_sql['paytype']=$input['paytype'];
    		$cashier_class_sql['payinfo']=json_encode(array());
    		//判断是否存在组合支付信息
    		if(!empty($input['paytype'])){
    		    $cashier_class_sql['payinfo']=json_encode($input['payinfo']);
    		}
    		$cashier_class_sql['type']=0;
    		$cashier_class=cashierclass::create ($cashier_class_sql);
    		set_number('cashier');
    		//处理商品INFO数据
    		foreach ($goodsinfo as $vo){
    			$cashier_info_sql=[];
    			$cashier_info_sql['pid']=$cashier_class['id'];
    			$cashier_info_sql['room']=$vo['id'];
    			$tmp_room=db('room')->find(['id'=>$vo['id']]);
    			$cashier_info_sql['warehouse']=$tmp_room['warehouse'];
    			$cashier_info_sql['goods']=$tmp_room['goods'];
    			//判断串号
    			if ($vo['serial_type'] && array_key_exists('set_serial',$vo)){
    				$cashier_info_sql['serial']=implode(",",$vo['set_serial']);
    			}
    			$cashier_info_sql['nums']=$vo['set_nums'];
    			$cashier_info_sql['price']=$vo['set_price'];
    			$cashier_info_sql['discount']=$vo['set_discount'];
    			$cashier_info_sql['total']=$vo['set_total'];
    			if (!empty($vo['set_data'])){
    				$cashier_info_sql['data']=$vo['set_data'];
    			}
    			$cashier_info_sql['timemark']=0;//时间标识
    			cashierinfo::create ($cashier_info_sql);
    		}
    		$this->auditing_cashier($cashier_class['id'],true);
    		$rejson['id']=$cashier_class['id'];
	    }
	    //判断是否处理服务数据
	    if(array_key_exists('item_info',$input)){
	        //处理服务CLASS数据
	        $item_class_sql['customer']=$input['customer'];
    		$item_class_sql['time']=date('Y-m-d',$time);
    		$item_class_sql['number']=get_number('itemorder');
    		$item_class_sql['total']=$input['settle_item_total'];
    		$item_class_sql['discount']=$input['settle_item_discount'];
    		$item_class_sql['money']=$input['settle_item_money'];
    		$item_class_sql['user']=Session('is_user_id');
    		//判断是否组合支付
    		if(empty($input['paytype'])){
    		    //非组合支付
    		    $item_class_sql['account']=$input['account'];
    		}else{
    		    //组合支付取默认服务账户
    		    $sys=sys::all();
    		    $item_class_sql['account']=$sys['3']['info']['cashier_item'];
    		}
    		$item_class_sql['billtype']=-1;
	        $item_class=itemorderclass::create ($item_class_sql);
	        set_number('itemorder');
	        //处理服务INFO数据
	        $iteminfo=$input['item_info'];
    		foreach ($iteminfo as $vo){
    			$item_info_sql=[];
    			$item_info_sql['pid']=$item_class['id'];
    			$item_info_sql['item']=$vo['id'];
    			$item_info_sql['nums']=$vo['set_nums'];
    			$item_info_sql['price']=$vo['set_price'];
    			$item_info_sql['total']=$vo['set_total'];
    			itemorderinfo::create ($item_info_sql);
    		}
    		push_log ('提交服务订单-'.$item_class_sql['number']);
    		//判断自动审核
    		$this->auditing_itemorder($item_class['id'],true);
	    }
	    $rejson['state']='success';
	    return json ($rejson);
	}
	
	//更新零售单
	public function  update_cashier (){
		$input=input ('post.');
		$info=$input['info'];
		//预先判断串号使用情况
		$tmp_arr=room::where (['id'=>['in',array_column ($info,'room'),'OR']])->select ()->toArray();//取出所有商品的仓储数据
		foreach ($info as $key=>$check_vo){
		    //先判断库存
		    $tmp_val=find_arr_key_val ($tmp_arr,'id',$check_vo['room']);//获取当前仓储ID的数据
			if ($check_vo['nums']>$tmp_val['nums']){
				return json (['state'=>'stock_error','row'=>$key+1]);
				exit ;
			}
			//判断串码是否存在,此处加判断高并发可能出问题,待验证
			if(array_key_exists('serial',$check_vo) && !empty($check_vo['serial'])){
			    //查找系统未销售串码
    			$tmp_serial=arrayChange (serial::where (['room'=>$check_vo['room'],'type'=>0])->field ('code')->select ()->toArray (),'code');
				$serial_arr=explode(',',$check_vo['serial']);
				foreach ($serial_arr as $arr_vo){
					if (!in_array($arr_vo,$tmp_serial)){
						return json (['state'=>'serial_error','row'=>$key+1]);
						exit ;
					}
				}
    		    $info[$key]['serial_type']=true;//是串码商品
			}else{
			    $info[$key]['serial_type']=false;//非串码商品
			}
		}
		$time=time();
		//兼容单据时间
		if(empty($input['time'])){
		    $class_sql['time']=strtotime(date('Y-m-d',time()));
		}else{
		    $class_sql['time']=strtotime($input['time']);
		}
		//兼容单据编号
		if(empty($input['number'])){
		    $class_sql['number']=get_number('cashier');
		}else{
		    $class_sql['number']=$input['number'];
		}
		$class_sql['total']=$input['total'];
		$class_sql['discount']=$input['discount'];
		$class_sql['integral']=$input['integral'];
		$class_sql['customer']=$input['customer'];
		$class_sql['user']=$input['user'];
		$class_sql['money']=$input['money'];
		$class_sql['account']=$input['account'];
		$class_sql['paytype']=$input['paytype'];
		$class_sql['payinfo']=$input['payinfo'];
		$class_sql['data']=$input['data'];
		$class_sql['type']=0;
		if (empty($input['id'])){
			//新增
			$class=cashierclass::create ($class_sql);
			set_number('cashier');
		}else {
			//更新
			$class_sql['id']=$input['id'];
			$class=cashierclass::update ($class_sql);
			cashierinfo::destroy (['pid'=>$input['id']]);//删除旧info数据
		}
		//详情
		foreach ($info as $vo){
			$info_sql=[];
			$info_sql['pid']=$class['id'];
			$info_sql['room']=$vo['room'];
			$tmp_warehouse=room::get (['id'=>$vo['room']]);
			$info_sql['warehouse']=$tmp_warehouse['warehouse']['ape'];
			$info_sql['goods']=$vo['goods'];
			//判断串号
			if ($vo['serial_type'] && array_key_exists('serial',$vo)){
				$info_sql['serial']=$vo['serial'];
			}
			$info_sql['nums']=$vo['nums'];
			$info_sql['price']=$vo['price'];
			$info_sql['discount']=$vo['discount'];
			$info_sql['total']=$vo['total'];
			if (array_key_exists('data',$vo)){
				$info_sql['data']=$vo['data'];
			}
			$info_sql['timemark']=0;//时间标识
			cashierinfo::create ($info_sql);
		}
		//判断自动审核
		$sys=sys::all();
		if(empty($sys['1']['info']['auditing'])){
		    $this->auditing_cashier($class['id'],true);
		}
		return json (['state'=>'success','id'=>$class['id']]);
	}
	//审核零售单
	public function auditing_cashier($id,$auth=false){
		//兼容自动审核
		if (empty($id)){
			$id=input ('post.id');
		}
		$cashierclass=cashierclass::get ($id);
		$cashierinfo=cashierinfo::where (['pid'=>$id])->select ()->toArray ();
		//读取info
		if (empty($cashierclass['type']['ape'])){
			//审核
			//预先判断库存以及串码使用情况
			foreach ($cashierinfo as $key=>$check_vo){
				//判断库存
				if ($check_vo['nums']>$check_vo['room']['info']['nums']){
					if ($auth){
						push_log ('自动审核零售单-'.$cashierclass['number'].'失败，原因：商品库存不足');
						exit ;
					}else {
						return json (['state'=>'stock_error','row'=>$key+1]);
						exit ;
					}
				}
				//判断串号是否使用
				if (!empty($check_vo['serial'])){
					$tmp_serial_arr=explode(',',$check_vo['serial']);
					//判断当前商品串码个数与串码表中这些串码(状态未销售)的个数是否相同
					if (count($tmp_serial_arr)!==serial::where (['code'=>['in',$tmp_serial_arr,'OR'],'type'=>0])->count()){
						if ($auth){
							//自动审核
							push_log ('自动审核零售单-'.$cashierclass['number'].'失败，原因：商品串码已销售');
						    exit ;
						}else {
							//手动审核
							return json (['state'=>'serial_error','row'=>$key+1]);
							exit ;
						}
					}
				}
			}
			$timemark=time();//时间标识
			foreach ($cashierinfo as $cashierinfo_vo){
			    
				//循环保存数据
				room::update([
				    'id'=>$cashierinfo_vo['room']['ape'],
				    'nums'=>$cashierinfo_vo['room']['info']['nums']-$cashierinfo_vo['nums'],
				    'timemark'=>$timemark//时间标识
				]);//更新仓储信息
				//新增仓储详情
				$roominfo_sql['pid']=$cashierinfo_vo['room']['ape'];
				$roominfo_sql['type']=9;
				$roominfo_sql['class']=$id;
				$roominfo_sql['info']=$cashierinfo_vo['id'];
				$roominfo_sql['nums']=$cashierinfo_vo['nums'];
				$roominfo_sql['timemark']=$timemark;//时间标识
				$roominfo_sql['oldtimemark']=$cashierinfo_vo['room']['info']['timemark'];//旧时间标识
				roominfo::create ($roominfo_sql);
				cashierinfo::update(['id'=>$cashierinfo_vo['id'],'timemark'=>$timemark]);//更新详情
				//判断批次
				
				if (!empty($cashierinfo_vo['serial'])){
				    
					//串号存在
					$tmp_serial=explode(',',$cashierinfo_vo['serial']);
					foreach ($tmp_serial as $tmp_serial_vo){
					    $serial_info=serial::get(['code'=>$tmp_serial_vo]);//获取串号详情
					    serial::update(['id'=>$serial_info['id'],'type'=>1,'timemark'=>$timemark]);//修改串号状态
					    serialinfo::create ([
					        'pid'=>$serial_info['id'],
					        'type'=>8,
					        'class'=>$id,
					        'oldroom'=>$serial_info['room'],
					        'timemark'=>$timemark,
					        'oldtimemark'=>$serial_info['timemark']
					    ]);//增加串号使用详情
					}
				}
			}
			//判断支付方式
    		if(empty($cashierclass['paytype'])){
    		    //默认支付
    		    account::where (['id'=>$cashierclass['account']["ape"]])->setInc ('balance',$cashierclass['money']);//操作资金-增
    			accountinfo::create (['pid'=>$cashierclass['account']["ape"],'set'=>1,'money'=>$cashierclass['money'],'type'=>9,'time'=>$timemark,'user'=>Session ('is_user_id'),'class'=>$cashierclass['id'],'number'=>$cashierclass['number'],'only'=>0]);
    		}else{
    		    //组合支付
    		    $tmp_payinfo=json_decode($cashierclass['payinfo'],true);
    		    foreach ($tmp_payinfo as $payinfo_vo) {
    		        account::where (['id'=>$payinfo_vo['account']])->setInc ('balance',$payinfo_vo['money']);//操作资金-增
    			    accountinfo::create (['pid'=>$payinfo_vo['account'],'set'=>1,'money'=>$payinfo_vo['money'],'type'=>9,'time'=>$timemark,'user'=>Session ('is_user_id'),'class'=>$cashierclass['id'],'number'=>$cashierclass['number'],'only'=>0]);
    		    }
    		}
    		
			//更新class审核状态
			cashierclass::update (['id'=>$id,'type'=>1,'auditinguser'=>Session ('is_user_id'),'auditingtime'=>$timemark]);
			//处理客户积分
			
    		if(!empty($cashierclass['integral'])){
    		    customer::where (['id'=>$cashierclass['customer']['ape']])->setInc ('integral',$cashierclass['integral']);//操作积分-增加
    		    customerinfo::create(['pid'=>$cashierclass['customer']['ape'],'set'=>1,'integral'=>$cashierclass['integral'],'type'=>1,'class'=>$cashierclass['id'],'number'=>$cashierclass['number'],'time'=>$timemark]);
    		}
    		
			set_summary('cashierclass',$id,true);//更新Summary
			if ($auth){
				push_log ('自动审核零售单-'.$cashierclass['number']);
			}else {
				//手动
				push_log ('审核零售单-'.$cashierclass['number']);
			}
		}else {
			//反审核
    		//判断逆操作以及串码状态
    		foreach ($cashierinfo as $key=>$check_vo){
    			//判断逆操作
    			//判断当前详情的时间标识与仓储ID中的时间标识是否相同
    			if($check_vo['timemark']!==$check_vo['room']['info']['timemark']){
    			    return json (['state'=>'set_error','row'=>$key+1]);
                    exit;
    			}else{
    			    //开始判断当前时间标识是否存在后续操作
    			    if(roominfo::get([
            	        'pid'=>$check_vo['room']['ape'],
            	        'timemark'=>['gt',$check_vo['timemark']]
            	     ])){
            	        return json (['state'=>'set_error','row'=>$key+1]);
                        exit;
            	    }
    			}
    		}
    		foreach ($cashierinfo as $cashierinfo_vo){
    			room::where (['id'=>$cashierinfo_vo['room']['ape']])->setInc('nums',$cashierinfo_vo['nums']);//增加对应库存数量
    			$room_info=roominfo::get([
    			    'pid'=>$cashierinfo_vo['room']['ape'],
    			    'type'=>9,
    			    'info'=>$cashierinfo_vo['id']
			    ]);//获取仓储详情
    			room::update([
    			    'id'=>$cashierinfo_vo['room']['ape'],
    			    'timemark'=>$room_info['oldtimemark']
			    ]);//还原旧时间标识
    			cashierinfo::update (['id'=>$cashierinfo_vo['id'],'timemark'=>0]);//详情还原时间标识
    			if(!empty($cashierinfo_vo['serial'])){
    			    $serial_arr=serial::where (['code'=>['in',explode(',',$cashierinfo_vo['serial']),'OR']])->select();//获取串号详情
    			    foreach ($serial_arr as $serial_vo) {
    			        $serial_info=serialinfo::get(['pid'=>$serial_vo['id'],'type'=>8,'timemark'=>$serial_vo['timemark']]);//获取当前时间标识的串码详情
    			        serial::update([
    			            'id'=>$serial_vo['id'],
    			            'room'=>$serial_info['oldroom'],
    			            'type'=>0,
    			            'timemark'=>$serial_info['oldtimemark']
    			        ]);//还原旧仓储ID|设置状态
    			        serialinfo::destroy(['id'=>$serial_info['id']]);//删除串码详情
    			    }
    			}
    		}
    		roominfo::destroy (['type'=>9,'class'=>$id]);//删除仓储详情
    		cashierclass::update (['id'=>$id,'type'=>0,'auditinguser'=>0,'auditingtime'=>0]);//复原class审核状态
    		//判断支付方式
    		if(empty($cashierclass['paytype'])){
    		    //默认支付
    		    account::where (['id'=>$cashierclass['account']['ape']])->setDec ('balance',$cashierclass['money']);//操作资金-减少
    		}else{
    		    //组合支付
    		    $tmp_payinfo=json_decode($cashierclass['payinfo'],true);
    		    foreach ($tmp_payinfo as $payinfo_vo) {
    		        account::where (['id'=>$payinfo_vo['account']])->setDec ('balance',$payinfo_vo['money']);//操作资金-减少
    		    }
    		}
    		accountinfo::destroy (['type'=>9,'class'=>$id]);//删除资金详情
    		//处理客户积分
    		if(!empty($cashierclass['integral'])){
    		    customer::where (['id'=>$cashierclass['customer']['ape']])->setDec ('integral',$cashierclass['integral']);//操作积分-减少
    		    customerinfo::destroy (['type'=>1,'class'=>$id]);//删除积分详情
    		}
    		set_summary('cashierclass',$id,false);//更新Summary
    		push_log ('反审核零售单-'.$cashierclass['number']);
		}
		return json (['state'=>'success']);
	}
	//删除零售销货单
	public function  del_cashier(){
		$id=input ('post.id');
		$cashierclass=cashierclass::where (['id'=>$id])->find ();
		if (empty($cashierclass['type']['ape'])){
			//未审核可删除
			cashierclass::destroy (['id'=>$id]);
			//删除class
			cashierinfo::destroy (['pid'=>$id]);
			//删除info
		}
		push_log ('删除零售单-'.$cashierclass['number']);
		return json ('success');
	}
	//保存零售退货单
	public function  save_recashier (){
		$input=input ('post.');
		$info=$input['info'];
		//预先判断串号使用情况
		foreach ($info as $key=>$check_vo){
			//判断串码是否存在,此处加判断高并发可能出问题,待验证
			if(array_key_exists('serial',$check_vo) && !empty($check_vo['serial'])){
			    //查找系统已销售串码
    			$tmp_serial=arrayChange (serial::where (['room'=>$check_vo['room'],'type'=>1])->field ('code')->select ()->toArray (),'code');
				$serial_arr=explode(',',$check_vo['serial']);
				foreach ($serial_arr as $arr_vo){
					if (!in_array($arr_vo,$tmp_serial)){
						return json (['state'=>'serial_error','row'=>$key+1]);
						exit ;
					}
				}
    		    $info[$key]['serial_type']=true;//是串码商品
			}else{
			    $info[$key]['serial_type']=false;//非串码商品
			}
		}
		$class_sql['customer']=$input['customer'];
		$class_sql['time']=$input['time']; 
		$class_sql['number']=$input['number'];
		$class_sql['total']=$input['total'];
		$class_sql['money']=$input['money'];
		$class_sql['user']=$input['user'];
		$class_sql['account']=$input['account'];
		$class_sql['integral']=$input['integral'];
		$class_sql['data']=$input['data'];
		if (!isset($input['id'])){
			//新增
			$class=recashierclass::create ($class_sql);
			set_number('recashier');
		}else {
			//更新
			$class_sql['id']=$input['id'];
			$class=recashierclass::update ($class_sql);
			recashierinfo::destroy (['pid'=>$input['id']]);//删除旧info数据
		}
		//详情
		foreach ($info as $vo){
			$info_sql=[];
			$info_sql['pid']=$class['id'];
			$info_sql['room']=$vo['room'];
			$tmp_warehouse=room::get (['id'=>$vo['room']]);
			$info_sql['warehouse']=$tmp_warehouse['warehouse']['ape'];
			$info_sql['goods']=$vo['goods'];
			//判断串号
			if (array_key_exists('serial',$vo) && !empty($vo['serial'])){
				$info_sql['serial']=$vo['serial'];
			}
			$info_sql['nums']=$vo['nums'];
			$info_sql['price']=$vo['price'];
			$info_sql['total']=$vo['total'];
			if (array_key_exists('data',$vo)){
				$info_sql['data']=$vo['data'];
			}
			$info_sql['timemark']=0;//时间标识
			recashierinfo::create ($info_sql);
		}
		push_log ('提交零售退货单-'.$input['number']);
		//判断自动审核
		$sys=sys::all();
		if(empty($sys['1']['info']['auditing'])){
		    $this->auditing_recashier($class['id'],true);
		}
		return json (['state'=>'success']);
	}
	//审核|反审核零售退货单
	public function  auditing_recashier ($id,$auth=false){
		//兼容自动审核
		if (empty($id)){
			$id=input ('post.id');
		}
		$recashierclass=recashierclass::get ($id);
		$recashierinfo=recashierinfo::where (['pid'=>$id])->select ()->toArray ();
		//读取info
		if (empty($recashierclass['type']['ape'])){
			//审核
			//预先判断库存以及串码使用情况
			foreach ($recashierinfo as $key=>$check_vo){
				//判断串号是否存在未销售
				if (!empty($check_vo['serial'])){
					$tmp_serial_arr=explode(',',$check_vo['serial']);
					//判断当前商品串码个数与串码表中这些串码(状态未销售)的个数是否相同
					if (count($tmp_serial_arr)!==serial::where (['code'=>['in',$tmp_serial_arr,'OR'],'type'=>1])->count()){
						if ($auth){
							//自动审核
							push_log ('自动审核销货退货单-'.$purchaseclass['number'].'失败，原因：商品串码未销售');
						    exit ;
						}else {
							//手动审核
							return json (['state'=>'serial_error','row'=>$key+1]);
							exit ;
						}
					}
				}
			}
			$timemark=time();//时间标识
			foreach ($recashierinfo as $recashierinfo_vo){
				//循环保存数据
				room::update([
				    'id'=>$recashierinfo_vo['room']['ape'],
				    'nums'=>$recashierinfo_vo['room']['info']['nums']+$recashierinfo_vo['nums'],
				    'timemark'=>$timemark//时间标识
				]);//更新仓储信息
				//新增仓储详情
				$roominfo_sql['pid']=$recashierinfo_vo['room']['ape'];
				$roominfo_sql['type']=10;
				$roominfo_sql['class']=$id;
				$roominfo_sql['info']=$recashierinfo_vo['id'];
				$roominfo_sql['nums']=$recashierinfo_vo['nums'];
				$roominfo_sql['timemark']=$timemark;//时间标识
				$roominfo_sql['oldtimemark']=$recashierinfo_vo['room']['info']['timemark'];//旧时间标识
				roominfo::create ($roominfo_sql);
				recashierinfo::update(['id'=>$recashierinfo_vo['id'],'timemark'=>$timemark]);//更新详情时间标识
				if (!empty($recashierinfo_vo['serial'])){
					//串号存在
					$tmp_serial=explode(',',$recashierinfo_vo['serial']);
					foreach ($tmp_serial as $tmp_serial_vo){
					    $serial_info=serial::get(['code'=>$tmp_serial_vo]);//获取串号详情
					    serial::update(['id'=>$serial_info['id'],'type'=>0,'timemark'=>$timemark]);//修改串号状态以及时间标识
					    serialinfo::create ([
					        'pid'=>$serial_info['id'],
					        'type'=>9,
					        'class'=>$id,
					        'oldroom'=>$serial_info['room'],
					        'timemark'=>$timemark,
					        'oldtimemark'=>$serial_info['timemark']
					    ]);//增加串号使用详情
					}
				}
			}
			//更新class审核状态
			recashierclass::update (['id'=>$id,'type'=>1,'auditinguser'=>Session ('is_user_id'),'auditingtime'=>time()]);
			//资金非零
			if (!empty($recashierclass['money'])){
				//增加对账单
				account::where (['id'=>$recashierclass['account']['ape']])->setDec ('balance',$recashierclass['money']);//操作资金-减
				accountinfo::create (['pid'=>$recashierclass['account']['ape'],'set'=>0,'money'=>$recashierclass['money'],'type'=>10,'time'=>time(),'user'=>Session ('is_user_id'),'class'=>$id,'number'=>$recashierclass['number'],'only'=>0]);
				//资金操作-详情
			}
			//更新客户积分
			if(!empty($recashierclass['integral'])){
			    customer::where (['id'=>$recashierclass['customer']['ape']])->setDec ('integral',$recashierclass['integral']);//操作积分-减少
		        customerinfo::create(['pid'=>$recashierclass['customer']['ape'],'set'=>0,'integral'=>$recashierclass['integral'],'type'=>2,'class'=>$recashierclass['id'],'number'=>$recashierclass['number'],'time'=>$timemark]);
			}
			set_summary('recashierclass',$id,true);//更新Summary
			if ($auth){
				push_log ('自动审核零售退货单-'.$recashierclass['number']);
			}else {
				//手动
				push_log ('审核零售退货单-'.$recashierclass['number']);
			}
		}else {
			//反审核
			//判断逆操作以及串码状态
			foreach ($recashierinfo as $key=>$check_vo){
				//判断逆操作
				//判断当前详情的时间标识与仓储ID中的时间标识是否相同
				if($check_vo['timemark']!==$check_vo['room']['info']['timemark']){
				    return json (['state'=>'set_error','row'=>$key+1]);
                    exit;
				}else{
				    //开始判断当前时间标识是否存在后续操作
				    if(roominfo::get([
            	        'pid'=>$check_vo['room']['ape'],
            	        'timemark'=>['gt',$check_vo['timemark']]
            	     ])){
            	        return json (['state'=>'set_error','row'=>$key+1]);
                        exit;
            	    }
				}
			}
			foreach ($recashierinfo as $recashierinfo_vo){
				room::where (['id'=>$recashierinfo_vo['room']['ape']])->setDec('nums',$recashierinfo_vo['nums']);//减少对应库存数量
				$room_info=roominfo::get([
				    'pid'=>$recashierinfo_vo['room']['ape'],
				    'type'=>10,
				    'info'=>$recashierinfo_vo['id']
				    ]);//获取仓储详情
				room::update([
				    'id'=>$recashierinfo_vo['room']['ape'],
				    'timemark'=>$room_info['oldtimemark']
				    ]);//还原旧时间标识
				recashierinfo::update (['id'=>$recashierinfo_vo['id'],'timemark'=>0]);//详情还原时间标识
				if(!empty($recashierinfo_vo['serial'])){
				    $serial_arr=serial::where (['code'=>['in',explode(',',$recashierinfo_vo['serial']),'OR']])->select();//获取串号详情
				    foreach ($serial_arr as $serial_vo) {
				        $serial_info=serialinfo::get(['pid'=>$serial_vo['id'],'type'=>9,'timemark'=>$serial_vo['timemark']]);//获取当前时间标识的串码详情
				        serial::update([
				            'id'=>$serial_vo['id'],
				            'room'=>$serial_info['oldroom'],
				            'type'=>1,
				            'timemark'=>$serial_info['oldtimemark']
				        ]);//还原旧仓储ID|设置状态
				        serialinfo::destroy(['id'=>$serial_info['id']]);//删除串码详情
				    }
				}
			}
			roominfo::destroy (['type'=>10,'class'=>$id]);//删除仓储详情
			recashierclass::update (['id'=>$id,'type'=>0,'auditinguser'=>0,'auditingtime'=>0]);
			//复原class审核状态和对账单状态
			//资金非零
			if (!empty($recashierclass['money'])){
				//复原资金
				account::where (['id'=>$recashierclass['account']['ape']])->setInc ('balance',$recashierclass['money']);//增加金额
				accountinfo::destroy (['type'=>10,'class'=>$id]);//删除资金详情
			}
			//处理客户积分
    		if(!empty($recashierclass['integral'])){
    		    customer::where (['id'=>$recashierclass['customer']['ape']])->setInc ('integral',$recashierclass['integral']);//操作积分-增加
    		    customerinfo::destroy (['type'=>2,'class'=>$recashierclass['id']]);//删除积分详情
    		}
    		set_summary('recashierclass',$id,false);//更新Summary
			push_log ('反审核零售退货单-'.$recashierclass['number']);
		}
		return json (['state'=>'success']);
	}
	//删除零售退货单
	public function  del_recashier(){
		$id=input ('post.id');
		$recashierclass=recashierclass::where (['id'=>$id])->find ();
		if (empty($recashierclass['type']['ape'])){
			//未审核可删除
			recashierclass::destroy (['id'=>$id]);
			//删除class
			recashierinfo::destroy (['pid'=>$id]);
			//删除info
		}
		push_log ('删除零售退货单-'.$recashierclass['number']);
		return json ('success');
	}
	//保存数据授权
	public function save_user_auth(){
	    $info=input('post.');
	    $sql['id']=$info['id'];
	    $sql['auth']=$info['auth'];
	    //自动填充当前用户
	    if(!empty($sql['auth']['user'])){
	        if(!in_array(Session('is_user_id'),$sql['auth']['user'])){
	            array_push($sql['auth']['user'],$info['id']);
	        }
	    }
	    user::update($sql);
	    return json ('success');
	}
	//保存数据授权
	public function save_user_root(){
	    $info=input('post.');
	    $sql['id']=$info['id'];
	    $sql['root']=$info['root'];
	    user::update($sql);
	    return json ('success');
	}
	//保存常用功能
	public function  save_often (){
	    $info=input('post.');
	    often::destroy(['id'=>['gt',0]]);
	    if(!empty($info['info'])){
	        foreach ($info['info'] as $vo) {
	            often::create($vo);
	        }
	    }
	    return json ('success');
	}
	//客户信息
    public function customer_list(){
        $sql['name'] = ['like','%'.input('post.name').'%'];
        $sql['number'] = ['like','%'.input('post.number').'%'];
        $sql['tel'] = ['like','%'.input('post.tel').'%'];
        $sql['data'] = ['like','%'.input('post.data').'%'];
        $count = customer::where ($sql)->count();//获取总条数
        $arr = customer::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json($re);
    }
    //资金账户信息
    public function account_list(){
        $sql['name'] = ['like','%'.input('post.name').'%'];
        $sql['data'] = ['like','%'.input('post.data').'%'];
        $count = account::where ($sql)->count();//获取总条数
        $arr = account::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //供应商信息
    public function supplier_list(){
        $sql['name'] = ['like','%'.input('post.name').'%'];
        $sql['number'] = ['like','%'.input('post.number').'%'];
        $sql['contacts'] = ['like','%'.input('post.contacts').'%'];
        $sql['tel'] = ['like','%'.input('post.tel').'%'];
        $sql['data'] = ['like','%'.input('post.data').'%'];
        $count = supplier::where ($sql)->count();//获取总条数
        $arr = supplier::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //商品信息
    public function goods_list(){
        $sql['name|py'] = ['like','%'.input('post.name').'%'];
        $sql['number'] = ['like','%'.input('post.number').'%'];
        $sql['spec'] = ['like','%'.input('post.spec').'%'];
        $sql['location'] = ['like','%'.input('post.location').'%'];
        $sql['stocktip'] = ['like','%'.input('post.stocktip').'%'];
        $sql['integral'] = ['like','%'.input('post.integral').'%'];
        $sql['retail_name'] = ['like','%'.input('post.retail_name').'%'];
        if(!empty(input('post.class'))){
            $sql['class'] = ['in',goodsclass_more_arr(input('post.class')),'OR'];
        }
        //判断默认仓库
        if(!empty(input('post.warehouse'))){
            $sql['warehouse'] = input('post.warehouse');
        }
        //判断默认品牌
        if(!empty(input('post.brand'))){
            $sql['brand'] = input('post.brand');
        }
        //判断商品单位
        if(!empty(input('post.unit'))){
            $sql['unit'] = input('post.unit');
        }
        $sql['code'] = ['like','%'.input('post.code').'%'];
        $count = goods::where ($sql)->count();//获取总条数
        $arr = goods::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //仓库信息
    public function warehouse_list(){
        $sql['name'] = ['like','%'.input('post.name').'%'];
        $sql['number'] = ['like','%'.input('post.number').'%'];
        $sql['contacts'] = ['like','%'.input('post.contacts').'%'];
        $sql['tel'] = ['like','%'.input('post.tel').'%'];
        $sql['data'] = ['like','%'.input('post.data').'%'];
        $count = warehouse::where ($sql)->count();//获取总条数
        $arr = warehouse::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //计量单位
    public function unit_list(){
        $sql['name'] = ['like','%'.input('post.name').'%'];
        $sql['number'] = ['like','%'.input('post.number').'%'];
        $sql['data'] = ['like','%'.input('post.data').'%'];
        $count = unit::where ($sql)->count();//获取总条数
        $arr = unit::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }    
    //品牌管理
    public function brand_list(){
        $sql['name'] = ['like','%'.input('post.name').'%'];
        $sql['number'] = ['like','%'.input('post.number').'%'];
        $sql['data'] = ['like','%'.input('post.data').'%'];
        $count = brand::where ($sql)->count();//获取总条数
        $arr = brand::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }    
    //条码管理
    public function code_list(){
        $sql['name'] = ['like','%'.input('post.name').'%'];
        $sql['code'] = ['like','%'.input('post.code').'%'];
        $sql['data'] = ['like','%'.input('post.data').'%'];
        //类型
        if(!empty(input('post.type'))){
            $sql['type']=input('post.type');
        }
        $count = code::where ($sql)->count();//获取总条数
        $arr = code::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }    
    //辅助属性
    public function attribute_list(){
        $sql['pid']=0;
        $sql['name'] = ['like','%'.input('post.name').'%'];
        $sql['data'] = ['like','%'.input('post.data').'%'];
        $count = attribute::where ($sql)->count();//获取总条数
        $arr = attribute::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }    
    //职员管理
    public function user_list(){
        $sql['name'] = ['like','%'.input('post.name').'%'];
        $sql['user'] = ['like','%'.input('post.number').'%'];
        $sql['data'] = ['like','%'.input('post.data').'%'];
        $sql['type']=array('neq',1);//排除超级管理员
        $count = user::where ($sql)->count();//获取总条数
        $arr = user::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //权限设置
    public function root_list(){
        $sql['name'] = ['like','%'.input('post.name').'%'];
        $sql['user'] = ['like','%'.input('post.user').'%'];
        $sql['type'] = ['neq',1];//排除超级管理员
        $count = user::where ($sql)->count();//获取总条数
        $arr = user::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }    
    //数据授权
    public function auth_list(){
        $sql['name'] = ['like','%'.input('post.name').'%'];
        $sql['user'] = ['like','%'.input('post.user').'%'];
        $sql['type'] = ['neq',1];//排除超级管理员
        $count = user::where ($sql)->count();//获取总条数
        $arr = user::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    } 
    //购货单报表
    public function purchaseclass_list(){
        //按照名称搜索
        if(!empty(input('post.goods'))){
            $goods_sql['name|py']=['like','%'.input('post.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $purchaseinfo_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('post.warehouse'))){
            $purchaseinfo_sql['warehouse']=input('post.warehouse');
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
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.supplier'))){
            $sql['supplier']=input('post.supplier');
        }
        //按照制单人搜索
        if(!empty(input('post.user'))){
            $sql['user']=input('post.user');
        }
        //按照结算账户搜索
        if(!empty(input('post.account'))){
            $sql['account']=input('post.account');
        }
        //按照审核状态搜索
        if(!empty(input('post.type'))){
            $sql['type']=input('post.type')-1;
        }
        $sql['number'] = ['like','%'.input('post.number').'%'];
        $sql['data']=['like','%'.input('post.data').'%'];
        $count = purchaseclass::where ($sql)->count();//获取总条数
        $arr = purchaseclass::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //购货退货单报表
    public function repurchaseclass_list(){
        //按照名称搜索
        if(!empty(input('post.goods'))){
            $goods_sql['name|py']=['like','%'.input('post.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $repurchaseinfo_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('post.warehouse'))){
            $repurchaseinfo_sql['warehouse']=input('post.warehouse');
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
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.supplier'))){
            $sql['supplier']=input('post.supplier');
        }
        //按照制单人搜索
        if(!empty(input('post.user'))){
            $sql['user']=input('post.user');
        }
        //按照结算账户搜索
        if(!empty(input('post.account'))){
            $sql['account']=input('post.account');
        }
        //按照审核状态搜索
        if(!empty(input('post.type'))){
            $sql['type']=input('post.type')-1;
        }
        $sql['number'] = ['like','%'.input('post.number').'%'];
        $sql['data'] = ['like','%'.input('post.data').'%'];
        $count = repurchaseclass::where ($sql)->count();//获取总条数
        $arr = repurchaseclass::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    } 
    //销货单报表
    public function sale_list(){
        //按照名称搜索
        if(!empty(input('post.goods'))){
            $goods_sql['name|py']=['like','%'.input('post.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $info_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('post.warehouse'))){
            $info_sql['warehouse']=input('post.warehouse');
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
        $class_sql['number']=['like','%'.input('post.number').'%'];
        $class_sql['data']=['like','%'.input('post.data').'%'];
        //按照客户搜索
        if(!empty(input('post.customer'))){
            $class_sql['customer']=input('post.customer');
        }
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.user'))){
            $class_sql['user']=input('post.user');
        }
        //按照结算账户搜索
        if(!empty(input('post.account'))){
            $class_sql['account']=input('post.account');
        }
        //按照审核状态搜索
        if(!empty(input('post.type'))){
            $class_sql['type']=input('post.type')-1;
        }
        $count = saleclass::where ($class_sql)->count();//获取总条数
        $arr = saleclass::where($class_sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //销货退货单报表
    public function resale_list(){
        //按照名称搜索
        if(!empty(input('post.goods'))){
            $goods_sql['name|py']=['like','%'.input('post.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $resaleinfo_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('post.warehouse'))){
            $resaleinfo_sql['warehouse']=input('post.warehouse');
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
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.customer'))){
            $sql['customer']=input('post.customer');
        }
        //按照制单人搜索
        if(!empty(input('post.user'))){
            $sql['user']=input('post.user');
        }
        //按照结算账户搜索
        if(!empty(input('post.account'))){
            $sql['account']=input('post.account');
        }
        //按照审核状态搜索
        if(!empty(input('post.type'))){
            $sql['type']=input('post.type')-1;
        }
        $sql['number'] = ['like','%'.input('post.number').'%'];
        $sql['data'] = ['like','%'.input('post.data').'%'];
        $count = resaleclass::where ($sql)->count();//获取总条数
        $arr = resaleclass::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //零售单报表
    public function cashier_list(){
        //按照名称搜索
        if(!empty(input('post.goods'))){
            $goods_sql['name|py']=['like','%'.input('post.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $info_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('post.warehouse'))){
            $info_sql['warehouse']=input('post.warehouse');
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
        $class_sql['number']=['like','%'.input('post.number').'%'];
        $class_sql['data']=['like','%'.input('post.data').'%'];
        //按照客户搜索
        if(!empty(input('post.customer'))){
            $class_sql['customer']=input('post.customer');
        }
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.user'))){
            $class_sql['user']=input('post.user');
        }
        //按照结算账户搜索
        if(!empty(input('post.account'))){
            $class_sql['account']=input('post.account');
        }
        //按照审核状态搜索
        if(!empty(input('post.type'))){
            $class_sql['type']=input('post.type')-1;
        }
        $count = cashierclass::where ($class_sql)->count();//获取总条数
        $arr = cashierclass::where($class_sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //零售退货单报表
    public function recashier_list(){
        //按照名称搜索
        if(!empty(input('post.goods'))){
            $goods_sql['name|py']=['like','%'.input('post.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $recashierinfo_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('post.warehouse'))){
            $recashierinfo_sql['warehouse']=input('post.warehouse');
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
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.customer'))){
            $sql['customer']=input('post.customer');
        }
        //按照制单人搜索
        if(!empty(input('post.user'))){
            $sql['user']=input('post.user');
        }
        //按照结算账户搜索
        if(!empty(input('post.account'))){
            $sql['account']=input('post.account');
        }
        //按照审核状态搜索
        if(!empty(input('post.type'))){
            $sql['type']=input('post.type')-1;
        }
        $sql['number'] = ['like','%'.input('post.number').'%'];
        $sql['data'] = ['like','%'.input('post.data').'%'];
        $count = recashierclass::where ($sql)->count();//获取总条数
        $arr = recashierclass::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //调拨单报表
    public function allocation_list(){
        //按照名称搜索
        if(!empty(input('post.goods'))){
            $goods_sql['name|py']=['like','%'.input('post.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $info_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照调拨仓库搜索
        if(!empty(input('post.towarehouse'))){
            $info_sql['towarehouse']=input('post.towarehouse');
        }
        //按照所属仓库搜索
        if(!empty(input('post.warehouse'))){
            $info_sql['warehouse']=input('post.warehouse');
        }
        //info条件转class条件
        if(isset($info_sql)){
            $class_sql['id'] = ['in',arrayChange(allocationinfo::where($info_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
        }
        //按照编号搜索
        $class_sql['number']=['like','%'.input('post.number').'%'];
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.user'))){
            $class_sql['user']=input('post.user');
        }
        //按照审核状态搜索
        if(!empty(input('post.type'))){
            $class_sql['type']=input('post.type')-1;
        }
        $count = allocationclass::where ($class_sql)->count();//获取总条数
        $arr = allocationclass::where($class_sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //其他入库单报表
    public function otpurchase_list(){
        //按照名称搜索
        if(!empty(input('post.goods'))){
            $goods_sql['name|py']=['like','%'.input('post.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $info_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('post.warehouse'))){
            $info_sql['warehouse']=input('post.warehouse');
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
        $class_sql['number']=['like','%'.input('post.number').'%'];
        $class_sql['data']=['like','%'.input('post.data').'%'];
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.user'))){
            $class_sql['user']=input('post.user');
        }
        //按照审核状态搜索
        if(!empty(input('post.type'))){
            $class_sql['type']=input('post.type')-1;
        }
         //按照单据类型搜索
        if(!empty(input('post.pagetype'))){
            $class_sql['pagetype']=input('post.pagetype')-1;
        }
        $count = otpurchaseclass::where ($class_sql)->count();//获取总条数
        $arr = otpurchaseclass::where($class_sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //其他出库单报表
    public function otsale_list(){
        //按照名称搜索
        if(!empty(input('post.goods'))){
            $goods_sql['name|py']=['like','%'.input('post.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $info_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('post.warehouse'))){
            $info_sql['warehouse']=input('post.warehouse');
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
        $class_sql['number']=['like','%'.input('post.number').'%'];
        $class_sql['data']=['like','%'.input('post.data').'%'];
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.user'))){
            $class_sql['user']=input('post.user');
        }
        //按照审核状态搜索
        if(!empty(input('post.type'))){
            $class_sql['type']=input('post.type')-1;
        }
         //按照单据类型搜索
        if(!empty(input('post.pagetype'))){
            $class_sql['pagetype']=input('post.pagetype')-1;
        }
        $count = otsaleclass::where ($class_sql)->count();//获取总条数
        $arr = otsaleclass::where($class_sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //收款单报表
    public function gather_list(){
        //按照结算账户搜索
        if(!empty(input('post.account'))){
            $info_sql['account']=input('post.account');
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
        $class_sql['number']=['like','%'.input('post.number').'%'];
        //按照备注信息搜索
        $class_sql['data']=['like','%'.input('post.data').'%'];
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.user'))){
            $class_sql['user']=input('post.user');
        }
        //按照收款客户搜索
        if(!empty(input('post.customer'))){
            $class_sql['customer']=input('post.customer');
        }
        //按照审核状态搜索
        if(!empty(input('post.type'))){
            $class_sql['type']=input('post.type')-1;
        }
        $count = gatherclass::where ($class_sql)->count();//获取总条数
        $arr = gatherclass::where($class_sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        foreach ($arr as $key=>$arr_vo) {
            //补充单据金额
            $arr[$key]['money']=gatherinfo::where(['pid'=>$arr_vo['id']])->sum('total');
        }
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }  
    //其他收入单报表
    public function otgather_list(){
        //按照结算账户搜索
        if(!empty(input('post.account'))){
            $info_sql['account']=input('post.account');
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
        $class_sql['number']=['like','%'.input('post.number').'%'];
        $class_sql['data']=['like','%'.input('post.data').'%'];
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.user'))){
            $class_sql['user']=input('post.user');
        }
        //按照审核状态搜索
        if(!empty(input('post.type'))){
            $class_sql['type']=input('post.type')-1;
        }
        $count = otgatherclass::where ($class_sql)->count();//获取总条数
        $arr = otgatherclass::where($class_sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        foreach ($arr as $key=>$arr_vo) {
            //补充单据金额
            $arr[$key]['money']=otgatherinfo::where(['pid'=>$arr_vo['id']])->sum('total');
        }
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //付款单报表
    public function payment_list(){
        //按照结算账户搜索
        if(!empty(input('post.account'))){
            $info_sql['account']=input('post.account');
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
        $class_sql['number']=['like','%'.input('post.number').'%'];
        //按照备注信息搜索
        $class_sql['data']=['like','%'.input('post.data').'%'];
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.user'))){
            $class_sql['user']=input('post.user');
        }
        //按照收款客户搜索
        if(!empty(input('post.supplier'))){
            $class_sql['supplier']=input('post.supplier');
        }
        //按照审核状态搜索
        if(!empty(input('post.type'))){
            $class_sql['type']=input('post.type')-1;
        }
        $count = paymentclass::where ($class_sql)->count();//获取总条数
        $arr = paymentclass::where($class_sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        foreach ($arr as $key=>$arr_vo) {
            //补充单据金额
            $arr[$key]['money']=paymentinfo::where(['pid'=>$arr_vo['id']])->sum('total');
        }
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //其他支出单报表
    public function otpayment_list(){
        //按照结算账户搜索
        if(!empty(input('post.account'))){
            $info_sql['account']=input('post.account');
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
        $class_sql['number']=['like','%'.input('post.number').'%'];
        $class_sql['data']=['like','%'.input('post.data').'%'];
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.user'))){
            $class_sql['user']=input('post.user');
        }
        //按照审核状态搜索
        if(!empty(input('post.type'))){
            $class_sql['type']=input('post.type')-1;
        }
        $count = otpaymentclass::where ($class_sql)->count();//获取总条数
        $arr = otpaymentclass::where($class_sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        foreach ($arr as $key=>$arr_vo) {
            //补充单据金额
            $arr[$key]['money']=otpaymentinfo::where(['pid'=>$arr_vo['id']])->sum('total');
        }
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //购货对账单报表
    public function purchasebill_list(){
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.user'))){
            $sql['user'] = input('post.user');
        }
        //判断供应商
        if(!empty(input('post.supplier'))){
            $sql['supplier'] = input('post.supplier');
        }
        //判断结算账户
        if(!empty(input('post.account'))){
            $sql['account'] = input('post.account');
        }
        //判断付款状态
        if(empty(input('post.billtype'))){
            $sql['billtype']=['neq',-1];
        }else{
            $sql['billtype']=[
                ['neq',-1],
                ['eq',input('post.billtype')-1]
            ];
        }
        $sql['number'] = ['like','%'.input('post.number').'%'];
        $count = purchaseclass::where ($sql)->count();//获取总条数
        $arr = purchaseclass::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }    
    //购货退货对账单报表
    public function repurchasebill_list(){
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.user'))){
            $sql['user'] = input('post.user');
        }
        //判断购买客户
        if(!empty(input('post.supplier'))){
            $sql['supplier'] = input('post.supplier');
        }
        //判断结算账户
        if(!empty(input('post.account'))){
            $sql['account'] = input('post.account');
        }
        //判断付款状态
        if(empty(input('post.billtype'))){
            $sql['billtype']=['neq',-1];
        }else{
            $sql['billtype']=[
                ['neq',-1],
                ['eq',input('post.billtype')-1]
            ];
        }
        $sql['number'] = ['like','%'.input('post.number').'%'];
        $count = repurchaseclass::where ($sql)->count();//获取总条数
        $arr = repurchaseclass::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }    
    //销货对账单报表
    public function salebill_list(){
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.user'))){
            $sql['user'] = input('post.user');
        }
        //判断购买客户
        if(!empty(input('post.customer'))){
            $sql['customer'] = input('post.customer');
        }
        //判断结算账户
        if(!empty(input('post.account'))){
            $sql['account'] = input('post.account');
        }
        //判断付款状态
        if(empty(input('post.billtype'))){
            $sql['billtype']=['neq',-1];
        }else{
            $sql['billtype']=[
                ['neq',-1],
                ['eq',input('post.billtype')-1]
            ];
        }
        $sql['number'] = ['like','%'.input('post.number').'%'];
        $count = saleclass::where ($sql)->count();//获取总条数
        $arr = saleclass::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //销货退货对账单报表
    public function resalebill_list(){
        ///按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.user'))){
            $sql['user'] = input('post.user');
        }
        //判断供应商
        if(!empty(input('post.customer'))){
            $sql['customer'] = input('post.customer');
        }
        //判断结算账户
        if(!empty(input('post.account'))){
            $sql['account'] = input('post.account');
        }
        //判断付款状态
        if(empty(input('post.billtype'))){
            $sql['billtype']=['neq',-1];
        }else{
            $sql['billtype']=[
                ['neq',-1],
                ['eq',input('post.billtype')-1]
            ];
        }
        $sql['number'] = ['like','%'.input('post.number').'%'];
        $count = resaleclass::where ($sql)->count();//获取总条数
        $arr = resaleclass::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //账户资金流向详情表
    public function accountform_list(){
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.user'))){
            $sql['user'] = input('post.user');
        }
        
        //判断资金操作
        if(!empty(input('post.set'))){
            $sql['set']=input('post.set')-1;
        }
        //判断单据类型
        if(!empty(input('post.type'))){
            $sql['type']=input('post.type');
        }
        $sql['pid']=input('post.id');
        $count = accountinfo::where ($sql)->count();//获取总条数
        $arr = accountinfo::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }    
    //客户积分详情表
    public function customerform_list(){
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.set'))){
            $sql['set']=input('post.set')-1;
        }
        //判断单据类型
        if(!empty(input('post.type'))){
            $sql['type']=input('post.type');
        }
        $sql['pid']=input('post.id');
        $count = customerinfo::where ($sql)->count();//获取总条数
        $arr = customerinfo::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	
    	return json ($re);
    }
    //商品详情表
    public function roomform_list(){
        $sql['pid']=input('post.id');
        //判断单据类型
        if(!empty(input('post.type'))){
            $sql['type']=input('post.type');
        }
        //按照时间搜索
        $start_time=strtotime(input('post.start_time'));//开始时间
        $end_time=strtotime(input('post.end_time'));//结束时间
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
            $count = roominfo::where(['id'=>['in',$in_sql,'OR']])->count();//获取总条数
            $arr = roominfo::where(['id'=>['in',$in_sql,'OR']])->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        }else{
            $count = roominfo::where ($sql)->count();//获取总条数
            $arr = roominfo::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        }
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }    
    //操作日志
    public function log_list(){
        $sql['text'] = ['like','%'.input('post.text').'%'];
        //按照时间搜索(最后访问时间)
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.user'))){
            $sql['user'] = input('post.user');
        }
        $count = log::where ($sql)->count();//获取总条数
        $arr = log::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //库存查询
    public function room_list(){
        if(!empty(input('post.name'))){
            $goods_sql['name|py']=['like','%'.input('post.name').'%'];
        }
        if(!empty(input('post.number'))){
            $goods_sql['number']=['like','%'.input('post.number').'%'];
        }
        if(!empty(input('post.location'))){
            $goods_sql['location']=['like','%'.input('post.location').'%'];
        }
        if(!empty(input('post.spec'))){
            $goods_sql['spec'] = ['like','%'.input('post.spec').'%'];
        }
        //判断商品类型
        if(!empty(input('post.class'))){
            $goods_sql['class'] = ['in',goodsclass_more_arr(input('post.class')),'OR'];
        }
        //判断商品单位
        if(!empty(input('post.unit'))){
            $goods_sql['unit'] = input('post.unit');
        }
        //判断所属品牌
        if(!empty(input('post.brand'))){
            $goods_sql['brand'] = input('post.brand');
        }
        if(isset($goods_sql)){
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $sql['goods']=['in',$goods_arr,'OR'];
        }
        //判断所属仓库
        if(!empty(input('post.warehouse'))){
            $sql['warehouse'] = input('post.warehouse');
        }
        //判断零库存商品
        if(empty(input('post.eye'))){
            $sql['nums'] = ['neq',0];
        }
        if(isset($sql)){
            $count = room::where ($sql)->count();//获取总条数
            $arr = room::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        }else{
            $count = room::count();//获取总条数
            $arr = room::page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        }
        foreach ($arr as $key=>$vo) {
            $arr[$key]['serial']=implode('|', arrayChange(serial::where(['room'=>$vo['id'],'type'=>0])->field('code')->select()->toArray(),'code'));
            if(empty($vo['goods']['info']['imgs'])){
			    $arr[$key]['img']='/skin/images/main/none.png';
			}else{
			    $arr[$key]['img']=$vo['goods']['info']['imgs'][0];
			}
        }
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    } 
    //库存预警
    public function roomwarning_list(){
        if(!empty(input('post.name'))){
            $goods_sql['name|py']=['like','%'.input('post.name').'%'];
        }
        if(!empty(input('post.number'))){
            $goods_sql['number']=['like','%'.input('post.number').'%'];
        }
        if(!empty(input('post.location'))){
            $goods_sql['location']=['like','%'.input('post.location').'%'];
        }
        if(!empty(input('post.spec'))){
            $goods_sql['spec'] = ['like','%'.input('post.spec').'%'];
        }
        //判断商品类型
        if(!empty(input('post.class'))){
            $goods_sql['class'] = ['in',goodsclass_more_arr(input('post.class')),'OR'];
        }
        //判断商品单位
        if(!empty(input('post.unit'))){
            $goods_sql['unit'] = input('post.unit');
        }
        //判断所属品牌
        if(!empty(input('post.brand'))){
            $goods_sql['brand'] = input('post.brand');
        }
        if(isset($goods_sql)){
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $sql['goods']=['in',$goods_arr,'OR'];
        }
        //判断所属仓库
        if(!empty(input('post.warehouse'))){
            $sql['warehouse'] = input('post.warehouse');
        }
        if(isset($sql)){
            $list=room::where($sql)->select()->toArray();
        }else{
            $list=room::select()->toArray();
        }
        foreach ($list as $key=>$vo) {
            if(empty($vo['goods']['info']['imgs'])){
			    $list[$key]['img']='/skin/images/main/none.png';
			}else{
			    $list[$key]['img']=$vo['goods']['info']['imgs'][0];
			}
            $list[$key]['serial']=arrayChange(serial::where(['room'=>$vo['id'],'type'=>0])->field('code')->select()->toArray(),'code');
            if(empty($vo['attr']['ape'])){
                //取默认-预警阈值
                $list[$key]['stocktip']=$vo['goods']['info']['stocktip'];
            }else{
                //取辅助属性-预警阈值
                $attr=attr::where(['pid'=>$vo['goods']['ape'],'ape'=>$vo['attr']['ape'],'enable'=>1])->find();
                //兼容辅助属性被修改
                if($attr){
                    $list[$key]['stocktip']=$attr['stocktip'];
                }else{
                    $list[$key]['stocktip']=$vo['goods']['info']['stocktip'];
                }
            }
            //判断预警阈值
            if($vo['nums']>=$list[$key]['stocktip']){
                unset($list[$key]);
            }
        }
        $count =count($list);//获取总条数
        $tmp_page=input('post.page');
        $tmp_limit=input('post.limit');
        $arr = array_slice($list,$tmp_limit*($tmp_page-1),$tmp_limit);
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //往来单位欠款报表
    public function arrears_list(){
        //判断单位类型
        if(empty(input('post.type'))){
            //客户
            $list=[];
            $sql['name']=['like','%'.input('post.name').'%'];
            $sql['number']=['like','%'.input('post.number').'%'];
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
                array_push($list,$tmp);
            }
        }else{
            //供应商
            $list=[];
            $sql['name']=['like','%'.input('post.name').'%'];
            $sql['number']=['like','%'.input('post.number').'%'];
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
                array_push($list,$tmp);
            }
        }
        $count =count($list);//获取总条数
        $tmp_page=input('post.page');
        $tmp_limit=input('post.limit');
        $arr = array_slice($list,$tmp_limit*($tmp_page-1),$tmp_limit);
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //销售利润表
    public function profit_list(){
        //单据类型
        $type=input('post.type');
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
        $summary_sql['company']=summary_customer_sql(input('post.customer'));
        //制单人
        if(!empty(input('post.user'))){
            $summary_sql['user']=input('post.user');
        }
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        $count =count($list);//获取总条数
        $tmp_page=input('post.page');
        $tmp_limit=input('post.limit');
        $data = array_slice($list,$tmp_limit*($tmp_page-1),$tmp_limit);
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$data;
    	return json ($re);
    }
    //选择商品弹框数据(GOODS)
    public function choice_goods_info(){
        $re['brand_arr']=brand::field('id,name')->select();
        $re['unit_arr']=unit::field('id,name')->select();
        $re['goodsclass_arr']=goodsclass_arr(true,'全部分类');
        return json($re);
    }
    //选择商品弹框数据(ROOM)
    public function choice_room_info(){
        $re['brand_arr']=brand::field('id,name')->select();
        $re['goodsclass_arr']=goodsclass_arr(true,'全部分类');
        return json($re);
    }
    //商品管理页面数据
    public function ape_goods_info(){
        $re['brand_arr']=brand::field('id,name')->select();
        $re['goodsclass_arr']=goodsclass_arr(true,'全部分类');
        $re['supplier_arr']=supplier::field('id,name')->select();
        $re['unit_arr']=unit::field('id,name')->select();
        $re['warehouse_arr']=warehouse::field('id,name')->select();
        $re['attribute_arr']=attribute_arr();
        return json($re);
    }
    //数据授权页面数据
    public function ape_auth_info(){
        $re['customer_arr']=customer::where(['noauth'=>'ape'])->field('id,name')->select();
        $re['supplier_arr']=supplier::where(['noauth'=>'ape'])->field('id,name')->select();
        $re['warehouse_arr']=warehouse::where(['noauth'=>'ape'])->field('id,name')->select();
        $re['user_arr']=user::where(['noauth'=>'ape'])->field('id,name')->select();
        $re['account_arr']=account::where(['noauth'=>'ape'])->field('id,name')->select();
        return json($re);
    }
    //删除商品图片
    public function del_goods_img(){
        $file=input('post.file');
        $file_arr = explode('/',$file);
        $del_file=$_SERVER['DOCUMENT_ROOT'].'/skin/upload/goods/'.end($file_arr); //重构路径,防止恶意操作
        @unlink($del_file);
        return json('success');
    }
    //保存系统编码规则
    public function save_sys_number(){
        $tmp=input('post.');
        $info=$tmp['info'];
        foreach ($info as $vo) {
            number::where(['name'=>$vo['name']])->update($vo);
        }
        return json('success');
    }
    //获取系统编码规则
    public function sys_number_info(){
        return json(number::all());
    }
    
    //保存采购订单
	public function  save_opurchase (){
		$input=input ('post.');
		$info=$input['info'];
		$class_sql['time']=$input['time'];
		$class_sql['number']=$input['number'];
		$class_sql['user']=$input['user'];
		if (!empty($input['file'])){
			$class_sql['file']=$input['file'];
		}
		$class_sql['data']=$input['data'];
		if (!isset($input['id'])){
			//新增
			$class=opurchaseclass::create ($class_sql);
			set_number('opurchase');
		}else {
			//更新
			$class_sql['id']=$input['id'];
			$class=opurchaseclass::update ($class_sql);
			opurchaseinfo::destroy (['pid'=>$input['id']]);
			//删除旧info数据
		}
		//详情
		foreach ($info as $vo){
			$info_sql=[];
			$info_sql['pid']=$class['id'];
			$info_sql['goods']=$vo['goods'];
			if ($vo['attr']!=="-1"){
				$info_sql['attr']=$vo['attr'];
			}
			$info_sql['nums']=$vo['nums'];
			if (array_key_exists('data',$vo)){
				$info_sql['data']=$vo['data'];
			}
			opurchaseinfo::create ($info_sql);
		}
		push_log ('提交采购订单-'.$input['number']);
		//判断自动审核
		$sys=sys::all ();
		if (empty($sys['1']['info']['auditing'])){
			$this->auditing_opurchase ($class['id'],true);
		}
		return json (['state'=>'success']);
	}
	//审核|反审核购货单
	public function  auditing_opurchase ($id,$auth=false){
		//兼容自动审核
		if (empty($id)){
			$id=input ('post.id');
		}
		$opurchaseclass=opurchaseclass::get ($id);
		//读取info
		if (empty($opurchaseclass['type']['ape'])){
			//审核
			//更新class审核状态
			opurchaseclass::update (['id'=>$id,'type'=>1,'auditinguser'=>Session ('is_user_id'),'auditingtime'=>time()]);
			if ($auth){
				push_log ('自动审核采购订单-'.$opurchaseclass['number']);
			}else {
				//手动
				push_log ('审核采购订单-'.$opurchaseclass['number']);
			}
		}else {
			//反审核
			//判断是否存在入库行为
			$rpurchase=rpurchaseclass::where(['oid'=>$id])->find();
			if(!empty($rpurchase)){
			    return json (['state'=>'error']);
			    exit;
			}
			//复原class审核状态
			opurchaseclass::update (['id'=>$id,'type'=>0,'auditinguser'=>0,'auditingtime'=>0]);
			push_log ('反审核采购订单-'.$opurchaseclass['number']);
		}
		return json (['state'=>'success']);
	}
	//删除采购订单
	public function  del_opurchase (){
		$id=input ('post.id');
		$opurchaseclass=opurchaseclass::where (['id'=>$id])->find ();
		if (empty($opurchaseclass['type']['ape'])){
			//未审核可删除
			opurchaseclass::destroy (['id'=>$id]);
			//删除class
			opurchaseinfo::destroy (['pid'=>$id]);
			//删除info
		}
		push_log ('删除采购订单-'.$opurchaseclass['number']);
		return json ('success');
	}
    //采购订单报表
    public function opurchaseclass_list(){
        //按照名称搜索
        if(!empty(input('post.goods'))){
            $goods_sql['name|py']=['like','%'.input('post.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $opurchaseinfo_sql['goods']=['in',$goods_arr,'OR'];
        }
        if(isset($opurchaseinfo_sql)){
            $opurchaseinfo_arr=arrayChange(opurchaseinfo::where($opurchaseinfo_sql)->field('pid')->select()->toArray(),'pid');
            $sql['id']=['in',$opurchaseinfo_arr,'OR'];
        }
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.supplier'))){
            $sql['supplier']=input('post.supplier');
        }
        //按照制单人搜索
        if(!empty(input('post.user'))){
            $sql['user']=input('post.user');
        }
        //按照审核状态搜索
        if(!empty(input('post.type'))){
            $sql['type']=input('post.type')-1;
        }
        $sql['number'] = ['like','%'.input('post.number').'%'];
        $sql['data']=['like','%'.input('post.data').'%'];
        $count = opurchaseclass::where ($sql)->count();//获取总条数
        $arr = opurchaseclass::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //采购入库单 - 采购转入库
    public function orpurchaseclass_list(){
        //按照名称搜索
        if(!empty(input('post.goods'))){
            $goods_sql['name|py']=['like','%'.input('post.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $opurchaseinfo_sql['goods']=['in',$goods_arr,'OR'];
        }
        if(isset($opurchaseinfo_sql)){
            $opurchaseinfo_arr=arrayChange(opurchaseinfo::where($opurchaseinfo_sql)->field('pid')->select()->toArray(),'pid');
            $sql['id']=['in',$opurchaseinfo_arr,'OR'];
        }
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.supplier'))){
            $sql['supplier']=input('post.supplier');
        }
        //按照制单人搜索
        if(!empty(input('post.user'))){
            $sql['user']=input('post.user');
        }
        //按照入库状态搜索
        if(!empty(input('post.storage'))){
            $sql['storage']=input('post.storage')-1;
        }
        $sql['number'] = ['like','%'.input('post.number').'%'];
        $sql['type'] = '1';
        $count = opurchaseclass::where ($sql)->count();//获取总条数
        $arr = opurchaseclass::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //保存采购入库单
	public function  save_rpurchase (){
		$input=input ('post.');
		$info=$input['info'];
		//预先判断串号重复情况
		foreach ($info as $key=>$check_vo){
			//如果为串码商品,并且串号存在
			if (!empty($check_vo['serialtype']) && array_key_exists('serial',$check_vo)){
				//判断 - 查找串码状态为未销售的
				if (serial::get (['code'=>['in',explode(',',$check_vo['serial']),'OR'],'type'=>['neq',2]])){
				    //找到重复串码
					return json (['state'=>'serial_repeat','row'=>$key+1]);
					exit ;
				}
			}
		}
		$class_sql['supplier']=$input['supplier'];
		$class_sql['time']=$input['time'];
		$class_sql['number']=$input['number'];
		$class_sql['total']=$input['total'];
		$class_sql['actual']=$input['actual'];
		$class_sql['money']=$input['money'];
		$class_sql['user']=$input['user'];
		$class_sql['account']=$input['account'];
		$class_sql['billtype']=-1;
		if (!empty($input['file'])){
			$class_sql['file']=$input['file'];
		}
		$class_sql['data']=$input['data'];
		if (!isset($input['id'])){
			//新增
			$class_sql['oid']=$input['oid'];//新增时保存OID
			$class=rpurchaseclass::create ($class_sql);
			set_number('rpurchase');
		}else {
			//更新
			$class_sql['id']=$input['id'];
			$class=rpurchaseclass::update ($class_sql);
			rpurchaseinfo::destroy (['pid'=>$input['id']]);
			//删除旧info数据
		}
		$timemark=time();//时间标识
		//详情
		foreach ($info as $vo){
			$info_sql=[];
			$info_sql['pid']=$class['id'];
			$info_sql['oid']=$vo['oid'];
			$info_sql['goods']=$vo['goods'];
			if ($vo['attr']!=="-1"){
				$info_sql['attr']=$vo['attr'];
			}
			$info_sql['warehouse']=$vo['warehouse'];
			//判断串号
			if (array_key_exists('serial',$vo) && !empty($vo['serialtype'])){
				$info_sql['serial']=$vo['serial'];
			}
			$info_sql['nums']=$vo['nums'];
			$info_sql['price']=$vo['price'];
			$info_sql['total']=$vo['total'];
			if (array_key_exists('batch',$vo) && !empty($vo['batch'])){
				$info_sql['batch']=$vo['batch'];
			}
			if (array_key_exists('data',$vo)){
				$info_sql['data']=$vo['data'];
			}
			$info_sql['timemark']=0;//时间标识
			rpurchaseinfo::create ($info_sql);
		}
		push_log ('提交采购入库单-'.$input['number']);
		//判断自动审核
		$sys=sys::all ();
		if (empty($sys['1']['info']['auditing'])){
			$this->auditing_rpurchase ($class['id'],true);
		}
		return json (['state'=>'success']);
	}
	//购货入库单报表
    public function rpurchaseclass_list(){
        //按照名称搜索
        if(!empty(input('post.goods'))){
            $goods_sql['name|py']=['like','%'.input('post.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $rpurchaseinfo_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('post.warehouse'))){
            $rpurchaseinfo_sql['warehouse']=input('post.warehouse');
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
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.supplier'))){
            $sql['supplier']=input('post.supplier');
        }
        //按照制单人搜索
        if(!empty(input('post.user'))){
            $sql['user']=input('post.user');
        }
        //按照结算账户搜索
        if(!empty(input('post.account'))){
            $sql['account']=input('post.account');
        }
        //按照审核状态搜索
        if(!empty(input('post.type'))){
            $sql['type']=input('post.type')-1;
        }
        $sql['number'] = ['like','%'.input('post.number').'%'];
        $sql['data'] = ['like','%'.input('post.data').'%'];
        if(!empty(input('post.oidnumber'))){
            $opurchaseclass_arr=arrayChange(opurchaseclass::where(['number'=>['like','%'.input('post.oidnumber').'%']])->select()->toArray(),'id');
            $sql['oid'] = ['in',$opurchaseclass_arr,'OR'];
        }
        $count = rpurchaseclass::where ($sql)->count();//获取总条数
        $arr = rpurchaseclass::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //删除采购入库单
	public function  del_rpurchase (){
		$id=input ('post.id');
		$rpurchaseclass=rpurchaseclass::where (['id'=>$id])->find ();
		if (empty($rpurchaseclass['type']['ape'])){
			//未审核可删除
			rpurchaseclass::destroy (['id'=>$id]);
			//删除class
			rpurchaseinfo::destroy (['pid'=>$id]);
			//删除info
		}
		push_log ('删除采购入库单-'.$rpurchaseclass['number']);
		return json ('success');
	}
	//审核|反审核购货入库单
	public function  auditing_rpurchase ($id,$auth=false){
		//兼容自动审核
		if (empty($id)){
			$id=input ('post.id');
		}
		$rpurchaseclass=rpurchaseclass::get ($id);
		$rpurchaseinfo=rpurchaseinfo::where (['pid'=>$id])->select ()->toArray ();
		//读取info
		if (empty($rpurchaseclass['type']['ape'])){
			//审核
			//预先判断串号重复情况以及可入库数量
			foreach ($rpurchaseinfo as $key=>$check_vo){
				//判断串号是否设置
				if (!empty($check_vo['serial'])){
				    //查找串码状态为在库和销售
					if (serial::get (['code'=>['in',explode(',',$check_vo['serial']),'OR'],'type'=>['neq',2]])){
						if ($auth){
							//自动审核
							push_log ('自动审核购货单-'.$purchaseclass['number'].'失败，原因：串码重复');
							exit ;
						}else {
							//手动审核
							return json (['state'=>'serial_repeat','row'=>$key+1]);
							exit ;
						}
					}else{
					    //开始判断可入库数量
					    $apenums=$check_vo['oid']['info']['nums']-$check_vo['oid']['info']['readynums'];
					    if($check_vo['nums'] > $apenums){
					        if ($auth){
    							//自动审核
    							push_log ('自动审核采购入库单-'.$rpurchaseclass['number'].'失败，原因：某商品该次入库数量大于可入库数量');
    							exit ;
    						}else {
    							//手动审核
    							return json (['state'=>'nums_error','row'=>$key+1,'apenums'=>$apenums]);
    							exit ;
    						}
					    }
					}
				}
			}
			$timemark=time();//时间标识
			$goods_repeat_arr=[];
			foreach ($rpurchaseinfo as $rpurchaseinfo_vo){
				//循环保存数据
				$room_sql=[];
				$room_sql['warehouse']=$rpurchaseinfo_vo['warehouse']['ape'];
				$room_sql['goods']=$rpurchaseinfo_vo['goods']['ape'];
				$room_sql['attr']=$rpurchaseinfo_vo['attr']['ape'];
				$room_sql['batch']=$rpurchaseinfo_vo['batch'];
				$room=room::get($room_sql);
				//判断仓储
				if (empty($room)){
					//新增仓储数据
					$room_sql['nums']=$rpurchaseinfo_vo['nums'];
					$room_sql['timemark']=$timemark;//时间标识
					$room=room::create ($room_sql);//时间标识
					$room_oldtimemark=0;//初始化旧时间标识
				} else {
					//更新仓储数据
					room::where (['id'=>$room['id']])->update([
					    'nums'=>$room['nums']+$rpurchaseinfo_vo['nums'],
					    'timemark'=>$timemark
					]);
					//增加库存数量
					$room_oldtimemark=$room['timemark'];//转存仓储旧时间标识
				}
				rpurchaseinfo::update (['id'=>$rpurchaseinfo_vo['id'],'room'=>$room['id'],'timemark'=>$timemark]);//info保存仓储ID以及时间标识
				//判断是否有重复商品
				if(array_key_exists($room['id'],$goods_repeat_arr)){
				    $room_oldtimemark=$goods_repeat_arr[$room['id']];//获取仓储旧时间标识
				}else{
				    $goods_repeat_arr[$room['id']]=$room['timemark'];
				}
				//新增仓储详情
				$roominfo_sql['pid']=$room['id'];
				$roominfo_sql['type']=11;
				$roominfo_sql['class']=$id;
				$roominfo_sql['info']=$rpurchaseinfo_vo['id'];
				$roominfo_sql['nums']=$rpurchaseinfo_vo['nums'];
				$roominfo_sql['timemark']=$timemark;//时间标识
				$roominfo_sql['oldtimemark']=$room_oldtimemark;//时间标识
				roominfo::create ($roominfo_sql);
				//判断批次
				if (!empty($rpurchaseinfo_vo['serial'])){
					//串号存在
					$tmp_serial=explode(',',$rpurchaseinfo_vo['serial']);
					foreach ($tmp_serial as $tmp_serial_vo){
					    //判断串码多次录入情况
					    $serial_ape=serial::get(['code'=>$tmp_serial_vo]);
					    if(empty($serial_ape)){
					        //新录入
					        $oldroom=0;//初始化旧仓库ID
					        $serial_oldtimemark=0;//初始化串码旧时间标识
				        	$serial_info=serial::create ([
				        	    'room'=>$room['id'],
				        	    'code'=>$tmp_serial_vo,
				        	    'type'=>0,
				        	    'timemark'=>$timemark,
				        	]);//增加串号
					    }else{
					        //多次录入
					        $oldroom=$serial_ape['room'];//旧ROOM_ID
					        $serial_oldtimemark=$serial_ape['timemark'];//转存旧串码时间标识
					        $serial_info=serial::update([
					            'id'=>$serial_ape['id'],
					            'room'=>$room['id'],
					            'type'=>0,
					            'timemark'=>$timemark,
					        ]);//更新串号
					    }
						serialinfo::create ([
						    'pid'=>$serial_info['id'],
						    'type'=>10,
						    'class'=>$id,
						    'oldroom'=>$oldroom,//旧仓储ID
						    'timemark'=>$timemark,//新时间标识
						    'oldtimemark'=>$serial_oldtimemark//旧时间标识
						]);//增加串号详情
					}
				}
				//增加已入库数量
    			opurchaseinfo::where (['id'=>$rpurchaseinfo_vo['oid']['ape']])->setInc ('readynums',$rpurchaseinfo_vo['nums']);
			}
			//获取资金状态
			if (empty($rpurchaseclass['money'])){
				$billtype=0;
				//未结算
			}elseif ($rpurchaseclass['money']==$rpurchaseclass['actual']){
				$billtype=2;
				//已结算
			}else {
				$billtype=1;
				//部分结算
			}
			//更新class审核状态和对账单状态
			rpurchaseclass::update (['id'=>$id,'type'=>1,'auditinguser'=>Session ('is_user_id'),'auditingtime'=>time(),'billtype'=>$billtype]);
			//资金非零
			if (!empty($rpurchaseclass['money'])){
				//增加对账单
				$bill_info=rpurchasebill::create (['pid'=>$id,'account'=>$rpurchaseclass['account']['ape'],'money'=>$rpurchaseclass['money'],'data'=>'系统自动生成','user'=>Session ('is_user_id'),'time'=>time()]);
				account::where (['id'=>$rpurchaseclass['account']['ape']])->setDec ('balance',$rpurchaseclass['money']);
				//操作资金-减
				accountinfo::create (['pid'=>$rpurchaseclass['account']['ape'],'set'=>0,'money'=>$rpurchaseclass['money'],'type'=>11,'time'=>time(),'user'=>Session ('is_user_id'),'class'=>$id,'number'=>$rpurchaseclass['number'],'only'=>$bill_info['id']]);
				//资金操作-详情
			}
			set_summary('rpurchaseclass',$id,true);//更新Summary
			if ($auth){
				push_log ('自动审核采购入库单-'.$rpurchaseclass['number']);
			}else {
				//手动
				push_log ('审核采购入库单-'.$rpurchaseclass['number']);
			}
		}else {
			//反审核
			//判断库存中的是否够反审核|串码是否已经使用
			$tmp_arr=room::where (['id'=>['in',array_column ($rpurchaseinfo,'room'),'OR']])->select ()->toArray();//取出所有商品的仓储数据
			foreach ($rpurchaseinfo as $key=>$check_vo){
			    $tmp_val=find_arr_key_val ($tmp_arr,'id',$check_vo['room']);//获取当前仓储ID的数据
			    //判断逆操作
			    //判断当前详情的时间标识与仓储ID中的时间标识是否相同
			    if(!empty($tmp_val['timemark']!==$check_vo['timemark'])){
			        return json (['state'=>'set_error','row'=>$key+1]);
			        exit;
			    }else{
			        //开始判断当前时间标识是否存在后续操作
    			    if(roominfo::get([
    			        'pid'=>$check_vo['room'],
    			        'timemark'=>['gt',$check_vo['timemark']]
    			     ])){
    			        return json (['state'=>'set_error','row'=>$key+1]);
                        exit;
    			    }
			    }
			}
			foreach ($rpurchaseinfo as $rpurchaseinfo_vo){
			    room::where (['id'=>$rpurchaseinfo_vo['room']])->setDec ('nums',$rpurchaseinfo_vo['nums']);//减少库存
				$room_info=roominfo::get([
				    'pid'=>$rpurchaseinfo_vo['room'],
				    'type'=>11,
				    'info'=>$rpurchaseinfo_vo['id']
				    ]);//获取仓储详情
				room::update([
				    'id'=>$rpurchaseinfo_vo['room'],
				    'timemark'=>$room_info['oldtimemark']
				    ]);//还原旧时间标识
				rpurchaseinfo::update (['id'=>$rpurchaseinfo_vo['id'],'room'=>0,'timemark'=>0]);//info删除仓储ID还原时间标识
				if(!empty($rpurchaseinfo_vo['serial'])){
				    $serial_arr=serial::where (['code'=>['in',explode(',',$rpurchaseinfo_vo['serial']),'OR']])->select();//获取串号详情
				    foreach ($serial_arr as $serial_vo) {
				        $serial_info=serialinfo::get(['pid'=>$serial_vo['id'],'type'=>1,'timemark'=>$serial_vo['timemark']]);//获取当前时间标识的串码详情
				        serial::update([
				            'id'=>$serial_vo['id'],
				            'room'=>$serial_info['oldroom'],
				            'type'=>2,
				            'timemark'=>$serial_info['oldtimemark']
				        ]);//还原旧仓储ID|设置状态|还原旧时间标识
				        serialinfo::destroy(['id'=>$serial_info['id']]);//删除串码详情
				    }
				}
				//减少已入库数量
			    opurchaseinfo::where (['id'=>$rpurchaseinfo_vo['oid']['ape']])->setDec ('readynums',$rpurchaseinfo_vo['nums']);
			}
			roominfo::destroy (['type'=>11,'class'=>$id]);//删除仓储详情
			rpurchaseclass::update (['id'=>$id,'type'=>0,'auditinguser'=>0,'auditingtime'=>0,'billtype'=>-1]);
			//复原class审核状态和对账单状态
			//资金非零
			if (!empty($rpurchaseclass['money'])){
				$rpurchasebill=rpurchasebill::where (['pid'=>$id])->select ();
				//获取对账单操作记录
				//复原资金
				foreach ($rpurchasebill as $rpurchasebill_vo){
					account::where (['id'=>$rpurchasebill_vo['account']['ape']])->setInc ('balance',$rpurchasebill_vo['money']);
					//增加金额
				}
				rpurchasebill::destroy (['pid'=>$id]);
				//删除对账单
				accountinfo::destroy (['type'=>11,'class'=>$id]);
				//删除资金详情
			}
			set_summary('rpurchaseclass',$id,false);//更新Summary
			push_log ('反审核采购入库单-'.$rpurchaseclass['number']);
		}
		//更新采购订单入库状态 - 开始
		$opurchaseinfo_nums=opurchaseinfo::where(['pid'=>$rpurchaseclass['oid']['ape']])->select()->toArray();//获取采购单info数据
		$tmp_all_nums=0;//应该入库总数量
		$tmp_ape_nums=0;//已经入库总数量
		foreach ($opurchaseinfo_nums as $opurchaseinfo_nums_vo) {
		    $tmp_all_nums+=$opurchaseinfo_nums_vo['nums'];//递增总数量
		    $tmp_ape_nums+=$opurchaseinfo_nums_vo['readynums'];//已入库数量
		}
		$tmp_check_nums=$tmp_all_nums-$tmp_ape_nums;//数量差
		if($tmp_check_nums == $tmp_all_nums){
		    $storage_type=0;//未入库
		}elseif($tmp_check_nums == 0){
		    $storage_type=2;//全部入库
		}else{
		    $storage_type=1;//部分入库
		}
		//更新入库状态
		opurchaseclass::update(['id'=>$rpurchaseclass['oid']['ape'],'storage'=>$storage_type]);
		//更新采购订单入库状态 - 结束
		return json (['state'=>'success']);
	}
	//采购入库对账单报表
    public function rpurchasebill_list(){
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.user'))){
            $sql['user'] = input('post.user');
        }
        //判断供应商
        if(!empty(input('post.supplier'))){
            $sql['supplier'] = input('post.supplier');
        }
        //判断结算账户
        if(!empty(input('post.account'))){
            $sql['account'] = input('post.account');
        }
        //判断付款状态
        if(empty(input('post.billtype'))){
            $sql['billtype']=['neq',-1];
        }else{
            $sql['billtype']=[
                ['neq',-1],
                ['eq',input('post.billtype')-1]
            ];
        }
        $sql['number'] = ['like','%'.input('post.number').'%'];
        $count = rpurchaseclass::where ($sql)->count();//获取总条数
        $arr = rpurchaseclass::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //采购入库对账单详情
	public function  rpurchasebill_info (){
		$id=input ('post.id');
		$re['class']=rpurchaseclass::get (['id'=>$id]);
		$re['bill']=rpurchasebill::where (['pid'=>$id])->select ();
		//数据鉴权
		$user_auth=json_decode(user_info('auth'),true);
		foreach ($re['bill'] as $key => $vo) {
		    if(!empty($user_auth) && !empty($user_auth['account'])){
		        if(in_array($vo['account']['ape'],$user_auth['account'])){
		            $re['bill'][$key]['auth_info']=true;
		        }else{
		            $re['bill'][$key]['auth_info']=false;
		        }
		    }else{
		        $re['bill'][$key]['auth_info']=true;
		    }
		}
		return json ($re);
	}
	//保存采购入库对账单操作
	public function  save_rpurchasebill (){
		$input=input ('post.');
		$rpurchaseclass=rpurchaseclass::get (['id'=>$input['id']]);
		//判断合法性
		if ($input['sum']<=$rpurchaseclass['actual']-$rpurchaseclass['money']){
			//获取资金状态
			if ($rpurchaseclass['money']+$input['sum']==$rpurchaseclass['actual']){
				$billtype=2;
				//已结算
			}else {
				$billtype=1;
				//部分结算
			}
			rpurchaseclass::update (['id'=>$input['id'],'money'=>$rpurchaseclass['money']+$input['sum'],//金额增加
			'billtype'=>$billtype//资金状态
			]);
			$bill_info=rpurchasebill::create (['pid'=>$input['id'],'account'=>$input['account'],'money'=>$input['sum'],'data'=>input ('post.data'),'user'=>Session ('is_user_id'),'time'=>time()]);
			account::where (['id'=>$input['account']])->setDec ('balance',$input['sum']);
			//操作资金-减
			accountinfo::create (['pid'=>$input['account'],'set'=>0,'money'=>$input['sum'],'type'=>11,'time'=>time(),'user'=>Session ('is_user_id'),'class'=>$rpurchaseclass['id'],'number'=>$rpurchaseclass['number'],'only'=>$bill_info['id']]);
			//资金操作-详情
		}
		push_log ('增加采购入库对账单详情-'.$rpurchaseclass['number']);
		return json ('success');
	}
	//删除采购入库对账单详情
	public function  del_rpurchasebill (){
		$id=input ('post.id');
		$rpurchasebill=rpurchasebill::get (['id'=>$id]);
		$rpurchaseclass=rpurchaseclass::get (['id'=>$rpurchasebill['pid']]);
		//获取资金状态
		if ($rpurchasebill['money']==$rpurchaseclass['money']){
			$billtype=0;
			//未结算
		}else {
			$billtype=1;
			//部分结算
		}
		rpurchaseclass::update (['id'=>$rpurchasebill['pid'],'money'=>$rpurchaseclass['money']-$rpurchasebill['money'],//金额减少
		'billtype'=>$billtype//资金状态 
		]);
		account::where (['id'=>$rpurchasebill['account']['ape']])->setInc ('balance',$rpurchasebill['money']);
		//操作资金-加
		accountinfo::destroy (['pid'=>$rpurchasebill['account']['ape'],'type'=>11,'only'=>$id]);
		//删除资金操作-详情
		rpurchasebill::destroy (['id'=>$id]);
		push_log ('删除采购入库对账单详情-'.$rpurchaseclass['number']);
		return json ('success');
	}
	//新增|保存服务信息
	public function  save_item (){
	    $info=input('post.');
	    $info['py']=text_to_py($info['name']);
		if (!isset($info['id'])){
			push_log ('创建服务信息-'.$info['name']);
			item::create ($info);
		}else {
			push_log ('修改服务信息-'.$info['name']);
			item::update ($info);
		}
		return json ('success');
	}
	//服务列表信息
    public function item_list(){
        $sql['name'] = ['like','%'.input('post.name').'%'];
        $sql['data'] = ['like','%'.input('post.data').'%'];
        $count = item::where ($sql)->count();//获取总条数
        $arr = item::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //获取服务信息
	public function  item_info (){
		return json (item::get (input ('post.id')));
	}
	//删除服务信息
	public function  del_item (){
		$id=input ('post.id');
		$itemorder=itemorderinfo::get(['item'=>$id]);
		if($itemorder){
		    return json ('error');
		}else{
		    $item=item::get ($id);
    		push_log ('删除服务信息-'.$item['name']);
    		item::destroy ($id);
    		return json ('success');
		}
	}
	//弹框-服务信息
	public function  item_info_list (){
		$info=input ('post.');
		$sql['name|py']=['like','%'.$info['name'].'%'];
		$sql['data']=['like','%'.$info['data'].'%'];
		$count=item::where ($sql)->count();//获取总条数
		$arr=item::where ($sql)->field ('id,name,price,data')->page($info['page'].','.$info['limit'])->select ()->toArray ();//查询分页数据
		$re['code']=0;
		$re['msg']='获取成功';
		$re['count']=$count;
		$re['data']=$arr;
		return json ($re);
	}
	//扫码录入-服务项目表
	public function  item_scan (){
		$info=input ('post.');
		$sql['name|py']=['like','%'.$info['val'].'%'];//名称|拼音
		//查询数据
		$arr=item::where ($sql)->field ('id,name,price')->select ();
		if (count($arr)===0){
			//无数据
			$re['type']=0;
		}elseif (count($arr)===1){
			//只有一条
			$re['type']=1;
			$re['info']=$arr[0];
		}else {
			//有多条|弹框处理
			$re['type']=2;
		}
		return json ($re);
	}
	//保存服务订单
	public function  save_itemorder (){
		$input=input ('post.');
		$info=$input['info'];
		$class_sql['customer']=$input['customer'];
		$class_sql['time']=$input['time']; 
		$class_sql['number']=$input['number'];
		$class_sql['total']=$input['total'];
		$class_sql['discount']=$input['discount'];
		//优惠金额
		$class_sql['money']=$input['money'];
		$class_sql['user']=$input['user'];
		$class_sql['account']=$input['account'];
		$class_sql['billtype']=-1;
		if (!empty($input['file'])){
			$class_sql['file']=$input['file'];
		}
		$class_sql['data']=$input['data'];
		
		if (!isset($input['id'])){
			//新增
			$class=itemorderclass::create ($class_sql);
			set_number('itemorder');
		}else {
			//更新
			$class_sql['id']=$input['id'];
			$class=itemorderclass::update ($class_sql);
			itemorderinfo::destroy (['pid'=>$input['id']]);//删除旧info数据
		}
		//详情
		foreach ($info as $vo){
			$info_sql=[];
			$info_sql['pid']=$class['id'];
			$info_sql['item']=$vo['item'];
			$info_sql['nums']=$vo['nums'];
			$info_sql['price']=$vo['price'];
			$info_sql['total']=$vo['total'];
			if (array_key_exists('data',$vo)){
				$info_sql['data']=$vo['data'];
			}
			itemorderinfo::create ($info_sql);
		}
		push_log ('提交服务订单-'.$input['number']);
		//判断自动审核
		$sys=sys::all();
		if(empty($sys['1']['info']['auditing'])){
		    $this->auditing_itemorder($class['id'],true);
		}
		return json (['state'=>'success']);
	}
	//服务订单报表
    public function itemorder_list(){
        //按照名称搜索
        if(!empty(input('post.item'))){
            $item_sql['name|py']=['like','%'.input('post.item').'%'];
            $item_arr=arrayChange(item::where($item_sql)->field('id')->select()->toArray(),'id');
            $info_sql['item']=['in',$item_arr,'OR'];
        }
        //info条件转class条件
        if(isset($info_sql)){
            $class_sql['id'] = ['in',arrayChange(itemorderinfo::where($info_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
        }
        
        //按照编号搜索
        $class_sql['number']=['like','%'.input('post.number').'%'];
        //按照客户搜索
        if(!empty(input('post.customer'))){
            $class_sql['customer']=input('post.customer');
        }
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.user'))){
            $class_sql['user']=input('post.user');
        }
        //按照结算账户搜索
        if(!empty(input('post.account'))){
            $class_sql['account']=input('post.account');
        }
        //按照审核状态搜索
        if(!empty(input('post.type'))){
            $class_sql['type']=input('post.type')-1;
        }
        $class_sql['data']=['like','%'.input('post.data').'%'];;
        $count = itemorderclass::where ($class_sql)->count();//获取总条数
        $arr = itemorderclass::where($class_sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //审核|反审核服务订单
	public function  auditing_itemorder ($id,$auth=false){
		//兼容自动审核
		if (empty($id)){
			$id=input ('post.id');
		}
		$itemorderclass=itemorderclass::get ($id);
		$itemorderinfo=itemorderinfo::where (['pid'=>$id])->select ()->toArray ();
		//读取info
		if (empty($itemorderclass['type']['ape'])){
			//审核
			//获取资金状态
			if (empty($itemorderclass['money'])){
				$billtype=0;
				//未结算
			}elseif ($itemorderclass['money']==$itemorderclass['total']-$itemorderclass['discount']){
				$billtype=2;
				//已结算
			}else {
				$billtype=1;
				//部分结算
			}
			//更新class审核状态和对账单状态
			itemorderclass::update (['id'=>$id,'type'=>1,'auditinguser'=>Session ('is_user_id'),'auditingtime'=>time(),'billtype'=>$billtype]);
			//资金非零
			if (!empty($itemorderclass['money'])){
				//增加对账单
				$bill_info=itemorderbill::create (['pid'=>$id,'account'=>$itemorderclass['account']['ape'],'money'=>$itemorderclass['money'],'data'=>'系统自动生成','user'=>Session ('is_user_id'),'time'=>time()]);
				account::where (['id'=>$itemorderclass['account']['ape']])->setInc ('balance',$itemorderclass['money']);//操作资金-增
				accountinfo::create (['pid'=>$itemorderclass['account']['ape'],'set'=>1,'money'=>$itemorderclass['money'],'type'=>12,'time'=>time(),'user'=>Session ('is_user_id'),'class'=>$id,'number'=>$itemorderclass['number'],'only'=>$bill_info['id']]);
				//资金操作-详情
			}
			if ($auth){
				push_log ('自动审核服务订单-'.$itemorderclass['number']);
			}else {
				//手动
				push_log ('审核服务订单-'.$itemorderclass['number']);
			}
		}else {
			//反审核
			itemorderclass::update (['id'=>$id,'type'=>0,'auditinguser'=>0,'auditingtime'=>0,'billtype'=>-1]);
			//复原class审核状态和对账单状态
			//资金非零
			if (!empty($itemorderclass['money'])){
				$itemorderbill=itemorderbill::where (['pid'=>$id])->select ();
				//获取对账单操作记录
				//复原资金
				foreach ($itemorderbill as $itemorderbill_vo){
					account::where (['id'=>$itemorderbill_vo['account']['ape']])->setDec ('balance',$itemorderbill_vo['money']);
					//减少金额
				}
				itemorderbill::destroy (['pid'=>$id]);
				//删除对账单
				accountinfo::destroy (['type'=>12,'class'=>$id]);
				//删除资金详情
			}
			push_log ('反审核服务订单-'.$itemorderclass['number']);
		}
		return json (['state'=>'success']);
	}
	//删除服务订单
	public function  del_itemorder(){
		$id=input ('post.id');
		$itemorderclass=itemorderclass::where (['id'=>$id])->find ();
		if (empty($itemorderclass['type']['ape'])){
			//未审核可删除
			itemorderclass::destroy (['id'=>$id]);
			//删除class
			itemorderinfo::destroy (['pid'=>$id]);
			//删除info
		}
		push_log ('删除服务订单-'.$itemorderclass['number']);
		return json ('success');
	}
	//服务对账单报表
    public function itemorderbill_list(){
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.user'))){
            $sql['user'] = input('post.user');
        }
        //判断购买客户
        if(!empty(input('post.customer'))){
            $sql['customer'] = input('post.customer');
        }
        //判断结算账户
        if(!empty(input('post.account'))){
            $sql['account'] = input('post.account');
        }
        //判断付款状态
        if(empty(input('post.billtype'))){
            $sql['billtype']=['neq',-1];
        }else{
            $sql['billtype']=[
                ['neq',-1],
                ['eq',input('post.billtype')-1]
            ];
        }
        $sql['number'] = ['like','%'.input('post.number').'%'];
        $count = itemorderclass::where ($sql)->count();//获取总条数
        $arr = itemorderclass::where($sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //服务对账单详情
	public function  itemorderbill_info (){
		$id=input ('post.id');
		$re['class']=itemorderclass::get (['id'=>$id]);
		$re['bill']=itemorderbill::where (['pid'=>$id])->select ()->toArray();
		//数据鉴权
		$user_auth=json_decode(user_info('auth'),true);
		foreach ($re['bill'] as $key => $vo) {
		    if(!empty($user_auth) && !empty($user_auth['account'])){
		        if(in_array($vo['account']['ape'],$user_auth['account'])){
		            $re['bill'][$key]['auth_info']=true;
		        }else{
		            $re['bill'][$key]['auth_info']=false;
		        }
		    }else{
		        $re['bill'][$key]['auth_info']=true;
		    }
		}
		return json ($re);
	}
	//保存服务对账单操作
	public function  save_itemorderbill (){
		$input=input ('post.');
		$itemorderclass=itemorderclass::get (['id'=>$input['id']]);
		//判断合法性
		if ($input['sum']<=$itemorderclass['total']-$itemorderclass['discount']-$itemorderclass['money']){
			//获取资金状态
			if ($itemorderclass['money']+$input['sum']==$itemorderclass['total']-$itemorderclass['discount']){
				$billtype=2;
				//已结算
			}else {
				$billtype=1;
				//部分结算
			}
			itemorderclass::update (['id'=>$input['id'],'money'=>$itemorderclass['money']+$input['sum'],//金额增加
			'billtype'=>$billtype//资金状态
			]);
			$bill_info=itemorderbill::create (['pid'=>$input['id'],'account'=>$input['account'],'money'=>$input['sum'],'data'=>input ('post.data'),'user'=>Session ('is_user_id'),'time'=>time()]);
			account::where (['id'=>$input['account']])->setInc ('balance',$input['sum']);
			//操作资金-增
			accountinfo::create (['pid'=>$input['account'],'set'=>1,'money'=>$input['sum'],'type'=>12,'time'=>time(),'user'=>Session ('is_user_id'),'class'=>$itemorderclass['id'],'number'=>$itemorderclass['number'],'only'=>$bill_info['id']]);
			//资金操作-详情
		}
		push_log ('增加服务对账单详情-'.$itemorderclass['number']);
		return json ('success');
	}
	//删除销货对账单详情
	public function  del_itemorderbill (){
		$id=input ('post.id');
		$itemorderbill=itemorderbill::get (['id'=>$id]);
		$itemorderclass=itemorderclass::get (['id'=>$itemorderbill['pid']]);
		//获取资金状态
		if ($itemorderbill['money']==$itemorderclass['money']){
			$billtype=0;
			//未结算
		}else {
			$billtype=1;
			//部分结算
		}
		itemorderclass::update (['id'=>$itemorderbill['pid'],'money'=>$itemorderclass['money']-$itemorderbill['money'],'billtype'=>$billtype]);
		account::where (['id'=>$itemorderbill['account']['ape']])->setDec ('balance',$itemorderbill['money']);
		//操作资金-减
		accountinfo::destroy (['pid'=>$itemorderbill['account']['ape'],'type'=>12,'only'=>$id]);
		//删除资金操作-详情
		itemorderbill::destroy (['id'=>$id]);
		push_log ('删除服务对账单详情-'.$itemorderclass['number']);
		return json ('success');
	}
	//保存客户积分操作
	public function save_customer_integral(){
	    $info=input('post.');
	    $sql['pid']=$info['id'];
	    if($info['set']=='inc'){
	        //增加积分
	        $sql['set']=1;
	    }else{
	        //减少积分
	        $sql['set']=0;
	    }
	    $sql['integral']=$info['integral'];
	    $sql['type']=3;
	    $sql['class']=0;
	    $sql['number']=-1;
	    $sql['time']=time();
	    $sql['data']=$info['data'];
	    customerinfo::create($sql);
	    customer::where (['id'=>$info['id']])->setInc ('integral',$info['integral']);
	    return json ('success');
	}
	//保存积分兑换单
	public function  save_exchange(){
		$input=input ('post.');
		$info=$input['info'];
		//预先判断串号使用情况
		$tmp_arr=room::where (['id'=>['in',array_column ($info,'room'),'OR']])->select ()->toArray();//取出所有商品的仓储数据
		foreach ($info as $key=>$check_vo){
		    //先判断库存
		    $tmp_val=find_arr_key_val ($tmp_arr,'id',$check_vo['room']);//获取当前仓储ID的数据
			if ($check_vo['nums']>$tmp_val['nums']){
				return json (['state'=>'stock_error','row'=>$key+1]);
				exit ;
			}
			//判断串码是否存在,此处加判断高并发可能出问题,待验证
			if(array_key_exists('serial',$check_vo) && !empty($check_vo['serial'])){
			    //查找系统未销售串码
    			$tmp_serial=arrayChange (serial::where (['room'=>$check_vo['room'],'type'=>0])->field ('code')->select ()->toArray (),'code');
				$serial_arr=explode(',',$check_vo['serial']);
				foreach ($serial_arr as $arr_vo){
					if (!in_array($arr_vo,$tmp_serial)){
						return json (['state'=>'serial_error','row'=>$key+1]);
						exit ;
					}
				}
    		    $info[$key]['serial_type']=true;//是串码商品
			}else{
			    $info[$key]['serial_type']=false;//非串码商品
			}
		}
		$class_sql['customer']=$input['customer'];
		$class_sql['time']=$input['time']; 
		$class_sql['number']=$input['number'];
		$class_sql['order_integral']=$input['order_integral'];
		$class_sql['discount']=$input['discount'];//优惠积分
		$class_sql['actual_integral']=$input['actual_integral'];
		$class_sql['user']=$input['user'];
		if (!empty($input['file'])){
			$class_sql['file']=$input['file'];
		}
		$class_sql['data']=$input['data'];
		if (!isset($input['id'])){
			//新增
			$class=exchangeclass::create ($class_sql);
			set_number('exchange');
		}else {
			//更新
			$class_sql['id']=$input['id'];
			$class=exchangeclass::update ($class_sql);
			exchangeinfo::destroy (['pid'=>$input['id']]);//删除旧info数据
		}
		//详情
		foreach ($info as $vo){
			$info_sql=[];
			$info_sql['pid']=$class['id'];
			$info_sql['room']=$vo['room'];
			$tmp_warehouse=room::get (['id'=>$vo['room']]);
			$info_sql['warehouse']=$tmp_warehouse['warehouse']['ape'];
			$info_sql['goods']=$vo['goods'];
			//判断串号
			if ($vo['serial_type'] && array_key_exists('serial',$vo)){
				$info_sql['serial']=$vo['serial'];
			}
			$info_sql['nums']=$vo['nums'];
			$info_sql['integral']=$vo['integral'];
			$info_sql['allintegral']=$vo['allintegral'];
			if (array_key_exists('data',$vo)){
				$info_sql['data']=$vo['data'];
			}
			$info_sql['timemark']=0;//时间标识
			exchangeinfo::create ($info_sql);
		}
		push_log ('提交积分兑换单-'.$input['number']);
		//判断自动审核
		$sys=sys::all();
		if(empty($sys['1']['info']['auditing'])){
		    $this->auditing_exchange($class['id'],true);
		}
		return json (['state'=>'success']);
	}
	//积分兑换单报表
    public function exchange_list(){
        //按照名称搜索
        if(!empty(input('post.goods'))){
            $goods_sql['name|py']=['like','%'.input('post.goods').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $info_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照仓库搜索
        if(!empty(input('post.warehouse'))){
            $info_sql['warehouse']=input('post.warehouse');
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
        $class_sql['number']=['like','%'.input('post.number').'%'];
        //按照客户搜索
        if(!empty(input('post.customer'))){
            $class_sql['customer']=input('post.customer');
        }
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.user'))){
            $class_sql['user']=input('post.user');
        }
        //按照审核状态搜索
        if(!empty(input('post.type'))){
            $class_sql['type']=input('post.type')-1;
        }
        $class_sql['data']=['like','%'.input('post.data').'%'];
        $count = exchangeclass::where ($class_sql)->count();//获取总条数
        $arr = exchangeclass::where($class_sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //审核|反审核积分兑换单
	public function  auditing_exchange ($id,$auth=false){
		//兼容自动审核
		if (empty($id)){
			$id=input ('post.id');
		}
		$exchangeclass=exchangeclass::get ($id);
		$exchangeinfo=exchangeinfo::where (['pid'=>$id])->select ()->toArray ();
		//读取info
		if (empty($exchangeclass['type']['ape'])){
			//审核
			//预先判断库存以及串码使用情况
			foreach ($exchangeinfo as $key=>$check_vo){
				//判断库存
				if ($check_vo['nums']>$check_vo['room']['info']['nums']){
					if ($auth){
						push_log ('自动审核积分兑换单-'.$check_vo['number'].'失败，原因：商品库存不足');
						exit ;
					}else {
						return json (['state'=>'stock_error','row'=>$key+1]);
						exit ;
					}
				}
				//判断串号是否使用
				if (!empty($check_vo['serial'])){
					$tmp_serial_arr=explode(',',$check_vo['serial']);
					//判断当前商品串码个数与串码表中这些串码(状态未销售)的个数是否相同
					if (count($tmp_serial_arr)!==serial::where (['code'=>['in',$tmp_serial_arr,'OR'],'type'=>0])->count()){
						if ($auth){
							//自动审核
							push_log ('自动审核积分兑换单-'.$check_vo['number'].'失败，原因：商品串码已销售');
						    exit ;
						}else {
							//手动审核
							return json (['state'=>'serial_error','row'=>$key+1]);
							exit ;
						}
					}
				}
			}
			$timemark=time();//时间标识
			foreach ($exchangeinfo as $exchangeinfo_vo){
				//循环保存数据
				room::update([
				    'id'=>$exchangeinfo_vo['room']['ape'],
				    'nums'=>$exchangeinfo_vo['room']['info']['nums']-$exchangeinfo_vo['nums'],
				    'timemark'=>$timemark//时间标识
				]);//更新仓储信息
				//新增仓储详情
				$roominfo_sql['pid']=$exchangeinfo_vo['room']['ape'];
				$roominfo_sql['type']=12;
				$roominfo_sql['class']=$id;
				$roominfo_sql['info']=$exchangeinfo_vo['id'];
				$roominfo_sql['nums']=$exchangeinfo_vo['nums'];
				$roominfo_sql['timemark']=$timemark;//时间标识
				$roominfo_sql['oldtimemark']=$exchangeinfo_vo['room']['info']['timemark'];//旧时间标识
				roominfo::create ($roominfo_sql);
				exchangeinfo::update(['id'=>$exchangeinfo_vo['id'],'timemark'=>$timemark]);//更新详情次数
				//判断批次
				if (!empty($exchangeinfo_vo['serial'])){
					//串号存在
					$tmp_serial=explode(',',$exchangeinfo_vo['serial']);
					foreach ($tmp_serial as $tmp_serial_vo){
					    $serial_info=serial::get(['code'=>$tmp_serial_vo]);//获取串号详情
					    serial::update(['id'=>$serial_info['id'],'type'=>1,'timemark'=>$timemark]);//修改串号状态
					    serialinfo::create ([
					        'pid'=>$serial_info['id'],
					        'type'=>11,
					        'class'=>$id,
					        'oldroom'=>$serial_info['room'],
					        'timemark'=>$timemark,
					        'oldtimemark'=>$serial_info['timemark']
					    ]);//增加串号使用详情
					}
				}
			}
			//更新class审核状态
			exchangeclass::update (['id'=>$id,'type'=>1,'auditinguser'=>Session ('is_user_id'),'auditingtime'=>time()]);
			//积分非零
			if (!empty($exchangeclass['actual_integral'])){
				customer::where (['id'=>$exchangeclass['customer']['ape']])->setDec('integral',$exchangeclass['actual_integral']);//积分资金-减
				customerinfo::create([
				    'pid'=>$exchangeclass['customer']['ape'],
				    'set'=>0,
				    'integral'=>$exchangeclass['actual_integral'],
				    'type'=>4,
				    'class'=>$id,
				    'number'=>$exchangeclass['number'],
				    'time'=>time()
				]);
			}
			set_summary('exchangeclass',$id,true);//更新Summary
			if ($auth){
				push_log ('自动审核积分兑换单-'.$exchangeclass['number']);
			}else {
				//手动
				push_log ('审核积分兑换单-'.$exchangeclass['number']);
			}
		}else {
			//反审核
			//判断逆操作以及串码状态
			foreach ($exchangeinfo as $key=>$check_vo){
				//判断逆操作
				//判断当前详情的时间标识与仓储ID中的时间标识是否相同
				if($check_vo['timemark']!==$check_vo['room']['info']['timemark']){
				    return json (['state'=>'set_error','row'=>$key+1]);
                    exit;
				}else{
				    //开始判断当前时间标识是否存在后续操作
				    if(roominfo::get([
            	        'pid'=>$check_vo['room']['ape'],
            	        'timemark'=>['gt',$check_vo['timemark']]
            	     ])){
            	        return json (['state'=>'set_error','row'=>$key+1]);
                        exit;
            	    }
				}
			}
			foreach ($exchangeinfo as $exchangeinfo_vo){
				room::where (['id'=>$exchangeinfo_vo['room']['ape']])->setInc('nums',$exchangeinfo_vo['nums']);//增加对应库存数量
				$room_info=roominfo::get([
				    'pid'=>$exchangeinfo_vo['room']['ape'],
				    'type'=>12,
				    'info'=>$exchangeinfo_vo['id']
				    ]);//获取仓储详情
				room::update([
				    'id'=>$exchangeinfo_vo['room']['ape'],
				    'timemark'=>$room_info['oldtimemark']
				    ]);//还原旧时间标识
				exchangeinfo::update (['id'=>$exchangeinfo_vo['id'],'timemark'=>0]);//详情还原时间标识
				if(!empty($exchangeinfo_vo['serial'])){
				    $serial_arr=serial::where (['code'=>['in',explode(',',$exchangeinfo_vo['serial']),'OR']])->select();//获取串号详情
				    foreach ($serial_arr as $serial_vo) {
				        $serial_info=serialinfo::get(['pid'=>$serial_vo['id'],'type'=>11,'timemark'=>$serial_vo['timemark']]);//获取当前时间标识的串码详情
				        serial::update([
				            'id'=>$serial_vo['id'],
				            'room'=>$serial_info['oldroom'],
				            'type'=>0,
				            'timemark'=>$serial_info['oldtimemark']
				        ]);//还原旧仓储ID|设置状态
				        serialinfo::destroy(['id'=>$serial_info['id']]);//删除串码详情
				    }
				}
			}
			roominfo::destroy (['type'=>12,'class'=>$id]);//删除仓储详情
			exchangeclass::update (['id'=>$id,'type'=>0,'auditinguser'=>0,'auditingtime'=>0]);
			//复原class审核状态
			//积分非零
			if (!empty($exchangeclass['actual_integral'])){
				customer::where (['id'=>$exchangeclass['customer']['ape']])->setInc('integral',$exchangeclass['actual_integral']);//积分资金-增加
				customerinfo::destroy([
				    'pid'=>$exchangeclass['customer']['ape'],
				    'type'=>4,
			        'class'=>$id
				]);//删除积分详情
			}
			set_summary('exchangeclass',$id,false);//更新Summary
			push_log ('反审核积分兑换单-'.$exchangeclass['number']);
		}
		return json (['state'=>'success']);
	}
	//删除积分兑换单
	public function  del_exchange(){
		$id=input ('post.id');
		$exchangeclass=exchangeclass::where (['id'=>$id])->find ();
		if (empty($exchangeclass['type']['ape'])){
			//未审核可删除
			exchangeclass::destroy (['id'=>$id]);
			//删除class
			exchangeinfo::destroy (['pid'=>$id]);
			//删除info
		}
		push_log ('删除积分兑换单-'.$exchangeclass['number']);
		return json ('success');
	}
	//保存资金调拨单
	public function  save_eft (){
		$input=input ('post.');
		$info=$input['info'];
		$class_sql['time']=$input['time']; 
		$class_sql['number']=$input['number'];
		$class_sql['user']=$input['user'];
		$class_sql['data']=$input['data'];
		if (!isset($input['id'])){
			//新增
			$class=eftclass::create ($class_sql);
			set_number('eft');
		}else {
			//更新
			$class_sql['id']=$input['id'];
			$class=eftclass::update ($class_sql);
			eftinfo::destroy (['pid'=>$input['id']]);//删除旧info数据
		}
		//详情
		foreach ($info as $vo){
			$info_sql=[];
			$info_sql['pid']=$class['id'];
			$info_sql['account_id']=$vo['account_id'];
			$info_sql['toaccount_id']=$vo['toaccount_id'];
			$info_sql['money']=$vo['money'];
			if (array_key_exists('data',$vo)){
				$info_sql['data']=$vo['data'];
			}
			eftinfo::create ($info_sql);
		}
		push_log ('提交资金调拨单-'.$input['number']);
		//判断自动审核
		$sys=sys::all();
		if(empty($sys['1']['info']['auditing'])){
		    $this->auditing_eft($class['id'],true);
		}
		return json (['state'=>'success']);
	}
	//调拨单报表
    public function eft_list(){
        if(!empty(input('post.account'))){
            $info_sql['account_id']=input('post.account');
        }
        if(!empty(input('post.toaccount'))){
            $info_sql['toaccount_id']=input('post.toaccount');
        }
        //info条件转class条件
        if(isset($info_sql)){
            $class_sql['id'] = ['in',arrayChange(eftinfo::where($info_sql)->field('pid')->select()->toArray(),'pid'),'OR'];
        }
        //按照编号搜索
        $class_sql['number']=['like','%'.input('post.number').'%'];
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        if(!empty(input('post.user'))){
            $class_sql['user']=input('post.user');
        }
        //按照审核状态搜索
        if(!empty(input('post.type'))){
            $class_sql['type']=input('post.type')-1;
        }
        $class_sql['data']=['like','%'.input('post.data').'%'];
        $count = eftclass::where ($class_sql)->count();//获取总条数
        $arr = eftclass::where($class_sql)->page(input('post.page').','.input('post.limit'))->select ()->toArray ();//查询分页数据
        foreach ($arr as $key=>$arr_vo) {
            //补充单据金额
            $arr[$key]['money']=eftinfo::where(['pid'=>$arr_vo['id']])->sum('money');
        }
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=$count;
    	$re['data']=$arr;
    	return json ($re);
    }
    //审核|反审核资金调拨单
	public function  auditing_eft ($id,$auth=false){
		//兼容自动审核
		if (empty($id)){
			$id=input ('post.id');
		}
		$eftclass=eftclass::get ($id);
		$eftinfo=eftinfo::where (['pid'=>$id])->select ()->toArray ();
		//读取info
		if (empty($eftclass['type']['ape'])){
			//审核
			foreach ($eftinfo as $eftinfo_vo){
				//循环保存数据
				//先处理调出资金账户
				account::where(['id'=>$eftinfo_vo['account_id']['ape']])->setDec('balance',$eftinfo_vo['money']);//减少账户余额
				accountinfo::create (['pid'=>$eftinfo_vo['account_id']['ape'],'set'=>0,'money'=>$eftinfo_vo['money'],'type'=>13,'time'=>time(),'user'=>Session ('is_user_id'),'class'=>$id,'number'=>$eftclass['number']]);
				//处理调入资金账户
				account::where(['id'=>$eftinfo_vo['toaccount_id']['ape']])->setInc('balance',$eftinfo_vo['money']);//增加账户余额
				accountinfo::create (['pid'=>$eftinfo_vo['toaccount_id']['ape'],'set'=>1,'money'=>$eftinfo_vo['money'],'type'=>14,'time'=>time(),'user'=>Session ('is_user_id'),'class'=>$id,'number'=>$eftclass['number']]);
			}
			//更新class审核状态
			eftclass::update (['id'=>$id,'type'=>1,'auditinguser'=>Session ('is_user_id'),'auditingtime'=>time()]);
			if ($auth){
				push_log ('自动审核资金调拨单-'.$eftclass['number']);
			}else {
				//手动
				push_log ('审核资金调拨单-'.$eftclass['number']);
			}
		}else {
			//反审核
			foreach ($eftinfo as $eftinfo_vo){
			    //先处理调出资金账户
			    account::where(['id'=>$eftinfo_vo['account_id']['ape']])->setInc('balance',$eftinfo_vo['money']);//增加账户余额
			    accountinfo::destroy (['type'=>13,'class'=>$id]);
			    
				//处理调入资金账户
				account::where(['id'=>$eftinfo_vo['toaccount_id']['ape']])->setDec('balance',$eftinfo_vo['money']);//增加账户余额
				accountinfo::destroy (['type'=>14,'class'=>$id]);
			}
			eftclass::update (['id'=>$id,'type'=>0,'auditinguser'=>0,'auditingtime'=>0]);//复原class审核状态
			push_log ('反审核资金调拨单-'.$eftclass['number']);
		}
		return json (['state'=>'success']);
	}
	
	//商品利润表
    public function ape_goods_list(){
        //按照名称搜索
        if(!empty(input('post.name'))){
            $goods_sql['name|py']=['like','%'.input('post.name').'%'];
            $goods_arr=arrayChange(goods::where($goods_sql)->field('id')->select()->toArray(),'id');
            $summary_sql['goods']=['in',$goods_arr,'OR'];
        }
        //按照时间搜索
        $start_time=input('post.start_time');//开始时间
        $end_time=input('post.end_time');//结束时间
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
        $arr=[];
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
            $avg=db('summary')->where(['type'=>['in',[1,2],'OR'],'room'=>$vo['room'],'price'=>['gt',0]])->avg('price');//购货单|采购单|不为零的平均价
            $allnums=db('summary')->where($summary_sql)->where(['room'=>$vo['room']])->sum('nums');
            $tmp['sales_cost']=$avg*$allnums;
            $tmp['sales_maori']=$tmp['sales_revenue']-$tmp['sales_cost'];
            $tmp['gross_interest_rate']=@round(($tmp['sales_maori']/$tmp['sales_revenue'])*100,2).'%';
            array_push($arr,$tmp);
        }
        $tmp_page=input('post.page');
        $tmp_limit=input('post.limit');
        $data = array_slice($arr,$tmp_limit*($tmp_page-1),$tmp_limit);
        $re['code']=0;
    	$re['msg']='获取成功';
    	$re['count']=count($arr);
    	$re['data']=$data;
    	return json ($re);
    }
    //获取用户功能权限
    public function user_root_info(){
        return json (['info'=>check_root(input('post.set'))]);
    }
    //删除调拨单
	public function  del_eft(){
		$id=input ('post.id');
		$eftclass=eftclass::where (['id'=>$id])->find ();
		if (empty($eftclass['type']['ape'])){
			//未审核可删除
			eftclass::destroy (['id'=>$id]);
			//删除class
			eftinfo::destroy (['pid'=>$id]);
			//删除info
		}
		push_log ('删除资金调拨单-'.$eftclass['number']);
		return json ('success');
	}
	//数据初始化获取单据数量
	public function  summary_forms(){
	    summary::execute('TRUNCATE TABLE `is_summary`;');//清空数据表
	    $form=[
            'purchaseclass'=>'购货单',
            'rpurchaseclass'=>'采购单',
            'repurchaseclass'=>'购货退货单',
            'saleclass'=>'销货单',
            'resaleclass'=>'销货退货单',
            'cashierclass'=>'零售单',
            'recashierclass'=>'零售退货单',
            'exchangeclass'=>'积分兑换单',
            'allocationclass'=>'调拨单',
            'otpurchaseclass'=>'其他入库单',
            'otsaleclass'=>'其他出库单'
        ];
        $arr=[];
        foreach ($form as $key => $vo) {
            array_push($arr,[$vo,$key,db($key)->where(['type'=>1])->count()]);
        }
		return json ($arr);
	}
	
	//初始化报表数据
	public function cal_summary(){
	    $base=30;//每次更新个数
	    $input=input('post.');
	    $class=db($input['form'])->where(['type'=>1])->field('id')->page($input['infocur'],$base)->select();
	    foreach ($class as $vo) {
	        set_summary($input['form'],$vo['id'],true);
	    }
	    $input['start']=($input['infocur']-1)*$base+1;
	    $input['end']=$input['start']+count($class)-1;
	    return json ($input);
	}
	//弹框-供应商信息
	public function  suppliers_list (){
		$info=input ('post.');
		$sql['name']=['like','%'.$info['name'].'%'];
		$sql['number']=['like','%'.$info['number'].'%'];
		$sql['contacts']=['like','%'.$info['contacts'].'%'];
		$sql['tel']=['like','%'.$info['tel'].'%'];
		$sql['data']=['like','%'.$info['data'].'%'];
		$count=supplier::where ($sql)->count();//获取总条数
		$arr=supplier::where ($sql)->page($info['page'].','.$info['limit'])->select ()->toArray ();//查询分页数据
		$re['code']=0;
		$re['msg']='获取成功';
		$re['count']=$count;
		$re['data']=$arr;
		return json ($re);
	}
	//弹框-供应商信息
	public function  customers_list (){
		$info=input ('post.');
		$sql['name']=['like','%'.$info['name'].'%'];
		$sql['number']=['like','%'.$info['number'].'%'];
		$sql['contacts']=['like','%'.$info['contacts'].'%'];
		$sql['tel']=['like','%'.$info['tel'].'%'];
		$sql['data']=['like','%'.$info['data'].'%'];
		$count=customer::where ($sql)->count();//获取总条数
		$arr=customer::where ($sql)->page($info['page'].','.$info['limit'])->select ()->toArray ();//查询分页数据
		$re['code']=0;
		$re['msg']='获取成功';
		$re['count']=$count;
		$re['data']=$arr;
		return json ($re);
	}
	//串码跟踪表
    public function serial_list(){
        $info=input('post.');
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
        $count=serial::where ($sql)->count();//获取总条数
        $arr=serial::where ($sql)->page($info['page'].','.$info['limit'])->select ()->toArray ();//查询分页数据
        $type_name=['0'=>'未销售','1'=>'已销售','2'=>'不在库'];
        foreach ($arr as $key=>$vo) {
            $room=db('room')->where(['id'=>$vo['room']])->find();
            $goods=db('goods')->where(['id'=>$room['goods']])->find();
            $arr[$key]['name']=$goods['name'];
            if(empty($room['attr'])){
                $arr[$key]['attr']='无';
            }else{
                $arr[$key]['attr']=attr_name($room['attr']);
            }
            $arr[$key]['type']=$type_name[$vo['type']];
        }
        $re['code']=0;
		$re['msg']='获取成功';
		$re['count']=$count;
		$re['data']=$arr;
		return json ($re);
    }
    //串码详情表
    public function serialinfo_list(){
        $info=input('post.');
        $sql['pid']=$info['id'];
        $count=serialinfo::where ($sql)->count();//获取总条数
        $arr=serialinfo::where ($sql)->page($info['page'].','.$info['limit'])->select ()->toArray ();//查询分页数据
        $re['code']=0;
		$re['msg']='获取成功';
		$re['count']=$count;
		$re['data']=$arr;
		return json ($re);
    }
    //收银台商品搜索
    public function cashier_goods_list(){
        $limit=20;
		$goods_sql['name|py|code']=['like','%'.input ('post.info').'%'];
		$goods_arr=arrayChange (goods::where ($goods_sql)->field ('id')->select ()->toArray (),'id');
		$sql['goods']=['in',$goods_arr,'OR'];
		$sql['nums']=['neq',0];//不查找零库存
		$count=room::where ($sql)->count();//获取总条数
		$arr=room::where ($sql)->page(input ('post.page').','.$limit)->select ()->toArray ();//查询分页数据
		$new_arr=[];
		//补充辅助属性数组
		foreach ($arr as $vo){
			$tmp['id']=$vo['id'];
			if(empty($vo['goods']['info']['retail_name'])){
			    $tmp['name']=$vo['goods']['info']['name'];
			}else{
			    $tmp['name']=$vo['goods']['info']['retail_name'];
			}
			
			$tmp['warehouse']=$vo['warehouse']['info']['name'];
			$tmp['nums']=$vo['nums'];
			$tmp['attr']=$vo['attr']['name'];
			$tmp['serial']=implode(',',arrayChange (serial::where (['room'=>$vo['id'],'type'=>0])->field ('code')->select ()->toArray (),'code'));
			$tmp['batch']=$vo['batch'];
			$tmp['number']=$vo['goods']['info']['number'];
			$tmp['class']=$vo['goods']['info']['class']['info']['name'];
			$tmp['unit']=$vo['goods']['info']['unit']['info']['name'];
			$tmp['brand']=$vo['goods']['info']['brand']['info']['name'];
			//判断价格是否存在辅助属性
			if(empty($vo['attr']['ape'])){
			    $tmp['buy']=$vo['goods']['info']['buy'];
			    $tmp['sell']=$vo['goods']['info']['sell'];
			    $tmp['retail']=$vo['goods']['info']['retail'];
			    $tmp['stocktip']=$vo['goods']['info']['stocktip'];
			}else{
			    $attr_price=attr::get(['pid'=>$vo['goods']['ape'],'ape'=>$vo['attr']['ape'],'enable'=>1]);
			    //兼容辅助属性倍修改的默认价格问题
			    if(empty($attr_price)){
			        //读取无属性信息
			        $tmp['buy']=$vo['goods']['info']['buy'];
    			    $tmp['sell']=$vo['goods']['info']['sell'];
    			    $tmp['retail']=$vo['goods']['info']['retail'];
    			    $tmp['stocktip']=$vo['goods']['info']['stocktip'];
			    }else{
			        //读取属性信息
			        $tmp['buy']=$attr_price['buy'];
    			    $tmp['sell']=$attr_price['sell'];
    			    $tmp['retail']=$attr_price['retail'];
    			    $tmp['stocktip']=$attr_price['stocktip'];
			    }
			}
			$tmp['code']=$vo['goods']['info']['code'];
			$tmp['spec']=$vo['goods']['info']['spec'];
			$tmp['location']=$vo['goods']['info']['location'];
			$tmp['integral']=$vo['goods']['info']['integral'];
			$tmp['data']=$vo['goods']['info']['data'];
			if(empty($vo['goods']['info']['imgs'])){
			    $tmp['img']='/skin/images/main/none.png';
			}else{
			    $tmp['img']=$vo['goods']['info']['imgs'][0];
			}
			array_push($new_arr,$tmp);
		}
		$re['count']=$count;
		$re['limit']=$limit;
		$re['data']=$new_arr;
		return json ($re);
    }
    
    //收银台服务搜索
    public function cashier_item_list(){
        $limit=20;
		$sql['name|py']=['like','%'.input ('post.info').'%'];
		$count=item::where ($sql)->count();//获取总条数
		$arr=item::where ($sql)->page(input ('post.page').','.$limit)->select ()->toArray ();//查询分页数据
		$re['count']=$count;
		$re['limit']=$limit;
		$re['data']=$arr;
		return json ($re);
    }
    //退出登录
    public function out_sys(){
        push_log('退出系统');
        $this->back_db();
        Cache(null);
        Session(null);
        cookie(null,'Ape_');
        header('Location: '.'http://'.$_SERVER['HTTP_HOST']);
        exit;
    }
}