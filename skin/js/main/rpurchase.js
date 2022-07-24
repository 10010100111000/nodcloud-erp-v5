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
    var supplier = $('#supplier').attr('ape');
    var time = $('#time').val();
    var number = $('#number').val();
    var total = $('#total').val();
    var actual = $('#actual').val();
    var money = $('#money').val();
    var user = $('#user').val();
    var account = $('#account').val();
    var file = $('#file').attr('ape');
    var data = $('#data').val();
    if(!supplier){
        dump('供应商不可为空');
    }else if(!regular_time.test(time)){
        dump('单据日期不正确');
    }else if(number===""){
        dump('单据编号不可为空');
    }else{
        //判断表格合法性
        var info = tab_info();
        if(info){
            if(!regular_price.test(actual)){
                dump('实际金额不正确');
            }else if(!regular_price.test(money)){
                dump('实付金额不正确');
            }else{
                //计算实际金额是否合法
                if((actual-0)>(total-0)){
                    dump('实际金额不可大于单据金额');
                }else if((money-0)>(actual-0)){
                    dump('实付金额不可大于实际金额');
                }else if(!user){
                    dump('制单人不可为空');
                }else if(!account){
                    dump('结算账户不可为空');
                }else{
                    //提交信息
                    $(ape).attr('disabled',true);
                    $.post("/index/service/save_rpurchase", {
                        "id": id,
                        "supplier": supplier,
                        "time": time,
                        "number": number,
                        "total": total,
                        "actual": actual,
                        "money": money,
                        "user": user,
                        "account": account,
                        "file": file,
                        "data": data,
                        "info": info,
                    }, function(re) {
                        if (re.state === "success") {
                            alert_info('单据提交成功!');
                        }else if(re.state === "serial_repeat"){
                            dump('第'+re.row+'行商品串码重复录入!');
                            $(ape).attr('disabled',false);
                        }else{
                            dump('服务器响应超时!');
                        }
                    });
                }
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
            var tmp={};
            tmp['oid']=info[i].oid;
            tmp['goods']=info[i].set_id;
            tmp['serialtype']=info[i].serialtype;
            tmp['attr']=info[i].attr_ape;
            tmp['warehouse']=info[i].warehouse_id;
            tmp['serial']=info[i].serial;
            tmp['nums']=info[i].nums;
            tmp['price']=info[i].price;
            tmp['total']=info[i].total;
            tmp['batch']=info[i].batch;
            tmp['data']=info[i].data;
            arr.push(tmp);
        }
        return arr;
    }else{
        return false;
    }
}
//计算金额
function cal_actual(){
    var total = $('#total').val();
    var actual = $('#actual').val();
    if(total===""){
        dump('请先录入商品数据');
    }else if(!regular_price.test(actual)){
        dump('实际金额不正确');
    }else if((actual-0)>(total-0)){
        dump('实际金额不可大于单据金额');
    }
}
//审核
function auditing(id,type){
    layui.use('layer', function() {
        var tip=(type ? '审核后将操作商品库存以及资金账户,请再次确定？':'反审核后将反操作库存以及资金账户,请再次确定？')
        //审核
        layer.confirm(tip, {
            btn: ['确定', '取消'], //按钮
            offset: '12%'
        }, function() {
            $('.layui-layer-btn0').unbind();//解除绑定事件
            $.post("/index/service/auditing_rpurchase", {
                "id": id
            }, function(re) {
                if (re.state === "success") {
                    alert_info('操作成功!');
                }else if(re.state === "serial_repeat"){
                    dump('第'+re.row+'行商品串码重复录入!');
                }else if(re.state === "nums_error"){
                    dump('第'+re.row+'行商品该次入库数量大于可入库数量['+re.apenums+']');
                }else if(re.state === "set_error"){
                    dump('第'+re.row+'行商品已经发生过业务，反审核失败!');
                }else{
                    dump('服务器响应超时!');
                }
            });
        });
    });
}




















