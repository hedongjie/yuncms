<?php
defined('IN_YUNCMS') or exit('No permission resources.');
Loader::lib ( 'admin:admin', false );
/**
 * 会员中心菜单管理
 * @author Tongle Xu <xutongle@gmail.com> 2012-7-2
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Member_menuController.php 294 2013-04-02 09:24:57Z 85825770@qq.com $
 */
class Member_menuController extends admin {
    public function __construct() {
        parent::__construct();
        $this->db = Loader::model('member_menu_model');
    }

    public function manage() {
        $tree = Loader::lib('Tree');
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        $userid = $_SESSION['userid'];
        $admin_username = cookie('admin_username');
        $result = $this->db->order('listorder ASC,id DESC')->select();
        foreach($result as $r) {
            $r['cname'] = L($r['name'], '', 'member_menu');
            $r['str_manage'] = '<a href="?app=member&controller=member_menu&action=edit&id='.$r['id'].'&menuid='.$_GET['menuid'].'">'.L('edit').'</a> | <a href=\''.art_confirm(L('confirm',array('message'=>$r['cname'])),'?app=member&controller=member_menu&action=delete&id='.$r['id'].'&menuid='.$_GET['menuid']).'\'>'.L('delete').'</a> ';
            $array[] = $r;
        }
        $str  = "<tr>
        <td align='center'><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input-text-c'></td>
        <td align='center'>\$id</td>
        <td >\$spacer\$cname</td>
        <td align='center'>\$str_manage</td>
        </tr>";
        $tree->init($array);
        $categorys = $tree->get_tree(0, $str);
        include $this->admin_tpl('member_menu');
    }

    public function add() {
        if(isset($_POST['dosubmit'])) {
            $this->db->insert($_POST['info']);
            //开发过程中用于自动创建语言包
            $file = SOURCE_PATH.'languages'.DIRECTORY_SEPARATOR.'zh-cn'.DIRECTORY_SEPARATOR.'member_menu.lang.php';
            if(file_exists($file)) {
                $content = File::read($file);
                $content = substr($content,0,-2);
                $key = $_POST['info']['name'];
                $data = $content."\$LANG['$key'] = '$_POST[language]';\r\n?>";
                File::write($file,$data);
            } else {
                $key = $_POST['info']['name'];
                $data = "<?php\r\n\$LANG['$key'] = '$_POST[language]';\r\n?>";
                File::write($file,$data);
            }
            //结束
            showmessage(L('add_success'));
        } else {
            $show_validator = '';
            $tree = Loader::lib('Tree');
            $result = $this->db->select();
            foreach($result as $r) {
                $r['cname'] = L($r['name'], '', 'member_menu');
                $r['selected'] = $r['id'] == $_GET['parentid'] ? 'selected' : '';
                $array[] = $r;
            }
            $str  = "<option value='\$id' \$selected>\$spacer \$cname</option>";
            $tree->init($array);
            $select_categorys = $tree->get_tree(0, $str);

            include $this->admin_tpl('member_menu');
        }
    }

    public function delete() {
        $_GET['id'] = intval($_GET['id']);
        $this->db->where(array('id'=>$_GET['id']))->delete();
        showmessage(L('operation_success'));
    }

    public function edit() {
        if(isset($_POST['dosubmit'])) {
            $id = intval($_POST['id']);
            $this->db->where(array('id'=>$id))->update($_POST['info']);
            //修改语言文件
            $file = SOURCE_PATH.'languages'.DIRECTORY_SEPARATOR.'zh-cn'.DIRECTORY_SEPARATOR.'member_menu.php';
            require $file;
            $key = $_POST['info']['name'];
            if(!isset($LANG[$key])) {
                $content = File::read($file);
                $content = substr($content,0,-2);
                $data = $content."\$LANG['$key'] = '$_POST[language]';\r\n?>";
                File::write($file,$data);
            } elseif(isset($LANG[$key]) && $LANG[$key]!=$_POST['language']) {
                $content = file_get_contents($file);
                $content = str_replace($LANG[$key],$_POST['language'],$content);
                File::write($file,$data);
            }
            //结束语言文件修改
            showmessage(L('operation_success'));
        } else {
            $show_validator = '';
            $tree = Loader::lib('Tree');
            $id = intval($_GET['id']);
            $r = $this->db->getby_id($id);
            if($r) extract($r);
            $result = $this->db->select();
            foreach($result as $r) {
                $r['cname'] = L($r['name'], '', 'member_menu');
                $r['selected'] = $r['id'] == $parentid ? 'selected' : '';
                $array[] = $r;
            }
            $str  = "<option value='\$id' \$selected>\$spacer \$cname</option>";
            $tree->init($array);
            $select_categorys = $tree->get_tree(0, $str);
            include $this->admin_tpl('member_menu');
        }
    }

    /**
     * 排序
     */
    public function listorder() {
        if(isset($_POST['dosubmit'])) {
            foreach($_POST['listorders'] as $id => $listorder) {
                $this->db->where(array('id'=>$id))->update(array('listorder'=>$listorder));
            }
            showmessage(L('operation_success'));
        } else {
            showmessage(L('operation_failure'));
        }
    }
}