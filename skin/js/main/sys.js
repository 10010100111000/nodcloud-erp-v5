var form;
$(function(){
    layui.use(['form', 'element'], function(){
        layui.element;
        form = layui.form; //Tab的切换功能
        form.on('checkbox(number)', function(data){
            number_info();
        });
    });
    $("#number").bind("input propertychange change",function(event){
        number_info();
    });
    //设置编码规则
    $.post("/index/service/sys_number_info", {
        "by": 'nodcloud'
    }, function(re) {
        for (var i = 0; i < re.length; i++) {
            var ape = $('tr[ape='+re[i]['name']+']');
            ape.find('input[type=text]').eq(0).val(re[i]['pre']);
            if(re[i]['enable']=='1'){ape.find('input[type=checkbox]').eq(0).attr('checked',true);}
            if(re[i]['y']=='1'){ape.find('input[type=checkbox]').eq(1).attr('checked',true);}
            if(re[i]['m']=='1'){ape.find('input[type=checkbox]').eq(2).attr('checked',true);}
            if(re[i]['d']=='1'){ape.find('input[type=checkbox]').eq(3).attr('checked',true);}
            if(re[i]['h']=='1'){ape.find('input[type=checkbox]').eq(4).attr('checked',true);}
            if(re[i]['i']=='1'){ape.find('input[type=checkbox]').eq(5).attr('checked',true);}
            if(re[i]['s']=='1'){ape.find('input[type=checkbox]').eq(6).attr('checked',true);}
            if(re[i]['nums']!='0'){ape.find('input[type=text]').eq(1).val(re[i]['nums']);}
        }
        layui.use('form', function(){layui.form.render();});
        number_info();
    });
});
//保存基础信息
function save_info(){
    var sys_name = $('#sys_name').val();
    var company_name = $('#company_name').val();
    var company_tel = $('#company_tel').val();
    var company_add = $('#company_add').val();
    if(sys_name===""){
        dump('系统名称不可为空');
    }else{
        $.post("/index/service/save_sys_info", {
            "sys_name": sys_name,
            "company_name": company_name,
            "company_tel": company_tel,
            "company_add": company_add
        }, function(re) {
            if (re === 'success') {
                dump('保存基础信息成功!');
            } else {
                dump('服务器响应超时！');
            }
        });
    }
}
//保存功能设置
function save_set(){
    var threshold = $('#threshold').val();//默认阀值
    var default_account = $('#default_account').val();//默认结算账户
    var default_print = $('#default_print').val();//默认打印纸张
    var homeday = $('#homeday').val();//首页报表默认天数
    var user_choose = 0;//制单人可选
    var auditing = 0;//审核功能
    var serial = 0;//串码功能
    var batch = 0;//批次功能
    var even = 0;//隔行背景功能
    if($('#user_choose').is(':checked')){
        user_choose = 1;
    }
    if($('#auditing').is(':checked')){
        auditing = 1;
    }
    if($('#serial').is(':checked')){
        serial = 1;
    }
    if($('#batch').is(':checked')){
        batch = 1;
    }
    if($('#even').is(':checked')){
        even = 1;
    }
    if(!regular_positive.test(threshold)){
        dump('默认阀值不正确!');
    }else if(!regular_positive.test(homeday)){
        dump('首页报表天数不正确!');
    }else{
        $.post("/index/service/save_sys_set", {
            "threshold": threshold,
            "default_account":default_account,
            "default_print":default_print,
            "homeday":homeday,
            "user_choose": user_choose,
            "auditing": auditing,
            "serial": serial,
            "batch": batch,
            "even": even
        }, function(re) {
            if (re === 'success') {
                dump('保存功能设置成功!');
            } else {
                dump('服务器响应超时!');
            }
        });
    }
}
//保存收银台配置
function save_cashier(){
    var ape={};
    var check=true;
    if($('#cashier').is(':checked')){
        //开启
        ape['cashier']=1;
        var cashier_name=$('#cashier_name').val();
        var cashier_customer=$('#cashier_customer').val();
        var cashier_actual=$('#cashier_actual').val();
        var cashier_item=$('#cashier_item').val();
        var cashier_print=$('#cashier_print').val();
        if(cashier_name===''){
            dump('收银标题不可为空!');
            check=false;
        }else if(cashier_actual===""){
            dump('请选择默认收银账户');
            check=false;
        }else if(cashier_item===""){
            dump('请选择服务收款账户');
            check=false;
        }else{
            ape['cashier_name']=cashier_name;
            ape['cashier_customer']=cashier_customer;
            ape['cashier_actual']=cashier_actual;
            ape['cashier_item']=cashier_item;
            ape['cashier_print']=cashier_print;
        }
    }else{
        //关闭
        ape['cashier']=0;
        ape['cashier_name']='';
        ape['cashier_customer']='';
        ape['cashier_actual']='';
        ape['cashier_item']='';
        ape['cashier_print']=0;
    }
    if(check){
        $.post("/index/service/save_sys_cashier", {
            "info": ape,
        }, function(re) {
            if (re === 'success') {
                dump('保存收银设置成功!');
            } else {
                dump('服务器响应超时!');
            }
        });
    }
}
//获取并设置编码规则
function number_info(){
    var info=[];
    for (var n=1; n < $('#number tr').length; n++){
        var tmp_info={};
        var str='';
        var tmp_tr = $('#number tr').eq(n);
        tmp_info['name']=tmp_tr.attr('ape');
        tmp_info['pre'] = tmp_tr.find('input[type=text]').eq(0).val();
        if(tmp_info['pre']!==''){
            str+=tmp_info['pre'];
        }
        tmp_info['enable']=0;
        tmp_info['y']=0;
        tmp_info['m']=0;
        tmp_info['d']=0;
        tmp_info['h']=0;
        tmp_info['i']=0;
        tmp_info['s']=0;
        if(tmp_tr.find('input[type=checkbox]').eq(0).is(':checked')){
            tmp_info['enable'] = 1;
        }
        if(tmp_tr.find('input[type=checkbox]').eq(1).is(':checked')){
            tmp_info['y'] = 1;
            str+='1995';
        }
        if(tmp_tr.find('input[type=checkbox]').eq(2).is(':checked')){
            tmp_info['m'] = 1;
            str+='05';
        }
        if(tmp_tr.find('input[type=checkbox]').eq(3).is(':checked')){
            tmp_info['d'] = 1;
            str+='13';
        }
        if(tmp_tr.find('input[type=checkbox]').eq(4).is(':checked')){
            tmp_info['h'] = 1;
            str+='14';
        }
        if(tmp_tr.find('input[type=checkbox]').eq(5).is(':checked')){
            tmp_info['i'] = 1;
            str+='05';
        }
        if(tmp_tr.find('input[type=checkbox]').eq(6).is(':checked')){
            tmp_info['s'] = 1;
            str+='21';
        }
        tmp_info['nums'] = tmp_tr.find('input[type=text]').eq(1).val();
        if(tmp_info['nums']!=='' && !regular_positive.test(tmp_info['nums'])){
            var title = tmp_tr.find('td').eq(0).html();
            dump(title+' - 起始数必须为数值');
            return false;
        }else{
            str+=tmp_info['nums'];
        }
        tmp_tr.find('span').html(str);
        info.push(tmp_info);
    }
    return info;
} 
//保存编码规则
function save_number(){
    var info = number_info();
    if(info){
        $.post("/index/service/save_sys_number", {
            "info": info,
        }, function(re) {
            if (re === 'success') {
                dump('保存编码规则成功!');
            } else {
                dump('服务器响应超时!');
            }
        });
    }
}
