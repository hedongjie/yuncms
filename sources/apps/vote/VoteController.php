<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-7
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: VoteController.php 307 2012-11-11 11:24:56Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
error_reporting ( E_ERROR );
class VoteController extends admin {
	private $db2, $db;
	public function __construct() {
		parent::__construct ();
		$this->M = new_htmlspecialchars ( S ( 'common/vote' ) );
		$this->db = Loader::model ( 'vote_subject_model' );
		$this->db2 = Loader::model ( 'vote_option_model' );
	}

	public function init() {
		$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$infos = $this->db->order('subjectid DESC')->listinfo ($page, 14 );
		$pages = $this->db->pages;
		$big_menu = big_menu ( U ( 'vote/vote/add' ), 'add', L ( 'add_vote' ), 700, 450 );
		include $this->admin_tpl ( 'vote_list' );
	}

	/**
	 * 判断标题重复和验证
	 */
	public function public_name() {
		$subject_title = isset ( $_GET ['subject_title'] ) && trim ( $_GET ['subject_title'] ) ? (CHARSET == 'gbk' ? iconv ( 'utf-8', 'gbk', trim ( $_GET ['subject_title'] ) ) : trim ( $_GET ['subject_title'] )) : exit ( '0' );
		$subjectid = isset ( $_GET ['subjectid'] ) && intval ( $_GET ['subjectid'] ) ? intval ( $_GET ['subjectid'] ) : '';
		$data = array ();
		if ($subjectid) {
			$data = $this->db->where ( array ('subjectid' => $subjectid ) )->field('subject')->find();
			if (! empty ( $data ) && $data ['subject'] == $subject_title) {
				exit ( '1' );
			}
		}
		if ($this->db->where ( array ('subject' => $subject_title ))->field('subject')->find()) {
			exit ( '0' );
		} else {
			exit ( '1' );
		}
	}

	/**
	 * 判断结束时间是否比当前时间小
	 */
	public function checkdate() {
		$nowdate = date ( 'Y-m-d', TIME );
		$todate = $_GET ['todate'];
		if ($todate > $nowdate) {
			exit ( '1' );
		} else {
			exit ( '0' );
		}
	}

	/**
	 * 添加投票
	 */
	public function add() {
		// 读取配置文件
		$data = array ();
		$data = $this->M;
		if (isset ( $_POST ['dosubmit'] )) {
			if (empty ( $_POST ['subject'] ['subject'] )) {
				showmessage ( L ( 'vote_title_noempty' ), U ( 'vote/vote/add' ) );
			}
			if (isset ( $_POST ['subject'] ['maxval'] ) && empty ( $_POST ['subject'] ['maxval'] )) unset ( $_POST ['subject'] ['maxval'] );
			if (isset ( $_POST ['subject'] ['minval'] ) && empty ( $_POST ['subject'] ['minval'] )) unset ( $_POST ['subject'] ['minval'] );
			// 记录选项条数 optionnumber
			$_POST ['subject'] ['optionnumber'] = count ( $_POST ['option'] );
			$_POST ['subject'] ['template'] = $_POST ['vote_subject'] ['vote_tp_template'];

			$subjectid = $this->db->insert ( $_POST ['subject'], true );
			if (! $subjectid) return FALSE; // 返回投票ID值, 以备下面添加对应选项用,不存在返回错误
			                              // 添加选项操作
			$this->db2->add_options ( $_POST ['option'], $subjectid );
			// 生成JS文件
			$this->update_votejs ( $subjectid );
			if (isset ( $_POST ['from_api'] ) && $_POST ['from_api']) {
				showmessage ( L ( 'operation_success' ), U('vote/vote/add'), '100', '', "window.top.$('#voteid').val('" . $subjectid . "');window.top.art.dialog({id:'addvote'}).close();" );
			} else {
				showmessage ( L ( 'operation_success' ), U('vote/vote/add'), '', 'add' );
			}
		} else {
			$show_validator = $show_scroll = $show_header = true;
			@extract ( $data );
			// 模版
			$template_list = template_list ( 0 );
			foreach ( $template_list as $k => $v ) {
				$template_list [$v ['dirname']] = $v ['name'] ? $v ['name'] : $v ['dirname'];
				unset ( $template_list [$k] );
			}
			include $this->admin_tpl ( 'vote_add' );
		}

	}

