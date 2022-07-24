//通用显示服务信息-ITEM
function item_info_show(state,so_val) {
    var html = '<div class="goods_info_list"><div class="layui-form layui-form-pane"><table class="so_info"><tr><td><div class="layui-form-item"><label class="layui-form-label">服务名称</label><div class="layui-input-block"><input type="text" id="so_name" placeholder="服务名称/首字母" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">服务备注</label><div class="layui-input-block"><input type="text" id="so_data" placeholder="服务备注" class="layui-input"></div></div></td><td><button class="layui-btn layui-btn-primary" onclick="so()" style="margin:0"><i class="layui-icon">&#xe615;</i></button></td></tr></table></div><hr><table id="item_lists"></table></div>';
    layui.use(['layer','form','laypage','table'], function() {
        var form = layui.form;
        var table = layui.table;
        layer.ready(function() {
            layer.open({
                type: 1,
                title: '服务信息',
                skin: 'layui-layer-rim', //加上边框
                area: ['860px', '520px'], //宽高
                offset: '6%',
                content: html,
                btn: ['确定', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    var ape_cols=[
                        {checkbox: true},
                        {field: 'name', title: '服务名称', width: 200, align:'center'},
                        {field: 'price', title: '服务价格', width: 90, align:'center'},
                        {field: 'data', title: '服务备注', width: 150, align:'center'}
                    ];//默认表格选项
                    table.render({
                        id: 'item_lists',
                        elem: '#item_lists',
                        height:'340',
                        even: ape_even,
                        cols:  [ape_cols],
                        url: '/index/service/item_info_list',
                        page: true,
                        limits: [30,60,90,150,300],
                        method: 'post',
                        where: where_info()
                    });//渲染表格
                    //扫码选择接口
                    if(state){
                        $('#so_name').val(so_val);
                        so();//触发搜索
                    }
                    form.render();
                },
                btn1: function(layero) {
                    //保存
                    var checkStatus = table.checkStatus('item_lists'); //test即为基础参数id对应的值
                    var item_arr = checkStatus.data;//获取选中行的数据
                    if(item_arr.length === 0){
                        dump('您还未选择服务项目');
                    }else{
                        add_item(item_arr,true);//回调以及选择的服务数组
                        layer.closeAll();
                    }
                }
            });
        });
    });
}
//搜索
function so(){
    layui.use('table', function() {
        layui.table.reload('item_lists',{
          where: where_info()
        });
    });
}
function where_info(){
    return {
        "name": $('#so_name').val(),
        "data": $('#so_data').val()
    };
}