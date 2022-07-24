<?php
//通用函数库
//产生随机令牌
function  user_token (){
	$token='';
	$n='qwertyuioplkjhgfdsazxcvbnm+=-1234567890QWERTYUIOPASDFGHJKLZXCVBNM';
	for ($i=0;$i<30;$i++){
		$token.=$n[mt_rand(0,strlen($n)-1)];
	}
	return $token;
}
//登录认证
function  checklogin (){
	$userid=cookie ('Ape_User_Id');
	$usertoken=cookie ('Ape_User_Token');
	if (empty($userid) || empty($usertoken)){
		return false;
	}else {
		$sql['id']=$userid;
		$sql['token']=$usertoken;
		$m=db('user')->where($sql)->find();
		if (!empty($m)){
			Session ('is_user_id',$m['id']);
			return true;
		}else {
			return false;
		}
	}
}
//用户信息_可传入指定字段
function  user_info ($name=false){
	$user=db ('user')->where(['id'=>Session('is_user_id')])->find();
	if($name){
	    return $user[$name];
	}else{
	    return $user;
	}
}
//操作日志
function  push_log ($text){
	$sql['text']=$text;
	$sql['user']=Session ('is_user_id');
	$sql['time']=time();
	db ('log')->insert ($sql);
}
//获取xls文件数据
function  xls_info ($file){
	vendor ("Execl.PHPExcel");
	$objReader=PHPExcel_IOFactory::createReader ('Excel5')->setReadDataOnly (true)->load ($file);
	//简易方式加载xls文件
	$re=$objReader->getSheet (0)->toArray (null,false,false,true);
	//获取当前工作簿并转为数组
	unset($re[1]);
	//删除标题
	return $re;
}
//商品分类数组(选择分类菜单)
function  goodsclass_arr ($type,$top){
	$info=db ('goodsclass')->select ();
	if(empty($info)){
	    array_unshift($info,[
            'id'=>0,
            'pid'=>0,
            'name'=>'请先添加分类',
            'open'=>'true',
        ]);
	    array_unshift($info,[
            'id'=>0,
            'pid'=>0,
            'name'=>'分类信息为空',
            'open'=>'true',
        ]);
	}else{
	    //如果传入$type就带全部顶级分类
    	if ($type){
    		$tmp['id']='0';
    		$tmp['pid']='0';
    		$tmp['name']=$top;
    		$tmp['open']='true';
    		array_unshift($info,$tmp);
    	}
	}
	
	return $info;
}
//按照ID_PID排序数组
function  treelist ($data,$pid){
	static $tree=array ();
	foreach ($data as $row){
		if ($row['pid']['ape']==$pid){
			$tree[]=$row;
			treelist ($data,$row['id']);
		}
	}
	return $tree;
}
//条码辅助-删除临时文件
function  del_code_tmp (){
	//删除时间戳大于1分钟的内容
	$hostdir=$_SERVER['DOCUMENT_ROOT'].'/skin/images/code/';
	//获取文件目录的文件夹地址
	$filesnames=scandir($hostdir);
	foreach ($filesnames as $name){
		if (strstr($name,"_")){
			$tmp=explode("_",$name);
			if (time()-$tmp[0]>30){
				//如果拆分文件名时间戳大于当前时间30秒则删除
				unlink($hostdir.$name);
			}
		}
	}
}
//生成二维码
//$type真输出，假返回地址
function  ewm ($text,$type=true){
	$nums=time().'_'.mt_rand();
	//当前时间戳加随机数
	vendor ("phpqrcode.phpqrcode");
	$size='6';
	$level='H';
	$padding=2;
	$QR=$_SERVER['DOCUMENT_ROOT'].'/skin/images/code/'.$nums.'.png';
	$re=QRcode::png ($text,$QR,$level,$size,$padding);
	if ($type){
		ob_end_clean();
		//清除缓冲区,避免乱码
		header('Content-Type:image/png');
		imagepng(imagecreatefromstring(file_get_contents($QR)));
		exit ;
	}else {
		return $QR;
	}
}
//生成条形码
function  txm ($text,$type=true){
	$nums=time().'_'.mt_rand();
	//当前时间戳加随机数
	$QR=$_SERVER['DOCUMENT_ROOT'].'/skin/images/code/'.$nums.'.png';
	$root=$_SERVER['DOCUMENT_ROOT'];
	require_once($root.'/vendor/Barcode/BCGFontFile.php');
	require_once($root.'/vendor/Barcode/BCGColor.php');
	require_once($root.'/vendor/Barcode/BCGDrawing.php');
	// 条形码的编码格式
	require_once($root.'/vendor/Barcode/BCGcode128.barcode.php');
	// 加载字体大小
	$font=new \BCGFontFile ($_SERVER['DOCUMENT_ROOT'].'/vendor/Barcode/Arial.ttf',18);
	//颜色条形码
	$color_black=new \BCGColor (0,0,0);
	$color_white=new \BCGColor (255,255,255);
	$drawException=null;
	try {
		$code=new \BCGcode128 ();
		$code->setScale (2);
		$code->setThickness (30);
		// 条形码的厚度
		$code->setForegroundColor ($color_black);
		// 条形码颜色
		$code->setBackgroundColor ($color_white);
		// 空白间隙颜色
		$code->setFont ($font);
		// 
		$code->parse ($text);
		// 条形码需要的数据内容
	}
	catch (Exception $exception){
		$drawException=$exception;
	}
	//根据以上条件绘制条形码
	$drawing=new \BCGDrawing ('',$color_white);
	if ($drawException){
		$drawing->drawException ($drawException);
	}else {
		$drawing->setBarcode ($code);
		$drawing->draw ();
	}
	// 生成PNG格式的图片
	if ($type){
		$drawing->finish (\BCGDrawing::IMG_FORMAT_PNG ,$QR,$type);
	}else {
		$drawing->finish (\BCGDrawing::IMG_FORMAT_PNG ,$QR,$type);
		return $QR;
	}
}
//获取全部商品辅助属性数组
function  attribute_arr (){
	$n=db ('attribute');
	$top_attr=$n->where (['pid'=>0])->select ();
	foreach ($top_attr as $key=>$top_vo){
	    $top_attr[$key]['more']=[];
		$lower_attr=$n->where (['pid'=>$top_vo['id']])->select ();
		if (!empty($lower_attr)){
			$top_attr[$key]['more']=$lower_attr;
		}
	}
	return $top_attr;
}
//递归查询当前分类id下属所有的id
//$id:需要查询的分类id
//$arr为最后返回的数组
//$tmp为本次递归查询的数组
function  goodsclass_more_arr ($id,$arr='',$tmp=''){
	$n=db ('goodsclass');
	if (is_array($arr)){
		$ape=array ();
		foreach ($tmp as $vo){
			$sql['pid']=$vo;
			$info=$n->where ($sql)->column ('id');
			if ($info){
				$arr=array_merge($arr,$info);
				$ape=array_merge($ape,$info);
			}
		}
		if (empty($ape)){
			array_push($arr,$id);
		}else {
			return goodsclass_more_arr ($id,$arr,$ape);
		}
	}else {
		//第一次递归
		$sql['pid']=$id;
		$info=$n->where ($sql)->column ('id');
		if (empty($info)){
			$arr=array ($id);
		}else {
			$arr=$info;
			//第一次填充最终返回数组
			return goodsclass_more_arr ($id,$arr,$info);
		}
	}
	return $arr;
}
//字符串转拼音首字母
function  text_to_py ($text){
	$re='';
	preg_match_all("/./u",$text,$arr);
	for ($i=0;$i<count($arr[0]);$i++){
		//中文获取，其他的直接返回
		if (preg_match("/[\x7f-\xff]/",$arr[0][$i])){
			$re.=getfirstchar ($arr[0][$i]);
		}else {
			$re.=$arr[0][$i];
		}
	}
	return strtolower($re);//转小写
}
//获取单个汉字拼音首字母
function  getfirstchar ($str){
	if (empty($str)) {
        return '';
    }
    $fchar = ord($str{0});
    if ($fchar >= ord('A') && $fchar <= ord('z')) return strtoupper($str{0});
    $s1 = iconv('UTF-8', 'gb2312//TRANSLIT//IGNORE', $str);
    $s2 = iconv('gb2312', 'UTF-8//TRANSLIT//IGNORE', $s1);
    $s = $s2 == $str ? $s1 : $str;
    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    if ($asc >= -20319 && $asc <= -20284) return 'A';
    if ($asc >= -20283 && $asc <= -19776) return 'B';
    if ($asc >= -19775 && $asc <= -19219) return 'C';
    if ($asc >= -19218 && $asc <= -18711) return 'D';
    if ($asc >= -18710 && $asc <= -18527) return 'E';
    if ($asc >= -18526 && $asc <= -18240) return 'F';
    if ($asc >= -18239 && $asc <= -17923) return 'G';
    if ($asc >= -17922 && $asc <= -17418) return 'H';
    if ($asc >= -17417 && $asc <= -16475) return 'J';
    if ($asc >= -16474 && $asc <= -16213) return 'K';
    if ($asc >= -16212 && $asc <= -15641) return 'L';
    if ($asc >= -15640 && $asc <= -15166) return 'M';
    if ($asc >= -15165 && $asc <= -14923) return 'N';
    if ($asc >= -14922 && $asc <= -14915) return 'O';
    if ($asc >= -14914 && $asc <= -14631) return 'P';
    if ($asc >= -14630 && $asc <= -14150) return 'Q';
    if ($asc >= -14149 && $asc <= -14091) return 'R';
    if ($asc >= -14090 && $asc <= -13319) return 'S';
    if ($asc >= -13318 && $asc <= -12839) return 'T';
    if ($asc >= -12838 && $asc <= -12557) return 'W';
    if ($asc >= -12556 && $asc <= -11848) return 'X';
    if ($asc >= -11847 && $asc <= -11056) return 'Y';
    if ($asc >= -11055 && $asc <= -10247) return 'Z';
    return null;
}
//获取仓库格式,兼容jqgrid
//{value:"FE:FedEx;IN:InTime;TN:TNT;AR:ARAMEX"}
//{value:"1:山西仓;3:北京仓;"}
function  warehouse_arr (){
	$tmp['value']='';
	$info=db ('warehouse')->field ('id,name')->select ();
	if (!empty($info)){
		foreach ($info as $vo){
			$tmp['value'].=$vo['id'].':'.$vo['name'].';';
		}
		$tmp['value']=substr($tmp['value'],0,-1);
	}
	$re['db']=$info;
	$re['grid']=$tmp;
	return json_encode($re);
}

