<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'member:foreground' );
Loader::helper ( 'member:global' );
Loader::session ();
/**
 * 通行证登陆
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-25
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: PassportController.php 955 2012-06-30 08:32:32Z 85825770@qq.com
 *          $
 */
class PassportController extends foreground {
	private $times_db;
	public function __construct() {
		parent::__construct ();
		$this->member_setting = S ( 'member/member_setting' );
		$this->times_db = Loader::model ( 'times_model' );
	}

	/**
	 * 会员中心首页
	 */
	public function init() {
		showmessage ( '', U ( 'member/index' ), 301 );
	}

	/**
	 * 显示会员注册协议
	 */
	public function protocol() {
		$member_setting = $this->member_setting;
		include template ( 'member', 'protocol' );
	}

	/**
	 * 会员注册
	 */
	public function register() {
		// 加载用户模块配置
		$member_setting = S ( 'member/member_setting' );
		if (! $member_setting ['allowregister']) {
			showmessage ( L ( 'deny_register' ), U ( 'member/passport/login' ) );
		}
		header ( "Cache-control: private" );
		if (isset ( $_POST ['dosubmit'] )) {
			if ($member_setting ['enablcodecheck'] == '1') { // 开启验证码
				if (empty ( $_SESSION ['connectid'] ) && $_SESSION ['code'] != strtolower ( $_POST ['code'] )) {
					showmessage ( L ( 'code_error' ) );
				}
			}

			$userinfo = array ();
			$userinfo ['encrypt'] = create_randomstr ( 6 );

			$userinfo ['username'] = (isset ( $_POST ['username'] ) && is_username ( $_POST ['username'] )) ? $_POST ['username'] : exit ( '0' );
			$userinfo ['nickname'] = (isset ( $_POST ['nickname'] ) && is_username ( $_POST ['nickname'] )) ? $_POST ['nickname'] : '';

			$userinfo ['email'] = (isset ( $_POST ['email'] ) && is_email ( $_POST ['email'] )) ? $_POST ['email'] : exit ( '0' );
			$userinfo ['password'] = isset ( $_POST ['password'] ) ? $_POST ['password'] : exit ( '0' );

			$userinfo ['email'] = (isset ( $_POST ['email'] ) && is_email ( $_POST ['email'] )) ? $_POST ['email'] : exit ( '0' );

			$userinfo ['modelid'] = isset ( $_POST ['modelid'] ) ? intval ( $_POST ['modelid'] ) : 10;
			$userinfo ['regip'] = ip ();
			$userinfo ['point'] = $member_setting ['defualtpoint'] ? $member_setting ['defualtpoint'] : 0;
			$userinfo ['amount'] = $member_setting ['defualtamount'] ? $member_setting ['defualtamount'] : 0;
			$userinfo ['regdate'] = $userinfo ['lastdate'] = TIME;
			$userinfo ['connectid'] = isset ( $_SESSION ['connectid'] ) ? $_SESSION ['connectid'] : '';
			$userinfo ['from'] = isset ( $_SESSION ['from'] ) ? $_SESSION ['from'] : '';
			// 手机强制验证

			if ($member_setting [mobile_checktype] == '1') {
				// 取用户手机号
				$mobile_verify = $_POST ['mobile_verify'] ? intval ( $_POST ['mobile_verify'] ) : '';
				if ($mobile_verify == '') showmessage ( '请提供正确的手机验证码！', HTTP_REFERER );
				$sms_report_db = pc_base::load_model ( 'sms_report_model' );
				$posttime = TIME - 360;
				$where = "`id_code`='$mobile_verify' AND `posttime`>'$posttime'";
				$r = $sms_report_db->get_one ( $where, '*', 'id DESC' );
				if (! empty ( $r )) {
					$userinfo ['mobile'] = $r ['mobile'];
				} else {
					showmessage ( '未检测到正确的手机号码！', HTTP_REFERER );
				}
			} elseif ($member_setting [mobile_checktype] == '2') {
				// 获取验证码，直接通过POST，取mobile值
				$userinfo ['mobile'] = isset ( $_POST ['mobile'] ) ? $_POST ['mobile'] : '';
			}
			if ($userinfo ['mobile'] != "") {
				if (! preg_match ( '/^1([0-9]{9})/', $userinfo ['mobile'] )) {
					showmessage ( '请提供正确的手机号码！', HTTP_REFERER );
				}
			}
			unset ( $_SESSION ['connectid'], $_SESSION ['from'] );

			if ($member_setting ['enablemailcheck']) { // 是否需要邮件验证
				$userinfo ['groupid'] = 7;
			} elseif ($member_setting ['registerverify']) { // 是否需要管理员审核
				$userinfo ['modelinfo'] = isset ( $_POST ['info'] ) ? array2string ( $_POST ['info'] ) : '';
				$this->verify_db = Loader::model ( 'member_verify_model' );
				unset ( $userinfo ['lastdate'], $userinfo ['connectid'], $userinfo ['from'] );
				$userinfo = array_map ( 'htmlspecialchars', $userinfo );
				$this->verify_db->insert ( $userinfo );
				showmessage ( L ( 'operation_success' ), 'index.php?m=member&c=index&a=register&t=3' );
			} else {
				// 查看当前模型是否开启了短信验证功能
				$model_field_cache = getcache ( 'model_field_' . $userinfo ['modelid'], 'model' );
				if (isset ( $model_field_cache ['mobile'] ) && $model_field_cache ['mobile'] ['disabled'] == 0) {
					$mobile = $_POST ['info'] ['mobile'];
					if (! preg_match ( '/^1([0-9]{10})/', $mobile )) showmessage ( L ( 'input_right_mobile' ) );
					$sms_report_db = pc_base::load_model ( 'sms_report_model' );
					$posttime = SYS_TIME - 300;
					$where = "`mobile`='$mobile' AND `posttime`>'$posttime'";
					$r = $sms_report_db->get_one ( $where );
					if (! $r || $r ['id_code'] != $_POST ['mobile_verify']) showmessage ( L ( 'error_sms_code' ) );
				}
				$userinfo ['groupid'] = $this->_get_usergroup_bypoint ( $userinfo ['point'] );
			}

			if (pc_base::load_config ( 'system', 'phpsso' )) {
				$this->_init_phpsso ();
				$status = $this->client->ps_member_register ( $userinfo ['username'], $userinfo ['password'], $userinfo ['email'], $userinfo ['regip'], $userinfo ['encrypt'] );
				if ($status > 0) {
					$userinfo ['phpssouid'] = $status;
					// 传入phpsso为明文密码，加密后存入phpcms_v9
					$password = $userinfo ['password'];
					$userinfo ['password'] = password ( $userinfo ['password'], $userinfo ['encrypt'] );
					$userid = $this->db->insert ( $userinfo, 1 );
					if ($member_setting ['choosemodel']) { // 如果开启选择模型
					                                     // 通过模型获取会员信息
						require_once CACHE_MODEL_PATH . 'member_input.class.php';
						require_once CACHE_MODEL_PATH . 'member_update.class.php';
						$member_input = new member_input ( $userinfo ['modelid'] );

						$_POST ['info'] = array_map ( 'htmlspecialchars', $_POST ['info'] );
						$user_model_info = $member_input->get ( $_POST ['info'] );
						$user_model_info ['userid'] = $userid;

						// 插入会员模型数据
						$this->db->set_model ( $userinfo ['modelid'] );
						$this->db->insert ( $user_model_info );
					}

					if ($userid > 0) {
						// 执行登陆操作
						if (! $cookietime) $get_cookietime = param::get_cookie ( 'cookietime' );
						$_cookietime = $cookietime ? intval ( $cookietime ) : ($get_cookietime ? $get_cookietime : 0);
						$cookietime = $_cookietime ? TIME + $_cookietime : 0;

						if ($userinfo ['groupid'] == 7) {
							cookie ( '_username', $userinfo ['username'], $cookietime );
							cookie ( 'email', $userinfo ['email'], $cookietime );
						} else {
							$phpcms_auth_key = md5 ( pc_base::load_config ( 'system', 'auth_key' ) . $this->http_user_agent );
							$phpcms_auth = sys_auth ( $userid . "\t" . $userinfo ['password'], 'ENCODE', $phpcms_auth_key );

							cookie ( 'auth', $phpcms_auth, $cookietime );
							cookie ( '_userid', $userid, $cookietime );
							cookie ( '_username', $userinfo ['username'], $cookietime );
							cookie ( '_nickname', $userinfo ['nickname'], $cookietime );
							cookie ( '_groupid', $userinfo ['groupid'], $cookietime );
							cookie ( 'cookietime', $_cookietime, $cookietime );
						}
					}
					// 如果需要邮箱认证
					if ($member_setting ['enablemailcheck']) {
						$phpcms_auth_key = md5 ( pc_base::load_config ( 'system', 'auth_key' ) );
						$code = sys_auth ( $userid . '|' . $phpcms_auth_key, 'ENCODE', $phpcms_auth_key );
						$url = APP_PATH . "index.php?m=member&c=index&a=register&code=$code&verify=1";
						$message = $member_setting ['registerverifymessage'];
						$message = str_replace ( array ('{click}','{url}','{username}','{email}','{password}' ), array ('<a href="' . $url . '">' . L ( 'please_click' ) . '</a>',$url,$userinfo ['username'],$userinfo ['email'],$password ), $message );
						sendmail ( $userinfo ['email'], L ( 'reg_verify_email' ), $message );
						// 设置当前注册账号COOKIE，为第二步重发邮件所用
						cookie ( '_regusername', $userinfo ['username'], $cookietime );
						cookie ( '_reguserid', $userid, $cookietime );
						cookie ( '_reguseruid', $userinfo ['phpssouid'], $cookietime );
						showmessage ( L ( 'operation_success' ), 'index.php?m=member&c=index&a=register&t=2' );
					} else {
						// 如果不需要邮箱认证、直接登录其他应用
						$synloginstr = $this->client->ps_member_synlogin ( $userinfo ['phpssouid'] );
						showmessage ( L ( 'operation_success' ) . $synloginstr, 'index.php?m=member&c=index&a=init' );
					}
				}
			} else {
				showmessage ( L ( 'enable_register' ) . L ( 'enable_phpsso' ), 'index.php?m=member&c=index&a=login' );
			}
			showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
		} else {
			if (! empty ( $_GET ['verify'] )) {
				$code = isset ( $_GET ['code'] ) ? trim ( $_GET ['code'] ) : showmessage ( L ( 'operation_failure' ), 'index.php?m=member&c=index' );
				$phpcms_auth_key = md5 ( pc_base::load_config ( 'system', 'auth_key' ) );
				$code_res = sys_auth ( $code, 'DECODE', $phpcms_auth_key );
				$code_arr = explode ( '|', $code_res );
				$userid = isset ( $code_arr [0] ) ? $code_arr [0] : '';
				$userid = is_numeric ( $userid ) ? $userid : showmessage ( L ( 'operation_failure' ), 'index.php?m=member&c=index' );

				$this->db->update ( array ('groupid' => $this->_get_usergroup_bypoint () ), array ('userid' => $userid ) );
				showmessage ( L ( 'operation_success' ), 'index.php?m=member&c=index' );
			} else {
				// 过滤非当前站点会员模型
				$modellist = S ( 'common/member_model' );
				foreach ( $modellist as $k => $v ) {
					if ($v ['disabled']) unset ( $modellist [$k] );
				}
				if (empty ( $modellist )) {
					showmessage ( L ( 'site_have_no_model' ) . L ( 'deny_register' ), HTTP_REFERER );
				}
				// 是否开启选择会员模型选项
				if ($member_setting ['choosemodel']) {
					$first_model = array_pop ( array_reverse ( $modellist ) );
					$modelid = isset ( $_GET ['modelid'] ) && in_array ( $_GET ['modelid'], array_keys ( $modellist ) ) ? intval ( $_GET ['modelid'] ) : $first_model ['modelid'];
					if (array_key_exists ( $modelid, $modellist )) {
						// 获取会员模型表单
						require CACHE_MODEL_PATH . 'member_form.php';
						$member_form = new member_form ( $modelid );
						$this->db->set_model ( $modelid );
						$forminfos = $forminfos_arr = $member_form->get ();
						// 万能字段过滤
						foreach ( $forminfos as $field => $info ) {
							if ($info ['isomnipotent']) {
								unset ( $forminfos [$field] );
							} else {
								if ($info ['formtype'] == 'omnipotent') {
									foreach ( $forminfos_arr as $_fm => $_fm_value ) {
										if ($_fm_value ['isomnipotent']) {
											$info ['form'] = str_replace ( '{' . $_fm . '}', $_fm_value ['form'], $info ['form'] );
										}
									}
									$forminfos [$field] ['form'] = $info ['form'];
								}
							}
						}

						$formValidator = $member_form->formValidator;
					}
				}
				$description = $modellist [$modelid] ['description'];

				include template ( 'member', 'register' );
			}
		}
	}

