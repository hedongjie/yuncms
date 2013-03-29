<?php
/**
 * 亿美软通短信接口
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-26
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
class SMS_Driver_Emay extends SMS {

	private $client;
	private $serial_number;
	private $password;
	private $session_key;
	private $sign;

	public function __construct() {
		if (! class_exists('SoapClient',false)) {
			throw_exception ( 'suppert does not exist.' . ':SoapClient' );
		}
		$this->client = new SoapClient ( "http://sdkhttp.eucp.b2m.cn/sdk/SDKService?wsdl" );
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see SMS::set()
	 */
	public function set($options) {
		$this->serial_number = $options ['username'];
		$this->password = $options ['password'];
		$this->session_key = $options ['session_key'];
		$this->sign = $options ['sign'];
	}

	/**
	 *
	 *
	 *
	 *
	 * 指定一个 session key 并 进行登录操作
	 *
	 * @param string $session_key 指定一个session key
	 * @return int 操作结果状态码
	 *
	 *
	 */
	public function login($session_key = '') {
		if ($session_key != '') $this->session_key = $session_key;
		$params = array ('arg0' => $this->serial_number,'arg1' => $this->session_key,'arg2' => $this->password );
		$result = $this->client->__soapCall ( 'registEx', array ('parameters' => $params ) );
		if ($result->return == 0)
			return true;
		else if ($result->return == '10')
			$this->error = '客户端注册失败';
		else if ($result->return == '101')
			$this->error = '客户端网络故障';
		else if ($result->return == '305')
			$this->error = '服务器端返回错误，错误的返回值';
		else if ($result->return == '999') $this->error = '操作频繁';
		return false;
	}

	/**
	 * 注销操作 (注:此方法必须为已登录状态下方可操作)
	 *
	 * @return int 操作结果状态码
	 *
	 *         之前保存的sessionKey将被作废
	 *         如需要，可重新login
	 */
	function logout() {
		$params = array ('arg0' => $this->serial_number,'arg1' => $this->session_key );
		$result = $this->client->__soapCall ( 'logout', array ('parameters' => $params ) );
		return $result;
	}

	/**
	 * 企业注册 [邮政编码]长度为6 其它参数长度为20以内
	 *
	 * @param string $eName 企业名称
	 * @param string $linkMan 联系人姓名
	 * @param string $phoneNum 联系电话
	 * @param string $mobile 联系手机号码
	 * @param string $email 联系电子邮件
	 * @param string $fax 传真号码
	 * @param string $address 联系地址
	 * @param string $postcode 邮政编码
	 *
	 * @return int 操作结果状态码
	 */
	public function register($ename, $linkman, $phonenum, $mobile, $email, $fax, $address, $postcode) {
		$params = array ('arg0' => $this->serial_number,'arg1' => $this->session_key,'arg2' => $ename,'arg3' => $linkman,'arg4' => $phonenum,'arg5' => $mobile,'arg6' => $email,'arg7' => $fax,'arg8' => $address,'arg9' => $postcode );
		$result = $this->client->__soapCall ( 'registDetailInfo', array ('parameters' => $params ) );
		if ($result->return == 0)
			return true;
		else if ($result->return == '-1')
			$this->error = '注册企业信息不符合要求';
		else if ($result->return == '11')
			$this->error = '企业信息注册失败';
		else if ($result->return == '101')
			$this->error = '客户端网络故障';
		else if ($result->return == '305')
			$this->error = '服务器端返回错误，错误的返回值';
		else if ($result->return == '307')
			$this->error = '目标电话号码不符合规则，电话号码必须是以0、1开头';
		else if ($result->return == '999') $this->error = '操作频繁';
		return false;
	}

	/**
	 * 修改密码
	 *
	 * @param string $NewPassword 新密码
	 * @return int 操作结果状态码
	 */
	public function editpassword($new_password) {
		$params = array ('arg0' => $this->serial_number,'arg1' => $this->session_key,'arg2' => $this->password,'arg3' => $new_password );
		$result = $this->client->__soapCall ( 'serialPwdUpd', array ('parameters' => $params ) );
		if ($result->return == '0')
			return true;
		else if ($result->return == '-1')
			$this->error = '新密码长度不能大于6';
		else if ($result->return == '101')
			$this->error = '客户端网络故障';
		else if ($result->return == '305')
			$this->error = '服务器端返回错误数据，返回的数据必须是数字';
		else if ($result->return == '308')
			$this->error = '新密码不是数字，必须是数字';
		else if ($result->return == '999') $this->error = '操作频繁';
		return false;
	}

	/**
	 * 查询单条费用
	 *
	 * @return double 单条费用
	 */
	public function get_each_fee() {
		$params = array ('arg0' => $this->serial_number,'arg1' => $this->session_key );
		$result = $this->client->__soapCall ( 'getEachFee', array ('parameters' => $params ) );
		return $result->return;
	}

	/**
	 * 余额查询
	 *
	 * @return double 余额
	 */
	public function get_balance() {
		$params = array ('arg0' => $this->serial_number,'arg1' => $this->session_key );
		$result = $this->client->__soapCall ( 'getBalance', array ('parameters' => $params ) );
		return $result->return;
	}

	/**
	 * 得到状态报告
	 *
	 * @return array 状态报告列表, 一次最多取5个
	 */
	public function get_report() {
		$params = array ('arg0' => $this->serial_number,'arg1' => $this->session_key );
		$result = $this->client->__soapCall ( 'getReport', array ('parameters' => $params ) );
		return $result;
	}

	/**
	 * 短信充值
	 *
	 * @param string $cardId [充值卡卡号]
	 * @param string $cardPass [密码]
	 * @return int 操作结果状态码
	 */
	public function charge_up($cardid, $cardpass) {
		$params = array ('arg0' => $this->serial_number,'arg1' => $this->session_key,'arg2' => $cardid,'arg3' => $cardpass );
		$result = $this->client->__soapCall ( 'chargeUp', array ('parameters' => $params ) );
		return $result->return;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see SMS::send()
	 */
	public function send($mobiles, $content, $sendtime = '', $addserial = '', $charset = 'UTF-8', $priority = 5) {
		if (! is_array ( $mobiles )) $mobiles = array ($mobiles );
		$params = array ('arg0' => $this->serial_number,'arg1' => $this->session_key,'arg2' => $sendtime,'arg3' => array (),'arg4' => $content . $this->sign,'arg5' => $addserial,'arg6' => $charset,'arg7' => $priority );
		foreach ( $mobiles as $mobile ) {
			array_push ( $params ['arg3'], $mobile );
		}
		$result = $this->client->__soapCall ( 'sendSMS', array ('parameters' => $params ) );
		if ($result->return == 0)
			return true;
		else if ($result->return == '17')
			$this->error = '发送信息失败';
		else if ($result->return == '18')
			$this->error = '发送定时信息失败';
		else if ($result->return == '101')
			$this->error = '客户端网络故障';
		else if ($result->return == '305')
			$this->error = '服务器端返回错误，错误的返回值';
		else if ($result->return == '307')
			$this->error = '目标电话号码不符合规则，电话号码必须是以0、1开头';
		else if ($result->return == '997')
			$this->error = '平台返回找不到超时的短信，该信息是否成功无法确定';
		else if ($result->return == '998') $this->error = '由于客户端网络问题导致信息发送超时，该信息是否成功下发无法确定';
		return false;
	}

}