$(function(){
    layui.use('table', function() {
        var width=$(window).width();
        var ape_cols=[
            {field: 'name', title: '客户名称', width: 200, align:'center'},
            {field: 'number', title: '客户编号', width: 200, align:'center'},
            {field: 'tel', title: '手机号', width: 200, align:'center'},
            {field: 'data', title: '备注信息', width: 200, align:'center'},
            {field: 'set', title: '相关操作', width: 211, align:'center',templet: '<div><div class="layui-btn-group"><button class="layui-btn layui-btn-primary layui-btn-sm" onclick="customer_info({{d.id}})"><i class="layui-icon">&#xe642;</i></button> <button class="layui-btn layui-btn-primary layui-btn-sm" onclick="customer_set({{d.id}})"><i class="layui-icon">&#xe620;</i></button> <button class="layui-btn layui-btn-primary layui-btn-sm" onclick="customer_form({{d.id}})"><i class="layui-icon">&#xe63c;</i></button> <button class="layui-btn layui-btn-primary layui-btn-sm" onclick="del_customer({{d.id}})"><i class="layui-icon">&#xe640;</i></button></div></div>'},
        ];//表格选项
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            cols:  [ape_cols],
            even: ape_even,
            url: '/index/service/customer_list',
            page: true,
            limits: [30,60,90,150,300],
            method: 'post',
            where: push_so_arr(),
        });//渲染表格 
    }); 
});
//条件搜索
function so() {
    layui.use('table', function() {
        layui.table.reload('ape_table',{
            where: push_so_arr(),
            page:1
        });
    });
}
//删除
function del_customer(id) {
    if(root_del){
        layui.use('layer', function() {
            layer.confirm('您确定要删除？', {
                btn: ['删除', '取消'], //按钮
                offset: '6%',
                shadeClose: true
            }, function() {
                $.post("/index/service/del_customer", {
                    "id": id
                }, function(re) {
                    if (re === 'success') {
                        re_alert('删除成功!');
                    }else if(re === 'error'){
                        dump('当前数据已经发生业务操作,删除失败!');
                    } else {
                        dump('服务器响应超时!');
                    }
                });
            });
        });
    }else{
        dump('很遗憾,您无权操作!');
    }    
}
//显示详情
function customer_info(id) {
    if(root_edit){
        var html = '<div class="info"><div class="layui-form layui-form-pane re_padding"><table style="width:100%;border-collapse:inherit;border-spacing:3px;font-size:inherit"><tr><td><div class="layui-form-item"><label class="layui-form-label">客户名称</label><div class="layui-input-block"><input type="text" id="name" placeholder="请输入客户名称" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">联系人</label><div class="layui-input-block"><input type="text" id="contacts" placeholder="请输入联系人" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">客户编号</label><div class="layui-input-block"><input type="text" id="number" placeholder="请输入客户编号" class="layui-input"></div></div></td></tr><tr><td><div class="layui-form-item"><label class="layui-form-label">手机号</label><div class="layui-input-block"><input type="text" id="tel" placeholder="请输入手机号" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">客户生日</label><div class="layui-input-block"><input type="text" id="birthday" placeholder="生日格式:05-13" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">客户地址</label><div class="layui-input-block"><input type="text" id="add" placeholder="请输入客户地址" class="layui-input"></div></div></td></tr><tr><td><div class="layui-form-item"><label class="layui-form-label">客户积分</label><div class="layui-input-block"><input type="text" id="integral" disabled class="layui-input" value="0"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">开户名</label><div class="layui-input-block"><input type="text" id="accountname" placeholder="请输入开户名" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">开户行</label><div class="layui-input-block"><input type="text" id="openingbank" placeholder="请输入开户行" class="layui-input"></div></div></td></tr><tr><td><div class="layui-form-item"><label class="layui-form-label">银行账号</label><div class="layui-input-block"><input type="text" id="bankaccount" placeholder="请输入银行账号" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">税号</label><div class="layui-input-block"><input type="text" id="tax" placeholder="请输入税号" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">社交账号</label><div class="layui-input-block"><input type="text" id="other" placeholder="请输入社交账号" class="layui-input"></div></div></td></tr><tr><td><div class="layui-form-item"><label class="layui-form-label">邮箱地址</label><div class="layui-input-block"><input type="text" id="email" placeholder="请输入邮箱地址" class="layui-input"></div></div></td><td colspan="2"><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text" id="data" placeholder="请输入备注信息" class="layui-input"></div></div></td></tr></table></div></div>';
        layui.use('layer', function() {
            layer.ready(function() {
                layer.open({
                    type: 1,
                    title: '客户详情',
                    skin: 'layui-layer-rim', //加上边框
                    area: ['900px', '420px'], //宽高
                    offset: '6%',
                    content: html,
                    btn: ['保存', '取消'],
                    fixed: false,
                    shadeClose: true,
                    success: function(layero, index) {
                        //弹出后回调
                        if (id !== undefined) {
                            $.post("/index/service/customer_info", {
                                "id": id,
                            }, function(re) {
                                $('#name').val(re.name);
                                $('#contacts').val(re.contacts);
                                $('#number').val(re.number);
                                $('#tel').val(re.tel);
                                $('#birthday').val(re.birthday);
                                $('#add').val(re.add);
                                $('#integral').val(re.integral);
                                $('#accountname').val(re.accountname);
                                $('#openingbank').val(re.openingbank);
                                $('#bankaccount').val(re.bankaccount);
                                $('#tax').val(re.tax);
                                $('#other').val(re.other);
                                $('#email').val(re.email);
                                $('#data').val(re.data);
                            });
                        }
                    },
                    btn1: function(layero) {
                        //保存
                        var name = $('#name').val();
                        var contacts = $('#contacts').val();
                        var number = $('#number').val();
                        var tel = $('#tel').val();
                        var birthday = $('#birthday').val();
                        var add = $('#add').val();
                        var accountname = $('#accountname').val();
                        var openingbank = $('#openingbank').val();
                        var bankaccount = $('#bankaccount').val();
                        var tax = $('#tax').val();
                        var other = $('#other').val();
                        var email = $('#email').val();
                        var data = $('#data').val();
                        if (name === "") {
                            dump('客户名称不可为空!');
                        } else {
                            //提交信息
                            $('.layui-layer-btn0').unbind();//解除绑定事件
                            $.post("/index/service/save_customer", {
                                "id": id,
                                "name": name,
                                "contacts": contacts,
                                "number": number,
                                "tel": tel,
                                "birthday": birthday,
                                "add": add,
                                "accountname": accountname,
                                "openingbank": openingbank,
                                "bankaccount": bankaccount,
                                "tax": tax,
                                "other": other,
                                "email": email,
                                "data": data
                            }, function(re) {
                                if (re === "success") {
                                    re_alert('客户详情保存成功!');
                                }else{
                                    alert_info('服务器响应超时!');
                                }
                            });
                        }
                    }
                });
            });
        });
    }else{
        dump('很遗憾,您无权操作!');
    }
}
//导入
function import_data(){
    var html='<div class="info xls"><div class="layui-form layui-form-pane re_padding"><div class="layui-form-item"><ul><li>1.该功能适用于批量导入客户数据。</li><li>2.您需要下载数据模板后使用Excel录入数据。</li><li>3.录入数据时，请勿修改首行标题以及数据格式。</li><li>4.标题为红色的列数据不可为空，否则将跳过该行数据。</li><li>5.点击下方上传文件按钮，选择您编辑好的文件即可。</li></ul></div><hr><div class="layui-form-item btn"><button class="layui-btn" onclick="down_xls()">下载模板</button> <button class="layui-btn layui-btn-primary" id="up_btn">上传文件</button></div></div></div>';
    layui.use(['layer','upload'], function() {
        layer.ready(function() {
            layer.open({
                type: 1,
                title: '导入数据',
                skin: 'layui-layer-rim', //加上边框
                area: ['500px', '320px'], //宽高
                offset: '6%',
                content: html,
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    //弹出后回调
                    layui.upload.render({
                        elem: '#up_btn',
                        url: '/index/service/import_customer',
                        accept: 'file',
                        exts: 'xls',
                        done: function(re) {
                            if(re.msg==="success"){
                                re_alert('恭喜你，成功导入'+re.nums+'条数据！');
                            }else{
                                alert_info(re.msg);
                            }
                        }
                    });
                }
            });
        });
    });
    
}
//下载模板
function down_xls(){
    alert_info('稍等，即将下载模板','//cdn.nodcloud.com/erp/xls/customer.xls',true);
}
//导出
function export_data(){
    var url_info = push_so_info();
    alert_info('稍等，数据请求中',"/index/export/customer?"+url_info,true);
}
//客户积分详情表
function customer_form(id){
    //iframe层
    layui.use('form', function(){
        layer.open({
          type: 2,
          title: '积分详情 - 报表',
          offset: '6%',
          area: ['66%', '66%'],
          shadeClose: true,
          content: '/index/main/customer_form?id='+ id
        }); 
    }); 
}
//积分操作
function customer_set(id) {
    var html = '<div class="info"><div class="layui-form layui-form-pane re_padding"><div class="layui-form-item" pane=""><label class="layui-form-label">操作类型</label><div class="layui-input-block"><input type="radio" name="set" value="inc" title="增加积分" checked> <input type="radio" name="set" value="dec" title="减少积分"></div></div><div class="layui-form-item"><label class="layui-form-label">积分数值</label><div class="layui-input-block"><input type="text" id="integral" placeholder="请输入积分数值" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text" id="data" placeholder="请输入备注信息" class="layui-input"></div></div></div></div>';
    layui.use(['layer','form'], function() {
        layer.ready(function() {
            var form = layui.form;
            layer.open({
                type: 1,
                title: '积分操作',
                skin: 'layui-layer-rim', //加上边框
                area: ['520px', '280px'], //宽高
                offset: '6%',
                content: html,
                btn: ['保存', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    //弹出后回调
                    form.render('radio'); //更新全部
                },
                btn1: function(layero) {
                    //保存
                    var set = $("input[name='set']:checked").val();
                    var integral = $('#integral').val();
                    var data = $('#data').val();
                    if(!regular_price.test(integral) || integral=='0'){
                        dump('积分数值填写错误');
                    } else {
                        //提交信息
                        $('.layui-layer-btn0').unbind();//解除绑定事件
                        $.post("/index/service/save_customer_integral", {
                            "id": id,
                            "set": set,
                            "integral": integral,
                            "data": data
                        }, function(re) {
                            if (re === "success") {
                                re_alert('客户积分保存成功!');
                            }else{
                                alert_info('服务器响应超时!');
                            }
                        });
                    }
                }
            });
        });
    });
}
