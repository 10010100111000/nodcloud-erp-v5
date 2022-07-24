var new_data = {set_id:'-1'};//默认数据
var sm_state=0;//默认扫码状态
var attr_arr=[];//属性数组
var lastrow;//最后编辑行
var lastcell;//最后编辑列
$("#table").jqGrid({
    datatype: "local",
    colNames: ['仓储ID','商品ID','串码详情','操作', '商品信息<span class="sm_false" onclick="set_sm(this);">扫码枪</span>','所属仓库','当前库存','辅助属性','商品品牌','商品编号','商品分类','规格型号','条形码','商品单位','库存预警','商品货位','赠送积分','零售名称','商品批次','商品串码','数量','退货单价','退货金额','备注信息'],
    colModel: [
        {
            name: 'set_id',
            hidden:true
        },{
            name: 'goods',
            hidden:true
        },{
            name: 'serial_info',
            hidden:true
        },{
            name: 'set',
            width: 60,
            sortable: false,
            formatter: function(value, grid, rows, state) {
				return "<p><i class='layui-icon add_row' title='新增'>&#xe654;</i><i class='layui-icon del_row' title='删除'>&#xe915;</i></p>";
			},
			align:"center",
        }, {
            name: 'name',
            width:'150px',
            sortable: false,
            edittype : "custom",
            editoptions:{custom_element: name_elem, custom_value: name_value},
            editable: true
        }, {
            name: 'warehouse',
            align:"center",
            hidden:true
        }, {
            name: 'stock',
            align:"center",
            hidden:true
        }, {
            name: 'attr',
            align:"center",
            hidden:true
        }, {
            name: 'brand',
            align:"center",
            hidden:true
        }, {
            name: 'number',
            align:"center",
            hidden:true
        }, {
            name: 'class',
            align:"center",
            hidden:true
        }, {
            name: 'spec',
            align:"center",
            hidden:true
        }, {
            name: 'code',
            align:"center",
            hidden:true
        }, {
            name: 'unit',
            align:"center",
            hidden:true
        }, {
            name: 'stocktip',
            align:"center",
            hidden:true
        }, {
            name: 'location',
            align:"center",
            hidden:true
        }, {
            name: 'integral',
            align:"center",
            hidden:true
        }, {
            name: 'retail_name',
            align:"center",
            hidden:true
        }, {
            name: 'batch',
            align:"center",
            hidden:true
        }, {
            name: 'serial',
            width:'150px',
            sortable: false,
            edittype : "custom",
            editoptions:{custom_element: serial_elem, custom_value: serial_value},
            editable: true,
            hidden:true
        }, {
            name: 'nums',
            sortable: false,
            align:"center",
            editable: true
        }, {
            name: 'price',
            sortable: false,
            align:"center",
            editable: true
        }, {
            name: 'total',
            align:"center",
            sortable: false
        }, {
            name: 'data',
            sortable: false,
            align:"center",
            editable: true
        }
    ],
    width:$(document).width()-30,
    cellsubmit: 'clientArray', //开启编辑保存
	height: $(document).height()*0.59,
	cellEdit: true, //开启单元格编辑
    rownumbers: true, //开启行号
    footerrow:true,//统计
    shrinkToFit: false,//滚动条
    beforeEditCell:function(rowid,cellname,v,iRow,iCol){
        //保存最后编辑行|列
        lastrow = iRow;//行
        lastcell = iCol;//列
    },
    afterSaveCell:function(rowid, cellname, value, iRow, iCol){
        //保存触发数据检测
        check_tab();
    }
});
//预留8个空字段
for (var i = 0; i < 10; i++) {
    $("#table").jqGrid('addRowData','APE', new_data);
}
//配置表格
$('#jqgh_table_rn').html('<i class="layui-icon">&#xe614;</i>');
set_tabinfo();//设置表格-显示列
if(batch_type){
    $("#table").setGridParam().showCol('batch').trigger("reloadGrid");//显示批次
}
if(serial_type){
    $("#table").setGridParam().showCol('serial').trigger("reloadGrid");//显示串号
}
//设置商品名称
function name_elem (value, options) {
    return "<div class='goods_input'><input type='text' value='"+value+"'><i class='layui-icon room_info'>&#xe63c;</i></div>";
};
//保存商品名称
function name_value(elem, operation, value) {
    var scan=false;
    var val=$(elem).find('input').val();
    if(val!==""){
        var row  = $("#table").jqGrid('getRowData',$(elem).context.id);//获取当前行数据
        //判断是否非空行
        if(row.set_id==='-1'){
            $(elem).find('input').val('');
            scan_info(val);//不为空-扫码处理
            scan=true;//触发扫码-返回空
        }
    }
    if(scan){
        return '';
    }else{
        return val;
    }
};

