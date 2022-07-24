$(function(){ 
    layui.use('element', function(){
        element = layui.element;
        element.on('tab(tabs)', function(data){
            $('.right .right_main').hide();
            if(data.index==0){
                //商品
                $('#goods_list').show();
            }else{
                //服务
                $('#item_list').show();
            }
        });
    });
    push_goods(1,'');//加载完成触发商品数据加载
    setTimeout(function(){
        push_item(1,'');//加载完成延时2S触发服务数据加载
    },2000);
    check_table_show();//表格显示
    
    //商品详情数据改变事件
    $("#settle_info").on('input propertychange',function(){
        sum_settle();
    });
    
}); 
//处理商品表格显示信息
function check_table_show(){
    var main=tabinfo.main;
    for (var i = 0; i < main.length; i++) {
        for (var key in main[i]) {
            if(main[i][key][1]==0){
                $('.'+key).hide();
            }
        }
    }
}
//结账
function settle(){
    if(!$("#settle_info").is(":hidden")){
        //提交单据
        push_cashier();
    }else{
        //赋值页面数据
        var integral = 0;
        if(goods_info.length==0 && item_info.length==0){
            dump('您还未录入数据');
            return false
        }else{
            //检查商品数据合法性
            for (var i = 0; i < goods_info.length; i++) {
                if(goods_info[i].serial!='' && goods_info[i].set_serial.length==0){
                	dump('第'+(i+1)+'个商品未选择串码');
                	return false;
                }else if(goods_info[i].serial!='' && goods_info[i].set_serial.length!=(goods_info[i].set_nums-0)){
                	dump('第'+(i+1)+'个商品串码个数与商品个数不符，请核实!');
                	return false;
                }else if(!regular_positive.test(goods_info[i].set_nums)){
                	dump('第'+(i+1)+'个商品数量不正确');
                	return false;
                }else if(goods_info[i].set_nums>goods_info[i].nums){
                	dump('第'+(i+1)+'个商品数量不可大于库存数[ '+goods_info[i].nums+' ]');
                	return false;
                }else if(!regular_price.test(goods_info[i].set_price)){
                	dump('第'+(i+1)+'个商品零售金额不正确');
                	return false;
                }else if(!regular_discount.test(goods_info[i].set_discount) || (goods_info[i].set_discount-0)==0){
                	dump('第'+(i+1)+'个商品折扣不正确[0.00-1]');
                	return false;
                }else{
                    integral+=(goods_info[i].set_integral-0);
                }
            }
            //检查服务数据合法性
            for (var s = 0; s < item_info.length; s++) {
                //兼容串码判断
                if(!regular_positive.test(item_info[s].set_nums)){
                    dump('第'+(s+1)+'个服务数量不正确');
                    return false;
                }else if(!regular_price.test(item_info[s].set_price)){
                    dump('第'+(s+1)+'个服务零售金额不正确');
                    return false;
                }
            }
        }
        //赋值结算页面信息
        layui.use(['layer','form'], function() {
            var goods_money=$('#goods_money').html();
            $('#settle_goods_total').val(goods_money);
            $('#settle_goods_money').val(goods_money);
            
            var item_money=$('#item_money').html();
            $('#settle_item_total').val(item_money);
            $('#settle_item_money').val(item_money);
            $('#cashier_money').val((goods_money-0)+(item_money-0));
            
            $('#integral').val(integral);
            
            //生成获取结算账户option
            var account_option_info='';
            for (var i = 0; i < account_arr.length; i++) {
                account_option_info+='<option value="'+account_arr[i].id+'">'+account_arr[i].name+'</option>';
            }
            $('#account').append(account_option_info).val(account_default);//增加|默认选项
            //新增组合支付tr
            var tr_html='<tr><td><select lay-ignore>'+account_option_info+'</select></td><td><input type="text" placeholder="请输入结算金额"/></td><td><i class="layui-icon ico_del">&#xe640;</i></td></tr>';
            $('#pay_menus').append(tr_html);
            $('#pay_menus select').val(account_default);//默认选项
            //监听选择
            layui.form.on('switch(paymemu)', function(switch_data){
                if(switch_data.elem.checked){
                    $('#account').parent().parent().hide();
                    $('#pay_menu').show();
                    $('#customer_money').attr("disabled",true);//禁用
                    $('#pay_menus input').eq(0).val($('#actual').val());//赋值组合支付第一条
                    $('#pay_menus tr:gt(0)').remove();//删除多余组合支付
                }else{
                    $('#account').parent().parent().show();
                    $('#pay_menu').hide();
                    $('#customer_money').attr("disabled",false);//解除禁用
                }
            });
            layui.form.render('select'); //重新渲染
        }); 
        $('.right .right_main').hide();
        $('#settle_info').show();
        sum_settle();
    }
}
//隐藏结账信息
function hide_settle_info(){
    //切换回商品页
    layui.use('element', function(){
        layui.element.tabChange('tabs', 'ape_goods');
        $('.right .right_main').hide();
        $('#goods_list').show();
    });
}
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
//结账信息处理
function sum_settle(){
    var settle_goods_total = $('#settle_goods_total').val();
    var settle_goods_discount = $('#settle_goods_discount').val();
    var settle_item_total = $('#settle_item_total').val();
    var settle_item_discount = $('#settle_item_discount').val();
    //预先判断商品模块和服务模块
    if(!regular_price.test(settle_goods_discount)){
        dump('商品模块优惠价格不正确');
        return false;
    }else if((settle_goods_discount-0)>(settle_goods_total-0)){
        dump('商品模块优惠价格不可大于单据金额');
        return false;
    }else if(!regular_price.test(settle_item_discount)){
        dump('服务模块优惠价格不正确');
        return false;
    }else if((settle_item_discount-0)>(settle_item_total-0)){
        dump('服务模块优惠价格不可大于单据金额');
        return false;
    }else{
        var settle_goods_money = cal((settle_goods_total-0)-(settle_goods_discount-0));//商品模块应收金额
        var settle_item_money = cal((settle_item_total-0)-(settle_item_discount-0));//服务模块应收金额
        var cashier_money=cal((settle_goods_money-0)+(settle_item_money-0));
        $('#settle_goods_money').val(settle_goods_money);
        $('#settle_item_money').val(settle_item_money);
        $('#cashier_money').val(cashier_money);
    }
    //处理支付信息
    if($('#paycheckbox').is(':checked')){
        //组合支付
        var ape_money = 0; 
        var ape_check=true;
        $('#pay_menus input').each(function(){
            var ape = $(this).val();
            if(ape!==""){
                if(!regular_price.test(ape)){
                    dump('组合支付第'+($(this).parent().parent().index()+1)+"行结算金额不正确");
                    $('#customer_money').val('0');
                    ape_check=false
                    return false;
                }else{
                    ape_money+=ape-0;
                }
            }
        })
        if(ape_check){
            $('#customer_money').val(ape_money);
        }else{
            return false;
        }
        
    }
    var customer_money = $('#customer_money').val();
    if(customer_money==""){
        dump('请输入客户付款金额');
        return false;
    }else if(!regular_price.test(customer_money)){
        dump('客户付款金额不正确');
        return false;
    }else{
        //计算找零金额
        $('#oddchange').val(0).css('color','#000');
        if((customer_money-0)>(cashier_money-0)){
            $('#oddchange').val(cal(customer_money-0)-(cashier_money-0)).css('color','#f00');
        }
    }
    var integral=$('#integral').val();
    if(!regular_price.test(integral)){
        dump('支付模块赠送积分不正确');
        return false;
    }
    return true;
}

