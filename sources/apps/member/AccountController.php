<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'member:foreground' );
/**
 * 帐户管理
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-7-3
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: AccountController.php 965 2012-07-03 09:37:48Z 85825770@qq.com
 *          $
 */
class AccountController extends foreground {
	/**
	 * 账户管理
	 */
	public function init() {
		$memberinfo = $this->memberinfo;
		$avatar = get_memberavatar ( $this->memberinfo ['userid'], 90 );
		$grouplist = S ( 'member/grouplist' );
		$member_model = S ( 'common/member_model' );
		// 获取用户模型数据
		$this->db->set_model ( $this->memberinfo ['modelid'] );
		$member_modelinfo_arr = $this->db->getby_userid ( $this->memberinfo ['userid'] );
		$model_info = S ( 'member/model_field_' . $this->memberinfo ['modelid'] );
		if (is_array ( $model_info )) {
			foreach ( $model_info as $k => $v ) {
				if ($v ['formtype'] == 'omnipotent') continue;
				if ($v ['formtype'] == 'image') {
					$member_modelinfo [$v ['name']] = "<a href='$member_modelinfo_arr[$k]' target='_blank'><img src='$member_modelinfo_arr[$k]' height='40' widht='40' onerror=\"this.src='" . IMG_PATH . "member/nophoto.gif'\"></a>";
				} elseif ($v ['formtype'] == 'datetime' && $v ['fieldtype'] == 'int') { // 如果为日期字段
					$member_modelinfo [$v ['name']] = Format::date ( $member_modelinfo_arr [$k], $v ['format'] == 'Y-m-d H:i:s' ? 1 : 0 );
				} elseif ($v ['formtype'] == 'images') {
					$tmp = string2array ( $member_modelinfo_arr [$k] );
					$member_modelinfo [$v ['name']] = '';
					if (is_array ( $tmp )) {
						foreach ( $tmp as $tv ) {
							$member_modelinfo [$v ['name']] .= " <a href='$tv[url]' target='_blank'><img src='$tv[url]' height='40' widht='40' onerror=\"this.src='" . IMG_PATH . "member/nophoto.gif'\"></a>";
						}
						unset ( $tmp );
					}
				} elseif ($v ['formtype'] == 'box') { // box字段，获取字段名称和值的数组
					$tmp = explode ( "\n", $v ['options'] );
					if (is_array ( $tmp )) {
						foreach ( $tmp as $boxv ) {
							$box_tmp_arr = explode ( '|', trim ( $boxv ) );
							if (is_array ( $box_tmp_arr ) && isset ( $box_tmp_arr [1] ) && isset ( $box_tmp_arr [0] )) {
								$box_tmp [$box_tmp_arr [1]] = $box_tmp_arr [0];
								$tmp_key = intval ( $member_modelinfo_arr [$k] );
							}
						}
					}
					if (isset ( $box_tmp [$tmp_key] )) {
						$member_modelinfo [$v ['name']] = $box_tmp [$tmp_key];
					} else {
						$member_modelinfo [$v ['name']] = $member_modelinfo_arr [$k];
					}
					unset ( $tmp, $tmp_key, $box_tmp, $box_tmp_arr );
				} elseif ($v ['formtype'] == 'linkage') { // 如果为联动菜单
					$tmp = string2array ( $v ['setting'] );
					$tmpid = $tmp ['linkageid'];
					$linkagelist = S ( 'linkage' . $tmpid );
					$fullname = $this->_get_linkage_fullname ( $member_modelinfo_arr [$k], $linkagelist );
					$member_modelinfo [$v ['name']] = substr ( $fullname, 0, - 1 );
					unset ( $tmp, $tmpid, $linkagelist, $fullname );
				} else {
					$member_modelinfo [$v ['name']] = $member_modelinfo_arr [$k];
				}
			}
		}
		include template ( 'member', 'account_manage' );
	}

