//初始化_表单|时间插件
layui.use(['form','laydate','upload'], function() {
    layui.form;//表单组件
    layui.laydate.render({
        elem: '#time'
    });//时间组件
    layui.upload.render({
        elem: '#up_btn',
        url: '/index/service/up_file',
        accept: 'file', //允许上传的文件类型
        done: function(re) {
            if(re.code==="success"){
                //上传成功
                $('#file').text('已上传').attr('ape',re.file);
            }else{
                //上传失败
                dump(re.code);
            }
        }
    });//上传组件
});
//设置表格-显示列
function set_tabinfo(){
    var main=tabinfo.main;
    for (var i = 0; i < main.length; i++) {
        for(var key in main[i]){
            if(main[i][key][1]==='1'){
                //显示当前列
                $("#table").setGridParam().showCol(key).trigger("reloadGrid");
            }
        }
    }
}
//配置表格
$(document).on("click", "#jqgh_table_rn i", function() {
    ape_set_tabinfo(tabinfo);
});
//增加行
$(document).on("click", ".add_row", function() {
    $("#table").jqGrid('addRowData','APE', new_data);
});
//删除行
$(document).on("click", ".del_row", function() {
    var row_id = $(this).parent().parent().parent().attr('id');//获取当前行ID
    $("#table").jqGrid('delRowData', row_id);//删除当前行
    //至少留一行
    if($("#table tr").length<2){
        $("#table").jqGrid('addRowData','APE', new_data);
    }
    check_tab();
});
//把商品数据添加到主表格中|删除空白行
function add_goods_row(rows_info,type){
    var tmp = $("#table").jqGrid("getGridParam",'data'); //获取表格数据
    var row_id='';
    //取出空白行
    var empty_arr=[];
    var eq_obj={};
    for (var i = 0; i < tmp.length; i++) {
        if(tmp[i].set_id === '-1'){
            empty_arr.push(tmp[i].id);//转存空行
        }else if(rows_info.set_id==tmp[i].set_id){
            //如果相同则转存
            eq_obj=tmp[i];
        }
    }
    //删除空白行
    for (var s = 0; s < empty_arr.length; s++) {
        $("#table").jqGrid('delRowData', empty_arr[s]);
    }
    if(type && !isEmptyObject(eq_obj)){
        //自增数量字段
        $("#table").jqGrid('setCell',eq_obj.id,'nums',((eq_obj.nums-0)+1));
        row_id = eq_obj.id;
    }else{
        row_id = $("#table").jqGrid('addRowData','APE', rows_info);//添加新数据
    }
    return row_id;
}

