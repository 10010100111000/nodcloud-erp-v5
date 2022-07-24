$(function(){
    layui.use('table', function() {
        var width=$(window).width();
        var ape_cols=[
            {field: 'name', title: '条码名称', width: 200, align:'center'},
            {field: 'code', title: '条码内容', width: 200, align:'center'},
            {field: 'type', title: '条码类型', width: 200, align:'center',templet: '<div>{{d.type.name}}</div>'},
            {field: 'data', title: '备注信息', width: 200, align:'center'},
            {field: 'set', title: '相关操作', width: 280, align:'center',templet: '<div><div class="layui-btn-group"><button class="layui-btn layui-btn-primary layui-btn-sm" onclick="show_code({{d.id}},{{d.type.ape}})"><i class="layui-icon">&#xe60d;</i></button> <button class="layui-btn layui-btn-primary layui-btn-sm" onclick="code_info({{d.id}})"><i class="layui-icon">&#xe642;</i></button> <button class="layui-btn layui-btn-primary layui-btn-sm" onclick="del_code({{d.id}})"><i class="layui-icon">&#xe640;</i></button></div></div>'},
        ];//表格选项
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            even: ape_even,
            cols:  [ape_cols],
            url: '/index/service/code_list',
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
function del_code(id) {
    if(root_del){
        layui.use('layer', function() {
            layer.confirm('您确定要删除？', {
                btn: ['删除', '取消'], //按钮
                offset: '6%',
                shadeClose: true
            }, function() {
                $('.layui-layer-btn0').unbind();//解除绑定事件
                $.post("/index/service/del_code", {
                    "id": id
                }, function(re) {
                    if (re === 'success') {
                        re_alert('删除成功!');
                    } else {
                        alert_info('服务器响应超时');
                    }
                });
            });
        });
    }else{
        dump('很遗憾,您无权操作!');
    }    
}
//显示详情
function code_info(id) {
    if(root_edit){
        var html = '<div class="info"><div class="layui-form layui-form-pane re_padding"><div class="layui-form-item"><label class="layui-form-label">条码名称</label><div class="layui-input-block"><input type="text" id="name" placeholder="请输入条码名称" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">条码内容</label><div class="layui-input-block"><input type="text" id="code" placeholder="请输入条码内容" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">条码类型</label><div class="layui-input-block"><select id="type"><option value="0">条形码</option><option value="1">二维码</option></select></div></div><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text" id="data" placeholder="请输入备注信息" class="layui-input"></div></div></div></div>';
        layui.use(['layer','form'], function() {
            layer.ready(function() {
                layer.open({
                    type: 1,
                    title: '条码详情',
                    skin: 'layui-layer-rim', //加上边框
                    area: ['500px', '376px'], //宽高
                    offset: '6%',
                    content: html,
                    btn: ['保存', '取消'],
                    fixed: false,
                    shadeClose: true,
                    success: function(layero, index) {
                        //弹出后回调
                        var form=layui.form.render('select'); 
                        if (id !== undefined) {
                            $.post("/index/service/code_info", {
                                "id": id,
                            }, function(re) {
                                $('#name').val(re.name);
                                $('#code').val(re.code);
                                $('#type').val(re.type.ape);
                                $('#data').val(re.data);
                                form.render('select')
                            });
                        }
                    },
                    btn1: function(layero) {
                        //保存
                        var name = $('#name').val();
                        var code = $('#code').val();
                        var type = $('#type').val();
                        var data = $('#data').val();
                        if (name === "") {
                            dump('条码名称不可为空!');
                        }else if(code===""){
                            dump('条码内容不可为空!');
                        }else if(/[\W_]/.test(code)){
                            dump('条码内容只可为字母或数字!');
                        } else {
                            //提交信息
                            $('.layui-layer-btn0').unbind();//解除绑定事件
                            $.post("/index/service/save_code", {
                                "id": id,
                                "name": name,
                                "code": code,
                                "type": type,
                                "data": data
                            }, function(re) {
                                if (re === "success") {
                                    re_alert('条码详情保存成功!');
                                } else {
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
    var html='<div class="info xls"><div class="layui-form layui-form-pane re_padding"><div class="layui-form-item"><ul><li>1.该功能适用于批量导入条码数据。</li><li>2.您需要下载数据模板后使用Excel录入数据。</li><li>3.录入数据时，请勿修改首行标题以及数据格式。</li><li>4.标题为红色的列数据不可为空，否则将跳过该行数据。</li><li>5.条码类型请输入条形码或者二维码,否则数据无效。</li><li>6.点击下方上传文件按钮，选择您编辑好的文件即可。</li></ul></div><hr/><div class="layui-form-item btn"><button class="layui-btn" onclick="down_xls();">下载模板</button> <button class="layui-btn layui-btn-primary" id="up_xls">上传文件</button></div></div></div>';
    layui.use(['layer','upload'], function() {
        layer.ready(function() {
            layer.open({
                type: 1,
                title: '导入数据',
                skin: 'layui-layer-rim', //加上边框
                area: ['500px', '360px'], //宽高
                offset: '6%',
                content: html,
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    //弹出后回调
                    layui.upload.render({
                        elem: '#up_xls',
                        url: '/index/service/import_code', //上传接口
                        exts: 'xls',
                        accept: 'file', //允许上传的文件类型
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
    alert_info('稍等，即将下载模板','//cdn.nodcloud.com/erp/xls/code.xls',true);
}
//导出
function export_data(){
    var url_info = push_so_info();
    alert_info('稍等，数据请求中',"/index/export/code?"+url_info,true);
}
//条码图像
function show_code(id,type){
    var html='<div class="info"><div class="layui-form layui-form-pane re_padding"><div class="layui-form-item" style="text-align: center;"><img style="width: 70%;" id="code_url"></div></div></div>';
    layui.use('layer', function() {
        layer.ready(function() {
            var index = layer.open({
                type: 1,
                title: '条码图像',
                skin: 'layui-layer-rim', //加上边框
                area: ['500px', '200px'], //宽高
                offset: '6%',
                content: html,
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    //弹出后回调
                    if(type){
                        //二维码
                        layer.style(index, {
                          width: '320px',
                          height: '320px',
                        });
                    }
                    $('#code_url').attr('src','/index/main/show_code?id='+id)
                }
            });
        });
    });
}