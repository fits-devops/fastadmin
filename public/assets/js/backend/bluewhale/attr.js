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
                var id = 12;
                // row 不使用
                Fast.api.ajax({
                    url:"/admin/bluewhale/model/changIcon/ids/"+id,
                    data:{"row":{"bk_obj_icon":icon}},
                }, function (data, ret) {
                    cmdbIcon.hide();
                    window.location.reload();
                });

            });
        },
        table:{
            first: function () {
                // 表格1
                // 初始化表格参数配置
                Table.api.init({
                    extend: {
                        index_url: 'bluewhale/attr/index' + location.search,
                        add_url: 'bluewhale/attr/add',
                        edit_url: 'bluewhale/attr/edit',
                        del_url: 'bluewhale/attr/del',
                        multi_url: '',
                        table: 'bluewhale'
                    }
                });
                var table1 = $("#table1");
                table1.bootstrapTable({
                    url: 'bluewhale/attr/table1',
                    toolbar: '#toolbar1',
                    sortName: 'id',
                    search: false,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id') , operate:false},
                            {field: 'bk_property_id', title: __('bk_classification_id')},
                            {field: 'bk_property_type', title: __('bk_classification_name')},
                            {field: 'isrequired', title: __('bk_classification_type')},
                            {field: 'bk_property_name', title: __('bk_classification_icon')},
                            {
                                field: 'operate',
                                title: __('Operate'),
                                table: table1,
                                events: Table.api.events.operate,
                                formatter: Table.api.formatter.operate
                            }
                        ]
                    ]
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
                    columns: [
                        [
                            {field: 'bk_obj_asst_id', title: __('Title')},
                            {field: 'bk_asst_id', title: __('Url')},
                            {field: 'mapping', title: __('ip')},
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
            }
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});