//批量设置仓库
function set_warehouse(){
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
                            $("#table").jqGrid('setCell',rows[i].id,'warehouse',warehouse_name);//设置行仓库名称
                            $("#table").jqGrid('setCell',rows[i].id,'warehouse_id',warehouse_id);//设置行仓库ID
                        }
                    }
                    layer.closeAll();
                    dump('单据非空行所入仓库已批量设置为-'+warehouse_name);
                }
            });
        });
    });
}
//扫码枪切换
function set_sm(ape) {
    if(sm_state==1){
        //不启用
        sm_state=0;
        $(ape).removeClass().addClass('sm_false');
    }else{
        //启用
        sm_state=1;
        $(ape).removeClass().addClass('sm_true');
    }
    $('#table').jqGrid("nextCell",lastrow,0);
}
//弹出串码输入框
function set_serial(serial_info,ape_obj){
    if(serial_info!==""){
        var serial_arr=serial_info.split(',');
        $("#table").jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
        var html = '<div class="info"><div class="layui-form layui-form-pane re_padding"><div class="layui-form-item"><label class="layui-form-label">串码内容</label><div class="layui-input-block"><select id="serial" class="layui-input" multiple lay-ignore></select></div></div></div></div>';
        layui.use('layer', function() {
            layer.ready(function() {
                layer.open({
                    type: 1,
                    title: '串码详情',
                    skin: 'layui-layer-rim', //加上边框
                    area: ['620px', '200px'], //宽高
                    offset: '6%',
                    content: html,
                    btn: ['保存', '取消'],
                    fixed: false,
                    shadeClose: true,
                    success: function(layero, index) {
                        var tmp_val=$(ape_obj).prev().val();
                        //获取现有数值
                        for (var s = 0; s < serial_arr.length; s++) {
                            $('#serial').append('<option value="'+serial_arr[s]+'">'+serial_arr[s]+'</option>');
                        }
                        if(tmp_val){
                            $('#serial').val(tmp_val.split(',')).select2({placeholder: "请选择串码"});//赋值
                        }else{
                            $('#serial').select2({placeholder: "请选择串码"});//初始化
                        }
                    },
                    btn1: function(layero) {
                        //保存
                        var save_info=$('#serial').select2('val');
                        if(save_info){
                            var rowid=$("#table").jqGrid('getGridParam','selrow');
                            $("#table").jqGrid('setCell',rowid,'serial',save_info.toString());//设置串码文本
                            $("#table").jqGrid('setCell',rowid,'nums',save_info.length);//设置数量
                            dump('已自动计算数量以及金额');
                            layer.closeAll();//关闭层
                            check_tab();
                        }else{
                            dump('串码不可为空');
                        }
                    }
                });
            });
        });
    }else{
        dump('该商品无需录入串码');
    }
}
//批量设置折扣
function set_discount(){
    var html = '<div class="re_padding"><div class="layui-form layui-form-pane"><div class="layui-form-item"><label class="layui-form-label">折扣</label><div class="layui-input-block"><input type="text" class="layui-input" id="set_discount" placeholder="请输入折扣" value="1"></div></div><blockquote class="layui-elem-quote layui-quote-nm">该操作可批量设置所有行的折扣</blockquote></div></div>';
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
                btn1: function(layero) {
                    var discount=$('#set_discount').val();
                    if(!regular_discount.test(discount) || (discount-0)===0){
                        dump('折扣不正确');
                    }else{
                        //批量设置
                        $("#table").jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
                        var rows = $("#table").jqGrid("getGridParam",'data'); //获取表格数据
                        for (var i = 0; i < rows.length; i++) {
                            if(rows[i].set_id !== '-1'){
                                $("#table").jqGrid('setCell',rows[i].id,'discount',discount);//设置行仓库名称
                            }
                        }
                        check_tab();
                        layer.closeAll();
                        dump('单据非空行折扣已批量设置为-'+discount);
                    }
                }
            });
        });
    });
}
//供应商|客户|快捷查看|录入
$(function(){
    var type = 0;
    var supplier_page=['purchase','purchase_info','orpurchase_info','rpurchase_info','repurchase','repurchase_info','payment','payment_info'];
    var customer_page=['sale','sale_info','resale','resale_info','cashier','cashier_info','recashier','recashier_info','itemorder','itemorder_info','exchange','exchange_info','gather','gather_info'];
    //判断页面
    var ape_pathname=window.location.pathname;
    var pathname_arr=ape_pathname.substr(1).split("/"); //字符分割
    if(pathname_arr.length>0){
        for (var i = 0; i < pathname_arr.length; i++) {
            if($.inArray(pathname_arr[i], supplier_page) != -1){
                //供应商页面
                var type = 1;
                break;
            }else if($.inArray(pathname_arr[i], customer_page) != -1){
                //客户页面
                var type = 2;
                break;
            }
        }
    }
    if(type !=0 ){
        jQuery.getScript("/skin/js/main/public_g_k.js", function(data, status, jqxhr) {
            $.post("/index/service/user_root_info", {
                "set": 'basics_edit',
            }, function(re) {
                if(type == 1){
                    //监听供应商页面
                    $('#supplier').parent().prev().on('click', function(){
                        supplier_info($('#supplier').attr('ape'),re.info);
                    });
                }else if(type == 2){
                    //监听客户页面
                    $('#customer').parent().prev().on('click', function(){
                        customer_info($('#customer').attr('ape'),re.info);
                    });
                }
            });
        });
    }
});













