<?php
    function map($field, $value, $fieldinfo) {
        extract($fieldinfo);
        $setting = string2array($setting);
        $size = $setting['size'];
        $errortips = $this->fields[$field]['errortips'];
        $modelid = $this->fields[$field]['modelid'];
        $tips = $value ? L('editmark','','map') : L('addmark','','map');
        return '<input type="text" name="info['.$field.']" value="'.$value.'" id="'.$field.'" ><input type="button" name="'.$field.'_mark" id="'.$field.'_mark" value="'.$tips.'" class="button" onclick="omnipotent(\'selectid\',\''.SITE_URL.'api.php?c=map&&a=init&field='.$field.'&modelid='.$modelid.'\',\''.L('mapmark','','map').'\',1,700,420)">';
    }
    ?>