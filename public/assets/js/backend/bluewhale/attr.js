/**
 * Created by chaofu on 2019/7/22.
 */
define(['jquery', 'bootstrap', 'backend', 'table', 'form','cmdbIcon'], function ($, undefined, Backend, Table, Form,cmdbIcon) {

    var Controller = {
        config:{
            'bk_obj_id':''
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
            $("body").on('click','#icon-box li',function (e) {
                e.preventDefault();
                var icon = $(this).attr('data-icon');
                var id = $("#obj-id").data('id');
                // 更新图标
                Fast.api.ajax({
                    url:"/admin/bluewhale/model/changIcon/ids/"+id,
                    data:{"row":{"bk_obj_icon":icon}},
                }, function (data, ret) {
                    cmdbIcon.hide();
                    $(".choose-icon-wrapper i").attr('class','icon '+icon +' ispre');
                });
            });
            $("body").on('click',function (e) {
                e.preventDefault();
                // 点击其他地方隐藏 图标插件
                if($(e.target).attr('class')!='hover-text') {
                   cmdbIcon.hide();
                }
            });

            // 更改模型名字
            $("body").on('click',".icon-cc-edit",function () {
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
                    $(this).after('<span class="text-content" id="bk_obj_name">'+name+'</span><i class="icon icon-cc-edit text-primary"></i>');
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
                        url:"/admin/bluewhale/model/changIcon/ids/"+id,
                        data:{"row":{"bk_obj_name":name}},
                    }, function (data, ret) {
                        $that.after('<span class="text-content" id="bk_obj_name">'+name+'</span><i class="icon icon-cc-edit text-primary"></i>');
                        $('.cancle').remove();
                        $that.remove();
                        $('.cmdb-form-item').remove();
                    });
                });


            });

        },
        table:{
            first: function () {
                // 表格1
                // 初始化表格参数配置
                Table.api.init({
                    extend: {
                        index_url: 'bluewhale/attr/index/obj/' + Controller.config.bk_obj_id,
                        add_url: 'bluewhale/attr/add/obj/' + Controller.config.bk_obj_id,
                        edit_url: 'bluewhale/attr/edit/obj/'+Controller.config.bk_obj_id,
                        del_url: 'bluewhale/attr/del',
                        multi_url: '',
                        table: 'bluewhale'
                    }
                });
                var table1 = $("#table1");
                table1.bootstrapTable({
                    url: 'bluewhale/attr/table1/obj/'+ Controller.config.bk_obj_id,
                    toolbar: '#toolbar1',
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
                            {field: 'bk_property_type', title: __('bk_type')},
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: table1,
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
                    url: 'bluewhale/attr/table2/obj/'+Controller.config.bk_obj_id,
                    extend: {
                        index_url: 'bluewhale/attr/table2/obj/'+Controller.config.bk_obj_id,
                        add_url: 'bluewhale/association/add/obj/'+Controller.config.bk_obj_id,
                        edit_url: 'bluewhale/association/edit/obj/'+Controller.config.bk_obj_id,
                        del_url: 'bluewhale/association/del',
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
                    url: 'bluewhale/attr/table4/obj/' + Controller.config.bk_obj_id,
                    extend: {
                        index_url: 'bluewhale/attr/table4/obj/' + Controller.config.bk_obj_id,
                        add_url: 'bluewhale/association/add/obj/' + Controller.config.bk_obj_id,
                        edit_url: 'bluewhale/association/edit/obj/' + Controller.config.bk_obj_id,
                        del_url: 'bluewhale/association/del',
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
                    url: 'bluewhale/unique/index/obj/'+Controller.config.bk_obj_id,
                    extend: {
                        index_url: 'bluewhale/unique/table3/obj/'+Controller.config.bk_obj_id,
                        add_url: 'bluewhale/unique/add/obj/'+Controller.config.bk_obj_id,
                        edit_url: 'bluewhale/unique/edit/obj/'+Controller.config.bk_obj_id,
                        del_url: 'bluewhale/unique/del/obj/'+Controller.config.bk_obj_id
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
                            {field: 'id', title: __('bk_asst_id')},
                            {field: 'must_check', title: __('must_check'),
                                formatter: function (value,row) {
                                    if (value === true) return '是';
                                    if (value === false) return '否';
                                }
                            },
                            {
                                field: 'operate',
                                title: __('Operate'),
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
            Controller.numberBlur();
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.typeChange();
            Controller.numberBlur();
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        },
        typeChange: function () {
            $('body').on('change','#attr_type',function () {
                var $this = $(this);
                var  val = $this.val();
                if(val === 'int' || val === 'float'){
                    $("#max,#min").removeClass('hidden');
                }else{
                    $("#max,#min").addClass('hidden');
                }
                // 枚举
                if(val === 'enum'){
                    $(".form-enum-wrapper").removeClass('hidden');
                    $("input[name='row[isrequired]']").parent().addClass('hidden');
                }else{
                    $(".form-enum-wrapper").addClass('hidden');
                    $("input[name='row[isrequired]']").parent().removeClass('hidden');
                }
            });
            $("#attr_type").val($("#attr_type").val()).trigger('change');
        },
        numberBlur: function () {
            // 监控最大值 和 最小值
            $("body").on('blur',"#number_min,#number_max",function () {
                if(( $("#number_min").val() > $("#number_max").val() ) && $("#number_max").val() > 0){
                    console.log('ddddddd')
                }
            });
        }
    };
    return Controller;
});