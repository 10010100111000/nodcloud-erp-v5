//查看文件
$(document).on("click", "#file", function() {
    var url=$('#file').attr('ape');
    if(url!=='0'){
        alert_info('稍等，数据请求中~',url,true);
    }
});
//提交|修改表单
function save(ape,id){
    $("#table").jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
    var time = $('#time').val();
    var number = $('#number').val();
    var user = $('#user').val();
    var file = $('#file').attr('ape');
    var data = $('#data').val();
    if(!regular_time.test(time)){
        dump('单据日期不正确');
    }else if(number===""){
        dump('单据编号不可为空');
    }else{
        //判断表格合法性
        var info = tab_info();
        if(info){
            if(!user){
                dump('制单人不可为空');
            }else{
                //提交信息
                $(ape).attr('disabled',true);
                $.post("/index/service/save_opurchase", {
                    "id": id,
                    "time": time,
                    "number": number,
                    "user": user,
                    "file": file,
                    "data": data,
                    "info": info,
                }, function(re) {
                    if (re.state === "success") {
                        alert_info('单据提交成功!');
                    }else{
                        dump('服务器响应超时!');
                    }
                });
            }
        }
    }
    
}
//获取表格信息
function tab_info(){
    if(check_tab(false)){
        var info = $("#table").jqGrid("getGridParam",'data'); //获取表格数据
        var arr=[];
        for (var i = 0; i < info.length; i++) {
            if(info[i].set_id !== "-1"){
                var tmp={};
                tmp['goods']=info[i].set_id;
                tmp['attr']=info[i].attr_ape;
                tmp['nums']=info[i].nums;
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
        var tip=(type ? '审核后将生成采购入库单,请再次确定？':'您确定要反审核该订单,请再次确定？')
        //审核
        layer.confirm(tip, {
            btn: ['确定', '取消'], //按钮
            offset: '12%'
        }, function() {
            $('.layui-layer-btn0').unbind();//解除绑定事件
            $.post("/index/service/auditing_opurchase", {
                "id": id
            }, function(re) {
                if (re.state === "success") {
                    alert_info('操作成功!');
                    //待修复反审核判断
                }else if(re.state === "error"){
                    dump('该单据发生过入库行为,反审核失败!');
                }else{
                    dump('服务器响应超时!');
                }
            });
        });
    });
}




















