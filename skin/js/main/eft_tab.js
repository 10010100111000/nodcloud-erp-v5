var new_data = {set_id:'-1',toaccount_id:'-1'};//默认数据
var sm_state=0;//默认扫码状态
var attr_arr=[];//属性数组
var lastrow;//最后编辑行
var lastcell;//最后编辑列
$("#table").jqGrid({
    datatype: "local",
    colNames: ['当前账户ID','目标账户ID','操作', '调出账户','调入账户','金额','备注信息'],
    colModel: [
        {
            name: 'set_id',
            hidden:true
        },{
            name: 'toaccount_id',
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
            name: 'toaccount',
            width:'150px',
            edittype:"select",
            editoptions:account_arr.grid,
            sortable: false,
            align:"center",
            editable: true,
        }, {
            name: 'money',
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
                    $("#table").jqGrid('setCell',rowid,'set_id',account_arr.db[i].id);//设置转出账户ID
                    break;
                }
            }
        }else if(cellname==="toaccount"){
            for (var i = 0; i < account_arr.db.length; i++) {
                if(account_arr.db[i].id==value){
                    $("#table").jqGrid('setCell',rowid,'toaccount_id',account_arr.db[i].id);//设置转入账户ID
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
//添加数据
function add_infos(arr,page_type){
    $("#table").jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
    for (var i = 0; i < arr.length; i++) {
        rows_info={};
        rows_info['set_id'] = arr[i].account_id;
        rows_info['toaccount_id'] = arr[i].toaccount_id;
        rows_info['account'] = arr[i].account;
        rows_info['toaccount'] = arr[i].toaccount;
        rows_info['money'] = arr[i].money;
        rows_info['data'] = arr[i].data;
        add_goods_row(rows_info); //添加到主表中
    }
    $("#table").jqGrid('addRowData','APE', new_data);//增加空白行
    if(auditing_type!=='1'){
        check_tab();//未审核状态检查表格
    }
}
//检查表格内容
function check_tab(){
    var arr = $("#table").jqGrid("getGridParam",'data'); //获取表格数据
    var set_money = 0; //金额合计
    //取出空白行
    for (var i = 0; i < arr.length; i++) {
        if(arr[i].set_id !== '-1'){
            if(arr[i].toaccount_id == '-1'){
                dump('第'+(i+1)+'行调入账户未选择');
                return false;
            }else if(arr[i].set_id==arr[i].toaccount_id){
                dump('第'+(i+1)+'行调出账户与调入账户相同');
                return false;
            }else if(!regular_price.test(arr[i].money) || arr[i].money=='0'){
                dump('第'+(i+1)+'行金额填写错误');
                return false;
            }else{
                set_money=cal(set_money+(arr[i].money-0))
            }
        }
    }
    $("#table").footerData("set", {"money": "总金额:"+set_money});//设置合计
    return true;
}

//初始化表格统计数据
function cal_default_rows(){
    var set_money = cal($("#table").getCol('money', false, 'sum')); //金额合计
    $("#table").footerData("set", {"money": "总金额:"+set_money});//设置合计
}