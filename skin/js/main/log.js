$(function(){
    layui.use('table', function() {
        var ape_cols=[
            {field: 'time', title: '操作时间', width: 200, align:'center'},
            {field: 'text', title: '操作内容', width: 500, align:'center'},
            {field: 'user', title: '操作用户', width: 200, align:'center'},
        ];//表格选项
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            even: ape_even,
            cols:  [ape_cols],
            url: '/index/service/log_list',
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
function empty_log(id) {
    layui.use('layer', function() {
        layer.confirm('您确定要清空全部日志？', {
            btn: ['删除', '取消'], //按钮
            offset: '6%',
            shadeClose: true
        }, function() {
            $('.layui-layer-btn0').unbind();//解除绑定事件
            $.post("/index/service/empty_log", {
                "by": 'nodcloud.com'
            }, function(re) {
                if (re === 'success') {
                    re_alert('日志清空成功!');
                } else {
                    dump('服务器响应超时！');
                }
            });
        });
    });
}
