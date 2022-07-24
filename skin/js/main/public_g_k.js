//显示详情
function supplier_info(id,root_edit) {
    if(root_edit){
        var html = '<div class="info"><div class="layui-form layui-form-pane re_padding"><table style="width:100%;border-collapse:inherit;border-spacing:3px;font-size:inherit"><tr><td><div class="layui-form-item"><label class="layui-form-label">供应商名称</label><div class="layui-input-block"><input type="text" id="public_name" placeholder="请输入供应商名称" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">供应商编号</label><div class="layui-input-block"><input type="text" id="public_number" placeholder="请输入供应商编号" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">供应商地址</label><div class="layui-input-block"><input type="text" id="public_add" placeholder="请输入供应商地址" class="layui-input"></div></div></td></tr><tr><td><div class="layui-form-item"><label class="layui-form-label">开户名</label><div class="layui-input-block"><input type="text" id="public_accountname" placeholder="请输入开户名" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">开户行</label><div class="layui-input-block"><input type="text" id="public_openingbank" placeholder="请输入开户行" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">银行账号</label><div class="layui-input-block"><input type="text" id="public_bankaccount" placeholder="请输入银行账号" class="layui-input"></div></div></td></tr><tr><td><div class="layui-form-item"><label class="layui-form-label">税号</label><div class="layui-input-block"><input type="text" id="public_tax" placeholder="请输入税号" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">联系人</label><div class="layui-input-block"><input type="text" id="public_contacts" placeholder="请输入联系人" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">联系电话</label><div class="layui-input-block"><input type="text" id="public_tel" placeholder="请输入联系电话" class="layui-input"></div></div></td></tr><tr><td><div class="layui-form-item"><label class="layui-form-label">社交账号</label><div class="layui-input-block"><input type="text" id="public_other" placeholder="请输入社交账号" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">邮箱地址</label><div class="layui-input-block"><input type="text" id="public_email" placeholder="请输入邮箱地址" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text" id="public_data" placeholder="请输入备注信息" class="layui-input"></div></div></td></tr></table></div></div>';
        layui.use('layer', function() {
            layer.ready(function() {
                layer.open({
                    type: 1,
                    title: '供应商详情',
                    skin: 'layui-layer-rim', //加上边框
                    area: ['900px', '370px'], //宽高
                    offset: '6%',
                    content: html,
                    btn: ['保存', '取消'],
                    fixed: false,
                    shadeClose: true,
                    success: function(layero, index) {
                        //弹出后回调
                        if (id != '') {
                            $.post("/index/service/supplier_info", {
                                "id": id,
                            }, function(re) {
                                $('#public_name').val(re.name);
                                $('#public_number').val(re.number);
                                $('#public_contacts').val(re.contacts);
                                $('#public_tel').val(re.tel);
                                $('#public_add').val(re.add);
                                $('#public_accountname').val(re.accountname);
                                $('#public_openingbank').val(re.openingbank);
                                $('#public_bankaccount').val(re.bankaccount);
                                $('#public_tax').val(re.tax);
                                $('#public_other').val(re.other);
                                $('#public_email').val(re.email);
                                $('#public_data').val(re.data);
                            });
                        }
                    },
                    btn1: function(layero) {
                        //保存
                        var name = $('#public_name').val();
                        var number = $('#public_number').val();
                        var contacts = $('#public_contacts').val();
                        var tel = $('#public_tel').val();
                        var add = $('#public_add').val();
                        var accountname = $('#public_accountname').val();
                        var openingbank = $('#public_openingbank').val();
                        var bankaccount = $('#public_bankaccount').val();
                        var tax = $('#public_tax').val();
                        var other = $('#public_other').val();
                        var email = $('#public_email').val();
                        var data = $('#public_data').val();
                        if (name === "") {
                            dump('供应商名称不可为空!');
                        } else {
                            //提交信息
                            $('.layui-layer-btn0').unbind();//解除绑定事件
                            $.post("/index/service/save_supplier", {
                                "id": id,
                                "name": name,
                                "number": number,
                                "contacts": contacts,
                                "tel": tel,
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
                                    alert_info('供应商详情保存成功!');
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


//显示详情
function customer_info(id,root_edit) {
    if(root_edit){
        var html = '<div class="info"><div class="layui-form layui-form-pane re_padding"><table style="width:100%;border-collapse:inherit;border-spacing:3px;font-size:inherit"><tr><td><div class="layui-form-item"><label class="layui-form-label">客户名称</label><div class="layui-input-block"><input type="text" id="public_name" placeholder="请输入客户名称" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">联系人</label><div class="layui-input-block"><input type="text" id="public_contacts" placeholder="请输入联系人" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">客户编号</label><div class="layui-input-block"><input type="text" id="public_number" placeholder="请输入客户编号" class="layui-input"></div></div></td></tr><tr><td><div class="layui-form-item"><label class="layui-form-label">手机号</label><div class="layui-input-block"><input type="text" id="public_tel" placeholder="请输入手机号" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">客户生日</label><div class="layui-input-block"><input type="text" id="public_birthday" placeholder="生日格式:05-13" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">客户地址</label><div class="layui-input-block"><input type="text" id="public_add" placeholder="请输入客户地址" class="layui-input"></div></div></td></tr><tr><td><div class="layui-form-item"><label class="layui-form-label">客户积分</label><div class="layui-input-block"><input type="text" id="public_integral" disabled class="layui-input" value="0"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">开户名</label><div class="layui-input-block"><input type="text" id="public_accountname" placeholder="请输入开户名" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">开户行</label><div class="layui-input-block"><input type="text" id="public_openingbank" placeholder="请输入开户行" class="layui-input"></div></div></td></tr><tr><td><div class="layui-form-item"><label class="layui-form-label">银行账号</label><div class="layui-input-block"><input type="text" id="public_bankaccount" placeholder="请输入银行账号" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">税号</label><div class="layui-input-block"><input type="text" id="public_tax" placeholder="请输入税号" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">社交账号</label><div class="layui-input-block"><input type="text" id="public_other" placeholder="请输入社交账号" class="layui-input"></div></div></td></tr><tr><td><div class="layui-form-item"><label class="layui-form-label">邮箱地址</label><div class="layui-input-block"><input type="text" id="public_email" placeholder="请输入邮箱地址" class="layui-input"></div></div></td><td colspan="2"><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text" id="public_data" placeholder="请输入备注信息" class="layui-input"></div></div></td></tr></table></div></div>';
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
                        if (id != '') {
                            $.post("/index/service/customer_info", {
                                "id": id,
                            }, function(re) {
                                $('#public_name').val(re.name);
                                $('#public_contacts').val(re.contacts);
                                $('#public_number').val(re.number);
                                $('#public_tel').val(re.tel);
                                $('#public_birthday').val(re.birthday);
                                $('#public_add').val(re.add);
                                $('#public_integral').val(re.integral);
                                $('#public_accountname').val(re.accountname);
                                $('#public_openingbank').val(re.openingbank);
                                $('#public_bankaccount').val(re.bankaccount);
                                $('#public_tax').val(re.tax);
                                $('#public_other').val(re.other);
                                $('#public_email').val(re.email);
                                $('#public_data').val(re.data);
                            });
                        }
                    },
                    btn1: function(layero) {
                        //保存
                        var name = $('#public_name').val();
                        var contacts = $('#public_contacts').val();
                        var number = $('#public_number').val();
                        var tel = $('#public_tel').val();
                        var birthday = $('#public_birthday').val();
                        var add = $('#public_add').val();
                        var accountname = $('#public_accountname').val();
                        var openingbank = $('#public_openingbank').val();
                        var bankaccount = $('#public_bankaccount').val();
                        var tax = $('#public_tax').val();
                        var other = $('#public_other').val();
                        var email = $('#public_email').val();
                        var data = $('#public_data').val();
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
                                    alert_info('客户详情保存成功!');
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