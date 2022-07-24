var element;
$(function(){
    //切换菜单
    $(".tab p").click(function(){
        var tmp = $(this).index();
        $(this).parent().find('p').removeClass().eq(tmp).addClass('set_tab_show');
        if(tmp){
            $('.sys').hide();
            $('.often').show();
        }else{
            $('.sys').show();
            $('.often').hide();
        }
    });
    //二级菜单
    $(".sys li").hover(function(){  
        $('.more').hide();
        //当前LI距离顶部部高度
        var top_hight =  $(this).offset().top;
        //当前要显示的DIV的高度
        var div_hight=$(this).find('.more').outerHeight(true);
        //计算要显示的DIV距离顶部的高度
        var tmp_hight=top_hight-(div_hight-64)/2;
        //判断可视区域是否够绘制显示区域
        var hight=$(window).height()-(tmp_hight+div_hight);
        if(hight >= 0){
            $(this).find('.more').show().css('top',tmp_hight);
        }else{
            $(this).find('.more').show().css('top',top_hight+64-div_hight);
        }
    },function(){  
        $('.more').hide();
    });
    
    //报表菜单
    $(".sys .more div").hover(function(){  
        $('tip').hide();
        $(this).find('tip').show();
    });
    
    
    //个人主页
    $(".top li").hover(function(){  
        $('.top_more').hide();
        $(this).find('.top_more').show();
    },function(){  
        $('.top_more').hide();
    });
    $('.layui-tab-title').on('mousedown','li',function(re){
        if (re.which == 3) {
            if(window.confirm('请再次确定关闭其他页面?')){
                var tmp_arr=[];
                for (var i = 0; i <= $('.layui-tab-title li').length; i++) {
                    if($('.layui-tab-title li').eq(i).attr('lay-id') !== $(this).attr('lay-id')){
                        tmp_arr.push($('.layui-tab-title li').eq(i).attr('lay-id'));
                    }
                }
                for (var s = 0; s < tmp_arr.length; s++) {
                    element.tabDelete('tabs',tmp_arr[s]); //关闭其他选项
                }
            }
        }
    });
});
//Tab的切换功能
layui.use('element', function(){
    element = layui.element;
    $('.layui-tab-title li').eq(0).find('i').hide(); //主页不可关闭
});
//设置选中
function set_tab(id,name,main) {
    if($("li[lay-id='"+id+"']").length === 0){
        //新增
        element.tabAdd('tabs', {
            id: id,
            title: name,
            content: '<iframe class="iframe_box" src="'+main+'" frameborder="0"></iframe>'
        });
    }
    element.tabChange('tabs', id); 
    $('.more').hide();
}
//修改个人信息
function top_user_info(id){
    var html = '<div class="pop_info"><div class="layui-form layui-form-pane re_padding"><div class="layui-form-item"><label class="layui-form-label">名称</label><div class="layui-input-block"><input type="text" id="name" placeholder="请输入用户名称" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">账号</label><div class="layui-input-block"><input type="text" id="user" placeholder="请输入用户账号" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">密码</label><div class="layui-input-block"><input type="text" id="pwd" placeholder="不修改密码请留空" class="layui-input"></div></div></div></div>';
    layui.use('layer', function() {
        layer.ready(function(){
            layer.open({
                type: 1,
                title: '账号设置',
                skin: 'layui-layer-rim', //加上边框
                area: ['366px', '290px'], //宽高
                content: html,
                btn: ['保存', '取消'],
                success: function(layero, index) {
                    //弹出后回调
                    //ID不为空获取详情
                    if (id !== undefined) {
                        $.post("/index/service/user_info", {
                            "id": id,
                        }, function(re) {
                            $('#name').val(re.name);
                            $('#user').val(re.user);
                        });
                    }
                },
                btn1: function(layero) {
                    //保存用户信息
                    var name = $('#name').val();
                    var user = $('#user').val();
                    var pwd = $('#pwd').val();
                    if(name===""){
                        dump('姓名不可为空!');
                    }else if(user===""){
                        dump('账号不可为空!');
                    }else{
                        $.post("/index/service/save_user", {
                            "id": id,
                            "name": name,
                            "user": user,
                            "pwd": pwd,
                        }, function(re) {
                            if(re==="success"){
                                alert_info('保存成功！')
                            }
                        });
                    }
                }
            });
        });
    });
}
//退出登录
function out_sys(){
    layui.use('layer', function() {
        layer.confirm('您确定要退出登录吗？', {
            offset: '99px',
            btn: ['确定', '取消'] //按钮
        }, function() {
            window.location.href='/index/service/out_sys';
        });
    });
}