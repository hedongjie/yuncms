<?php
/**
 * 外部数据源缓存
 */
function dbsource_cache() {
    $db = Loader::model('dbsource_model');
    $list = $db->select();
    $data = array();
    if ($list) {
        foreach ($list as $val) {
            $data [$val ['name']] = array ('hostname' => $val ['host'] ,'port'=>$val ['port'],'driver'=>$val ['driver'],'database' => $val ['dbname'],'username' => $val ['username'],'password' => $val ['password'],'charset' => $val ['charset'],'prefix' => $val ['dbtablepre'],'pconnect' => false,'autoconnect' => true );
        }
    } else {
        return false;
    }
    return S('common/dbsource', $data);
}

/**
 * 获取模型YUN标签配置相信
 * @param $module 模型名
 */
function yun_tag_class ($application) {
    $filepath = APPS_PATH.$application.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.$application.'_tag.php';
    if (file_exists($filepath)) {
        $yun_tag = Loader::lib($application.':'.$application.'_tag');
        if (!method_exists($yun_tag, 'yun_tag')) showmessage(L('the_application_will_not_support_the_operation'));
        $html  = $yun_tag->yun_tag();
    } else showmessage(L('the_application_will_not_support_the_operation'), HTTP_REFERER);
    return $html;
}

/**
 * 返回模板地址。
 * @param $id 数据源调用ID
 */
function template_url($id) {
    $filepath = CACHE_PATH.'template'.DIRECTORY_SEPARATOR.'dbsource'.DIRECTORY_SEPARATOR.$id.'.php';
    if (!file_exists($filepath)) {
        $datacall = Loader::model('datacall_model');
        $str = $datacall->where(array('id'=>$id))->field('template')->find();
        $dir = dirname($filepath);
        if(!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $tpl = Core::lib('Template');
        $str = $tpl->template_parse($str['template']);
        @file_put_contents($filepath, $str);
    }
    return $filepath;
}