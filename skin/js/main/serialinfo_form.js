$(function(){
    layui.use('table', function() {
        var ape_cols=[
            {field: 'class', title: '单据时间', width: 150, align:'center',templet: '<div>{{d.class.info.time}}</div>'},
            {field: 'type', title: '单据类型', width: 200, align:'center',templet: '<div>{{d.type.name}}</div>'},
            {field: 'class', title: '单据编号', width: 250, align:'center',templet: '<div>{{d.class.info.number}}</div>'},
        ];//表格选项
        layui.table.render({
            id: 'ape_table',
            size: 'lg',
            elem: '#ape_table',
            height:'full-100',
            even: ape_even,
            cols:  [ape_cols],
            url: '/index/service/serialinfo_list',
            page: true,
            limits: [30,60,90,150,300],
            method: 'post',
            where: push_so_arr(),
        });//渲染表格 
    }); 
});