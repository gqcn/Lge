<style type="text/css">
    #api-category-table tr.sortable-placeholder {
        height:40px;
        border-style: dashed;
    }
    .api-category-td {
        cursor:pointer;
    }
    .api-category-td.active {
        font-weight:bold;
        font-size:14px;
    }
</style>
<script src="{$sysurl}/assets/js/common.js"></script>

{if $catList}
    <form class="form-horizontal" id="categoy-sort-form" action="/api-category/ajaxSort" method="post">
        <table id="api-category-table" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>分类名称</th>
                    <th class="center">接口</th>
                    <th style="width:70px;" class="center">操作</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$catList index=$index key=$key item=$item}
                <tr item-info='{$_String->jsonEncode($item)}' item-id="{$item['id']}" item-pid="{$item['pid']}">
                    <td class="api-category-td" onclick="onClickCategory({$item['id']})">
                        <input type="hidden" name="ids[]"  value="{$item['id']}">
                        <input type="hidden" name="pids[]" value="{$item['pid']}">
                        {$item['name']}
                    </td>
                    <td class="center">{$item['api_count']}</td>
                    <td class="center" >
                        <div class="visible-md visible-lg hidden-sm hidden-xs action-buttons" style="margin-top:10px;">
                            <a href="#modal-form" data-toggle="modal" onclick="editForm({$item['id']})" class="green" title="修改" data-rel="tooltip">
                                <i class="ace-icon fa fa-pencil bigger-130"></i>
                            </a>
                            <a href="javascript:ajaxDeleteItem('/api-category/ajaxDelete?id={$item['id']}');" class="red ButtonDelete" title="删除" data-rel="tooltip">
                                <i class="ace-icon fa fa-trash-o bigger-130"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </form>
{else}
    暂无分类信息，请点击“添加分类”进行添加。
{/if}


<div id="modal-form" class="modal" tabindex="-1">
    <form class="form-horizontal" id="categoy-validation-form" action="/api-category/item" method="post">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="blue bigger">添加分类</h4>
                </div>

                <div class="modal-body overflow-visible">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12">
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-2 no-padding-right required">父级分类:</label>
                                <div class="col-xs-12 col-sm-10">
                                    <div class="clearfix">
                                        <select name="pid" style="width:200px;">
                                            <option value="0">作为顶级分类</option>
                                            {if $catList}
                                                {foreach from=$catList index=$index key=$key item=$item}
                                                    <option value="{$item['id']}" pid="{$item['pid']}">{$item['name']}</option>
                                                {/foreach}
                                            {/if}
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-2 no-padding-right required">分类名称:</label>
                                <div class="col-xs-12 col-sm-10">
                                    <div class="clearfix">
                                        <input name="name"  class="col-xs-12 col-sm-10" type="text" placeholder="请输入分类名称" value="" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input name="id" type="hidden" value="" />
                    <input name="appid" type="hidden" value="{$_Get->data['appid']}" />
                    <button class="btn btn-sm" data-dismiss="modal" type="button">取消</button>
                    <button class="btn btn-sm btn-primary" type="submit">保存</button>
                </div>
            </div>
        </div>
    </form>
</div>



<script type="text/javascript">
    // 点击分类
    function onClickCategory(catid)
    {
        $('.api-category-td').removeClass('active');
        $('#api-category-table tr[item-id='+ catid +'] .api-category-td').addClass('active');
        showCategoryApiList(catid);
    }

    // 创建分类时清空表单数据
    function clearForm()
    {
        var form = $("#categoy-validation-form");
        form.find("h4").html("添加分类");
        form.find("select[name='pid']").val(0);
        form.find("select[name='pid']").find("option").removeAttr("disabled");
        form.find("input[name='id']").val(0);
        form.find("input[name='name']").val('');
    }

    // 添加数据到表单中
    function editForm(id)
    {
        var info = $.parseJSON($("tr[item-id=" + id + "]").attr("item-info"));
        var form = $("#categoy-validation-form");
        form.find("h4").html("修改分类");
        form.find("select[name='pid']").val(info.pid);
        form.find("select[name='pid']").find("option").removeAttr("disabled");
        form.find("input[name='name']").val(info.old_name);
        form.find("input[name='id']").val(info.id);

        // 自身以及子级不能作为自己的父级
        var id     = info.id;
        var select = form.find("select[name='pid']");
        while (true) {
            select.find("option[value='" + id + "']").attr('disabled', true);
            select.find("option[pid='" + id + "']").attr('disabled', true);
            if (select.find("option[pid='" + id + "']").length > 0) {
                id = select.find("option[pid='" + id + "']").val();
            } else {
                break;
            }
        }
    }

    // 异步删除操作
    function ajaxDeleteItem(url)
    {
        var message = "<div class='bigger-110'>删除之后数据将无法恢复，确定要删除该数据？</div>";
        bootbox.dialog({
            message: message,
            buttons: {
                "button" : {
                    "label" : "取消",
                    "className" : "btn-sm"
                },
                "danger" : {
                    "label" : "删除",
                    "className" : "btn-sm btn-danger",
                    "callback": function() {
                        $.ajax({
                            url     : url,
                            dataType: 'json',
                            success : function(result){
                                if (result.result) {
                                    $.gritter.add({
                                        title: '成功提示',
                                        text : result.message,
                                        class_name: 'gritter-success gritter-right'
                                    });
                                    reloadCategoryAndApiList()
                                } else {
                                    $.gritter.add({
                                        title: '错误提示',
                                        text : result.message,
                                        class_name: 'gritter-error gritter-right'
                                    });
                                }
                                $('.modal-backdrop').hide();
                            }
                        });
                    }
                }
            }
        });
    }

    jQuery(function($) {

        // 分类表单校验
        $('#categoy-validation-form').validate({
            errorElement: 'div',
            errorClass  : 'help-block',
            focusInvalid: true,
            rules       : {
                name: {
                    required: true
                }
            },
            messages    : {
                name: {
                    required: "分类名称不能为空"
                }
            },
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    dataType: 'json',
                    success : function(result){
                        if (result.result) {
                            $.gritter.add({
                                title: '成功提示',
                                text : result.message,
                                class_name: 'gritter-success gritter-right'
                            });
                            reloadCategoryAndApiList();
                        } else {
                            $.gritter.add({
                                title: '错误提示',
                                text : result.message,
                                class_name: 'gritter-error gritter-right'
                            });
                        }
                        $('.modal-backdrop').hide();
                    }
                });
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

        // 排序
        $('#api-category-table tbody').sortable({
            placeholder: "sortable-placeholder"
        }).on('sortupdate', function(){
            $('#categoy-sort-form').ajaxSubmit({
                dataType: 'json',
                success : function(result){
                    if (result.result) {
                        $.gritter.add({
                            title: '成功提示',
                            text : '分类重新排完成',
                            class_name: 'gritter-success gritter-right'
                        });
                        reloadCategory();
                    }
                }
            });
        });
    });

</script>
