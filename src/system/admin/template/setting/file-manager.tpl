<div class="row">
<div class="col-xs-12">
<div id="elfinder" class='elfinder'></div>
</div>
</div>

<!-- elfinder -->
{include _subinc/elfinder}

<!-- codemirror -->
<link rel="stylesheet" href="/static/plugin/codemirror_3_22/lib/codemirror.css"   type="text/css" media="screen" charset="utf-8">
<script type="text/javascript" src="/static/plugin/codemirror_3_22/codemirror.all.min.js"></script>

<script type="text/javascript" charset="utf-8">
var myCodeMirror;

$().ready(function() {
	var elfinderHeight = $(document).height() - 80;
    $('#elfinder').elfinder({
        url  : '/static/plugin/elfinder_2_0_rc1/php/connector.php',
        lang : 'zh_CN',
        height: elfinderHeight,
        notifyDelay: 100,
        commandsOptions : {
            edit : {
            	dialogWidth: 800,
                mimes : [],
                editors : [
                    {
                        mimes : [],
                        load : function(textarea) {
                            $("#" + textarea.id).wrap('<div style="height:400px;"></div>');
                            myCodeMirror   = CodeMirror.fromTextArea(document.getElementById(textarea.id), {
                                theme:                   "default",
                                mode:                    "htmlmixed",
                                indentUnit:              4,
                                smartIndent:             true,
                                lineWrapping:            true,
                                lineNumbers:             true,
                                showCursorWhenSelecting: true,
                                autofocus:               true,
                                autoCloseTags:           true,
                                matchBrackets:           true
                            });
                            
                            
                        },
                        close : function(textarea, instance) {

                        },
                        save : function(textarea, editor) {
                            textarea.value = myCodeMirror.getValue();
                        }
                    }
                ]
            }
         },
         
        
         uiOptions : {
            toolbar : [
                        ['home', 'up', 'back', 'forward'],
                        ['mkdir', 'mkfile', 'upload'],
                        ['open', 'download', 'getfile'],
                        ['copy', 'cut', 'paste'],
                        ['rm'],
                        ['duplicate', 'rename', 'edit'],
                        ['info', 'quicklook', 'view', 'sort'],
                        // ['resize'], ['help'],
                        ['reload'],
                        ['search'],
                        // ['archive', 'extract'],
            ]
         }
    });
});
</script>
