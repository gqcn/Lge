<div class="row">
    <div class="col-xs-12">


        <div class="col-xs-12 col-sm-2 center">
            <!--
        <div>
            <span class="profile-picture">
                <a href="javascript:{if !$_Get->data['view']}changeAvatar(){/if};" >
                <img src="{$data['avatar']}" style="width:180px;height:200px;" id="avatar"/>
                </a>
                <div>头像预览</div>
            </span>
        </div>
    -->
        </div>

        <div class="col-xs-12 col-sm-9">
            <form class="form-horizontal" id="validation-form" action="/user/edit" method="post">
                <input value="{$data['avatar']}" type="hidden" name="avatar"/>
                <input value="{$data['uid']}" type="hidden" name="uid"/>
                <input value="{$data['create_time']}" type="hidden" name="create_time"/>
                <div class="form-group">
                    <label class="control-label col-xs-12 col-sm-2 no-padding-right required">用户组:</label>
                    <div class="col-xs-12 col-sm-9">
                        <select id="gid" class="select2" name="gid">
                            {foreach from=$groups index=$index key=$gid item=$item}
                                <option value="{$gid}">{$item['name']}</option>
                            {/foreach}
                            {if $data['gid'] == $superAdmin}
                            <option value="{$superAdmin}">超级管理员</option>
                            {/if}
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-xs-12 col-sm-2 no-padding-right {if !$_Get->data['view']}required{/if}">帐号:</label>
                    <div class="col-xs-12 col-sm-9">
                        <div class="clearfix">
                            <input value="{$data['passport']}" type="text" name="passport" class="col-xs-12 col-sm-12" {if $data['passport']}readonly="true"{/if} style="width:300px;"/>
                        </div>
                    </div>
                </div>

                {if !$_Get->data['view']}
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2 no-padding-right required">密码:</label>
                        <div class="col-xs-12 col-sm-9">
                            <div class="clearfix">
                                <input value="{$data['password']}" type="password" name="password" id="password" class="col-xs-12 col-sm-12" style="width:300px;"/>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2 no-padding-right required">确认密码:</label>
                        <div class="col-xs-12 col-sm-9">
                            <div class="clearfix">
                                <input value="{$data['password']}" type="password" name="password2" class="col-xs-12 col-sm-12" style="width:300px;"/>
                            </div>
                        </div>
                    </div>
                {/if}

                <div class="form-group">
                    <label class="control-label col-xs-12 col-sm-2 no-padding-right required">昵称:</label>
                    <div class="col-xs-12 col-sm-9">
                        <div class="clearfix">
                            <input value="{$data['nickname']}" type="text" name="nickname" class="col-xs-12 col-sm-12" {if $_Get->data['view']}readonly="true"{/if} style="width:300px;"/>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-xs-12 col-sm-2 no-padding-right">城市:</label>
                    <div class="col-xs-12 col-sm-9">
                        <select id ='city' class="select2" name="city">
                            <option value="">无</option>
                            {foreach from=$city index=$index key=$key item=$name}
                                <option value="{$name}">
                                    {$name}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-xs-12 col-sm-2 no-padding-right">状态:</label>

                    <div class="col-xs-12 col-sm-9">
                        {if $_Get->data['view']}
                            <div style="padding-top:5px;">
                                {if $data['status'] == 1}
                                    <span class="lbl green"> 正常</span>
                                {else}
                                    <span class="lbl red"> 禁用</span>
                                {/if}
                            </div>
                        {else}
                            <div>
                                <label>
                                    <input name="status" value="1" type="radio" class="ace" {if $data['status'] == 1}checked{/if}/>
                                    <span class="lbl green"> 正常</span>
                                </label>

                                <label>
                                    <input name="status" value="0" type="radio" class="ace" {if $data['status'] == 0}checked{/if}/>
                                    <span class="lbl red"> 禁用</span>
                                </label>
                            </div>
                        {/if}


                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-xs-12 col-sm-2 no-padding-right">性别:</label>

                    <div class="col-xs-12 col-sm-9">
                        {if $_Get->data['view']}
                            <div style="padding-top:5px;" class="lbl blue">
                                {if $data['gender'] == 1}
                                    男士
                                {else}
                                    女士
                                {/if}
                            </div>
                        {else}
                            <div>
                                <label>
                                    <input name="gender" value="1" type="radio" class="ace" {if $data['gender'] == 1}checked{/if}/>
                                    <span class="lbl blue"> 男士</span>
                                </label>

                                <label>
                                    <input name="gender" value="0" type="radio" class="ace" {if $data['gender'] == 0}checked{/if}/>
                                    <span class="lbl blue"> 女士</span>
                                </label>
                            </div>
                        {/if}


                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-xs-12 col-sm-2 no-padding-right">邮箱:</label>
                    <div class="col-xs-12 col-sm-9">
                        <div class="clearfix">
                            <input value="{$data['email']}" type="text" name="email" class="col-xs-12 col-sm-12" {if $_Get->data['view']}readonly="true"{/if} style="width:300px;"/>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-xs-12 col-sm-2 no-padding-right">手机:</label>
                    <div class="col-xs-12 col-sm-9">
                        <div class="clearfix">
                            <input value="{$data['mobile']}" type="text" name="mobile" class="col-xs-12 col-sm-12" {if $_Get->data['view']}readonly="true"{/if} style="width:300px;"/>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-xs-12 col-sm-2 no-padding-right">电话:</label>
                    <div class="col-xs-12 col-sm-9">
                        <div class="clearfix">
                            <input value="{$data['telephone']}" type="text" name="telephone" class="col-xs-12 col-sm-12" {if $_Get->data['view']}readonly="true"{/if} style="width:300px;"/>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-xs-12 col-sm-2 no-padding-right">QQ:</label>
                    <div class="col-xs-12 col-sm-9">
                        <div class="clearfix">
                            <input value="{$data['qq']}" type="text" name="qq" class="col-xs-12 col-sm-12" {if $_Get->data['view']}readonly="true"{/if} style="width:300px;"/>
                        </div>
                    </div>
                </div>

                {if $_Get->data['view']}
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2 no-padding-right">注册时间:</label>
                        <div class="col-xs-12 col-sm-9">
                            <div class="clearfix">
                                <input value="{$_Time->format($data['create_time'])}" type="text" readonly="true" class="col-xs-12 col-sm-12" style="width:300px;"/>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2 no-padding-right">最近登陆:</label>
                        <div class="col-xs-12 col-sm-9">
                            <div class="clearfix">
                                <input value="{if $data['last_login_time']}{$_Time->format($data['last_login_time'])}{/if}" type="text" readonly="true" class="col-xs-12 col-sm-12" style="width:300px;"/>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2 no-padding-right">注册IP:</label>
                        <div class="col-xs-12 col-sm-9">
                            <div class="clearfix">
                                <input value="{$data['register_ip']}" type="text" readonly="true" class="col-xs-12 col-sm-12" style="width:300px;"/>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2 no-padding-right">最近登陆IP:</label>
                        <div class="col-xs-12 col-sm-9">
                            <div class="clearfix">
                                <input value="{$data['last_login_ip']}" type="text" readonly="true" class="col-xs-12 col-sm-12" style="width:300px;"/>
                            </div>
                        </div>
                    </div>
                {/if}

                <div class="form-group">
                    <label class="control-label col-xs-12 col-sm-2 no-padding-right">地址:</label>
                    <div class="col-xs-12 col-sm-9">
                        <div class="clearfix">
                            <input value="{$data['address']}" type="text" name="address" class="col-xs-12 col-sm-12" {if $_Get->data['view']}readonly="true"{/if}/>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-xs-12 col-sm-2 no-padding-right">说明:</label>
                    <div class="col-xs-12 col-sm-9">
                        <div class="clearfix">
                            <textarea name="brief" style="height:100px;width:100%;" {if $_Get->data['view']}readonly="true"{/if}>{$data['brief']}</textarea>
                        </div>
                    </div>
                </div>


                {if $_Get->data['view']}
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2 no-padding-right"></label>
                        <div class="col-xs-12 col-sm-9">
                            <div class="clearfix">
                                <button class="btn btn-success" type="button" onclick="history.go(-1)">
                                    <i class="icon-arrow-left icon-on-left bigger-110"></i> 返回
                                </button>

                                <button class="btn btn-info" type="button" onclick="window.location.href='?app=user&act=showEdit&uid={$_Get->data['uid']}'">
                                    修改 <i class="icon-arrow-right icon-on-right bigger-110"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                {else}
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2 no-padding-right"></label>
                        <div class="col-xs-12 col-sm-9">
                            <div class="clearfix">
                                <button class="btn btn-info" type="submit">
                                    <i class="ace-icon fa fa-check bigger-110"></i>
                                    保存&nbsp;&nbsp;&nbsp;
                                </button>

                                <button class="btn" type="button" onclick="history.go(-1)">
                                    <i class="ace-icon fa fa-reply bigger-110"></i>
                                    返回&nbsp;&nbsp;&nbsp;
                                </button>
                            </div>
                        </div>
                    </div>
                {/if}
            </form>
        </div>
    </div>
</div>

<script src="/static/resource/js/md5-min.js"></script>

<script type="text/javascript">
    jQuery(function($) {
        $('#gid').val({$data['gid']});
        $('#city').val('{$data['city']}');

        $(".select2").css('width','300px').select2({allowClear:true})
                .on('change', function(){
                    $(this).closest('form').validate().element($(this));
                });

        $('#validation-form').validate({
            errorElement: 'div',
            errorClass: 'help-block',
            focusInvalid: true,
            rules: {
                passport:  {required: true},
                nickname:  {required: true},
                password:  {required: true},
                password2: {required: true, equalTo: "#password"}
            },
            messages: {
                passport:  {required: "帐号不能为空"},
                nickname:  {required: "昵称不能为空"},
                password:  {required: "请设置密码"},
                password2: {required: "请再输入一遍密码确认", equalTo:"两次输入的密码不一致"}
            },
            submitHandler: function(form) {
                $("input[name='password']").val(hex_md5($("input[name='password']").val()));
                $("input[name='password2']").val(hex_md5($("input[name='password2']").val()));
                form.submit();
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

