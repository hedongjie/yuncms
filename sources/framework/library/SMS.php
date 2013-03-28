<?php
/**
 * SMS短信基类
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-26
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
abstract class SMS {
	/**
	 * 短信配置信息
	 *
	 * @var string
	 */
	private $_options = '';

	/**
	 * 设置短信发送参数
	 *
	 * @param array $options
	 */
	public abstract function set($options);

	/**
	 * 短信发送
	 *
	 * @param array $mobiles 如 159xxxxxxxx ,如果需要多个手机号群发,如
	 *        array('159xxxxxxxx','159xxxxxxx2')
	 * @param string $content
	 * @param string $sendTime yyyymmddHHiiss, 即为
	 *        	年年年年月月日日时时分分秒秒,例如:20090504111010
	 *        代表2009年5月4日 11时10分10秒
	 *        如果不需要定时发送，请为'' (默认)
	 * @param string $addSerial 扩展号, 默认为 ''
	 * @param string $charset 内容字符集, 默认UTF-8
	 * @param int $priority 优先级, 默认5
	 * @return int 操作结果状态码
	 */
	public abstract function send($mobiles, $content, $sendtime = '', $addserial = '', $charset = 'UTF-8', $priority = 5);

	/**
	 * 查询余额
	 */
	public abstract function get_balance();
}