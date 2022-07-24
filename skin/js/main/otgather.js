//提交|修改表单
function save(ape,id){
    $("#table").jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
    var time = $('#time').val();
    var number = $('#number').val();
    var user = $('#user').val();
    var data = $('#data').val();
    if(!regular_time.test(time)){
        dump('单据日期不正确');
    }else if(number===""){
        dump('单据编号不可为空');
    }else{
        //判断表格合法性
        var info = tab_info();
        if(info){
            //提交信息
            $(ape).attr('disabled',true);
            $.post("/index/service/save_otgather", {
                "id": id,
                "time": time,
                "number": number,
                "user": user,
                "data": data,
                "info": info,
            }, function(re) {
                if(re.state === "success") {
                    alert_info('单据提交成功!');
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
                tmp['account']=info[i].set_id;//资金账户ID
                tmp['total']=info[i].total;
                tmp['data']=info[i].data;
                arr.push(tmp);
            }
        }
        if(arr.length===0){
            dump('您还未录入数据!');
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
        var tip=(type ? '审核后将操作资金账户,请再次确定？':'反审核后将反操作资金账户,请再次确定？')
        //审核
        layer.confirm(tip, {
            btn: ['确定', '取消'], //按钮
            offset: '12%'
        }, function() {
            $('.layui-layer-btn0').unbind();//解除绑定事件
            $.post("/index/service/auditing_otgather", {
                "id": id
            }, function(re) {
                if (re.state === "success") {
                    alert_info('操作成功!');
                }else{
                    dump('服务器响应超时!');
                }
            });
        });
    });
}




















