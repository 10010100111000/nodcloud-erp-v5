var item;
var item_info=[];
$(function(){ 
    //监听服务信息回车
    $("#item_soinfo").keydown(function() {
        if(event.keyCode == "13") {
            push_item(1,$("#item_soinfo").val());
            $("#item_soinfo").val('');//清空
        }
    });
    //监听选择服务事件
    $("#item_list").on("click",".lists",function(){
        var ape=$(this).attr('ape');
        add_item(item_list[ape]);//添加服务
    });
    //监听删除服务
    $("#item_tabinfo").on("click","i",function(){
        var ape=$(this).parent().parent().attr('ape');
        layui.use('layer', function() {
            layer.confirm('您确定要删除？', {
                btn: ['删除', '取消'], //按钮
                offset: '30%',
                shadeClose: true
            }, function() {
                item_info.splice(ape,1);
                apply_item();
                layer.close(layer.index);//关闭弹层
                hide_item_info();
                dump('删除成功！');
            });
        });
    });
    //监听设置服务事件
    $("#item_tabinfo").on("click","tr",function(){
        var ape=$(this).attr('ape');
        set_item(ape);//设置服务
    });
    //服务详情数据改变事件
    $("#show_item_info").on('input propertychange',function(){
        sum_item();
    });
}); 
//搜索服务
function so_item(){
    var info = $('#item_soinfo').val();
    push_item(1,info);
}
//填充服务搜索数据
//传入页数和搜索内容
function push_item(page,info){
    var ape=$('#item_list .right_bom');
    //获取数据
    $.ajax({
        type: 'POST',
        async: false,
        url: '/index/Service/cashier_item_list',
        data: {
            "page": page,
            "info": info
        },
		dataType: "json",
        success: function(re){
            ape.empty();//清空现有数据
            item_list=re.data;//转存新数据
            if(re.data.length>0){
                //item
                for (var i = 0; i < re.data.length; i++) {
                    var name=re.data[i].name;
                    var price=re.data[i].price;
                    var html='<div class="layui-col-sm2 lists" ape="'+i+'"><p title="'+name+'">'+name+'</p><span class="item">'+price+' 元</span></div>';
                    ape.append(html);
                }
                //判断是否单个服务
                if(re.count==1){
                    //添加服务
                    add_item(re.data[0]);
                    $('#item_soinfo').focus();
                    dump('自动加入服务列表');
                }
            }else{
                dump('[ '+info+' ] 未查到数据，换个条件试试？');
            }
            layui.use('laypage', function(){
                layui.laypage.render({
                    elem: 'item_page',
                    count: re.count, //数据总数
                    limit: re.limit, //数据总数
                    curr:page,
                    jump: function(obj, first){
                        //首次不执行
                        if(!first){
                            push_item(obj.curr,$('#item_soinfo').val());
                        }
                    }
                });
            });
        }
    });
}
//添加服务
function add_item(info){
    var repeat=false;
    for (var i = 0; i < item_info.length; i++) {
        if(item_info[i].id==info.id){
            repeat=i+1;//+1防止首次循环判断为假问题
            break;
        }
    }
    //判断是否存在重复
    if(repeat){
        //存在
        var tmp=item_info[repeat-1];
        item_info[repeat-1].set_nums=(tmp.set_nums-0)+1;//增加数量
        item_info[repeat-1].set_total=cal((tmp.set_nums-0)*(tmp.set_price-0));//设置总价 数量*单价
    }else{
        //不存在
        info.set_nums=1;//默认数量
        info.set_price=info.price;//默认单价
        info.set_total=info.price;//默认总价
        info.set_data='';//默认备注
        item_info.push(info);
    }
    apply_item();
    dump('已加入服务列表');
}
//渲染服务
function apply_item(){
    var tip_money=0;
    $('#item_tabinfo').empty();
    for (var i = 0; i < item_info.length; i++) {
        tip_money+=item_info[i].set_total-0;//累加金额
        var html='<tr ape="'+i+'"><td>'+item_info[i].name+'</td><td>'+item_info[i].set_nums+'</td><td class="total">￥'+item_info[i].set_total+'</td><td><i class="layui-icon">&#xe640;</i></td></tr>';
        $('#item_tabinfo').append(html);
    }
    $('#item_count').html(item_info.length);
    $('#item_money').html(tip_money);
}
//设置服务
function set_item(index){
    var info=item_info[index];
    //设置参数
    $('#show_item_info input').each(function(){
        //排除串码
        var id=$(this).attr('id');
        if(id){
            var ape=id.split('-');
            $(this).val(info[ape[1]]);
        }
    });
    $('.right .right_main').hide();
    $('#show_item_info').show();//显示详情
    $('#show_item_info').attr('ape',index);//转存INDEX
}
//隐藏服务详情
function hide_item_info(){
    $('.right .right_main').hide();
    $('#item_list').show();
}
//计算服务数据
function sum_item(){
    var index = $('#show_item_info').attr('ape');
    var set_nums = $('#item-set_nums').val();
    var set_price = $('#item-set_price').val();
    var data = $('#item-set_data').val();
    //兼容串码判断
    if(!regular_positive.test(set_nums)){
        dump('数量不正确');
    }else if(!regular_price.test(set_price)){
        dump('零售金额不正确');
    }else{
        item_info[index].set_nums=set_nums;//更新数量
        item_info[index].set_price=set_price;//更新单价
        item_info[index].set_data=data;//更新备注
        var set_total=cal((set_nums-0)*(set_price-0));//计算金额
        item_info[index].set_total=set_total;//更新价格
        $('#item-set_total').val(set_total);//更新价格
        apply_item();
    }
}