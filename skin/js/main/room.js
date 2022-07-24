$(function() {
    layui.use(['table','layer'], function() {
        var ape_cols=[
            {field: 'img', title: '商品图像', width: 90, align:'center',templet: '<div><img src="{{d.img}}" layer-src="{{d.img}}" alt="{{d.goods.info.name}}"/></div>'},
            {field: 'name', title: '商品名称', width: 200, align:'center',templet: '<div>{{d.goods.info.name}}</div>'},
            {field: 'attr', title: '辅助属性', width: 150, align:'center',templet: '<div>{{d.attr.name}}</div>'},
            {field: 'warehouse', title: '所属仓库', width: 150, align:'center',templet: '<div>{{d.warehouse.info.name}}</div>'},
            {field: 'nums', title: '库存数量', width: 90, align:'center'},
            {field: 'serial', title: '商品串码', width: 150, align:'center'},
            {field: 'batch', title: '商品批次', width: 150, align:'center'},
            {field: 'number', title: '商品编号', width: 150, align:'center',templet: '<div>{{d.goods.info.number}}</div>'},
            {field: 'class', title: '商品分类', width: 150, align:'center',templet: '<div>{{d.goods.info.class.info.name}}</div>'},
            {field: 'unit', title: '商品单位', width: 150, align:'center',templet: '<div>{{d.goods.info.unit.info.name}}</div>'},
            {field: 'brand', title: '商品品牌', width: 150, align:'center',templet: '<div>{{d.goods.info.brand.info.name}}</div>'},
            {field: 'code', title: '条形码', width: 150, align:'center',templet: '<div>{{d.goods.info.code}}</div>'},
            {field: 'spec', title: '规格型号', width: 150, align:'center',templet: '<div>{{d.goods.info.spec}}</div>'},
            {field: 'location', title: '商品货位', width: 150, align:'center',templet: '<div>{{d.goods.info.location}}</div>'},
            {field: 'stocktip', title: '库存预警', width: 150, align:'center',templet: '<div>{{d.goods.info.stocktip}}</div>'},
            {fixed: 'right',field: 'set', title: '相关操作', width: 100, align:'center',templet: '<div><button class="layui-btn layui-btn-primary layui-btn-sm" onclick="room_form({{d.id}})"><i class="layui-icon">&#xe63c;</i></button> </div>'},
        ];//默认表格选项
        var tmp_tabinfo=JSON.stringify(tabinfo);//转存表格配置|防止和设置冲突|JSON编码
        tmp_tabinfo=JSON.parse(tmp_tabinfo);//|JSON解码
        tmp_tabinfo['main'].push({'serial':['商品串码',(serial_type ? '1':'0')]});
        tmp_tabinfo['main'].push({'batch':['商品批次',(batch_type ? '1':'0')]});
        var col = run_layui_tabinfo(tmp_tabinfo,ape_cols);//初始化表格配置
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            even: ape_even,
            cols:  [col],
            url: '/index/service/room_list',
            page: true,
            limits: [30,60,90,150,300],
            method: 'post',
            where: push_so_arr(),
            done: function(res, curr, count){
                table_tip('#ape_table',[
            		{'text':'库存总数量','key':'nums'},
            	]);
                layer.photos({
                    photos: '.layui-table-view',
                    anim: 5
                }); 
            }
        });
    });
    $.fn.zTree.init($("#so_class_info"), {data: {simpleData: {enable: true,idKey: "id",pIdKey: "pid",rootPId: 0}},callback: {onClick: function(event, treeId, treeNode) {$('#so\\|class').val(treeNode.name).attr('ape',treeNode.id);$('.ape_select').hide();}}}, goodsclass_arr);//初始化搜索分类选择
});
//刷新
function tmp_re() {
    window.location.href = window.location.pathname + "?id=" + $('#so\\|id').val();
}
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
function export_data() {
    var url_info = push_so_info();
    alert_info('稍等，数据请求中', "/index/export/room?" + url_info,true);
}
//商品出入库明细
function room_form(id){
    //iframe层
    layui.use('form', function(){
        layer.open({
          type: 2,
          title: '商品出入库明细',
          offset: '6%',
          area: ['60%', '66%'],
          content: '/index/main/room_form?id='+ id
        }); 
    }); 
}