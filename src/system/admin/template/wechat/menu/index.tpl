<style type="text/css">
.table thead > tr > th, 
.table tbody > tr > th, 
.table tfoot > tr > th, 
.table thead > tr > td, 
.table tbody > tr > td, 
.table tfoot > tr > td  {vertical-align: middle;}
a {
    hide-focus: expression(this.hideFocus=true);
    outline: none;
}
</style>


<div class="row">
<div class="col-xs-12">

<div style="margin:0 0 5px 0;">
<button class="btn btn-sm btn-success" type="button" title="添加微信菜单" data-rel="tooltip" href="#modal-form" data-toggle="modal" onclick="clearForm()"><i class="ace-icon fa fa-plus bigger-110"></i>添加菜单&nbsp;</button>
<button id="update-menu" onclick="updateMenu()" class="btn btn-sm btn-primary" type="button" title="从微信服务器将菜单同步到本地" data-rel="tooltip"><i class="ace-icon fa fa-cloud-download bigger-110"></i>同步菜单&nbsp;</button>
<button id="deploy-menu" onclick="deployMenu()" class="btn btn-sm btn-danger" type="button" title="将本地菜单应用到微信公众号" data-rel="tooltip"><i class="ace-icon fa fa-cloud-upload bigger-110"></i>部署菜单&nbsp;</button>
</div>

    <i id="loading-icon" style="display: none;" class="ace-icon fa fa-spinner fa-spin orange bigger-250"></i>
    <div id="table-content">
        {if $treeMenus}
        <form class="form-horizontal" id="MenuSortForm" action="/wechat-menu/sort" method="post">
            <div>
            <div>微信菜单说明： </div>
            <div>1、自定义菜单最多包括3个一级菜单，每个一级菜单最多包含5个二级菜单。</div>
            <div>2、一级菜单最多4个汉字，二级菜单最多7个汉字，多出来的部分将会以“...”代替。</div>
            <div>3、创建自定义菜单后，菜单的刷新策略是，在用户进入公众号会话页或公众号profile页时，如果发现上一次拉取菜单的请求在5分钟以前，就会拉取一下菜单，如果菜单有更新，就会刷新客户端的菜单。测试时可以尝试取消关注公众账号后再次关注，则可以看到创建后的效果。</div>
        </div>
        <table id="sample-table-1" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th class="center" style="width:70px;">排序</th>
                    <th style="width:180px;">菜单名称</th>
                    <th style="width:100px;">菜单类型</th>
                    <th>菜单内容</th>
                    <th style="width:80px;" class="center">操作</th>
                </tr>
            </thead>

            <tbody>
                {foreach from=$treeMenus index=$index key=$key item=$item}
                <tr menu_info='{$_String->jsonEncode($item)}' id="{$item['id']}" pid="{$item['pid']}">
                    <td class="center">
                    <input type="text" name="orders[{$item['id']}]" style="width:50px;" value="{$item['order']}"/>
                    </td>
                    <td>{$item['name']}</td>
                    <td>{$item['typeName']}</td>
                    <td>{$item['value']}</td>
                    <td class="center">
                        <div class="visible-md visible-lg hidden-sm hidden-xs action-buttons">
                            <a href="#modal-form" data-toggle="modal" onclick="editForm({$item['id']})" class="green" title="修改" data-rel="tooltip">
                                <i class="ace-icon fa fa-pencil bigger-130"></i>
                            </a>

                            <a href="javascript:deleteItem({$item['id']});" class="red ButtonDelete" title="删除" data-rel="tooltip">
                                <i class="ace-icon fa fa-trash-o bigger-130"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                {/foreach}
            </tbody>
        </table>

        {if $treeMenus}
        <div style="margin:0;">
        <button class="btn btn-sm btn-info" type="submit"><i class="ace-icon fa fa-signal bigger-110"></i>排序</button>
        </div>
        {/if}
        </form>
    {else}
        暂无菜单信息，请添加或同步菜单！
    {/if}
</div>

<div id="modal-form" class="modal" tabindex="-1">
<form class="form-horizontal" id="validation-form" action="/wechat-menu/edit" method="post">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="blue bigger">添加菜单</h4>
            </div>

            <div class="modal-body overflow-visible">
                <div class="row">
                    <div class="col-xs-12 col-sm-12">
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-3 no-padding-right">父级菜单:</label>
                            <div class="col-xs-12 col-sm-9">
                                <div class="clearfix">
                                    <select name="pid" style="width:200px;" onchange="onPidChange()">
                                        <option value="0">作为顶级菜单</option>
                                        {if $treeMenus}
                                            {foreach from=$treeMenus index=$index key=$key item=$item}
                                                <option value="{$item['id']}" pid="{$item['pid']}">{$item['name']}</option>
                                            {/foreach}
                                        {/if}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-3 no-padding-right">菜单类型:</label>
                            <div class="col-xs-12 col-sm-9">
                                <div class="clearfix">
                                <select name="type" style="width:200px;" onchange="onTypeChange()">
                                    {foreach from=$menuTypes index=$index key=$key item=$name}
                                        <option value="{$key}">{$name}</option>
                                    {/foreach}
                                </select>
                                    <span class="help-button" data-rel="popover" data-trigger="hover" data-placement="left" data-content="菜单类型如果不是一级菜单时，菜单内容不能为空" title="" data-original-title="菜单类型说明">?</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-3 no-padding-right">菜单排序:</label>
                            <div class="col-xs-12 col-sm-9">
                            <div class="clearfix">
                                <input style="width:200px;" name="order" class="input-large" type="text" placeholder="序号越小,排序越靠前" value="99" />
                            </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-3 no-padding-right required">菜单名称:</label>
                            <div class="col-xs-12 col-sm-9">
                                <div class="clearfix">
                                <input name="name" style="width:250px;"  class="col-xs-12 col-sm-12" type="text" placeholder="请输入菜单名称" value="" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-3 no-padding-right">菜单内容:</label>
                            <div class="col-xs-12 col-sm-9">
                                <div class="clearfix">
                                <input name="value"  class="col-xs-12 col-sm-12" type="text" placeholder="请输入菜单类型对应的内容(非一级菜单时，菜单内容不能为空)" value="" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input name="id" type="hidden" value="" />
                <button class="btn btn-default" data-dismiss="modal" type="button">取消</button>
                <button class="btn btn-primary" type="submit">保存</button>
            </div>
        </div>
    </div>
