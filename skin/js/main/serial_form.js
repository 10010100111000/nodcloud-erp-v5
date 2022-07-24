$(function(){
    layui.use('table', function() {
        var ape_cols=[
            {field: 'name', title: '商品名称', width: 150, align:'center'},
            {field: 'attr', title: '辅助属性', width: 150, align:'center'},
            {field: 'code', title: '串码', width: 200, align:'center'},
            {field: 'type', title: '串码状态', width: 150, align:'center'},
            {field: 'set', title: '相关操作', width: 100, align:'center',templet: '<div><button class="layui-btn layui-btn-primary layui-btn-sm" onclick="serialinfo_form(&apos;{{d.id}}&apos;,&apos;{{d.name}}&apos;,&apos;{{d.attr}}&apos;,&apos;{{d.code}}&apos;)"><i class="layui-icon">&#xe63c;</i></button> </div>'},
        ];//表格选项
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            even: ape_even,
            cols:  [ape_cols],
            url: '/index/service/serial_list',
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
//导出类型
function export_data(){
    var html = '<div class="info"><div class="layui-form layui-form-pane re_padding form_choice"><ul><li onclick="export_info(0)"><i class="layui-icon">&#xe60a;</i><p>简易报表</p></li><li onclick="export_info(1)"><i class="layui-icon">&#xe63c;</i><p>详细报表</p></li></ul></div></div>';
    layui.use('layer', function() {
        layer.ready(function() {
            layer.open({
                type: 1,
                title: '报表类型',
                skin: 'layui-layer-rim', //加上边框
                area: ['390px', '150px'], //宽高
                offset: '12%',
                content: html,
                fixed: false,
                shadeClose: true,
            });
        });
    });
}
//导出数据
function export_info(type){
    var url_info = push_so_info();
    alert_info('稍等，数据请求中',"/index/export/serial?"+url_info+"&mode="+type,true);
}
//串码出入库明细
function serialinfo_form(id,name,attr,code){
    //iframe层
    layui.use('form', function(){
        layer.open({
          type: 2,
          title: '串码出入库明细',
          offset: '6%',
          area: ['60%', '66%'],
          shadeClose: true,
          content: '/index/main/serialinfo_form?id='+id+'&name='+name+'&attr='+attr+'&code='+code
        }); 
    }); 
}