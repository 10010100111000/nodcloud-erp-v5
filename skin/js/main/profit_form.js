$(function(){
    layui.use('table', function() {
        var ape_cols=[
            {field: 'time', title: '单据日期', width: 120, align:'center'},
            {field: 'type', title: '单据类型', width: 120, align:'center'},
            {field: 'number', title: '单据编号', width: 200, align:'center'},
            {field: 'customer', title: '客户', width: 120, align:'center'},
            {field: 'user', title: '制单人', width: 120, align:'center'},
            {field: 'nums', title: '数量', width: 120, align:'center'},
            {field: 'sales_revenue', title: '销售收入', width: 120, align:'center'},
            {field: 'selling_cost', title: '销售成本', width: 120, align:'center'},
            {field: 'gross_margin', title: '销售毛利', width: 120, align:'center'},
            {field: 'gross_profit_margin', title: '毛利率', width: 120, align:'center'},
            {field: 'discount', title: '优惠金额', width: 120, align:'center'},
            {field: 'net_profit', title: '销售净利润', width: 120, align:'center'},
            {field: 'net_profit_margin', title: '净利润率', width: 120, align:'center'},
            {field: 'receivable', title: '应收金额', width: 120, align:'center'},
            {field: 'money', title: '实收金额', width: 120, align:'center'},
            {field: 'data', title: '单据备注', width: 200, align:'center'},
        ];//表格选项
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            even: ape_even,
            cols:  [ape_cols],
            url: '/index/service/profit_list',
            page: true,
            limits: [30,60,90,150,300],
            method: 'post',
            where: push_so_arr(),
            done: function(res, curr, count){
            	table_tip('#ape_table',[
            		{'text':'销售总收入','key':'sales_revenue'},
            		{'text':'销售总成本','key':'selling_cost'},
            		{'text':'销售总毛利','key':'gross_margin'},
            		{'text':'优惠总金额','key':'discount'},
            		{'text':'销售总净利润','key':'net_profit'},
            		{'text':'应收总金额','key':'receivable'},
            		{'text':'实收总金额','key':'money'}
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
    alert_info('稍等，数据请求中',"/index/export/profit_form?"+url_info,true);
}