	/**
	 * 等待Email验证或审核
	 */
	public function verify() {
		if (! empty ( $_GET ['verify'] )) {
			$code = isset ( $_GET ['code'] ) ? trim ( $_GET ['code'] ) : showmessage ( L ( 'operation_failure' ), 'index.php?app=member&controller=index' );
			$yuncms_auth_key = md5 ( C ( 'config', 'auth_key' ) . $this->http_user_agent );
			$userid = authcode ( $code, 'DECODE', $yuncms_auth_key );
			$userid = is_numeric ( $userid ) ? $userid : showmessage ( L ( 'operation_failure' ), 'index.php?app=member&controller=index' );
			$this->db->update ( array ('groupid' => $this->db->_get_usergroup_bypoint () ), array ('userid' => $userid ) );
			showmessage ( L ( 'operation_success' ), 'index.php?app=member&controller=index' );
		} else {
			include template ( 'member', 'verify' );
		}
	}

	/**
	 * 用户登录
	 */
	public function login() {
		if (isset ( $_POST ['dosubmit'] )) {
			if (isset ( $_SESSION ['pwderror'] )) {
				$checkcode = isset ( $_POST ['code'] ) && trim ( $_POST ['code'] ) ? trim ( $_POST ['code'] ) : showmessage ( L ( 'input_code' ), HTTP_REFERER );
				if (! checkcode ( $checkcode )) { // 判断验证码
					showmessage ( L ( 'code_error' ), HTTP_REFERER );
				}
			}
			$username = isset ( $_POST ['username'] ) && trim ( $_POST ['username'] ) ? trim ( $_POST ['username'] ) : showmessage ( L ( 'username_empty' ), HTTP_REFERER );
			$password = isset ( $_POST ['password'] ) && trim ( $_POST ['password'] ) ? trim ( $_POST ['password'] ) : showmessage ( L ( 'password_empty' ), HTTP_REFERER );
			$_cookietime = isset ( $_POST ['auto_login'] ) && intval ( $_POST ['auto_login'] ) ? intval ( $_POST ['auto_login'] ) : (cookie ( 'cookietime' ) ? cookie ( 'cookietime' ) : 0);
			$rtime = $this->times_db->get_one ( array ('username' => $username ) );
			if ($rtime && $rtime ['times'] > 4) { // 密码错误剩余重试次数
				$minute = 60 - floor ( (TIME - $rtime ['logintime']) / 60 );
				showmessage ( L ( 'wait_1_hour', array ('minute' => $minute ) ) );
			}
			$res = $this->db->login ( $username, $password ); // 登陆
			if ($res ['userid'] == - 1) { // 用户不存在
				showmessage ( L ( 'user_not_exist' ), U ( 'member/passport/login' ) );
			} else if ($res ['userid'] == - 2) { // 密码错误
				$ip = IP;
				if ($rtime && $rtime ['times'] < 5) {
					$times = 5 - intval ( $rtime ['times'] );
					$this->times_db->update ( array ('ip' => $ip,'times' => '+=1' ), array ('username' => $username ) );
				} else {
					$this->times_db->insert ( array ('username' => $username,'ip' => $ip,'logintime' => TIME,'times' => 1 ) );
					$times = 5;
				}
				$_SESSION ['pwderror'] = true;
				showmessage ( L ( 'password_error', array ('times' => $times ) ), U ( 'member/passport/login' ), 3000 );
			} else if ($res ['userid'] == - 4) { // 帐户被禁用
				showmessage ( L ( 'user_is_lock' ) );
			}
			$this->times_db->delete ( array ('username' => $username ) );
			if (isset ( $_SESSION ['pwderror'] )) unset ( $_SESSION ['pwderror'] );
			$this->set_user ( $res ['userid'], $_cookietime );
			$forward = isset ( $_POST ['forward'] ) && ! empty ( $_POST ['forward'] ) ? urldecode ( $_POST ['forward'] ) : U ( 'member/index' );
			showmessage ( L ( 'login_success' ), $forward );
		} else {
			$setting = C ( 'system' );
			$forward = isset ( $_GET ['forward'] ) && trim ( $_GET ['forward'] ) ? $_GET ['forward'] : '';
			$siteinfo = S ( 'common/common' );
			include template ( 'member', 'login' );
		}
	}

