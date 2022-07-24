//登陆页
$(function(){
    layui.use('layer');  
    var nums= Math.floor(Math.random()*21+1); 　//输出1～21之间的随机整数
    $('.background').attr('src','/skin/images/login/'+nums+'.jpg');
    if( window.top != window.self ){
        top.window.location.href='/';
    }
});
//验证账号密码
function login(){
    var layer = layui.layer;
    var user = $('#user').val();
    var pwd = $('#pwd').val();
    if(user === ""){
        layer.msg('请输入账号！');
    }else if(pwd=== ""){
        layer.msg('请输入密码！');
    }else{
        $.post("/index/index/check_user",{"user":user,"pwd":pwd}, function(re) {
            if(re==='error'){
                layer.msg('账号或密码错误，请核实!');
            }else if(re==='success'){
                location.reload();
            }else{
                layer.msg('服务器响应超时！');
            }
        });
    }
}
//回车事件
document.onkeydown = function (event) {
    var e = event || window.event || arguments.callee.caller.arguments[0];
    if (e && e.keyCode == 13) {
        login();
    }
};