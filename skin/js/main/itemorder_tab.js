var new_data = {set_id:'-1'};//默认数据
var sm_state=0;//默认扫码状态
var attr_arr=[];//属性数组
var lastrow;//最后编辑行
var lastcell;//最后编辑列
$("#table").jqGrid({
    datatype: "local",
    colNames: ['服务ID','操作', '服务项目','服务数量','服务价格','总金额','备注信息'],
    colModel: [
        {
            name: 'set_id',
            hidden:true
        },{
            name: 'set',
            width: 60,
            sortable: false,
            formatter: function(value, grid, rows, state) {
				return "<p><i class='layui-icon add_row' title='新增'>&#xe654;</i><i class='layui-icon del_row' title='删除'>&#xe915;</i></p>";
			},
			align:"center"
        }, {
            name: 'name',
            width:'150px',
            sortable: false,
            edittype : "custom",
            editoptions:{custom_element: name_elem, custom_value: name_value},
            editable: true
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
            sortable: false,
            align:"center",
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
        check_tab();
    }
});

//设置商品名称
function name_elem (value, options) {
    return "<div class='goods_input'><input type='text' value='"+value+"'><i class='layui-icon item_info'>&#xe63c;</i></div>";
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

//预留8个空字段
for (var i = 0; i < 8; i++) {
    $("#table").jqGrid('addRowData','APE', new_data);
}

//弹出选择项目
$(document).on("click", ".item_info", function() {
    item_info_show();
});

//添加商品-传入商品ID数组
function add_item(arr,page_type){
    $("#table").jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
    for (var i = 0; i < arr.length; i++) {
        rows_info={};
        rows_info['set_id'] = arr[i].id;
        rows_info['name'] = arr[i].name;
        if(page_type){
            //录入
            rows_info['nums'] = '1';
            rows_info['price'] = arr[i].price;
            rows_info['total'] = arr[i].price;
        }else{
            //详情
            rows_info['nums'] = arr[i].nums;
            rows_info['price'] = arr[i].price;
            rows_info['total'] = arr[i].total;
            rows_info['data'] = arr[i].data;
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
    $.post("/index/service/item_scan", {
        "val" : val
    }, function(re) {
        if(re.type===0){
            $('#table').jqGrid("nextCell",lastrow,0);
            dump('未查找到服务信息，请核实');
        }else if(re.type===1){
            add_item([re.info],true);//添加商品
            $('#table').jqGrid("nextCell",lastrow,0);
        }else if(re.type===2){
            item_info_show(true,val);//弹框|传入类型和条件
            $('#table').jqGrid("nextCell",lastrow,0);
        }
    });
}

//检查表格内容
function check_tab(){
    var arr = $("#table").jqGrid("getGridParam",'data'); //获取表格数据
    var set_total = 0; //金额合计
    //取出空白行
    for (var i = 0; i < arr.length; i++) {
        if(arr[i].set_id !== '-1'){
            if(!regular_positive.test(arr[i].nums)){
                dump('第'+(i+1)+'行服务数量填写错误');
                return false;
            }else if(!regular_price.test(arr[i].price)){
                dump('第'+(i+1)+'行服务金额填写错误');
                return false;
            }else{
                cal_row(arr[i].id,arr[i].nums,arr[i].price);//计算行数据
            }
        }
    }
    return true;
}

//计算表格数据
function cal_row(row_id,row_nums,row_price){
    var total=cal((row_nums-0)*(row_price-0));//单价乘数量乘折扣
    $("#table").jqGrid('setCell',row_id,'total',total);//设置行总价格
    var set_nums = cal($("#table").getCol('nums', false, 'sum')); //数量合计
    var set_total = cal($("#table").getCol('total', false, 'sum')); //总价合计
    $('#total').val(set_total); //单据金额
    $("#table").footerData("set", {"nums": "总数量:"+set_nums,"total": "总金额:"+set_total});//设置合计
}


//初始化表格统计数据
function cal_default_rows(){
    var set_nums = cal($("#table").getCol('nums', false, 'sum')); //数量合计
    var set_total = cal($("#table").getCol('total', false, 'sum')); //总价合计
    $("#table").footerData("set", {"nums": "总数量:"+set_nums,"total": "总金额:"+set_total});//设置合计
}
