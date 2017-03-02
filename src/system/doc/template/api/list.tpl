<link href='/static/plugin/swagger-ui/css/reset.css' media='screen' rel='stylesheet' type='text/css'/>
<link href='/static/plugin/swagger-ui/css/screen.css' media='screen' rel='stylesheet' type='text/css'/>

<link rel="stylesheet" href="/static/plugin/codemirror-5.21.0/lib/codemirror.css">
<script type="text/javascript" src="/static/plugin/codemirror-5.21.0/lib/codemirror.js"></script>
<script type="text/javascript" src="/static/plugin/codemirror-5.21.0/addon/mode/loadmode.js"></script>
<script type="text/javascript" src="/static/plugin/codemirror-5.21.0/addon/search/match-highlighter.js"></script>
<script type="text/javascript" src="/static/plugin/codemirror-5.21.0/addon/edit/matchbrackets.js"></script>
<script type="text/javascript" src="/static/plugin/codemirror-5.21.0/addon/display/placeholder.js"></script>
<script type="text/javascript" src="/static/plugin/codemirror-5.21.0/addon/display/autorefresh.js"></script>
<script type="text/javascript" src="/static/plugin/codemirror-5.21.0/mode/javascript/javascript.js"></script>
<script type="text/javascript" src="/static/plugin/codemirror-5.21.0/mode/xml/xml.js"></script>
<script type="text/javascript" src="/static/plugin/codemirror-5.21.0/mode/htmlmixed/htmlmixed.js"></script>

<div class="swagger-section">
    <div id="swagger-ui-container" class="swagger-ui-wrap">
        <div class="" id="resources_container">
            <ul id="resources">
                {foreach from=$apiList index=$catIndex key=$_ item=$cat}
                <li class="resource">
                    <div class="heading">
                        <h2><a href="javascript:;" class="toggleEndpointList">{$catIndex+1}. {$cat['ancestor_names']}</a></h2>
                        <ul class="options">
                            <li><a href="javascript:;" class="toggleEndpointList">展开/折叠分组</a></li>
                            <li><a href="javascript:;" class="collapseResource">折叠所有接口</a></li>
                            <li><a href="javascript:;" class="expandResource">展开所有接口</a></li>
                        </ul>
                    </div>
                    <ul class="endpoints">
                        {foreach from=$cat['api_list'] index=$apiIndex key=$_ item=$api}
                        <li class="endpoint">
                            <ul class="operations">
                                <li class="{$_String->strtolower($api['content']['request_type'])} operation">
                                    <div class="heading">
                                        <h3>
                                            <span class="http_method">
                                            <a href="javascript:;" class="toggleOperation">{$api['content']['request_type']}</a>
                                            </span>
                                            <span class="path">
                                            <a href="javascript:;" class="toggleOperation ">{$api['address']}</a>
                                            </span>
                                        </h3>
                                        <ul class="options">
                                            <li>
                                                <a href="javascript:;" class="toggleOperation">
                                                    <span class="markdown"><p>{$api['name']}</p></span></a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="content">
                                        <h4>接口说明</h4>
                                        <div class="markdown"><p>{$api['brief']}</p></div>
                                        <div class="markdown"><p>{$api['detail']}</p></div>
                                        <br />
                                        <h4>接口地址</h4>
                                        <div><p>生产地址：<b>{$api['address_prod']}</b></p></div>
                                        <div><p>测试地址：<b>{$api['address_test']}</b></p></div>
                                        <br />
                                        <h4>请求参数</h4>
                                        <table class="fullwidth parameters">
                                            <thead>
                                                <tr>
                                                    <th style="width: 150px;">
                                                        参数名称
                                                    </th>
                                                    <th style="width: 150px;">
                                                        参数类型
                                                    </th>
                                                    <th style="width: 150px;">
                                                        参数状态
                                                    </th>
                                                    <th>
                                                        参数示例
                                                    </th>
                                                    <th>
                                                        参数说明
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="operation-params">
                                                {foreach from=$api['content']['request_params'] index=$_ key=$_ item=$param}
                                                    <tr>
                                                        <td class="{$param['status']}">{$param['name']}</td>
                                                        <td class="param-name">{$param['type']}</td>
                                                        <td>{$param['status']}</td>
                                                        <td>{$param['example']}</td>
                                                        <td>{$param['brief']}</td>
                                                    </tr>
                                                {/foreach}
                                            </tbody>
                                        </table>
                                        <br />
                                        <h4>返回参数</h4>
                                        <table class="fullwidth parameters">
                                            <thead>
                                            <tr>
                                                <th style="width: 150px;">
                                                    参数名称
                                                </th>
                                                <th style="width: 150px;">
                                                    参数类型
                                                </th>
                                                <th>
                                                    参数示例
                                                </th>
                                                <th>
                                                    参数说明
                                                </th>
                                            </tr>
                                            </thead>
                                            <tbody class="operation-params">
                                            {foreach from=$api['content']['response_params'] index=$_ key=$_ item=$param}
                                                <tr>
                                                    <td>{$param['name']}</td>
                                                    <td>{$param['type']}</td>
                                                    <td>{$param['example']}</td>
                                                    <td>{$param['brief']}</td>
                                                </tr>
                                            {/foreach}
                                            </tbody>
                                        </table>
                                        <br />
                                        <h4>返回示例</h4>
                                        <div class="tabbable response-example-tabs">
                                            <ul class="nav nav-tabs">
                                                <li class="active">
                                                    <a data-toggle="tab" href="#response-type-json-{$catIndex}-{$apiIndex}">JSON</a>
                                                </li>
                                                <li class="">
                                                    <a data-toggle="tab" href="#response-type-xml-{$catIndex}-{$apiIndex}" >XML</a>
                                                </li>
                                                <li class="">
                                                    <a data-toggle="tab" href="#response-type-jsonp-{$catIndex}-{$apiIndex}" >JSONP</a>
                                                </li>
                                                <li class="">
                                                    <a data-toggle="tab" href="#response-type-template-{$catIndex}-{$apiIndex}">模板变量</a>
                                                </li>
                                                <li class="">
                                                    <a data-toggle="tab" href="#response-type-other-{$catIndex}-{$apiIndex}">其他/自定义</a>
                                                </li>
                                            </ul>
                                            <div class="tab-content">
                                                <div id="response-type-json-{$catIndex}-{$apiIndex}" class="response-type-div tab-pane active">
                                                    <textarea class="code-box" code-type="application/json">{$api['content']['response_example']['JSON']}</textarea>
                                                </div>

                                                <div id="response-type-xml-{$catIndex}-{$apiIndex}" class="response-type-div tab-pane">
                                                    <textarea class="code-box" code-type="application/xml">{$api['content']['response_example']['XML']}</textarea>
                                                </div>

                                                <div id="response-type-jsonp-{$catIndex}-{$apiIndex}" class="response-type-div tab-pane">
                                                    <textarea class="code-box" code-type="javascript">{$api['content']['response_example']['JSONP']}</textarea>
                                                </div>

                                                <div id="response-type-template-{$catIndex}-{$apiIndex}" class="response-type-div tab-pane">
                                                    <textarea class="code-box" code-type="htmlmixed">{$api['content']['response_example']['Template']}</textarea>
                                                </div>

                                                <div id="response-type-other-{$catIndex}-{$apiIndex}" class="response-type-div tab-pane">
                                                    <textarea class="code-box" code-type="htmlmixed">{$api['content']['response_example']['Other']}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>

                        </li>

                        {/foreach}
                    </ul>
                </li>
                {/foreach}
            </ul>
        </div>
    </div>
