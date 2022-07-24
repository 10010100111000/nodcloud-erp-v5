var brand_arr;
var goodsclass_arr;
var supplier_arr;
var unit_arr;
var warehouse_arr;
var attribute_arr;
$(function(){
    $.ajax({
        type: 'POST',
        async: false,
        url: '/index/Service/ape_goods_info',
        data: {'by':'nodcloud.com'},
		dataType: "json",
        success: function(re){
            brand_arr=re['brand_arr'];
            goodsclass_arr=re['goodsclass_arr'];
            supplier_arr=re['supplier_arr'];
            unit_arr=re['unit_arr'];
            warehouse_arr=re['warehouse_arr'];
            attribute_arr=re['attribute_arr'];
        }
    });
    layui.use('table', function() {
        var ape_cols=[
            {field: 'name', title: '商品名称', width: 200, align:'center',fixed: true},
            {field: 'number', title: '商品编号', width: 150, align:'center'},
            {field: 'class', title: '商品分类', width: 150, align:'center',templet: '<div>{{d.class.info.name}}</div>'},
            {field: 'unit', title: '商品单位', width: 150, align:'center',templet: '<div>{{d.unit.info.name}}</div>'},
            {field: 'brand', title: '商品品牌', width: 150, align:'center',templet: '<div>{{d.brand.info.name}}</div>'},
            {field: 'warehouse', title: '默认仓库', width: 150, align:'center',templet: '<div>{{d.warehouse.info.name}}</div>'},
            {field: 'buy', title: '购货价格', width: 100, align:'center'},
            {field: 'sell', title: '销货价格', width: 100, align:'center'},
            {field: 'retail', title: '零售价格', width: 100, align:'center'},
            {field: 'code', title: '条形码', width: 150, align:'center'},
            {field: 'spec', title: '规格型号', width: 150, align:'center'},
            {field: 'stocktip', title: '库存预警', width: 150, align:'center'},
            {field: 'location', title: '商品货位', width: 150, align:'center'},
            {field: 'integral', title: '商品积分', width: 150, align:'center'},
            {field: 'data', title: '备注信息', width: 150, align:'center'},
            {fixed: 'right',field: 'set', title: '相关操作', width: 120, align:'center',templet: '<div><div class="layui-btn-group"><button class="layui-btn layui-btn-primary layui-btn-sm" onclick="goods_info({{d.id}})"><i class="layui-icon">&#xe642;</i></button> <button class="layui-btn layui-btn-primary layui-btn-sm" onclick="del_goods({{d.id}})"><i class="layui-icon">&#xe640;</i></button></div></div>'}
        ];//表格选项
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            even: ape_even,
            cols:  [ape_cols],
            url: '/index/service/goods_list',
            page: true,
            limits: [30,60,90,150,300],
            method: 'post',
            where: push_so_arr(),
        });//渲染表格 
    }); 
    $.fn.zTree.init($("#so_class_info"), {data: {simpleData: {enable: true,idKey: "id",pIdKey: "pid",rootPId: 0}},callback: {onClick: function(event, treeId, treeNode) {$('#so\\|class').val(treeNode.name).attr('ape',treeNode.id);$('.ape_select').hide();}}}, goodsclass_arr);//初始化搜索分类选择
    goodsclass_arr.shift();//删除数组中全部分类选项
    $('body').on('mousedown','.goods_imgs',function(re){
        if (re.which == 3) {
            if(window.confirm('确定删除该图像?')){
                var src= $(this).attr('src');
                $.post("/index/service/del_goods_img", {
                    "file": src
                }, function(re) {
                    if (re === 'success') {
                        $('[src="'+src+'"]').parent().remove();
                    }else {
                        dump('服务端图片删除失败!');
                    }
                });
                
            }
        }
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
//删除
function del_goods(id) {
    if(root_del){
        layui.use('layer', function() {
            layer.confirm('您确定要删除？', {
                btn: ['删除', '取消'], //按钮
                offset: '6%',
                shadeClose: true 
            }, function() {
                $.post("/index/service/del_goods", {
                    "id": id
                }, function(re) {
                    if (re === 'success') {
                        re_alert('删除成功!');
                    }else if(re === 'error'){
                        dump('当前数据已经发生业务操作,删除失败!');
                    }  else {
                        dump('服务器响应超时！');
                    }
                });
            });
        });
    }else{
        dump('很遗憾,您无权操作!');
    }    
}
//显示详情
function goods_info(id) {
    if(root_edit){
        var html = '<div class="info"><div class="layui-tab layui-tab-brief" style="margin-top:0" lay-filter="goods"><ul class="layui-tab-title"><li lay-id="goods_main" class="layui-this">基础信息</li><li lay-id="goods_attr">辅助属性</li><li lay-id="goods_more">图文详情</li></ul><div class="layui-tab-content"><div class="layui-tab-item layui-show"><div class="layui-form layui-form-pane"><table id="goods_info"><tr><td><div class="layui-form-item"><label class="layui-form-label">商品名称</label><div class="layui-input-block"><input type="text" id="name" placeholder="请输入商品名称" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">商品分类</label><div class="layui-input-block"><input type="text" id="class" placeholder="请选择商品分类" class="layui-input" onClick="$(this).next().show();" ape="0"><div class="ape_select"><ul id="class_info" class="ztree"></ul></div></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">规格型号</label><div class="layui-input-block"><input type="text" id="spec" placeholder="请输入规格型号" class="layui-input"></div></div></td></tr><tr><td><div class="layui-form-item"><label class="layui-form-label">商品编号</label><div class="layui-input-block"><input type="text" id="number" placeholder="请输入商品编号" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">条形码</label><div class="layui-input-block"><input type="text" id="code" placeholder="请输入条形码" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">商品货位</label><div class="layui-input-block"><input type="text" id="location" placeholder="请输入商品货位" class="layui-input"></div></div></td></tr><tr><td><div class="layui-form-item"><label class="layui-form-label">商品品牌</label><div class="layui-input-block"><select id="brand" lay-search><option value="">请选择商品品牌</option><option value="0">暂不关联</option></select></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">默认仓库</label><div class="layui-input-block"><select id="warehouse" lay-search><option value="">请选择默认仓库</option><option value="0">暂不关联</option></select></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">商品单位</label><div class="layui-input-block"><select id="unit" lay-search><option value="">请选择商品单位</option><option value="0">暂不关联</option></select></div></div></td></tr><tr><td><div class="layui-form-item"><label class="layui-form-label">购货价格</label><div class="layui-input-block"><input type="text" id="buy" placeholder="请输入购货价格" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">销货价格</label><div class="layui-input-block"><input type="text" id="sell" placeholder="请输入销货价格" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">零售价格</label><div class="layui-input-block"><input type="text" id="retail" placeholder="请输入零售价格" class="layui-input"></div></div></td></tr><tr><td><div class="layui-form-item"><label class="layui-form-label">库存预警</label><div class="layui-input-block"><input type="text" id="stocktip" placeholder="库存预警阀值" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">赠送积分</label><div class="layui-input-block"><input type="text" id="integral" placeholder="请输入赠送积分" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text" id="data" placeholder="请输入备注信息" class="layui-input"></div></div></td></tr><tr><td ape="serialtype" style="display:none"><div class="layui-form-item"><label class="layui-form-label">串码商品</label><div class="layui-input-block"><select id="serialtype"><option value="0">否</option><option value="1">是</option></select></div></div></td></tr></table></div></div><div class="layui-tab-item layui-form"><table class="layui-table" style="margin-top:0"><thead><tr><th style="width:20%">属性名称</th><th style="width:80%">属性内容</th></tr></thead><tbody id="attr_info"></tbody></table><table class="layui-table" style="margin-top:0"><thead><tr><th style="width:30%">规格名称</th><th style="width:15%">购货价格</th><th style="width:15%">销货价格</th><th style="width:15%">零售价格</th><th style="width:15%">预警阀值</th><th style="width:10%">启用</th></tr></thead><tbody id="attribute_info"></tbody></table></div><div class="layui-tab-item layui-form layui-form-pane"><div class="layui-form-item"><label class="layui-form-label">零售名称</label><div class="layui-input-block"><input type="text" id="retail_name" placeholder="请输入商品零售名称" class="layui-input"></div></div><div class="layui-form-item layui-form-text"><label class="layui-form-label">商品图片<small>提示：右键图片即可删除</small></label><div class="layui-input-block"><div class="layui-textarea"><ul class="goods_img"><li id="up_btn"><button id="up_img" class="layui-btn layui-btn-primary">上传图像</button></li><div style="clear:both"></div></ul></div></div></div><div class="layui-form-item layui-form-text"><label class="layui-form-label">商品详情</label><div class="layui-input-block"><script type="text/plain" id="details" style="height:360px"></script></div></div></div></div></div></div>';
        layui.use(['layer','form','element','upload'], function() {
            var form=layui.form;//定义Form
            layer.ready(function() {
                layer.open({
                    type: 1,
                    title: '商品详情',
                    skin: 'layui-layer-rim', //加上边框
                    area: ['860px', '460px'], //宽高
                    offset: '6%',
                    content: html,
                    btn: ['保存', '取消'],
                    fixed: false,
                    maxmin: true,
                    shadeClose: true,
                    success: function(layero, index) {
                        //弹出后回调
                        //功能配置
                        if(serial_type){
                            $('#goods_info td[ape="serialtype"]').show();//显示串号功能
                        }
                        sel_opt();//填充下拉列表
                        attr_info();//初始化辅助属性赋值
                        form.render(); //重新渲染表单元素
                        //监听-辅助属性选择
                        form.on('checkbox(attr)', function(data){
                            attr_set();//选择辅助属性
                            form.render('checkbox');//单独渲染checkbox
                        });
                        
                        layui.upload.render({
                            elem: '#up_img',
                            url: '/index/service/up_goods_img',
                            done: function(re) {
                                if(re.code==="success"){
                                    //上传成功
                                    $('#up_btn').after('<li><img class="goods_imgs" src="'+re.img+'"></li>');
                                }else{
                                    //上传失败
                                    dump(re.code);
                                }
                            }
                        });
                        $('#stocktip').val(sys_threshold);//默认阈值
                        UM.getEditor('details');//实例化编辑器
                        if (id !== undefined) {
                            $.post("/index/service/goods_info", {
                                "id": id,
                            }, function(re) {
                                //赋值基本属性
                                $('#name').val(re.name);
                                $('#brand').val(re.brand.ape);
                                $('#number').val(re.number);
                                $('#class').val(re.class.info.name).attr('ape',re.class.ape);
                                //设置节点选中
                                $.fn.zTree.getZTreeObj("class_info").selectNode($.fn.zTree.getZTreeObj("class_info").getNodeByParam("id", re.class.ape, null));
                                $('#spec').val(re.spec);
                                $('#code').val(re.code);
                                $('#buy').val(re.buy);
                                $('#sell').val(re.sell);
                                $('#retail').val(re.retail);
                                $('#unit').val(re.unit.ape);
                                $('#warehouse').val(re.warehouse.ape);
                                $('#stocktip').val(re.stocktip);
                                $('#location').val(re.location);
                                $('#integral').val(re.integral);
                                $('#data').val(re.data);
                                $('#serialtype').val(re.serialtype);
                                //赋值辅助属性
                                if(re.more.length!==0){
                                    set_attr(re.more);
                                }
                                //赋值图文详情
                                $('#retail_name').val(re.retail_name);
                                if(re.imgs!==null){
                                    for (var i = 0;  i< re.imgs.length; i++) {
                                        $('#up_btn').after('<li><img class="goods_imgs" src="'+re.imgs[i]+'"></li>');
                                    }
                                }
                                if(re.details!==null){
                                    UM.getEditor('details').setContent(re.details);
                                }
                                form.render();
                            });
                        }
                    },
                    btn1: function(layero) {
                        var goods_main = post_main_info();
                        if(goods_main){
                            var goods_attr = post_attr_info();
                            if(goods_attr){
                                var post={};
                                post['id']=id;
                                post['main']=goods_main;
                                post['attr']=goods_attr;
                                post['more']=post_more_info();
                                //提交信息
                                $('.layui-layer-btn0').unbind();//解除绑定事件
                                $.post("/index/service/save_goods", {
                                    "goods_info":post
                                }, function(re) {
                                    if (re === "success") {
                                        re_alert('商品详情保存成功!');
                                    }else{
                                        alert_info('服务器响应超时!');
                                    }
                                });
                            }
                        }
                    },
                    end: function(index, layero) {
                        UM.getEditor('details').destroy(); //删除编辑器 
                    },
                });
            });
        });
    }else{
        dump('很遗憾,您无权操作!');
    }       
}
//公用显示select值
function sel_opt(){
    //循环填充品牌
    for (var i = 0; i < brand_arr.length; i++) {
        $('#brand').append('<option value="'+brand_arr[i].id+'">'+brand_arr[i].name+'<option>');
    }
    //循环填充商品单位
    for (var i = 0; i < unit_arr.length; i++) {
        $('#unit').append('<option value="'+unit_arr[i].id+'">'+unit_arr[i].name+'<option>');
    }
    //循环填充默认仓库
    for (var i = 0; i < warehouse_arr.length; i++) {
        $('#warehouse').append('<option value="'+warehouse_arr[i].id+'">'+warehouse_arr[i].name+'<option>');
    }
    //初始化分类选择
    $.fn.zTree.init($("#class_info"), {data: {simpleData: {enable: true,idKey: "id",pIdKey: "pid",rootPId: 0}},callback: {onClick: function(event, treeId, treeNode) {$('#class').val(treeNode.name).attr('ape',treeNode.id);$('.ape_select').hide();}}}, goodsclass_arr);
}
//填充辅助属性列表
function attr_info(){
    for (var i = 0; i < attribute_arr.length; i++) {
        var html="<tr><th>"+attribute_arr[i].name+"</th><td>";
        var more=attribute_arr[i].more;
        for (var s = 0; s < more.length; s++) {
            html+="<input type='checkbox' value='"+more[s].id+"' title='"+more[s].name+"' lay-skin='primary' lay-filter='attr'>";
        }
        html+="</td></tr>";
        $('#attr_info').append(html);
    }
}
//辅助属性选择
function attr_set(){
    var tr=$('#attr_info').find('tr');
    //计算每行的选中个数
    var arr=[];
    for (var i = 0;  i< tr.length; i++) {
        tmp_arr=[];
        $(tr[i]).find('input').each(function(){
            var tmp="";
            if($(this).is(':checked')){
                tmp=$(this).val()+'|'+$(this).attr('title');
                tmp_arr.push(tmp);
            }
        });
        if(tmp_arr.length > 0){
            arr.push(tmp_arr);
        }
    }
    var ape = zuhe_arr(arr);
    if(ape!==undefined){
        var tr_html='';
        for (var k = 0; k < ape.length; k++) {
            
            var attr_arr=ape[k].split(",");
            
            var attr_k=[];//ID数组
            var attr_v=[];//名称数组
            for (var e = 0; e < attr_arr.length; e++){
                var tmp_arrs = attr_arr[e].split("|");
                attr_k.push(tmp_arrs[0]);
                attr_v.push(tmp_arrs[1]);
                
            }
            
            attr_k=attr_k.join("_");//ID数组转成字符串
            //判断辅助属性是否存在
            if($("#attribute_info tr[ape='"+attr_k+"']").length === 0){
                tr_html+="<tr ape='"+attr_k+"'>";
                for (var s = 0; s < attr_v.length; s++) {
                    tr_html+="<td>"+attr_v[s]+"</td>"
                }
                tr_html+="<td><input type='text' placeholder='购货价格' class='layui-input' /></td><td><input type='text' placeholder='销货价格' class='layui-input' /></td><td><input type='text' placeholder='零售价格' class='layui-input' /></td><td><input type='text' placeholder='库存预警阀值' class='layui-input' value='"+sys_threshold+"'/></td><td><input type='checkbox' lay-skin='primary' checked></td></tr>";
            }
        }
    }
    //重新渲染规格内容
    $('#attribute_info tr').each(function(){
         var tmp_attribute=$(this).attr('ape').split("_");
         if(tmp_attribute.length==arr.length){
             for (var e = 0; e < tmp_attribute.length; e++) {
                 if(!$("#attr_info input[value='"+tmp_attribute[e]+"']").is(":checked")){
                    $(this).remove();
                 }
             }
         }else{
             $(this).remove();
         }
    });
    $('#attribute_info').prev().find('th').eq(0).attr("colspan", arr.length);
    $('#attribute_info').append(tr_html);
}
//组合可变长数组参数
function zuhe_arr(){  
    var heads=arguments[0][0];
    for(var i=1,len=arguments[0].length;i<len;i++){  
        heads=addNewType(heads,arguments[0][i]);  
    }  
    return heads;  
}
//在原有组合结果的基础上添加一种新的规格
function addNewType(heads,choices){  
    var result=[];
    for(var i=0,len=heads.length;i<len;i++){  
        for(var j=0,lenj=choices.length;j<lenj;j++){  
            result.push(heads[i]+','+choices[j]);
        }  
    }  
    return result;  
}
//获取需要提交的商品主要数据
function post_main_info(){
    var info={};
    var tmp_state=0;
    var name=$('#name').val();
    var brand=$('#brand').val();
    var number=$('#number').val();
    var goods_class=$('#class').attr('ape');
    var spec=$('#spec').val();
    var code=$('#code').val();
    var warehouse=$('#warehouse').val();
    var unit=$('#unit').val();
    var buy=$('#buy').val();
    var sell=$('#sell').val();
    var retail=$('#retail').val();
    var stocktip=$('#stocktip').val();
    var location=$('#location').val();
    var integral=$('#integral').val();
    var data=$('#data').val();
    var serialtype=$('#serialtype').val();
    if(name===""){
        dump('商品名称不可为空');
    }else if(goods_class==="0"){
        dump('商品分类不可为空');
    }else if(!regular_price.test(buy)){
        dump('购货价格不正确');
    }else if(!regular_price.test(sell)){
        dump('销货价格不正确');
    }else if(!regular_price.test(retail)){
        dump('零售价格不正确');
    }else if(!regular_positive.test(stocktip)){
        dump('库存预警不正确');
    }else if(integral!=="" && !regular_price.test(integral)){
        dump('赠送积分不正确');
    }else{
        info['name']=name;
        if(brand===""){
            info['brand']=0;
        }else{
            info['brand']=brand;
        }
        info['number']=number;
        info['class']=goods_class;
        info['spec']=spec;
        info['code']=code;
        if(warehouse===""){
            info['warehouse']=0;
        }else{
            info['warehouse']=warehouse;
        }
        if(unit===""){
            info['unit']=0;
        }else{
            info['unit']=unit;
        }
        info['buy']=buy;
        info['sell']=sell;
        info['retail']=retail;
        info['stocktip']=stocktip;
        info['location']=location;
        info['integral']=integral;
        info['data']=data;
        info['serialtype']=serialtype;
        tmp_state=1;
    }
    if(tmp_state===0){
        layui.use('element', function(){
            layui.element.tabChange('goods', 'goods_main'); 
        });
        return false;
    }else{
        return info;
    }
}
//获取需要提交的辅助属性
function post_attr_info(){
    var info = [];
    var tmp_state=1;
    $('#attribute_info tr').each(function(){
        var tmp={};
        tmp['ape']=$(this).attr('ape');//属性组合
        //购货价格
        var buy=$(this).find('input').eq(0).val();
        if(buy===""){
            tmp['buy']=$('#buy').val();
        }else{
            if(!regular_price.test(buy)){
                dump('第'+($(this).index()+1)+'行购货价格不正确');
                tmp_state=0;
            }else{
                tmp['buy']=buy;
            }
        }
        //销货价格
        var sell=$(this).find('input').eq(1).val();
        if(sell===""){
            tmp['sell']=$('#sell').val();
        }else{
            if(!regular_price.test(sell)){
                dump('第'+($(this).index()+1)+'行销货价格不正确');
                tmp_state=0;
            }else{
                tmp['sell']=sell;
            }
        }
        //零售价格
        var retail=$(this).find('input').eq(2).val();
        if(retail===""){
            tmp['retail']=$('#retail').val();
        }else{
            if(!regular_price.test(retail)){
                dump('第'+($(this).index()+1)+'行零售价格不正确');
                tmp_state=0;
            }else{
                tmp['retail']=retail;
            }
        }
        //预警阀值
        var stocktip=$(this).find('input').eq(3).val();
        if(stocktip===""){
            tmp['stocktip']=$('#stocktip').val();
        }else{
            if(!regular_positive.test(stocktip)){
                dump('第'+($(this).index()+1)+'行预警阀值不正确');
                tmp_state=0;
            }else{
                tmp['stocktip']=stocktip;
            }
        }
        //启用
        var enable=$(this).find('input').eq(4);
        if(enable.is(':checked')){
            tmp['enable']=1;
        }else{
            tmp['enable']=0;
        }
        info.push(tmp);
    });
    if(tmp_state===0){
        layui.use('element', function(){
            layui.element.tabChange('goods', 'goods_attr'); 
        });
        return false;
    }else{
        return info;
    }
}
//获取商品详情
function post_more_info(){
    var info={};
    info['retail_name']=$('#retail_name').val();
    info['imgs']=[];
    $('.goods_img img').each(function(){
        info['imgs'].push($(this).attr('src'));
    });
    info['details']=UM.getEditor('details').getContent();
    return info;
}
//赋值辅助属性
function set_attr(arr){
    for (var i = 0;  i < arr.length; i++) {
        var ape_arr = arr[i].ape.ape.split("_");
        for (var r = 0; r < ape_arr.length; r++) {
            $("#attr_info input[value='"+ape_arr[r]+"']").prop("checked", true);
        }
    }
    attr_set();//重新渲染规格内容
    for (var e = 0;  e < arr.length; e++) {
        var tmp=$("#attribute_info tr[ape='"+arr[e].ape.ape+"']").find('input');
        tmp.eq(0).val(arr[e].buy);
        tmp.eq(1).val(arr[e].sell);
        tmp.eq(2).val(arr[e].retail);
        tmp.eq(3).val(arr[e].stocktip);
        if(arr[e].enable){
            tmp.eq(4).prop("checked", true);
        }else{
            tmp.eq(4).prop("checked", false);
        }
    }
}
//导入
function import_data(){
    var html='<div class="info xls"><div class="layui-form layui-form-pane re_padding"><div class="layui-form-item"><ul><li>1.该功能适用于批量导入商品基础信息。</li><li>2.您需要下载数据模板后使用Excel录入数据。</li><li>3.录入数据时，请勿修改首行标题以及数据格式。</li><li>4.标题为红色的列数据不可为空，否则将跳过该行数据。</li><li>5.如系统中不存在(品牌|分类|默认仓库|单位)将直接创建。</li><li>6.如系统中已经提前录入数据，请直接输入上述对应的名称即可。</li><li>7.点击下方上传文件按钮，选择您编辑好的文件即可。</li></ul></div><hr><div class="layui-form-item btn"><button class="layui-btn" onclick="down_xls()">下载模板</button> <button class="layui-btn layui-btn-primary" id="up_xls">上传文件</button></div></div></div>';
    layui.use(['layer','upload'], function() {
        layer.ready(function() {
            layer.open({
                type: 1,
                title: '导入数据',
                skin: 'layui-layer-rim', //加上边框
                area: ['500px', '390px'], //宽高
                offset: '6%',
                content: html,
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    //弹出后回调
                    layui.upload.render({
                        elem: '#up_xls',
                        url: '/index/service/import_goods', //上传接口
                        exts: 'xls',
                        accept: 'file', //允许上传的文件类型
                        done: function(re) {
                            if(re.msg==="success"){
                                re_alert('恭喜您，成功导入'+re.nums+'条数据！');
                            }else{
                                dump(re.msg);
                            }
                        }
                    });
                }
            });
        });
    });
    
}
//下载模板
function down_xls(){
    alert_info('稍等，即将下载模板','//cdn.nodcloud.com/erp/xls/goods.xls',true);
}
//导出
function export_data(){
    var url_info = push_so_info();
    alert_info('稍等，数据请求中',"/index/export/goods?"+url_info,true);
}
