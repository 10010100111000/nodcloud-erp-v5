$(function(){
    var form_heigth=$(document.body).height()-200;
    $('#purchase_form').height(form_heigth);
    $('#rpurchase_form').height(form_heigth);
    $('#sale_form').height(form_heigth);
    $('#room_form').height(form_heigth);
    $('#account_form').height(form_heigth);
    $('#itemorder_form').height(form_heigth);
    if(cashier_type){
        $('#cashier_form').height(form_heigth);
    }
    echarts.init(document.getElementById('purchase_form')).setOption(purchase_option);
});
layui.use('element', function(){
    layui.element.on('tab', function(data){
        var ape_name=$(this).attr('ape_name');
        var ape = echarts.init(document.getElementById(ape_name+'_form'));
        ape.clear();//预先清空
        if(ape_name=="purchase"){
            ape.setOption(purchase_option);
        }else if(ape_name=="rpurchase"){
            ape.setOption(rpurchase_option);
        }else if(ape_name=="sale"){
            ape.setOption(sale_option);
        }else if(ape_name=="cashier"){
            ape.setOption(cashier_option);
        }else if(ape_name=="itemorder"){
            ape.setOption(itemorder_option);
        }else if(ape_name=="room"){
            ape.setOption(room_option);
        }else if(ape_name=="account"){
            ape.setOption(account_option);
        }
    });
});

