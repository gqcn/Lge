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


<form class="form-horizontal" action="?" method="get">
    <div class="row">
        <div class="input-group col-xs-1" title="分页大小" data-rel="tooltip" style="width:60px;">
            <select class="select2" name="limit">
                {foreach from=$limits index=$index key=$k item=$v}
                    <option value="{$v}" {if $_Get->data['limit'] === $k}selected{/if}>{$v}</option>
                {/foreach}
            </select>
        </div>

        <div class="input-group col-xs-1 no-padding-left" title="支付方式" data-rel="tooltip" >
            <select class="select2" name="channel">
                <option value="">所有支付方式</option>
                {foreach from=$payTypes index=$index key=$k item=$v}
                    <option value="{$k}" {if $_Get->data['channel'] === $k}selected{/if}>{$v}</option>
                {/foreach}
            </select>
        </div>

        <div class="input-group col-xs-1 no-padding-left" title="微信支付用途(仅对微信支付有效)" data-rel="tooltip" >
            <select class="select2" name="pay_type">
                <option value="">支付用途</option>
                {foreach from=$payTypes index=$index key=$k item=$v}
                    <option value="{$k}" {if $_Get->data['pay_type'] === $k}selected{/if}>{$v}</option>
                {/foreach}
            </select>
        </div>

        <div class="input-group" style="width:130px;float:left;margin:0 10px 0 0;" title="购买时间筛选" data-rel="tooltip">
            <input value="{$_Get->data['date_from']}" placeholder="开始时间" name="date_from" class="form-control date-picker" type="text" data-date-format="yyyy-mm-dd" />
            <span class="input-group-addon">
                <i class="fa fa-calendar bigger-110"></i>
            </span>
        </div>

        <div class="input-group" style="width:130px;float:left;margin:0 10px 0 0;" title="购买时间筛选" data-rel="tooltip">
            <input value="{$_Get->data['date_to']}" placeholder="结束时间" name="date_to" class="form-control date-picker" type="text" data-date-format="yyyy-mm-dd" />
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

<div class="clearfix"></div>

<div style="text-align:center;">
    <ul class="pagination">{$page}</ul>
</div>

<table class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th style="width:50px;" class="center">序号</th>
            <th style="width:120px;" class="center">流水号</th>
            <th style="width:100px;" class="center">支付类型</th>
            <th style="width:100px;" class="center">支付用途</th>
            <th style="width:160px;" class="center">支付时间</th>
            <th style="width:80px;" class="center">支付状态</th>
            <th style="width:80px;" class="center">支付金额</th>
            <th style="width:100px;" class="center">支付用户</th>
            <th>信息</th>
        </tr>
    </thead>

    <tbody>
    {if $list}
        {foreach from=$list index=$index key=$key item=$item}
        <tr id="{$item['uid']}">
            <td class="center">{$listIndex--}</td>
            <td class="center">{$item['transaction_id']}</td>
            <td class="center">{$item['channel_name']}</td>
            <td class="center">{$item['pay_type_name']}</td>
            <td class="center">{$_Time->format($item['create_time'])}</td>
            <td class="center">{if $item['status']}<span class="green">成功</span>{else}<span class="red">失败</span>{/if}</td>
            <td style="text-align: right">{$item['amount_format']} 元</td>
            <td style="line-height:20px;" class="center">
                {if $item['uid']}
                    <a href="{$item['avatar']}" target="_blank">
                        <img src="{$item['avatar']}" style="width:80px;height:80px;">
                    </a>
                    <br />
                    {$item['nickname']}
                {/if}
            </td>
            <td>{$item['description']}</td>
        </tr>
        {/foreach}
    {/if}
    </tbody>
</table>
</form>
<div style="text-align:center;">
<ul class="pagination">{$page}</ul>
</div>



</div>
</div>



<script type="text/javascript">

jQuery(function($) {
    $('.date-picker').datepicker({autoclose:true}).next().on(ace.click_event, function(){
        $(this).prev().focus();
    });

   $(".select2").css('width','180px').select2({allowClear:true})
    .on('change', function(){
        $(this).closest('form').validate().element($(this));
    });

    $('#validation-form').validate({
        errorElement: 'div',
        errorClass: 'help-block',
        focusInvalid: true,
        rules: {
            amount: {
                required: true
            }
        },
        messages: {
            amount: {
                required: "预付款金额不能为空"
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
