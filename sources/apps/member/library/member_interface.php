<?php
/**
 * 会员接口
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-8
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: member_interface.php 205 2013-03-29 23:26:40Z 85825770@qq.com $
 */
class member_interface {
    // 数据库连接
    private $db;
    public function __construct() {
        $this->db = Loader::model ( 'member_model' );
    }


    /**
     * 获取用户信息
     *
     * @param $username 用户名
     * @param $type {1:用户id;2:用户名;3:email}
     * @return $mix {-1:用户不存在;userinfo:用户信息}
     */
    public function get_member_info($mix, $type = 1) {
        $mix = safe_replace ( $mix );
        if ($type == 1) {
            $userinfo = $this->db->getby_userid ( $mix );
        } elseif ($type == 2) {
            $userinfo = $this->db->getby_username ( $mix );
        } elseif ($type == 3) {
            if (! Validate::is_email ( $mix )) {
                return - 4;
            }
            $userinfo = $this->db->getby_email ( $mix );
        }
        if ($userinfo) {
            return $userinfo;
        } else {
            return - 1;
        }
    }

    /**
     * 将文章加入收藏夹
     *
     * @param int $cid
     *            文章id
     * @param int $userid
     *            会员id
     * @param string $title
     *            文章标题
     * @param $mix {-1:加入失败;$id:加入成功，返回收藏id}
     */
    public function add_favorite($cid, $userid, $title) {
        $cid = intval ( $cid );
        $userid = intval ( $userid );
        $title = safe_replace ( $title );
        $this->favorite_db = Loader::model ( 'favorite_model' );
        $id = $this->favorite_db->insert ( array ('title' => $title,'userid' => $userid,'cid' => $cid,'adddate' => TIME ), 1 );
        if ($id) {
            return $id;
        } else {
            return - 1;
        }
    }

    /**
     * 根据uid增加用户积分
     *
     * @param int $userid
     * @param int $point
     * @return boolean
     */
    public function add_point($userid, $point) {
        $point = intval ( $point );
        return $this->db->where(array ('userid' => $userid ))->update ( array ('point' => "+=$point" ) );
    }
}