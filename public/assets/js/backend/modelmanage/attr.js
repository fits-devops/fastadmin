/**
 * Created by chaofu on 2019/7/22.
 */
define(['jquery', 'bootstrap', 'backend', 'table', 'form','cmdbIcon'], function ($, undefined, Backend, Table, Form,cmdbIcon) {

    var Controller = {
        config:{
            'bk_obj_id':'',
            'type_arr':{
                'singlechar' :'短字符',
                'int' : '数字',
                'float' : '浮点数',
                'enum' : '枚举',
                'date' : '日期',
                'time' : '时间',
                'longchar' : '长字符',
                'objuser' : '用户',
                'timezone' : '时区',
                'bool' : 'bool',
            }
        },
        index: function () {
            // 初始化表格参数配置
            Table.api.init();
            //绑定事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var panel = $($(this).attr("href"));
                if (panel.size() > 0) {
                    Controller.table[panel.attr("id")].call(this);
                    $(this).on('click', function (e) {
                        $($(this).attr("href")).find(".btn-refresh").trigger("click");
                    });
                }
                //移除绑定的事件
                $(this).unbind('shown.bs.tab');
            });
            Controller.config.bk_obj_id = $("#obj-id").text();
            //必须默认触发shown.bs.tab事件
            $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");
            $(".choose-icon-wrapper").on('click',function () {
                cmdbIcon.show();
            });

            // 隐藏删除启用
            var creator = $("#obj-id").attr('data-creator');
            if(creator !== 'cc_system'){
                $(".btn-group").removeClass('hidden');
                $("#btn-import-file").removeClass('hidden');
                $("#btn-export-file").removeClass('hidden');
            }else{
                $("#btn-import-file").addClass('hidden');
                $("#btn-export-file").addClass('hidden');
            }
            // 导出字段数据
            $("body").on('click','#btn-export-file',function (e) {
                var form=$("<form></form>");
                //设置属性
                var url = '/admin/modelmanage/attr/export/name/'+ $("#obj-id").text();
                form.attr("action",url);
                form.attr("method","post");
                $(document.body).append(form);
                //提交表单
                form.submit();
            });
            $("body").on('click','#icon-box li',function (e) {
                var icon = $(this).attr('data-icon');
                var id = $("#obj-id").data('id');
                // 更新图标
                Fast.api.ajax({
                    url:"/admin/modelmanage/model/changIcon/ids/"+id,
                    data:{"row":{"bk_obj_icon":icon}},
                }, function (data, ret) {
                    cmdbIcon.hide();
                    $(".choose-icon-wrapper i").attr('class','icon '+icon +' ispre');
                });
            });
            $("body").on('click',function (e) {
                // 点击其他地方隐藏 图标插件
                if($(e.target).attr('class')!='hover-text') {
                    cmdbIcon.hide();
                }
            });
            // 返回到模型页面
            $(document).on('click',"#obj2",function () {
                window.top.location.href = '/admin/modelmanage/model?ref=addtabs';
            });

            // 更改模型名字
            $("body").on('click',".bk-edit",function () {
                var $bk_obj_name = $("#bk_obj_name");
                var name = $bk_obj_name.text();
                $bk_obj_name.remove();
                var html = '<div class="cmdb-form-item">' +
                    '<input  type="text" name="modelName" id="modelName" class="cmdb-form-input" aria-required="true" aria-invalid="false">' +
                    '</div>' +
                    '<span class="text-primary save">保存</span>' +
                    '<span  class="text-primary cancle">取消</span>';

                $(this).after(
                    html
                ).remove();
                $("#modelName").val(name);
                // 取消还原
                $("body").on('click','.cancle',function () {
                    $(this).after('<span class="text-content" id="bk_obj_name">'+name+'</span><i class="icon icon-cc-edit text-primary bk-edit"></i>');
                    $('.save').remove();
                    $(this).remove();
                    $('.cmdb-form-item').remove();

                });

                // 保存更新姓名
                $("body").on('click','.save',function () {
                    var name = $("#modelName").val();
                    var $that =   $(this);
                    var id = $("#obj-id").data('id');
                    Fast.api.ajax({
                        url:"/admin/modelmanage/model/changIcon/ids/"+id,
                        data:{"row":{"bk_obj_name":name}},
                    }, function (data, ret) {
                        $that.after('<span class="text-content" id="bk_obj_name">'+name+'</span><i class="icon icon-cc-edit text-primary bk-edit"></i>');
                        $('.cancle').remove();
                        $that.remove();
                        $('.cmdb-form-item').remove();
                    });
                });

            });
            //添加字段分组
            $("body").on('click',".group-add",function () {
                var html = '<div class="group-add-div">' +
                    '<input type="text" class="add-input">' +
                    ' <a href="javascript:void(0)" class="add-group-button group-save">保存</a>' +
                    ' <a href="javascript:void(0)" class="add-group-button group-cancle">取消</a>' +
                    '</div>';

                $(this).after(
                    html
                ).remove();
            });
            // 取消还原
            $("body").on('click','.group-cancle',function () {
                $('.group-add-div').after('<i class="icon icon-cc-edit group-add"></i>').remove();
            });

            // 保存更新组名
            $("body").on('click','.group-save',function (e) {
                var $that =   $(this);
                var bk_group_id = randomNum(100000000,999999999);
                var bk_group_name= $(".add-input").val();
                var bk_obj_id = $("#obj-id").text();
                var bk_group_index =$('.group-list').attr('max_bk_group_index');
                Fast.api.ajax({
                    url:"/admin/modelmanage/fieldgroup/add",
                    data:{"row":{
                        "bk_group_id":bk_group_id,
                        "bk_group_name":bk_group_name,
                        "bk_group_index":++bk_group_index,
                        "bk_obj_id":bk_obj_id,
                        "bk_supplier_account":0,
                    }},
                }, function (data, ret) {
                    var li ='<li class="group-item clearfix" bk_group_id='+bk_group_id+'>' +
                        ' <span class="black-line"></span>'+
                        '<div class="group-title">' +
                        '<span class="group-obj-name" vale="' +
                        data.bk_group_name +
                        '">'+bk_group_name+'</span> ' +
                        '<span class="number">(0)</span>' +
                        '<i class="icon icon-cc-edit group-edit " edit-id="' +
                        data.id +
                        '"></i>'+
                        ' </div>' +
                        '<i class="icon-cc-del all-group-del" del-id="' +
                        data.id +
                        '"></i>'+
                        ' <ul class="property-list clearfix disabled" data-listidx="">' +
                        '<li  class="property-empty">立即添加 </li>'+
                        ' </ul>' +
                        '</li>';
                    $('.group-list li').last().after(li);
                    $('.group-add-div').after('<i class="icon icon-cc-edit group-add"></i>').remove();
                });
            });

            //删除字段分组总组
            $("body").on('click','.all-group-del',function (e) {
                e.preventDefault();
                var id = $(this).attr("del-id");
                var that = $(this);
                Layer.confirm(
                    __('Are you sure you want to delete this item?'),
                    {icon: 3, title: __('Warning'), shadeClose: true},
                    function (index) {
                        Fast.api.ajax({
                            url:"/admin/modelmanage/Fieldgroup/del/ids/"+id,
                        }, function (data, ret) {
                            Layer.close(index);
                            that.parent().remove();
                            //window.location.reload();
                        });
                    }
                );
            });

            //更新分组信息
            $("body").on('click','.group-edit',function () {
                //避免重复触发,将edit_id和parent作为全局变量
                edit_id =$(this).attr('edit-id');
                $edit_parent = $(this).parent();
                var name = $edit_parent.children('.group-obj-name').text();
                number = $edit_parent.children('.number').text();
                $edit_parent.children('.group-obj-name').remove();
                $edit_parent.children('.number').remove();
                var html = '<div class="edit-k">' +
                    '<div class="group-obj-name">' +
                    '<input  type="text" name="modelName" id="group-edit-name" class="cmdb-form-input" aria-required="true" aria-invalid="false">' +
                    '</div>' +
                    '<div class="text-check group-name-save">保存</div>' +
                    '<div  class="text-check group-name-cancle">取消</div>' +
                    '</div>';

                $(this).after(
                    html
                ).remove();
                $("#group-edit-name").val(name);
                //取消还原
                $("body").on('click','.group-name-cancle',function () {
                    $edit_parent.html('' +
                        ' <span class="group-obj-name">' +
                        name  +
                        '</span>'+
                        '<span class="number">' +
                        number +
                        '</span>'+
                        '<i class="icon icon-cc-edit group-edit " edit-id="'+edit_id+'"></i>'
                    );
                    $('.edit-k').remove();
                });

            });
            // 保存更新组名
            $("body").on('click','.group-name-save',function (e) {
                e.preventDefault();
                var change_name = $("#group-edit-name").val();
                Fast.api.ajax({
                    url:"/admin/modelmanage/fieldgroup/edit",
                    data:{"row":{
                        "condition":{
                            "id":edit_id
                        },
                        "data":
                            {
                                "bk_group_name":change_name
                            }
                    }},
                }, function (data, ret) {
                    $edit_parent.html('' +
                        ' <span class="group-obj-name">' +
                        change_name  +
                        '</span>'+
                        '<span class="number">' +
                        number +
                        '</span>'+
                        '<i class="icon icon-cc-edit group-edit " edit-id="'+edit_id+'"></i>'
                    );
                    $('.edit-k').remove();
                });
            });

            //添加字段
            $("body").on('click','.property-empty',function(){
                //获得添加字段的bk_group_id值
                var bk_group_id = $(this).parent().parent().attr('bk_group_id');
                Fast.api.open("/admin/modelmanage/attr/groupadd/obj/"+Controller.config.bk_obj_id, __('增加字段'),  {
                    callback:function(data){
                        if(data!=[]){
                            //循环移除需要添加且原本存在的li
                            for(var i=0;i< data['bk_property_ids'].length;i++){
                                var toRemove = data['bk_property_ids'][i];
                                $("[bk_property_id="+toRemove+"]").remove();
                            }

                            //将需要添加的li加入到bk_group_id的分组中
                            var chosen =$('.group-item[bk_group_id='+bk_group_id+']');
                            for(var i=0;i< data['data'].length;i++){
                                var html = '<li class="property-item fl" bk_group_id="' +
                                    data['data'][i]['bk_group_id'] +
                                    '" bk_property_id="' +
                                    data['data'][i]['bk_property_id'] +
                                    '" bk_property_group="' +
                                    data['data'][i]['bk_property_group'] +
                                    '" bk_property_index="' +
                                    data['data'][i]['bk_property_index'] +
                                    '" bk_property_name="' +
                                    data['data'][i]['bk_property_name'] +
                                    '" style="cursor: pointer;">' +
                                    data['data'][i]['bk_property_name']+
                                    '</li>'
                                chosen.find("ul"). append(html);
                            }
                            chosen.find('.property-empty').remove();
                            //更新字段在分组中的显示
                            Controller.changeGroup();
                        }
                    }
                });
            });
            //生成从minNum到maxNum的随机数
            function randomNum(minNum,maxNum){
                switch(arguments.length){
                    case 1:
                        return parseInt(Math.random()*minNum+1,10);
                        break;
                    case 2:
                        return parseInt(Math.random()*(maxNum-minNum+1)+minNum,10);
                        break;
                    default:
                        return 0;
                        break;
                }
            };

            // 禁用
            $(document).on("click", "#model-stop", function (e) {
                e.preventDefault();
                var $that = $(this);
                var id = $that.data("id");
                var ispaused = $that.data("ispaused");
                var msg = ispaused == '0' ? '确认要停用该模型' : '确认要启用该模型';
                var text = ispaused == '0' ? '启用' : '停用';
                var ispausedBool = ispaused == '0' ? 'true' : 'false';
                Layer.confirm(
                    msg,
                    {icon: 3, title: __('Warning'), shadeClose: true},
                    function (index) {
                        Fast.api.ajax({
                            url:"/admin/modelmanage/model/edit/ids/"+id,
                            data:{"row":{"bk_ispaused":ispausedBool}}
                        }, function (data, ret) {
                            Layer.close(index);
                            $("#model-stop span").text(text);
                            var ispausedText =  ispaused == '0' ? '1' : '0';
                            $that.data('ispaused',ispausedText);
                        });
                    }
                );
            });

            // 删除
            $(document).on("click", "#model-del", function (e) {
                e.preventDefault();
                var id = $(this).data("id");
                Layer.confirm(
                    '确认要删除该模型？',
                    {icon: 3, title: __('Warning'), shadeClose: true},
                    function (index) {
                        Fast.api.ajax({
                            url:"/admin/modelmanage/model/del/ids/"+id,
                        }, function (data, ret) {
                            Layer.close(index);
                            window.top.location.href = '/admin/modelmanage/model?ref=addtabs';
                        });
                    }
                );
            });
            // 字段分组 拖动改变分组或者顺序
            require(['dragsort'], function () {
                $(".property-list").dragsort({
                    dragSelector: ".property-item",
                    dragBetween: true,
                    dragEnd: function (a, b) {
                        Controller.changeGroup();
                    },
                    placeHolderTemplate: "<li class='placeHolder property-item fl'></li>"
                });
            });



        },
        table:{
            first: function () {

                // 表格1
                // 初始化表格参数配置
                Table.api.init({
                    extend: {
                        index_url: 'modelmanage/attr/index/obj/' + Controller.config.bk_obj_id,
                        add_url: 'modelmanage/attr/add/obj/' + Controller.config.bk_obj_id,
                        edit_url: 'modelmanage/attr/edit/obj/'+Controller.config.bk_obj_id,
                        del_url: 'modelmanage/attr/del',
                        import_url: 'modelmanage/attr/import',
                        multi_url: '',
                        table: 'modelmanage'
                    }
                });
                var table1 = $("#table1");
                table1.bootstrapTable({
                    url: 'modelmanage/attr/table1/obj/'+ Controller.config.bk_obj_id,
                    toolbar: '#toolbar',
                    sortName: 'id',
                    search: false,
                    columns: [
                        [
                            {field: 'isrequired', title: __('bk_require'),
                                formatter: function (value,row) {
                                    if (value === true) return '√';
                                    if (value === false) return '×';
                                }
                            },
                            {field: 'bk_property_id', title: __('bk_id')},
                            {field: 'bk_property_name', title: __('bk_name')},
                            {field: 'bk_property_type', title: __('bk_type'),
                                formatter: function (value,row) {
                                    return Controller.config.type_arr[value];
                                }
                            },
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: table1,
                                buttons: [
                                    {
                                        name: 'edit',
                                        icon: 'fa fa-pencil',
                                        title: __('Edit'),
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-success btn-editone',
                                        visible: function (row) {
                                            //返回true时按钮显示,返回false隐藏
                                            return row['creator'] === "cc_system" ? false : true;
                                        }
                                    },
                                    {
                                        name: 'del',
                                        icon: 'fa fa-trash',
                                        title: __('Del'),
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-danger btn-delone',
                                        visible: function (row) {
                                            //返回true时按钮显示,返回false隐藏
                                            return row['creator'] === "cc_system" ? false : true;
                                        }
                                    }
                                ],
                                events: Table.api.events.operate,
                                formatter: Table.api.formatter.operate
                            }
                        ]
                    ],
                    showExport: false,
                    showSearch: false
                });
                // 为表格1绑定事件
                Table.api.bindevent(table1);
            },
            second: function () {
                // 表格2
                var table2 = $("#table2");
                table2.bootstrapTable({
                    url: 'modelmanage/attr/table2/obj/'+Controller.config.bk_obj_id,
                    extend: {
                        index_url: 'modelmanage/attr/table2/obj/'+Controller.config.bk_obj_id,
                        add_url: 'modelmanage/association/add/obj/'+Controller.config.bk_obj_id,
                        edit_url: 'modelmanage/association/edit/obj/'+Controller.config.bk_obj_id,
                        del_url: 'modelmanage/association/del',
                    },
                    toolbar: '#toolbar2',
                    sortName: 'id',
                    bk_obj_id: Controller.config.bk_obj_id,
                    search: false,
                    showExport: false,
                    showSearch: false,
                    columns: [
                        [
                            {field: 'bk_obj_asst_id', title: __('bk_obj_asst_id')},
                            {field: 'bk_asst_id', title: __('bk_asst_id')},
                            {field: 'mapping', title: __('mapping')},
                            {field: 'bk_obj_id', title: __('bk_obj_id')},
                            {field: 'bk_asst_obj_id', title: __('bk_asst_obj_id')},
                            {
                                field: 'operate',
                                buttons: [
                                    {
                                        name: 'edit',
                                        icon: 'fa fa-pencil',
                                        title: __('Edit'),
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-success btn-editone',
                                        visible: function (row) {
                                            //返回true时按钮显示,返回false隐藏
                                            return row['creator'] === "cc_system" ? false : true;
                                        }
                                    },
                                    {
                                        name: 'del',
                                        icon: 'fa fa-trash',
                                        title: __('Del'),
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-danger btn-delone',
                                        visible: function (row) {
                                            //返回true时按钮显示,返回false隐藏
                                            return row['creator'] === "cc_system" ? false : true;
                                        }
                                    }
                                ],
                                title: __('Operate'),
                                table: table2,
                                events: Table.api.events.operate,
                                formatter: Table.api.formatter.operate
                            }
                        ]
                    ]
                });

                // 为表格2绑定事件
                Table.api.bindevent(table2);
            },
            fourth: function () {
                // 表格4
                var table4 = $("#table4");
                table4.bootstrapTable({
                    url: 'modelmanage/attr/table4/obj/' + Controller.config.bk_obj_id,
                    extend: {
                        index_url: 'modelmanage/attr/table4/obj/' + Controller.config.bk_obj_id,
                        add_url: 'modelmanage/association/add/obj/' + Controller.config.bk_obj_id,
                        edit_url: 'modelmanage/association/edit/obj/' + Controller.config.bk_obj_id,
                        del_url: 'modelmanage/association/del',
                    },
                    toolbar: '#toolbar4',
                    sortName: 'id',
                    bk_obj_id: Controller.config.bk_obj_id,
                    search: false,
                    showToggle: false,
                    showColumns: false,
                    showExport: false,
                    commonSearch: false,
                });

                // 为表格4绑定事件
                Table.api.bindevent(table4);
            },
            third: function () {
                // 表格3
                var table3 = $("#table3");
                table3.bootstrapTable({
                    url: 'modelmanage/unique/index/obj/'+Controller.config.bk_obj_id,
                    extend: {
                        index_url: 'modelmanage/unique/table3/obj/'+Controller.config.bk_obj_id,
                        add_url: 'modelmanage/unique/add/obj/'+Controller.config.bk_obj_id,
                        edit_url: 'modelmanage/unique/edit/obj/'+Controller.config.bk_obj_id,
                        del_url: 'modelmanage/unique/del/obj/'+Controller.config.bk_obj_id
                    },
                    toolbar: '#toolbar3',
                    sortName: 'id',
                    bk_obj_id: Controller.config.bk_obj_id,
                    search: false,
                    showExport: false,
                    showSearch: false,
                    columns: [
                        [
                            {field: 'name', title: __('bk_obj_asst_id')},
                            {field: 'must_check', title: __('must_check'),
                                formatter: function (value,row) {
                                    if (value === true) return '是';
                                    if (value === false) return '否';
                                }
                            },
                            {
                                field: 'operate',
                                title: __('Operate'),
                                buttons: [
                                    {
                                        name: 'edit',
                                        icon: 'fa fa-pencil',
                                        title: __('Edit'),
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-success btn-editone',
                                        visible: function (row) {
                                            //返回true时按钮显示,返回false隐藏
                                            return row['ispre'] === true  ? false : true;
                                        }
                                    },
                                    {
                                        name: 'del',
                                        icon: 'fa fa-trash',
                                        title: __('Del'),
                                        extend: 'data-toggle="tooltip"',
                                        classname: 'btn btn-xs btn-danger btn-delone',
                                        visible: function (row) {
                                            //返回true时按钮显示,返回false隐藏
                                            return row['ispre'] === true  ? false : true;
                                        }
                                    }
                                ],
                                table: table3,
                                events: Table.api.events.operate,
                                formatter: Table.api.formatter.operate
                            }
                        ]
                    ]
                });

                // 为表格2绑定事件
                Table.api.bindevent(table3);
            },
        },
        add: function () {
            // 绑定类型change 事件
            Controller.typeChange();
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.typeChange();
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        },
        groupadd:function(){
            Form.api.bindevent($("form[role=form]"), function(data, ret){
                //这里是表单提交处理成功后的回调函数，接收来自php的返回数据
                Fast.api.close(data);//这里是重点
                return false;
            })
        },
        typeChange: function () {
            $('body').on('change','#attr_type',function () {
                var $this = $(this);
                var  val = $this.val();
                if(val === 'int' || val === 'float'){
                    $("#max,#min").removeClass('hidden');
                    $("#option").attr('disabled',true);
                }else{
                    $("#max,#min").addClass('hidden');
                    $("#option").attr('disabled',false);
                }
                // 枚举
                if(val === 'enum'){
                    $(".form-enum-wrapper").removeClass('hidden');
                    $("input[name='row[isrequired]']").parent().addClass('hidden');
                    $("#option").parent().parent().addClass('hidden');
                }else{
                    $(".form-enum-wrapper").addClass('hidden');
                    $("input[name='row[isrequired]']").parent().removeClass('hidden');
                }
                if(val === 'singlechar' || val === 'longchar'){
                    $("#option").parent().parent().removeClass('hidden');
                }else {
                    $("#option").parent().parent().addClass('hidden');
                }
            });
            $("#attr_type").val($("#attr_type").val()).trigger('change');
        },
        //更新字段在分组中的显示
        changeGroup:function () {
            var properties = [];
            var propertyIndex = 0;
            $(".group-item").each(function () {
                var bk_property_group = $(this).attr("bk_group_id");
                $(this).find(".property-list li").each(function () {
                    var $that = $(this);
                    if ($that.attr('bk_property_index') != propertyIndex || $that.attr('bk_property_group') !== bk_property_group) {
                        $that.attr('bk_property_index', propertyIndex);
                        $that.attr('bk_property_group', bk_property_group);
                        properties.push($that);
                        $(".property-empty").remove();
                    }
                    propertyIndex++;
                });
            });
            var attrGroupData = properties.map(function(e){
                var $that = $(e);
                return {
                    condition: {
                        'bk_obj_id':Controller.config.bk_obj_id,
                        'bk_property_id':$that.attr("bk_property_id"),
                        'bk_supplier_account': 0
                    },
                    data: {
                        'bk_property_group':$that.attr("bk_property_group"),
                        'bk_property_index':$that.attr("bk_property_index")
                    }
                };
            });
            var params = {
                url: '/admin/modelmanage/Fieldgroup/attrChangeGroup',
                data:  {"row":{"data":attrGroupData}}
            };
            Fast.api.ajax(params, function (data, ret) {
                // 改变组的字段数量
                $(".group-item").each(function () {
                    var $this = $(this);
                    var length =$this.find(".property-list li").length;
                    var text = '('+length+')';
                    $this.find(".number").text(text);
                    if(!length){
                        // 添加空的li
                        $this.find(".property-list").append('<li class="property-empty">立即添加</li>');
                    }
                });
            }, function (data, ret) {

            });
        }
    };
    return Controller;
});