	/**
	 * 已绑定的开放平台
	 */
	public function bind() {
		$this->member_bind = Loader::model ( 'member_bind_model' );
		if (isset ( $_SESSION ['connectid'] )) {
			// 更新开放平台
			$open_name = cookie ( 'open_name' );
			$open_from = cookie ( 'open_from' );
			$member_bind = array ('userid' => $this->memberinfo ['userid'],'connectid' => $_SESSION ['connectid'],'token' => $_SESSION ['token'],'token_secret' => $_SESSION ['token_secret'],'name' => $open_name,'form' => $open_from,'addtime' => TIME );
			unset ( $_SESSION ['connectid'], $_SESSION ['oauth_token'], $_SESSION ['oauth_token_secret'] );
			$member_bind_check = $this->member_bind->get_one ( array ('userid' => $this->memberinfo ['userid'],'form' => $open_from ) );
			if ($member_bind_check) {
				$this->member_bind->where(array ('userid' => $this->memberinfo ['userid'],'form' => $open_from ))->update ( $member_bind );
			} else {
				$this->member_bind->insert ( $member_bind );
			}
		}
		$bind_list = $this->member_bind->where(array ('userid' => $this->memberinfo ['userid'] ))->select (  );
		$bind = array ();
		foreach ( $bind_list as $val ) {
			$bind [$val ['form']] = $val;
		}
		$open_arr = array ('tencent','qq','163','sohu','douban','baidu','tianya','kaixin','renren','sina' );
		$open_list = array ();
		foreach ( $open_arr as $oval ) {
			$open_list [$oval] = $bind [$oval];
		}
		unset ( $_SESSION );
		include template ( 'member', 'account_bind' );
	}

	/**
	 * 解除开放平台的绑定
	 */
	public function unbind() {
		$form = isset ( $_GET ['form'] ) && trim ( $_GET ['form'] ) ? trim ( $_GET ['form'] ) : exit ( '0' );
		$this->member_bind = Loader::model ( 'member_bind_model' );
		if ($this->member_bind->where(array ('userid' => $this->memberinfo ['userid'],'form' => $form ))->delete (  )) {
			exit ( '1' );
		}
		exit ( '0' );
	}

	/**
	 * 手机绑定
	 */
	public function mobile() {
		Loader::session ();
		$config = S ( 'common/common' ); // 加载网站配置
		if (isset ( $_POST ['dosubmit'] )) { // 更新用户手机绑定
			$mobile = isset ( $_POST ['mobile'] ) && trim ( $_POST ['mobile'] ) ? trim ( $_POST ['mobile'] ) : showmessage ( L ( 'Please_input_the_correct_cell_phone_number' ), HTTP_REFERER );
			if ($mobile != $_SESSION ['mobile']) showmessage ( L ( 'Mobile_phone_number_does_not_agree' ), HTTP_REFERER );
			if ($_POST ['mobile_verify'] != $_SESSION ['mobile_verify']) showmessage ( L ( 'operation_failure' ) . L ( 'code_error' ), HTTP_REFERER );
			$this->db->where(array ('userid' => $this->memberinfo ['userid'] ))->update ( array ('mobile' => $mobile ) );
			unset ( $_SESSION ['mobile'], $_SESSION ['mobile_verify'] );
			Loader::model ( 'times_model' )->delete ( array ('username' => $this->memberinfo ['username'] ) );
			showmessage ( L ( 'operation_success' ), U ( 'member/account/mobile' ) );
		} else {
			if ($this->memberinfo ['mobile'] && ! isset ( $_GET ['edit_bind'] )) { // 已经绑定
				$mobile = $this->memberinfo ['mobile'];
			}
			include template ( 'member', 'account_bind_mobile' );
		}
	}

