<?php
//GET POST提交
function http($url,$param,$action="GET"){
	$ch=curl_init();
	$config=array(CURLOPT_RETURNTRANSFER=>true,CURLOPT_URL=>$url);	
	if($action=="POST"){
		$config[CURLOPT_POST]=true;		
	}
	$config[CURLOPT_POSTFIELDS]=http_build_query($param);
	curl_setopt_array($ch,$config);	
	$result=curl_exec($ch);	
	curl_close($ch);
	return $result;
}
//数据鉴权
//传入截取到的SQL信息
function  data_auth ($options){
	$name=$options['table'];//表名称
	//需要鉴权的数据表名称 [表名称=>['字段名称',[鉴权名称]];
	$check_tab_name=[
    	'is_user'=>['id'=>'user'],
    	'is_supplier'=>['id'=>'supplier'],
    	'is_warehouse'=>['id'=>'warehouse'],
    	'is_room'=>['warehouse'=>'warehouse'],
    	'is_account'=>['id'=>'account'],
    	'is_accountinfo'=>['pid'=>'account','user'=>'user'],
    	'is_customer'=>['id'=>'customer'],
    	'is_allocationclass'=>['user'=>'user'],
    	'is_gatherclass'=>['customer'=>'customer','user'=>'user'],
    	'is_otgatherclass'=>['user'=>'user'],
    	'is_paymentclass'=>['supplier'=>'supplier','user'=>'user'],
    	'is_otpaymentclass'=>['user'=>'user'],
    	'is_otpurchaseclass'=>['user'=>'user'],
    	'is_otsaleclass'=>['user'=>'user'],
    	'is_cashierclass'=>['customer'=>'customer','user'=>'user','account'=>'account','oddacc'=>'account'],
    	'is_recashierclass'=>['customer'=>'customer','user'=>'user','account'=>'account'],
	    'is_purchaseclass'=>['supplier'=>'supplier','user'=>'user','account'=>'account'],
    	'is_repurchaseclass'=>['supplier'=>'supplier','user'=>'user','account'=>'account'],
    	'is_saleclass'=>['customer'=>'customer','user'=>'user','account'=>'account'],
    	'is_resaleclass'=>['customer'=>'customer','user'=>'user','account'=>'account'],
    	'is_log'=>['user'=>'user'],
    	'is_rpurchaseclass'=>['supplier'=>'supplier','user'=>'user','account'=>'account'],
    	'is_opurchaseclass'=>['user'=>'user'],
    	'is_itemorderclass'=>['customer'=>'customer','user'=>'user','account'=>'account'],
    	'is_exchangeclass'=>['customer'=>'customer','user'=>'user'],
    	'is_eftclass'=>['user'=>'user'],
    	'is_summary'=>['user'=>'user'],//数据报表根据实际情况预先判断供应商|客户
	];
	//判断当前表是否需要鉴权
	if (isset($check_tab_name[$name]) && !empty(session('is_user_id'))){
	    $json_sql=json_encode($options['where']);
	    //['noauth'=>'ape'] 无需鉴权
	    if(strstr($json_sql,'noauth')){
			//无需鉴权,删除字段
			unset($options['where']['AND']['noauth']);
	    }else{
    		$user=db ('user')->where(['id'=>Session ('is_user_id'),'noauth'=>'ape'])->find ();
    		//获取当前用户数据
    		$auth=json_decode($user['auth'],true);
    		//获取当前用户的鉴权数据
    		//循环查询逻辑条件
    		foreach (['AND','OR'] as $logic){
    			//循环判断当前表需要检查的字段  $k字段名称,$v鉴权名称
    			foreach ($check_tab_name[$name] as $k=>$v){
    				//判断是否空权限
    				if (!empty($auth)){
    					//判断逻辑条件是否存在
    					if (isset($options['where'][$logic])){
    						//查询语句中是否存在鉴权字段 如果存在并且鉴权数组不为空则判断,反之放行
    						if (isset($options['where'][$logic][$k])){
    							if (!empty($auth[$v])){
    								//设置了  需要授权
    								//判断单条件还是多条件执行 IN|OR的查询用法
    								if (is_array($options['where'][$logic][$k])){
    									//多条件 第二组数组就是查询的字段数据
    									$options['where'][$logic][$k][1]=array_intersect($options['where'][$logic][$k][1],$auth[$v]);
    									//取当前数组与鉴权数组差集
    								}else {
    									//单条件 判断数据鉴权数组中是否存在当前条件中的字段数值  存在则放行,反之设置默认(['字段名']=>0)
    									if (!in_array($options['where'][$logic][$k],$auth[$v])){
    										$options['where'][$logic][$k]=0;
    										//修改查询语句
    									}
    								}
    							}
    						}else {
    							if (!empty($auth[$v])){
    								//没设置  需要授权
    								$options['where'][$logic][$k]=['in',$auth[$v],'OR'];
    								//创建查询语句
    							}
    						}
    					}else {
    						//ALL类似的无条件全部查询,如果设置了是鉴权数组不为空则补上条件,反之放行
    						if ($logic!='OR' && !empty($auth[$v])){
    							$options['where'][$logic][$k]=['in',$auth[$v],'OR'];
    							//创建查询语句
    						}
    					}
    				}
    			}
    		}
	    }
	}else{
		$json_sql=json_encode($options['where']);
		if(strstr($json_sql,'noauth')){
			//无需鉴权,删除字段
			unset($options['where']['AND']['noauth']);
		}
	}
	return $options;
}