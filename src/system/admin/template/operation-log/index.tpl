<div class="row">
    <div class="col-xs-12">
        <form class="form-horizontal" action="?" method="get">
            <div class="row">
                <div class="input-group col-xs-1" title="分页大小" data-rel="tooltip" style="width:60px;">
                    <select class="select2" name="limit" style="width:60px;">
                        {foreach from=$limits index=$index key=$k item=$v}
                            <option value="{$v}" {if $_Get->data['limit'] === $k}selected{/if}>{$v}</option>
                        {/foreach}
                    </select>
                </div>

                <div class="input-group" style="width:130px;float:left;margin:0 10px 0 0;" title="操作日期筛选" data-rel="tooltip">
                    <input value="{$_Get->data['date']}" placeholder="操作日期" name="date" class="form-control date-picker" type="text" data-date-format="yyyy-mm-dd" />
                    <span class="input-group-addon">
                        <i class="fa fa-calendar bigger-110"></i>
                    </span>
                </div>

                <div class="input-group col-xs-2 no-padding-left">
                    <span class="input-group-btn">
                        <button class="btn btn-info btn-sm" type="submit" title="点击搜索" data-rel="tooltip">
                            <i class="ace-icon fa fa-search fa-on-right bigger-110"></i>
                        </button>
                    </span>
                </div>
            </div>
        </form>

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
                <th style="width:80px;" class="center">ID</th>
                <th style="width:80px;" class="center">用户ID</th>
                <th style="width:100px;" class="center">用户名</th>
                <th class="center">功能模块</th>
                <th class="center">操作描述</th>
                <th class="center">IP</th>
                <th style="width:160px;" class="center">操作时间</th>
                <th style="width:50px;" class="center">操作</th>
            </tr>
            </thead>

            <tbody>
                {foreach from=$list index=$index key=$key item=$item}
                    <tr id="{$item['uid']}">
                        <td class="center">{$item['id']}</td>
                        <td class="center">{$item['uid']}</td>
                        <td class="center">{$item['nickname']}</td>
                        <td>{$item['system']}::{$item['ctl']}::{$item['act']}</td>
                        <td>{$item['brief']}</td>
                        <td class="center">{$item['ip']}</td>
                        <td class="center">{$_Time->format($item['create_time'])}</td>
                        <td class="center">
                            <a href="/operation-log/detail?id={$item['id']}" data-rel="tooltip" title="查看详细操作记录">查看</a>
                        </td>
                    </tr>
                {/foreach}

            </tbody>
        </table>
        {else}

            暂无操作日志。

        {/if}

        {if $page}
        <div style="text-align:center;">
            <ul class="pagination">{$page}</ul>
        </div>
        {/if}


    </div>
</div>

<script type="text/javascript">

    jQuery(function ($) {

    });
</script>