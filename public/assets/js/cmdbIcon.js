/**
 * Created by chaofu on 2019/7/19.
 */
define('cmdbIcon',['jquery'], function ($) {

    var cmdbIcon = {
        config:{
            jsontip:$("#icon-box"),
            chooseIconBox:$(".choose-icon-box"),
            dataJson:{},
            page:{},
            searchText:''
        },
        searchIcon:function (text) {
            if (text.length) {
                var newData = cmdbIcon.config.dataJson.filter(this.checkAdult);
                this.makeHtml(newData);
            }
        },
        checkAdult:function (icon) {
            return icon.nameZh.toLowerCase().indexOf(cmdbIcon.config.searchText.toLowerCase()) > -1 || icon.nameEn.toLowerCase().indexOf(cmdbIcon.config.searchText.toLowerCase()) > -1
        },
        makeHtml:function (data) {
            var $jsontip =  cmdbIcon.config.jsontip;
            $jsontip.html('');//清空内容
            var strHtml='';
            $.each(data, function (index, info) {
                strHtml += "<li   title="+info["nameZh"]+" class='icon' data-icon=" + info["value"] +"><i class=" + info["value"] +"></i></li>";
            });
            $jsontip.html(strHtml);//显示处理后的数据
        },
        pageTurning:function(pages) {
            cmdbIcon.config.page.current = pages;
            if(!pages){
                $("#button-pre").attr('disabled',true);
                $("#button-next").attr('disabled',false);
            }
            if(cmdbIcon.config.page.current === cmdbIcon.config.page.totalPage - 1){
                $("#button-next").attr('disabled',true);
                $("#button-pre").attr('disabled',false);
            }
            var newData = cmdbIcon.config.dataJson.slice(cmdbIcon.config.page.size * cmdbIcon.config.page.current, cmdbIcon.config.page.size * (cmdbIcon.config.page.current + 1));
            this.makeHtml(newData);
        },
        show:function () {
            cmdbIcon.config.chooseIconBox.show();
        },
        hide:function () {
            cmdbIcon.config.chooseIconBox.hide();
        },
        init: function () {
            $(function () {
                $.getJSON("../assets/js/model-icon.json", function (data) {
                    cmdbIcon.config.page = { current: 0,
                        size: 28,
                        totalPage: Math.ceil(data.length / 28)};
                    cmdbIcon.config.dataJson = data;
                    var newData = data.slice(cmdbIcon.config.page.size * cmdbIcon.config.page.current, cmdbIcon.config.page.size * (cmdbIcon.config.page.current + 1));
                    cmdbIcon.makeHtml(newData);
                });
                $(".cmdb-form-input").blur(function(){
                    var $this = $(this);
                    cmdbIcon.config.searchText = $this.val();
                    cmdbIcon.searchIcon($this.val());
                });
                // $("[data-toggle='popover']").popover();
                $("#button-pre").on('click',function () {
                    cmdbIcon.pageTurning(--cmdbIcon.config.page.current);
                });
                $("#button-next").on('click',function () {
                    cmdbIcon.pageTurning(++cmdbIcon.config.page.current);
                });
            });
        }

    };
    // 初始化
    cmdbIcon.init();
    return cmdbIcon;
});