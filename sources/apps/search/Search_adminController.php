<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-4
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Search_adminController.php 327 2012-11-12 02:33:10Z xutongle $
 */
defined('IN_YUNCMS') or exit('No permission resources.');
Loader::lib ( 'admin:admin', false );
class Search_adminController extends admin {
    public function __construct() {
        parent::__construct();
        $this->db = Loader::model('search_model');
        $this->application_db = Loader::model('application_model');
        $this->type_db = Loader::model('type_model');
    }

    public function setting() {
        if(isset($_POST['dosubmit'])) {
        	$this->application_db->set_setting('search',$_POST['setting']);
            S('search/search', $_POST['setting']);
            showmessage(L('operation_success'),HTTP_REFERER);
        } else {
            $setting = $this->application_db->get_setting('search');
            @extract($setting);
            $big_menu = big_menu(U('search/search_type/add'), 'add', L('add_search_type'), 580, 240);
            include $this->admin_tpl('setting');
        }
    }

    /**
     * 创建索引
     */
    public function createindex() {
        if(isset($_GET['dosubmit'])) {
            //重建索引首先清空表所有数据，然后根据搜索类型接口重新全部重建索引
            if(!isset($_GET['have_truncate'])) {
                $db_tablepre = $this->db->get_prefix();
                //删除站点全文索引
                $this->db->delete();
                $types = $this->type_db->where(array('application'=>'search'))->select();
                S('search/search_type',$types);
            } else{
                $types = S('search/search_type');
            }
            //$key typeid 的索引
            $key = isset($_GET['key']) ? intval($_GET['key']) : 0;

            foreach ($types as $_k=>$_v) {
                if($key==$_k) {
                    $typeid = $_v['typeid'];
                    if($_v['modelid']) {
                        $search_api = Loader::lib('content:search_api');
                        if(!isset($_GET['total'])) {
                            $total = $search_api->total($_v['modelid']);
                        } else {
                            $total = intval($_GET['total']);
                            $search_api->set_model($_v['modelid']);
                        }
                    } else {
                        $app = trim($_v['typedir']);
                        $search_api = Loader::lib($app.':search_api');
                        if(!isset($_GET['total'])) {
                            $total = $search_api->total();
                        } else {
                            $total = intval($_GET['total']);
                        }
                    }

                    $pagesize = isset($_GET['pagesize']) ? intval($_GET['pagesize']) : 50;
                    $page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
                    $pages = ceil($total/$pagesize);

                    $datas = $search_api->fulltext_api($pagesize,$page);
                    foreach ($datas as $id=>$r) {
                        $this->db->update_search($typeid ,$id, $r['fulltextcontent'],$r['title'],$r['adddate'], 1);
                    }
                    $page++;
                    if($pages>=$page) showmessage("正在更新 <span style='color:#ff0000;font-size:14px;text-decoration:underline;' >{$_v['name']}</span> - 总数：{$total} - 当前第 <font color='red'>{$page}</font> 页","?app=search&controller=search_admin&action=createindex&menuid=153&page={$page}&total={$total}&key={$key}&pagesize={$pagesize}&have_truncate=1&dosubmit=1");
                    $key++;
                    showmessage("开始更新： <span style='color:#ff0000;font-size:14px;text-decoration:underline;' >{$_v['name']}</span> - 总数：{$total}条","?app=search&controller=search_admin&action=createindex&menuid=153&page=1&key={$key}&pagesize={$pagesize}&have_truncate=1&dosubmit=1");

                }
            }
            showmessage('全站索引更新完成',U('search/search_admin/createindex',array('menuid'=>153)));
        } else {
            $big_menu = big_menu(U('search/search_type/add'), 'add', L('add_search_type'), 580, 240);
            include $this->admin_tpl('createindex');
        }
    }

    public function public_test_sphinx() {
        $sphinxhost = !empty($_POST['sphinxhost']) ? $_POST['sphinxhost'] : exit('-1');
        $sphinxport = !empty($_POST['sphinxport']) ? intval($_POST['sphinxport']) : exit('-2');
        $fp = @fsockopen($sphinxhost, $sphinxport, $errno, $errstr , 2);
        if (!$fp) {
            exit($errno.':'.$errstr);
        } else {
            exit('1');
        }
    }
}