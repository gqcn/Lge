<style type="text/css">
    .app-box .widget-body dt,.app-box .widget-body dd {
        font-size:14px;
        line-height:25px;
    }
    .app-logo {
        float:right;
        height:160px;
        border:1px solid #eee;
        padding:10px;
    }

</style>

<div class="row">
    <div class="col-xs-12">
        <div class="app-box">
            <h2 class="header smaller lighter blue">{$app['name']}</h2>
            <img class="app-logo" src="{$app['thumb']}" />
            <div>{$app['brief']}</div><br/>
            <div>{$app['content']}</div><br/>
        </div>
    </div>
</div>



<script type="text/javascript">
    jQuery(function($) {

    });
</script>