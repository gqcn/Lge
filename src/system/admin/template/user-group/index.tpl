<style type="text/css">
.table thead > tr > th, 
.table tbody > tr > th, 
.table tfoot > tr > th, 
.table thead > tr > td, 
.table tbody > tr > td, 
.table tfoot > tr > td  {vertical-align: middle;}
</style>


<div class="row">
<div class="col-xs-12">

{if $list}
<form class="form-horizontal" id="CategorySortForm" action="/user-group/sort" method="post">
<table id="sample-table-1" class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th style="width:70px;">排序</th>
            <th style="width:80px;">用户组ID</th>
            <th>用户组名称</th>
            <th>用户组Key</th>
            <th>用户组描述</th>
            <th style="width:100px;" class="center">操作</th>
        </tr>
    </thead>

    <tbody>

        {foreach from=$list index=$index key=$key item=$item}
        <tr group_info='{$_String->jsonEncode($item)}' id="{$item['id']}">
            <td>
            <input type="text" name="orders[{$item['id']}]" style="width:50px;" value="{$item['order']}"/>
            </td>
            <td>{$item['id']}</td>
            <td>{$item['name']}</td>
            <td>{$item['group_key']}</td>
            <td>{$item['brief']}</td>
            <td class="center" style="width:80px;" >
                {if $item['group_key'] != 'super_admin'}
                <div class="visible-md visible-lg hidden-sm hidden-xs action-buttons">
                    <a href="/user-group/item?id={$item['id']}" class="green" title="修改" data-rel="tooltip">
                        <i class="ace-icon fa fa-pencil bigger-130"></i>
                    </a>

                    <a href="/user-group/item?id={$item['id']}&type=copy" class="blue" title="复制" data-rel="tooltip">
                        <i class="ace-icon fa fa-copy bigger-130"></i>
                    </a>

                    {if $item['group_key'] != 'default_group'}
                    <a href="javascript:deleteItem('/user-group/delete?id={$item['id']}');" class="red ButtonDelete" title="删除" data-rel="tooltip">
                        <i class="ace-icon fa fa-trash-o bigger-130"></i>
                    </a>
                    {/if}
                </div>
                {else}
                -
                {/if}
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>

<div style="margin:0;">
<button class="btn btn-sm btn-info" type="submit"><i class="ace-icon fa fa-signal bigger-110"></i>排序</button>
</div>
</form>

{else}

暂无用户组信息，请先添加用户组。

{/if}

</div>
</div>


<script type="text/javascript">
// 创建分类时清空表单数据
function clearForm()
{
    var form = $("#validation-form");
    form.find("h4").html("添加用户组");
    form.find("input[name='id']").val(0);
    form.find("input[name='order']").val(99);
    form.find("input[name='name']").val('');
    form.find("input[name='group_key']").val('');
    form.find("textarea[name='brief']").val('');
}

// 添加数据到表单中
function editForm(id)
{
	var group = $.parseJSON($("tr[id=" + id + "]").attr("group_info"));
    var form  = $("#validation-form");
    form.find("h4").html("修改用户组");
    form.find("input[name='order']").val(group.order);
    form.find("input[name='name']").val(group.name);
    form.find("input[name='id']").val(group.id);
    form.find("input[name='group_key']").val(group.group_key);
    form.find("textarea[name='brief']").val(group.brief);
}

// 删除数据项
function deleteItem(url)
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
                    window.location.href = url;
                }
            }
        }
    });
}

jQuery(function($) {
    $(".select2").css('width','120px').select2({allowClear:true}).on('change', function(){
        $(this).closest('form').validate().element($(this));
    });

    $('#validation-form').validate({
        errorElement: 'div',
        errorClass: 'help-block',
        focusInvalid: true,
        rules: {
            name: {
                required: true
            }
        },
        messages: {
            name: {
                required: "用户组名称不能为空"
            }
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
