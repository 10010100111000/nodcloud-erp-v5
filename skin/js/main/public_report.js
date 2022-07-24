//初始化_表单|时间插件
layui.use(['form','laydate'], function() {
    layui.form;//表单组件
    var laydate = layui.laydate;
    laydate.render({
        elem: '#so\\|start_time'
    });
    laydate.render({
        elem: '#so\\|end_time'
    });
});