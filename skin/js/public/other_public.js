var regular_positive=/^[1-9]\d*$/;//正整数正则
var regular_price=/^([1-9][\d]{0,7}|0)(\.[\d]{1,2})?$/;//价格正则包含0
var regular_time=/^(19|20)\d{2}-(0?\d|1[012])-(0?\d|[12]\d|3[01])$/;//时间正则
var regular_discount=/^(0.\d+|0|1)$/;//折扣正则
var regular_code=/^[0-9a-zA-Z]*$/;//串码正则
//信息框|刷新OR跳转
function alert_info(info,url,state){
    var state=state||false;
    dump(info);
    setTimeout(function(){
        if(url===undefined){
            location.reload();
        }else{
            if(state){
                window.open(url);
            }else{
                window.location.href=url;
            }
        }
    },1000);
}
//联系客服
function kefu(){
    window.open('https://p.qiao.baidu.com/cps/chat?siteId=13056976&userId=27301598');
}
//帮助中心
function help(){
    window.open('https://www.nodcloud.com');
}
//消息|非跳转
function dump(msg_text){
    layui.use('layer', function(){
        layui.layer.msg(msg_text);
    });
}
//公用获取搜索条件
function push_so_info(){
    var url_info ="";
    //获取input
    $('.so input,select').each(function(){
        if($(this).attr('id')!==undefined){
            var tmp_arr=$(this).attr('id').split('|');
            //兼容APE用法
            if($(this).attr('ape')===undefined){
                url_info+="&"+tmp_arr[1]+'='+$(this).val();
            }else{
                url_info+="&"+tmp_arr[1]+'='+$(this).attr('ape');
            }
        }
    })
    url_info.substr(1,url_info.length);
    return url_info;
}
//公共获取搜索条件 — 数组
function push_so_arr(){
    var arr = {};
    //获取input
    $('.so input,select').each(function(){
        if($(this).attr('id')!==undefined){
            var tmp_arr=$(this).attr('id').split('|');
            //兼容APE用法
            if($(this).attr('ape')===undefined){
                arr[tmp_arr[1]]=$(this).val();
            }else{
                arr[tmp_arr[1]]=$(this).attr('ape');
            }
            
        }
    })
    return arr;
}
//刷新
function re() {
    window.location.href=window.location.pathname;
}
//刷新
function ape_re() {
    location.reload();
}
//返回四舍五入数值
function cal(nums){
    return parseFloat(nums.toFixed(2));
}
//展示更多搜索条件
function more(ape){
    if($(ape).attr('ape')==='hide'){
        //显示
        $(ape).attr('ape','show').html('<i class="layui-icon">&#xe619;</i>');
        $(ape).parent().parent().parent().find("tr:not(:first)").show();
    }else{
        //隐藏
        $(ape).attr('ape','hide').html('<i class="layui-icon">&#xe61a;</i>');
        $(ape).parent().parent().parent().find("tr:not(:first)").hide();
    }
}

