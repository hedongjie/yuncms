<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
include $this->admin_tpl ( 'header' );
?>
<script type="text/javascript">
<!--
	$(function(){
		SwapTab('setting','on','',8,<?php echo isset($_GET['tab']) ? $_GET['tab'] : '1'?>);
		$.formValidator.initConfig({formid:"myform",autotip:true,onerror:function(msg,obj){window.top.art.dialog.alert(msg);$(obj).focus();}});
		$("#site_name").formValidator({onshow:"<?php echo L('setting_input').L('site_name')?>",onfocus:"<?php echo L('setting_input').L('site_name')?>"}).inputValidator({min:1,onerror:"<?php echo L('setting_input').L('site_name')?>"}).defaultPassed();
		$("#site_url")
			.formValidator({
				onshow:"<?php echo L('site_domain_ex')?>",
				onfocus:"<?php echo L('site_domain_ex')?>",
				tipcss:{width:'300px'},
				empty:false
			})
			.inputValidator({
				onerror:"<?php echo L('site_domain_ex')?>"
			})
			.regexValidator({
				regexp:"http:\/\/(.+)\/$",
				onerror:"<?php echo L('site_domain_ex2')?>"
			});
		$("#js_path")
			.formValidator({
				onshow:"<?php echo L('setting_input').L('setting_js_path')?>",
				onfocus:"<?php echo L('setting_js_path').L('setting_end_with_x')?>"
			})
			.inputValidator({
				onerror:"<?php echo L('setting_js_path').L('setting_input_error')?>"
			})
			.regexValidator({
				regexp:"(.+)\/$",
				onerror:"<?php echo L('setting_js_path').L('setting_end_with_x')?>"
			});
		$("#css_path")
			.formValidator({
				onshow:"<?php echo L('setting_input').L('setting_css_path')?>",
				onfocus:"<?php echo L('setting_css_path').L('setting_end_with_x')?>"
			})
			.inputValidator({
				onerror:"<?php echo L('setting_css_path').L('setting_input_error')?>"
			})
			.regexValidator({
				regexp:"(.+)\/$",
				onerror:"<?php echo L('setting_css_path').L('setting_end_with_x')?>"
			});
		$("#img_path")
			.formValidator({
				onshow:"<?php echo L('setting_input').L('setting_img_path')?>",
				onfocus:"<?php echo L('setting_img_path').L('setting_end_with_x')?>"
			})
			.inputValidator({
				onerror:"<?php echo L('setting_img_path').L('setting_input_error')?>"
			})
			.regexValidator({
				regexp:"(.+)\/$",
				onerror:"<?php echo L('setting_img_path').L('setting_end_with_x')?>"
			});
		$("#errorlog_size")
			.formValidator({
				onshow:"<?php echo L('setting_errorlog_hint')?>",
				onfocus:"<?php echo L('setting_input').L('setting_error_log_size')?>"
			})
			.inputValidator({
				onerror:"<?php echo L('setting_error_log_size').L('setting_input_error')?>"
			})
			.regexValidator({
				regexp:"num",
				datatype:"enum",
				onerror:"<?php echo L('setting_errorlog_type')?>"
			});
		$("#error_message")
			.formValidator({
				onshow:"<?php echo L('error_message_tip');?>",
			})
			.inputValidator({
				onerror:"<?php echo L('error_message_tip')?>"
			});
	})