	/**
	 * 用户退出
	 */
	public function logout() {
		$synlogoutstr = '';
		if (ucenter_exists ()) $synlogoutstr = Loader::lib ( 'Ucenter' )->uc_user_synlogout ();
		$this->reset_user (); // 重置用户状态
		$forward = isset ( $_GET ['forward'] ) && trim ( $_GET ['forward'] ) ? $_GET ['forward'] : U ( 'member/passport/login' );
		showmessage ( L ( 'logout_success' ) . $synlogoutstr, $forward, 301 );
	}

	/**
	 * 找回密码
	 */
	public function public_forget_password() {
		if (isset ( $_POST ['dosubmit'] )) {
			$checkcode = isset ( $_POST ['code'] ) && trim ( $_POST ['code'] ) ? trim ( $_POST ['code'] ) : showmessage ( L ( 'input_code' ), HTTP_REFERER );
			if (! checkcode ( $checkcode )) { // 判断验证码
				showmessage ( L ( 'code_error' ), HTTP_REFERER );
			}
			$memberinfo = $this->db->get_one ( array ('email' => $_POST ['email'] ) );
			if (! empty ( $memberinfo ['email'] )) {
				$email = $memberinfo ['email'];
			} else {
				showmessage ( L ( 'email_error' ), HTTP_REFERER );
			}
			$code = authcode ( $memberinfo ['userid'] . "\t" . TIME, 'ENCODE', $this->auth_key );
			$url = SITE_URL . "index.php?app=member&controller=passport&action=public_forget_password&code=$code";
			$message = $this->member_setting ['forgetpassword'];
			$message = str_replace ( array ('{click}','{url}' ), array ('<a href="' . $url . '">' . L ( 'please_click' ) . '</a>',$url ), $message );
			sendmail ( $email, L ( 'forgetpassword' ), $message );
			showmessage ( L ( 'operation_success' ), 'index.php?app=member&controller=passport&action=login' );
		} elseif (isset ( $_GET ['code'] )) {
			$hour = date ( 'y-m-d h', TIME );
			$code = authcode ( $_GET ['code'], 'DECODE', $this->auth_key );
			$code = explode ( "\t", $code );
			if (is_array ( $code ) && is_numeric ( $code [0] ) && date ( 'y-m-d h', TIME ) == date ( 'y-m-d h', $code [1] )) {
				$memberinfo = $this->db->get_one ( array ('userid' => $code [0] ) );
				$password = random ( 8 );
				$updateinfo ['password'] = password ( $password, $memberinfo ['encrypt'] );
				$this->db->update ( $updateinfo, array ('userid' => $code [0] ) );
				if ($this->uc && ! empty ( $memberinfo ['ucenterid'] )) {
					Loader::lib ( 'member:uc_client' )->uc_user_edit ( $memberinfo ['username'], '', $password, '', $memberinfo ['encrypt'], 1 );
				}
				showmessage ( L ( 'operation_success' ) . L ( 'newpassword' ) . ':' . $password );
			} else {
				showmessage ( L ( 'operation_failure' ), 'index.php?app=member&controller=passport&action=login' );
			}
		} else {
			$siteinfo = S ( 'common/common' );
			include template ( 'member', 'forget_password' );
		}
	}

