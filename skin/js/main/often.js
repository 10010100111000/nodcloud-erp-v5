$(function(){
    layui.use('form', function(){
        var form = layui.form;
        form.on('checkbox(often)', function(data){
            if($('.show_box input:checked').length > 8){
                dump('最多可选择八个常用功能');
                $(data.elem).attr('checked',false);
                form.render();
            }
        });
    });
    //判断零售显示情况
    if(!cashier_type){
        $('.cashier_info').hide();
    }
})
//保存配置
function save(){
    var checked_info=$('.show_box input:checked');
    var info=[];
    for (var i = 0; i < checked_info.length; i++) {
        var tmp={};
        var ape=$(checked_info[i]);
        tmp['name']=ape.attr('title');
        tmp['set']=ape.attr('set');
        info.push(tmp);
    }
    $.post("/index/service/save_often", {
        "info": info
    }, function(re) {
        if (re === 'success') {
            alert_info('配置保存成功!');
        } else {
            dump('服务器响应超时！');
        }
    });
}