//-->
</script>
<style type="text/css">
.radio-label {
	border-top: 1px solid #e4e2e2;
	border-left: 1px solid #e4e2e2
}
.radio-label td {
	border-right: 1px solid #e4e2e2;
	border-bottom: 1px solid #e4e2e2;
	background: #f6f9fd
}
</style>
<form action="?app=admin&controller=setting&action=save" method="post"
	id="myform">
  <div class="pad-10">
    <div class="col-tab">
      <ul class="tabBut cu-li">
        <li id="tab_setting_1" class="on" onclick="SwapTab('setting','on','',8,1);"><?php echo L('setting_basic_cfg')?></li>
        <li id="tab_setting_2" onclick="SwapTab('setting','on','',8,2);"><?php echo L('site_config')?></li>
        <li id="tab_setting_3" onclick="SwapTab('setting','on','',8,3);"><?php echo L('setting_safe_cfg')?></li>
        <li id="tab_setting_4" onclick="SwapTab('setting','on','',8,4);"><?php echo L('attachment_config')?></li>
        <li id="tab_setting_5" onclick="SwapTab('setting','on','',8,5);"><?php echo L('sms_config')?></li>
        <li id="tab_setting_6" onclick="SwapTab('setting','on','',8,6);"><?php echo L('contactus_config')?></li>
        <li id="tab_setting_7" onclick="SwapTab('setting','on','',8,7);"><?php echo L('connect_config')?></li>
        <li id="tab_setting_8" onclick="SwapTab('setting','on','',8,8);"><?php echo L('mail_config')?></li>
      </ul>
      <!--基本设置-->
      <div id="div_setting_1" class="contentList pad-10">
        <table width="100%" class="table_form">
          <tr>
            <th width="140"><?php echo L('setting_admin_email')?></th>
            <td class="y-bg"><input type="text" class="input-text" name="system[system_email]" id="admin_email" size="30" value="<?php echo $system['system_email']?>" /></td>
          </tr>
          <tr>
            <th width="140"><?php echo L('setting_gzip')?></th>
            <td class="y-bg"><input name="config[gzip]" value="true" type="radio" <?php echo ($config['gzip']) ? ' checked' : ''?>> <?php echo L('setting_yes')?>&nbsp;&nbsp;&nbsp;&nbsp; <input name="config[gzip]" value="false" type="radio" <?php echo (!$config['gzip']) ? ' checked' : ''?>> <?php echo L('setting_no')?></td>
          </tr>

          <tr>
            <th width="140"><?php echo L('setting_js_path')?></th>
            <td class="y-bg"><input type="text" class="input-text" name="system[js_path]" id="js_path" size="50" value="<?php echo $system['js_path']?>" /></td>
          </tr>
          <tr>
            <th width="140"><?php echo L('setting_css_path')?></th>
            <td class="y-bg"><input type="text" class="input-text" name="system[css_path]" id="css_path" size="50" value="<?php echo $system['css_path']?>" /></td>
          </tr>
          <tr>
            <th width="140"><?php echo L('setting_img_path')?></th>
            <td class="y-bg"><input type="text" class="input-text" name="system[img_path]" id="img_path" size="50" value="<?php echo $system['img_path']?>" /></td>
          </tr>
          <tr>
            <th width="140"><?php echo L('show_page_trace')?></th>
            <td class="y-bg"><input name="config[show_trace]" value="true" type="radio" <?php echo ($config['show_trace']) ? ' checked' : ''?>> <?php echo L('setting_yes')?>&nbsp;&nbsp;&nbsp;&nbsp; <input name="config[show_trace]" value="false" type="radio" <?php echo (!$config['show_trace']) ? ' checked' : ''?>><?php echo L('setting_no')?></td>
          </tr>
          <tr>
            <th width="140"><?php echo L('show_run_time')?></th>
            <td class="y-bg"><input name="config[show_time]" value="true" type="radio" <?php echo ($config['show_time']) ? ' checked' : ''?>> <?php echo L('setting_yes')?>&nbsp;&nbsp;&nbsp;&nbsp; <input name="config[show_time]" value="false" type="radio" <?php echo (!$config['show_time']) ? ' checked' : ''?>> <?php echo L('setting_no')?></td>
          </tr>
        </table>
      </div>
      <!--网站设置-->
      <div id="div_setting_2" class="contentList pad-10 hidden">
        <table width="100%" class="table_form">
          <tr>
            <th width="140"><?php echo L('site_name')?></th>
            <td class="y-bg"><input type="text" class="input-text" name="setting[site_name]" id="site_name" size="30" value="<?php echo $setting['site_name']?>" /></td>
          </tr>
          <tr>
            <th width="140"><?php echo L('site_domain')?></th>
            <td class="y-bg"><input type="text" class="input-text" name="system[app_path]" id="site_url" size="30" value="<?php echo defined('SITE_URL') ? SITE_URL : $app_path ?>" /></td>
          </tr>
          <tr>
            <th width="140"><?php echo L('site_title')?></th>
            <td class="y-bg"><input type="text" class="input-text" name="setting[site_title]" id="site_title" size="50" value="<?php echo $setting['site_title']?>" /></td>
          </tr>
          <tr>
            <th width="140"><?php echo L('keyword_name')?></th>
            <td class="y-bg"><textarea name='setting[keywords]' cols='60' rows='2'><?php echo $setting['keywords']?></textarea></td>
          </tr>
          <tr>
            <th width="140"><?php echo L('description')?></th>
            <td class="y-bg"><textarea name='setting[description]' cols='60' rows='2'><?php echo $setting['description']?></textarea></td>
          </tr>
          <tr>
            <th width="140"><?php echo L('site_copyrigh')?></th>
            <td class="y-bg"><textarea name='setting[copyrights]' cols='60' rows='2'><?php echo $setting['copyrights']?></textarea></td>
          </tr>
          <tr>
            <th width="140"><?php echo L('site_icp')?></th>
            <td class="y-bg"><input type="text" class="input-text" name="setting[icp]" size="50" value="<?php echo $setting['icp']?>" /> <?php echo L('icp_notice')?></td>
          </tr>
        </table>
      </div>
      <!--安全设置-->
      <div id="div_setting_3" class="contentList pad-10 hidden">
        <table width="100%" class="table_form">
          <tr>
            <th width="140"><?php echo L('setting_admin_log')?></th>
            <td class="y-bg">
            	<input name="system[admin_log]" value="true" type="radio" <?php echo ($system['admin_log']) ? ' checked' : ''?>> <?php echo L('setting_yes')?>&nbsp;&nbsp;&nbsp;&nbsp;
                <input name="system[admin_log]" value="false" type="radio"<?php echo (!$system['admin_log']) ? ' checked' : ''?>> <?php echo L('setting_no')?>
            </td>
          </tr>
          <tr>
            <th width="140"><?php echo L('setting_debug')?></th>
            <td class="y-bg">
            	<input name="config[debug]" value="true" type="radio"<?php echo ($config['debug']) ? ' checked' : ''?>> <?php echo L('site_att_watermark_open')?>&nbsp;&nbsp;&nbsp;&nbsp;
            	<input name="config[debug]" value="false"type="radio" <?php echo (!$config['debug']) ? ' checked' : ''?>> <?php echo L('site_att_watermark_close')?>
            </td>
          </tr>
          <tr>
            <th width="140"><?php echo L('setting_error_log')?></th>
            <td class="y-bg">
            	<input name="log[log_threshold]" value="0" type="radio" <?php echo ($log['log_threshold'] == 0) ? ' checked' : ''?>> <?php echo L('log_close')?>&nbsp;&nbsp;&nbsp;&nbsp;
            	<input name="log[log_threshold]" value="1" type="radio" <?php echo ($log['log_threshold'] == 1) ? ' checked' : ''?>> <?php echo L('log_open_1')?>&nbsp;&nbsp;&nbsp;&nbsp;
            	<input name="log[log_threshold]" value="2" type="radio" <?php echo ($log['log_threshold'] == 2) ? ' checked' : ''?>> <?php echo L('log_open_2')?>&nbsp;&nbsp;&nbsp;&nbsp;
            	<input name="log[log_threshold]" value="3" type="radio" <?php echo ($log['log_threshold'] == 3) ? ' checked' : ''?>> <?php echo L('log_open_3')?>&nbsp;&nbsp;&nbsp;&nbsp;
            	<input name="log[log_threshold]" value="4" type="radio" <?php echo ($log['log_threshold'] == 4) ? ' checked' : ''?>> <?php echo L('log_open_4')?></td>
          </tr>
          <tr>
            <th><?php echo L('setting_error_log_size')?></th>
            <td class="y-bg"><input type="text" class="input-text" name="log[log_chunk_size]" id="errorlog_size" size="5" value="<?php echo substr($log['log_chunk_size'],0,-1)?>" /> MB</td>
          </tr>
          <tr>
            <th width="140"><?php echo L('show_error_msg')?></th>
            <td class="y-bg">
            	<input name="config[show_error_msg]" value="true" type="radio" <?php echo ($config['show_error_msg']) ? ' checked' : ''?>> <?php echo L('setting_yes')?>&nbsp;&nbsp;&nbsp;&nbsp;
           		<input name="config[show_error_msg]" value="false" type="radio" <?php echo (!$config['show_error_msg']) ? ' checked' : ''?>> <?php echo L('setting_no')?></td>
          </tr>
          <tr>
            <th><?php echo L('error_message')?></th>
            <td class="y-bg"><input type="text" class="input-text" name="config[error_message]" size="40" id="error_message"value="<?php echo $config['error_message']?>" /></td>
          </tr>
          <tr>
            <th><?php echo L('error_page')?></th>
            <td class="y-bg"><input type="text" class="input-text" name="config[error_page]" size="40" value="<?php echo $config['error_page']?>" /></td>
          </tr>
          <tr>
            <th><?php echo L('setting_maxloginfailedtimes')?></th>
            <td class="y-bg"><input type="text" class="input-text" name="setting[maxloginfailedtimes]" id="maxloginfailedtimes" size="10" value="<?php echo $setting['maxloginfailedtimes']?>" /></td>
          </tr>
          <tr>
            <th width="140"><?php echo L('show_firephp')?></th>
            <td class="y-bg">
            	<input name="config[firephp]" value="true" type="radio" <?php echo ($config['firephp']) ? ' checked' : ''?>> <?php echo L('setting_yes')?>&nbsp;&nbsp;&nbsp;&nbsp;
            	<input name="config[firephp]" value="false" type="radio" <?php echo (!$config['firephp']) ? ' checked' : ''?>> <?php echo L('setting_no')?></td>
          </tr>
        </table>
      </div>
      <!--附件设置-->
      <div id="div_setting_4" class="contentList pad-10 hidden">
      		<table width="100%" class="table_form">
            	<tr>
            		<th width="140"><?php echo L('setting_attachment_storage')?></th>
            		<td class="y-bg">
                    	<input name="attachment[storage]" value="Local" type="radio" <?php echo ($attachment['storage'] == 'Local') ? ' checked' : ''?> onclick="$('#ftp').hide();$('#alioss').hide();$('#local').show();"> <?php echo L('local')?>&nbsp;&nbsp;&nbsp;&nbsp;
              			<input name="attachment[storage]" value="Ftp" type="radio" <?php echo ($attachment['storage'] == 'Ftp') ? ' checked' : ''?> onclick="$('#ftp').show();$('#alioss').hide();$('#local').hide()" onclick="$('#ftp').hide()"> FTP &nbsp;&nbsp;&nbsp;&nbsp;
						<input name="attachment[storage]" value="ALIOSS" type="radio" <?php echo ($attachment['storage'] == 'ALIOSS') ? ' checked' : ''?> onclick="$('#alioss').show();$('#ftp').hide();$('#local').hide()"> ALIOSS &nbsp;&nbsp;&nbsp;&nbsp;
						<?php echo L('setting_attachment_storage_tips')?></td>
          		</tr>

            <tbody id="local" <?php if($attachment['storage'] != 'Local'){?>style="display:none" <?php } ?>>
            	<tr>
					<th width="140"><?php echo L('setting_upload_url')?></th>
					<td class="y-bg"><input type="text" class="input-text" name="attachment[upload_url]" id="upload_url" size="50" value="<?php echo $attachment['upload_url']?>" /></td>
				</tr>
            </tbody>
            <tbody id="ftp" <?php if($attachment['storage'] != 'Ftp'){?>style="display:none" <?php } ?>>
                <tr>
					<th width="100"><?php echo L('setting_attachment_ftp_ssl')?></th>
					<td><input name="attachment[ftp_ssl]" value="true" type="radio" <?php echo ($attachment['ftp_ssl']) ? ' checked' : ''?>> <?php echo L('yes')?>&nbsp;&nbsp;&nbsp;&nbsp;
              			<input name="attachment[ftp_ssl]" value="false" type="radio" <?php echo (!$attachment['ftp_ssl']) ? ' checked' : ''?>> <?php echo L('no');?>  <?php echo L('setting_attachment_ftp_ssl_tips')?></td>
				</tr>
                <tr>
					<th width="100"><?php echo L('setting_attachment_ftp_host')?></th>
					<td><input type="text" name="attachment[ftp_host]" value="<?php echo isset($attachment['ftp_host']) ? $attachment['ftp_host'] : ''?>" size="30" id="ftp_host" class="input-text" />  <?php echo L('setting_attachment_ftp_host_tips')?></td>
				</tr>
                <tr >
					<th width="100"><?php echo L('setting_attachment_ftp_port')?></th>
					<td><input type="text" name="attachment[ftp_port]" id="ftp_port" value="<?php echo isset($attachment['ftp_port']) ? $attachment['ftp_port'] : ''?>" size="10" class="input-text" />  <?php echo L('setting_attachment_ftp_port_tips')?></td>
				</tr>
                <tr >
					<th width="100"><?php echo L('setting_attachment_ftp_username')?></th>
					<td><input type="text" name="attachment[ftp_username]" id="ftp_username" value="<?php echo isset($attachment['ftp_username']) ? $attachment['ftp_username'] : ''?>" size="30" class="input-text" />  <?php echo L('setting_attachment_ftp_username_tips')?></td>
				</tr>
                <tr >
					<th width="100"><?php echo L('setting_attachment_ftp_password')?></th>
                    <?php $attachment['ftp_password'] = $attachment['ftp_password'] ? $attachment['ftp_password'] {0} . '********' . substr ( $attachment['ftp_password'], - 2 ) : ''; ?>
					<td><input type="text" name="attachment[ftp_password]" id="ftp_password" value="<?php echo isset($attachment['ftp_password']) ? $attachment['ftp_password'] : ''?>" size="30" class="input-text" />  <?php echo L('setting_attachment_ftp_password_tips')?></td>
				</tr>
                <tr >
					<th width="100"><?php echo L('setting_attachment_ftp_pasv')?></th>
					<td><input name="attachment[ftp_pasv]" value="true" type="radio" <?php echo ($attachment['ftp_pasv']) ? ' checked' : ''?>> <?php echo L('yes')?>&nbsp;&nbsp;&nbsp;&nbsp;
              			<input name="attachment[ftp_pasv]" value="false" type="radio" <?php echo (!$attachment['ftp_pasv']) ? ' checked' : ''?>> <?php echo L('no');?>  <?php echo L('setting_attachment_ftp_pasv_tips')?></td>
				</tr>
                <tr >
					<th width="100"><?php echo L('setting_attachment_ftp_attachdir')?></th>
					<td><input type="text" name="attachment[ftp_attachdir]" id="ftp_attachdir" value="<?php echo isset($attachment['ftp_attachdir']) ? $attachment['ftp_attachdir'] : ''?>" size="30" class="input-text" /> <?php echo L('setting_attachment_ftp_attachdir_tips')?></td>
				</tr>
                <tr >
					<th width="100"><?php echo L('setting_attachment_ftp_timeout')?></th>
					<td><input type="text" name="attachment[ftp_timeout]" id="ftp_timeout" value="<?php echo isset($attachment['ftp_timeout']) ? $attachment['ftp_timeout'] : ''?>" size="10" class="input-text" /> <?php echo L('miao');?> <input type="button" class="button" onClick="javascript:ftp_test();" value="<?php echo L('setting_attachment_ftp_test')?>"> <span id="test_ftp"></span></td>
				</tr>
                <tr>
					<th width="140"><?php echo L('setting_upload_url')?></th>
					<td class="y-bg"><input type="text" class="input-text" name="attachment[ftp_url]" id="ftp_url" size="50" value="<?php echo $attachment['ftp_url']?>" /></td>
				</tr>
            </tbody>
            <tbody id="alioss" <?php if($attachment['storage'] != 'ALIOSS'){?>style="display:none"<?php } ?>>
            	<tr >
					<th width="100">OSS HOST</th>
					<td><input name="attachment[oss_host]" value="oss.aliyuncs.com" onclick="set_aliossurl();" type="radio" <?php echo ($attachment['oss_host'] == 'oss.aliyuncs.com') ? ' checked' : ''?>> oss.aliyuncs.com&nbsp;&nbsp;&nbsp;&nbsp;
              			<input name="attachment[oss_host]" value="oss-internal.aliyuncs.com" onclick="set_aliossurl();" type="radio" <?php echo ($attachment['oss_host'] == 'oss-internal.aliyuncs.com') ? ' checked' : ''?>> oss-internal.aliyuncs.com  <?php echo L('setting_attachment_oss_host_tips')?>
                        </td>
				</tr>
    			<tr >
					<th width="100">OSS ACCESS ID</th>
					<td><input type="text" name="attachment[oss_access_id]" id="oss_access_id" value="<?php echo isset($attachment['oss_access_id']) ? $attachment['oss_access_id'] : ''?>" size="30" class="input-text" /></td>
				</tr>
                <tr >
					<th width="100">OSS ACCESS KEY</th>
					<td><input type="text" name="attachment[oss_access_key]" id="oss_access_key" value="<?php echo isset($attachment['oss_access_key']) ? $attachment['oss_access_key'] : ''?>" size="30" class="input-text" /></td>
				</tr>

                <tr >
					<th width="100">OSS Bucket</th>
					<td><input type="text" name="attachment[oss_bucket]" id="oss_bucket" value="<?php echo isset($attachment['oss_bucket']) ? $attachment['oss_bucket'] : ''?>" size="20" class="input-text" />  </td>
				</tr>
                <tr>
					<th width="140"><?php echo L('setting_upload_url')?></th>
					<td class="y-bg"><input type="text" class="input-text" name="attachment[oss_url]" id="oss_url" size="50" value="<?php echo $attachment['oss_url']?>" /></td>
				</tr>
                <tr >
					<th width="100"><?php echo L('setting_attachment_alioss_domain_style');?></th>
					<td><input name="attachment[oss_domain_style]" value="true" type="radio" <?php echo ($attachment['oss_domain_style']) ? ' checked' : ''?>> <?php echo L('yes')?>&nbsp;&nbsp;&nbsp;&nbsp;
              			<input name="attachment[oss_domain_style]" value="false" type="radio" <?php echo (!$attachment['oss_domain_style']) ? ' checked' : ''?>> <?php echo L('no');?>&nbsp;&nbsp;<input type="button" class="button" onClick="javascript:alioss_test();" value="<?php echo L('setting_attachment_alioss_test')?>"> <span id="test_alioss"></span></td>
				</tr>
            </tbody>
            	<tr>
            		<th width="140"><?php echo L('setting_attachment_stat')?></th>
            		<td class="y-bg">
                    	<input name="attachment[stat]" value="true" type="radio" <?php echo ($attachment['stat']) ? ' checked' : ''?>> <?php echo L('setting_yes')?>&nbsp;&nbsp;&nbsp;&nbsp;
              			<input name="attachment[stat]" value="false" type="radio" <?php echo (!$attachment['stat']) ? ' checked' : ''?>> <?php echo L('setting_no')?>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo L('setting_attachment_stat_desc')?></td>
          		</tr>
				<tr>
					<th width="140"><?php echo L('site_att_upload_maxsize')?></th>
					<td class="y-bg"><input type="text" class="input-text" name="attachment[maxsize]" id="upload_maxsize" size="10" value="<?php echo $attachment['maxsize'] ? $attachment['maxsize'] : '2048' ?>" />KB</td>
				</tr>
				<tr>
					<th width="140"><?php echo L('site_att_allow_ext')?></th>
					<td class="y-bg"><input type="text" class="input-text" name="attachment[allowext]" id="upload_allowext" size="50" value="<?php echo $attachment['allowext']?>" /></td>
				</tr>
                <tr>
					<th width="140"><?php echo L('site_att_gb_check')?></th>
					<td class="y-bg"><?php echo $this->check_gd()?></td>
				<tr>
					<th><?php echo L('site_att_watermark_enable')?></th>
					<td class="y-bg"><input class="radio_style" name="attachment[watermark_enable]" value="1" <?php echo $attachment['watermark_enable']==1 ? 'checked="checked"' : ''?> type="radio"><?php echo L('site_att_watermark_open')?>&nbsp;&nbsp;&nbsp;&nbsp;<input class="radio_style" name="attachment[watermark_enable]" value="0" <?php echo $attachment['watermark_enable']==0 ? 'checked="checked"' : ''?> type="radio"><?php echo L('site_att_watermark_close')?></td>
				</tr>
				<tr>
					<th><?php echo L('site_att_watermark_condition')?></th>
					<td class="y-bg"><?php echo L('site_att_watermark_minwidth')?> <input type="text" class="input-text" name="attachment[watermark_minwidth]" id="watermark_minwidth" size="10" value="<?php echo $attachment['watermark_minwidth'] ? $attachment['watermark_minwidth'] : '300' ?>" /> * <?php echo L('site_att_watermark_minheight')?> <input type="text" class="input-text" name="attachment[watermark_minheight]" id="watermark_minheight" size="10" value="<?php echo $attachment['watermark_minheight'] ? $attachment['watermark_minheight'] : '300' ?>" /></td>
				</tr>
				<tr>
					<th width="140"><?php echo L('site_att_watermark_img')?></th>
					<td class="y-bg"><input type="text" name="attachment[watermark_img]" id="watermark_img" size="30" value="<?php echo $attachment['watermark_img'] ? $attachment['watermark_img'] : 'mark.gif' ?>" /> <?php echo L('site_att_watermark_img_desc')?></td>
				</tr>
				<tr>
					<th width="140"><?php echo L('site_att_watermark_pct')?></th>
					<td class="y-bg"><input type="text" class="input-text" name="attachment[watermark_pct]" id="watermark_pct" size="10" value="<?php echo $attachment['watermark_pct'] ? $attachment['watermark_pct'] : '100' ?>" /> <?php echo L('site_att_watermark_pct_desc')?></td>
				</tr>
				<tr>
					<th width="140"><?php echo L('site_att_watermark_quality')?></th>
					<td class="y-bg"><input type="text" class="input-text" name="attachment[watermark_quality]" id="watermark_quality" size="10" value="<?php echo $attachment['watermark_quality'] ? $attachment['watermark_quality'] : '80' ?>" /><?php echo L('site_att_watermark_quality_desc')?></td>
				</tr>
				<tr>
					<th width="140"><?php echo L('site_att_watermark_pos')?></th>
					<td><table width="80%" class="radio-label">
						<tr>
							<td rowspan="3"><input class="radio_style" name="attachment[watermark_pos]" value="10" type="radio" <?php echo ($attachment['watermark_pos']==10) ? 'checked':''?>><?php echo L('site_att_watermark_pos_10')?></td>
							<td><input class="radio_style" name="attachment[watermark_pos]" value="1" type="radio" <?php echo ($attachment['watermark_pos']==1) ? 'checked':''?>> <?php echo L('site_att_watermark_pos_1')?></td>
							<td><input class="radio_style" name="attachment[watermark_pos]" value="2" type="radio" <?php echo ($attachment['watermark_pos']==2) ? 'checked':'' ?>> <?php echo L('site_att_watermark_pos_2')?></td>
							<td><input class="radio_style" name="attachment[watermark_pos]" value="3" type="radio" <?php echo ($attachment['watermark_pos']==3) ? 'checked':''?>> <?php echo L('site_att_watermark_pos_3')?></td>
						</tr>
						<tr>
						<td><input class="radio_style" name="attachment[watermark_pos]" value="4" type="radio" <?php echo ($attachment['watermark_pos']==4) ? 'checked':''?>> <?php echo L('site_att_watermark_pos_4')?></td>
						<td><input class="radio_style" name="attachment[watermark_pos]" value="5" type="radio" <?php echo ($attachment['watermark_pos']==5) ? 'checked':''?>> <?php echo L('site_att_watermark_pos_5')?></td>
						<td><input class="radio_style" name="attachment[watermark_pos]" value="6" type="radio" <?php echo ($attachment['watermark_pos']==6) ? 'checked':''?>> <?php echo L('site_att_watermark_pos_6')?></td>
					</tr>
					<tr>
						<td><input class="radio_style" name="attachment[watermark_pos]" value="7" type="radio" <?php echo ($attachment['watermark_pos']==7) ? 'checked':''?>> <?php echo L('site_att_watermark_pos_7')?></td>
						<td><input class="radio_style" name="attachment[watermark_pos]" value="8" type="radio" <?php echo ($attachment['watermark_pos']==8) ? 'checked':''?>> <?php echo L('site_att_watermark_pos_8')?></td>
						<td><input class="radio_style" name="attachment[watermark_pos]" value="9" type="radio" <?php echo ($attachment['watermark_pos']==9) ? 'checked':''?>> <?php echo L('site_att_watermark_pos_9')?></td>
					</tr>
				</table></td>
			</tr>
		</table>
      </div>
      <!--短信设置-->
      <div id="div_setting_5" class="contentList pad-10 hidden">
      		<table width="100%" class="table_form">
            	<tr>
            		<th width="140"><?php echo L('setting_sms_driver')?></th>
            		<td class="y-bg">
                    	<input name="sms[driver]" value="Emay" type="radio" id="driver" <?php echo ($sms['driver'] == 'Emay') ? ' checked' : ''?>> <?php echo L('emay')?>&nbsp;&nbsp;&nbsp;&nbsp;
              			<input name="sms[driver]" value="Winic" type="radio" id="driver" <?php echo ($sms['driver'] == 'Winic') ? ' checked' : ''?>> <?php echo L('winic')?>&nbsp;&nbsp;&nbsp;&nbsp;
                    </td>
          		</tr>
				<tr>
					<th width="140"><?php echo L('username')?></th>
					<td class="y-bg">
                    	<input type="text" class="input-text"	name="sms[username]" id="username" size="30" value="<?php echo $sms['username']?>" />
                        <input type="button" class="button" onClick="javascript:get_balance();" value="<?php echo L('get_balance')?>">  <span id='get_balance'></span>
                    </td>
				</tr>
				<tr>
					<th width="140"><?php echo L('password')?></th>
					<td class="y-bg"><input type="text" class="input-text" name="sms[password]" id="password" size="30" value="<?php echo $sms['password']?>" /> </td>
				</tr>
				<tr>
					<th width="140"><?php echo L('session_key')?></th>
					<td class="y-bg"><input type="text" class="input-text" name="sms[session_key]" id="session_key" size="30" value="<?php echo $sms['session_key']?>" /> </td>
				</tr>
				<tr>
					<th width="140"><?php echo L('sign')?></th>
					<td class="y-bg"><input type="text" class="input-text"name="sms[sign]" id="sign" size="30" value="<?php echo $sms['sign']?>" /></td>
				</tr>
				<tr>
					<th width="140"><?php echo L('sms_test')?></th>
					<td class="y-bg">
                    	<input type="text" class="input-text" name="sms_to" id="sms_to" size="30" value="" />
                        <input type="button" class="button" onClick="javascript:test_sms();" value="<?php echo L('sms_test_send')?>"> <span id='test_sms'></span>
                    </td>
				</tr>
			</table>
      </div>
      <!--联系设置-->
      <div id="div_setting_6" class="contentList pad-10 hidden">
        <table width="100%" class="table_form">
        <tr>
       <th width="140"><?php echo L('open_front_desk_customer_service')?></th>
       <td class="y-bg">
       <input class="radio_style" name="setting[live_ifonserver]" value="true" <?php echo $setting['live_ifonserver'] ? 'checked="checked"' : ''?> type="radio"> <?php echo L('site_att_watermark_open')?>&nbsp;&nbsp;&nbsp;&nbsp;
	   <input class="radio_style" name="setting[live_ifonserver]" value="false" <?php echo !$setting['live_ifonserver'] ? 'checked="checked"' : ''?> type="radio"> <?php echo L('site_att_watermark_close')?></td>
  	</tr>
  	<tr>
       <th width="140"><?php echo L('the_default_on_customer_service_list')?></th>
       <td class="y-bg">
       <input class="radio_style" name="setting[live_boxopen]" value="true" <?php echo $setting['live_boxopen'] ? 'checked="checked"' : ''?> type="radio"> <?php echo L('site_att_watermark_open')?>&nbsp;&nbsp;&nbsp;&nbsp;
	   <input class="radio_style" name="setting[live_boxopen]" value="false" <?php echo !$setting['live_boxopen'] ? 'checked="checked"' : ''?> type="radio"> <?php echo L('site_att_watermark_close')?></td>
  	</tr>
  	 <tr>
       <th width="140"><?php echo L('popup_invite_dialog_box')?></th>
       <td class="y-bg">
       <input class="radio_style" name="setting[live_boxtip]" value="true" <?php echo $setting['live_boxtip'] ? 'checked="checked"' : ''?> type="radio"> <?php echo L('site_att_watermark_open')?>&nbsp;&nbsp;&nbsp;&nbsp;
	   <input class="radio_style" name="setting[live_boxtip]" value="false" <?php echo !$setting['live_boxtip'] ? 'checked="checked"' : ''?> type="radio"> <?php echo L('site_att_watermark_close')?></td>
  	</tr>
  	<tr>
       <th width="140"><?php echo L('customer_service_floating_box_location')?></th>
       <td class="y-bg">
       <input class="radio_style" name="setting[live_serverlistp]" value="left" <?php echo $setting['live_serverlistp']=='left' ? 'checked="checked"' : ''?> type="radio"> <?php echo L('live_left')?>&nbsp;&nbsp;&nbsp;&nbsp;
	   <input class="radio_style" name="setting[live_serverlistp]" value="right" <?php echo $setting['live_serverlistp']=='right' ? 'checked="checked"' : ''?> type="radio"> <?php echo L('live_right')?></td>
  	</tr>
  	<tr>
       <th width="140"><?php echo L('Company_name')?></th>
       <td class="y-bg"><input name="setting[companyname]" type="text" size="30" value="<?php echo $setting['companyname']?>"></td>
  	</tr>
 	<tr>
       <th width="140"><?php echo L('Contact')?></th>
       <td class="y-bg"><input name="setting[contact_name]" type="text" size="20" value="<?php echo $setting['contact_name']?>" ></td>
  	</tr>
  	<tr>
       <th><?php echo L('Mobile')?></th>
       <td class="y-bg"><input name="setting[mobile]" type="text" size="20" value="<?php echo $setting['mobile']?>"></td>
 	</tr>
 	<tr>
       <th><?php echo L('Phone')?></th>
       <td class="y-bg"> <input name="setting[telephone]" type="text" size="30" value="<?php echo $setting['telephone']?>" ></td>
	 </tr>
	 <tr>
            <th width="140">QQ</th>
            <td class="y-bg"><input name="setting[qq]" type="text" size="40" value="<?php echo $setting['qq']?>"> <?php echo L('qq_tip')?><a href="http://zc.qq.com/" target="_blank"><?php echo L('click_register')?></a></td>
          </tr>
 	<tr>
       <th><?php echo L('Address')?></th>
       <td class="y-bg"> <input name="setting[address]" type="text" size="50" value="<?php echo $setting['address']?>"></td>
 	</tr>
 	<tr>
       <th>E-Mail</th>
       <td class="y-bg"> <input name="setting[email]" type="text" size="40" value="<?php echo $setting['email']?>" > <a href="http://email.163.com/" target="_blank"><?php echo L('click_register')?></a></td>
  	</tr>
        </table>
      </div>
      <!--SNS设置-->
      <div id="div_setting_7" class="contentList pad-10 hidden">
      		<table width="100%" class="table_form">
            	<tr>
            		<td width="180"><?php echo L('Sina_Weibo_Open')?></td>
                  	<td width="150">开启：
                    	<input type="radio" name="sns[sina][enable]" value="1" <?php if($sns['sina']['enable'] == 1) echo 'checked'?>> <?php echo L('yes')?>
                    	<input type="radio" name="sns[sina][enable]" value="0" <?php if($sns['sina']['enable'] == 0) echo 'checked'?>> <?php echo L('no')?>
                  	</td>
                  	<td width="250">App Key：<input type="text" name="sns[sina][app_key]" class="input-text" value="<?php echo $sns['sina']['app_key'];?>"></td>
                  	<td >App Secret：<input type="text" name="sns[sina][app_secret]" class="input-text"  size="50" value="<?php echo $sns['sina']['app_secret'];?>"></td>
          		</tr>
          		<tr>
            		<td><?php echo L('Tencent_QQ_Open')?></td>
            		<td>开启：
                    	<input type="radio" name="sns[qq][enable]" value="1" <?php if($sns['qq']['enable'] == 1) echo 'checked'?>> <?php echo L('yes')?>
                        <input type="radio" name="sns[qq][enable]" value="0" <?php if($sns['qq']['enable'] == 0) echo 'checked'?>> <?php echo L('no')?>
                    </td>
                  	<td> App  Id：<input type="text" name="sns[qq][app_key]" class="input-text" value="<?php echo $sns['qq']['app_key'];?>"></td>
                  	<td> App Key：<input type="text" name="sns[qq][app_secret]" class="input-text" size="50" value="<?php echo $sns['qq']['app_secret'];?>"></td>
          		</tr>
          		<tr>
            		<td><?php echo L('Baidu_Open')?></td>
            		<td>开启：
                    	<input type="radio" name="sns[baidu][enable]" value="1" <?php if($sns['baidu']['enable'] == 1) echo 'checked'?>> <?php echo L('yes')?>
                    	<input type="radio" name="sns[baidu][enable]" value="0" <?php if($sns['baidu']['enable'] == 0) echo 'checked'?>> <?php echo L('no')?>
                    </td>
                  	<td> App Key：<input type="text" name="sns[baidu][app_key]" class="input-text" value="<?php echo $sns['baidu']['app_key'];?>"></td>
                  	<td> App Secret： <input type="text" name="sns[baidu][app_secret]" class="input-text" size="50" value="<?php echo $sns['baidu']['app_secret'];?>"></td>

          		</tr>
          		<tr>
            		<td><?php echo L('Renren_Open')?></td>
            		<td>开启：
                    	<input type="radio" name="sns[renren][enable]" value="1" <?php if($sns['renren']['enable'] == 1) echo 'checked'?>> <?php echo L('yes')?>
                    	<input type="radio" name="sns[renren][enable]" value="0" <?php if($sns['renren']['enable'] == 0) echo 'checked'?>> <?php echo L('no')?>
                    </td>
                  	<td> App Key： <input type="text" name="sns[renren][app_key]" class="input-text" value="<?php echo $sns['renren']['app_key'];?>"></td>
                  	<td> App Secret： <input type="text" name="sns[renren][app_secret]" class="input-text" size="50" value="<?php echo $sns['renren']['app_secret'];?>"></td>

          		</tr>
          		<tr>
            		<td><?php echo L('Douban_Open')?></td>
            		<td>开启：
                    	<input type="radio" name="sns[douban][enable]" value="1" <?php if($sns['douban']['enable'] == 1) echo 'checked'?>> <?php echo L('yes')?>
                        <input type="radio" name="sns[douban][enable]" value="0" <?php if($sns['douban']['enable'] == 0) echo 'checked'?>> <?php echo L('no')?>
                    </td>
                  	<td> App Key：<input type="text" name="sns[douban][app_key]" class="input-text" value="<?php echo $sns['douban']['app_key'];?>"></td>
                  	<td> App Secret： <input type="text" name="sns[douban][app_secret]" class="input-text" size="50"	value="<?php echo $sns['douban']['app_secret'];?>"></td>
          		</tr>
            </table>
      </div>
      <!--邮件设置-->
      <div id="div_setting_8" class="contentList pad-10 hidden">
      	<table width="100%" class="table_form">
			<tr>
				<th width="125"><?php echo L('mail_type')?></th>
				<td class="y-bg">
                	<input name="settingnew[type]" checkbox="type" value="3" onclick="$('#hidden1').show();$('#hidden2').hide();" type="radio" <?php echo $mail['type']== 3 ? ' checked' : ''?>> <?php echo L('mail_type_smtp')?><br>
					<input name="settingnew[type]" checkbox="type" value="2" onclick="$('#hidden1').hide();$('#hidden2').show();" type="radio" <?php echo $mail['type']== 2 ? ' checked' : ''?>> <?php echo L('mail_type_esmtp')?><br>
					<input name="settingnew[type]" checkbox="type" value="1" onclick="$('#hidden1').hide();$('#hidden2').hide();" type="radio" <?php echo $mail['type']== 1 ? ' checked' : ''?> <?php if(substr(strtolower(PHP_OS), 0, 3) == 'win') echo 'disabled'; ?> /> <?php echo L('mail_type_mail')?>
				</td>
			</tr>
			<tbody class="sub" id="hidden1" style="<?php if($mail['type'] != 3) echo 'display:none'?>">
				<tr>
					<td colspan="2">
						<table width="90%" id="hidden" class="table-list">
							<thead>
								<tr>
									<th width="11%" style="text-align: left;"><?php echo L('delete')?></th>
									<th width="20%" style="text-align: left;"><?php echo L('mail_server')?></th>
									<th width="10%" style="text-align: left;"><?php echo L('mail_port')?></th>
								</tr>
							</thead>
							<?php foreach($mail['smtp'] as $id => $smtp) {?>
							<tr>
								<td><input type="checkbox" name="settingnew[smtp][delete][]" value="<?php echo $id;?>"></td>
								<td><input type="text" class="input-text" name="settingnew[smtp][<?php echo $id;?>][server]" value="<?php echo $smtp['server']?>" style="width: 90%;"></td>
								<td><input type="text" class="input-text" name="settingnew[smtp][<?php echo $id;?>][port]" value="<?php echo $smtp['port'];?>" style="width: 30%;"></td>
							</tr>
							<?php }	?>
							<tr>
								<td colspan="7"><div><a href="###" onclick="setrowtypedata(0);addrow(this, 0);" class="addtr"><?php echo L('mail_add_server');?></a></div></td>
							</tr>
						</table>
					</td>
				</tr>
			</tbody>
			<tbody id="hidden2" style="<?php if($mail['type'] != 2) echo 'display:none'?>">
				<tr>
					<td colspan="2">
						<table width="90%" id="hidden" class="table-list">
							<thead>
								<tr>
									<th width="11%" style="text-align: left;"><?php echo L('delete')?></th>
									<th width="20%" style="text-align: left;"><?php echo L('mail_server')?></th>
									<th width="10%" style="text-align: left;"><?php echo L('mail_port')?></th>
									<th width="10%" style="text-align: left;"><?php echo L('mail_auth')?></th>
									<th width="18%" style="text-align: left;"><?php echo L('mail_from')?></th>
									<th width="18%" style="text-align: left;"><?php echo L('mail_user')?></th>
									<th width="18%" style="text-align: left;"><?php echo L('mail_password')?></th>
								</tr>
							</thead>
							<?php
		foreach ( $mail ['smtp'] as $id => $smtp ) {
			$checkauth = $smtp ['auth'] ? 'checked' : '';
			$smtp ['auth_password'] = $smtp ['auth_password'] ? $smtp ['auth_password'] {0} . '********' . substr ( $smtp ['auth_password'], - 2 ) : '';
			?>
							<tr>
								<td><input type="checkbox" name="settingnew[esmtp][delete][]" value="<?php echo $id;?>"></td>
								<td><input type="text" class="input-text" name="settingnew[esmtp][<?php echo $id;?>][server]" value="<?php echo $smtp['server']?>" style="width: 90%;"></td>
								<td><input type="text" class="input-text" name="settingnew[esmtp][<?php echo $id;?>][port]" value="<?php echo $smtp['port'];?>" style="width: 30%;"></td>
								<td><input type="checkbox" name="settingnew[esmtp][<?php echo $id;?>][auth]" value="1" <?php echo $checkauth;?>></td>
								<td><input type="text" class="txt" name="settingnew[esmtp][<?php echo $id;?>][from]" value="<?php echo $smtp['from'];?>" style="width: 90%;"></td>
								<td><input type="text" class="txt" name="settingnew[esmtp][<?php echo $id;?>][auth_username]" value="<?php echo $smtp['auth_username'];?>" style="width: 90%;"></td>
								<td><input type="text" class="txt" name="settingnew[esmtp][<?php echo $id;?>][auth_password]" value="<?php echo $smtp['auth_password'];?>" style="width: 90%;"></td>
							</tr>
			<?php }		?>
							<tr>
								<td colspan="7"><div><a href="###" onclick="setrowtypedata(1);addrow(this, 0);"><?php echo L('mail_add_server');?></a></div></td>
							</tr>
						</table>
					</td>
				</tr>
			</tbody>
				<script type="text/JavaScript">
			var rowtypedata = [];
			function setrowtypedata(sendtype) {
				if(sendtype) {
					rowtypedata = [[
									[1,'', ''],
									[1,'<input type="text" class="input-text" name="newsmtp[server][]" style="width: 90%;">', ''],
									[1,'<input type="text" class="input-text" name="newsmtp[port][]" value="25" style="width: 30%;">', ''],
									[1,'<input type="checkbox" name="newsmtp[auth][]" value="1">', ''],
									[1,'<input type="text" class="input-text" name="newsmtp[from][]" style="width: 90%;">'],
									[1,'<input type="text" class="input-text" name="newsmtp[auth_username][]" style="width: 90%;">'],
									[1,'<input type="text" class="input-text" name="newsmtp[auth_password][]" style="width: 90%;">'],
								]];
				}else{
					rowtypedata = [
									[
										[1,'', ''],
										[1,'<input type="text" class="input-text" name="newsmtp[server][]" style="width: 90%;">', ''],
										[1,'<input type="text" class="input-text" name="newsmtp[port][]" value="25" style="width: 30%;">', '']
									]
								];
				}

			}
			setrowtypedata(<?php echo $mail['type']?>);
