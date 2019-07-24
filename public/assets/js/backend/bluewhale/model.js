define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'bluewhale/model/index' + location.search,
                    add_url: 'bluewhale/model/add',
                    edit_url: 'bluewhale/model/edit',
                    del_url: 'bluewhale/model/del',
                    multi_url: 'bluewhale/model/multi',
                    table: 'bluewhale'
                }
            });

            var table = $("#table");
            var group = $(".group-list");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id') , operate:false},
                        {field: 'bk_classification_id', title: __('bk_classification_id')},
                        {field: 'bk_classification_name', title: __('bk_classification_name')},
                        {field: 'bk_classification_type', title: __('bk_classification_type')},
                        {field: 'bk_classification_icon', title: __('bk_classification_icon')},
                        {field: 'bk_supplier_account', title: __('bk_supplier_account')},

                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]

            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            //点击事件
            $('#create-model').on('click',function(){
                Fast.api.open("/admin", __('Add'), $(this).data() || {});
            });
            $('#create-group').on('click',function(){
                Fast.api.open("/admin/bluewhale/model/add", __('Add'),{
                    callback:function(value){
                        console.log(value);
                        var html = '' +
                            '<li class="group-item clearfix">'+
                            '<div class="group-title">'+
                            '<span class=' +
                            '"name_'+value.data.id+'"'+
                            '>' +
                            value.data.bk_classification_name+
                            '</span>'+
                            '<span class="number">(0)</span>'+
                            '<i data-v-7e5121d5="" class="icon-cc-edit text-primary" data-id="' +
                            value.data.id +
                            '"></i>'+
                            '<i data-v-7e5121d5="" class="icon-cc-del text-primary" data-id="' +
                            value.data.id +
                            '"></i>'+
                            '</div>'+
                            '<ul class="model-list clearfix">'+
                            '</ul>'+
                            '</li>' ;
                        console.log(html);
                        $('.group-list').append(html);
                    }
                });
            });

            $('body').on('click','.icon-cc-edit',function(){
                var id = $(this).attr('data-id');
                var name = 'name_'+id;
                Fast.api.open("/admin/bluewhale/model/edit?ids="+$(this).attr('data-id'), __('Edit'), {
                    callback:function(value){
                        $('.'+name).text(value.data.bk_classification_name);
                    }
                });
            })


            $('body').on("click",'.icon-cc-del', function (e) {
                    e.preventDefault();
                    var id = $(this).attr("data-id");
                    var that = this;
                    Layer.confirm(
                        __('Are you sure you want to delete this item?'),
                        {icon: 3, title: __('Warning'), shadeClose: true},
                        function (index) {
                            Fast.api.ajax({
                                url:"/admin/bluewhale/model/del/ids/"+id,
                            }, function (data, ret) {
                                Layer.close(index);
                                window.location.reload();
                            });
                        }
                    );
                });
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                //Form.api.bindevent($("form[role=form]"));
                Form.api.bindevent("form[role=form]", function(data, ret){
                    //这里只要返回false，就会阻止我们的弹窗自动关闭和自动提示
                    Fast.api.close(ret);
                });
            }
        }
    };
    return Controller;
});