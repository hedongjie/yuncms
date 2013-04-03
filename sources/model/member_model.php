<?php
/**
 * 会员主表操作模型
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-4
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: member_model.php 292 2013-04-02 09:22:55Z 85825770@qq.com $
 */
if (! defined ( 'CACHE_MODEL_PATH' )) define ( 'CACHE_MODEL_PATH', DATA_PATH . 'model' . DIRECTORY_SEPARATOR );
class member_model extends Model {

	public function __construct() {
		$this->setting = 'default';
		$this->table_name = 'member';
		parent::__construct ();
		// 加载用户模块配置
		$this->member_setting = S ( 'member/member_setting' );
	}

	/**
	 * 获取主表用户信息
	 *
	 * @param string $username 用户名
	 * @param bool $field 字段
	 */
	public function get_user($username = null, $field = 'userid') {
		if (is_null ( $username )) return false;
		$memberinfo = $this->where ( array ($field => $username ) )->find();
		if (! $memberinfo) return false;
		// 获取用户模型信息
		$this->set_model ( $memberinfo ['modelid'] );
		$member_modelinfo = $this->getby_userid ( $memberinfo ['userid'] );
		$this->set_model ();
		if (is_array ( $memberinfo )) {
			$memberinfo = array_merge ( $memberinfo, $member_modelinfo );
		}
		return $memberinfo;
	}

	/**
	 * 注册会员
	 *
	 * @param array $info
	 * @return -1 用户名不合法 -2 用户名包含不允许注册的词语 -3 用户名已存在 -4 E-mail不合法 -5 E-mail不允许注册
	 *         -6 该 Email 已经被注册 大于1注册成功
	 */
	public function register($userinfo) {
		if (ucenter_exists ()) {
			$status = Loader::lib ( 'member:uc_client' )->uc_user_register ( $userinfo ['username'], $userinfo ['password'], $userinfo ['email'], $userinfo ['encrypt'] );
			if ($status < 0) {
				return array ('userid' => $status );
			}
			$userinfo ['ucenterid'] = $status;
		}
		$userinfo ['password'] = password ( $userinfo ['password'], $userinfo ['encrypt'] );
		$this->insert ( $userinfo );
		$return = $this->insert_id ();
		return $return ? $return : '-9999';
	}

	/**
	 * 用户登陆
	 *
	 * @param string $username 用户名或邮箱
	 * @param string $password 密码
	 * @return array userid 大于 0:返回用户 ID，表示用户登录成功 -1:用户不存在，或者被删除 -2:密码错
	 *         -3:安全提问错 -4 用户被锁定
	 */
	public function login($username, $password) {
		$field = strpos ( $username, '@' ) ? 'email' : 'username'; // 判断是否是邮箱
		$res = $this->get_user ( $username, $field );
		if (! $res) {
			return array ('userid' => - 1 ); // 用户不存在
		}
		$pwd = password ( $password, $res ['encrypt'] );
		if ($res ['password'] != $pwd) {
			return array ('userid' => - 2 ); // 密码错误
		}
		$res ['password'] = $pwd;
		if ($res ['islock'] == 1) {
			return array ('userid' => - 4 ); // 用户被锁定
		}

		if (ucenter_exists ()) { // UCenter登录
			$ucuid = Loader::lib ( 'member:uc_client' )->uc_user_login ( $username, $password );
			if ($ucuid < 0) return array ('userid' => $ucuid );
			$res ['synloginstr'] = Loader::lib ( 'member:uc_client' )->uc_user_synlogin ( $ucuid );
		}

		$updatearr = array ('lastip' => IP,'lastdate' => TIME );
		// 检查用户积分，更新新用户组，除去邮箱认证、禁止访问、游客组用户、vip用户
		if ($res ['point'] >= 0 && ! in_array ( $res ['groupid'], array ('1','2','3' ) ) && empty ( $res ['vip'] )) {
			$check_groupid = $this->_get_usergroup_bypoint ( $res ['point'] );
			if ($check_groupid != $res ['groupid']) {
				$updatearr ['groupid'] = $groupid = $check_groupid;
			}
		}
		$this->update ( $updatearr, array ('userid' => $res ['userid'] ) );
		return $res;
	}

	/**
	 * 根据积分算出用户组
	 *
	 * @param $point int 积分数
	 */
	public function _get_usergroup_bypoint($point = 0) {
		$groupid = 4;
		if (empty ( $point )) {
			$member_setting = S ( 'member/setting' );
			$point = isset ( $member_setting ['defualtpoint'] ) && ! empty ( $member_setting ['defualtpoint'] ) ? $member_setting ['defualtpoint'] : 0;
		}
		$grouplist = S ( 'member/grouplist' );
		foreach ( $grouplist as $k => $v ) {
			$grouppointlist [$k] = $v ['point'];
		}
		arsort ( $grouppointlist );
		if ($point > max ( $grouppointlist )) // 如果超出用户组积分设置则为积分最高的用户组
			$groupid = key ( $grouppointlist );
		else {
			foreach ( $grouppointlist as $k => $v ) {
				if ($point >= $v) {
					$groupid = $tmp_k;
					break;
				}
				$tmp_k = $k;
			}
		}
		return $groupid;
	}

	/**
	 * 重置模型操作表表
	 *
	 * @param string $modelid 模型id
	 */
	public function set_model($modelid = '') {
		if ($modelid) {
			$model = S ( 'common/member_model' );
			$this->table_name = $this->prefix . $model [$modelid] ['tablename'];
			$this->fields_bak = $this->fields;
			$this->fields = null;
		} else {
			if (is_null ( $this->fields )) $this->fields = $this->fields_bak;
			$this->table_name = $this->prefix . 'member';
		}
	}

	/**
	 * 锁定会员
	 *
	 * @param array $uidarr
	 */
	public function lock($uidarr) {
		$where = to_sqls ( $uidarr, '', 'userid' );
		return $this->update ( array ('islock' => 1 ), $where );
	}

	/**
	 * 解除锁定会员
	 */
	public function unlock($uidarr) {
		$where = to_sqls ( $uidarr, '', 'userid' );
		return $this->update ( array ('islock' => 0 ), $where );
	}

	/**
	 * 检查用户名是否可用
	 *
	 * @param string $username
	 */
	public function checkname($username) {
		if ($this->get_one ( array ('username' => $username ) )) return false;
		return true;
	}
}