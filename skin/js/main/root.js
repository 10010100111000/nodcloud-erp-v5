$(function(){
    layui.use('table', function() {
        var width=$(window).width();
        var ape_cols=[
            {field: 'name', title: '职员名称', width: 200, align:'center'},
            {field: 'user', title: '职员账号', width: 200, align:'center'},
            {field: 'tel', title: '手机号码', width: 200, align:'center'},
            {field: 'set', title: '相关操作', width: 120, align:'center',templet: '<div><button class="layui-btn layui-btn-primary layui-btn-sm" onclick="set_auth({{d.id}})"><i class="layui-icon">&#xe614;</i></button></div>'},
        ];//表格选项
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            even: ape_even,
            cols:  [ape_cols],
            url: '/index/service/root_list',
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
//显示详情
function set_auth(id) {
    if(root_edit){
        var html = '<div class="info"><div class="layui-form" style="padding:1%"><table class="layui-table" id="tabs"><thead><tr><th>功能名称</th><th>新增</th><th>删除</th><th>修改</th><th>报表</th><th>审核</th></tr></thead><tbody><tr><td>购货单</td><td><input type="checkbox" id="purchase_add" lay-skin="primary"></td><td><input type="checkbox" id="purchase_del" lay-skin="primary"></td><td><input type="checkbox" id="purchase_edit" lay-skin="primary"></td><td><input type="checkbox" id="purchase_form" lay-skin="primary"></td><td><input type="checkbox" id="purchase_auditing" lay-skin="primary"></td></tr><tr><td>采购订单</td><td><input type="checkbox" id="opurchase_add" lay-skin="primary"></td><td><input type="checkbox" id="opurchase_del" lay-skin="primary"></td><td><input type="checkbox" id="opurchase_edit" lay-skin="primary"></td><td><input type="checkbox" id="opurchase_form" lay-skin="primary"></td><td><input type="checkbox" id="opurchase_auditing" lay-skin="primary"></td></tr><tr><td>采购入库单</td><td><input type="checkbox" id="rpurchase_add" lay-skin="primary"></td><td><input type="checkbox" id="rpurchase_del" lay-skin="primary"></td><td><input type="checkbox" id="rpurchase_edit" lay-skin="primary"></td><td><input type="checkbox" id="rpurchase_form" lay-skin="primary"></td><td><input type="checkbox" id="rpurchase_auditing" lay-skin="primary"></td></tr><tr><td>购货退货单</td><td><input type="checkbox" id="repurchase_add" lay-skin="primary"></td><td><input type="checkbox" id="repurchase_del" lay-skin="primary"></td><td><input type="checkbox" id="repurchase_edit" lay-skin="primary"></td><td><input type="checkbox" id="repurchase_form" lay-skin="primary"></td><td><input type="checkbox" id="repurchase_auditing" lay-skin="primary"></td></tr><tr><td>销货单</td><td><input type="checkbox" id="sale_add" lay-skin="primary"></td><td><input type="checkbox" id="sale_del" lay-skin="primary"></td><td><input type="checkbox" id="sale_edit" lay-skin="primary"></td><td><input type="checkbox" id="sale_form" lay-skin="primary"></td><td><input type="checkbox" id="sale_auditing" lay-skin="primary"></td></tr><tr><td>销货退货单</td><td><input type="checkbox" id="resale_add" lay-skin="primary"></td><td><input type="checkbox" id="resale_del" lay-skin="primary"></td><td><input type="checkbox" id="resale_edit" lay-skin="primary"></td><td><input type="checkbox" id="resale_form" lay-skin="primary"></td><td><input type="checkbox" id="resale_auditing" lay-skin="primary"></td></tr><tr class="cashier_info"><td>收银台</td><td><input type="checkbox" id="cashier_add" lay-skin="primary"></td><td><input type="checkbox" id="cashier_del" lay-skin="primary"></td><td><input type="checkbox" id="cashier_edit" lay-skin="primary"></td><td><input type="checkbox" id="cashier_form" lay-skin="primary"></td><td><input type="checkbox" id="cashier_auditing" lay-skin="primary"></td></tr><tr class="cashier_info"><td>零售退货单</td><td><input type="checkbox" id="recashier_add" lay-skin="primary"></td><td><input type="checkbox" id="recashier_del" lay-skin="primary"></td><td><input type="checkbox" id="recashier_edit" lay-skin="primary"></td><td><input type="checkbox" id="recashier_form" lay-skin="primary"></td><td><input type="checkbox" id="recashier_auditing" lay-skin="primary"></td></tr><tr><td>服务订单</td><td><input type="checkbox" id="itemorder_add" lay-skin="primary"></td><td><input type="checkbox" id="itemorder_del" lay-skin="primary"></td><td><input type="checkbox" id="itemorder_edit" lay-skin="primary"></td><td><input type="checkbox" id="itemorder_form" lay-skin="primary"></td><td><input type="checkbox" id="itemorder_auditing" lay-skin="primary"></td></tr><tr><td>积分兑换单</td><td><input type="checkbox" id="exchange_add" lay-skin="primary"></td><td><input type="checkbox" id="exchange_del" lay-skin="primary"></td><td><input type="checkbox" id="exchange_edit" lay-skin="primary"></td><td><input type="checkbox" id="exchange_form" lay-skin="primary"></td><td><input type="checkbox" id="exchange_auditing" lay-skin="primary"></td></tr><tr><td>调拨单</td><td><input type="checkbox" id="allocation_add" lay-skin="primary"></td><td><input type="checkbox" id="allocation_del" lay-skin="primary"></td><td><input type="checkbox" id="allocation_edit" lay-skin="primary"></td><td><input type="checkbox" id="allocation_form" lay-skin="primary"></td><td><input type="checkbox" id="allocation_auditing" lay-skin="primary"></td></tr><tr><td>其他入库单</td><td><input type="checkbox" id="otpurchase_add" lay-skin="primary"></td><td><input type="checkbox" id="otpurchase_del" lay-skin="primary"></td><td><input type="checkbox" id="otpurchase_edit" lay-skin="primary"></td><td><input type="checkbox" id="otpurchase_form" lay-skin="primary"></td><td><input type="checkbox" id="otpurchase_auditing" lay-skin="primary"></td></tr><tr><td>其他出库单</td><td><input type="checkbox" id="otsale_add" lay-skin="primary"></td><td><input type="checkbox" id="otsale_del" lay-skin="primary"></td><td><input type="checkbox" id="otsale_edit" lay-skin="primary"></td><td><input type="checkbox" id="otsale_form" lay-skin="primary"></td><td><input type="checkbox" id="otsale_auditing" lay-skin="primary"></td></tr><tr><td>收款单</td><td><input type="checkbox" id="gather_add" lay-skin="primary"></td><td><input type="checkbox" id="gather_del" lay-skin="primary"></td><td><input type="checkbox" id="gather_edit" lay-skin="primary"></td><td><input type="checkbox" id="gather_form" lay-skin="primary"></td><td><input type="checkbox" id="gather_auditing" lay-skin="primary"></td></tr><tr><td>付款单</td><td><input type="checkbox" id="payment_add" lay-skin="primary"></td><td><input type="checkbox" id="payment_del" lay-skin="primary"></td><td><input type="checkbox" id="payment_edit" lay-skin="primary"></td><td><input type="checkbox" id="payment_form" lay-skin="primary"></td><td><input type="checkbox" id="payment_auditing" lay-skin="primary"></td></tr><tr><td>其他收入单</td><td><input type="checkbox" id="otgather_add" lay-skin="primary"></td><td><input type="checkbox" id="otgather_del" lay-skin="primary"></td><td><input type="checkbox" id="otgather_edit" lay-skin="primary"></td><td><input type="checkbox" id="otgather_form" lay-skin="primary"></td><td><input type="checkbox" id="otgather_auditing" lay-skin="primary"></td></tr><tr><td>其他支出单</td><td><input type="checkbox" id="otpayment_add" lay-skin="primary"></td><td><input type="checkbox" id="otpayment_del" lay-skin="primary"></td><td><input type="checkbox" id="otpayment_edit" lay-skin="primary"></td><td><input type="checkbox" id="otpayment_form" lay-skin="primary"></td><td><input type="checkbox" id="otpayment_auditing" lay-skin="primary"></td></tr><tr><td>资金调拨单</td><td><input type="checkbox" id="eft_add" lay-skin="primary"></td><td><input type="checkbox" id="eft_del" lay-skin="primary"></td><td><input type="checkbox" id="eft_edit" lay-skin="primary"></td><td><input type="checkbox" id="eft_form" lay-skin="primary"></td><td><input type="checkbox" id="eft_auditing" lay-skin="primary"></td></tr><tr><td>库存操作</td><td><input type="checkbox" id="room_add" lay-skin="primary"></td><td>-</td><td>-</td><td>-</td><td>-</td></tr><tr><td>数据报表</td><td>-</td><td>-</td><td>-</td><td><input type="checkbox" id="data_form" lay-skin="primary"></td><td>-</td></tr><tr><td>基础资料</td><td><input type="checkbox" id="basics_add" lay-skin="primary"></td><td><input type="checkbox" id="basics_del" lay-skin="primary"></td><td><input type="checkbox" id="basics_edit" lay-skin="primary"></td><td><input type="checkbox" id="basics_form" lay-skin="primary"></td><td>-</td></tr><tr><td>辅助资料</td><td><input type="checkbox" id="auxiliary_add" lay-skin="primary"></td><td><input type="checkbox" id="auxiliary_del" lay-skin="primary"></td><td><input type="checkbox" id="auxiliary_edit" lay-skin="primary"></td><td><input type="checkbox" id="auxiliary_form" lay-skin="primary"></td><td>-</td></tr><tr><td>高级设置</td><td><input type="checkbox" id="senior_add" lay-skin="primary"></td><td><input type="checkbox" id="senior_del" lay-skin="primary"></td><td><input type="checkbox" id="senior_edit" lay-skin="primary"></td><td><input type="checkbox" id="senior_form" lay-skin="primary"></td><td>-</td></tr></tbody></table></div></div>';
        layui.use(['layer','form'], function() {
            var form=layui.form;//定义Form
            layer.ready(function() {
                layer.open({
                    type: 1,
                    title: '功能授权详情',
                    skin: 'layui-layer-rim', //加上边框
                    area: ['800px', '80%'], //宽高
                    offset: '6%',
                    content: html,
                    btn: ['保存', '取消'],
                    fixed: false,
                    shadeClose: true,
                    success: function(layero, index) {
                        //弹出后回调
                        //判断零售显示情况
                        if(!cashier_type){
                            $('.cashier_info').hide();
                        }
                        
                        $.post("/index/service/user_info", {
                            "id": id,
                        }, function(re) {
                            if(re.root){
                                
                            for(var name in re.root){
                                var key=re.root[name];
                                if(key=='1'){
                                    $('#'+name).attr('checked',true);
                                }
                            }
                            form.render();
                            }else{
                                $('#tabs input').attr('checked',true);
                                form.render();
                            }
                        });
                        
                    },
                    btn1: function(layero) {
                        //保存
                        var root={};
                        $('#tabs input').each(function(){
                            var tmp={};
                            if($(this).is(':checked')){
                                root[$(this).attr('id')]=1;
                            }else{
                                root[$(this).attr('id')]=0;
                            }
                        });
                        $.post("/index/service/save_user_root", {
                            "id": id,
                            "root": root
                        }, function(re) {
                            if(re==="success"){
                                re_alert('保存成功!');
                            }else{
                                dump('服务器响应超时!');
                            }
                        });
                    }
                });
            });
        });
    }else{
        dump('很遗憾,您无权操作!');
    }    
}