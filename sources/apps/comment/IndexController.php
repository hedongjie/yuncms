<?php
defined('IN_YUNCMS') or exit('No permission resources.');
Loader::helper('comment:global');
/**
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-13
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: IndexController.php 273 2013-04-01 09:30:54Z 85825770@qq.com $
 */
class IndexController {

    protected  $commentid, $applications, $format;

    public function __construct() {
        $this->commentid = isset($_GET['commentid']) && trim(urldecode($_GET['commentid'])) ? trim(urldecode($_GET['commentid'])) : $this->_show_msg(L('illegal_parameters'));
        $this->commentid = safe_replace($this->commentid);
        $this->format = isset($_GET['format']) ? $_GET['format'] : '';
        list($this->applications, $this->contentid) = decode_commentid($this->commentid);
    }

    /**
     * 评论首页和内容页调用
     */
    public function init() {
        $hot = isset($_GET['hot']) && intval($_GET['hot']) ? intval($_GET['hot']) : 0;
        $commentid =& $this->commentid;
        $applications =& $this->applications;
        $contentid =& $this->contentid;
        $username = cookie('_username',L('yuncms_friends'));
        $userid = cookie('_userid');
        $setting = S('common/comment');
        $SEO = seo('', $title);//SEO
        //通过API接口调用数据的标题、URL地址
        if (!$data = get_comment_api($commentid)) $this->_show_msg(L('illegal_parameters'));
        else {
            $title = $data['title'];
            $url = $data['url'];
            if (isset($data['allow_comment']) && empty($data['allow_comment'])) showmessage(L('canot_allow_comment'));
            unset($data);
        }
        if (isset($_GET['js'])) {
            if (strpos($url,SITE_URL) === 0) $domain = SITE_URL;
            else {
                $urls = parse_url($url);
                $domain = $urls['scheme'].'://'.$urls['host'].(isset($urls['port']) && !empty($urls['port']) ? ":".$urls['port'] : '').'/';
            }
            ob_start();
            include template('comment', 'show_list');
            $html = ob_get_contents();
            ob_clean();
            echo format_js($html);
        } else {
            include template('comment', 'list');
        }
    }

    /**
     * 发送评论信息
     */
    public function post() {
        $comment = Loader::lib('comment:comment');
        $id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : '';
        $setting = S('common/common');
        $username = cookie('_username',$setting['site_name'].L('yuncms_friends'));
        $userid = cookie('_userid');
        $setting = S('common/comment');
        if (!empty($setting)) {
            //是否允许游客
            if (!$setting['guest']) {
                if (!$username || !$userid) $this->_show_msg(L('landing_users_to_comment'), HTTP_REFERER);
            }
            if ($setting['code']) {
                Loader::session();
                $code = isset($_POST['code']) && trim($_POST['code']) ? strtolower(trim($_POST['code'])) : $this->_show_msg(L('please_enter_code'), HTTP_REFERER);
                if ($code != $_SESSION['code']) $this->_show_msg(L('code_error'), HTTP_REFERER);
            }
        }
        //通过API接口调用数据的标题、URL地址
        if (!$data = get_comment_api($this->commentid)) $this->_show_msg(L('illegal_parameters'));
        else {
            $title = $data['title'];
            $url = $data['url'];
            unset($data);
        }
        if (strpos($url,SITE_URL) === 0) $domain = SITE_URL;
        else {
            $urls = parse_url($url);
            $domain = $urls['scheme'].'://'.$urls['host'].(isset($urls['port']) && !empty($urls['port']) ? ":".$urls['port'] : '').'/';
        }
        $content = isset($_POST['content']) && trim($_POST['content']) ? trim($_POST['content']) : $this->_show_msg(L('please_enter_content'), HTTP_REFERER);
        $data = array('userid'=>$userid, 'username'=>$username, 'content'=>$content);
        $comment->add($this->commentid, $data, $id, $title, $url);
        $this->_show_msg($comment->get_error()."<iframe width='0' id='top_src' height='0' src='$domain/js.html?200'></iframe>", (in_array($comment->msg_code, array(0,7)) ? HTTP_REFERER : ''), (in_array($comment->msg_code, array(0,7)) ? 1 : 0));
    }

    public function support() {
        $id = isset($_GET['id']) && intval($_GET['id']) ? intval($_GET['id']) : $this->_show_msg(L('illegal_parameters'), HTTP_REFERER);
        if (cookie('comment_'.$id)) $this->_show_msg(L('dragonforce'), HTTP_REFERER);
        $comment = Loader::lib('comment:comment');
        if ($comment->support($this->commentid, $id)) cookie('comment_'.$id, $id, TIME+3600);
        $this->_show_msg($comment->get_error(), ($comment->msg_code == 0 ? HTTP_REFERER : ''), ($comment->msg_code == 0 ? 1 : 0));
    }

    public function ajax() {
        $commentid =& $this->commentid;
        $num = isset($_GET['num']) && intval($_GET['num']) ? intval($_GET['num']) : 20 ;
        $direction = isset($_GET['direction']) && intval($_GET['direction']) ? intval($_GET['direction']) : 0 ;
        $yun_tag = Loader::lib('comment:comment_tag');
        $comment = array();
        if ($comment = $yun_tag->get_comment(array('commentid'=>$commentid))) {
            $page = isset($_GET['page']) && intval($_GET['page']) ? intval($_GET['page']) : 1;
            $offset = ($page-1)*$num;
            $data = array('commentid'=>$commentid, 'limit'=>$offset.','.$num, 'direction'=>$direction);
            $comment['data'] = $yun_tag->lists($data);
            foreach ($comment['data'] as $k=>$v) $comment['data'][$k]['format_time'] = Format::date($v['creat_at'], 1);
            switch ($direction) {
                case '1'://正
                    $total = $comment['square'];
                    break;

                case '2'://反
                    $total = $comment['anti'];
                    break;

                case '3'://中
                    $total = $comment['neutral'];
                    break;

                default:
                    $total = $comment['total'];
                    break;
            }
            $comment['pages'] = Page::pages($total, $page, $num, 'javascript:comment_next_page({$page})');
            if (CHARSET == 'gbk') $comment = array_iconv($comment, 'gbk', 'utf-8');
            echo json_encode($comment);
        } else {
            exit('0');
        }
    }

    /**
     * 提示信息处理
     */
    protected function _show_msg($msg, $url = '', $status = 0) {
        switch ($this->format) {
            case 'json':
                $msg = CHARSET == 'gbk' ? iconv('gbk', 'utf-8', $msg) : $msg;
                echo json_encode(array('msg'=>$msg, 'status'=>$status));
                exit;
                break;
            case 'jsonp':
                $msg = CHARSET == 'gbk' ? iconv('gbk', 'utf-8', $msg) : $msg;
                echo $_GET['callback'].'('.json_encode(array('msg'=>$msg, 'status'=>$status)).')';
                exit;
                break;
            default:
                showmessage($msg, $url);
                break;
        }
    }
}