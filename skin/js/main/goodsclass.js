$(function() {
    //初始化树状表格
    $('#tab').treeTable({
        theme:'vsStyle',
        expandLevel : 1,
        onSelect : function($treeTable, id) {
            window.console && console.log('onSelect:' + id);
        }
    });
});
//条件搜索
function so() {
    var url_info = push_so_info();
    window.location.href = "/index/main/goodsclass?"+url_info;
}
//删除
function del_goodsclass(id) {
    if(root_del){
        layui.use('layer', function() {
            layer.confirm('您确定要删除？', {
                btn: ['删除', '取消'], //按钮
                offset: '6%',
                shadeClose: true
            }, function() {
                $('.layui-layer-btn0').unbind();//解除绑定事件
                $.post("/index/service/del_goodsclass", {
                    "id": id
                }, function(re) {
                    if (re === 'success') {
                        alert_info('删除成功!');
                    } else if(re === 'error') {
                        dump('存在下级分类,删除失败！');
                    }else if(re === 'ape_error'){
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
function goodsclass_info(id) {
    if(root_edit){
        var html = '<div class="info"><div class="layui-form layui-form-pane re_padding"><div class="layui-form-item"><label class="layui-form-label">分类名称</label><div class="layui-input-block"><input type="text" id="name" placeholder="请输入分类名称" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">所属分类</label><div class="layui-input-block"><input type="text" id="pid" placeholder="请选择所属分类" class="layui-input" onClick="$(this).next().show();"><div class="ape_select"><ul id="class_info" class="ztree"></ul></div></div></div><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text" id="data" placeholder="请输入备注信息" class="layui-input"></div></div></div></div>';
        layui.use('layer', function() {
            layer.ready(function() {
                layer.open({
                    type: 1,
                    title: '分类详情',
                    skin: 'layui-layer-rim', //加上边框
                    area: ['500px', '360px'], //宽高
                    offset: '6%',
                    content: html,
                    btn: ['保存', '取消'],
                    fixed: false,
                    shadeClose: true,
                    success: function(layero, index) {
                        //弹出后回调
                        //初始化分类选择
                        $.fn.zTree.init($("#class_info"), {
                            data: {
                                simpleData: {
                                    enable: true,
                                    idKey: "id",
                                    pIdKey: "pid",
                                    rootPId: 0
                                }
                            },
                            callback: {
                                onClick: function(event, treeId, treeNode) {
                                    $('#pid').val(treeNode.name).attr('ape',treeNode.id);
                                    $('.ape_select').hide();
                                }
                            }
                        }, goodsclass_arr);
                        //获取信息
                        if (id !== undefined) {
                            $.post("/index/service/goodsclass_info", {
                                "id": id,
                            }, function(re) {
                                $('#name').val(re.name);
                                $('#pid').val(re.pid.name).attr('ape',re.pid.ape);
                                $('#data').val(re.data);
                                //设置节点选中
                                var zTree = $.fn.zTree.getZTreeObj("class_info");
                                var node = zTree.getNodeByParam("id",re.pid.ape);
                                zTree.selectNode(node);
                            });
                        }
                    },
                    btn1: function(layero) {
                        //保存
                        var name = $('#name').val();
                        var pid = $('#pid').attr('ape');
                        var data = $('#data').val();
                        if (name === "") {
                            dump('分类名称不可为空!');
                        }else if(pid === undefined){
                            dump('所属分类不可为空!');
                        }else if(id!==undefined && pid==id){
                            dump('所属分类不可为当前分类!');
                        } else {
                            //提交信息
                            $('.layui-layer-btn0').unbind();//解除绑定事件
                            $.post("/index/service/save_goodsclass", {
                                "id": id,
                                "name": name,
                                "pid": pid,
                                "data": data
                            }, function(re) {
                                if (re === "success") {
                                    alert_info('分类详情保存成功!');
                                } else {
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