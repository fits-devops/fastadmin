define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'cmdbIcon'], function ($, undefined, Backend, Table, Form,cmdbIcon) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'modelmanage/Model/index' + location.search,
                    add_url: 'modelmanage/Model/add',
                    edit_url: 'modelmanage/Classification/edit',
                    del_url: 'modelmanage/Classification/del',
                    multi_url: 'modelmanage/Classification/multi',
                    table: 'modelmanage'
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
                Fast.api.open("/admin/modelmanage/model/add", __('Add'),{
                        callback:function(value){
                           var id = value.data.bk_classification_id;
                           var bk_obj_id = value.data.bk_obj_id;
                           var bk_obj_name = value.data.bk_obj_name;
                           var bk_obj_icon = value.data.bk_obj_icon;
                           var li = '<li class="model-item ispre">' +
                               '<a href="attr/index/obj/'+bk_obj_id+'">' +
                               '<div class="icon-box"> ' +
                               '<i class="'+bk_obj_icon+'"></i> ' +
                               '</div> ' +
                               '<div class="model-details"> ' +
                               '<p title="v[bk_obj_name]" class="model-name">'+bk_obj_name+'</p> ' +
                               '<p title="v[bk_obj_id]" class="model-id">'+bk_obj_id+'</p> ' +
                               '</div> ' +
                               '</a> ' +
                               '</li>';
                           $('#'+id).find(".model-list").append(li);
                           var count = $('#'+id).find(".model-list li").length;
                           var text = '('+count+')';
                            $('#'+id).find(".number").text(text);
                        }
                    });
            });
            $('#create-group').on('click',function(){
                Fast.api.open("/admin/modelmanage/Classification/add", __('Add'),{
                    callback:function(value){
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
                        $('.group-list').append(html);
                    }
                });
            });

            $('body').on('click','.icon-cc-edit',function(){
                var id = $(this).attr('data-id');
                var name = '.name_'+id;
                Fast.api.open("/admin/modelmanage/Classification/edit?ids="+$(this).attr('data-id'), __('Edit'), {
                    callback:function(value){
                        $(name).text(value.data.bk_classification_name);
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
                                url:"/admin/modelmanage/Classification/del/ids/"+id,
                            }, function (data, ret) {
                                Layer.close(index);
                                window.location.reload();
                            });
                        }
                    );
                });

            //  停用和启用切换
            $(document).on('click','#status-on',function () {
                $(this).addClass('bk-primary');
                $("#status-off").removeClass('bk-primary');
                $("#model-status-on").removeClass('hidden');
                $("#model-status-off").addClass('hidden');
                $("#create-model").removeAttr('disabled');
                $("#create-group").removeAttr('disabled');
            });
            $(document).on('click','#status-off',function () {
                $(this).addClass('bk-primary');
                $("#status-on").removeClass('bk-primary');
                $("#model-status-on").addClass('hidden');
                $("#model-status-off").removeClass('hidden');
                $("#create-model").attr('disabled',"disabled");
                $("#create-group").attr('disabled',"disabled");
            });
        },
        add: function () {
            $(document).on('click','.model-add',function () {
                cmdbIcon.show();
                $(".choose-icon-box").css({left:"200px",top:"100px"});
            });
            $(document).on('click',function (e) {
                //e.preventDefault(); 这里用了不能提交
                var data = $(e.target).attr('class');
                // 点击其他地方隐藏 图标插件
                if(data.indexOf('icon') < 0) {
                    if(data !== 'cmdb-form-input' && data.indexOf('bk-default') < 0){
                        cmdbIcon.hide();
                    }

                }
            });
            $(document).on('click','#icon-box li',function (e) {
                e.preventDefault();
                var icon = $(this).attr('data-icon');
                // 更新图标
                $("#c-icon").val(icon);
                $(".icon-wrapper").html('<i class="'+icon+'"></i>');
                cmdbIcon.hide();
            });
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