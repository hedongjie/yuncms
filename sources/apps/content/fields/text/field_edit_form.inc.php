<table cellpadding="2" cellspacing="1" width="98%">
	<tr>
      <td width="100">文本框长度</td>
      <td><input type="text" name="setting[size]" value="<?php echo isset($setting['size']) ? $setting['size']:'0';?>" size="10" class="input-text"></td>
    </tr>
	<tr>
      <td>默认值</td>
      <td><input type="text" name="setting[defaultvalue]" value="<?php echo isset($setting['defaultvalue']) ? $setting['defaultvalue'] : '';?>" size="40" class="input-text"></td>
    </tr>
	<tr>
      <td>是否为密码框</td>
      <td><input type="radio" name="setting[ispassword]" value="1" <?php echo isset($setting['ispassword']) ? 'checked' : '';?>> 是 <input type="radio" name="setting[ispassword]" value="0" <?php echo isset($setting['ispassword']) ? '' : 'checked';?>> 否</td>
    </tr>
</table>