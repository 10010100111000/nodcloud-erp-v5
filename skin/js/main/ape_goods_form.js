$(function(){
    layui.use('table', function() {
        var ape_cols=[
            {field: 'name', title: '商品名称', width: 200, align:'center'},
            {field: 'attr', title: '辅助属性', width: 120, align:'center'},
            {field: 'warehouse', title: '所属仓库', width: 120, align:'center'},
            {field: 'batch', title: '商品批次', width: 120, align:'center'},
            {field: 'number', title: '商品编号', width: 120, align:'center'},
            {field: 'class', title: '商品分类', width: 120, align:'center'},
            {field: 'unit', title: '商品单位', width: 120, align:'center'},
            {field: 'brand', title: '商品品牌', width: 120, align:'center'},
            {field: 'spec', title: '规格型号', width: 120, align:'center'},
            {field: 'location', title: '商品货位', width: 120, align:'center'},
            {field: 'stocktip', title: '库存预警', width: 120, align:'center'},
            {field: 'sale', title: '销货金额', width: 120, align:'center'},
            {field: 'cashier', title: '零售金额', width: 120, align:'center'},
            {field: 'sales_revenue', title: '销售收入', width: 120, align:'center'},
            {field: 'sales_cost', title: '销售成本', width: 120, align:'center'},
            {field: 'sales_maori', title: '销售毛利', width: 120, align:'center'},
            {field: 'gross_interest_rate', title: '销售毛利率', width: 120, align:'center'}
        ];//表格选项
        var tmp_tabinfo=JSON.stringify(tabinfo);//转存表格配置|防止和设置冲突|JSON编码
        tmp_tabinfo=JSON.parse(tmp_tabinfo);//|JSON解码
        tmp_tabinfo['main'].push({'batch':['商品批次',(batch_type ? '1':'0')]});
        var col = run_layui_tabinfo(tmp_tabinfo,ape_cols);//初始化表格配置
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            even: ape_even,
            cols:  [col],
            url: '/index/service/ape_goods_list',
            page: true,
            limits: [30,60,90,150,300],
            method: 'post',
            where: push_so_arr(),
            done: function(res, curr, count){
            	table_tip('#ape_table',[
            		{'text':'销货总金额','key':'sale'},
            		{'text':'零售总金额','key':'cashier'},
            		{'text':'销售总收入','key':'sales_revenue'},
            		{'text':'销售总成本','key':'sales_cost'},
            		{'text':'销售总毛利','key':'sales_maori'}
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
    alert_info('稍等，数据请求中',"/index/export/ape_goods_form?"+url_info,true);
}
