<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class IndexController {
	public function __construct() {
		$this->type = S ( 'common/type_license' ); // 加载授权分类
		$this->db = Loader::model ( 'license_model' );
		$this->db2 = Loader::model ( 'license_client_model' );
	}

	/**
	 * 授权查询
	 */
	public function init() {
		$SEO = seo ( '', L ( 'license' ), '', '' );
		include template ( 'license', 'index' );
	}

	/**
	 * 查看授权
	 */
	public function query() {
		$d = trim ( urldecode ( $_POST ['d'] ) );
		if (! $d) $d = trim ( urldecode ( $_GET ['d'] ) );
		if (! $d) showmessage ( L ( 'license_exit' ) );
		$type_arr = S ( 'common/type_license' );
		$info = $this->db->get_one ( array ('domain' => $d ) );
		if (! $info) showmessage ( L ( 'license_exit' ) );
		extract ( $info );
		$SEO = seo ( '', L ( 'license' ), '', '' );
		include template ( 'license', 'show' );
	}

	public function notice() {
		// 处理接收的数据
		$str = '';
		$info = array ();
		$info ['typeid'] = isset ( $_GET ['typeid'] ) ? intval ( trim ( $_GET ['typeid'] ) ) : 0; // 产品类型
		$info ['sitename'] = isset ( $_GET ['sitename'] ) ? trim ( $_GET ['sitename'] ) : ''; // 站点名称
		$info ['siteurl'] = isset ( $_GET ['siteurl'] ) ? trim ( $_GET ['siteurl'] ) : ''; // 站点URL
		$info ['charset'] = isset ( $_GET ['charset'] ) ? trim ( $_GET ['charset'] ) : ''; // 站点charset
		$info ['version'] = isset ( $_GET ['version'] ) ? trim ( $_GET ['version'] ) : ''; // 站点version
		$info ['release'] = isset ( $_GET ['siteurl'] ) ? trim ( $_GET ['release'] ) : ''; // 站点release
		$info ['os'] = isset ( $_GET ['os'] ) ? trim ( $_GET ['os'] ) : ''; // 站点os
		$info ['php'] = isset ( $_GET ['php'] ) ? trim ( $_GET ['php'] ) : ''; // 站点php
		$info ['mysql'] = isset ( $_GET ['mysql'] ) ? trim ( $_GET ['mysql'] ) : ''; // 站点mysql
		$info ['browser'] = isset ( $_GET ['browser'] ) ? urldecode ( trim ( $_GET ['browser'] ) ) : ''; // 站点browser
		$info ['username'] = isset ( $_GET ['username'] ) ? urldecode ( trim ( $_GET ['username'] ) ) : ''; // 站点username
		$info ['email'] = isset ( $_GET ['email'] ) ? urldecode ( trim ( $_GET ['email'] ) ) : ''; // 站点username
		$info ['uuid'] = isset ( $_GET ['uuid'] ) ? strtoupper ( urldecode ( trim ( $_GET ['uuid'] ) ) ) : exit (); // 站点username
		$r = $this->db2->get_one ( array ('uuid' => $info ['uuid'] ) ); // 查询是否存在
		if (! $r) $this->db2->insert ( $info );
		$res = $this->check_license ( $info ['uuid'] ); // 检测授权
		if ($res) {
			$str .= '$("#yuncms_license").html("' . $res ['type'] . '");';
			$str .= '$("#yuncms_sn").html("' . $res ['uuid'] . '");';
		} else { // 未授权
			$str .= '$("#yuncms_license").html("未授权（<a href=\"http://www.yuncms.net/html/price/\" target=\"_blank\" style=\"color:red\">点击购买</a>）");';
			$str .= '$("#yuncms_sn").html("未激活（<a href=\'http://www.yuncms.net/index.php?app=member&controller=service&action=activation_key&domain=127.0.0.1\' target=\'_blank\' style=\'color:red\'>点击激活</a>）");';
		}
		exit ( $str );
		// 查询升级
		$str .= '}catch(e){}';
		exit ( $str );
	}

	/**
	 * 查询授权
	 * @param array $uuid
	 */
	private function check_license($uuid) {
		$return = array ();
		$res = $this->db->get_one ( array ('uuid' => $uuid ) );
		if ($res) {
			$return ['type'] = $this->type [$res ['typeid']]; // 产品类型
			$return ['uuid'] = $res ['uuid']; // 序列号
			return $return;
		}
		return false;
	}

	/**
	 * 查询升级
	 */
	private function check_update($info) {
		// 查询最新的版本补丁
		// 补丁名称
		$title = "phpcms v9.2.4 正式发布";
		// 补丁地址
		$patch_url = "http://bbs.phpcms.cn/thread-444632-1-1.html";
		// 校验码
		$verify = md5 ( $_GET ['uuid'] );
		// 发布日期
		$release = '20120523';
		// 危险等级
		$level = '中';
		// 发布时间
		$time = "";
		$str = 'function set_site_notice(type) {';
		$str = '	if(type) {';
		$str = "		document.getElementById('site_noticeid').style.display='none';";
		$str = "	$.getJSON('http://update.v9.phpcms.cn/index.php?m=update&c=index&a=notice_op&jsoncallback=?',{
		notice_type: type,verify: '$verify',release:'$release',flag:'2'},function(json){return false;});if(type==3) {window.open ('$patch_url');return false;}} else {document.getElementById('site_noticeid').style.display='none';}}";
		$str .= 'try {$("#yuncms_notice").html("
		<div id=\"site_noticeid\" style=\"position:absolute;left:318px;top:176px;width:400px;height:152px;z-index:1;background: #EDF2F8;border:#A4C0F7 solid 5px;color:#000000;padding:8px;\">
		<span style=\"background: #82A7F4;font-weight:bold;color:#ffffff;padding:2px;\">YUNCMS 远程公告</span><br>
		<div style=\"padding:10px 10px 5px;\"><img src=\"' . IMG_PATH . 'message-warn.png\" style=\"vertical-align: middle\">
		$title<BR>危险&nbsp;&nbsp;等级：<font color=\"red\">$level</font>
		<BR>当前版本号：phpcmsV9.1.14（20120523） <br>
		最新版本号：Phpcms v9.2.4（20121109）<BR>
		发布&nbsp;&nbsp;日期：2012-11-12 11:05:00</div>
		<div style=\" text-align:center\"><input type=\'button\' class=\"button\" value=\'下次提醒\' onclick=\'set_site_notice(0)\'><input type=\'button\' class=\"button\" value=\'明天提醒\' onclick=\'set_site_notice(1)\'><input type=\'button\' class=\"button\" value=\'忽略\' onclick=\'set_site_notice(2)\'><input type=\'button\' class=\"button\" value=\'我要解决此问题\' onclick=\'set_site_notice(3)\' style=\"width:110px\"></div></div>");';
	}
}