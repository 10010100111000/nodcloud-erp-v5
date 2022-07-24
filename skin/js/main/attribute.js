$(function(){
    layui.use('table', function() {
        var width=$(window).width();
        var ape_cols=[
            {field: 'name', title: '属性名称', width: 200, align:'center'},
            {field: 'data', title: '备注信息', width: 200, align:'center'},
            {field: 'set', title: '相关操作', width: 120, align:'center',templet: '<div><div class="layui-btn-group"><button class="layui-btn layui-btn-primary layui-btn-sm" onclick="attribute_info({{d.id}})"><i class="layui-icon">&#xe642;</i></button> <button class="layui-btn layui-btn-primary layui-btn-sm" onclick="del_attribute({{d.id}},this,true)"><i class="layui-icon">&#xe640;</i></button></div></div>'},
        ];//表格选项
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            even: ape_even,
            cols:  [ape_cols],
            url: '/index/service/attribute_list',
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
function del_attribute(id,ape,type) {
    if(root_del){
        layui.use('layer', function() {
            layer.confirm('您确定要删除？', {
                btn: ['删除', '取消'], //按钮
                offset: '6%',
                shadeClose: true
            }, function() {
                $('.layui-layer-btn0').unbind();//解除绑定事件
                $.post("/index/service/del_attribute", {
                    "id": id,
                    'type':type
                }, function(re) {
                    if (re === 'success') {
                        if(type){
                            //删除主属性
                            re_alert('删除属性成功');
                        }else{
                            //删除副属性
                            $(ape).parent().parent().remove();
                        }
                        layer.close(layer.index);//关闭弹层
                    }else if(re==="error"){
                        dump('存在副属性，删除失败!');
                    }else if(re === 'ape_error'){
                        dump('当前数据已经发生业务操作,删除失败!');
                    }else {
                        dump('服务器响应超时!');
                    }
                });
            });
        });
    }else{
        dump('很遗憾,您无权操作!');
    }     
}
//显示详情
function attribute_info(id) {
    if(root_edit){
        var html = '<div class="info"><div class="layui-form layui-form-pane re_padding"><div class="layui-form-item"><label class="layui-form-label">属性名称</label><div class="layui-input-block"><input type="text"id="name"placeholder="请输入属性名称"class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text"id="data"placeholder="请输入备注信息"class="layui-input"></div></div><div class="edit_attribute"><p>副属性信息</p><hr/><div class="layui-form-item"><label class="layui-form-label">副属性名称</label><div class="layui-input-block"><input type="text" id="names" placeholder="请输入副属性名称"class="layui-input re_row_input"><label class="layui-form-label re_row_label"onclick="add_attribute();">添加</label></div></div><table class="layui-table"><thead><tr><th style="width:75%">副属性名称</th><th style="width:25%">操作</th></tr></thead><tbody id="tabs"></tbody></table></div></div></div>';
        layui.use('layer', function() {
            layer.ready(function() {
                var index=layer.open({
                    type: 1,
                    title: '属性详情',
                    skin: 'layui-layer-rim', //加上边框
                    area: ['600px', '240px'], //宽高
                    offset: '6%',
                    content: html,
                    btn: ['保存', '取消'],
                    fixed: false,
                    shadeClose: true,
                    success: function(layero, index) {
                        $('.edit_attribute').hide();//初始化隐藏
                        //弹出后回调
                        if (id !== undefined) {
                            $('.re_row_label').attr('ape',id);//转存id
                            $('.edit_attribute').show();//显示副属性
                            layer.style(index, {
                              width: '600px',
                              height: '480px',
                            });
                            $.post("/index/service/attribute_info", {
                                "id": id,
                            }, function(re) {
                                //设置主属性
                                $('#name').val(re.one.name);
                                $('#data').val(re.one.data);
                                //设置副属性
                                var tmp=re.two;
                                for (var i = 0; i < tmp.length; i++) {
                                    var tr='<tr><td>'+tmp[i].name+'</td><td><i class="layui-icon" onclick="del_attribute('+tmp[i].id+',this,false);">&#xe640;</i></td></tr>'
                                    $('#tabs').append(tr);
                                }
                            });
                        }
                    },
                    btn1: function(layero) {
                        //保存
                        var name = $('#name').val();
                        var data = $('#data').val();
                        if (name === "") {
                            dump('属性名称不可为空!');
                        } else {
                            //提交信息
                            $('.layui-layer-btn0').unbind();//解除绑定事件
                            $.post("/index/service/save_attribute", {
                                "id": id,
                                "pid": 0,
                                "name": name,
                                "data": data
                            }, function(re) {
                                if (re.state === "success") {
                                    re_alert('属性详情保存成功!');
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
//新增副辅助属性
function add_attribute(){
    var pid=$('.re_row_label').attr('ape');
    var name=$('#names').val();
    if(name===""){
        dump('副辅助属性名称不可为空!');
    }else{
        //提交信息
        $.post("/index/service/save_attribute", {
            "id": 0,
            "pid": pid,
            "name": name,
        }, function(re) {
            if (re.state === "success") {
                $('#names').val('');
                var html='<tr><td>'+name+'</td><td><i class="layui-icon" onclick="del_attribute('+re.ape+',this,false);">&#xe640;</i></td></tr>'
                $('#tabs').append(html);
            }else{
                alert_info('服务器响应超时!');
            }
        });
    }
}