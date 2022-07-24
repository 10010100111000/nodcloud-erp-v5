var goods_list;
var goods_info=[];
$(function(){ 
    //监听商品信息回车
    $("#goods_soinfo").keydown(function() {
        if(event.keyCode == "13") {
            push_goods(1,$("#goods_soinfo").val());
            $("#goods_soinfo").val('');//清空
        }
    });
    //监听选择商品事件
    $("#goods_list").on("click",".lists",function(){
        var ape=$(this).attr('ape');
        add_goods(goods_list[ape]);//添加商品
    });
    //监听删除商品
    $("#goods_tabinfo").on("click","i",function(){
        var ape=$(this).parent().parent().attr('ape');
        layui.use('layer', function() {
            layer.confirm('您确定要删除？', {
                btn: ['删除', '取消'], //按钮
                offset: '30%',
                shadeClose: true
            }, function() {
                goods_info.splice(ape,1);
                apply_goods();
                layer.close(layer.index);//关闭弹层
                hide_goods_info();
                dump('删除成功！');
            });
        });
    });
    //监听设置商品事件
    $("#goods_tabinfo").on("click","tr",function(){
        var ape=$(this).attr('ape');
        set_goods(ape);//设置商品
    });
    //商品详情数据改变事件
    $("#show_goods_info").on('input propertychange',function(){
        sum_goods();
    });
}); 
//搜索商品
function so_goods(){
    var info = $('#goods_soinfo').val();
    push_goods(1,info);
}
//填充商品搜索数据
//传入页数和搜索内容
function push_goods(page,info){
    var ape=$('#goods_list .right_bom');
    //获取数据
    $.ajax({
        type: 'POST',
        async: false,
        url: '/index/Service/cashier_goods_list',
        data: {
            "page": page,
            "info": info
        },
		dataType: "json",
        success: function(re){
            ape.empty();//清空现有数据
            goods_list=re.data;//转存新数据
            if(re.data.length>0){
                for (var i = 0; i < re.data.length; i++) {
                    var name=re.data[i].name;
                    var more=re.data[i].warehouse+' / '+re.data[i].attr;
                    var retail=re.data[i].retail;
                    var html='<div class="layui-col-sm2 lists" ape="'+i+'"><p title="'+name+'">'+name+'</p><div><more title="'+more+'">'+more+'</more><span>'+retail+' 元</span></div></div>';
                    ape.append(html);
                }
                //判断是否单个商品
                if(re.count==1){
                    //添加商品
                    add_goods(re.data[0]);
                    $('#goods_soinfo').focus();
                    dump('自动加入商品列表');
                }
            }else{
                dump('[ '+info+' ] 未查到数据，换个条件试试？');
            }
            layui.use('laypage', function(){
                layui.laypage.render({
                    elem: 'goods_page',
                    count: re.count, //数据总数
                    limit: re.limit, //数据总数
                    curr:page,
                    jump: function(obj, first){
                        //首次不执行
                        if(!first){
                            push_goods(obj.curr,$('#goods_soinfo').val());
                        }
                    }
                });
            });
        }
    });
}
//添加商品
function add_goods(info){
    var repeat=false;
    for (var i = 0; i < goods_info.length; i++) {
        if(goods_info[i].id==info.id){
            repeat=i+1;//+1防止首次循环判断为假问题
            break;
        }
    }
    //判断是否存在重复
    if(repeat){
        //存在
        var tmp=goods_info[repeat-1];
        goods_info[repeat-1].set_nums=(tmp.set_nums-0)+1;//增加数量
        goods_info[repeat-1].set_total=cal((tmp.set_nums-0)*(tmp.set_price-0)*(tmp.set_discount-0));//设置总价 数量*单价*折扣
        goods_info[repeat-1].set_integral=cal((tmp.set_nums-0)*(tmp.integral-0));//设置积分 数量*积分
    }else{
        //不存在
        info.set_nums=1;//默认数量
        info.set_price=info.retail;//默认单价
        info.set_discount=1;//默认折扣
        info.set_total=info.retail;//默认总价
        info.set_serial=[];//默认串码
        info.set_integral=info.integral;//默认积分
        info.set_data='';//默认备注
        goods_info.push(info);
    }
    apply_goods();
    dump('已加入商品列表');
}
//渲染商品
function apply_goods(){
    var tip_money=0;
    $('#goods_tabinfo').empty();
    for (var i = 0; i < goods_info.length; i++) {
        tip_money+=goods_info[i].set_total-0;//累加金额
        var html='<tr ape="'+i+'"><td>'+goods_info[i].name+'</td><td>'+goods_info[i].set_nums+'</td><td class="total">￥'+goods_info[i].set_total+'</td><td><i class="layui-icon">&#xe640;</i></td></tr>';
        $('#goods_tabinfo').append(html);
    }
    $('#goods_count').html(goods_info.length);
    $('#goods_money').html(tip_money);
}
//设置商品
function set_goods(index){
    var info=goods_info[index];
    //设置串码
    $('#goods-set_serial').empty();
    var serial=info['serial'].split(',');
    if(serial[0]!=''){
        for (var i = 0; i < serial.length; i++) {
            $('#goods-set_serial').append('<option value="'+serial[i]+'">'+serial[i]+'</option>');
        }
        $('#goods-set_serial').val(info.set_serial).select2({width:'100%',placeholder: "请选择串码"}).on("change",function(e){
            var vals=$(this).select2('val');
            if(vals!=null){
                $('#goods-set_nums').val(vals.length);
                sum_goods();
            }
        });//赋值监听
    }else{
        $('#goods-set_serial').select2({width:'100%',placeholder: "无需串码"});//赋值
    }
    //设置其他参数
    $('#show_goods_info input').each(function(){
        //排除串码
        var id=$(this).attr('id');
        if(id){
            var ape=id.split('-');
            $(this).val(info[ape[1]]);
        }
    });
    $('#show_goods_info input:gt(8)').attr('disabled',true);//禁止修改
    $('.right .right_main').hide();
    $('#show_goods_info').show();//显示详情
    $('#show_goods_info').attr('ape',index);//转存INDEX
}
//隐藏商品详情
function hide_goods_info(){
    $('.right .right_main').hide();
    $('#goods_list').show();
}
//计算商品数据
function sum_goods(){
    var index = $('#show_goods_info').attr('ape');
    var set_serial = $('#goods-set_serial').select2('val');
    var set_nums = $('#goods-set_nums').val();
    var set_price = $('#goods-set_price').val();
    var set_discount = $('#goods-set_discount').val();
    var data = $('#goods-data').val();
    //兼容串码判断
    if(goods_info[index].serial!='' && set_serial==null){
        dump('请先选择串码');
    }else if(goods_info[index].serial!='' && set_serial.length!=(set_nums-0)){
        dump('串码个数与商品个数不符，请核实!');
    }else if(!regular_positive.test(set_nums)){
        dump('数量不正确');
    }else if(set_nums>goods_info[index].nums){
        dump('数量不可大于库存数[ '+goods_info[index].nums+' ]');
    }else if(!regular_price.test(set_price)){
        dump('零售金额不正确');
    }else if(!regular_discount.test(set_discount) || (set_discount-0)==0){
        dump('折扣不正确[0.00-1]');
    }else{
        goods_info[index].set_nums=set_nums;//更新数量
        goods_info[index].set_price=set_price;//更新单价
        goods_info[index].set_discount=set_discount;//更新折扣
        goods_info[index].data=data;//更新备注
        var set_total=cal((set_nums-0)*(set_price-0)*(set_discount-0));//计算金额
        var set_integral=cal((set_nums-0)*(goods_info[index].integral-0));//计算积分
        goods_info[index].set_total=set_total;//更新价格
        goods_info[index].set_integral=set_integral;//更新积分
        if(set_serial!=null){
            goods_info[index].set_serial=set_serial;//更新串码
        }
        $('#goods-set_total').val(set_total);//更新价格
        $('#goods-set_integral').val(set_integral);//更新积分
        apply_goods();
    }
}