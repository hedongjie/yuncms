<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
error_reporting ( E_ERROR );
/**
 * 投票
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-7
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: IndexController.php 307 2012-11-11 11:24:56Z xutongle $
 */
class IndexController {
	function __construct() {
		$this->vote = Loader::model ( 'vote_subject_model' ); // 投票标题
		$this->vote_option = Loader::model ( 'vote_option_model' ); // 投票选项
		$this->vote_data = Loader::model ( 'vote_data_model' ); // 投票统计的数据模型
		$this->username = cookie_get ( '_username' );
		$this->userid = cookie_get ( '_userid' ) ? cookie_get ( '_userid' ) : 0;
		$this->groupid = cookie_get ( '_groupid' );
		$this->ip = IP;
	}

	public function init() {
		$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		include template ( 'vote', 'list_new' );
	}

	/**
	 * 投票列表页
	 */
	public function lists() {
		$page = intval ( $_GET ['page'] );
		if ($page <= 0) {
			$page = 1;
		}
		include template ( 'vote', 'list_new' );
	}

	/**
	 * 投票显示页
	 */
	public function show() {
		$type = intval ( $_GET ['type'] ); // 调用方式ID
		$subjectid = abs ( intval ( $_GET ['subjectid'] ) );
		if (! $subjectid) showmessage ( L ( 'vote_novote' ), 'blank' );
		// 取出投票标题
		$subject_arr = $this->vote->get_subject ( $subjectid );

		// 增加判断，防止模板调用不存在投票时js报错 wangtiecheng
		if (! is_array ( $subject_arr )) {
			if (isset ( $_GET ['do'] ) && $_GET ['do'] == 'js') {
				exit ();
			} else {
				showmessage ( L ( 'vote_novote' ), 'blank' );
			}
		}
		extract ( $subject_arr );
		// 显示模版
		$template = $template ? $template : 'vote_tp';
		// 获取投票选项
		$options = $this->vote_option->get_options ( $subjectid );

		// 新建一数组用来存新组合数据
		$total = 0;
		$vote_data = array ();
		$vote_data ['total'] = 0; // 所有投票选项总数
		$vote_data ['votes'] = 0; // 投票人数

		// 获取投票结果信息
		$infos = $this->vote_data->select ( array ('subjectid' => $subjectid ), 'data' );
		// 循环每个会员的投票记录
		foreach ( $infos as $subjectid_arr ) {
			extract ( $subjectid_arr );
			$arr = string2array ( $data );
			foreach ( $arr as $key => $values ) {
				$vote_data [$key] += 1;
			}
			$total += array_sum ( $arr );
			$vote_data ['votes'] ++;
		}
		$vote_data ['total'] = $total;

		// 取出投票结束时间，如果小于当前时间，则选项变灰不可选
		if (date ( "Y-m-d", TIME ) > $todate) {
			$check_status = 'disabled';
			$display = 'display:none;';
		} else {
			$check_status = '';
		}

		// JS调用
		if ($_GET ['do'] == 'js') {
			if (! function_exists ( 'ob_gzhandler' )) ob_clean ();
			ob_start ();
			// $template = 'submit';
			$template = $subject_arr ['template'];
			// 根据TYPE值，判断调用模版
			switch ($type) {
				case 3 : // 首页、栏目页调用
					$true_template = 'vote_tp_3';
					break;
				case 2 : // 内容页调用
					$true_template = 'vote_tp_2';
					break;
				default :
					$true_template = $template;
			}
			include template ( 'vote', $true_template );
			$data = ob_get_contents ();
			ob_clean ();
			exit ( format_js ( $data ) );
		}

		// SEO设置
		$SEO = seo ( '', $subject, $description, $subject );
		// 前台投票列表调用默认页面,以免页面样式错乱.
		if ($_GET ['show_type'] == 1) {
			include template ( 'vote', 'vote_tp' );
		} else {
			include template ( 'vote', $template );
		}

	}

