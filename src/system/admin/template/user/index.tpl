<style type="text/css">
    .table thead > tr > th,
    .table tbody > tr > th,
    .table tfoot > tr > th,
    .table thead > tr > td,
    .table tbody > tr > td,
    .table tfoot > tr > td  {
        vertical-align: middle;
    }
</style>

<div class="row">
    <div class="col-xs-12">


        <form class="form-horizontal" action="?" method="get">
            <div class="row" style="margin-bottom:10px;">
                <div class="input-group col-xs-1"  style="width:80px;" title="分页大小" data-rel="tooltip" >
                    <select class="select2" name="limit">
                        <option value="10" {if $_Get->data['limit'] == 10}selected{/if}>10</option>
                        <option value="20" {if $_Get->data['limit'] == 20}selected{/if}>20</option>
                        <option value="30" {if $_Get->data['limit'] == 30}selected{/if}>30</option>
                        <option value="50" {if $_Get->data['limit'] == 50}selected{/if}>50</option>
                        <option value="80" {if $_Get->data['limit'] == 80}selected{/if}>80</option>
                        <option value="100" {if $_Get->data['limit'] == 100}selected{/if}>100</option>
                    </select>
                </div>

                <div class="input-group col-xs-1 no-padding-left" title="用户来源" data-rel="tooltip" >
                    <select class="select2" name="from">
                        <option value="">所有用户来源</option>
                        {foreach from=$froms index=$index key=$key item=$name}
                            <option value="{$key}" {if $_Get->data['from'] === $key}selected{/if}>
                                {$name}
                            </option>
                        {/foreach}
                    </select>
                </div>

                <div class="input-group col-xs-1 no-padding-left" title="用户组" data-rel="tooltip" >
                    <select class="select2" name="gid">
                        <option value="">所有用户组</option>
                        {foreach from=$groups index=$index key=$key item=$item}
                            <option value="{$item['id']}" {if $_Get->data['gid'] === $item['id']}selected{/if}>
                                {$item['name']}
                            </option>
                        {/foreach}
                    </select>
                </div>

                <div class="input-group col-xs-3 no-padding-left" title="昵称搜索" data-rel="tooltip" >
                    <input type="text" name="key" placeholder="昵称关键字查询" class="form-control search-query" value="{$_String->escape($_Get->data['key'])}">
                    <span class="input-group-btn">
                        <button class="btn btn-info btn-sm" type="submit">
                            <i class="ace-icon fa fa-search fa-on-right bigger-110"></i>
                        </button>
                    </span>
                </div>
            </div>

            <div class="clearfix"></div>

            {if $page}
                <div style="text-align:center;">
                    <ul class="pagination">{$page}</ul>
                </div>
            {/if}

            {if $list}
            <table class="table table-striped table-bordered table-hover">
                <thead>
                <tr>
                    <th style="width:50px;" class="center">选择</th>
                    <th style="width:50px;" class="center">头像</th>
                    <th style="width:50px;" class="center">状态</th>
                    <th style="width:50px;" class="center">来源</th>
                    <th style="width:80px;" class="center">UID</th>
                    <th class="center">用户组</th>
                    <th>用户帐号</th>
                    <th>用户昵称</th>
                    <th>邮箱地址</th>
                    <th>手机号</th>
                    <th style="width:160px;" class="center">注册时间</th>
                    <th style="width:160px;" class="center">最近登录</th>
                    <th style="width:100px;"  class="center">操作</th>
                </tr>
                </thead>

                <tbody>

                {foreach from=$list index=$index key=$key item=$item}
                    <tr id="{$item['uid']}" style="height:66px;">
                        <td class="center">
                            {if $item['gid'] != 1}
                                <input type="checkbox" name="batch[]" value="{$item['uid']}" class="ace"/>
                                <span class="lbl"></span>
                            {else}
                                -
                            {/if}
                        </td>
                        <td class="center">
                            {if $item['avatar']}
                                <a href="{$item['avatar']}" target="_blank"><img src="{$item['avatar']}" style="width:50px;height:50px;" /></a>
                            {else}
                                -
                            {/if}
                        </td>
                        <td class="center">{if $item['status'] == 1}<span class="green">正常</span>{else}<span class="red">禁用</span>{/if}</td>
                        <td class="center">{$item['from_name']}</td>
                        <td class="center">{$item['uid']}</td>
                        <td class="center">{$item['group_name']}</td>
                        <td>{$item['passport']}</td>
                        <td>
                            {if $_Get->data['key'] && $item['nickname']}
                                {$_String->highlight($item['nickname'], $_Get->data['key'])}</a>
                            {else}
                                {$item['nickname']}
                            {/if}
                        </td>
                        <td>{$item['email']}</td>
                        <td>{$item['mobile']}</td>
                        <td>{$_Time->format($item['create_time'])}</td>
                        <td>{if $item['latest_time']}{$_Time->format($item['latest_time'])}{/if}</td>
                        <td class="center">
                            <div class="visible-md visible-lg hidden-sm hidden-xs action-buttons">
                                <!--
                                <a href="/user/item?uid={$item['uid']}&view=1" class="blue" title="查看" data-rel="tooltip">
                                    <i class="icon-zoom-in bigger-130"></i>
                                </a>
                                -->
                                <a href="/user/item?uid={$item['uid']}" class="green" title="修改" data-rel="tooltip">
                                    <i class="ace-icon fa fa-pencil bigger-130"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                {/foreach}

                <tr style="height:66px;">
                    <td class="center">
                        <input type="checkbox" onclick="onCheckAll()" name="batch_all" class="ace"/>
                        <span class="lbl"></span>
                    </td>
                    <td colspan="12">
                        <button class="btn btn-primary" type="button" href="#modal-form" data-toggle="modal">对选中用户执行批量操作</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
        {else}
        暂无用户信息
        {/if}


        {if $page}
            <div style="text-align:center;">
                <ul class="pagination">{$page}</ul>
            </div>
        {/if}
    </div>
