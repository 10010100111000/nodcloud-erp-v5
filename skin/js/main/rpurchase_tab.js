var new_data = {set_id:'-1'};//默认数据
var lastrow;//最后编辑行
var lastcell;//最后编辑列
$("#table").jqGrid({
    datatype: "local",
    colNames: ['OID','商品ID','所入仓库ID','辅助属性标识','串码商品', '商品信息', '辅助属性','所入仓库<span onclick="set_warehouse();">（批量）</span>','商品品牌','商品编号','商品分类','规格型号','条形码','商品单位','库存预警','商品货位','赠送积分','零售名称','商品串码','数量','采购单价','采购金额','商品批次','备注信息'],
    colModel: [
        {
            name: 'oid',
            hidden:true
        },{
            name: 'set_id',
            hidden:true
        },{
            name:'warehouse_id',
            hidden:true
        },{
            name:'attr_ape',
            hidden:true
        },{
            name:'serialtype',
            hidden:true
        }, {
            name: 'name',
            width:'150px',
            align:"center",
        }, {
            name: 'attr',
            align:"center",
        }, {
            name: 'warehouse',
            width:'150px',
            edittype:"select",
            editoptions:warehouse_arr.grid,
            sortable: false,
            align:"center",
            editable: true,
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
            name: 'serial',
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
            name: 'batch',
            sortable: false,
            align:"center",
            editable: true,
            hidden:true
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
        //修改触发
        if(cellname==="warehouse"){
            for (var i = 0; i < warehouse_arr.db.length; i++) {
                if(warehouse_arr.db[i].id==value){
                    $("#table").jqGrid('setCell',rowid,'warehouse_id',warehouse_arr.db[i].id);//设置所入仓库ID
                    break;
                }
            }
        }
        //保存触发数据检测
        check_tab();
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
//设置串码
function serial_elem (value, options) {
    return "<div class='goods_input'><input type='text' value='"+value+"' ><i class='layui-icon serial_info'>&#xe63c;</i></div>";
};
//保存串码
function serial_value(elem, operation, value) {
    var val=$(elem).find('input').val();
    return val;
};
//弹出串码输入框
$(document).on("click", ".serial_info", function() {
    $("#table").jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
    var ape=$(this);
    var html = '<div class="info"><div class="layui-form layui-form-pane re_padding"><textarea rows="10" placeholder="录入多个串码时每行一个" id="serial" class="layui-textarea"></textarea></div></div>';
    layui.use('layer', function() {
        layer.ready(function() {
            layer.open({
                type: 1,
                title: '串码详情',
                skin: 'layui-layer-rim', //加上边框
                area: ['630px', '350px'], //宽高
                offset: '6%',
                content: html,
                btn: ['保存', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    //获取现有数值
                    var val = ape.prev().val();
                    if(val!==""){
                        var tmp_arr = val.split(','); //字符分割
                        for (var i = 0; i < tmp_arr.length; i++) {
                            $('#serial').val($('#serial').val()+tmp_arr[i]+'\n');//追加赋值并换行
                        }
                    }
                },
                btn1: function(layero) {
                    //保存
                    var tmp_val=$('#serial').val().split('\n');
                    var arr =[];
                    for (var s = 0; s < tmp_val.length; s++) {
                        //排除空白行
                        if(tmp_val[s]!==""){
                            if(!regular_code.test(tmp_val[s])){
                                dump('第'+(s+1)+'行串码不正确');
                                return false;
                            }else{
                                arr.push(tmp_val[s]);//转存数组
                            }
                        }
                    }
                    var rowid=$("#table").jqGrid('getGridParam','selrow');
                    $("#table").jqGrid('setCell',rowid,'serial',arr.toString());//设置串码文本
                    $("#table").jqGrid('setCell',rowid,'nums',arr.length);//设置数量
                    layer.closeAll();//关闭层
                    check_tab();
                    dump('已自动计算数量以及金额');
                }
            });
        });
    });
});

//添加商品-传入商品ID数组
function add_goods(arr){
    $("#table").jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
    for (var i = 0; i < arr.length; i++) {
        rows_info={};
        rows_info['oid'] = arr[i].oid; //oid
        rows_info['set_id'] = arr[i].id; //商品ID
        rows_info['serialtype'] = arr[i].serialtype; //串号详情
        rows_info['name'] = arr[i].name; //商品名称
        //判断是否存在辅助属性
        if(arr[i].attr_type == 1){
            rows_info['attr'] = arr[i]['attr_name'];
            rows_info['attr_ape'] = arr[i]['attr_ape'];
        }else{
            rows_info['attr'] = '无';
            rows_info['attr_ape'] = '-1';
        }
        rows_info['warehouse'] = arr[i].warehouse.info.name; //所入仓库
        rows_info['warehouse_id'] = arr[i].warehouse_id; //所入仓库ID
        rows_info['brand'] = arr[i].brand.info.name; //商品品牌
        rows_info['number'] = arr[i].number; //商品编号
        rows_info['class'] = arr[i].class.info.name; //商品分类
        rows_info['spec'] = arr[i].spec; //规格型号
        rows_info['code'] = arr[i].code; //条形码
        rows_info['unit'] = arr[i].unit.info.name; //商品单位
        rows_info['stocktip'] = arr[i].stocktip; //库存预警
        rows_info['location'] = arr[i].location; //商品货位
        rows_info['integral'] = arr[i].integral; //赠送积分
        rows_info['nums'] = arr[i].nums; //总数量
        rows_info['price'] = arr[i].price; //单价
        rows_info['total'] = arr[i].total;; //总价
        rows_info['serial'] = arr[i].serial; //串号详情
        rows_info['batch'] = arr[i].batch; //批次
        rows_info['data'] = arr[i].data; //批次
        add_goods_row(rows_info,false); //添加到主表中
    }
}
//检查表格内容
function check_tab(cal_type){
    var cal_type=cal_type||true;
    var arr = $("#table").jqGrid("getGridParam",'data'); //获取表格数据
    var serial_arr=[];
    //取出空白行
    for (var i = 0; i < arr.length; i++) {
        if(arr[i].set_id !== '-1'){
            $("#table tr").eq(i+1).find('td').css('color','#000');//初始化颜色
            //开始判断
           if(arr[i].warehouse_id==='0'){
                dump('第'+(i+1)+'行未选择所入仓库');
                $("#table tr").eq(i+1).find('td[aria-describedby="table_warehouse"]').css('color','#F00');
                return false;
            }else if(arr[i].serialtype!='0' && !arr[i].serial){
                dump('第'+(i+1)+'行为串码商品，串码不可为空');
                return false;
            }else if(!regular_positive.test(arr[i].nums) && arr[i].nums != 0){
                dump('第'+(i+1)+'行数量填写错误');
                return false;
            }else if(!regular_price.test(arr[i].price)){
                dump('第'+(i+1)+'行采购单价不正确');
                return false;
            }else{
                //如果存在串码
                if(arr[i].serial){
                    var tmp_serial = arr[i].serial.split(',');
                    if(tmp_serial.length === (arr[i].nums-0)){
                        for (var s = 0; s < tmp_serial.length; s++) {
                            if(tmp_serial[s]!==""){
                                if(!regular_code.test(tmp_serial[s])){
                                    dump('第'+(i+1)+'行串码第'+(s+1)+'条不正确');
                                    return false;
                                }
                            }
                            serial_arr.push(tmp_serial[s]);//转存串号
                        }
                    }else{
                        dump('第'+(i+1)+'行数量与串码个数不符,已自动计算数量');
                        arr[i].nums = tmp_serial.length;//覆盖数量
                        $("#table").jqGrid('setCell',arr[i].id,'nums',arr[i].nums);//设置数量
                        cal_row(cal_type,arr[i].id,arr[i].nums,arr[i].price);//计算行数据
                        return false;
                    }
                }
                cal_row(cal_type,arr[i].id,arr[i].nums,arr[i].price);//计算行数据
            }
        }
    }
    if(serial_arr.length>0){
        if(isRepeat(serial_arr)){
            dump('串码重复，请核实!');
            return false;
        }
    }
    return true;
}

function cal_row(cal_type,row_id,row_nums,row_price){
    //开始计算购货总额
    var total=cal((row_nums-0)*(row_price-0));
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