	/**
	 * 处理投票
	 */
	public function post() {
		$subjectid = intval ( $_POST ['subjectid'] );
		if (! $subjectid) showmessage ( L ( 'vote_novote' ), 'blank' );
		// 判断是否已投过票,或者尚未到第二次投票期
		$return = $this->check ( $subjectid );
		switch ($return) {
			case 0 :
				showmessage ( L ( 'vote_voteyes' ), "?app=vote&controller=index&action=result&subjectid=$subjectid" );
				break;
			case - 1 :
				showmessage ( L ( 'vote_voteyes' ), "?app=vote&controller=index&action=result&subjectid=$subjectid" );
				break;
		}
		if (! is_array ( $_POST ['radio'] )) showmessage ( L ( 'vote_nooption' ), 'blank' );
		$time = TIME;

		$data_arr = array ();
		foreach ( $_POST ['radio'] as $radio ) {
			$data_arr [$radio] = '1';
		}
		$new_data = array2string ( $data_arr ); // 转成字符串存入数据库中
		                                     // 添加到数据库
		$this->vote_data->insert ( array ('userid' => $this->userid,'username' => $this->username,'subjectid' => $subjectid,'time' => $time,'ip' => $this->ip,'data' => $new_data ) );
		// 查询投票奖励点数，并更新会员点数
		$vote_arr = $this->vote->get_one ( array ('subjectid' => $subjectid ) );
		Loader::lib ( 'pay:receipts', false );
		receipts::point ( $vote_arr ['credit'], $this->userid, $this->username, '', 'selfincome', L ( 'vote_post_point' ) );
		// 更新投票人数
		$this->vote->update ( array ('votenumber' => '+=1' ), array ('subjectid' => $subjectid ) );
		showmessage ( L ( 'vote_votesucceed' ), "?app=vote&controller=index&action=result&subjectid=$subjectid" );
	}

	/**
	 * 投票结果显示
	 */
	public function result() {
		$subjectid = abs ( intval ( $_GET ['subjectid'] ) );
		if (! $subjectid) showmessage ( L ( 'vote_novote' ), 'blank' );
		// 取出投票标题
		$subject_arr = $this->vote->get_subject ( $subjectid );
		if (! is_array ( $subject_arr )) showmessage ( L ( 'vote_novote' ), 'blank' );
		extract ( $subject_arr );
		// 获取投票选项
		$options = $this->vote_option->get_options ( $subjectid );

		// 新建一数组用来存新组合数据
		$total = 0;
		$vote_data = array ();
		$vote_data ['total'] = 0; // 所有投票选项总数
		$vote_data ['votes'] = 0; // 投票人数

		// 获取投票结果信息
		$infos = $this->vote_data->select ( array ('subjectid' => $subjectid ), 'data' );
		// 循环每个会员的投票记录
		foreach ( $infos as $subjectid_arr ) {
			extract ( $subjectid_arr );
			$arr = string2array ( $data );
			foreach ( $arr as $key => $values ) {
				$vote_data [$key] += 1;
			}
			$total += array_sum ( $arr );
			$vote_data ['votes'] ++;
		}
		$vote_data ['total'] = $total;
		// SEO设置
		$SEO = seo ('', $subject, $description, $subject );
		include template ( 'vote', 'vote_result' );
	}

	/**
	 *
	 *
	 * 投票前检测
	 *
	 * @param $subjectid 投票ID
	 * @return 返回值 (1:可投票 0: 多投,时间段内不可投票 -1:单投,已投票,不可重复投票)
	 */
	public function check($subjectid) {
		// 查询本投票配置
		$subject_arr = $this->vote->get_subject ( $subjectid );
		if ($subject_arr ['enabled'] == 0) {
			showmessage ( L ( 'vote_votelocked' ), "?app=vote&controller=index&action=result&subjectid=$subjectid" );
		}
		if (date ( "Y-m-d", TIME ) > $subject_arr ['todate']) {
			showmessage ( L ( 'vote_votepassed' ), "?app=vote&controller=index&action=result&subjectid=$subjectid" );
		}
		// 游客是否可以投票
		if ($subject_arr ['allowguest'] == 0) {
			if (! $this->username) {
				showmessage ( L ( 'vote_votenoguest' ), "?app=vote&controller=index&action=result&subjectid=$subjectid" );
			} elseif ($this->groupid == '7') {
				showmessage ( '对不起，不允许邮件待验证用户投票！', "?app=vote&controller=index&action=result&subjectid=$subjectid" );
			}
		}

		// 是否有投票记录
		$user_info = $this->vote_data->select ( array ('subjectid' => $subjectid,'ip' => $this->ip,'username' => $this->username ), '*', '1', ' time DESC' );
		if (! $user_info) {
			return 1;
		} else {
			if ($subject_arr ['interval'] == 0) {
				return - 1;
			}
			if ($subject_arr ['interval'] > 0) {
				$condition = (TIME - $user_info [0] ['time']) / (24 * 3600) > $subject_arr ['interval'] ? 1 : 0;
				return $condition;
			}
		}
	}

}