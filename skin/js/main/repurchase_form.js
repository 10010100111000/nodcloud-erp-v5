$(function(){
    layui.use('table', function() {
        var ape_cols=[
            {field: 'time', title: '单据日期', width: 150, align:'center'},
            {field: 'number', title: '单据编号', width: 200, align:'center'},
            {field: 'supplier', title: '供应商', width: 220, align:'center',templet: '<div>{{d.supplier.info.name}}</div>'},
            {field: 'account', title: '结算账户', width: 150, align:'center',templet: '<div>{{d.account.info.name}}</div>'},
            {field: 'user', title: '制单人', width: 150, align:'center',templet: '<div>{{d.user.info.name}}</div>'},
            {field: 'type', title: '审核状态', width: 150, align:'center',templet: '<div><span class="{{#if(!d.type.ape){}}text_red{{#}}}">{{d.type.name}}</span></div>',event:'auditinginfo'},
            {field: 'total', title: '单据金额', width: 150, align:'center'},
            {field: 'actual', title: '实际金额', width: 150, align:'center'},
            {field: 'money', title: '实收金额', width: 150, align:'center'},
            {field: 'data', title: '单据备注', width: 150, align:'center'},
            {fixed: 'right',field: 'set', title: '相关操作', width: 215, align:'center',templet: '<div><div class="layui-btn-group"><button class="layui-btn layui-btn-primary layui-btn-sm" onclick="repurchase_print({{d.id}})"><i class="layui-icon">&#xe911;</i></button> <button class="layui-btn layui-btn-primary layui-btn-sm" onclick="repurchase_info({{d.id}})"><i class="layui-icon">&#xe912;</i></button> <button class="layui-btn layui-btn-primary layui-btn-sm" onclick="del_repurchase({{d.id}},{{d.type.ape}})"><i class="layui-icon">&#xe640;</i></button></div></div>'}
        ];//表格选项
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            even: ape_even,
            cols:  [ape_cols],
            url: '/index/service/repurchaseclass_list',
            page: true,
            limits: [30,60,90,150,300],
            method: 'post',
            where: push_so_arr(),
            done: function(res, curr, count){
            	table_tip('#ape_table',[
            		{'text':'单据总金额','key':'total'},
            		{'text':'实际总金额','key':'actual'},
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
    alert_info('稍等，数据请求中',"/index/export/repurchase?"+url_info+"&mode="+type,true);
}
//打印
function repurchase_print(id){
    //iframe层 
    layui.use('form', function(){
        layer.open({
          type: 2,
          title: '购货退货单 - 打印',
          offset: '9%',
          area: ['600px', '350px'],
          content: '/index/main/repurchase_print?id='+ id
        }); 
    }); 
}
//修改
function repurchase_info(id){
    //iframe层
    layui.use('form', function(){
        layer.open({
          type: 2,
          title: '购货退货单 - 详情',
          offset: '2%',
          area: ['98%', '96%'],
          content: '/index/main/repurchase_info?id='+ id,
          end:function(){
            re_alert('数据已重新加载');//刷新父窗口
          }
        }); 
    }); 
}
//删除
function del_repurchase(id,type){
    if(root_del){
        if(type){
            dump("订单需反审核后才可删除!");
        }else{
            layui.use('layer', function() {
                layer.confirm('单据删除后不可恢复，确定删除？', {
                    btn: ['删除', '取消'], //按钮
                    offset: '6%',
                    shadeClose: true
                }, function() {
                    $('.layui-layer-btn0').unbind();//解除绑定事件
                    $.post("/index/service/del_repurchase", {
                        "id": id
                    }, function(re) {
                        if (re === "success") {
                            re_alert('单据删除成功!');
                        }else{
                            dump('服务器响应超时!');
                        }
                    });
                });
            });
        }
    }else{
        dump('很遗憾,您无权操作!');
    }
}