//配置表格
function ape_set_tabinfo(ape_tabinfo){
    var html = '<div class="re_padding"><div class="layui-form layui-form-pane"><table class="layui-table"><thead><tr><th style="width:60%">名称</th><th style="width:40%">配置</th></tr></thead><tbody id="tabinfo"></tbody></table></div></div>';
    layui.use(['layer','form'], function() {
        layer.ready(function() {
            var form=layui.form;
            layer.open({
                type: 1,
                title: '表格设置',
                skin: 'layui-layer-rim', //加上边框
                area: ['390px', '320px'], //宽高
                offset: '9%',
                content: html,
                btn: ['确定', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    $('#tabinfo').attr('ape',ape_tabinfo.name);
                    var main=ape_tabinfo.main;
                    for (var i = 0; i < main.length; i++) {
                        for(var key in main[i]){
                            var checked_info =(main[i][key][1]==="1" ? 'checked':'');
                            var html='<tr ape="'+key+'"><td>'+main[i][key][0]+'</td><td><input type="checkbox" '+checked_info+' lay-skin="switch" lay-text="显示|隐藏"></td></tr>';
                            $('#tabinfo').append(html);
                        }
                    }
                    form.render();
                },
                btn1: function(layero) {
                    var arr=[];
                    $('#tabinfo tr').each(function(){
                        var tmp={};
                        if($(this).find('input').is(':checked')){
                            tmp[$(this).attr('ape')]=[$(this).find('td').eq(0).text(),1];
                        }else{
                            tmp[$(this).attr('ape')]=[$(this).find('td').eq(0).text(),0];
                        }
                        arr.push(tmp);
                    });
                    $.post("/index/service/set_tabinfo", {
                        "name" : $('#tabinfo').attr('ape'),
                        "main" : arr
                    }, function(re) {
                        if(re==='success'){
                            layer.closeAll();
                            alert_info('保存成功');
                        }else{
                            dump('服务器响应超时');
                        }
                    });
                }
            });
        });
    });
}
//配置表格-预处理
function run_set_tabinfo(ape_obj,ape_tabinfo){
    for (var i = 0; i < ape_tabinfo.main.length; i++) {
        for (var vo in ape_tabinfo.main[i]) {
            if(ape_tabinfo.main[i][vo][1]==='0'){
                //删除不显示项
                $(ape_obj+" th[ape='"+vo+"']").each(function(){
                    var index = $(this).index();
                    $(this).remove();//删除TH
                    $(ape_obj+" tr").each(function(){
                        $(this).find('td').eq(index).remove();//删除TD
                    });
                });
            }
        }
    }
}
//表格功能配置-系统功能
function run_sys_tabinfo(ape_obj,ape_key){
    var tmp_obj=$(ape_obj+" th[ape='"+ape_key+"']");
    var tmp_index=tmp_obj.index();//获取下标
    tmp_obj.remove();//删除TH
    $(ape_obj+" tr").each(function(){
        $(this).find('td').eq(tmp_index).remove();//删除TD
    });
}

//layui删除表头列
function run_layui_tabinfo(goodstabinfo,ape_cols){
    var tmp_obj={};
    for (var i = 0; i < goodstabinfo.main.length; i++) {
        for (var ape_k in goodstabinfo.main[i]) {
            tmp_obj[ape_k]=goodstabinfo.main[i][ape_k][1];
        }
    }
    var re_col=[];
    //循环表头配置
    for (var s = 0; s < ape_cols.length; s++) {
        if(tmp_obj[ape_cols[s].field]!=0){
            re_col.push(ape_cols[s]);
        }
    }
    return re_col;
}
//判断对象是否为空
function isEmptyObject(obj) {
  for (var key in obj) {
    return false;
  }
  return true;
}
//判断元素是否重复
function isRepeat(arr){
    var hash = {};
    for(var i in arr) {
        if(hash[arr[i]]){
            return true;
        }else{
            hash[arr[i]] = true;
        }
    }
    return false;
}

//ESC关闭所有弹框
$(document).keyup(function(event){
    switch(event.keyCode) {
        case 27:
        layui.use('layer', function(){
            layer.closeAll();
        });
    }
});
//分类选择移除事件
$(document).on('mouseleave','.ape_select',function(){
    $('.ape_select').hide();
})

//触发搜索|信息框|关闭弹框
function re_alert(info){
    //关闭弹框
    layui.use('layer', function(){
        layer.closeAll();
    });
    //触发搜索
    if($('.so').length>0){
        $('.so i').each(function(){
            if(!$(this).parent().attr('ape')){
                var func = $(this).parent().attr('onclick')
                eval(func);//执行搜索
            }
        });
    }
    //消息框
    dump(info);
}

//表格数据统计
//表格标示,统计数据
function table_tip(ape,info){
    var tip='';
    var dom = $(ape).next();
    if(info.length>0){
        var tip_arr=[];
        for (var i = 0; i < info.length; i++) {
            var nums = 0;
            dom.find('.layui-table-main td[data-field="'+info[i].key+'"]').each(function(){
                nums+=$(this).find('div').html()-0;
                
            });
            tip_arr.push(info[i].text+':'+cal(nums));
        }
        tip=tip_arr.join(" | ");
    }
    dom.find('.layui-laypage-default').append('<span class="layui-laypage-tip">'+tip+'</span>');
}