	public function send_mobile() {
		Loader::session ();
		$mobile = isset ( $_GET ['mobile'] ) && trim ( $_GET ['mobile'] ) ? trim ( $_GET ['mobile'] ) : exit ( '1' );
		// 验证手机号码是否合法
		if (! preg_match ( "/13[123456789]{1}\d{8}|15[123456789]\d{8}|18[1235689]\d{8}/", $mobile )) exit ( '1' );
		$this->times_db = Loader::model ( 'times_model' );
		$rtime = $this->times_db->getby_username ( $this->memberinfo ['username'] );
		if ($rtime ['times'] > 4) {
			$minute = 1440 - floor ( (TIME - $rtime ['logintime']) / 60 );
			exit ( '-1' );
		}

		$_SESSION ['mobile'] = $mobile;
		if (! isset ( $_SESSION ['mobile_verify'] )) $_SESSION ['mobile_verify'] = random ( 6, 1 );
		$config = S ( 'common/common' ); // 加载网站配置
		$message = L ( 'your_sms_verification_code_is' ) . $_SESSION ['mobile_verify'] . "[{$config['site_name']}].";

		// 发送短信验证码
		if (sendsms ( $mobile, $message )) {
			if ($rtime && $rtime ['times'] < 5) {
				$times = 5 - intval ( $rtime ['times'] );
				$this->times_db->where(array ('username' => $this->memberinfo ['username'] ))->update ( array ('ip' => IP,'times' => '+=1' ) );
			} else {
				$this->times_db->insert ( array ('username' => $this->memberinfo ['username'],'ip' => IP,'logintime' => TIME,'times' => 1 ) );
				$times = 5;
			}
			exit ( '2' );
		} else {
			exit ( '0' );
		}
	}

	public function account_manage_info() {
		if (isset ( $_POST ['dosubmit'] )) {
			// 更新用户昵称
			$nickname = isset ( $_POST ['nickname'] ) && trim ( $_POST ['nickname'] ) ? trim ( $_POST ['nickname'] ) : '';
			if ($nickname) {
				$this->db->update ( array ('nickname' => $nickname ), array ('userid' => $this->memberinfo ['userid'] ) );
				if (! isset ( $cookietime )) {
					$get_cookietime = cookie ( 'cookietime' );
				}
				$_cookietime = isset ( $cookietime ) ? intval ( $cookietime ) : ($get_cookietime ? $get_cookietime : 0);
				$cookietime = $_cookietime ? TIME + $_cookietime : 0;
				cookie ( '_nickname', $nickname, $cookietime );
			}
			if (isset ( $_POST ['info'] )) {
				require_once CACHE_MODEL_PATH . 'member_input.php';
				require_once CACHE_MODEL_PATH . 'member_update.php';
				$member_input = new member_input ( $this->memberinfo ['modelid'] );
				$modelinfo = $member_input->get ( $_POST ['info'] );
				$this->db->set_model ( $this->memberinfo ['modelid'] );
				$membermodelinfo = $this->db->get_one ( array ('userid' => $this->memberinfo ['userid'] ) );
				if (! empty ( $membermodelinfo )) {
					$this->db->update ( $modelinfo, array ('userid' => $this->memberinfo ['userid'] ) );
				} else {
					$modelinfo ['userid'] = $this->memberinfo ['userid'];
					$this->db->insert ( $modelinfo );
				}
			}

			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			$memberinfo = $this->memberinfo;
			// 获取会员模型表单
			require CACHE_MODEL_PATH . 'member_form.php';
			$member_form = new member_form ( $this->memberinfo ['modelid'] );
			$this->db->set_model ( $this->memberinfo ['modelid'] );

			$membermodelinfo = $this->db->get_one ( array ('userid' => $this->memberinfo ['userid'] ) );
			$forminfos = $forminfos_arr = $member_form->get ( $membermodelinfo );

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
			include template ( 'member', 'account_manage_info' );
		}
	}

