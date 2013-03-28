<?php
defined('IN_ADMIN') or exit('No permission resources.');
?>
<form style="border: medium none;" id="voteform<?php echo $subjectid;?>" method="post" action="{SITE_URL}index.php?app=vote&controller=index&action=post&subjectid=<?php echo $subjectid;?>">
 <dl>
      <dt><?php echo $subject;?></dt>
      </dl>
<dl>
<?php
if(is_array($options)){
$i=0;
foreach($options as $optionid=>$option){
$i++;
?>
<dd>
&nbsp;&nbsp;<input type="radio" value="<?php echo $option['optionid']?>" name="radio[]" id="radio">
<?php echo $option['option'];?>
</dd>
<?php }}?>
<input type="hidden" name="voteid" value="<?php echo $subjectid;?>">
</dl>
<p> &nbsp;&nbsp; <input type="submit" value="<?php echo L('submit')?>" name="dosubmit" />    &nbsp;&nbsp; <a href="<?php echo SITE_URL?>index.php?app=vote&controller=index&action=result&id=<?php echo $subjectid;?>"><?php echo L('vote_showresult')?></a> </p>
</form>