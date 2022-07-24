$(function() {
    layui.use('table', function() {
        var ape_cols=[
            {field: 'img', title: '商品图像', width: 90, align:'center',templet: '<div><img src="{{d.img}}" layer-src="{{d.img}}" alt="{{d.goods.info.name}}"/></div>'},
            {field: 'name', title: '商品名称', width: 150, align:'center',templet: '<div>{{d.goods.info.name}}</div>'},
            {field: 'attr', title: '辅助属性', width: 150, align:'center',templet: '<div>{{d.attr.name}}</div>'},
            {field: 'warehouse', title: '所属仓库', width: 150, align:'center',templet: '<div>{{d.warehouse.info.name}}</div>'},
            {field: 'nums', title: '库存数量', width: 150, align:'center'},
            {field: 'serial', title: '商品串码', width: 150, align:'center'},
            {field: 'batch', title: '商品批次', width: 150, align:'center'},
            {field: 'number', title: '商品编号', width: 150, align:'center',templet: '<div>{{d.goods.info.number}}</div>'},
            {field: 'class', title: '商品分类', width: 150, align:'center',templet: '<div>{{d.goods.info.class.info.name}}</div>'},
            {field: 'unit', title: '商品单位', width: 150, align:'center',templet: '<div>{{d.goods.info.unit.info.name}}</div>'},
            {field: 'brand', title: '商品品牌', width: 150, align:'center',templet: '<div>{{d.goods.info.brand.info.name}}</div>'},
            {field: 'code', title: '条形码', width: 150, align:'center',templet: '<div>{{d.goods.info.code}}</div>'},
            {field: 'spec', title: '规格型号', width: 150, align:'center',templet: '<div>{{d.goods.info.spec}}</div>'},
            {field: 'location', title: '商品货位', width: 150, align:'center',templet: '<div>{{d.goods.info.location}}</div>'},
            {fixed: 'right',field: 'stocktip', title: '库存预警', width: 90, align:'center',templet: '<div>{{d.goods.info.stocktip}}</div>'},
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
            url: '/index/service/roomwarning_list',
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
    alert_info('稍等，数据请求中', "/index/export/room_warning?" + url_info,true);
}
