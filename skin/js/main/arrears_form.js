$(function(){
    layui.use('table', function() {
        var ape_cols=[
            {field: 'number', title: '单位编号', width: 150, align:'center'},
            {field: 'name', title: '单位名称', width: 200, align:'center'},
            {field: 'type', title: '单位类型', width: 150, align:'center',},
            {field: 'money', title: '欠款金额', width: 150, align:'center'},
        ];//表格选项
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            even: ape_even,
            cols:  [ape_cols],
            url: '/index/service/arrears_list',
            page: true,
            limits: [30,60,90,150,300],
            method: 'post',
            where: push_so_arr(),
            done: function(res, curr, count){
            	table_tip('#ape_table',[
            		{'text':'欠款总金额','key':'money'}
            	]);
            }
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
//导出
function export_data(){
    var url_info = push_so_info();
    alert_info('稍等，数据请求中',"/index/export/arrears_form?"+url_info,true);
}