//设置串码
function serial_elem (value, options) {
    var serial_info = "'"+$("#table").jqGrid('getCell',options.ape,'serial_info')+"'";//转字符串
    return '<div class="goods_input"><input type="text" value="'+value+'"><i class="layui-icon" onclick="set_serial('+serial_info+',this)">&#xe63c;</i></div>';
    return html;
};
//保存串码
function serial_value(elem, operation, value) {
    var val=$(elem).find('input').val();
    return val;
};
//弹出选择商品
$(document).on("click", ".room_info", function() {
    room_info_show();
});
//添加商品-传入商品ID数组
function add_goods(arr,page_type){
    $("#table").jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
    for (var i = 0; i < arr.length; i++) {
        rows_info={};
        rows_info['goods'] = arr[i].goods; //商品ID
        rows_info['name'] = arr[i].name; //商品名称
        if(arr[i].attr){
            rows_info['attr'] = arr[i].attr;
        }else{
            rows_info['attr'] = '无';
        }
        rows_info['warehouse'] = arr[i].warehouse; //所入仓库
        rows_info['brand'] = arr[i].brand; //商品品牌
        rows_info['number'] = arr[i].number; //商品编号
        rows_info['class'] = arr[i].class; //商品分类
        rows_info['spec'] = arr[i].spec; //规格型号
        rows_info['code'] = arr[i].code; //条形码
        rows_info['unit'] = arr[i].unit; //商品单位
        rows_info['stocktip'] = arr[i].stocktip; //库存预警
        rows_info['location'] = arr[i].location; //商品货位
        rows_info['integral'] = arr[i].integral; //赠送积分
        rows_info['batch'] = arr[i].batch; //商品批次
        //判断页面类型
        if(page_type){
            //录入
            rows_info['set_id'] = arr[i].id; //仓储ID
            rows_info['serial_info'] = arr[i].serial;//转存串码
            rows_info['stock'] = arr[i].nums; //当前库存数
            rows_info['nums'] = '1'; //默认数量
            rows_info['price'] = arr[i].sell; //单价
            rows_info['total'] = arr[i].sell; //总价
        }else{
            //详情
            rows_info['set_id'] = arr[i].set_id; //仓储ID
            rows_info['serial_info'] = arr[i].serial_info;//转存串码详情
            rows_info['serial'] = arr[i].serial; //串号
            rows_info['stock'] = arr[i].stock; //当前库存数
            rows_info['nums'] = arr[i].nums; //详情数量
            rows_info['price'] = arr[i].price; //详情单价
            rows_info['total'] = arr[i].total; //详情总价
            rows_info['data'] = arr[i].data; //详情总价
        }
        add_goods_row(rows_info,true); //添加到主表中
    }
    $("#table").jqGrid('addRowData','APE', new_data);//增加空白行
    $('#table').jqGrid("nextCell",$("#table tr").length-1,0);//最后一行激活输入
    if(auditing_type!=='1'){
        check_tab();//未审核状态检查表格
    }
}
//扫码返回
function scan_info(val){
    $.post("/index/service/room_scan", {
        "val" : val,
        "type" : sm_state,
        'zero':1
    }, function(re) {
        if(re.type===0){
            $('#table').jqGrid("nextCell",lastrow,0);
            dump('未查找到商品信息，请核实');
        }else if(re.type===1){
            add_goods(re.info,true);//添加商品
            $('#table').jqGrid("nextCell",lastrow,0);
        }else if(re.type===2){
            room_info_show(sm_state,val);//弹框|传入类型和条件
            $('#table').jqGrid("nextCell",lastrow,0);
        }
    });
}
//检查表格内容
function check_tab(cal_type){
    var cal_type=cal_type||true;
    var arr = $("#table").jqGrid("getGridParam",'data'); //获取表格数据
    var serial_all=[];
    //取出空白行
    for (var i = 0; i < arr.length; i++) {
        if(arr[i].set_id !== '-1'){
            //开始判断串码
            if(arr[i].serial_info){
                var serial_arr=arr[i].serial_info.split(',');
                if(arr[i].serial){
                    var tmp_serial = arr[i].serial.split(',');
                    for (var n = 0; n < tmp_serial.length; n++) {
                        //判断串码是否存在
                        if(serial_arr.indexOf(tmp_serial[n])<0){
                            //串码不匹配
                            dump('第'+(i+1)+'行第'+(n+1)+'条串码与已销售串码不匹配，请核实');
                            return false;
                        }else{
                            serial_all.push(tmp_serial[n]);//转存串号数组
                        }
                    }
                }else{
                    dump('第'+(i+1)+'行为串码商品，串码不可为空');
                    return false;
                }
            }
            if(!regular_positive.test(arr[i].nums)){
                dump('第'+(i+1)+'行数量填写错误');
                return false;
            }else if(arr[i].serial_info && (arr[i].nums-0)>serial_arr.length){
                dump('第'+(i+1)+'行为串码商品，数量不可大于串码数'+serial_arr.length);
                return false;
            }else if(arr[i].serial_info && (arr[i].nums-0)!==tmp_serial.length){
                dump('第'+(i+1)+'行数量与串码个数不符,已自动计算数量');
                arr[i].nums = tmp_serial.length;//覆盖数量
                $("#table").jqGrid('setCell',arr[i].id,'nums',arr[i].nums);//设置数量
                cal_row(cal_type,arr[i].id,arr[i].nums,arr[i].price);//计算行数据
                return false;
            }else if(!regular_price.test(arr[i].price)){
                dump('第'+(i+1)+'行退货单价不正确');
                return false;
            }else{
                cal_row(cal_type,arr[i].id,arr[i].nums,arr[i].price);//计算行数据
            }
        }
    }
    if(serial_all.length>0){
        if(isRepeat(serial_all)){
            dump('商品串码重复，请核实!');
            return false;
        }
    }
    
    return true;
}
//计算行数据
function cal_row(cal_type,row_id,row_nums,row_price){
    var total=cal((row_nums-0)*(row_price-0));//单价乘数量
    $("#table").jqGrid('setCell',row_id,'total',total);//设置单商品总价格
    var set_nums = cal($("#table").getCol('nums', false, 'sum')); //数量合计
    var set_total = cal($("#table").getCol('total', false, 'sum')); //总价合计
    $('#total').val(set_total); //单据金额
    if($('#actual').attr('cal')==='true' && cal_type===true){
        $('#actual').val(set_total);//实际金额
    }
    $("#table").footerData("set", {"nums": "总数量:"+set_nums,"total": "总金额:"+set_total});//设置合计
    
}
//初始化表格统计数据
function cal_default_rows(){
    var set_nums = cal($("#table").getCol('nums', false, 'sum')); //数量合计
    var set_total = cal($("#table").getCol('total', false, 'sum')); //总价合计
    $("#table").footerData("set", {"nums": "总数量:"+set_nums,"total": "总金额:"+set_total});//设置合计
}
