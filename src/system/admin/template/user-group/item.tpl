<link rel="stylesheet" href="/static/plugin/zTree_v3/css/zTreeStyle/zTreeStyle.css" type="text/css">
<script type="text/javascript" src="/static/plugin/zTree_v3/js/jquery.ztree.core-3.5.min.js"></script>
<script type="text/javascript" src="/static/plugin/zTree_v3/js/jquery.ztree.excheck-3.5.min.js"></script>
<script type="text/javascript">
    <!--
    var setting = {
        view: {
            showIcon: false
        },
        check: {
            enable: true
        },
        data: {
            simpleData: {
                enable: true
            }
        },
        callback: {
            beforeClick: beforeClick
        }
    };
    function beforeClick(treeId, treeNode) {
        var treeObj = $.fn.zTree.getZTreeObj("AuthTree");
        treeObj.checkNode(treeNode, !treeNode.checked, true);
        return false;
    }

    var zNodes = {$treeJson};
    // 获得所有选中的权限Key，组织成点号分隔的字符串返回
    function getCheckedKeys()
    {
        var treeObj = $.fn.zTree.getZTreeObj("AuthTree");
        var nodes   = treeObj.getCheckedNodes(true);
        var array   = new Array();
        for(var i = 0; i < nodes.length; ++i) {
            array.push(nodes[i].key);
        }
        return array.join(',');
    }

    $(document).ready(function(){
        $.fn.zTree.init($("#AuthTree"), setting, zNodes);
    });
    //-->
</script>

<div class="row">
    <div class="col-xs-12">
        <form class="form-horizontal" id="validation-form" action="/user-group/edit" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="{$data['id']}"/>
            <input type="hidden" name="keys" value=""/>

            <div class="row">
                <div class="col-xs-12 col-sm-12">
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2 no-padding-right">用户组排序:</label>
                        <div class="col-xs-12 col-sm-10">
                            <div class="clearfix">
                                <input style="width:200px;" name="order" class="input-large" type="text" placeholder="序号越小，排序越靠前" value="{$data['order']}" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2 no-padding-right required">用户组名称:</label>
                        <div class="col-xs-12 col-sm-10">
                            <div class="clearfix">
                                <input name="name"  class="col-xs-12 col-sm-10" type="text" placeholder="请输入用户组名称" value="{$data['name']}" />
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2 no-padding-right">用户组Key:</label>
                        <div class="col-xs-12 col-sm-10">
                            <div class="clearfix">
                                <input name="group_key"  class="col-xs-12 col-sm-10" type="text" placeholder="程序功能定制需要(开发人员使用，默认请留空)" value="{$data['group_key']}" />
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2 no-padding-right">用户组描述:</label>
                        <div class="col-xs-12 col-sm-10">
                            <div class="clearfix">
                            <textarea name="brief" class="col-xs-12 col-sm-10" placeholder="关于该用户组的备注描述"
                                      style="height:100px;padding:5px 4px;" >{$data['brief']}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2 no-padding-right">用户组权限:</label>
                        <div class="col-xs-12 col-sm-10">
                            <div class="tabbable col-sm-10 no-padding">
                                <ul class="nav nav-tabs">
                                    <li class="active">
                                        <a data-toggle="tab" href="#auth1">功能权限</a>
                                    </li>
                                    <li class="">
                                        <a data-toggle="tab" href="#auth2">自定义权限</a>
                                    </li>
                                </ul>

                                <div class="tab-content" style="min-height: 300px;">
                                    <div id="auth1" class="tab-pane fade active in">
                                        <ul id="AuthTree" class="ztree"></ul>
                                    </div>
                                    <div id="auth2" class="tab-pane fade ">
                                        {if $customAuths}
                                            {foreach from=$customAuths index=$index key=$key item=$item}
                                                <div class="checkbox" style="padding:0;margin-bottom:5px;">
                                                    <label style="padding:0;">
                                                        <input name="custom_auth_keys[]" {if $item['checked']}checked{/if} value="{$item['key']}" type="checkbox" class="ace">
                                                        <span class="lbl" style="min-width:200px;"> {$item['name']}</span>
                                                        权限值：<input name="custom_auth_values[]" value="{$item['value']}" type="text"/>
                                                    </label>
                                                </div>
                                            {/foreach}
                                        {else}
                                            无
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="form-group">
                <label class="control-label col-xs-12 col-sm-2 no-padding-right"></label>
                <div class="col-xs-12 col-sm-9">
                    <div class="clearfix">
                        <button class="btn btn-info" type="submit">
                            <i class="ace-icon fa fa-check bigger-110"></i>
                            保存&nbsp;&nbsp;&nbsp;
                        </button>

                        <button class="btn" type="button" onclick="history.go(-1)">
                            <i class="ace-icon fa fa-reply bigger-110"></i>
                            返回&nbsp;&nbsp;&nbsp;
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

    
<script type="text/javascript">
jQuery(function($) {
    $('#validation-form').validate({
        errorElement: 'div',
        errorClass: 'help-block',
        focusInvalid: true,
        rules: {
            name:    {required: true}
        },
        messages: {
            name:    {required: "用户组名称不能为空"}
        },
        submitHandler: function(form) {
            $("input[name='keys']").val(getCheckedKeys());
            form.submit();
        },
        highlight: function (e) {
            $(e).closest('.form-group').removeClass('has-info').addClass('has-error');
        },

        success: function (e) {
            $(e).closest('.form-group').removeClass('has-error').addClass('has-info');
            $(e).remove();
        },

        errorPlacement: function (error, element) {
            if(element.is(':checkbox') || element.is(':radio')) {
                var controls = element.closest('div[class*="col-"]');
                if(controls.find(':checkbox,:radio').length > 1) {
                    controls.append(error);
                } else {
                    error.insertAfter(element.nextAll('.lbl:eq(0)').eq(0));
                }
            } else if(element.is('.select2')) {
                error.insertAfter(element.siblings('[class*="select2-container"]:eq(0)'));
            } else if(element.is('.chosen-select')) {
                error.insertAfter(element.siblings('[class*="chosen-container"]:eq(0)'));
            } else {
                error.insertAfter(element.parent());
            }
        }
    });

});
</script>






















