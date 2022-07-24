var lastrow;//最后编辑行
var lastcell;//最后编辑列
$("#table").jqGrid({
    url: "/index/service/room_check_info",
	datatype: "JSON",
	mtype: "POST",
    colNames: ['仓储ID','商品ID','辅助属性标识','所属仓库ID','所属仓库','商品名称','辅助属性','商品品牌','商品编号','商品分类','规格型号','条形码','商品单位','库存预警','商品货位','赠送积分','零售名称','商品批次','商品串码','当前库存','盘点库存','盘盈盘亏'],
    colModel: [
        {
            name: 'room_id',
            hidden:true
        },{
            name: 'goods_id',
            hidden:true
        },{
            name: 'attr',
            hidden:true
        }, {
            name: 'warehouse_id',
            hidden:true
        }, {
            name: 'warehouse_name',
            sortable: false,
            hidden:true
        }, {
            name: 'goods_name',
            sortable: false,
            align:"center"
        }, {
            name: 'attr_name',
            sortable: false,
            align:"center",
            hidden:true
        }, {
            name: 'brand',
            sortable: false,
            align:"center",
            hidden:true
        }, {
            name: 'number',
            sortable: false,
            align:"center",
            hidden:true
        }, {
            name: 'class',
            sortable: false,
            align:"center",
            hidden:true
        }, {
            name: 'spec',
            sortable: false,
            align:"center",
            hidden:true
        }, {
            name: 'code',
            sortable: false,
            align:"center",
            hidden:true
        }, {
            name: 'unit',
            sortable: false,
            align:"center",
            hidden:true
        }, {
            name: 'stocktip',
            sortable: false,
            align:"center",
            hidden:true
        }, {
            name: 'location',
            sortable: false,
            align:"center",
            hidden:true
        }, {
            name: 'integral',
            sortable: false,
            align:"center",
            hidden:true
        }, {
            name: 'retail_name',
            sortable: false,
            align:"center",
            hidden:true
        }, {
            name: 'batch',
            sortable: false,
            align:"center",
            hidden:true
        }, {
            name: 'serial',
            sortable: false,
            align:"center",
            hidden:true
        }, {
            name: 'stock',
            sortable: false,
            align:"center"
        }, {
            name: 'nums',
            sortable: false,
            align:"center",
            editable: true
        }, {
            name: 'check',
            sortable: false,
            align:"center"
        }
    ],
    width:$(document).width()-30,
    cellsubmit: 'clientArray', //开启编辑保存
	height: $(document).height()*0.80,
	cellEdit: true, //开启单元格编辑
    rownumbers: true, //开启行号
    shrinkToFit: false,//滚动条
    pager: "#pager",
    rowNum: 30,//默认条数
    rowList: [30 , 60 , 90 , 150,300],//条数选择
    beforeEditCell:function(rowid,cellname,v,iRow,iCol){
        //保存最后编辑行|列
        lastrow = iRow;//行
        lastcell = iCol;//列
    },
    afterSaveCell:function(rowid, cellname, value, iRow, iCol){
        //保存触发数据检测
        if(cellname==="nums"){
            if(value!=='0' && !regular_positive.test(value)){
                dump('第'+iRow+'行盘点库存输入错误');
                $("#table").jqGrid('setCell',rowid,'check','输入错误');//设置错误信息
            }else{
                var rowData = $("#table").jqGrid('getRowData',rowid);
                $("#table").jqGrid('setCell',rowid,'check',cal((value-0)-(rowData.stock-0)));//设置盘盈盘亏数量
            }
        }
    }
});
//配置表格
$('#jqgh_table_rn').html('<i class="layui-icon">&#xe614;</i>');
set_tabinfo();//设置表格-显示列
if(batch_type){
    $("#table").setGridParam().showCol('batch').trigger("reloadGrid");//显示批次
}
if(serial_type){
    $("#table").setGridParam().showCol('serial').trigger("reloadGrid");//显示串号
}