	public function mini() {
		$_username = cookie ( '_username' );
		$_userid = cookie ( '_userid' );
		ob_start ();
		include template ( 'member', 'mini' );
		$html = ob_get_contents ();
		ob_clean ();
		echo format_js ( $html );
	}

	/**
	 * 用QQ账户登录
	 */
	public function public_qq_login() {
		$connector = new Connector ();
		$connector->connect ( 'QQ' );
		exit ();
		define ( 'APP_ID', C ( 'open_platform', 'Tencent_QQ_App_Id' ) );
		define ( 'APP_KEY', C ( 'open_platform', 'Tencent_QQ_App_Key' ) );
		Core::load_core_class ( 'qq', CORE_PATH . 'class' . DS . 'opensdk' . DS . 'qq', 0 );
		Core::session_start ();
		$sdk = new qq ( APP_ID, APP_KEY );
		if (isset ( $_GET ['callback'] ) && trim ( $_GET ['callback'] )) {
			$access_token = $sdk->getAccessToken ( $_REQUEST ["oauth_token"], $_SESSION ["secret"], $_REQUEST ["oauth_vericode"] );
			$me = $sdk->get_user_info ( $access_token ["oauth_token"], $access_token ["oauth_token_secret"], $access_token ["openid"] );
			if (CHARSET != 'utf-8') {
				$me ['nickname'] = iconv ( 'utf-8', CHARSET, $me ['nickname'] );
			}
			if (! empty ( $access_token ["openid"] )) {
				// 检查connect会员是否绑定，已绑定直接登录，未绑定提示注册/绑定页面
				$member_bind = Loader::model ( 'member_bind_model' )->get_one ( array ('connectid' => $access_token ["openid"],'form' => 'sina' ) );
				if (! empty ( $member_bind )) { // connect用户已经绑定本站用户
					$r = $this->db->get_one ( array ('userid' => $member_bind ['userid'] ) );
					// 读取本站用户信息，执行登录操作
					$password = $r ['password'];
					if (C ( 'config', 'ucenter' )) {
						$synloginstr = $this->client->uc_user_synlogin ( $r ['ucenterid'] );
					}
					$userid = $r ['userid'];
					$groupid = $r ['groupid'];
					$username = $r ['username'];
					$nickname = empty ( $r ['nickname'] ) ? $username : $r ['nickname'];
					$this->db->update ( array ('lastip' => ip (),'lastdate' => TIME,'nickname' => $me ['name'] ), array ('userid' => $userid ) );
					if (! $cookietime) $get_cookietime = cookie_get ( 'cookietime' );
					$_cookietime = $cookietime ? intval ( $cookietime ) : ($get_cookietime ? $get_cookietime : 0);
					$cookietime = $_cookietime ? TIME + $_cookietime : 0;
					$yuncms_auth_key = md5 ( C ( 'config', 'auth_key' ) . $this->http_user_agent );
					$yuncms_auth = authcode ( $userid . "\t" . $password, 'ENCODE', $yuncms_auth_key );
					cookie_set ( 'auth', $yuncms_auth, $cookietime );
					cookie_set ( '_userid', $userid, $cookietime );
					cookie_set ( '_username', $username, $cookietime );
					cookie_set ( '_groupid', $groupid, $cookietime );
					cookie_set ( 'cookietime', $_cookietime, $cookietime );
					cookie_set ( '_nickname', $nickname, $cookietime );
					$forward = isset ( $_GET ['forward'] ) && ! empty ( $_GET ['forward'] ) ? $_GET ['forward'] : 'index.php?app=member&controller=index';
					showmessage ( L ( 'login_success' ) . $synloginstr, $forward );
				} else {
					// $sdk->add_feeds($access_token["oauth_token"],
					// $access_token["oauth_token_secret"],
					// $access_token["openid"]);
					unset ( $_SESSION ["secret"] );
					// 弹出绑定注册页面
					$_SESSION ['connectid'] = $access_token ["openid"];
					$_SESSION ['token'] = $access_token ["oauth_token"];
					$_SESSION ['token_secret'] = $access_token ["oauth_token_secret"];
					$connect_username = $me ['nickname'];
					$connect_nick = $me ['nickname'];
					cookie_set ( 'open_name', $me ['nickname'] );
					cookie_set ( 'open_from', 'qq' );
					if (isset ( $_GET ['bind'] )) showmessage ( L ( 'bind_success' ), 'index.php?app=member&controller=account&action=bind&t=1' );
					include template ( 'member', 'connect' );
				}
			} else {
				unset ( $_SESSION ["secret"] );
				showmessage ( L ( 'login_failure' ), 'index.php?app=member&controller=passport&action=login' );
			}
		} else {
			$request_token = $sdk->getRequestToken ();
			$_SESSION ["secret"] = $request_token ["oauth_token_secret"];
			$bind = isset ( $_GET ['bind'] ) && trim ( $_GET ['bind'] ) ? '&bind=' . trim ( $_GET ['bind'] ) : '';
			$url = $sdk->getAuthorizeURL ( $request_token ['oauth_token'], SITE_URL . 'index.php?app=member&controller=passport&action=public_qq_login&callback=1' . $bind );
			Header ( "HTTP/1.1 301 Moved Permanently" );
			Header ( "Location: $url" );
		}
	}

