$(function() {
    run_set_tabinfo('#ape_tabinfo',tabinfo);//预处理
    if(!serial_type){
        run_sys_tabinfo('#ape_tabinfo','serial');//单据功能设置-串码
    }
    if(!batch_type){
        run_sys_tabinfo('#ape_tabinfo','batch');//单据功能设置-批次
    }
    layui.use(['form', 'laydate'], function() {
        layui.form.render('select');
    });
    $.fn.zTree.init($("#so_class_info"), {data: {simpleData: {enable: true,idKey: "id",pIdKey: "pid",rootPId: 0}},callback: {onClick: function(event, treeId, treeNode) {$('#so\\|class').val(treeNode.name).attr('ape',treeNode.id);$('.ape_select').hide();}}}, goodsclass_arr);//初始化搜索分类选择
});
//刷新
function tmp_re() {
    window.location.href = window.location.pathname + "?id=" + $('#so\\|id').val();
}
//条件搜索
function so() {
    $("#table").jqGrid('setGridParam',{
    	postData:push_so_arr()
	}).trigger('reloadGrid');
}
//导出
function export_data() {
    var url_info = push_so_info();
    alert_info('稍等，数据请求中', "/index/export/room_check?" + url_info,true);
}
//生成盘点单据
function check_form(){
    var html = '<div class="info"><div class="layui-form layui-form-pane re_padding"><ul style="text-align:center"><li onclick="check_overage()" style="width:48%;float:left;padding:12px 0;border:1px solid #ccc"><i class="layui-icon" style="font-size:36px">&#xe63c;</i><p>盘盈单</p></li><li onclick="check_loss()" style="width:48%;float:right;padding:12px 0;border:1px solid #ccc"><i class="layui-icon" style="font-size:36px">&#xe63c;</i><p>盘亏单</p></li></ul></div></div>';
    layui.use('layer', function() {
        layer.ready(function() {
            layer.open({
                type: 1,
                title: '请选择盘点单类型',
                skin: 'layui-layer-rim', //加上边框
                area: ['390px', '150px'], //宽高
                offset: '12%',
                content: html,
                fixed: false,
                shadeClose: true,
            });
        });
    });
}
//盘盈单 - 其他出库单
function check_overage(){
    $("#table").jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
    var arr = $("#table").jqGrid("getRowData"); //获取表格数据
    var data=[];
    for (var i = 0; i < arr.length; i++) {
        if((arr[i].check-0)>0){
            var tmp={};
            tmp['goods_id']=arr[i].goods_id;
            tmp['attr']=arr[i].attr;
            tmp['warehouse_id']=arr[i].warehouse_id;
            tmp['batch']=arr[i].batch;
            tmp['nums']=arr[i].check;
            data.push(tmp);
        }
    }
    if(data.length>0){
        //判断单据打开情况
        if(parent.$("li[lay-id='otpurchase']").length>0){
            dump('请关闭其他入库单页面');
        }else{
            parent.set_tab('otpurchase','其他入库单','/index/main/otpurchase?checkinfo='+Base64.encode(JSON.stringify(data)));
        }
    }else{
        dump('盘盈单数据为空,请核实!');
    }
}

//盘亏单 - 其他入库单
function check_loss(){
    $("#table").jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
    var arr = $("#table").jqGrid("getRowData"); //获取表格数据
    var data=[];
    for (var i = 0; i < arr.length; i++) {
        if((arr[i].check-0)<0){
            var tmp={};
            tmp['room_id']=arr[i].room_id;
            tmp['nums']=arr[i].check;
            data.push(tmp);
        }
    }
    if(data.length>0){
        //判断单据打开情况
        if(parent.$("li[lay-id='otsale']").length>0){
            dump('请关闭其他出库单页面');
        }else{
            parent.set_tab('otsale','其他出库单','/index/main/otsale?checkinfo='+Base64.encode(JSON.stringify(data)));
        }
    }else{
        dump('盘亏单数据为空,请核实!');
    }
}