	/**
	 * 编辑投票
	 */
	public function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			// 验证数据正确性
			$subjectid = intval ( $_GET ['subjectid'] );
			if ($subjectid < 1) return false;
			if (! is_array ( $_POST ['subject'] ) || empty ( $_POST ['subject'] )) return false;
			if ((! $_POST ['subject'] ['subject']) || empty ( $_POST ['subject'] ['subject'] )) return false;
			$this->db2->update_options ( $_POST ['option'] ); // 先更新已有 投票选项,再添加新增加投票选项
			if(isset($_POST ['newoption'])) {
			if (is_array ( $_POST ['newoption'] ) && ! empty ( $_POST ['newoption'] )) {
				$this->db2->add_options ( $_POST ['newoption'], $subjectid );
			}}
			// 模版
			$_POST ['subject'] ['template'] = $_POST ['vote_subject'] ['vote_tp_template'];
			$_POST ['subject'] ['optionnumber'] = count ( $_POST ['option'] ) + (isset($_POST ['newoption']) ? count ( $_POST ['newoption'] ) : 0);
			$this->db->where(array ('subjectid' => $subjectid ))->update ( $_POST ['subject'] ); // 更新投票选项总数
			$this->update_votejs ( $subjectid ); // 生成JS文件
			showmessage ( L ( 'operation_success' ), U('vote/vote/edit'), '', 'edit' );
		} else {
			$show_validator = $show_scroll = $show_header = true;
			// 解出投票内容
			$info = $this->db->getby_subjectid ($_GET ['subjectid'] );
			if (! $info) showmessage ( L ( 'operation_success' ) );
			extract ( $info );
			// 解出投票选项
			$this->db2 = Loader::model ( 'vote_option_model' );
			$options = $this->db2->get_options ( $_GET ['subjectid'] );
			$template_list = template_list ( 0 );
			foreach ( $template_list as $k => $v ) {
				$template_list [$v ['dirname']] = $v ['name'] ? $v ['name'] : $v ['dirname'];
				unset ( $template_list [$k] );
			}
			include $this->admin_tpl ( 'vote_edit' );
		}
	}

	/**
	 * 删除投票
	 *
	 * @param intval $sid
	 */
	public function delete() {
		if ((! isset ( $_GET ['subjectid'] ) || empty ( $_GET ['subjectid'] )) && (! isset ( $_POST ['subjectid'] ) || empty ( $_POST ['subjectid'] ))) {
			showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		} else {
			if (is_array ( $_POST ['subjectid'] )) {
				foreach ( $_POST ['subjectid'] as $subjectid_arr ) {
					// 删除对应投票的选项
					$this->db2 = Loader::model ( 'vote_option_model' );
					$this->db2->del_options ( $subjectid_arr );
					$this->db->where(array ('subjectid' => $subjectid_arr ))->delete (  );
				}
				showmessage ( L ( 'operation_success' ), U ( 'vote/vote' ) );
			} else {
				$subjectid = intval ( $_GET ['subjectid'] );
				if ($subjectid < 1) return false;
				// 删除对应投票的选项
				$this->db2 = Loader::model ( 'vote_option_model' );
				$this->db2->del_options ( $subjectid );

				// 删除投票
				$this->db->where(array ('subjectid' => $subjectid ))->delete (  );
				$result = $this->db->where(array ('subjectid' => $subjectid ))->delete (  );
				if ($result) {
					showmessage ( L ( 'operation_success' ), U ( 'vote/vote' ) );
				} else {
					showmessage ( L ( "operation_failure" ), U ( 'vote/vote' ) );
				}
			}

			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		}
	}

	/**
	 * 说明:删除对应投票选项
	 *
	 * @param
	 *        	$optionid
	 */
	public function del_option() {
		$result = $this->db2->del_option ( $_GET ['optionid'] );
		if ($result) {
			echo 1;
		} else {
			echo 0;
		}
	}

	/**
	 * 投票模块配置
	 */
	public function setting() {
		// 读取配置文件
		$data = array ();
		// 更新模型数据库,重设setting 数据.
		$m_db = Loader::model ( 'application_model' );
		$now_seting = $m_db->get_setting('vote');
		if (isset ( $_POST ['dosubmit'] )) {
			S( 'common/vote', $_POST ['setting'] );
			$m_db->set_setting('vote',$_POST ['setting']);
			showmessage ( L ( 'setting_updates_successful' ), U ( 'vote/vote/init' ) );
		} else {
			@extract ( $now_seting );
			// 模版
			$template_list = template_list ( 0 );
			foreach ( $template_list as $k => $v ) {
				$template_list [$v ['dirname']] = $v ['name'] ? $v ['name'] : $v ['dirname'];
				unset ( $template_list [$k] );
			}
			include $this->admin_tpl ( 'setting' );
		}
	}

	/**
	 * 检查表单数据
	 *
	 * @param Array $data
	 * @return Array
	 */
	private function check($data = array()) {
		if ($data ['name'] == '') showmessage ( L ( 'name_plates_not_empty' ) );
		if (! isset ( $data ['width'] ) || $data ['width'] == 0) {
			showmessage ( L ( 'plate_width_not_empty' ), HTTP_REFERER );
		} else {
			$data ['width'] = intval ( $data ['width'] );
		}
		if (! isset ( $data ['height'] ) || $data ['height'] == 0) {
			showmessage ( L ( 'plate_height_not_empty' ), HTTP_REFERER );
		} else {
			$data ['height'] = intval ( $data ['height'] );
		}
		return $data;
	}

	/**
	 * 投票结果统计
	 */
	public function statistics() {
		$subjectid = intval ( $_GET ['subjectid'] );
		if (! $subjectid) {
			showmessage ( L ( 'illegal_operation' ) );
		}
		$show_validator = $show_scroll = $show_header = true;
		// 获取投票信息
		$sdb = Loader::model ( 'vote_data_model' ); // 加载投票统计的数据模型
		$infos = $sdb->where(array('subjectid'=>$subjectid))->field('data')->select (  );
		// 新建一数组用来存新组合数据
		$total = 0;
		$vote_data = array ();
		$vote_data ['total'] = 0; // 所有投票选项总数
		$vote_data ['votes'] = 0; // 投票人数
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
		// 取投票选项
		$options = $this->db2->get_options ( $subjectid );
		include $this->admin_tpl ( 'vote_statistics' );
	}

	/**
	 * 投票会员统计
	 */
	public function statistics_userlist() {
		$subjectid = $_GET ['subjectid'];
		if (empty ( $subjectid )) return false;
		$show_validator = $show_scroll = $show_header = true;
		$where = array ("subjectid" => $subjectid );
		$sdb = Loader::model ( 'vote_data_model' ); // 调用统计的数据模型
		$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$infos = $sdb->where($where)->order('time DESC')->listinfo ($page, 7 );
		$pages = $sdb->pages;
		include $this->admin_tpl ( 'vote_statistics_userlist' );
	}

	/**
	 * 说明:生成JS投票代码
	 *
	 * @param $subjectid 投票ID
	 */
	function update_votejs($subjectid) {
		if (! isset ( $subjectid ) || intval ( $subjectid ) < 1) return false;
		// 解出投票内容
		$info = $this->db->get_subject ( $subjectid );
		if (! $info) showmessage ( L ( 'not_vote' ) );
		extract ( $info );
		// 解出投票选项
		$options = $this->db2->get_options ( $subjectid );
		ob_start ();
		include template ( 'vote', $template );
		$voteform = ob_get_contents ();
		ob_clean ();
		@file_put_contents ( DATA_PATH . 'vote_js/vote_' . $subjectid . '.js', $this->format_js ( $voteform ) );

	}

	/**
	 * 更新js
	 */
	public function create_js() {
		$infos = $this->db->select ();
		if (is_array ( $infos )) {
			foreach ( $infos as $subjectid_arr ) {
				$this->update_votejs ( $subjectid_arr ['subjectid'] );
			}
		}
		showmessage ( L ( 'operation_success' ), U ( 'vote/vote' ) );
	}

	/**
	 * 说明:对字符串进行处理
	 *
	 * @param $string 待处理的字符串
	 * @param $isjs 是否生成JS代码
	 */
	public function format_js($string, $isjs = 1) {
		$string = addslashes ( str_replace ( array ("\r","\n" ), array ('','' ), $string ) );
		return $isjs ? 'document.write("' . $string . '");' : $string;
	}

	/**
	 * 投票调用代码
	 */
	public function public_call() {
		$_GET ['subjectid'] = intval ( $_GET ['subjectid'] );
		if (! $_GET ['subjectid']) showmessage ( L ( 'illegal_action' ), HTTP_REFERER, '', 'call' );
		$r = $this->db->getby_subjectid ( $_GET ['subjectid'] );
		include $this->admin_tpl ( 'vote_call' );
	}

	/**
	 * 信息选择投票接口
	 */
	public function public_get_votelist() {
		$infos = $this->db->order('subjectid DESC')->listinfo ( 1, 10 );
		$target = isset ( $_GET ['target'] ) ? $_GET ['target'] : '';
		include $this->admin_tpl ( 'get_votelist' );
	}

}