	public function account_manage_password() {
		if (isset ( $_POST ['dosubmit'] )) {
			if (! Validate::is_password ( $_POST ['info'] ['password'] )) {
				showmessage ( L ( 'password_format_incorrect' ), HTTP_REFERER );
			}
			if ($this->memberinfo ['password'] != password ( $_POST ['info'] ['password'], $this->memberinfo ['encrypt'] )) {
				showmessage ( L ( 'old_password_incorrect' ), HTTP_REFERER );
			}
			// 修改会员邮箱
			if ($this->memberinfo ['email'] != $_POST ['info'] ['email'] && Validate::is_email ( $_POST ['info'] ['email'] )) {
				$email = $_POST ['info'] ['email'];
				$updateinfo ['email'] = $_POST ['info'] ['email'];
			} else {
				$email = '';
			}
			$newpassword = password ( $_POST ['info'] ['newpassword'], $this->memberinfo ['encrypt'] );
			$updateinfo ['password'] = $newpassword;

			$this->db->update ( $updateinfo, array ('userid' => $this->memberinfo ['userid'] ) );
			if (ucenter_exists ()) {
				$res = Loader::lib ( 'Ucenter' )->uc_user_edit ( $username, $_POST ['info'] ['password'], $_POST ['info'] ['newpassword'], '', $this->memberinfo ['encrypt'], 1 );

			}
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			$show_validator = true;
			$memberinfo = $this->memberinfo;

			include template ( 'member', 'account_manage_password' );
		}
	}

	public function account_manage_upgrade() {
		$memberinfo = $this->memberinfo;
		$grouplist = S ( 'member/grouplist' );
		if (empty ( $grouplist [$memberinfo ['groupid']] ['allowupgrade'] )) {
			showmessage ( L ( 'deny_upgrade' ), HTTP_REFERER );
		}
		if (isset ( $_POST ['upgrade_type'] ) && intval ( $_POST ['upgrade_type'] ) < 0) {
			showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
		}

		if (isset ( $_POST ['upgrade_date'] ) && intval ( $_POST ['upgrade_date'] ) < 0) {
			showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
		}

		if (isset ( $_POST ['dosubmit'] )) {
			$groupid = isset ( $_POST ['groupid'] ) ? intval ( $_POST ['groupid'] ) : showmessage ( L ( 'operation_failure' ), HTTP_REFERER );

			$upgrade_type = isset ( $_POST ['upgrade_type'] ) ? intval ( $_POST ['upgrade_type'] ) : showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
			$upgrade_date = ! empty ( $_POST ['upgrade_date'] ) ? intval ( $_POST ['upgrade_date'] ) : showmessage ( L ( 'operation_failure' ), HTTP_REFERER );

			// 消费类型，包年、包月、包日，价格
			$typearr = array ($grouplist [$groupid] ['price_y'],$grouplist [$groupid] ['price_m'],$grouplist [$groupid] ['price_d'] );
			// 消费类型，包年、包月、包日，时间
			$typedatearr = array ('366','31','1' );
			// 消费的价格
			$cost = $typearr [$upgrade_type] * $upgrade_date;
			// 购买时间
			$buydate = $typedatearr [$upgrade_type] * $upgrade_date * 86400;
			$overduedate = $memberinfo ['overduedate'] > TIME ? ($memberinfo ['overduedate'] + $buydate) : (TIME + $buydate);

			if ($memberinfo ['amount'] >= $cost) {
				$this->db->update ( array ('groupid' => $groupid,'overduedate' => $overduedate,'vip' => 1 ), array ('userid' => $memberinfo ['userid'] ) );
				// 消费记录
				Loader::lib ( 'pay:spend', false );
				spend::amount ( $cost, L ( 'allowupgrade' ), $memberinfo ['userid'], $memberinfo ['username'] );
				showmessage ( L ( 'operation_success' ), U ( 'member/index/init' ) );
			} else {
				showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
			}
		} else {
			$groupid = isset ( $_GET ['groupid'] ) ? intval ( $_GET ['groupid'] ) : '';
			// 获取头像数组
			$avatar = get_memberavatar ( $this->memberinfo ['userid'], false );
			$memberinfo ['groupname'] = $grouplist [$memberinfo [groupid]] ['name'];
			$memberinfo ['grouppoint'] = $grouplist [$memberinfo [groupid]] ['point'];
			unset ( $grouplist [$memberinfo ['groupid']] );
			include template ( 'member', 'account_manage_upgrade' );
		}
	}
}