	/**
	 * 人人账户登录
	 */
	public function public_renren_login() {
		define ( 'APP_KEY', Core::load_config ( 'open_platform', 'Renren_App_Key' ) );
		define ( 'APP_SECRET', Core::load_config ( 'open_platform', 'Renren_App_Secret' ) );
		Core::load_core_class ( 'renren', CORE_PATH . 'class' . DS . 'opensdk' . DS . 'renren', 0 );
		Core::session_start ();
		$connection = new renren ( APP_KEY, APP_SECRET );
		if (isset ( $_GET ['callback'] ) && trim ( $_GET ['callback'] )) {
			if (cookie_get ( 'open_bind' )) {
				$bind = '&bind=1';
				cookie_set ( 'open_bind', '' );
			}
			$access_token = $connection->getAccessToken ( urlencode ( SITE_URL . 'index.php?app=member&controller=passport&action=public_renren_login&callback=1' . $bind ), $_GET ['code'] );
			$uinfo = $access_token->user;
			if ($uinfo) {
				// 检查connect会员是否绑定，已绑定直接登录，未绑定提示注册/绑定页面
				$member_bind = Loader::model ( 'member_bind_model' )->get_one ( array ('connectid' => $uinfo->id,'form' => 'renren' ) );
				if (! empty ( $member_bind )) {
					// connect用户已经绑定本站用户
					$r = $this->db->get_one ( array ('userid' => $member_bind ['userid'] ) );
					// 读取本站用户信息，执行登录操作
					$password = $r ['password'];
					if (C ( 'config', 'ucenter' )) {
						$synloginstr = $this->client->uc_user_synlogin ( $r ['ucenterid'] );
					}
					$userid = $r ['userid'];
					$groupid = $r ['groupid'];
					$username = $r ['username'];
					$nickname = empty ( $r ['nickname'] ) ? $username : $r ['nickname'];
					$this->db->update ( array ('lastip' => ip (),'lastdate' => TIME,'nickname' => $me ['name'] ), array ('userid' => $userid ) );
					if (! $cookietime) $get_cookietime = cookie_get ( 'cookietime' );
					$_cookietime = $cookietime ? intval ( $cookietime ) : ($get_cookietime ? $get_cookietime : 0);
					$cookietime = $_cookietime ? TIME + $_cookietime : 0;
					$yuncms_auth_key = md5 ( C ( 'config', 'auth_key' ) . $this->http_user_agent );
					$yuncms_auth = authcode ( $userid . "\t" . $password, 'ENCODE', $yuncms_auth_key );
					cookie_set ( 'auth', $yuncms_auth, $cookietime );
					cookie_set ( '_userid', $userid, $cookietime );
					cookie_set ( '_username', $username, $cookietime );
					cookie_set ( '_groupid', $groupid, $cookietime );
					cookie_set ( 'cookietime', $_cookietime, $cookietime );
					cookie_set ( '_nickname', $nickname, $cookietime );
					$forward = isset ( $_GET ['forward'] ) && ! empty ( $_GET ['forward'] ) ? $_GET ['forward'] : 'index.php?app=member&controller=index';
					showmessage ( L ( 'login_success' ) . $synloginstr, $forward );
				} else {
					// 弹出绑定注册页面
					$_SESSION ['connectid'] = $uinfo->id;
					$_SESSION ['token'] = $access_token->access_token;
					$connect_username = $uinfo->name;
					$connect_nick = $uinfo->name;
					cookie_set ( 'open_name', $uinfo->name );
					cookie_set ( 'open_from', 'renren' );
					if (isset ( $_GET ['bind'] )) showmessage ( L ( 'bind_success' ), 'index.php?app=member&controller=account&action=bind&t=1' );
					include template ( 'member', 'connect' );
				}
			} else {
				showmessage ( L ( 'login_failure' ), 'index.php?app=member&controller=passport&action=login' );
			}
		} else {
			cookie_set ( 'open_bind', '1' );
			$bind = isset ( $_GET ['bind'] ) && trim ( $_GET ['bind'] ) ? '&bind=' . trim ( $_GET ['bind'] ) : '';
			$url = $connection->getRequestToken ( urlencode ( SITE_URL . 'index.php?app=member&controller=passport&action=public_renren_login&callback=1' . $bind ) );
			Header ( "HTTP/1.1 301 Moved Permanently" );
			Header ( "Location: $url" );
		}
	}