</div>





<div id="modal-form" class="modal" tabindex="-1">
    <form class="form-horizontal" id="batch-form" action="/user/batch" method="post">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="blue bigger">批量操作</h4>
                </div>

                <div class="modal-body overflow-visible">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12">
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-3 no-padding-right">选择批量操作:</label>
                                <div class="col-xs-12 col-sm-9">
                                    <div class="clearfix">
                                        <select name="batch_type" style="width:200px;" onchange="onTypeChange()">
                                            <option value="group" selected>修改用户组</option>
                                            <option value="status">修改状态</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group batch-div" id="batch-div-group">
                                <label class="control-label col-xs-12 col-sm-3 no-padding-right">修改用户组:</label>
                                <div class="col-xs-12 col-sm-9">
                                    <div class="clearfix">
                                        <select name="batch_gid" style="width:200px;">
                                        {foreach from=$groups index=$index key=$key item=$item}
                                            <option value="{$item['id']}">
                                                {$item['name']}
                                            </option>
                                        {/foreach}
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group batch-div" id="batch-div-status" style="display: none;">
                                <label class="control-label col-xs-12 col-sm-3 no-padding-right">修改状态:</label>
                                <div class="col-xs-12 col-sm-9">
                                    <div class="clearfix">
                                        <div style="margin: 4px 0 0 0;">
                                            <label>
                                                <input name="batch_status" value="1" type="radio" class="ace" checked/>
                                                <span class="lbl green"> 正常</span>
                                            </label>

                                            <label>
                                                <input name="batch_status" value="0" type="radio" class="ace" />
                                                <span class="lbl red"> 禁用</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input name="batch_uids" value="" type="hidden" />
                    <button class="btn btn-default" data-dismiss="modal" type="button">取消</button>
                    <button class="btn btn-primary" type="submit">提交</button>
                </div>
            </div>
        </div>
    </form>
</div>






<script type="text/javascript">
    function onTypeChange()
    {
        var type = $("#batch-form select[name='batch_type']").val();
        $(".batch-div").hide();
        $("#batch-div-"+type).show();
    }

    function onCheckAll()
    {
        var checked = $("input[name='batch_all']").is(':checked');
        console.log(checked);
        if (checked) {
            $("input[name='batch[]']").each(function(){
                $(this).attr('checked', true);
            });
        } else {
            $("input[name='batch[]']").each(function(){
                $(this).attr('checked', false);
            });
        }
    }


    jQuery(function($) {
        $(".select2").css('width','180px').select2({allowClear:true}).on('change', function(){ $(this).closest('form').validate().element($(this));});
        $("select[name=limit]").css('width','80px').select2({allowClear:true}).on('change', function(){ $(this).closest('form').validate().element($(this));});

        $('#batch-form').on('submit', function(){
            var uidArray = new Array;
            $("input[name='batch[]']:checked").each(function(){
                uidArray.push($(this).val());
            });
            if (uidArray.length < 1) {
                $.gritter.add({
                    title:      '错误提示',
                    text:       '请选择需要操作的用户！',
                    class_name: 'gritter-error gritter-right'
                });
                return false;
            }
            $("input[name='batch_uids']").val(uidArray.join(','));
            return true;
        });

    });
</script>
