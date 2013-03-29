<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-4
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Search_typeController.php 251 2012-11-07 09:43:07Z xutongle $
 */
defined('IN_YUNCMS') or exit('No permission resources.');
Loader::lib ( 'admin:admin', false );
class Search_typeController extends admin {

    public function __construct() {
        parent::__construct();
        $this->db = Loader::model('type_model');
        $this->model = S('common/model');
        $this->application_db = Loader::model('application_model');
    }

    public function init () {
        $datas = array();
        $page = isset($_GET['page']) && trim($_GET['page']) ? intval($_GET['page']) : 1;
        $result_datas = $this->db->where(array('application'=>'search'))->order('listorder ASC')->listinfo( $page);
        $pages = $this->db->pages;
        foreach($result_datas as $r) {
            $r['modelname'] = $this->model[$r['modelid']]['name'];
            $datas[] = $r;
        }
        $big_menu = big_menu(U('search/search_type/add'), 'add', L('add_search_type'), 580, 240);
        $this->cache();
        include $this->admin_tpl('type_list');
    }

    public function add() {
        if(isset($_POST['dosubmit'])) {
            $_POST['info']['application'] = 'search';
            if($_POST['application']=='content') {
                $_POST['info']['modelid'] = intval($_POST['info']['modelid']);
            } else {
                $_POST['info']['typedir'] = $_POST['application'];
                $_POST['info']['modelid'] = 0;
            }
            $this->db->insert($_POST['info']);
            showmessage(L('add_success'), '', '', 'add');
        } else {
            $show_header = $show_validator = '';
            foreach($this->model as $_key=>$_value) {
                $model_data[$_key] = $_value['name'];
            }
            $application_data = array('special' => L('special'),'content' => L('content').L('application'));
            include $this->admin_tpl('type_add');
        }
    }

    public function edit() {
        if(isset($_POST['dosubmit'])) {
            $typeid = intval($_POST['typeid']);
            if($_POST['application']=='content') {
                $_POST['info']['modelid'] = intval($_POST['info']['modelid']);
            } else {
                $_POST['info']['typedir'] = $_POST['application'];
                $_POST['info']['modelid'] = 0;
            }
            $this->db->where(array('typeid'=>$typeid))->update($_POST['info']);
            showmessage(L('update_success'), '', '', 'edit');
        } else {
            $show_header = $show_validator = '';
            $typeid = intval($_GET['typeid']);
            foreach($this->model as $_key=>$_value) {
                $model_data[$_key] = $_value['name'];
            }
            $application_data = array('special' => L('special'),'content' => L('content').L('application'));
            $r = $this->db->getby_typeid($typeid);
            extract($r);
            include $this->admin_tpl('type_edit');
        }
    }

    public function delete() {
        $_GET['typeid'] = intval($_GET['typeid']);
        $this->db->where(array('typeid'=>$_GET['typeid']))->delete();
        showmessage(L('operation_success'), HTTP_REFERER);
    }

    /**
     * 排序
     */
    public function listorder() {
        if(isset($_POST['dosubmit'])) {
            foreach($_POST['listorders'] as $id => $listorder) {
                $this->db->where(array('typeid'=>$id))->update(array('listorder'=>$listorder));
            }
            showmessage(L('operation_success'));
        } else {
            showmessage(L('operation_failure'));
        }
    }

    /**
     * 更新缓存
     * @return boolean
     */
    public function cache() {
        $datas = $search_model = array();
        $result_datas = $result_datas2 = $this->db->where(array('application'=>'search'))->order('listorder ASC')->select();
        foreach($result_datas as $_key=>$_value) {
            if(!$_value['modelid']) continue;
            $datas[$_value['modelid']] = $_value['typeid'];
            $search_model[$_value['modelid']]['typeid'] = $_value['typeid'];
            $search_model[$_value['modelid']]['name'] = $_value['name'];
            $search_model[$_value['modelid']]['sort'] = $_value['listorder'];
        }
        S('search/type_model',$datas);
        $datas = array();
        foreach($result_datas2 as $_key=>$_value) {
            if($_value['modelid']) continue;
            $datas[$_value['typedir']] = $_value['typeid'];
            $search_model[$_value['typedir']]['typeid'] = $_value['typeid'];
            $search_model[$_value['typedir']]['name'] = $_value['name'];
        }
        S('search/type_application_',$datas);
        //搜索header头中使用类型缓存
        S('search/search_model',$search_model);
        return true;
    }
}