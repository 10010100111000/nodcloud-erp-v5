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
    var customer = $('#customer').attr('ape');
    var time = $('#time').val();
    var number = $('#number').val();
    var total = $('#total').val();
    var discount = $('#discount').val();
    var money = $('#money').val();
    var user = $('#user').val();
    var account = $('#account').val();
    var file = $('#file').attr('ape');
    var data = $('#data').val();
    if(customer===""){
        dump('客户不可为空');
    }else if(!regular_time.test(time)){
        dump('单据日期不正确');
    }else if(number===""){
        dump('单据编号不可为空');
    }else{
        //判断表格合法性
        var info = tab_info();
        if(info){
            if(!regular_price.test(discount)){
                dump('优惠金额不正确');
            }else if(!regular_price.test(money)){
                dump('实收金额不正确');
            }else{
                //计算金额是否合法
                if((discount-0)>(total-0)){
                    dump('优惠金额不可大于单据金额');
                }else if((money-0)+(discount-0)>(total-0)){
                    dump('实收金额加优惠金额不可大于单据金额');
                }else if(account===""){
                    dump('结算账户不可为空');
                }else{
                    //提交信息
                    $(ape).attr('disabled',true);
                    $.post("/index/service/save_itemorder", {
                        "id": id,
                        "customer": customer,
                        "time": time,
                        "number": number,
                        "total": total,
                        "discount": discount,
                        "money": money,
                        "user": user,
                        "account": account,
                        "file": file,
                        "data": data,
                        "info": info
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
                tmp['item']=info[i].set_id;//服务项目ID
                tmp['nums']=info[i].nums;
                tmp['price']=info[i].price;
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
            $.post("/index/service/auditing_itemorder", {
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
//计算金额
function cal_money(){
    var total = $('#total').val();
    var discount = $('#discount').val();
    if(total===""){
        dump('请先录入服务项目数据');
    }else if(!regular_price.test(discount)){
        dump('优惠金额不正确');
    }else if((discount-0)>(total-0)){
        dump('优惠金额不可大于单据金额');
    }else{
        layui.use('layer', function() {
            layer.confirm('是否自动计算实收金额？', {
                btn: ['计算', '取消'], //按钮
                offset: '6%'
            }, function() {
                $('#money').val(cal((total-0)-(discount-0)));
                layer.closeAll();
            });
        });
    }
}