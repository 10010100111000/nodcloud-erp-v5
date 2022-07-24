$(function(){
    layui.use('table', function() {
        var ape_cols=[
            {field: 'time', title: '操作时间', width: 120, align:'center'},
            {field: 'type', title: '单据类型', width: 150, align:'center',templet: '<div>{{d.type.name}}</div>'},
            {field: 'number', title: '单据编号', width: 200, align:'center'},
            {field: 'set', title: '资金操作', width: 100, align:'center',templet: '<div>{{d.set.name}}</div>'},
            {field: 'money', title: '资金数额', width: 100, align:'center'},
            {field: 'user', title: '操作用户', width: 100, align:'center',templet: '<div>{{d.user.info.name}}</div>'}
        ];//表格选项
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            even: ape_even,
            cols:  [ape_cols],
            url: '/index/service/accountform_list',
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

//刷新
function tmp_re() {
    window.location.href=window.location.pathname+"?id="+$('#so\\|id').val();
}

//导出
function export_data(){
    var url_info = push_so_info();
    alert_info('稍等，数据请求中',"/index/export/account_form?"+url_info,true);
}