	/**
	 * 百度账户登录
	 */
	public function public_baidu_login() {
		define ( 'APP_KEY', Core::load_config ( 'open_platform', 'Baidu_App_Key' ) );
		define ( 'APP_SECRET', Core::load_config ( 'open_platform', 'Baidu_App_Secret' ) );
		Core::load_core_class ( 'baidu', CORE_PATH . 'class' . DS . 'opensdk' . DS . 'baidu', 0 );
		Core::session_start ();
		if (isset ( $_GET ['callback'] ) && trim ( $_GET ['callback'] )) {
			$baidu = new Baidu ( APP_KEY, APP_SECRET, new BaiduCookieStore ( APP_KEY ) );
			$access_token = $baidu->getAccessToken ();
			$uinfo = $baidu->api ( 'passport/users/getInfo', array ('fields' => 'userid,username,sex,birthday' ) );
			if ($uinfo) {
				// 检查connect会员是否绑定，已绑定直接登录，未绑定提示注册/绑定页面
				$member_bind = Loader::model ( 'member_bind_model' )->get_one ( array ('connectid' => $uinfo ['userid'],'form' => 'baidu' ) );
				if (! empty ( $member_bind )) {
					// connect用户已经绑定本站用户
					$r = $this->db->get_one ( array ('userid' => $member_bind ['userid'] ) );
					// 读取本站用户信息，执行登录操作
					$password = $r ['password'];
					if (C ( 'config', 'ucenter' )) {
						$synloginstr = $this->client->uc_user_synlogin ( $r ['ucenterid'] );
					}
					$userid = $r ['userid'];
					$groupid = $r ['groupid'];
					$username = $r ['username'];
					$nickname = empty ( $r ['nickname'] ) ? $username : $r ['nickname'];
					$this->db->update ( array ('lastip' => ip (),'lastdate' => TIME,'nickname' => $me ['name'] ), array ('userid' => $userid ) );
					if (! $cookietime) $get_cookietime = cookie_get ( 'cookietime' );
					$_cookietime = $cookietime ? intval ( $cookietime ) : ($get_cookietime ? $get_cookietime : 0);
					$cookietime = $_cookietime ? TIME + $_cookietime : 0;
					$yuncms_auth_key = md5 ( C ( 'config', 'auth_key' ) . $this->http_user_agent );
					$yuncms_auth = authcode ( $userid . "\t" . $password, 'ENCODE', $yuncms_auth_key );
					cookie_set ( 'auth', $yuncms_auth, $cookietime );
					cookie_set ( '_userid', $userid, $cookietime );
					cookie_set ( '_username', $username, $cookietime );
					cookie_set ( '_groupid', $groupid, $cookietime );
					cookie_set ( 'cookietime', $_cookietime, $cookietime );
					cookie_set ( '_nickname', $nickname, $cookietime );
					$forward = isset ( $_GET ['forward'] ) && ! empty ( $_GET ['forward'] ) ? $_GET ['forward'] : 'index.php?app=member&controller=index';
					showmessage ( L ( 'login_success' ) . $synloginstr, $forward );
				} else {
					// 弹出绑定注册页面
					$_SESSION ['connectid'] = $uinfo ['userid'];
					$_SESSION ['token'] = '';
					$_SESSION ['token_secret'] = '';
					$connect_username = $uinfo ['username'];
					$connect_nick = $uinfo ['username'];
					cookie_set ( 'open_name', $uinfo ['username'] );
					cookie_set ( 'open_from', 'baidu' );
					if (isset ( $_GET ['bind'] )) showmessage ( L ( 'bind_success' ), 'index.php?app=member&controller=account&action=bind&t=1' );
					include template ( 'member', 'connect' );
				}
			} else {
				showmessage ( L ( 'login_failure' ), 'index.php?app=member&controller=passport&action=login' );
			}
		} else {
			/* 创建OAuth对象 */
			$oauth = new Baidu ( APP_KEY, APP_SECRET, new BaiduCookieStore ( APP_KEY ) );
			$bind = isset ( $_GET ['bind'] ) && trim ( $_GET ['bind'] ) ? '&bind=' . trim ( $_GET ['bind'] ) : '';
			$url = $oauth->getLoginUrl ( array ('response_type' => 'code','redirect_uri' => SITE_URL . 'index.php?app=member&controller=passport&action=public_baidu_login&callback=1' . $bind ) );
			Header ( "HTTP/1.1 301 Moved Permanently" );
			Header ( "Location: $url" );
		}
	}

