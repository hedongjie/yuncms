<?php
/**
 * IP禁止模型
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-26
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
class ipbanned_model extends Model {
	public $table_name = '';

	public function __construct() {
		$this->setting = 'default';
		$this->table_name = 'ipbanned';
		parent::__construct ();
	}

	/**
	 *
	 *
	 * 把IP进行格式化，统一为IPV4， 参数：$op --操作类型 max 表示格式为该段的最大值，比如：192.168.1.*
	 * 格式化为：192.168.1.255 ，其它任意值表示格式化最小值： 192.168.1.1
	 *
	 * @param $op 操作类型,值为(min,max)
	 * @param $ip 要处理的IP段(127.0.0.*)
	 *            (127.0.0.5)
	 */
	public function convert_ip($op, $ip) {
		$arr_ip = explode ( ".", $ip );
		$arr_temp = array ();
		$i = 0;
		$ip_val = $op == "max" ? "255" : "1";
		foreach ( $arr_ip as $key => $val ) {
			$i ++;
			$val = $val == "*" ? $ip_val : $val;
			$arr_temp [] = $val;
		}
		for($i = 4 - $i; $i > 0; $i --) {
			$arr_temp [] = $ip_val;
		}
		$comma = "";
		foreach ( $arr_temp as $v ) {
			$result .= $comma . $v;
			$comma = ".";
		}
		return $result;
	}

	/**
	 *
	 *
	 * 判断IP是否被限并返回
	 *
	 * @param string $ip 当前IP
	 * @param string $ip_from 开始IP段
	 * @param string $ip_to 结束IP段
	 */
	public function ipforbidden($ip, $ip_from, $ip_to) {
		$from = strcmp ( $ip, $ip_from );
		$to = strcmp ( $ip, $ip_to );
		if ($from >= 0 && $to <= 0) {
			return 0;
		} else {
			return 1;
		}
	}

	/**
	 * IP禁止判断接口,供外部调用 ...
	 */
	public function check_ip() {
		$ip_array = array ();
		// 加载IP禁止缓存
		$ipbanned_cache = S ( 'common/ipbanned' );
		if (! empty ( $ipbanned_cache )) {
			foreach ( $ipbanned_cache as $data ) {
				$ip_array [$data ['ip']] = $data ['ip'];
				// 是否是IP段
				if (strpos ( $data ['ip'], '*' )) {
					$ip_min = $this->convert_ip ( "min", $data ['ip'] );
					$ip_max = $this->convert_ip ( "max", $data ['ip'] );
					$result = $this->ipforbidden ( IP, $ip_min, $ip_max );
					if ($result == 0 && $data ['expires'] > TIME) {
						// 被封
						showmessage ( '你的IP地址在不受欢迎的IP段内,所以禁止你访问！' );
					}
				} else {
					// 不是IP段,用绝对匹配
					if (IP == $data ['ip'] && $data ['expires'] > TIME) {
						showmessage ( '你的IP地址不受欢迎,禁止你访问！' );
					}
				}
			}
		}
	}

	/**
	 * 更新IP禁止缓存
	 */
	public function cache(){
		$infos = $this->field('ip,expires')->order('ipbannedid desc')->select ();
		S ( 'common/ipbanned', $infos );
		return true;
	}
}