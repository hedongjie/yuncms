<?php
/**
 * 新浪云计算短信接口
 * @author Tongle Xu <xutongle@gmail.com>
 * @copyright Copyright (c) 2003-2103 Jinan TintSoft development co., LTD
 * @license http://www.tintsoft.com/html/about/copyright/
 * @version $Id: SAE.php 64 2013-02-27 01:09:36Z 85825770@qq.com $
 */
class SMS_Driver_SAE extends SMS {

	/**
	 * (non-PHPdoc)
	 * @see SMS::set()
	 */
	public function set($options){
		$this->sms = apibus::init( "sms"); //创建短信服务对象
	}

	/**
	 * (non-PHPdoc)
	 * @see SMS::send()
	 */
	public function send($mobiles, $content, $sendtime = '', $addserial = '', $charset = 'UTF-8', $priority = 5){
		if (! is_array ( $mobiles )) $mobiles = array ($mobiles );
		foreach ( $mobiles as $mobile ) {
			//TODO 发送短信未完成
			$obj = $this->sms->send( $mobile, $content , $charset);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see SMS::get_balance()
	 */
	public function get_balance(){

	}
}