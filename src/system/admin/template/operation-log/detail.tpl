<style type="text/css">

</style>


<div class="row">
<div class="col-xs-12">
<form class="form-horizontal reply-form" id="validation-form" action="?app=picture&act=edit" method="post" enctype="multipart/form-data">
<input type="hidden" name="id" value="{$data['id']}"/>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">日志ID:</label>
        <div class="col-xs-12 col-sm-9">
            <div class="clearfix" style="margin: 5px 0 0 0;">{$data['id']}</div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">来源IP:</label>
        <div class="col-xs-12 col-sm-9">
            <div class="clearfix" style="margin: 5px 0 0 0;">{$data['ip']}</div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">用户ID:</label>
        <div class="col-xs-12 col-sm-9">
            <div class="clearfix" style="margin: 5px 0 0 0;">{$data['uid']}</div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">用户名:</label>
        <div class="col-xs-12 col-sm-9">
            <div class="clearfix" style="margin: 5px 0 0 0;">{$data['nickname']}</div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">功能模块:</label>
        <div class="col-xs-12 col-sm-9">
            <div class="clearfix" style="margin: 5px 0 0 0;">{$data['system']}::{$data['ctl']}::{$data['act']}</div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">操作描述:</label>
        <div class="col-xs-12 col-sm-9">
            <div class="clearfix" style="margin: 5px 0 0 0;">{$data['brief']}</div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">操作时间:</label>
        <div class="col-xs-12 col-sm-9">
            <div class="clearfix" style="margin: 5px 0 0 0;">{$_Time->format($data['create_time'])}</div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">操作内容:</label>
        <div class="col-xs-12 col-sm-9">
            <div class="clearfix">
                <pre  style="color:#111;">{$data['content']}</pre>
            </div>
        </div>
    </div>



    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right"></label>
        <div class="col-xs-12 col-sm-9">
            <div class="clearfix">
                <button class="btn" type="button" onclick="window.history.go(-1)">
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

});
</script>






