//获取表格配置
//传入TAB名称m
function  tabinfo ($name){
	$sql['name']=$name;
	$info=db ('tabinfo')->where ($sql)->find ();
	$info['main']=json_decode($info['main'],true);
	return json_encode($info);
}
//多维数组转一维
function  arrayChange ($arr,$key){
	return array_unique(array_column ($arr,$key));
}
//导出为Excels
//文件名称，文件数据，文件是否返回文件名还是直接下载
function  ExportExcel ($file_name,$arr,$ape_return=[false,0]){
    del_tmp_file ('skin/tmp_file/xls/');//删除临时文件
	$file_name=iconv('utf-8','gb2312',$file_name);
	vendor ("Execl.PHPExcel");
	$objPHPExcel=new PHPExcel();
	$cellName=['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ'];
	$width_arr=[];
	foreach ($arr as $key=>$vo){
		if (!empty($key)){
			//设置非第一个SHELL
			$objPHPExcel->createSheet ();
			$objPHPExcel->setActiveSheetIndex ($key);
		}
		$APE=$objPHPExcel->getActiveSheet ($key);
		//当前工作簿
		$APE->getPageMargins ()->setTop (0.2);
		//上边距
		$APE->getPageMargins ()->setBottom (0.2);
		//下
		$APE->getPageMargins ()->setLeft (0.2);
		//左
		$APE->getPageMargins ()->setRight (0.2);
		//右
		$APE->setTitle ($vo['shell']);
		//设置工作簿的名称
		$APE->mergeCells ('A1:'.$cellName[$vo['colnums']-1].'1');
		//合并标题单元格
		$APE->setCellValue ('A1',$vo['title'])->getStyle ('A1')->applyFromArray (['font'=>['bold'=>true],'alignment'=>['horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER ]]);
		//设置标题|居中|粗体
		$APE->getRowDimension (1)->setRowHeight (20);
		//循环加入行数据
		$cellnums=2;
		//初始列
		foreach ($vo['rows'] as $key=>$row_vo){
			$rownums=0;
			//初始行
			$check=explode("_",$key);
			//拆分识别
			if ($check[0]==="text"){
				//行文本数据
				//合并行单元格
				$APE->mergeCells ($cellName[$rownums].$cellnums.':'.$cellName[$vo['colnums']-1].$cellnums);
				//合并标题单元格
				$ape_k=$cellName[$rownums].$cellnums;
				//当前位置
				$APE->setCellValue ($ape_k,str_replace('||','            ',$row_vo));
				//填充并替换占位符数据
				$rownums++;
				$cellnums++;
				//行自增
			}else if ($check[0]==="title"){
				//表格标题数据
				$tab['key']=[];
				foreach ($row_vo as $title_key=>$title_vo){
					//填充表格标题
					$ape_k=$cellName[$rownums].$cellnums;
					//当前位置
					$APE->setCellValue ($ape_k,$title_vo);
					//设置居中
					$APE->getStyle ($ape_k)->applyFromArray (['alignment'=>['horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER]]);//设置居中
					//填充数据
					array_push($tab['key'],$title_key);
					//转存标题键名
					$rownums++;
				}
				$cellnums++;
				//行自增
			}else if ($check[0]==="data"){
				//表格内容数据
				foreach ($row_vo as $data_vo){
					$rownums=0;
					//初始行
					//填充表格内容
					foreach ($tab['key'] as $tab_vo){
						$ape_k=$cellName[$rownums].$cellnums;//当前位置
						//判断字段是否存在
						if(array_key_exists($tab_vo,$data_vo)){
						    //判断单元格内容
						    if (strstr($data_vo[$tab_vo],'||')){
    							//自定义格式
    							$tmp_arr=explode('||',$data_vo[$tab_vo]);
    							if ($tmp_arr['1']==="img"){
    								//图像格式
    								$objDrawing=new PHPExcel_Worksheet_Drawing();
    								/*设置图片路径 切记：只能是本地图片*/
    								$objDrawing->setPath ($tmp_arr[0]);
    								/*设置图片宽高*/
    								if (empty($tmp_arr['2'])){
    									//条形码
    									$objDrawing->setWidth (180);
    									$APE->getRowDimension ($cellnums)->setRowHeight (60);
    									//设置高度
    								}else {
    									//二维码
    									$objDrawing->setWidth (180);
    									$objDrawing->setHeight (180);
    									$APE->getRowDimension ($cellnums)->setRowHeight (150);
    									//设置高度
    								}
    								$objDrawing->setOffsetX (6);
    								$objDrawing->setOffsetY (6);
    								/*设置图片要插入的单元格*/
    								$objDrawing->setCoordinates ($ape_k);
    								$objDrawing->setWorksheet ($APE);
    								$APE->getColumnDimension ($cellName[$rownums])->setWidth (30);
    								//设置宽度
    							}
    						}else {
    							$APE->setCellValue ($ape_k,$data_vo[$tab_vo]);//填充设置左对齐
    							$APE->getStyle ($ape_k)->applyFromArray (['alignment'=>['horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT]]);//设置左对齐
    							//转存宽度
    							$width_size=strlen($data_vo[$tab_vo]);
    							if(array_key_exists($cellName[$rownums],$width_arr)){
    							    array_push($width_arr[$cellName[$rownums]],$width_size);
    							}else{
    							    $width_arr[$cellName[$rownums]]=[$width_size];
    							}
    						}
						}
						$rownums++;
					}
					$cellnums++;//行自增
				}
			}
		}
		
		//设置边框
		$APE->getStyle ('A1:'.$cellName[$vo['colnums']-1].($vo['rownums']+1))->applyFromArray (['borders'=>['allborders'=>['style'=>PHPExcel_Style_Border::BORDER_THIN]],'alignment'=>['vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER]]);
		//设置列宽度自适应
    	foreach($width_arr as $key=>$vo){
    	    array_push($vo,9);//加入最小宽度(标题)
    	    $max=max($vo);//获取最大值
    	    //限定最宽
    	    if($max<16){
    	        $size=$max;
    	    }else{
    	        $size=16;
    	    }
    	    $APE->getColumnDimension ($key)->setWidth ($size*1.2);
    	}
	}
	$objPHPExcel->setActiveSheetIndex (0);
	//激活第一个
	if($ape_return[0]){
	    $save_file_name=strtoupper($file_name.'-'.$ape_return[1]);//拼接文件名并转大写
	    $objWriter=PHPExcel_IOFactory::createWriter ($objPHPExcel,'Excel5');
    	$objWriter->save ('skin/tmp_file/xls/'.$save_file_name.'.xls');
	    return $save_file_name;
	}else{
	    ob_end_clean();
	    //清除缓冲区,避免乱码
    	header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$file_name.'.xls"');
    	header("Content-Disposition:attachment;filename=$file_name.xls");
    	//attachment新窗口打印inline本窗口打印
    	$objWriter=PHPExcel_IOFactory::createWriter ($objPHPExcel,'Excel5');
    	$objWriter->save ('php://output');
    	exit ;
	}
}
//压缩文件为ZIP并下载
function file_to_zip($zip_name,$file_arr,$down=true){
    $filename = "skin/tmp_file/xls/".$zip_name.".zip";
    $zip = new ZipArchive();
    if ($zip->open ($filename, ZIPARCHIVE::CREATE) !== TRUE) {
        exit('无法打开文件，或者文件创建失败');
    }
    //$file_arr 就是一个存储文件路径的数组 比如 array('/a/1.jpg,/a/2.jpg....');
    foreach ($file_arr as $val) {
        $zip->addFile ($val,basename($val));
    }
    $zip->close (); // 关闭
    if($down){
        //下面是输出下载
        header("Cache-Control: max-age=0");
        header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename='.basename($filename)); // 文件名
        header("Content-Type: application/zip"); // zip格式的
        header("Content-Transfer-Encoding: binary"); // 告诉浏览器，这是二进制文件
        header('Content-Length: '.filesize($filename)); // 告诉浏览器，文件大小
        @readfile($filename);//输出文件;
    }else{
        return true;
    }
}
//获取属性组合名称
function  attr_name ($attr){
	$tmp=[];
	$arr=explode('_',$attr);
	foreach ($arr as $vo){
		$ape=db ('attribute')->where(['id'=>$vo])->find();
		array_push($tmp,$ape['name']);
	}
	return implode('|',$tmp);
}
//返回二维数组指定KEY和VALUE数据
function  find_arr_key_val ($arr,$key,$val){
	foreach ($arr as $vo){
		if ($vo[$key]==$val){
			return $vo;
		}
	}
}
//获取系统分页条数
function  sys_paging (){
	$sys=db ('sys')->where (['id'=>2])->find ();
	$tmp=json_decode($sys['info'],true);
	return 2;
}
//获取资金账户格式,兼容jqgrid
//{value:"FE:FedEx;IN:InTime;TN:TNT;AR:ARAMEX"}
//{value:"1:支付宝;3:招商银行;"}
function  account_arr (){
	$tmp['value']='';
	$info=db ('account')->field ('id,name')->select ();
	if (!empty($info)){
		foreach ($info as $vo){
			$tmp['value'].=$vo['id'].':'.$vo['name'].';';
		}
		$tmp['value']=substr($tmp['value'],0,-1);
	}
	$re['db']=$info;
	$re['grid']=$tmp;
	return json_encode($re);
}
//获取服务项目格式,兼容jqgrid
//{value:"FE:FedEx;IN:InTime;TN:TNT;AR:ARAMEX"}
function  item_arr (){
	$tmp['value']='';
	$info=db ('item')->field ('id,name,price')->select ();
	if (!empty($info)){
		foreach ($info as $vo){
			$tmp['value'].=$vo['id'].':'.$vo['name'].';';
		}
		$tmp['value']=substr($tmp['value'],0,-1);
	}
	$re['db']=$info;
	$re['grid']=$tmp;
	return json_encode($re);
}
//判断一个数组中在另外一个中是否包含
//$arr1 小数组,$arr2 大数组
function arr_contain($arr1,$arr2){
    $tmp=true;
    foreach ($arr2 as $vo) {
        if(!in_array($vo,$arr1)){
            $tmp=false;
            break;
        }
    }
    if($tmp){
        return true;
    }else{
        return false;
    }
}
//功能权限判断
//传入操作名称
function check_root($set){
    $root=user_info('root');
    if(empty($root)){
        return true;
    }else{
        $user_root=json_decode($root,true);
        if(empty($user_root[$set])){
            return false;
        }else{
            return true;
        }
    }
}
//计算当前天之前的时间-默认一周
function sum_old_day($day=7){
    $time=strtotime(date('Y-m-d',time()));//获取今天开始时间戳
    $tmp_time_arr=[];
    for ($i = 0; $i < $day; $i++) {
        array_push($tmp_time_arr,date('Y-m-d',$time-($i*86400)));
    }
    return array_reverse($tmp_time_arr);//返回反转的数组
}
//获取首页-柱状数据
//传入需要获取的表名称,统计字段,时间数组-时间格式
//返回对应时间的数据-json格式
function home_form_option($dbtab,$field,$day_arr){
    $info=[];
    $m=db($dbtab);
    foreach ($day_arr as $vo) {
        $tmp=$m->where(['time'=>strtotime($vo),'type'=>1])->sum($field);
        if(empty($tmp)){
            array_push($info,0);
        }else{
            array_push($info,$tmp);
        }
    }
    return json_encode($info);
}
//首页-仓库数量分布数据
function home_room_form(){
    //获取所有仓库
    $title=[];
    $main=[];
    $m=db('room');
    $warehouse=db('warehouse')->select();
    foreach ($warehouse as $warehouse_vo) {
        array_push($title,$warehouse_vo['name']);
        $room_nums=$m->where(['warehouse'=>$warehouse_vo['id']])->sum('nums');
        $tmp_main['name']=$warehouse_vo['name'];
        $tmp_main['value']=$room_nums;
        array_push($main,$tmp_main);
    }
    if(empty($warehouse)){
        $re['title']='["暂无"]';
        $re['main']='[{"name":"暂无","value":0}]';
    }else{
        $re['title']=json_encode($title);
        $re['main']=json_encode($main);
    }
    return $re;
}
//首页-资金分布数据
function home_account_form(){
    //获取所有账户
    $title=[];
    $main=[];
    $account=db('account')->select();
    foreach ($account as $account_vo) {
        array_push($title,$account_vo['name']);
        $tmp_main['name']=$account_vo['name'];
        $tmp_main['value']=$account_vo['balance']+$account_vo['initial'];
        array_push($main,$tmp_main);
    }
    if(empty($account)){
        $re['title']='["暂无"]';
        $re['main']='[{"name":"暂无","value":0}]';
    }else{
        $re['title']=json_encode($title);
        $re['main']=json_encode($main);
    }
    return $re;
}
//首页-本月购货总量
function cal_purchase_info(){
    $m_time=mktime(0, 0 , 0,date("m"),1,date("Y"));
    $sql['time']=[['egt',$m_time],['elt',time()]];
    $sql['type']=1;
    $summary=db('summary')->where($sql)->select();
    if(empty($summary)){
        $re['nums']=0;//总量
        $re['money']=0;//总价
    }else{
        $sum_arr=get_sums($summary,['nums','total']);
        $re['nums']=$sum_arr['nums'];//总量
        $re['money']=opt_decimal($sum_arr['total']);//总价
    }
    
    return $re;
}
//首页-本月销售总量(销货以及零售)
function cal_sale_cashier_info(){
    $m_time=mktime(0, 0 , 0,date("m"),1,date("Y"));
    $sql['time']=[['egt',$m_time],['elt',time()]];
    $sql['type']=['in',[4,6],'OR'];
    $summary=db('summary')->where($sql)->select();
    if(empty($summary)){
        $re['nums']=0;//总量
        $re['money']=0;//总价
    }else{
        $sum_arr=get_sums($summary,['nums','total']);
        $re['nums']=$sum_arr['nums'];//总量
        $re['money']=$sum_arr['total'];//总价
    }
    return $re;
}
//首页-仓储数据(库存总量-库存成本)
function cal_room_info(){
    $re['nums']=0;//库存总量
    $re['money']=0;//库存成本
    $re['tip_nums']=0;//库存预警
    $room=db('room')->select();
    $goods=db('goods');
    $attr=db('attr');
    foreach ($room as $room_vo) {
        $re['nums']+=$room_vo['nums'];//递增库存总量
        //判断是否存在辅助属性
        if(empty($room_vo['attr'])){
            //不存在
            $tmp_goods=$goods->where(['id'=>$room_vo['goods']])->find();
            $re['money']+=$tmp_goods['buy'];//递增库存成本
            if($room_vo['nums']<$tmp_goods['stocktip']){
                $re['tip_nums']++;//递增库存预警
            }
        }else{
            //存在
            $tmp_attr=$attr->where(['pid'=>$room_vo['goods'],'ape'=>$room_vo['attr'],'enable'=>1])->find();
            //兼容辅助属性被修改
            if(empty($tmp_attr)){
                $tmp_goods=$goods->where(['id'=>$room_vo['goods']])->find();
                $re['money']+=$tmp_goods['buy'];//递增库存成本
                if($room_vo['nums']<$tmp_goods['stocktip']){
                    $re['tip_nums']++;//递增库存预警
                }
            }else{
                $re['money']+=$tmp_attr['buy'];//递增库存成本
                if($room_vo['nums']<$tmp_attr['stocktip']){
                    $re['tip_nums']++;//递增库存预警
                }
            }
        }
    }
    return $re;
}
//首页-计算欠款
function cal_arrears_info(){
    $re['customer']=0;//客户欠款
    $re['supplier']=0;//供应商欠款
    //计算购货单
    $purchase=db('purchaseclass')->where(['billtype'=>['in',[0,1],'OR']])->select();
    foreach ($purchase as $purchase_vo) {
        $re['supplier']-=$purchase_vo['actual']-$purchase_vo['money'];
    }
    //计算购货退货单
    $repurchase=db('repurchaseclass')->where(['billtype'=>['in',[0,1],'OR']])->select();
    foreach ($repurchase as $repurchase_vo) {
        $re['supplier']+=$repurchase_vo['actual']-$repurchase_vo['money'];
    }
    //计算销货单
    $sale=db('saleclass')->where(['billtype'=>['in',[0,1],'OR']])->select();
    foreach ($sale as $sale_vo) {
        $re['customer']+=$sale_vo['total']-$sale_vo['discount']-$sale_vo['money'];
    }
    //计算销货退货单
    $resale=db('resaleclass')->where(['billtype'=>['in',[0,1],'OR']])->select();
    foreach ($resale as $resale_vo) {
        $re['customer']-=$resale_vo['actual']-$resale_vo['money'];
    }
    return $re;
}
//删除临时存储的文件
//取创建时间,大于30秒就删除
//$path="skin/upload/file/";
function  del_tmp_file ($path){
	//文件夹路径
	$filesnames=scandir($path);
	//获取文件目录
	$time=time();
	//当前时间
	foreach ($filesnames as $key=>$name){
		//排除掉..
		if ($key>1){
			$tmp=$path.$name;
			//文件路径
			if ($time-filectime($tmp)>30){
				@unlink($tmp);
			}
		}
	}
}
//获取指定模块编码
//$name=>模块名称
function get_number($name){
    $time=time();
    $re='';
    $info = db('number')->where(['name'=>$name])->find();
    //先判断其否启用
    if(empty($info['enable'])){
        //默认
        $default=[
            'purchase'=>['pre'=>'GH','y'=>1,'m'=>1,'d'=>1,'h'=>1,'i'=>1,'s'=>1,'nums'=>'','enable'=>1],
            'repurchase'=>['pre'=>'GHTH','y'=>1,'m'=>1,'d'=>1,'h'=>1,'i'=>1,'s'=>1,'nums'=>'','enable'=>1],
            'sale'=>['pre'=>'XH','y'=>1,'m'=>1,'d'=>1,'h'=>1,'i'=>1,'s'=>1,'nums'=>'','enable'=>1],
            'resale'=>['pre'=>'XHTH','y'=>1,'m'=>1,'d'=>1,'h'=>1,'i'=>1,'s'=>1,'nums'=>'','enable'=>1],
            'cashier'=>['pre'=>'LS','y'=>1,'m'=>1,'d'=>1,'h'=>1,'i'=>1,'s'=>1,'nums'=>'','enable'=>1],
            'recashier'=>['pre'=>'LSTH','y'=>1,'m'=>1,'d'=>1,'h'=>1,'i'=>1,'s'=>1,'nums'=>'','enable'=>1],
            'allocation'=>['pre'=>'DB','y'=>1,'m'=>1,'d'=>1,'h'=>1,'i'=>1,'s'=>1,'nums'=>'','enable'=>1],
            'otpurchase'=>['pre'=>'QTRK','y'=>1,'m'=>1,'d'=>1,'h'=>1,'i'=>1,'s'=>1,'nums'=>'','enable'=>1],
            'otsale'=>['pre'=>'QTCK','y'=>1,'m'=>1,'d'=>1,'h'=>1,'i'=>1,'s'=>1,'nums'=>'','enable'=>1],
            'gather'=>['pre'=>'SK','y'=>1,'m'=>1,'d'=>1,'h'=>1,'i'=>1,'s'=>1,'nums'=>'','enable'=>1],
            'payment'=>['pre'=>'FK','y'=>1,'m'=>1,'d'=>1,'h'=>1,'i'=>1,'s'=>1,'nums'=>'','enable'=>1],
            'otgather'=>['pre'=>'QTSR','y'=>1,'m'=>1,'d'=>1,'h'=>1,'i'=>1,'s'=>1,'nums'=>'','enable'=>1],
            'otpayment'=>['pre'=>'QTZC','y'=>1,'m'=>1,'d'=>1,'h'=>1,'i'=>1,'s'=>1,'nums'=>'','enable'=>1],
            'opurchase'=>['pre'=>'CG','y'=>1,'m'=>1,'d'=>1,'h'=>1,'i'=>1,'s'=>1,'nums'=>'','enable'=>1],
            'rpurchase'=>['pre'=>'CGRK','y'=>1,'m'=>1,'d'=>1,'h'=>1,'i'=>1,'s'=>1,'nums'=>'','enable'=>1],
            'itemorder'=>['pre'=>'FW','y'=>1,'m'=>1,'d'=>1,'h'=>1,'i'=>1,'s'=>1,'nums'=>'','enable'=>1],
            'exchange'=>['pre'=>'JFDH','y'=>1,'m'=>1,'d'=>1,'h'=>1,'i'=>1,'s'=>1,'nums'=>'','enable'=>1],
            'eft'=>['pre'=>'ZZDB','y'=>1,'m'=>1,'d'=>1,'h'=>1,'i'=>1,'s'=>1,'nums'=>'','enable'=>1]
        ];
        //编号不可为空的模块
        if(isset($default[$name])){
            $info=$default[$name];//替换默认
        }
    }
    //再次判断启用情况
    if(!empty($info['enable'])){
        $re.=$info['pre'];
        if(!empty($info['y'])){$re.=date('Y',$time);}
        if(!empty($info['m'])){$re.=date('m',$time);}
        if(!empty($info['d'])){$re.=date('d',$time);}
        if(!empty($info['h'])){$re.=date('H',$time);}
        if(!empty($info['i'])){$re.=date('i',$time);}
        if(!empty($info['s'])){$re.=date('s',$time);}
        if(!empty($info['nums'])){$re.=$info['nums'];}
    }
    return $re;
}
//编码规则自增
function set_number($name){
    $db=db('number');
    $info = $db->where(['name'=>$name])->find();
    if(!empty($info['enable']) && !empty($info['nums'])){
        $db->where(['name'=>$name])->setInc('nums');
    }
}
//前端功能权限判断
function check_roots($arr){
    $bool=true;
    $root=user_info('root');
    if(!empty($root)){
        $user_root=json_decode($root,true);
        if(count($arr) == 1){
            //单个
            if(empty($user_root[$arr[0]])){
                $bool=false;
            }
        }else{
            //多个
            $true_nums=0;
            foreach ($arr as $vo) {
                if(!empty($user_root[$vo])){
                    $true_nums++;
                }
            }
            if(empty($true_nums)){
                $bool=false;
            }
        }
    }
    return $bool;
}
//获取表格背景
function table_bg(){
    $sys=db('sys')->where(['id'=>2])->find();
    $sysinfo=json_decode($sys['info'],true);
    if(empty($sysinfo['even'])){
        $eveninfo='false';
    }else{
        $eveninfo='true';
    }
    return $eveninfo;
}
//计算二维数组多个字段单独的和
//$tab_data,['total','actual','money']
function get_sums($arr,$keys){
   $re=[];
   foreach($arr as $vo) {
       foreach ($keys as $key) {
           if(is_numeric($vo[$key])){
               if(array_key_exists($key,$re)){
                   $re[$key] += $vo[$key];
               }else{
                   $re[$key] = $vo[$key];
               }
           }
       }
   }
   return $re;
}
//数据报表-客户鉴权sql
//$id为传入的条件
function summary_customer_sql($id){
    $user=user_info();
    if(empty($user['type'])){
        $auth=json_decode($user['auth'],true);
        if(empty($auth['customer'])){
            $sql=['gt',0];//放行
        }else{
            $sql=['in',$auth['customer'],'OR'];//获取鉴权数组
        }
    }else{
        $sql=['gt',0];//放行
    }
    //如果传入指定ID,并且有鉴权数据
    if(!empty($id) && $sql[0]!='gt'){
        $sql[1][]=$id;
    }
    return $sql;
}
//优化小数位
function opt_decimal($val){
    $tmp=explode('.',$val);
    if(count($tmp)>1 && $tmp[1]=='00'){
        $val=$tmp[0];
    }
    return $val;
}
//新增|删除Summary数组
//$type单据类型
//$class_id类ID
//$set true为新增 false未删除
function set_summary($type,$class_id,$set){
    $summary=db('summary');
    if($type=='purchaseclass'){
        //购货单
        if($set){
            //新增
            $class=db($type)->find($class_id);
            $info=db('purchaseinfo')->where(['pid'=>$class_id,'timemark'=>['neq',0]])->select();
            foreach ($info as $vo) {
                $tmp['type']=1;
                $tmp['class']=$class['id'];
                $tmp['info']=$vo['id'];
                $tmp['company']=$class['supplier'];
                $tmp['time']=$class['time'];
                $tmp['number']=$class['number'];
                $tmp['user']=$class['user'];
                $tmp['account']=$class['account'];
                $tmp['goods']=$vo['goods'];
                $tmp['warehouse']=$vo['warehouse'];
                $tmp['room']=$vo['room'];
                if(!empty($vo['attr'])){$tmp['attr']=$vo['attr'];}
                if(!empty($vo['serial'])){$tmp['serial']=$vo['serial'];}
                if(!empty($vo['batch'])){$tmp['batch']=$vo['batch'];}
                $tmp['nums']=$vo['nums'];
                $tmp['price']=$vo['price'];
                $tmp['total']=$vo['total'];
                if(!empty($class['data'])){$tmp['data']=$class['data'];}
                $summary->insert($tmp);
            }
        }else{
            //删除
            $summary->where(['type'=>1,'class'=>$class_id])->delete();
        }
    }elseif($type=='rpurchaseclass'){
        //采购单
        if($set){
            //新增
            $class=db($type)->find($class_id);
            $info=db('rpurchaseinfo')->where(['pid'=>$class_id,'timemark'=>['neq',0]])->select();
            foreach ($info as $vo) {
                $tmp['type']=2;
                $tmp['class']=$class['id'];
                $tmp['info']=$vo['id'];
                $tmp['company']=$class['supplier'];
                $tmp['time']=$class['time'];
                $tmp['number']=$class['number'];
                $tmp['user']=$class['user'];
                $tmp['account']=$class['account'];
                $tmp['goods']=$vo['goods'];
                $tmp['warehouse']=$vo['warehouse'];
                $tmp['room']=$vo['room'];
                if(!empty($vo['attr'])){$tmp['attr']=$vo['attr'];}
                if(!empty($vo['serial'])){$tmp['serial']=$vo['serial'];}
                if(!empty($vo['batch'])){$tmp['batch']=$vo['batch'];}
                $tmp['nums']=$vo['nums'];
                $tmp['price']=$vo['price'];
                $tmp['total']=$vo['total'];
                if(!empty($class['data'])){$tmp['data']=$class['data'];}
                $summary->insert($tmp);
            }
        }else{
            //删除
            $summary->where(['type'=>2,'class'=>$class_id])->delete();
        }
    }elseif($type=='repurchaseclass'){
        //购货退货单
        if($set){
            //新增
            $class=db($type)->find($class_id);
            $info=db('repurchaseinfo')->where(['pid'=>$class_id,'timemark'=>['neq',0]])->select();
            foreach ($info as $vo) {
                $tmp['type']=3;
                $tmp['class']=$class['id'];
                $tmp['info']=$vo['id'];
                $tmp['company']=$class['supplier'];
                $tmp['time']=$class['time'];
                $tmp['number']=$class['number'];
                $tmp['user']=$class['user'];
                $tmp['account']=$class['account'];
                $tmp['goods']=$vo['goods'];
                $tmp['warehouse']=$vo['warehouse'];
                $tmp['room']=$vo['room'];
                $room=db('room')->find(['id'=>$vo['room']]);
                if(!empty($room['attr'])){$tmp['attr']=$room['attr'];}
                if(!empty($room['batch'])){$tmp['batch']=$room['batch'];}
                if(!empty($vo['serial'])){$tmp['serial']=$vo['serial'];}
                $tmp['nums']=$vo['nums'];
                $tmp['price']=$vo['price'];
                $tmp['total']=$vo['total'];
                if(!empty($class['data'])){$tmp['data']=$class['data'];}
                $summary->insert($tmp);
            }
        }else{
            //删除
            $summary->where(['type'=>3,'class'=>$class_id])->delete();
        }
    }elseif($type=='saleclass'){
        //销货单
        if($set){
            //新增
            $class=db($type)->find($class_id);
            $info=db('saleinfo')->where(['pid'=>$class_id,'timemark'=>['neq',0]])->select();
            foreach ($info as $vo) {
                $tmp['type']=4;
                $tmp['class']=$class['id'];
                $tmp['info']=$vo['id'];
                $tmp['company']=$class['customer'];
                $tmp['time']=$class['time'];
                $tmp['number']=$class['number'];
                $tmp['user']=$class['user'];
                $tmp['account']=$class['account'];
                $tmp['goods']=$vo['goods'];
                $tmp['warehouse']=$vo['warehouse'];
                $tmp['room']=$vo['room'];
                $room=db('room')->find(['id'=>$vo['room']]);
                if(!empty($room['attr'])){$tmp['attr']=$room['attr'];}
                if(!empty($room['batch'])){$tmp['batch']=$room['batch'];}
                if(!empty($vo['serial'])){$tmp['serial']=$vo['serial'];}
                $tmp['nums']=$vo['nums'];
                $tmp['price']=$vo['price'];
                $tmp['total']=$vo['total'];
                if(!empty($class['data'])){$tmp['data']=$class['data'];}
                $summary->insert($tmp);
            }
        }else{
            //删除
            $summary->where(['type'=>4,'class'=>$class_id])->delete();
        }
    }elseif($type=='resaleclass'){
        //销货退货单
        if($set){
            //新增
            $class=db($type)->find($class_id);
            $info=db('resaleinfo')->where(['pid'=>$class_id,'timemark'=>['neq',0]])->select();
            foreach ($info as $vo) {
                $tmp['type']=5;
                $tmp['class']=$class['id'];
                $tmp['info']=$vo['id'];
                $tmp['company']=$class['customer'];
                $tmp['time']=$class['time'];
                $tmp['number']=$class['number'];
                $tmp['user']=$class['user'];
                $tmp['account']=$class['account'];
                $tmp['goods']=$vo['goods'];
                $tmp['warehouse']=$vo['warehouse'];
                $tmp['room']=$vo['room'];
                $room=db('room')->find(['id'=>$vo['room']]);
                if(!empty($room['attr'])){$tmp['attr']=$room['attr'];}
                if(!empty($room['batch'])){$tmp['batch']=$room['batch'];}
                if(!empty($vo['serial'])){$tmp['serial']=$vo['serial'];}
                $tmp['nums']=$vo['nums'];
                $tmp['price']=$vo['price'];
                $tmp['total']=$vo['total'];
                if(!empty($class['data'])){$tmp['data']=$class['data'];}
                $summary->insert($tmp);
            }
        }else{
            //删除
            $summary->where(['type'=>5,'class'=>$class_id])->delete();
        }
    }elseif($type=='cashierclass'){
        //零售单
        if($set){
            //新增
            $class=db($type)->find($class_id);
            $info=db('cashierinfo')->where(['pid'=>$class_id,'timemark'=>['neq',0]])->select();
            foreach ($info as $vo) {
                $tmp['type']=6;
                $tmp['class']=$class['id'];
                $tmp['info']=$vo['id'];
                $tmp['company']=$class['customer'];
                $tmp['time']=$class['time'];
                $tmp['number']=$class['number'];
                $tmp['user']=$class['user'];
                $tmp['goods']=$vo['goods'];
                $tmp['warehouse']=$vo['warehouse'];
                $tmp['room']=$vo['room'];
                $room=db('room')->find(['id'=>$vo['room']]);
                if(!empty($room['attr'])){$tmp['attr']=$room['attr'];}
                if(!empty($room['batch'])){$tmp['batch']=$room['batch'];}
                if(!empty($vo['serial'])){$tmp['serial']=$vo['serial'];}
                $tmp['nums']=$vo['nums'];
                $tmp['price']=$vo['price'];
                $tmp['total']=$vo['total'];
                if(!empty($class['data'])){$tmp['data']=$class['data'];}
                $summary->insert($tmp);
            }
        }else{
            //删除
            $summary->where(['type'=>6,'class'=>$class_id])->delete();
        }
    }elseif($type=='recashierclass'){
        //零售退货单
        if($set){
            //新增
            $class=db($type)->find($class_id);
            $info=db('recashierinfo')->where(['pid'=>$class_id,'timemark'=>['neq',0]])->select();
            foreach ($info as $vo) {
                $tmp['type']=7;
                $tmp['class']=$class['id'];
                $tmp['info']=$vo['id'];
                $tmp['company']=$class['customer'];
                $tmp['time']=$class['time'];
                $tmp['number']=$class['number'];
                $tmp['user']=$class['user'];
                $tmp['account']=$class['account'];
                $tmp['goods']=$vo['goods'];
                $tmp['warehouse']=$vo['warehouse'];
                $tmp['room']=$vo['room'];
                $room=db('room')->find(['id'=>$vo['room']]);
                if(!empty($room['attr'])){$tmp['attr']=$room['attr'];}
                if(!empty($room['batch'])){$tmp['batch']=$room['batch'];}
                if(!empty($vo['serial'])){$tmp['serial']=$vo['serial'];}
                $tmp['nums']=$vo['nums'];
                $tmp['price']=$vo['price'];
                $tmp['total']=$vo['total'];
                if(!empty($class['data'])){$tmp['data']=$class['data'];}
                $summary->insert($tmp);
            }
        }else{
            //删除
            $summary->where(['type'=>7,'class'=>$class_id])->delete();
        }
    }elseif($type=='exchangeclass'){
        //积分兑换单
        if($set){
            //新增
            $class=db($type)->find($class_id);
            $info=db('exchangeinfo')->where(['pid'=>$class_id,'timemark'=>['neq',0]])->select();
            foreach ($info as $vo) {
                $tmp['type']=8;
                $tmp['class']=$class['id'];
                $tmp['info']=$vo['id'];
                $tmp['company']=$class['customer'];
                $tmp['time']=$class['time'];
                $tmp['number']=$class['number'];
                $tmp['user']=$class['user'];
                $tmp['goods']=$vo['goods'];
                $tmp['warehouse']=$vo['warehouse'];
                $tmp['room']=$vo['room'];
                $room=db('room')->find(['id'=>$vo['room']]);
                if(!empty($room['attr'])){$tmp['attr']=$room['attr'];}
                if(!empty($room['batch'])){$tmp['batch']=$room['batch'];}
                if(!empty($vo['serial'])){$tmp['serial']=$vo['serial'];}
                $tmp['nums']=$vo['nums'];
                if(!empty($class['data'])){$tmp['data']=$class['data'];}
                $summary->insert($tmp);
            }
        }else{
            //删除
            $summary->where(['type'=>8,'class'=>$class_id])->delete();
        }
    }elseif($type=='allocationclass'){
        //调拨单
        if($set){
            //新增
            $class=db($type)->find($class_id);
            $info=db('allocationinfo')->where(['pid'=>$class_id,'timemark'=>['neq',0]])->select();
            foreach ($info as $vo) {
                $tmp['type']=9;
                $tmp['class']=$class['id'];
                $tmp['info']=$vo['id'];
                $tmp['time']=$class['time'];
                $tmp['number']=$class['number'];
                $tmp['user']=$class['user'];
                $tmp['goods']=$vo['goods'];
                $tmp['warehouse']=$vo['warehouse'];
                $tmp['room']=$vo['room'];
                $room=db('room')->find(['id'=>$vo['room']]);
                if(!empty($room['attr'])){$tmp['attr']=$room['attr'];}
                if(!empty($room['batch'])){$tmp['batch']=$room['batch'];}
                if(!empty($vo['serial'])){$tmp['serial']=$vo['serial'];}
                $tmp['nums']=$vo['nums'];
                if(!empty($class['data'])){$tmp['data']=$class['data'];}
                $summary->insert($tmp);
            }
        }else{
            //删除
            $summary->where(['type'=>9,'class'=>$class_id])->delete();
        }
    }elseif($type=='otpurchaseclass'){
        //其他入库单
        if($set){
            //新增
            $class=db($type)->find($class_id);
            $info=db('otpurchaseinfo')->where(['pid'=>$class_id,'timemark'=>['neq',0]])->select();
            foreach ($info as $vo) {
                $tmp['type']=10;
                $tmp['class']=$class['id'];
                $tmp['info']=$vo['id'];
                $tmp['time']=$class['time'];
                $tmp['number']=$class['number'];
                $tmp['user']=$class['user'];
                $tmp['goods']=$vo['goods'];
                $tmp['warehouse']=$vo['warehouse'];
                $tmp['room']=$vo['room'];
                if(!empty($vo['attr'])){$tmp['attr']=$vo['attr'];}
                if(!empty($vo['serial'])){$tmp['serial']=$vo['serial'];}
                if(!empty($vo['batch'])){$tmp['batch']=$vo['batch'];}
                $tmp['nums']=$vo['nums'];
                if(!empty($class['data'])){$tmp['data']=$class['data'];}
                $summary->insert($tmp);
            }
        }else{
            //删除
            $summary->where(['type'=>10,'class'=>$class_id])->delete();
        }
    }elseif($type=='otsaleclass'){
        //其他出库单
        if($set){
            //新增
            $class=db($type)->find($class_id);
            $info=db('otsaleinfo')->where(['pid'=>$class_id,'timemark'=>['neq',0]])->select();
            foreach ($info as $vo) {
                $tmp['type']=11;
                $tmp['class']=$class['id'];
                $tmp['info']=$vo['id'];
                $tmp['time']=$class['time'];
                $tmp['number']=$class['number'];
                $tmp['user']=$class['user'];
                $tmp['goods']=$vo['goods'];
                $tmp['warehouse']=$vo['warehouse'];
                $tmp['room']=$vo['room'];
                $room=db('room')->find(['id'=>$vo['room']]);
                if(!empty($room['attr'])){$tmp['attr']=$room['attr'];}
                if(!empty($room['batch'])){$tmp['batch']=$room['batch'];}
                if(!empty($vo['serial'])){$tmp['serial']=$vo['serial'];}
                $tmp['nums']=$vo['nums'];
                if(!empty($class['data'])){$tmp['data']=$class['data'];}
                $summary->insert($tmp);
            }
        }else{
            //删除
            $summary->where(['type'=>11,'class'=>$class_id])->delete();
        }
    }
    return true;
}

