$(function(){
    layui.use('table', function() {
        var width=$(window).width();
        var ape_cols=[
            {field: 'name', title: '职员名称', width: 200, align:'center'},
            {field: 'user', title: '职员账号', width: 200, align:'center'},
            {field: 'tel', title: '手机号码', width: 200, align:'center'},
            {field: 'data', title: '备注信息', width: 200, align:'center'},
            {field: 'set', title: '相关操作', width: 120, align:'center',templet: '<div><div class="layui-btn-group"><button class="layui-btn layui-btn-primary layui-btn-sm" onclick="user_info({{d.id}})"><i class="layui-icon">&#xe642;</i></button> <button class="layui-btn layui-btn-primary layui-btn-sm" onclick="del_user({{d.id}})"><i class="layui-icon">&#xe640;</i></button></div></div>'},
        ];//表格选项
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            even: ape_even,
            cols:  [ape_cols],
            url: '/index/service/user_list',
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
//删除
function del_user(id) {
    if(root_del){
        layui.use('layer', function() {
            layer.confirm('您确定要删除？', {
                btn: ['删除', '取消'], //按钮
                offset: '6%',
                shadeClose: true 
            }, function() {
                $('.layui-layer-btn0').unbind();//解除绑定事件
                $.post("/index/service/del_user", {
                    "id": id
                }, function(re) {
                    if (re === 'success') {
                        re_alert('删除成功!');
                    }else if(re === 'error'){
                        dump('当前数据已经发生业务操作,删除失败!');
                    } else {
                        dump('服务器响应超时！');
                    }
                });
            });
        });
    }else{
        dump('很遗憾,您无权操作!');
    } 
}
//显示详情
function user_info(id) {
    if(root_edit){
        var html = '<div class="info"><div class="layui-form layui-form-pane re_padding"><div class="layui-form-item"><label class="layui-form-label">职员名称</label><div class="layui-input-block"><input type="text" id="name" placeholder="请输入职员名称" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">职员账号</label><div class="layui-input-block"><input type="text" id="user" placeholder="请输入职员账号" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">职员密码</label><div class="layui-input-block"><input type="text" id="pwd" placeholder="请输入职员密码" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">手机号码</label><div class="layui-input-block"><input type="text" id="tel" placeholder="请输入职员手机号码" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text" id="data" placeholder="请输入备注信息" class="layui-input"></div></div></div></div>';
        layui.use('layer', function() {
            layer.ready(function() {
                layer.open({
                    type: 1,
                    title: '职员详情',
                    skin: 'layui-layer-rim', //加上边框
                    area: ['500px', '390px'], //宽高
                    offset: '6%',
                    content: html,
                    btn: ['保存', '取消'],
                    fixed: false,
                    shadeClose: true,
                    success: function(layero, index) {
                        //弹出后回调
                        if (id !== undefined) {
                            $.post("/index/service/user_info", {
                                "id": id,
                            }, function(re) {
                                $('#name').val(re.name);
                                $('#user').val(re.user);
                                $('#tel').val(re.tel);
                                $('#data').val(re.data);
                            });
                        }
                    },
                    btn1: function(layero) {
                        //保存
                        var name = $('#name').val();
                        var user = $('#user').val();
                        var pwd = $('#pwd').val();
                        var data = $('#data').val();
                        var tel = $('#tel').val();
                        if (name === "") {
                            dump('职员名称不可为空!');
                        } else if (user === "") {
                            dump('职员账号不可为空!');
                        }else if (tel === "") {
                            dump('手机号码不可为空!');
                        }else if (!(/^1[3|4|5|7|8][0-9]\d{4,8}$/.test(tel))) {
                            dump('手机号码不正确!');
                        }else if(id === undefined && pwd===""){
                            dump('职员密码不可为空!');
                        } else {
                            //提交信息
                            $('.layui-layer-btn0').unbind();//解除绑定事件
                            $.post("/index/service/save_user", {
                                "id": id,
                                "name": name,
                                "user": user,
                                "pwd": pwd,
                                "tel": tel,
                                "data": data
                            }, function(re) {
                                if (re === "success") {
                                    re_alert('职员详情保存成功!');
                                }else{
                                    alert_info('服务器响应超时!');
                                }
                            });
                        }
                    }
                });
            });
        });
    }else{
        dump('很遗憾,您无权操作!');
    } 
}