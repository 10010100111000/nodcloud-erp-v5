$(function(){
    layui.use('table', function() {
        var width=$(window).width();
        var ape_cols=[
            {field: 'name', title: '仓库名称', width: 200, align:'center'},
            {field: 'number', title: '仓库编号', width: 200, align:'center'},
            {field: 'contacts', title: '联系人', width: 200, align:'center'},
            {field: 'tel', title: '联系电话', width: 200, align:'center'},
            {field: 'data', title: '备注信息', width: 200, align:'center'},
            {field: 'set', title: '相关操作', width: 215, align:'center',templet: '<div><div class="layui-btn-group"><button class="layui-btn layui-btn-primary layui-btn-sm" onclick="warehouse_info({{d.id}})"><i class="layui-icon">&#xe642;</i></button> <button class="layui-btn layui-btn-primary layui-btn-sm" onclick="del_warehouse({{d.id}})"><i class="layui-icon">&#xe640;</i></button></div></div>'},
        ];//表格选项
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            even: ape_even,
            cols:  [ape_cols],
            url: '/index/service/warehouse_list',
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
function del_warehouse(id) {
    if(root_del){
        layui.use('layer', function() {
            layer.confirm('您确定要删除？', {
                btn: ['删除', '取消'], //按钮
                offset: '6%'
            }, function() {
                $('.layui-layer-btn0').unbind();//解除绑定事件
                $.post("/index/service/del_warehouse", {
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
function warehouse_info(id) {
    if(root_edit){
        var html = '<div class="info"><div class="layui-form layui-form-pane re_padding"><table style="width:100%;border-collapse:inherit;border-spacing:3px;font-size:inherit"><tr><td><div class="layui-form-item"><label class="layui-form-label">仓库名称</label><div class="layui-input-block"><input type="text" id="name" placeholder="请输入仓库名称" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">仓库编号</label><div class="layui-input-block"><input type="text" id="number" placeholder="请输入仓库编号" class="layui-input"></div></div></td></tr><tr><td><div class="layui-form-item"><label class="layui-form-label">联系人</label><div class="layui-input-block"><input type="text" id="contacts" placeholder="请输入联系人" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">联系电话</label><div class="layui-input-block"><input type="text" id="tel" placeholder="请输入联系电话" class="layui-input"></div></div></td></tr><tr><td><div class="layui-form-item"><label class="layui-form-label">仓库地址</label><div class="layui-input-block"><input type="text" id="add" placeholder="请输入仓库地址" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text" id="data" placeholder="请输入备注信息" class="layui-input"></div></div></td></tr></table></div></div>';
        layui.use('layer', function() {
            layer.ready(function() {
                layer.open({
                    type: 1,
                    title: '仓库详情',
                    skin: 'layui-layer-rim', //加上边框
                    area: ['700px', '300px'], //宽高
                    offset: '6%',
                    content: html,
                    btn: ['保存', '取消'],
                    fixed: false,
                    shadeClose: true,
                    success: function(layero, index) {
                        //弹出后回调
                        if (id !== undefined) {
                            $.post("/index/service/warehouse_info", {
                                "id": id,
                            }, function(re) {
                                $('#name').val(re.name);
                                $('#number').val(re.number);
                                $('#contacts').val(re.contacts);
                                $('#tel').val(re.tel);
                                $('#add').val(re.add);
                                $('#data').val(re.data);
                            });
                        }
                    },
                    btn1: function(layero) {
                        //保存
                        var name = $('#name').val();
                        var number = $('#number').val();
                        var contacts = $('#contacts').val();
                        var tel = $('#tel').val();
                        var add = $('#add').val();
                        var data = $('#data').val();
                        if (name === "") {
                            dump('仓库名称不可为空!');
                        } else {
                            //提交信息
                            $('.layui-layer-btn0').unbind();//解除绑定事件
                            $.post("/index/service/save_warehouse", {
                                "id": id,
                                "name": name,
                                "number": number,
                                "contacts": contacts,
                                "tel": tel,
                                "add": add,
                                "data": data
                            }, function(re) {
                                if (re === "success") {
                                    re_alert('仓库详情保存成功!');
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