</script>
			<tr>
				<th><?php echo L('mail_smtp_cc')?></th>
				<td class="y-bg"><input type="text" class="input-text" name="settingnew[cc]" id="cc" size="30" value="<?php echo $mail['cc']?>" /></td>
			</tr>
			<tr>
				<th><?php echo L('mail_smtp_poll')?></th>
				<td class="y-bg">
                	<input name="settingnew[poll]" value="1" type="radio" <?php echo $mail['poll']==1 ? ' checked' : ''?>> <?php echo L('setting_yes')?>&nbsp;&nbsp;&nbsp;&nbsp;
	 				<input name="settingnew[poll]" value="0" type="radio" <?php echo $mail['poll']==0 ? ' checked' : ''?>> <?php echo L('setting_no')?>
    			</td>
			</tr>
			<tr>
				<th width="130"><?php echo L('mail_delimiter')?></th>
				<td class="y-bg">
                	<input name="settingnew[delimiter]" checkbox="type" value="1" type="radio" <?php echo $mail['delimiter']==1 ? ' checked' : ''?>>  <?php echo L('mail_delimiter_win')?><BR>
					<input name="settingnew[delimiter]" checkbox="type" value="0" type="radio" <?php echo $mail['delimiter']==0 ? ' checked' : ''?>>  <?php echo L('mail_delimiter_unix')?><BR>
					<input name="settingnew[delimiter]" checkbox="type" value="2" type="radio" <?php echo $mail['delimiter']==2 ? ' checked' : ''?>>  <?php echo L('mail_delimiter_mac')?>
    			</td>
			</tr>
			<tr>
				<th width="135"><?php echo L('mail_includeuser')?></th>
				<td class="y-bg">
                	<input name="settingnew[mailusername]" value="1" type="radio" <?php echo $mail['mailusername']==1 ? ' checked' : ''?>> <?php echo L('setting_yes')?>&nbsp;&nbsp;&nbsp;&nbsp;
	 				<input name="settingnew[mailusername]" value="0" type="radio" <?php echo $mail['mailusername']==0 ? ' checked' : ''?>> <?php echo L('setting_no')?>
    			</td>
			</tr>
			<tr>
				<th><?php echo L('mail_test')?></th>
				<td class="y-bg">
                	<input type="text" class="input-text" name="mail_to" id="mail_to" size="30" value="" />
                    <input type="button" class="button" onClick="javascript:test_mail();" value="<?php echo L('mail_test_send')?>">
                </td>
			</tr>
			</table>
      </div>
      <div class="bk15"></div>
      <input name="dosubmit" type="submit" value="<?php echo L('submit')?>"
				class="button">
    </div>
  </div>
