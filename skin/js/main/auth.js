var customer_arr;
var supplier_arr;
var warehouse_arr;
var user_arr;
var account_arr;
$(function(){
    $.ajax({
        type: 'POST',
        async: false,
        url: '/index/Service/ape_auth_info',
        data: {'by':'nodcloud.com'},
		dataType: "json",
        success: function(re){
            customer_arr=re['customer_arr'];
            supplier_arr=re['supplier_arr'];
            warehouse_arr=re['warehouse_arr'];
            user_arr=re['user_arr'];
            account_arr=re['account_arr'];
        }
    });
    layui.use('table', function() {
        var width=$(window).width();
        var ape_cols=[
            {field: 'name', title: '职员名称', width: 200, align:'center'},
            {field: 'user', title: '职员账号', width: 200, align:'center'},
            {field: 'tel', title: '手机号码', width: 200, align:'center'},
            {field: 'set', title: '相关操作', width: 120, align:'center',templet: '<div><button class="layui-btn layui-btn-primary layui-btn-sm" onclick="set_auth({{d.id}})"><i class="layui-icon">&#xe614;</i></button></div>'},
        ];//表格选项
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            even: ape_even,
            cols:  [ape_cols],
            url: '/index/service/auth_list',
            page: true,
            limits: [30,60,90,150,300],
            method: 'post',
            where: push_so_arr(),
        });//渲染表格 
    }); 
});
//条件搜索
function so() {
    layui.use('table', function() {
        layui.table.reload('ape_table',{
            where: push_so_arr(),
            page:1
        });
    });
}
//显示详情
function set_auth(id) {
    if(root_edit){
        var html = '<div class="info"><div class="layui-form layui-form-pane re_padding"><div class="layui-form-item"><label class="layui-form-label">客户</label><div class="layui-input-block"><select id="customer" class="layui-input" multiple lay-ignore></select></div></div><div class="layui-form-item"><label class="layui-form-label">供应商</label><div class="layui-input-block"><select id="supplier" class="layui-input" multiple lay-ignore></select></div></div><div class="layui-form-item"><label class="layui-form-label">仓库</label><div class="layui-input-block"><select id="warehouse" class="layui-input" multiple lay-ignore></select></div></div><div class="layui-form-item"><label class="layui-form-label">制单人</label><div class="layui-input-block"><select id="user" class="layui-input" multiple lay-ignore></select></div></div><div class="layui-form-item"><label class="layui-form-label">资金账户</label><div class="layui-input-block"><select id="account" class="layui-input" multiple lay-ignore></select></div></div></div></div>';
        layui.use('layer', function() {
            layer.ready(function() {
                layer.open({
                    type: 1,
                    title: '数据授权详情',
                    skin: 'layui-layer-rim', //加上边框
                    area: ['500px', '412px'], //宽高
                    offset: '6%',
                    content: html,
                    btn: ['保存', '取消'],
                    fixed: false,
                    shadeClose: true,
                    success: function(layero, index) {
                        //填充数据
                        for (var i = 0; i < customer_arr.length; i++) {
                            $('#customer').append('<option value="'+customer_arr[i].id+'">'+customer_arr[i].name+'</option>');
                        }
                        for (var o = 0; o < supplier_arr.length; o++) {
                            $('#supplier').append('<option value="'+supplier_arr[o].id+'">'+supplier_arr[o].name+'</option>');
                        }
                        for (var p = 0; p < warehouse_arr.length; p++) {
                            $('#warehouse').append('<option value="'+warehouse_arr[p].id+'">'+warehouse_arr[p].name+'</option>');
                        }
                        for (var u = 0; u < user_arr.length; u++) {
                            $('#user').append('<option value="'+user_arr[u].id+'">'+user_arr[u].name+'</option>');
                        }
                        for (var e = 0; e < account_arr.length; e++) {
                            $('#account').append('<option value="'+account_arr[e].id+'">'+account_arr[e].name+'</option>');
                        }
                        //弹出后回调
                        $.post("/index/service/user_info", {
                            "id": id,
                        }, function(re) {
                            if(re.auth){
                                $('#customer').val(re.auth.customer).select2({placeholder: "请选择客户"});//赋值
                                $('#supplier').val(re.auth.supplier).select2({placeholder: "请选择供应商"});//赋值
                                $('#warehouse').val(re.auth.warehouse).select2({placeholder: "请选择仓库"});//赋值
                                $('#user').val(re.auth.user).select2({placeholder: "请选择制单人"});//赋值
                                $('#account').val(re.auth.account).select2({placeholder: "请选择资金账户"});//赋值
                            }else{
                                $('#customer').select2({placeholder: "请选择客户"});//赋值
                                $('#supplier').select2({placeholder: "请选择供应商"});//赋值
                                $('#warehouse').select2({placeholder: "请选择仓库"});//赋值
                                $('#user').select2({placeholder: "请选择制单人"});//赋值
                                $('#account').select2({placeholder: "请选择资金账户"});//赋值
                            }
                            
                        });
                    },
                    btn1: function(layero) {
                        //保存
                        var auth={};
                        auth['customer'] = $('#customer').select2('val');
                        auth['supplier'] = $('#supplier').select2('val');
                        auth['warehouse'] = $('#warehouse').select2('val');
                        auth['user'] = $('#user').select2('val');
                        auth['account'] = $('#account').select2('val');
                        //提交信息
                        $.post("/index/service/save_user_auth", {
                            "id": id,
                            "auth": auth
                        }, function(re) {
                            if (re === "success") {
                                re_alert('数据授权保存成功!');
                            }else{
                                alert_info('服务器响应超时!');
                            }
                        });
                    }
                });
            });
        });
    }else{
        dump('很遗憾,您无权操作!');
    }     
}