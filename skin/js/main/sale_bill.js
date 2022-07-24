$(function(){
    layui.use('table', function() {
        var ape_cols=[
            {field: 'time', title: '单据日期', width: 150, align:'center'},
            {field: 'number', title: '订单编号', width: 200, align:'center'},
            {field: 'customer', title: '购买客户', width: 220, align:'center',templet: '<div>{{d.customer.info.name}}</div>'},
            {field: 'account', title: '结算账户', width: 150, align:'center',templet: '<div>{{d.account.info.name}}</div>'},
            {field: 'total', title: '单据金额', width: 150, align:'center'},
            {field: 'discount', title: '优惠金额', width: 150, align:'center'},
            {field: 'money', title: '实收金额', width: 150, align:'center'},
            {field: 'user', title: '制单人', width: 150, align:'center',templet: '<div>{{d.user.info.name}}</div>'},
            {field: 'billtype', title: '付款状态', width: 150, align:'center',templet: '<div><span class="{{#if(d.billtype.ape==0){}}text_red{{#}else if(d.billtype.ape==1){}}text_green{{#}}}">{{d.billtype.name}}</span></div>'},
            {fixed: 'right',field: 'set', title: '相关操作', width: 150, align:'center',templet: '<div><div class="layui-btn-group"><button class="layui-btn layui-btn-primary layui-btn-sm" onclick="form_info({{d.id}})"><i class="layui-icon">&#xe912;</i></button> <button class="layui-btn layui-btn-primary layui-btn-sm" onclick="set_bill({{d.id}})"><i class="layui-icon">&#xe614;</i></button></div></div>'}
        ];//表格选项
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            even: ape_even,
            cols:  [ape_cols],
            url: '/index/service/salebill_list',
            page: true,
            limits: [30,60,90,150,300],
            method: 'post',
            where: push_so_arr(),
            done: function(res, curr, count){
                table_tip('#ape_table',[
                    {'text':'单据总金额','key':'total'},
                    {'text':'优惠总金额','key':'discount'},
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
//刷新
function tmp_re() {
    window.location.href = window.location.pathname + "?id=" + $('#so\\|id').val();
}

//导出
function export_data() {
    var url_info = push_so_info();
    alert_info('稍等，数据请求中', "/index/export/sale_bill?" + url_info,true);
}
//报表
function form_info(id) {
    //iframe层
    layui.use('form', function() {
        layer.open({
            type: 2,
            title: '销货单 - 详情',
            offset: '2%',
            area: ['98%', '96%'],
            content: '/index/main/sale_info?id=' + id,
            end: function() {
                re_alert('数据已重新加载');//刷新父窗口
            }
        });
    });
}
//操作对账单
function set_bill(id) {
    var html = '<div class="info re_padding"><div class="layui-form layui-form-pane"><table class="re_table"><tr><td><div class="layui-form-item"><label class="layui-form-label">实际金额</label><div class="layui-input-block"><input type="text" id="actual" class="layui-input" disabled></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">实收金额</label><div class="layui-input-block"><input type="text" id="money" class="layui-input" disabled></div></div></td></tr></table><p>资金操作</p><hr><table class="re_table"><tr><td><div class="layui-form-item"><label class="layui-form-label">结算账户</label><div class="layui-input-block"><select id="account" class="layui-input" lay-search></select></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">结算金额</label><div class="layui-input-block"><input type="text" id="sum" class="layui-input" placeholder="请输入结算金额"></div></div></td></tr><tr><td colspan="2"><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text" id="data" class="layui-input" placeholder="请输入备注信息"></div></div></td></tr></table><p>操作记录</p><hr><table class="layui-table"><thead><tr><th style="width:20%">操作时间</th><th style="width:15%">结算账户</th><th style="width:15%">结算金额</th><th style="width:15%">制单人</th><th style="width:15%">备注信息</th><th style="width:10%">操作</th></tr></thead><tbody id="tabs"></tbody></table></div></div>';
    layui.use(['layer','form'], function() {
        layer.ready(function() {
            var index = layer.open({
                type: 1,
                title: '资金操作',
                skin: 'layui-layer-rim', //加上边框
                area: ['800px', '520px'], //宽高
                offset: '6%',
                content: html,
                btn: ['保存', '取消'], //按钮
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    $('#account').html($('#so\\|account').html());
                    $('#account option[value="0"]').remove();
                    //默认资金账户
                    if(default_account){
                        $('#account').val(default_account);
                    }
                    layui.form.render('select');
                    //弹出后回调
                    $.post("/index/service/salebill_info", {
                        "id": id,
                    }, function(re) {
                        $('#actual').val((re.class.total-0)-(re.class.discount-0));
                        $('#money').val(re.class.money);
                        for (var i = 0; i < re.bill.length; i++) {
                            $('#tabs').append('<tr><td>'+re.bill[i].time+'</td><td>'+re.bill[i].account.info.name+'</td><td>'+re.bill[i].money+'</td><td>'+re.bill[i].user.info.name+'</td><td>'+re.bill[i].data+'</td><td><button class="layui-btn layui-btn-primary layui-btn-sm" onclick="del_salebill('+re.bill[i].id+','+re.bill[i].auth_info+')"><i class="layui-icon">&#xe640;</i></button></td></tr>');
                        }
                    });
                    
                },
                btn1: function(layero) {
                    //保存
                    $('.layui-layer-btn0').unbind();//解除绑定事件
                    var account =  $('#account').val();
                    var sum =  $('#sum').val();
                    var data =  $('#data').val();
                    if(!regular_price.test(sum) || sum==="0"){
                        dump('结算金额不正确');
                    }else if((sum-0)>($('#actual').val()-0)-($('#money').val()-0)){
                        dump('结算金额超出可结算金额');
                    }else{
                        $.post("/index/service/save_salebill", {
                            "id": id,
                            "account": account,
                            "sum": sum,
                            "data": data
                        }, function(re) {
                            if (re === "success") {
                                re_alert('资金操作成功!');
                            } else {
                                alert_info('服务器响应超时!');
                            }
                        });
                    }
                },
                end: function() {
                    re_alert('数据已重新加载');//刷新父窗口
                }
            });
        });
    });
}
//删除资金详情
function del_salebill(id,auth_type){
    if(auth_type){
        layui.use('layer', function() {
            layer.confirm('删除后资金将归还到原先账户，确定操作？', {
                btn: ['删除', '取消'], //按钮
                offset: '12%',
                shadeClose: true
            }, function() {
                $.post("/index/service/del_salebill", {
                    "id": id
                }, function(re) {
                    if (re === "success") {
                        re_alert('删除成功!');
                    } else {
                        dump('服务器响应超时!');
                    }
                });
            });
        });
    }else{
        dump('很遗憾,您无权操作!');
    }
}