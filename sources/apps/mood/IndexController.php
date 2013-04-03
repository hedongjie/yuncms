<?php
defined('IN_YUNCMS') or exit('No permission resources.');
/**
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-13
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: IndexController.php 199 2013-03-29 23:07:40Z 85825770@qq.com $
 */

class IndexController {
    private $setting, $catid, $contentid, $mood_id;
    public function __construct() {
        $this->setting = S('common/mood_program');
        $this->mood_id = isset($_GET['id']) ? $_GET['id'] : '';
        if (empty($this->mood_id)) showmessage(L('id_cannot_be_empty'));
        list($this->catid, $this->contentid) = id_decode($this->mood_id);
        foreach ($this->setting as $k=>$v) {
            if (empty($v['use'])) unset($this->setting[$k]);
        }
    }

    /**
     * 显示心情代码
     */
    public function init() {
        $mood_id =& $this->mood_id;
        $setting =& $this->setting;
        ob_start();
        include template('mood', 'index');
        $html = ob_get_contents();
        ob_clean();
        echo format_js($html);
    }

    //提交选中
    public function post() {
        $mood_id =& $this->mood_id;
        $setting =& $this->setting;
        $cookies = cookie('mood_id');
        $cookie = explode(',', $cookies);
        if (in_array($this->mood_id, $cookie)) {
            $status = 0;
            $msg = L('expressed');
        } else {
            $mood_db = Loader::model('mood_model');
            $key = isset($_GET['k']) && intval($_GET['k']) ? intval($_GET['k']) : '';
            $fields = 'n'.$key;
            if ($data = $mood_db->where(array('catid'=>$this->catid, 'contentid'=>$this->contentid))->find()) {
                $mood_db->where(array('id'=>$data['id']))->update(array('total'=>'+=1', $fields=>'+=1', 'lastupdate'=>TIME));
                $data['total']++;
                $data[$fields]++;
            } else {
                $mood_db->insert(array('total'=>'1', $fields=>'1', 'catid'=>$this->catid, 'contentid'=>$this->contentid,'lastupdate'=>TIME));
                $data['total'] = 1;
                $data[$fields] = 1;
            }
            cookie('mood_id', $cookies.','.$mood_id);
            $status = 1;
        }
        $data = $this->_get();
        $this->_show_result($status,$msg,$data['data'],$data['total']);
    }

    public function _get(){
        $mood_id =& $this->mood_id;
        $setting =& $this->setting;
        $mood_db = Loader::model('mood_model');
        $res = $mood_db->where(array('catid'=>$this->catid, 'contentid'=>$this->contentid))->find();
        $data = array();
        foreach ($setting as $k=>$v) {
            $fields = 'n'.$k;
            $data[$fields] = array();
            if (isset($res['total']) && !empty($res['total'])) {
                $data[$fields]['height'] = ceil(($res[$fields]/$res['total']) * 100);
            } else {
                $data[$fields]['height'] = 0;
            }
            $data[$fields]['number'] = $res[$fields];
        }
        return array('data'=>$data,'total'=>$res['total']);
    }

    //显示AJAX结果
    protected function _show_result($status = 0, $msg = '',$data,$total) {
        if(CHARSET != 'utf-8') $msg = iconv(CHARSET, 'utf-8', $msg);
        exit($_GET['callback'].'('.json_encode(array('status'=>$status,'data'=>$data,'total'=>$total, 'msg'=>$msg)).')');
    }
}