</form>
<script type="text/javascript">
function SwapTab(name,cls_show,cls_hide,cnt,cur){
    for(i=1;i<=cnt;i++){
		if(i==cur){
			 $('#div_'+name+'_'+i).show();
			 $('#tab_'+name+'_'+i).attr('class',cls_show);
		}else{
			 $('#div_'+name+'_'+i).hide();
			 $('#tab_'+name+'_'+i).attr('class',cls_hide);
		}
	}
}
function set_aliossurl(){
	var oss_host = $("input[name='attachment[oss_host]']:checked").val();//服务器
	var oss_bucket = $('#oss_bucket').val();//ftp_pasv
	var oss_domain_style = $("input[name='attachment[oss_domain_style]']:checked").val();//开启三级域名
	if(oss_domain_style == 'true'){
		$("#oss_url").val('http://' + oss_bucket + '.' + oss_host + '/');
	}else{
		$("#oss_url").val('http://' + oss_host + '/' + oss_bucket + '/');
	}
}
//测试AliOSS
function alioss_test() {
	var oss_host = $("input[name='attachment[oss_host]']:checked").val();//服务器
	var oss_access_id = $('#oss_access_id').val();//oss_access_id
	var oss_access_key = $('#oss_access_key').val();//oss_access_key
	var oss_bucket = $('#oss_bucket').val();//ftp_pasv
	var oss_domain_style = $("input[name='attachment[oss_domain_style]']:checked").val();//开启三级域名
	if(oss_host == '') {
		window.top.art.dialog.tips('<?php echo 'OSS ACCESS HOST'.L('empty')?>');
	}else if(oss_access_id == '') {
		window.top.art.dialog.tips('<?php echo 'OSS ACCESS ID'.L('empty')?>');
	}else if(oss_access_key == '') {
		window.top.art.dialog.tips('<?php echo 'OSS ACCESS KEY'.L('empty')?>');
	}else if(oss_bucket == '') {
		window.top.art.dialog.tips('<?php echo 'OSS Bucket'.L('empty')?>');
	}else{
		$.post('?app=admin&controller=setting&action=public_test_alioss',{oss_host:oss_host,oss_access_id:oss_access_id,oss_access_key:oss_access_key,oss_bucket:oss_bucket,oss_domain_style:oss_domain_style},
			function(data){
				try {$("#test_alioss").html(data);}catch(e){}
			}
		);
	}
}
//测试FTP
function ftp_test() {
	var ftp_ssl = $("input[name='attachment[ftp_ssl]']:checked").val();//驱动
	var ftp_host = $('#ftp_host').val();//服务器
	var ftp_port = $('#ftp_port').val();//端口
	var ftp_username = $('#ftp_username').val();//FTP帐号
	var ftp_password = $('#ftp_password').val();//FTP密码
	var ftp_pasv = $("input[name='attachment[ftp_pasv]']:checked").val();//ftp_pasv
	var ftp_attachdir = $('#ftp_attachdir').val();//ftp_pasv
	var ftp_timeout = $('#ftp_timeout').val();//ftp_pasv
	if(ftp_host == '') {
		window.top.art.dialog.tips('<?php echo L('setting_attachment_ftp_host').L('empty')?>');
	}else if(ftp_port == '') {
		window.top.art.dialog.tips('<?php echo L('setting_attachment_ftp_port').L('empty')?>');
	}else if(ftp_username == '') {
		window.top.art.dialog.tips('<?php echo L('setting_attachment_ftp_username').L('empty')?>');
	}else if(ftp_password == '') {
		window.top.art.dialog.tips('<?php echo L('setting_attachment_ftp_password').L('empty')?>');
	}else{
		$.post('?app=admin&controller=setting&action=public_test_ftp',{ftp_ssl:ftp_ssl,ftp_host:ftp_host,ftp_port:ftp_port,ftp_username:ftp_username,ftp_password:ftp_password,ftp_pasv:ftp_pasv,ftp_attachdir:ftp_attachdir,ftp_timeout:ftp_timeout},
			function(data){
				try {$("#test_ftp").html(data);}catch(e){}
			}
		);
	}
}
var addrowdirect = 0;
var addrowkey = 0;
function addrow(obj, type) {
	var table = obj.parentNode.parentNode.parentNode.parentNode.parentNode;
	var row = table.insertRow(obj.parentNode.parentNode.parentNode.rowIndex);
	var typedata = rowtypedata[type];
	for(var i = 0; i <= typedata.length - 1; i++) {
		var cell = row.insertCell(i);
		cell.colSpan = typedata[i][0];
		var tmp = typedata[i][1];
		if(typedata[i][2]) {
			cell.className = typedata[i][2];
		}
		tmp = tmp.replace(/\{(n)\}/g, function($1) {return addrowkey;});
		tmp = tmp.replace(/\{(\d+)\}/g, function($1, $2) {return addrow.arguments[parseInt($2) + 1];});
		cell.innerHTML = tmp;
	}
	addrowkey ++;
	addrowdirect = 0;
}
function test_mail() {
    $.get('?app=admin&controller=setting&action=public_test_mail&mail_to='+$('#mail_to').val(),function(data){
			alert(data);
		}
	);
}
//测试发送短信
function test_sms() {
	var driver = $("input[name='sms[driver]']:checked").val();//驱动
	var username = $('#username').val();//序列号
	var password = $('#password').val();//密码
	var session_key = $('#session_key').val();//Session
	var sign = $('#sign').val();//签名
	var smsto = $('#sms_to').val();
	if(username == '') {
		window.top.art.dialog.tips('<?php echo L('username').L('empty')?>');
	}else if(smsto == '') {
		window.top.art.dialog.tips('<?php echo L('test_sms_error')?>');
	}else{
		$.post('?app=admin&controller=setting&action=public_test_sms&sms_to='+$('#sms_to').val(),{driver:driver,username:username,password:password,session_key:session_key,sign:sign},
			function(data){
				try {$("#test_sms").html(data);}catch(e){}
			}
		);
	}
}
//查询短信余额
function get_balance() {
	var driver = $("input[name='sms[driver]']:checked").val();//驱动
	var username = $('#username').val();//序列号
	var password = $('#password').val();//密码
	var session_key = $('#session_key').val();//Session
	var sign = $('#sign').val();//签名
	if(username == '') {
		window.top.art.dialog.tips('<?php echo L('username').L('empty')?>');
	}else if(password == ''){
		window.top.art.dialog.tips('<?php echo L('password').L('empty')?>');
	}else{
		$.post('?app=admin&controller=setting&action=public_get_balance',{driver:driver,username:username,password:password,session_key:session_key,sign:sign},
			function(data){
			try {
			    $("#get_balance").html(data);
			    }catch(e){}
			}
		);
	}
}
</script>
</body>
</html>