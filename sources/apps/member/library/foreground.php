<?php
/**
 * 会员基类
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-8
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: foreground.php 295 2013-04-02 09:25:22Z 85825770@qq.com $
 */
class foreground {
	public $db, $memberinfo;
	private $_member_modelinfo;
	public $http_user_agent;
	public $uc = null;
	public function __construct() {
		self::check_ip ();
		$this->http_user_agent = str_replace ( '7.0', '8.0', $_SERVER ['HTTP_USER_AGENT'] );
		$this->auth_key = md5 ( C ( 'config', 'auth_key' ) . $this->http_user_agent );
		$this->db = Loader::model ( 'member_model' );
		//ajax验证信息不需要登录
		if(substr(ACTION, 0, 7) != 'public_') {
			self::check_member();
		}
		if (ucenter_exists()) $this->uc = Loader::lib ( 'Ucenter' );
	}

	/**
	 * 判断用户是否已经登陆
	 */
	final public static function check_member() {
		$yuncms_auth = cookie ( 'auth' );
		if (APP == 'member' && CONTROLLER == 'Passport') {
			return true;
		} else {
		//判断是否存在auth cookie
			if ($yuncms_auth) {
				$yuncms_auth = authcode ( $yuncms_auth, 'DECODE', $this->auth_key );
				list ( $userid, $password ) = explode ( "\t", $yuncms_auth );
				//验证用户，获取用户信息
				$this->memberinfo = $this->db->getby_userid($userid);
				//获取用户模型信息
				$this->db->set_model($this->memberinfo['modelid']);

				$this->_member_modelinfo = $this->db->getby_userid($userid);
				$this->_member_modelinfo = $this->_member_modelinfo ? $this->_member_modelinfo : array();
				$this->db->set_model();
				if(is_array($this->memberinfo)) {
					$this->memberinfo = array_merge($this->memberinfo, $this->_member_modelinfo);
				}

				if($this->memberinfo && $this->memberinfo['password'] === $password) {

					if($this->memberinfo['groupid'] == 2) {
						cookie('auth', '');
						cookie('_userid', '');
						cookie('_username', '');
						cookie('_groupid', '');
						showmessage ( L ( 'userid_banned_by_administrator', '', 'member' ), U ( 'member/passport/verify', array ('t' => 1 ) ), 301 );
					} elseif($this->memberinfo['groupid'] == 3) {
						cookie('auth', '');
						cookie('_userid', '');
						cookie('_groupid', '');

						//设置当前登录待验证账号COOKIE，为重发邮件所用
						cookie('_regusername', $this->memberinfo['username']);
						cookie('_reguserid', $this->memberinfo['userid']);
						cookie('_reguseruid', $this->memberinfo['phpssouid']);

						cookie('email', $this->memberinfo['email']);
						showmessage(L('need_emial_authentication', '', 'member'), 'index.php?m=member&c=index&a=register&t=2');
					}
				} else {
					cookie('auth', '');
					cookie('_userid', '');
					cookie('_username', '');
					cookie('_groupid', '');
				}
				unset($userid, $password, $phpcms_auth, $auth_key);
			} else {
				$forward= isset($_GET['forward']) ?  urlencode($_GET['forward']) : urlencode(Core_Request::get_url ());
				showmessage ( L ( 'please_login', '', 'member' ), U ( 'member/passport/login', array ('forward' => $forward ) ), 301 );
			}
		}
	}

	/**
	 * IP禁止判断 ...
	 */
	final private static function check_ip() {
		$ipbanned = Loader::model ( 'ipbanned_model' );
		$ipbanned->check_ip ();
	}
}