</form>
</div>
                                
                                
</div>
</div>



<script type="text/javascript">
// 创建菜单时清空表单数据
function clearForm()
{
    var form = $("#validation-form");
    form.find("h4").html("添加菜单");
    form.find("select[name='type']").val('');
    form.find("select[name='pid']").val(0);
    form.find("select[name='pid']").find("option").removeAttr("disabled");
    form.find("input[name='id']").val(0);
    form.find("input[name='order']").val(99);
    form.find("input[name='name']").val('');
    form.find("input[name='value']").val('');
}

// 添加数据到表单中
function editForm(id)
{
    var menu = $.parseJSON($("tr[id=" + id + "]").attr("menu_info"));
    var form = $("#validation-form");
    form.find("h4").html("修改菜单");
    form.find("select[name='type']").val(menu.type);
    form.find("select[name='pid']").val(menu.pid);
    form.find("select[name='pid']").find("option").removeAttr("disabled");
    form.find("input[name='order']").val(menu.order);
    form.find("input[name='name']").val(menu.old_name);
    form.find("input[name='value']").val(menu.value);
    form.find("input[name='id']").val(menu.id);
    // 自身以及子级不能作为自己的父级
    var id     = menu.id;
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

// 类型切换时事件
function onTypeChange()
{
    var form = $("#validation-form");
    var type = form.find("select[name='type']").val();
    if (type == '') {
        form.find("select[name='pid']").val(0);
    }
}

// 父级切换时事件
function onPidChange()
{
    var form = $("#validation-form");
    var type = form.find("select[name='type']").val();
    var pid  = form.find("select[name='pid']").val();
    if (pid != 0 && type == '') {
        form.find("select[name='type']").val('click');
    }
}

// 从微信服务器同步菜单到本地
function updateMenu()
{
    var message   = "<div class='bigger-110'>确认从微信端更新菜单到本地？</div>";
    message      += "<div class='red'>注意: 如果确认更新，本地菜单将会被覆盖！</div>";
    bootbox.dialog({
        message: message,
        buttons: {
            "button" :{
                "label"     : "取消",
                "className" : "btn-sm"
            },
            "danger" : {
                "label"     : "确定",
                "className" : "btn-sm btn-danger",
                "callback"  : function() {
                    $('#table-content').hide();
                    $('#loading-icon').show();
                    $.ajax({
                        type:    'get',
                        url:     '/wechat-menu/ajaxUpdateMenu',
                        dataType:'json',
                        success: function(data){
                            window.location.reload();
                        }
                    });
                }
            }
        }
    });
}

// 将本地菜单同步到微信
function deployMenu()
{
    var message   = "<div class='bigger-110'>确认将本地菜单部署到微信端？</div>";
    message      += "<div class='red'>注意: 如果确认部署，微信端菜单将会被覆盖！</div>";
    bootbox.dialog({
        message: message,
        buttons: {
            "button" :{
                "label"     : "取消",
                "className" : "btn-sm"
            },
            "danger" : {
                "label"     : "确定",
                "className" : "btn-sm btn-danger",
                "callback"  : function() {
                    $('#table-content').hide();
                    $('#loading-icon').show();
                    $.ajax({
                        type:    'get',
                        url:     '/wechat-menu/ajaxDeployMenu',
                        dataType:'json',
                        success: function(data){
                            window.location.reload();
                        }
                    });
                }
            }
        }
    });
}

// 删除数据项
function deleteItem(id)
{
    var menu_info = $.parseJSON($("tr[id=" + id + "]").attr("menu_info"));
    var message   = "<div class='bigger-110'>确认删除该菜单: <b>" + menu_info.old_name + "</b>？</div>";
    message      += "<div class='red'>注意: 如果菜单下有子级菜单, 子级菜单也将会被全部删除！</div>";
    bootbox.dialog({
        message: message,
        buttons: {
            "button" :{
                "label"     : "取消",
                "className" : "btn-sm"
            },
            "danger" : {
                "label"     : "删除",
                "className" : "btn-sm btn-danger",
                "callback"  : function() {
                    window.location.href='/wechat-menu/delete?id=' + id
                }
            }
        }
    }); 
}

jQuery(function($) {
    $('#validation-form').validate({
        errorElement: 'div',
        errorClass: 'help-block',
        focusInvalid: true,
        rules: {
        	name: {
                required: true
            },
            value: {
                required: {
                    depends: function (element) {
                        var form = $("#validation-form");
                        var type = form.find("select[name='type']").val();
                        return type != '';
                    }
                }
            }
        },
        messages: {
            name: {
                required: "菜单名称不能为空"
            },
            value: {
                required: "菜单内容不能为空"
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
