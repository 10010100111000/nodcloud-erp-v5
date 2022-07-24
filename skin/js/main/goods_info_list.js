var brand_arr; //品牌数据
var unit_arr; //单位数据
var goodsclass_arr; //分类数据
$(function(){
    $.ajax({
        type: 'POST',
        async: false,
        url: '/index/Service/choice_goods_info',
        data: {'by':'nodcloud.com'},
		dataType: "json",
        success: function(re){
            brand_arr=re['brand_arr'];
            unit_arr=re['unit_arr'];
            goodsclass_arr=re['goodsclass_arr'];
        }
    });
});
//通用显示商品信息-GOODS
function goods_info_show(state,so_val) {
    var html = '<div class="goods_info_list"><div class="layui-form layui-form-pane"><table class="so_info"><tr><td><div class="layui-form-item"><label class="layui-form-label">商品名称</label><div class="layui-input-block"><input type="text" id="so_name" placeholder="商品名称/首字母" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">条形码</label><div class="layui-input-block"><input type="text" id="so_code" placeholder="条形码" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">商品分类</label><div class="layui-input-block"><input type="text" id="so_class" placeholder="请选择商品分类" value="全部分类" class="layui-input" onClick="$(this).next().show();" ape="0"><div class="ape_select"><ul id="class_info" class="ztree"></ul></div></div></div></td><td><button class="layui-btn layui-btn-primary" onclick="more(this)" ape="hide"><i class="layui-icon">&#xe61a;</i></button> <button class="layui-btn layui-btn-primary" onclick="so()" style="margin:0"><i class="layui-icon">&#xe615;</i></button></td></tr><tr style="display:none"><td><div class="layui-form-item"><label class="layui-form-label">商品编号</label><div class="layui-input-block"><input type="text" id="so_number" placeholder="商品编号" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">规格型号</label><div class="layui-input-block"><input type="text" id="so_spec" placeholder="规格型号" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">默认仓库</label><div class="layui-input-block"><select id="so_warehouse" class="layui-input" lay-search><option value="0">全部仓库</option></select></div></div></td></tr><tr style="display:none"><td><div class="layui-form-item"><label class="layui-form-label">商品单位</label><div class="layui-input-block"><select id="so_unit" class="layui-input" lay-search><option value="0">全部单位</option></select></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">商品品牌</label><div class="layui-input-block"><select id="so_brand" class="layui-input" lay-search><option value="0">全部品牌</option></select></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text" id="so_data" placeholder="商品编号" class="layui-input"></div></div></td></tr></table></div><hr><table id="goods_lists"></table></div>';
    layui.use(['layer','form','laypage','table'], function() {
        var form = layui.form;
        var table = layui.table;
        layer.ready(function() {
            layer.open({
                type: 1,
                title: '商品信息',
                skin: 'layui-layer-rim', //加上边框
                area: ['860px', '520px'], //宽高
                offset: '6%',
                content: html,
                btn: ['确定', '取消','<i class="layui-icon re_left">&#xe614;</i>'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    var ape_cols=[
                        {checkbox: true},
                        {field: 'img', title: '商品图像', width: 120, align:'center',templet: '<div><img src="{{d.img}}" layer-src="{{d.img}}" alt="{{d.name}}"/></div>'},
                        {field: 'name', title: '商品名称', width: 200, align:'center'},
                        {field: 'number', title: '商品编号', width: 150, align:'center'},
                        {field: 'class', title: '商品分类', width: 150, align:'center',templet: '<div>{{d.class.info.name}}</div>'},
                        {field: 'unit', title: '商品单位', width: 150, align:'center',templet: '<div>{{d.unit.info.name}}</div>'},
                        {field: 'brand', title: '商品品牌', width: 150, align:'center',templet: '<div>{{d.brand.info.name}}</div>'},
                        {field: 'warehouse', title: '默认仓库', width: 150, align:'center',templet: '<div>{{d.warehouse.info.name}}</div>'},
                        {field: 'buy', title: '购货价格', width: 90, align:'center'},
                        {field: 'sell', title: '销货价格', width: 90, align:'center'},
                        {field: 'retail', title: '零售价格', width: 90, align:'center'},
                        {field: 'code', title: '条形码', width: 150, align:'center'},
                        {field: 'spec', title: '规格型号', width: 150, align:'center'},
                        {field: 'stocktip', title: '库存预警', width: 150, align:'center'},
                        {field: 'location', title: '商品货位', width: 150, align:'center'},
                        {field: 'integral', title: '商品积分', width: 150, align:'center'},
                        {field: 'data', title: '备注信息', width: 150},
                    ];//默认表格选项
                    var col = run_layui_tabinfo(goodstabinfo,ape_cols);//初始化表格配置
                    table.render({
                        id: 'goods_lists',
                        elem: '#goods_lists',
                        height:'340',
                        even: ape_even,
                        cols:  [col],
                        url: '/index/service/goods_info_list',
                        page: true,
                        limits: [30,60,90,150,300],
                        method: 'post',
                        where: where_info(),
                        done: function(res, curr, count){
                            layer.photos({
                                photos: '.layui-table-view',
                                anim: 5
                            });
                        }
                    });//渲染表格
                    layero.find('.re_left').parent().attr('style','float:left;line-height: 32px;')//左浮动按钮
                    //初始化分类选择
                    $.fn.zTree.init($("#class_info"), {data: {simpleData: {enable: true,idKey: "id",pIdKey: "pid",rootPId: 0}},callback: {onClick: function(event, treeId, treeNode) {$('#so_class').val(treeNode.name).attr('ape',treeNode.id);$('.ape_select').hide();}}}, goodsclass_arr);
                    //循环填充仓库
                    for (var i = 0; i < warehouse_arr.db.length; i++) {
                        $('#so_warehouse').append('<option value="'+warehouse_arr.db[i].id+'">'+warehouse_arr.db[i].name+'<option>');
                    }
                    //循环填充商品品牌
                    for (var i = 0; i < brand_arr.length; i++) {
                        $('#so_brand').append('<option value="'+brand_arr[i].id+'">'+brand_arr[i].name+'<option>');
                    }
                    //循环填充商品单位
                    for (var i = 0; i < unit_arr.length; i++) {
                        $('#so_unit').append('<option value="'+unit_arr[i].id+'">'+unit_arr[i].name+'<option>');
                    }
                    //扫码选择接口
                    //设置搜索内容
                    if(state==0){
                        $('#so_name').val(so_val);
                        so();//触发搜索
                    }else if(state==1){
                        $('#so_code').val(so_val);
                        so();//触发搜索
                    }
                    
                    form.render();
                },
                btn1: function(layero) {
                    //保存
                    var checkStatus = table.checkStatus('goods_lists'); //test即为基础参数id对应的值
                    var goods_arr = checkStatus.data;//获取选中行的数据
                    if(goods_arr.length === 0){
                        dump('您还未选择商品');
                    }else{
                        add_goods(goods_arr,true);//回调以及选择的商品数组
                        layer.closeAll();
                    }
                },
                btn3: function(layero) {
                    ape_set_tabinfo(goodstabinfo);
                    return false;
                }
            });
        });
    });
}
//搜索
function so(){
    layui.use('table', function() {
        layui.table.reload('goods_lists',{
          where: where_info()
        });
    });
}
function where_info(){
    return {
        "name": $('#so_name').val(),//商品名称
        "code": $('#so_code').val(),//条形码
        "class": $('#so_class').attr('ape'),//商品分类
        "number": $('#so_number').val(),//商品编号
        "spec": $('#so_spec').val(),//规格型号
        "warehouse": $('#so_warehouse').val(),//默认仓库
        "brand": $('#so_brand').val(),//商品品牌
        "unit": $('#so_unit').val(),//商品单位
        "data": $('#so_data').val(),//备注信息
    };
}