	/**
	 * 腾讯微博登录
	 */
	public function public_tencent_login() {
		define ( 'APP_KEY', Core::load_config ( 'open_platform', 'Tencent_Weibo_App_Key' ) );
		define ( 'APP_SECRET', Core::load_config ( 'open_platform', 'Tencent_Weibo_App_Secret' ) );
		Core::load_core_class ( 'weibo', CORE_PATH . 'class' . DS . 'opensdk' . DS . 'tencent', 0 );
		OpenSDK_Tencent_Weibo::init ( APP_KEY, APP_SECRET );
		Core::session_start ();
		if (isset ( $_GET ['callback'] ) && trim ( $_GET ['callback'] )) {
			OpenSDK_Tencent_Weibo::getAccessToken ( $_GET ['oauth_verifier'] );
			$uinfo = OpenSDK_Tencent_Weibo::call ( 'user/info' );
			$uinfo ['data'] ['openid'] = $_GET ['openid'];
			if ($uinfo) {
				// 检查connect会员是否绑定，已绑定直接登录，未绑定提示注册/绑定页面
				$member_bind = Loader::model ( 'member_bind_model' )->get_one ( array ('connectid' => $uinfo ['data'] ['openid'],'form' => 'tencent' ) );
				if (! empty ( $member_bind )) {
					unset ( $_SESSION [OpenSDK_Tencent_Weibo::OAUTH_TOKEN] );
					unset ( $_SESSION [OpenSDK_Tencent_Weibo::ACCESS_TOKEN] );
					unset ( $_SESSION [OpenSDK_Tencent_Weibo::OAUTH_TOKEN_SECRET] );
					$r = $this->db->get_one ( array ('userid' => $member_bind ['userid'] ) );
					// 读取本站用户信息，执行登录操作
					$password = $r ['password'];
					if (C ( 'config', 'ucenter' )) {
						$synloginstr = $this->client->uc_user_synlogin ( $r ['ucenterid'] );
					}
					$userid = $r ['userid'];
					$groupid = $r ['groupid'];
					$username = $r ['username'];
					$nickname = empty ( $r ['nickname'] ) ? $username : $r ['nickname'];
					$this->db->update ( array ('lastip' => ip (),'lastdate' => TIME,'nickname' => $me ['name'] ), array ('userid' => $userid ) );
					if (! $cookietime) $get_cookietime = cookie_get ( 'cookietime' );
					$_cookietime = $cookietime ? intval ( $cookietime ) : ($get_cookietime ? $get_cookietime : 0);
					$cookietime = $_cookietime ? TIME + $_cookietime : 0;
					$yuncms_auth_key = md5 ( C ( 'config', 'auth_key' ) . $this->http_user_agent );
					$yuncms_auth = authcode ( $userid . "\t" . $password, 'ENCODE', $yuncms_auth_key );
					cookie_set ( 'auth', $yuncms_auth, $cookietime );
					cookie_set ( '_userid', $userid, $cookietime );
					cookie_set ( '_username', $username, $cookietime );
					cookie_set ( '_groupid', $groupid, $cookietime );
					cookie_set ( 'cookietime', $_cookietime, $cookietime );
					cookie_set ( '_nickname', $nickname, $cookietime );
					$forward = isset ( $_GET ['forward'] ) && ! empty ( $_GET ['forward'] ) ? $_GET ['forward'] : 'index.php?app=member&controller=index';
					showmessage ( L ( 'login_success' ) . $synloginstr, $forward );
				} else {
					OpenSDK_Tencent_Weibo::call ( 'friends/add', array ('name' => 'newsteng' ), 'POST' );
					// 弹出绑定注册页面

					$_SESSION ['connectid'] = $uinfo ['data'] ['openid'];
					$_SESSION ['token'] = $_SESSION [OpenSDK_Tencent_Weibo::ACCESS_TOKEN];
					$_SESSION ['token_secret'] = $_SESSION [OpenSDK_Tencent_Weibo::OAUTH_TOKEN_SECRET];
					$connect_username = $uinfo ['data'] ['name'];
					$connect_nick = $uinfo ['data'] ['nick'];
					$connect_email = $uinfo ['data'] ['email'];
					unset ( $_SESSION [OpenSDK_Tencent_Weibo::OAUTH_TOKEN] );
					unset ( $_SESSION [OpenSDK_Tencent_Weibo::ACCESS_TOKEN] );
					unset ( $_SESSION [OpenSDK_Tencent_Weibo::OAUTH_TOKEN_SECRET] );
					cookie_set ( 'open_name', $uinfo ['data'] ['name'] );
					cookie_set ( 'open_from', 'tencent' );
					if (isset ( $_GET ['bind'] )) showmessage ( L ( 'bind_success' ), 'index.php?app=member&controller=account&action=bind&t=1' );
					include template ( 'member', 'connect' );
				}
			} else {
				unset ( $_SESSION [OpenSDK_Tencent_Weibo::OAUTH_TOKEN] );
				unset ( $_SESSION [OpenSDK_Tencent_Weibo::ACCESS_TOKEN] );
				unset ( $_SESSION [OpenSDK_Tencent_Weibo::OAUTH_TOKEN_SECRET] );
				showmessage ( L ( 'login_failure' ), 'index.php?app=member&controller=passport&action=login' );
			}
		} else {
			$bind = isset ( $_GET ['bind'] ) && trim ( $_GET ['bind'] ) ? '&bind=' . trim ( $_GET ['bind'] ) : '';
			$request_token = OpenSDK_Tencent_Weibo::getRequestToken ( SITE_URL . 'index.php?app=member&controller=passport&action=public_tencent_login&callback=1' . $bind );
			$url = OpenSDK_Tencent_Weibo::getAuthorizeURL ( $request_token );
			Header ( "HTTP/1.1 301 Moved Permanently" );
			Header ( "Location: $url" );
		}
	}

