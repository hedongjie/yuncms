<?php
/**
 * 系统设置
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-26
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class SettingController extends admin {

	private $db;

	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'application_model' );
	}

	/**
	 * 配置信息
	 */
	public function init() {
		$show_validator = $show_header = true;
		$config = C ( 'config' ); // 加载框架配置
		$system = C ( 'system' ); // 加载系统设置
		$log = C ( 'log' ); // 加载日志设置
		$attachment = C ( 'attachment' ); // 加载附件设置
		$sms = C ( 'sms' ); // 加载短信设置
		$sns = C ( 'sns' ); // 加载SMS配置
		$mail = C ( 'mail' ); // 加载邮箱配置
		$setting = $this->db->get_setting('admin');
		include $this->admin_tpl ( 'setting' );
	}

	/**
	 * 保存配置信息
	 */
	public function save() {
		// 保存框架配置
		Core_Config::modify ( 'config', $_POST ['config'] );
		// 保存系统配置
		if (! Validate::is_email ( $_POST ['system'] ['system_email'] )) { // 判断邮件是否合法
			showmessage ( L ( 'email_illegal' ), HTTP_REFERER );
		}
		Core_Config::modify ( 'system', $_POST ['system'] );
		// 保存网站配置
		foreach ( $_POST ['setting'] as $key => $value ) {
			$setting [$key] = trim ( $value );
		}
		if (empty ( $setting ['site_name'] )) { // 站点名称不能为空
			showmessage ( L ( 'site_name' ) . L ( 'not_empty' ), HTTP_REFERER );
		}
		$this->db->set_setting('admin',$setting);
		// 保存日志配置
		$_POST ['log']['log_chunk_size'] = $_POST ['log']['log_chunk_size'].'M';
		Core_Config::modify ( 'log', $_POST ['log'] ); // 系统配置
		// 处理邮件配置
		$mail = C ( 'mail' ); // 加载邮箱配置
		$settingnew = $_POST ['settingnew'];
		$mail_config = array ();
		$mail_config ['type'] = $settingnew ['type'];
		$mail_config ['delimiter'] = $settingnew ['delimiter'];
		$mail_config ['mailusername'] = $settingnew ['mailusername'];
		$mail_config ['cc'] = $settingnew ['cc'];
		$mail_config ['poll'] = $settingnew ['poll'];
		$oldsmtp = $settingnew ['type'] == 3 ? $settingnew ['smtp'] : $settingnew ['esmtp'];
		if ($settingnew ['type'] != 1) {
			if ($settingnew ['type'] == 3) {
				$deletesmtp = isset ( $settingnew ['smtp'] ['delete'] ) ? $settingnew ['smtp'] ['delete'] : array ();
			} else {
				$deletesmtp = isset ( $settingnew ['esmtp'] ['delete'] ) ? $settingnew ['esmtp'] ['delete'] : array ();
			}
		}
		// $deletesmtp = $settingnew ['type'] != 1 ? ($settingnew ['type'] == 3
		// ? (isset($settingnew ['smtp'] ['delete']) ? $settingnew['smtp']
		// ['delete'] : array()) : $settingnew ['esmtp']['delete']) : array ();
		foreach ( $oldsmtp as $id => $value ) {
			if ((empty ( $deletesmtp ) || ! in_array ( $id, $deletesmtp )) && ! empty ( $value ['server'] ) && ! empty ( $value ['port'] )) {
				$passwordmask = $mail ['smtp'] [$id] ['auth_password'] ? $mail ['smtp'] [$id] ['auth_password'] {0} . '********' . substr ( $mail ['smtp'] [$id] ['auth_password'], - 2 ) : '';
				if ($settingnew ['type'] == 2) $value ['auth_password'] = $value ['auth_password'] == $passwordmask ? $mail ['smtp'] [$id] ['auth_password'] : $value ['auth_password'];
				$mail_config ['smtp'] [] = $value;
			}
		}
		if (! empty ( $_POST ['newsmtp'] )) {
			foreach ( $_POST ['newsmtp'] ['server'] as $id => $server ) {
				if (! empty ( $server ) && ! empty ( $_POST ['newsmtp'] ['port'] [$id] )) {
					$mail_config ['smtp'] [] = array ('server' => $server,'port' => $_POST ['newsmtp'] ['port'] [$id] ? intval ( $_POST ['newsmtp'] ['port'] [$id] ) : 25,'auth' => $_POST ['newsmtp'] ['auth'] [$id] ? 1 : 0,'from' => $_POST ['newsmtp'] ['from'] [$id],
							'auth_username' => $_POST ['newsmtp'] ['auth_username'] [$id],'auth_password' => $_POST ['newsmtp'] ['auth_password'] [$id] );
				}
			}
		}


		$attachment = C ( 'attachment' ); // 加载附件设置
		$passwordmask = $attachment ['ftp_password'] ? $attachment ['ftp_password'] {0} . '********' . substr ( $attachment ['ftp_password'], - 2 ) : '';
		$_POST ['attachment'] ['ftp_password'] = $passwordmask == $_POST ['attachment'] ['ftp_password'] ? $attachment ['ftp_password'] : $_POST ['attachment'] ['ftp_password'];
		Core_Config::modify ( 'attachment', $_POST ['attachment'] ); // 附件配置
		Core_Config::modify ( 'sms', $_POST ['sms'] ); // 短信配置
		Core_Config::set ( 'mail', $mail_config ); // 邮件配置
		$this->setcache ();
		showmessage ( L ( 'setting_succ' ), HTTP_REFERER );
	}

	/**
	 * 设置缓存
	 */
	private function setcache() {
		$result = $this->db->where ( array ('application' => 'admin' ) )->find ();
		$setting = string2array ( $result ['setting'] );
		S ( 'common/common', $setting );
	}

	/**
	 * 测试邮件发送
	 */
	public function public_test_mail() {
		$subject = 'yuncms test mail';
		$message = 'this is a test mail from tintsoft team';
		if ($b = sendmail ( $_GET ['mail_to'], $subject, $message )) {
			echo L ( 'test_email_succ' ) . $_GET ['mail_to'];
		} else {
			echo L ( 'test_email_faild' );
		}
	}

	/**
	 * 初始化短信发送设置
	 *
	 * @param array $options
	 */
	private function sms_init($options = array()) {
		$class = 'SMS_Driver_' . ucfirst ( $options ['driver'] );
		$sms = new $class ();
		$sms->set ( $options );
		return $sms;
	}

	/**
	 * 测试短信发送设置
	 */
	public function public_test_sms() {
		$options = array ('driver' => $_POST ['driver'],'username' => $_POST ['username'],'password' => $_POST ['password'],'session_key' => $_POST ['session_key'],'sign' => $_POST ['sign'] );
		$sms = $this->sms_init ( $options );
		$message = 'this is a test sms from yuncms team';
		$msg = $sms->send ( $_GET ['sms_to'], $message );
		if ($msg) {
			echo L ( 'test_sms_succ' ) . $_GET ['sms_to'];
		} else {
			echo $sms->error;
		}
	}

	/**
	 * 查询短信余额
	 */
	public function public_get_balance() {
		$options = array ('driver' => $_POST ['driver'],'username' => $_POST ['username'],'password' => $_POST ['password'],'session_key' => $_POST ['session_key'],'sign' => $_POST ['sign'] );
		$sms = $this->sms_init ( $options );
		$r = $sms->get_balance ();
		exit ( '￥' . $r );
	}

	/**
	 * 测试GD库
	 */
	private function check_gd() {
		if (! function_exists ( 'imagepng' ) && ! function_exists ( 'imagejpeg' ) && ! function_exists ( 'imagegif' )) {
			$gd = L ( 'gd_unsupport' );
		} else {
			$gd = L ( 'gd_support' );
		}
		return $gd;
	}
}