$(function(){
    layui.use('table', function() {
        var ape_cols=[
            {field: 'time', title: '单据日期', width: 150, align:'center'},
            {field: 'number', title: '单据编号', width: 200, align:'center'},
            {field: 'user', title: '制单人', width: 150, align:'center',templet: '<div>{{d.user.info.name}}</div>'},
            {field: 'storage', title: '入库状态', width: 150, align:'center',templet: '<div>{{d.storage.name}}</div>'},
            {field: 'data', title: '单据备注', width: 150, align:'center'},
            {fixed: 'right',field: 'set', title: '相关操作', width: 215, align:'center',templet: '<div><div class="layui-btn-group"><button class="layui-btn layui-btn-primary layui-btn-sm" onclick="orpurchase_print({{d.id}})"><i class="layui-icon">&#xe911;</i></button> <button class="layui-btn layui-btn-primary layui-btn-sm" onclick="orpurchase_info({{d.id}})"><i class="layui-icon">&#xe912;</i></button> </div></div>'}
        ];//表格选项
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            even: ape_even,
            cols:  [ape_cols],
            url: '/index/service/orpurchaseclass_list',
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
//导出
function export_data(){
    var url_info = push_so_info();
    alert_info('稍等，数据请求中',"/index/export/orpurchase?"+url_info,true);
}
//打印
function orpurchase_print(id){
    //iframe层
    layui.use('form', function(){
        layer.open({
          type: 2,
          title: '采购入库单 - 打印',
          offset: '9%',
          area: ['600px', '350px'],
          content: '/index/main/orpurchase_print?id='+ id
        }); 
    }); 
}
//打开入库单
function orpurchase_info(id){
    //iframe层
    layui.use('form', function(){
        layer.open({
          type: 2,
          title: '采购入库单',
          offset: '2%',
          area: ['98%', '96%'],
          content: '/index/main/orpurchase_info?id='+ id,
          end:function(){
            re_alert('数据已重新加载');//刷新父窗口
          }
        }); 
    }); 
}
