var new_data = {set_id:'-1',attr_ape:'-1'};//默认数据
var sm_state=0;//默认扫码状态
var attr_arr=[];//属性数组
var lastrow;//最后编辑行
var lastcell;//最后编辑列
$("#table").jqGrid({
    datatype: "local",
    colNames: ['商品ID','辅助属性标识','操作', '商品信息<span class="sm_false" onclick="set_sm(this);">扫码枪</span>', '辅助属性','商品品牌','商品编号','商品分类','规格型号','条形码','商品单位','库存预警','商品货位','赠送积分','零售名称','数量','备注信息'],
    colModel: [
        {
            name: 'set_id',
            hidden:true
        },{
            name:'attr_ape',
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
            name: 'attr',
            sortable: false,
            align:"center",
		    editable : true,
		    edittype : "custom",
			editoptions:{custom_element: attr_elem, custom_value: attr_value},
			hidden:false
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
            name: 'nums',
            sortable: false,
            align:"center",
            editable: true
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
//设置商品名称
function name_elem (value, options) {
    return "<div class='goods_input'><input type='text' value='"+value+"'><i class='layui-icon goods_info'>&#xe63c;</i></div>";
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
//设置辅助属性
function attr_elem (value, options) {
    var row_id = $("#table").jqGrid('getGridParam','selrow');
    var row  = $("#table").jqGrid('getRowData',row_id);//获取当前行数据
    var html;
    if(row.attr_ape === '-1'){
        html = "<select><option value='-1'>无</option></select>";
    }else{
        var attr=attr_arr[row.set_id];
        html="<select>"
        for (var i = 0; i < attr.length; i++) {
            html = html+"<option value='"+attr[i].ape.ape+"'>"+attr[i].ape.name+"</option>";
        }
        html = html+"</select>";
    }
    return html;
};
//保存辅助属性
function attr_value(elem, operation, value) {
    var row_id=$(elem).context.id;
    $("#table").jqGrid('setCell',row_id,'attr_ape',$(elem).val());//设置辅助属性组合
    return $(elem).find("option:selected").text();
};
//弹出选择商品
$(document).on("click", ".goods_info", function() {
    goods_info_show();
});
//添加商品-传入商品ID数组
function add_goods(arr,page_type){
    $("#table").jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
    for (var i = 0; i < arr.length; i++) {
        rows_info={};
        rows_info['set_id'] = arr[i].id; //商品ID
        rows_info['name'] = arr[i].name; //商品名称
        if(arr[i].attr.length>0){
            //存在辅助属性
            if(page_type){
                //录入
                rows_info['attr'] = '点击选择';
                rows_info['attr_ape'] = '0';
            }else{
                //详情
                rows_info['attr'] = arr[i]['attr_name'];
                rows_info['attr_ape'] = arr[i]['attr_ape'];
            }
            attr_arr[arr[i].id]=arr[i].attr;//转存辅助属性数组
        }else{
            rows_info['attr'] = '无'; //辅助属性
            rows_info['attr_ape'] = '-1';
        }
        rows_info['brand'] = arr[i].brand.info.name; //商品品牌
        rows_info['number'] = arr[i].number; //商品编号
        rows_info['class'] = arr[i].class.info.name; //商品分类
        rows_info['spec'] = arr[i].spec; //规格型号
        rows_info['code'] = arr[i].code; //条形码
        rows_info['unit'] = arr[i].unit.info.name; //商品单位
        rows_info['stocktip'] = arr[i].stocktip; //库存预警
        rows_info['location'] = arr[i].location; //商品货位
        rows_info['integral'] = arr[i].integral; //赠送积分
        //判断页面类型
        if(page_type){
            //录入
            rows_info['nums'] = '1'; //默认数量
        }else{
            //详情
            rows_info['nums'] = arr[i].nums; //详情数量
            rows_info['data'] = arr[i].data; //备注
        }
        add_goods_row(rows_info,false); //添加到主表中
    }
    $("#table").jqGrid('addRowData','APE', new_data);//增加空白行
    $('#table').jqGrid("nextCell",$("#table tr").length-1,0);//最后一行激活输入
    if(auditing_type!=='1'){
        check_tab();//未审核状态检查表格
    }
}
//扫码返回
function scan_info(val){
    $.post("/index/service/goods_scan", {
        "val" : val,
        "type" : sm_state
    }, function(re) {
        if(re.type===0){
            $('#table').jqGrid("nextCell",lastrow,0);
            dump('未查找到商品信息，请核实');
        }else if(re.type===1){
            add_goods([re.info],true);//添加商品
            $('#table').jqGrid("nextCell",lastrow,0);
        }else if(re.type===2){
            goods_info_show(sm_state,val);//弹框|传入类型和条件
            $('#table').jqGrid("nextCell",lastrow,0);
        }
    });
}
//检查表格内容
function check_tab(cal_type){
    var cal_type=cal_type||true;
    var arr = $("#table").jqGrid("getGridParam",'data'); //获取表格数据
    //取出空白行
    for (var i = 0; i < arr.length; i++) {
        if(arr[i].set_id !== '-1'){
            $("#table tr").eq(i+1).find('td').css('color','#000');//初始化颜色
            //开始判断
            if(arr[i].attr_ape==='0'){
                dump('第'+(i+1)+'行未选择辅助属性');
                $("#table tr").eq(i+1).find('td[aria-describedby="table_attr"]').css('color','#F00');
                return false;
            }else if(!regular_positive.test(arr[i].nums)){
                dump('第'+(i+1)+'行数量填写错误');
                return false;
            }else{
                cal_row(cal_type,arr[i].id,arr[i].nums,arr[i].price);//计算行数据
            }
        }
    }
    return true;
}
function cal_row(cal_type,row_id,row_nums,row_price){
    //开始计算购货总额
    var set_nums = cal($("#table").getCol('nums', false, 'sum')); //数量合计
    $("#table").footerData("set", {"nums": "总数量:"+set_nums});//设置合计
}
//初始化表格统计数据
function cal_default_rows(){
    var set_nums = cal($("#table").getCol('nums', false, 'sum')); //数量合计
    $("#table").footerData("set", {"nums": "总数量:"+set_nums});//设置合计
}