</div>

<script type="text/javascript">
    CodeMirror.modeURL     = "/static/plugin/codemirror-5.21.0/mode/%N/%N.js";
    var codeMirrorOptions  = {
        theme:                   "default",
        lineNumbers:             true,
        matchBrackets:           true,
        autoRefresh:             true,
        readOnly:                true
    };

    $(function () {
        // 分类折叠/展开
        $('.toggleEndpointList').click(function(){
            $(this).parents('li.resource').find('ul.endpoints').slideToggle('normal');
        });

        // 折叠所有接口
        $('.collapseResource').click(function(){
            $(this).parents('li.resource').find('ul.endpoints').slideUp('normal');
            $(this).parents('li.resource').find('div.content').slideUp('normal');
        });

        // 展开所有接口
        $('.expandResource').click(function(){
            $(this).parents('li.resource').find('ul.endpoints').slideDown('normal');
            $(this).parents('li.resource').find('div.content').slideDown('normal');
        });

        // 接口展开/折叠
        $('.toggleOperation').click(function(){
            $(this).parents('li.operation').find('div.content').slideToggle('normal');
        });

        // 代码高亮
        $('textarea.code-box').each(function(){
            if ($(this).val().length > 1) {
                codeMirrorOptions.mode = $(this).attr('code-type');
                CodeMirror.fromTextArea($(this)[0], codeMirrorOptions);
            } else {
                $(this).parent().html('<div class="no-response-example">该格式暂无示例。</div>');
            }
        });
    });
</script>