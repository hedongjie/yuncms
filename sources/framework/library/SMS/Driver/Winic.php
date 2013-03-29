<?php
/**
 * 移动商务短信接口
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-26
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
class SMS_Driver_Winic extends SMS{

	public function __construct(){
		$this->client = new SoapClient ( "http://sdkhttp.eucp.b2m.cn/sdk/SDKService?wsdl" );
	}

	public function set($options) {
		$this->serial_number = $options['username'];
		$this->password = $options['password'];
		$this->session_key = $options['session_key'];
		$this->sign = $options['sign'];
	}

	public function send(){

	}

	public function get_balance(){

	}
}