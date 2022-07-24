var new_data = {set_id:'-1'};//默认数据
var sm_state=0;//默认扫码状态
var attr_arr=[];//属性数组
var lastrow;//最后编辑行
var lastcell;//最后编辑列
$("#table").jqGrid({
    datatype: "local",
    colNames: ['仓储ID','商品ID','串码详情','当前仓库ID','目标仓库ID','操作', '商品信息<span class="sm_false" onclick="set_sm(this);">扫码枪</span>','所属仓库','当前库存','辅助属性','商品品牌','商品编号','商品分类','规格型号','条形码','商品单位','库存预警','商品货位','赠送积分','零售名称','商品批次','商品串码','调拨数量','调拨仓库<span onclick="set_towarehouse();">（批量）</span>','备注信息'],
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
            name: 'warehouse_id',
            hidden:true
        },{
            name: 'towarehouse_id',
            hidden:true
        },{
            name: 'set',
            width: 60,
            sortable: false,
            formatter: function(value, grid, rows, state) {
				return "<p><i class='layui-icon add_row' title='新增'>&#xe654;</i><i class='layui-icon del_row' title='删除'>&#xe640;</i></p>";
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
            name: 'towarehouse',
            width:'150px',
            edittype:"select",
            editoptions:warehouse_arr.grid,
            sortable: false,
            align:"center",
            editable: true,
        }, {
            name: 'data',
            sortable: false,
            align:"center",
            editable: true
        }
    ],
    width: $(document).width()-30,
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
        //修改触发
        if(cellname==="towarehouse"){
            for (var i = 0; i < warehouse_arr.db.length; i++) {
                if(warehouse_arr.db[i].id==value){
                    $("#table").jqGrid('setCell',rowid,'towarehouse_id',warehouse_arr.db[i].id);//设置辅助属性价格
                    break;
                }
            }
        }
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
        rows_info['warehouse_id'] = arr[i].warehouse_id; //所属仓库ID
        rows_info['warehouse'] = arr[i].warehouse; //所属仓库
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
            rows_info['towarehouse_id'] = '0'; //默认调拨仓库ID
            rows_info['towarehouse'] = '点击选择'; //默认调拨仓库
        }else{
            //详情
            rows_info['set_id'] = arr[i].set_id; //仓储ID
            rows_info['serial_info'] = arr[i].serial_info;//转存串码详情
            rows_info['serial'] = arr[i].serial; //串号
            rows_info['stock'] = arr[i].stock; //当前库存数
            rows_info['nums'] = arr[i].nums; //详情数量
            rows_info['towarehouse_id'] = arr[i].towarehouse_id; //默认调拨仓库ID
            rows_info['towarehouse'] = arr[i].towarehouse; //默认调拨仓库
            rows_info['data'] = arr[i].data; //备注信息
        }
        add_goods_row(rows_info,true); //添加到主表中
    }
    $("#table").jqGrid('addRowData','APE', new_data);//增加空白行
    $('#table').jqGrid("nextCell",$("#table tr").length-1,0);//最后一行激活输入
    if(auditing_type!=='1'){
        check_tab();//未审核状态检查表格
    }
}
//批量设置仓库
function set_towarehouse(){
    var html = '<div class="re_padding"><div class="layui-form layui-form-pane"><div class="layui-form-item"><label class="layui-form-label">所入仓库</label><div class="layui-input-block"><select id="set_warehouse" class="layui-input" lay-search></select></div></div><blockquote class="layui-elem-quote layui-quote-nm">该操作可批量设置所有行的所入仓库</blockquote></div></div>';
    layui.use(['layer','form'], function() {
        layer.ready(function() {
            layer.open({
                type: 1,
                title: '批量设置',
                skin: 'layui-layer-rim', //加上边框
                area: ['460px', '240px'], //宽高
                offset: '9%',
                content: html,
                btn: ['确定', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    for (var i = 0; i < warehouse_arr.db.length; i++) {
                        $('#set_warehouse').append('<option value="'+warehouse_arr.db[i].id+'">'+warehouse_arr.db[i].name+'<option>');
                    }
                    layui.form.render();
                },
                btn1: function(layero) {
                    //批量设置
                    $("#table").jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
                    var rows = $("#table").jqGrid("getGridParam",'data'); //获取表格数据
                    var warehouse_name=$('#set_warehouse').find("option:selected").text();
                    var warehouse_id=$('#set_warehouse').val();
                    for (var i = 0; i < rows.length; i++) {
                        if(rows[i].set_id !== '-1'){
                            $("#table").jqGrid('setCell',rows[i].id,'towarehouse',warehouse_name);//设置行仓库名称
                            $("#table").jqGrid('setCell',rows[i].id,'towarehouse_id',warehouse_id);//设置行仓库ID
                        }
                    }
                    layer.closeAll();
                    dump('单据非空行调拨仓库已批量设置为-'+warehouse_name);
                }
            });
        });
    });
}
//扫码返回
function scan_info(val){
    $.post("/index/service/room_scan", {
        "val" : val,
        "type" : sm_state
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
function check_tab(){
    var arr = $("#table").jqGrid("getGridParam",'data'); //获取表格数据
    var serial_all=[];
    //取出空白行
    for (var i = 0; i < arr.length; i++) {
        if(arr[i].set_id !== '-1'){
            $("#table tr").eq(i+1).find('td').css('color','#000');//初始化颜色
            //开始判断串码
            if(arr[i].serial_info){
                var serial_arr=arr[i].serial_info.split(',');
                if(arr[i].serial){
                    var tmp_serial = arr[i].serial.split(',');
                    for (var n = 0; n < tmp_serial.length; n++) {
                        //判断串码是否存在
                        if(serial_arr.indexOf(tmp_serial[n])<0){
                            //串码不匹配
                            dump('第'+(i+1)+'行第'+(n+1)+'条串码与已有串码不匹配，请核实');
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
                dump('第'+(i+1)+'行调拨数量填写错误');
                return false;
            }else if(arr[i].serial_info && (arr[i].nums-0)>serial_arr.length){
                dump('第'+(i+1)+'行为串码商品，数量不可大于串码数'+serial_arr.length);
                return false;
            }else if(arr[i].serial_info && (arr[i].nums-0)!==tmp_serial.length){
                dump('第'+(i+1)+'行调拨数量与串码个数不符,已自动计算数量');
                arr[i].nums = tmp_serial.length;//覆盖数量
                $("#table").jqGrid('setCell',arr[i].id,'nums',arr[i].nums);//设置数量
                return false;
            }else if((arr[i].nums-0)>(arr[i].stock-0)){
                dump('第'+(i+1)+'行数量不可大于库存数量，该商品库存为'+arr[i].stock);
                return false;
            }else if(arr[i].towarehouse_id==='0'){
                dump('第'+(i+1)+'行未选择调拨仓库');
                $("#table tr").eq(i+1).find('td[aria-describedby="table_towarehouse"]').css('color','#F00');
                return false;
            }else if(arr[i].towarehouse_id==arr[i].warehouse_id){
                dump('第'+(i+1)+'行调拨仓库与现有仓库相同');
                return false;
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

