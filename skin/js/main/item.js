$(function(){
    layui.use('table', function() {
        var width=$(window).width();
        var ape_cols=[
            {field: 'name', title: '服务名称', width: (width*0.20), align:'center'},
            {field: 'price', title: '服务价格', width: (width*0.10), align:'center'},
            {field: 'data', title: '备注信息', width: (width*0.20), align:'center'},
            {field: 'set', title: '相关操作', width: 215, align:'center',templet: '<div><div class="layui-btn-group"><button class="layui-btn layui-btn-primary layui-btn-sm" onclick="item_info({{d.id}})"><i class="layui-icon">&#xe642;</i></button> <button class="layui-btn layui-btn-primary layui-btn-sm" onclick="del_item({{d.id}})"><i class="layui-icon">&#xe640;</i></button></div></div>'},
        ];//表格选项
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            even: ape_even,
            cols:  [ape_cols],
            url: '/index/service/item_list',
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
function del_item(id) {
    if(root_del){
        layui.use('layer', function() {
            layer.confirm('您确定要删除？', {
                btn: ['删除', '取消'], //按钮
                offset: '6%',
                shadeClose: true
            }, function() {
                $('.layui-layer-btn0').unbind();//解除绑定事件
                $.post("/index/service/del_item", {
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
function item_info(id) {
    if(root_edit){    
        var html = '<div class="info"><div class="layui-form layui-form-pane re_padding"><div class="layui-form-item"><label class="layui-form-label">服务名称</label><div class="layui-input-block"><input type="text" id="name" placeholder="请输入账户名称" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">服务价格</label><div class="layui-input-block"><input type="text" id="price" placeholder="请输入服务价格" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text" id="data" placeholder="请输入备注信息" class="layui-input"></div></div></div></div>';
        layui.use(['layer','laydate'], function() {
            layer.ready(function() {
                layer.open({
                    type: 1,
                    title: '服务详情',
                    skin: 'layui-layer-rim', //加上边框
                    area: ['500px', '280px'], //宽高
                    offset: '6%',
                    content: html,
                    btn: ['保存', '取消'],
                    fixed: false,
                    shadeClose: true,
                    success: function(layero, index) {
                        //弹出后回调
                        if (id !== undefined) {
                            $.post("/index/service/item_info", {
                                "id": id,
                            }, function(re) {
                                $('#name').val(re.name);
                                $('#price').val(re.price);
                                $('#data').val(re.data);
                            });
                        }
                    },
                    btn1: function(layero) {
                        //保存
                        var name = $('#name').val();
                        var price = $('#price').val();
                        var data = $('#data').val();
                        if (name === "") {
                            dump('服务名称不可为空!');
                        }else if(!regular_price.test(price)){
                            dump('服务价格不正确!');
                        } else {
                            //提交信息
                            $('.layui-layer-btn0').unbind();//解除绑定事件
                            $.post("/index/service/save_item", {
                                "id": id,
                                "name": name,
                                "price": price,
                                "data": data
                            }, function(re) {
                                if (re === "success") {
                                    re_alert('服务详情保存成功!');
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