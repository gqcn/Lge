<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>{$title}</title>
		<meta name="description" content="" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <style type="text/css">
            body {font-size:16px;font-family:Calibri;}
        </style>
	</head>

    <body>
    {foreach from=$list index=$index key=$key item=$item}
        <div>{$item['uid']}: {$item['name']}</div>
    {/foreach}
    <div style="width:100%;height:100px;text-align:center;">{if $test}{$a}{else}2{/if}</div>
    <div style="width:100%;height:100px;text-align:center;">{include menu}</div>
    <div style="width:100%;height:300px;text-align:center;">{include main}</div>
    <div style="width:100%;height:100px;text-align:center;">{include foot}</div>
	</body>
</html>