//选择供应商
function set_supplier(ape){
    var html = '<div class="goods_info_list"><div class="layui-form layui-form-pane"><table class="so_info"><tr><td><div class="layui-form-item"><label class="layui-form-label">名称</label><div class="layui-input-block"><input type="text" id="so_name" placeholder="供应商名称" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">编号</label><div class="layui-input-block"><input type="text" id="so_number" placeholder="供应商编号" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">联系人</label><div class="layui-input-block"><input type="text" id="so_contacts" placeholder="联系人" class="layui-input"></div></div></td><td><button class="layui-btn layui-btn-primary" onclick="more(this)" ape="hide"><i class="layui-icon">&#xe61a;</i></button> <button class="layui-btn layui-btn-primary" onclick="suppliers_so()" style="margin:0"><i class="layui-icon">&#xe615;</i></button></td></tr><tr style="display:none"><td><div class="layui-form-item"><label class="layui-form-label">联系电话</label><div class="layui-input-block"><input type="text" id="so_tel" placeholder="联系电话" class="layui-input"></div></div></td><td colspan="2"><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text" id="so_data" placeholder="备注信息" class="layui-input"></div></div></td></tr></table></div><hr><table id="suppliers_list"></table></div>';
    layui.use(['layer','form','laypage','table'], function() {
        var table = layui.table;
        var form = layui.form;
        layer.ready(function() {
            layer.open({
                type: 1,
                title: '选择供应商',
                skin: 'layui-layer-rim', //加上边框
                area: ['860px', '520px'], //宽高
                offset: '6%',
                content: html,
                btn: ['确定', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    var ape_cols=[
                        {field: 'set', title:'选中',align:'center',templet: '<div><input type="checkbox" lay-skin="primary" lay-filter="onset" setid="{{d.id}}" setname="{{d.name}}"></div>',unresize:true},
                        {field: 'name', title: '供应商名称', width: 150, align:'center'},
                        {field: 'number', title: '供应商编号', width: 150, align:'center'},
                        {field: 'contacts', title: '联系人', width: 150, align:'center'},
                        {field: 'tel', title: '联系电话', width: 150, align:'center'},
                        {field: 'add', title: '供应商地址', width: 150, align:'center'},
                        {field: 'other', title: '社交账号', width: 150, align:'center'},
                        {field: 'email', title: '邮箱地址', width: 150, align:'center'},
                        {field: 'accountname', title: '开户名', width: 150, align:'center'},
                        {field: 'openingbank', title: '开户行', width: 150, align:'center'},
                        {field: 'bankaccount', title: '银行账号', width: 150, align:'center'},
                        {field: 'tax', title: '税号', width: 150, align:'center'},
                        {field: 'data', title: '备注信息', width: 150, align:'center'}
                    ];//默认表格选项
                    table.render({
                        id: 'suppliers_list',
                        elem: '#suppliers_list',
                        height:'340',
                        even: ape_even,
                        cols:  [ape_cols],
                        url: '/index/service/suppliers_list',
                        page: true,
                        limits: [30,60,90,150,300],
                        method: 'post',
                        where: suppliers_where_info()
                    });//渲染表格
                    form.on('checkbox(onset)', function(data){
                        //判断是否选中
                        if(data.elem.checked){
                            var setid = $(data.elem).attr('setid');
                            $("input:checkbox[setid!='"+setid+"']").prop('checked',false);
                            form.render('checkbox');
                        }
                    });
                },
                btn1: function(layero) {
                    //保存
                    var on_arr=[];
                    $("input:checkbox[lay-filter='onset']").each(function(){
                        if($(this).is(':checked')){
                            on_arr.push({
                                'setid':$(this).attr('setid'),
                                'setname':$(this).attr('setname')
                            })
                        }
                    });
                    if(on_arr.length>0){
                        $(ape).attr('ape',on_arr[0]['setid']).val(on_arr[0]['setname']);
                        layer.closeAll();
                    }else{
                        dump('您还未选择数据！');
                    }
                    
                }
            });
        });
    });
};
//供应商弹框搜索条件
function suppliers_where_info(){
    return {
        "name": $('#so_name').val(),
        "number": $('#so_number').val(),
        "contacts": $('#so_contacts').val(),
        "tel": $('#so_tel').val(),
        "data": $('#so_data').val()
    };
}
//供应商弹框搜索
function suppliers_so(){
    layui.use('table', function() {
        layui.table.reload('suppliers_list',{
            where: suppliers_where_info()
        });
    });
}
//选择客户
function set_customer(ape){
    var html = '<div class="goods_info_list"><div class="layui-form layui-form-pane"><table class="so_info"><tr><td><div class="layui-form-item"><label class="layui-form-label">名称</label><div class="layui-input-block"><input type="text"id="so_name"placeholder="客户名称"class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">编号</label><div class="layui-input-block"><input type="text"id="so_number"placeholder="客户编号"class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">联系人</label><div class="layui-input-block"><input type="text"id="so_contacts"placeholder="联系人"class="layui-input"></div></div></td><td><button class="layui-btn layui-btn-primary"onclick="more(this)"ape="hide"><i class="layui-icon">&#xe61a;</i></button><button class="layui-btn layui-btn-primary"onclick="customers_so()"style="margin:0"><i class="layui-icon">&#xe615;</i></button></td></tr><tr style="display:none"><td><div class="layui-form-item"><label class="layui-form-label">手机号</label><div class="layui-input-block"><input type="text"id="so_tel"placeholder="手机号"class="layui-input"></div></div></td><td colspan="2"><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text"id="so_data"placeholder="备注信息"class="layui-input"></div></div></td></tr></table></div><hr><table id="customers_lists"></table></div>';
    layui.use(['layer','form','laypage','table'], function() {
        var table = layui.table;
        var form = layui.form;
        layer.ready(function() {
            layer.open({
                type: 1,
                title: '选择客户',
                skin: 'layui-layer-rim', //加上边框
                area: ['860px', '520px'], //宽高
                offset: '6%',
                content: html,
                btn: ['确定', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    var ape_cols=[
                        {field: 'set', title:'选中',align:'center',templet: '<div><input type="checkbox" lay-skin="primary" lay-filter="onset" setid="{{d.id}}" setname="{{d.name}}"></div>',unresize:true},
                        {field: 'name', title: '客户名称', width: 150, align:'center'},
                        {field: 'number', title: '客户编号', width: 150, align:'center'},
                        {field: 'contacts', title: '联系人', width: 150, align:'center'},
                        {field: 'tel', title: '手机号', width: 150, align:'center'},
                        {field: 'birthday', title: '生日', width: 150, align:'center'},
                        {field: 'add', title: '客户地址', width: 150, align:'center'},
                        {field: 'integral', title: '客户积分', width: 150, align:'center'},
                        {field: 'other', title: '社交账号', width: 150, align:'center'},
                        {field: 'email', title: '邮箱地址', width: 150, align:'center'},
                        {field: 'accountname', title: '开户名', width: 150, align:'center'},
                        {field: 'openingbank', title: '开户行', width: 150, align:'center'},
                        {field: 'bankaccount', title: '银行账号', width: 150, align:'center'},
                        {field: 'tax', title: '税号', width: 150, align:'center'},
                        {field: 'data', title: '备注信息', width: 150, align:'center'}
                    ];//默认表格选项
                    table.render({
                        id: 'customers_lists',
                        elem: '#customers_lists',
                        height:'340',
                        even: ape_even,
                        cols:  [ape_cols],
                        url: '/index/service/customers_list',
                        page: true,
                        limits: [30,60,90,150,300],
                        method: 'post',
                        where: customers_where_info()
                    });//渲染表格
                    form.on('checkbox(onset)', function(data){
                        //判断是否选中
                        if(data.elem.checked){
                            var setid = $(data.elem).attr('setid');
                            $("input:checkbox[setid!='"+setid+"']").prop('checked',false);
                            form.render('checkbox');
                        }
                    });
                },
                btn1: function(layero) {
                    //保存
                    var on_arr=[];
                    $("input:checkbox[lay-filter='onset']").each(function(){
                        if($(this).is(':checked')){
                            on_arr.push({
                                'setid':$(this).attr('setid'),
                                'setname':$(this).attr('setname')
                            })
                        }
                    });
                    if(on_arr.length>0){
                        $(ape).attr('ape',on_arr[0]['setid']).val(on_arr[0]['setname']);
                        layer.closeAll();
                    }else{
                        dump('您还未选择数据！');
                    }
                    
                }
            });
        });
    });
};
//客户弹框搜索
function customers_so(){
    layui.use('table', function() {
        layui.table.reload('customers_lists',{
            where: customers_where_info()
        });
    });
}
//客户弹框搜索条件
function customers_where_info(){
    return {
        "name": $('#so_name').val(),
        "number": $('#so_number').val(),
        "contacts": $('#so_contacts').val(),
        "tel": $('#so_tel').val(),
        "data": $('#so_data').val()
    };
}