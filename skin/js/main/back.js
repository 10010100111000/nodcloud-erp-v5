//数据库操作|新增|恢复备份
function back_info(file,ape) {
    if(file===undefined){
        //数据备份
        $('#back_btn').attr('disabled',true);
        $.post("/index/service/back_db", {
            "by": 'nodcloud.com'
        }, function(re) {
            if (re === 'success') {
                alert_info('数据库备份成功!');
            } else {
                dump('服务器响应超时！');
            }
        });
    }else{
        if(root_edit){
            layui.use('layer', function() {
                //恢复备份
                layer.confirm('您确定要恢复该备份吗？', {
                    btn: ['恢复', '取消'], //按钮
                    offset: '6%'
                }, function() {
                    $('.layui-layer-btn0').unbind();//解除绑定事件
                    $.post("/index/service/re_sn_db", {
                        "file": file
                    }, function(re) {
                        if (re === 'success') {
                            alert_info('恢复数据成功!');
                        } else {
                            dump('服务器响应超时！');
                        }
                    });
                });
            });
        }else{
            dump('很遗憾,您无权操作!');
        }
    }
}
//删除备份
function del_sn_db(file,ape){
    if(root_del){
        layui.use('layer', function() {
            layer.confirm('您确定要删除该备份吗？', {
                btn: ['删除', '取消'], //按钮
                offset: '6%'
            }, function() {
                $('.layui-layer-btn0').unbind();//解除绑定事件
                $.post("/index/service/del_sn_db", {
                    "file": file
                }, function(re) {
                    if (re === 'success') {
                        alert_info('删除备份数据成功!');
                    } else {
                        dump('服务器响应超时！');
                    }
                });
            });
        });
    }else{
        dump('很遗憾,您无权操作!');
    } 
}
//说明
function info(){
    dump('1.恢复备份后将直接更新系统。<br/>2.备份数据删除后将无法恢复。<br/>3.数据备份文件最多保存12份。<br/>4.文件超出删除最早备份文件。');
}
