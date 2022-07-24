$(function(){
    layui.use('form', function() {
        //监听选择
        layui.form.on('switch(paymemu)', function(switch_data){
            if(switch_data.elem.checked){
                $('#account').attr("disabled",true);
                $('#pay_menu').show();
                $('#money').attr("disabled",true);//禁用
                $('#pay_menus tr:gt(0)').remove();//删除多余组合支付
                $('#pay_menus select').val(account_default);
                $('#pay_menus input').val('');
            }else{
                $('#account').attr("disabled",false);
                $('#pay_menu').hide();
                $('#money').attr("disabled",false);//解除禁用
            }
            cal_payinfo();
        });
    });
});
//增加组合支付
$(document).on("click", ".ico_add", function() {
    var ape = $('#pay_menus tr');
    if(ape.length==3){
        dump('组合支付最多支持三种方式');
    }else{
        $('#pay_menus').append('<tr>'+ape.eq(0).html()+'</tr>');
    }
});

//删除组合支付
$(document).on("click", ".ico_del", function() {
    var ape = $('#pay_menus tr');
    if(ape.length!==1){
        $(this).parent().parent().remove();
    }
});
//提交|修改表单
function save(){
    var time = $('#time').val();
    var number = $('#number').val();
    var total = $('#total').val();
    var discount = $('#discount').val();
    var integral = $('#integral').val();
    var customer = $('#customer').attr('ape');
    var user = $('#user').val();
    var data = $('#data').val();
    var money = $('#money').val();
    var account = $('#account').val();
    //默认非组合支付
    var paytype=0;
    var payinfo=[];
    if($('#paycheckbox').is(':checked')){
        paytype = 1;
        //获取组合支付内容
        $('#pay_menus tr').each(function(){
            var tmp_input=$(this).find('input').val();
            var tmp_select=$(this).find('select').val();
            if(tmp_input!==""){
                if(!tmp_select){
                    dump('组合支付第'+($(this).index()+1)+"行结算账户不正确");
                    return false;
                }else if(!regular_price.test(tmp_input)){
                    dump('组合支付第'+($(this).index()+1)+"行结算金额不正确");
                    return false;
                }else{
                    var tmp_info={};
                    tmp_info['account']=tmp_select;
                    tmp_info['money']=tmp_input;
                    payinfo.push(tmp_info);//转存支付方式和金额
                }
            }
        });
    }
    if(!regular_time.test(time)){
        dump('单据日期不正确');
    }else if(number===""){
        dump('单据编号不可为空');
    }else if(!regular_price.test(discount)){
        dump('优惠金额不正确');
    }else if(money===""){
        dump('实收金额不可为空');
    }else if(!regular_price.test(money)){
        dump('实收金额不正确');
    }else if((money-0)!=((total-0)-(discount-0))){
        dump('实收金额与应收金额不符');
    }else if(!user){
        dump('制单人不可为空');
    }else if(!regular_price.test(integral)){
        dump('积分不正确');
    }else if(paytype==0 && !account){
        dump('结算账户不可为空');
    }else{
        var info=tab_info();
        if(info){
            $.post("/index/service/update_cashier", {
                "id": id,
                "time": time,
                "number": number,
                "total": total,
                "discount": discount,
                "integral": integral,
                "customer": customer,
                "user": user,
                "money": money,
                "account": account,
                "paytype": paytype,
                "payinfo": JSON.stringify(payinfo),
                "data": data,
                "info": info,
            }, function(re) {
                if(re.state === "success") {
                    alert_info('单据提交成功!');
                }else if(re.state === "stock_error"){
                    dump('第'+re.row+'行商品库存不足，请核实');
                }else if(re.state === "serial_error"){
                    dump('第'+re.row+'行商品某串码已销售，请核实');
                }else{
                    dump('服务器响应超时!');
                }
            });
        }
    }
}
//获取表格信息
function tab_info(){
    if(check_tab()){
        var info = $("#table").jqGrid("getGridParam",'data'); //获取表格数据
        var arr=[];
        for (var i = 0; i < info.length; i++) {
            if(info[i].set_id !== "-1"){
                var tmp={};
                tmp['room']=info[i].set_id;//仓储ID
                tmp['goods']=info[i].goods;//商品ID
                tmp['serial']=info[i].serial;//串号
                tmp['nums']=info[i].nums;
                tmp['price']=info[i].price;
                tmp['discount']=info[i].discount;//折扣
                tmp['total']=info[i].total;
                tmp['data']=info[i].data;
                arr.push(tmp);
            }
        }
        if(arr.length===0){
            dump('您还未选择商品!');
            return false;
        }else{
            return arr;
        }
    }else{
        return false;
    }
}


//审核
function auditing(id,type){
    layui.use('layer', function() {
        var tip=(type ? '审核后将操作商品库存以及资金账户和客户积分,请再次确定？':'反审核后将反操作库存以及资金账户和客户积分,请再次确定？')
        layer.confirm(tip, {
            btn: ['确定', '取消'], //按钮
            offset: '12%'
        }, function() {
            $('.layui-layer-btn0').unbind();//解除绑定事件
            $.post("/index/service/auditing_cashier", {
                "id": id
            }, function(re) {
                if (re.state === "success") {
                    alert_info('操作成功!');
                }else if(re.state ==="stock_error"){
                    dump('第'+re.row+'行商品库存不足!');
                }else if(re.state ==="serial_error"){
                    dump('第'+re.row+'行商品某串码已销售!');
                }else if(re.state ==="set_error"){
                    dump('第'+re.row+'行商品已经发生过业务，反审核失败!');
                }else{
                    dump('服务器响应超时!');
                }
            });
        });
    });
}
//计算金额
function cal_money(){
    var total = $('#total').val();
    var discount = $('#discount').val();
    if(total===""){
        dump('请先录入服务项目数据');
    }else if(!regular_price.test(discount)){
        dump('优惠金额不正确');
    }else if((discount-0)>(total-0)){
        dump('优惠金额不可大于单据金额');
    }else{
        layui.use('layer', function() {
            layer.confirm('是否自动计算实收金额？', {
                btn: ['计算', '取消'], //按钮
                offset: '6%'
            }, function() {
                $('#money').val(cal((total-0)-(discount-0)));
                layer.closeAll();
            });
        });
    }
}
//渲染信息
function render_payinfo(){
    $('#pay_menu').show();
    //渲染组合支付信息
    for (var s = 0; s < class_payinfo.length; s++) {
        if(s>0){
            $('#pay_menus').append('<tr>'+$('#pay_menus tr').eq(0).html()+'</tr>');
        }
        $('#pay_menus select').eq(s).val(class_payinfo[s].account);
        $('#pay_menus input').eq(s).val(class_payinfo[s].money);
    }
}
//计算组合金额
function cal_payinfo(){
    //计算实收金额
    var all=0;
    var money= $('#money').val();
    $('#pay_menus input').each(function(){
        var ape = $(this).val();
        if(ape!=""){
            if(!regular_price.test(ape)){
                dump('组合支付第'+($(this).parent().parent().index()+1)+"行结算金额不正确");
                return false;
            }else{
                all+=(ape-0);
            }
        }
    })
    $('#money').val(all);
}