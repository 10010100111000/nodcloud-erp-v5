//表格点击时间
layui.use(['table','layer'], function() {
    var table=layui.table;
    var layer=layui.layer;
    layui.table.on('tool', function(obj){
        if(obj.event=='auditinginfo'){
            var html='<div class="info"><div class="layui-form layui-form-pane re_padding"><div class="layui-form-item"><label class="layui-form-label">审核状态</label><div class="layui-input-block"><input type="text" id="auditing_type" class="layui-input" disabled="disabled" value="未审核"></div></div><div class="layui-form-item"><label class="layui-form-label">审核人</label><div class="layui-input-block"><input type="text" id="auditing_user" class="layui-input" disabled="disabled" value="-"></div></div><div class="layui-form-item"><label class="layui-form-label">审核时间</label><div class="layui-input-block"><input type="text" id="auditing_time" class="layui-input" disabled="disabled" value="-"></div></div></div></div>';
            layer.open({
                type: 1,
                title: '审核详情',
                skin: 'layui-layer-rim', //加上边框
                area: ['360px', '220px'], //宽高
                offset: '6%',
                content: html,
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    //弹出后回调
                    if(obj.data.type.ape==1){
                        $('#auditing_type').val('-').val('已审核');
                        $('#auditing_time').val('-').val(new Date((obj.data.auditingtime)*1000).toLocaleString());
                        $.post("/index/service/user_info", {"id": obj.data.auditinguser}, function(re) {
                            $('#auditing_user').val(re.name);
                        });
                    }
                }
            });
        }
    });
});