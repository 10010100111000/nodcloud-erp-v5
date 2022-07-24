var new_data = {set_id:'-1'};//默认数据
var sm_state=0;//默认扫码状态
var attr_arr=[];//属性数组
var lastrow;//最后编辑行
var lastcell;//最后编辑列
$("#table").jqGrid({
    datatype: "local",
    colNames: ['账户ID','操作', '结算账户','结算金额','备注信息'],
    colModel: [
        {
            name: 'set_id',
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
            name: 'account',
            width:'150px',
            edittype:"select",
            editoptions:account_arr.grid,
            sortable: false,
            align:"center",
            editable: true,
        }, {
            name: 'total',
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
        if(cellname==="account"){
            for (var i = 0; i < account_arr.db.length; i++) {
                if(account_arr.db[i].id==value){
                    $("#table").jqGrid('setCell',rowid,'set_id',account_arr.db[i].id);//设置结算账户ID
                    break;
                }
            }
        }
        //金额触发数据检测
        if(cellname==="total"){
            check_tab();
        }
    }
});
//预留8个空字段
for (var i = 0; i < 10; i++) {
    $("#table").jqGrid('addRowData','APE', new_data);
} 


//添加表格数据-传入数组
function add_row_info(arr,page_type){
    $("#table").jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
    for (var i = 0; i < arr.length; i++) {
        rows_info={};
        rows_info['set_id'] = arr[i].set_id; //资金账户标识
        rows_info['account'] = arr[i].account; //资金账户名称
        rows_info['total'] = arr[i].total; //结算金额
        rows_info['data'] = arr[i].data; //备注信息
        add_goods_row(rows_info,false); //添加到主表中
    }
    $("#table").jqGrid('addRowData','APE', new_data);//增加空白行
}

//检查表格内容
function check_tab(){
    var arr = $("#table").jqGrid("getGridParam",'data'); //获取表格数据
    var set_total = 0; //金额合计
    //取出空白行
    for (var i = 0; i < arr.length; i++) {
        if(arr[i].set_id !== '-1'){
            if(!regular_price.test(arr[i].total) || arr[i].total==="0"){
                dump('第'+(i+1)+'行结算金额填写错误');
                return false;
            }else{
                set_total=cal(set_total+(arr[i].total-0))
            }
        }
    }
    $("#table").footerData("set", {"total": "总金额:"+set_total});//设置合计
    return true;
}

//初始化表格统计数据
function cal_default_rows(){
    var set_total = cal($("#table").getCol('total', false, 'sum')); //金额合计
    $("#table").footerData("set", {"total": "总金额:"+set_total});//设置合计
}