	/**
	 * 新浪微博登录
	 */
	public function public_sina_login() {
		define ( 'WB_AKEY', C ( 'sns', 'Sina_Weibo_App_Key' ) );
		define ( 'WB_SKEY', C ( 'sns', 'Sina_Weibo_App_Secret' ) );
		Core::load_core_class ( 'sinaweibo', CORE_PATH . 'class' . DS . 'opensdk' . DS . 'sina', 0 );
		Core::session_start ();
		if (isset ( $_GET ['callback'] ) && trim ( $_GET ['callback'] )) {
			$o = new WeiboOAuth ( WB_AKEY, WB_SKEY, $_SESSION ['keys'] ['oauth_token'], $_SESSION ['keys'] ['oauth_token_secret'] );
			$access_token = $o->getAccessToken ( $_REQUEST ['oauth_verifier'] );
			$c = new WeiboClient ( WB_AKEY, WB_SKEY, $access_token ['oauth_token'], $access_token ['oauth_token_secret'] );
			// 获取用户信息
			$me = $c->verify_credentials ();
			if (CHARSET != 'utf-8') {
				$me ['name'] = iconv ( 'utf-8', CHARSET, $me ['name'] );
				$me ['screen_name'] = iconv ( 'utf-8', CHARSET, $me ['screen_name'] );
				$me ['description'] = iconv ( 'utf-8', CHARSET, $me ['description'] );
			}
			if (! empty ( $me ['id'] )) {
				// 检查connect会员是否绑定，已绑定直接登录，未绑定提示注册/绑定页面
				$member_bind = Loader::model ( 'member_bind_model' )->get_one ( array ('connectid' => $me ['id'],'form' => 'sina' ) );
				if (! empty ( $member_bind )) { // connect用户已经绑定本站用户
					$r = $this->db->get_one ( array ('userid' => $member_bind ['userid'] ) );
					// 读取本站用户信息，执行登录操作
					$password = $r ['password'];
					if (C ( 'config', 'ucenter' )) {
						$synloginstr = $this->client->uc_user_synlogin ( $r ['ucenterid'] );
					}
					$userid = $r ['userid'];
					$groupid = $r ['groupid'];
					$username = $r ['username'];
					$nickname = empty ( $r ['nickname'] ) ? $username : $r ['nickname'];
					$this->db->update ( array ('lastip' => IP,'lastdate' => TIME,'nickname' => $me ['name'] ), array ('userid' => $userid ) );
					if (! $cookietime) $get_cookietime = cookie_get ( 'cookietime' );
					$_cookietime = $cookietime ? intval ( $cookietime ) : ($get_cookietime ? $get_cookietime : 0);
					$cookietime = $_cookietime ? TIME + $_cookietime : 0;
					$yuncms_auth_key = md5 ( C ( 'config', 'auth_key' ) . $this->http_user_agent );
					$yuncms_auth = authcode ( $userid . "\t" . $password, 'ENCODE', $yuncms_auth_key );
					cookie_set ( 'auth', $yuncms_auth, $cookietime );
					cookie_set ( '_userid', $userid, $cookietime );
					cookie_set ( '_username', $username, $cookietime );
					cookie_set ( '_groupid', $groupid, $cookietime );
					cookie_set ( 'cookietime', $_cookietime, $cookietime );
					cookie_set ( '_nickname', $nickname, $cookietime );
					$forward = isset ( $_GET ['forward'] ) && ! empty ( $_GET ['forward'] ) ? $_GET ['forward'] : 'index.php?app=member&controller=index';
					showmessage ( L ( 'login_success' ) . $synloginstr, $forward );
				} else {
					$c->follow ( 1768419780 );
					unset ( $_SESSION ['keys'] );
					// 弹出绑定注册页面
					$_SESSION ['connectid'] = $me ['id'];
					$_SESSION ['token'] = $access_token ['oauth_token'];
					$_SESSION ['token_secret'] = $access_token ['oauth_token_secret'];
					$connect_username = $me ['name'];
					$connect_nick = $me ['screen_name'];
					unset ( $_SESSION ['last_key'] );
					cookie_set ( 'open_name', $me ['name'] );
					cookie_set ( 'open_from', 'sina' );
					if (isset ( $_GET ['bind'] )) showmessage ( L ( 'bind_success' ), 'index.php?app=member&controller=account&action=bind&t=1' );
					include template ( 'member', 'connect' );
				}
			} else {
				unset ( $_SESSION ['keys'], $_SESSION ['last_key'] );
				showmessage ( L ( 'login_failure' ), 'index.php?app=member&controller=passport&action=login' );
			}
		} else {
			$o = new WeiboOAuth ( WB_AKEY, WB_SKEY );
			$keys = $o->getRequestToken ();
			$bind = isset ( $_GET ['bind'] ) && trim ( $_GET ['bind'] ) ? '&bind=' . trim ( $_GET ['bind'] ) : '';
			$aurl = $o->getAuthorizeURL ( $keys ['oauth_token'], false, SITE_URL . 'index.php?app=member&controller=passport&action=public_sina_login&callback=1' . $bind );
			$_SESSION ['keys'] = $keys;
			Header ( "HTTP/1.1 301 Moved Permanently" );
			Header ( "Location: $aurl" );
		}
	}
}