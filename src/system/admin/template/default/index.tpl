<div class="row">
    <div class="col-xs-12">
        <div class="alert alert-block">
            欢迎您！
            尊敬的 <b>{$_Session->get('user')['nickname']}</b>
            {if $session['user']['gender']}先生{else}女士{/if}！
        </div>

    </div>
</div>


<!--
<div style="position:fixed;bottom: 10px;right: 10px;">
    当前系统版本: {$config['Soft']['version']}，
    发布时间: {$config['Soft']['date']}，
    <a href="{$config['Soft']['contact']}">技术支持</a>
</div>
-->
