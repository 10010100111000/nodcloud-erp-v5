$(function(){
    layui.use('table', function() {
        var width=$(window).width();
        var ape_cols=[
            {field: 'name', title: '供应商名称', width: 200, align:'center'},
            {field: 'number', title: '供应商编号', width: 200, align:'center'},
            {field: 'contacts', title: '联系人', width: 200, align:'center'},
            {field: 'tel', title: '联系电话', width: 200, align:'center'},
            {field: 'data', title: '备注信息', width: 200, align:'center'},
            {field: 'set', title: '相关操作', width: 215, align:'center',templet: '<div><div class="layui-btn-group"><button class="layui-btn layui-btn-primary layui-btn-sm" onclick="supplier_info({{d.id}})"><i class="layui-icon">&#xe642;</i></button> <button class="layui-btn layui-btn-primary layui-btn-sm" onclick="del_supplier({{d.id}})"><i class="layui-icon">&#xe640;</i></button></div></div>'},
        ];//表格选项
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            even: ape_even,
            cols:  [ape_cols],
            url: '/index/service/supplier_list',
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
function del_supplier(id) {
    if(root_del){
        layui.use('layer', function() {
            layer.confirm('您确定要删除？', {
                btn: ['删除', '取消'], //按钮
                offset: '6%',
                shadeClose: true    
            }, function() {
                $('.layui-layer-btn0').unbind();//解除绑定事件
                $.post("/index/service/del_supplier", {
                    "id": id
                }, function(re) {
                    if (re === 'success') {
                        re_alert('删除成功!');
                    }else if(re === 'error'){
                        dump('当前数据已经发生业务操作,删除失败!');
                    }  else {
                        dump('服务器响应超时！');
                    }
                });
            });
        });
    }else{
        dump('很遗憾,您无权操作!');
    }
}
//显示详情
function supplier_info(id) {
    if(root_edit){
        var html = '<div class="info"><div class="layui-form layui-form-pane re_padding"><table style="width:100%;border-collapse:inherit;border-spacing:3px;font-size:inherit"><tr><td><div class="layui-form-item"><label class="layui-form-label">供应商名称</label><div class="layui-input-block"><input type="text" id="name" placeholder="请输入供应商名称" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">供应商编号</label><div class="layui-input-block"><input type="text" id="number" placeholder="请输入供应商编号" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">供应商地址</label><div class="layui-input-block"><input type="text" id="add" placeholder="请输入供应商地址" class="layui-input"></div></div></td></tr><tr><td><div class="layui-form-item"><label class="layui-form-label">开户名</label><div class="layui-input-block"><input type="text" id="accountname" placeholder="请输入开户名" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">开户行</label><div class="layui-input-block"><input type="text" id="openingbank" placeholder="请输入开户行" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">银行账号</label><div class="layui-input-block"><input type="text" id="bankaccount" placeholder="请输入银行账号" class="layui-input"></div></div></td></tr><tr><td><div class="layui-form-item"><label class="layui-form-label">税号</label><div class="layui-input-block"><input type="text" id="tax" placeholder="请输入税号" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">联系人</label><div class="layui-input-block"><input type="text" id="contacts" placeholder="请输入联系人" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">联系电话</label><div class="layui-input-block"><input type="text" id="tel" placeholder="请输入联系电话" class="layui-input"></div></div></td></tr><tr><td><div class="layui-form-item"><label class="layui-form-label">社交账号</label><div class="layui-input-block"><input type="text" id="other" placeholder="请输入社交账号" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">邮箱地址</label><div class="layui-input-block"><input type="text" id="email" placeholder="请输入邮箱地址" class="layui-input"></div></div></td><td><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text" id="data" placeholder="请输入备注信息" class="layui-input"></div></div></td></tr></table></div></div>';
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
                        if (id !== undefined) {
                            $.post("/index/service/supplier_info", {
                                "id": id,
                            }, function(re) {
                                $('#name').val(re.name);
                                $('#number').val(re.number);
                                $('#contacts').val(re.contacts);
                                $('#tel').val(re.tel);
                                $('#add').val(re.add);
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
                        var number = $('#number').val();
                        var contacts = $('#contacts').val();
                        var tel = $('#tel').val();
                        var add = $('#add').val();
                        var accountname = $('#accountname').val();
                        var openingbank = $('#openingbank').val();
                        var bankaccount = $('#bankaccount').val();
                        var tax = $('#tax').val();
                        var other = $('#other').val();
                        var email = $('#email').val();
                        var data = $('#data').val();
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
                                    re_alert('供应商详情保存成功!');
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
    var html='<div class="info xls"><div class="layui-form layui-form-pane re_padding"><div class="layui-form-item"><ul><li>1.该功能适用于批量导入供应商数据。</li><li>2.您需要下载数据模板后使用Excel录入数据。</li><li>3.录入数据时，请勿修改首行标题以及数据格式。</li><li>4.标题为红色的列数据不可为空，否则将跳过该行数据。</li><li>5.点击下方上传文件按钮，选择您编辑好的文件即可。</li></ul></div><hr/><div class="layui-form-item btn"><button class="layui-btn" onclick="down_xls();">下载模板</button> <button class="layui-btn layui-btn-primary" id="up_btn">上传文件</button></div></div></div>';
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
                        url: '/index/service/import_supplier',
                        accept: 'file',
                        exts: 'xls',
                        done: function(re) {
                            if(re.msg==="success"){
                                re_alert('恭喜你，成功导入'+re.nums+'条数据！');
                            }else{
                                dump(re.msg);
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
    alert_info('稍等，即将下载模板','//cdn.nodcloud.com/erp/xls/supplier.xls',true);
}
//导出
function export_data(){
    var url_info = push_so_info();
    alert_info('稍等，数据请求中',"/index/export/supplier?"+url_info,true);
}