//提交数据
function push_cashier(){
    var check=sum_settle();
    if(check){
        var settle_goods_total = $('#settle_goods_total').val();
        var settle_goods_discount = $('#settle_goods_discount').val();
        var settle_goods_money = $('#settle_goods_money').val();
        var settle_goods_data = $('#settle_goods_data').val();
        var settle_item_total = $('#settle_item_total').val();
        var settle_item_discount = $('#settle_item_discount').val();
        var settle_item_money = $('#settle_item_money').val();
        var settle_item_data = $('#settle_item_data').val();
        var customer = $('#customer').attr('ape');
        var cashier_money = $('#cashier_money').val();
        var customer_money = $('#customer_money').val();
        var account = $('#account').val();
        var integral = $('#integral').val();
        var paytype = 0;
        if($('#paycheckbox').is(':checked')){
            //组合支付
            paytype = 1;
        }
        var payinfo=get_payinfo();//组合支付信息
        console.log();
        
        //合法性判断
        if(!customer){
            dump('您还未选择购买客户');
        }else if((customer_money-0)<(cashier_money-0)){
            dump('客户付款不可小于结算金额');
        }else if(!account){
            dump('您还未选择结算账户');
        }else{
            //提交数据
            $.post("/index/service/save_cashier", {
                "settle_goods_total": settle_goods_total,
                "settle_goods_discount": settle_goods_discount,
                "settle_goods_money": settle_goods_money,
                "settle_goods_data": settle_goods_data,
                "settle_item_total": settle_item_total,
                "settle_item_discount": settle_item_discount,
                "settle_item_money": settle_item_money,
                "settle_item_data": settle_item_data,
                "customer": customer,
                "cashier_money": cashier_money,
                "account": account,
                "integral": integral,
                "paytype": paytype,
                "payinfo": payinfo,
                "goods_info": goods_info,
                "item_info": item_info
            }, function(re) {
                if(re.state === "success") {
                    if(auto_print==1 && re.id){
                        //自动打印
                        //打印小票
                        layui.use('form', function(){
                            layer.open({
                              type: 2,
                              title: '零售小票 - 打印',
                              offset: '9%',
                              area: ['600px', '350px'],
                              shadeClose: true,
                              end:function(){
                                alert_info('单据提交成功,页面即将刷新!');
                              },
                              content: '/index/main/cashier_min_print?auto=true&id='+ re.id
                            }); 
                        }); 
                    }else{
                        alert_info('单据提交成功!');
                    }
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



//获取组合支付数据
function get_payinfo(){
    var arr = [];
    $('#pay_menus tr').each(function(){
        var money = $(this).find('input').val();
        if(money){
            var tmp={};
            tmp['account']=$(this).find('select').val();
            tmp['money'] = money;
            arr.push(tmp);
        }
        
    })
    return arr;
}