<?php
/**
 * 前台搜索
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-4
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: IndexController.php 342 2012-11-12 09:31:07Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class IndexController {
	public function __construct() {
		$this->db = Loader::model ( 'search_model' );
		$this->content_db = Loader::model ( 'content_model' );
	}

	/**
	 * 关键词搜索
	 */
	public function init() {
		G ( 'begin_time' );
		// 搜索配置
		$setting = S ( 'search/search' );
		$search_model = S ( 'search/search_model' );
		$type_application = S ( 'search/type_application' );
		if (isset ( $_GET ['q'] )) {
			if (trim ( $_GET ['q'] ) == '') {
				header ( 'Location: ' . SITE_URL . 'index.php?app=search' );
				exit ();
			}
			$typeid = isset ( $_GET ['typeid'] ) && $_GET ['typeid'] > 0 ? intval ( $_GET ['typeid'] ) : 1;
			$time = empty ( $_GET ['time'] ) ? 'all' : trim ( $_GET ['time'] );
			$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
			$pagesize = 10;
			$q = safe_replace ( trim ( $_GET ['q'] ) );
			$q = htmlspecialchars ( strip_tags ( $q ) );
			$q = str_replace ( '%', '', $q ); // 过滤'%'，用户全文搜索
			$search_q = $q; // 搜索原内容
			                // 按时间搜索
			$where = array ();
			if ($time == 'day') {
				$search_time = TIME - 86400;
				$where ['adddate'] = array ('gt',$search_time );
			} elseif ($time == 'week') {
				$search_time = TIME - 604800;
				$where ['adddate'] = array ('gt',$search_time );
			} elseif ($time == 'month') {
				$search_time = TIME - 2592000;
				$where ['adddate'] = array ('gt',$search_time );
			} elseif ($time == 'year') {
				$search_time = TIME - 31536000;
				$where ['adddate'] = array ('gt',$search_time );
			} else {
				$search_time = 0;
			}
			if ($page == 1 && ! $setting ['sphinxenable']) {
				// 精确搜索
				if ($typeid != 0) {
					$where ['typeid'] = $typeid;
				}
				$where ['data'] = array ('like',"%$q%" );
				$commend = $this->db->where ( $where )->find ();
			} else {
				$commend = '';
			}
			// 如果开启sphinx
			if ($setting ['sphinxenable']) {
				$sphinx = Loader::lib ( 'search:search_interface', '', 0 );
				$sphinx = new search_interface ();
				$offset = $pagesize * ($page - 1);
				$res = $sphinx->search ( $q, array ($typeid ), array ($search_time,TIME ), $offset, $pagesize, '@weight desc' );
				$totalnums = $res ['total'];
				// 如果结果不为空
				if (! empty ( $res ['matches'] )) {
					$result = $res ['matches'];
				}
			} else {
				$segment = Loader::lib ( 'Segment' );
				// 分词结果
				$segment_q = $segment->get_keyword ( $segment->split_result ( $q ) );
				// 如果分词结果为空
				if (! empty ( $segment_q )) {
					$sql = array();
					$sql['typeid'] = $typeid;
					$sql = array_merge($where,$sql);
					$sql = $this->db->where($sql)->to_sql();
					$sql = "$sql AND MATCH (`data`) AGAINST ('$segment_q' IN BOOLEAN MODE)";
				} else {
					$sql = array();
					$sql['typeid'] = $typeid;
					$sql = array_merge($where,$sql);
					$sql ['data'] = array ('like',"%$q%" );
				}
				$result = $this->db->where($where)->order('searchid DESC')->listinfo ($page, 10 );
			}

			// 如果开启相关搜索功能
			if ($setting ['relationenble']) {
				// 如果关键词长度在8-16之间，保存关键词作为relation search
				$this->keyword_db = loader::model ( 'search_keyword_model' );
				if (strlen ( $q ) < 17 && strlen ( $q ) > 5 && ! empty ( $segment_q )) {
					$res = $this->keyword_db->where ( array ('keyword' => $q ) )->find();
					if ($res) {
						// 关键词搜索数+1
						$this->keyword_db->where(array ('keyword' => $q ))->update ( array ('searchnums' => '+=1' ) );
					} else {
						// 关键词转换为拼音
						$pinyin = string_to_pinyin ( $q );
						$this->keyword_db->insert ( array ('keyword' => $q,'searchnums' => 1,'data' => $segment_q,'pinyin' => $pinyin ) );
					}
				}
				// 相关搜索
				if (! empty ( $segment_q )) {
					$relation_q = str_replace ( ' ', '%', $segment_q );
				} else {
					$relation_q = $q;
				}
				$relation = $this->keyword_db->order('searchnums DESC')->select ( "MATCH (`data`) AGAINST ('%$relation_q%' IN BOOLEAN MODE)", '*', 10 );
			}

			// 如果结果不为空
			if (! empty ( $result ) || ! empty ( $commend ['id'] )) {
				// 开启sphinx后文章id取法不同
				if ($setting ['sphinxenable']) {
					foreach ( $result as $_v ) {
						$sids [] = $_v ['attrs'] ['id'];
					}
				} else {
					foreach ( $result as $_v ) {
						$sids [] = $_v ['id'];
					}
				}

				if (! empty ( $commend ['id'] )) {
					$sids [] = $commend ['id'];
				}
				$sids = array_unique ( $sids );

				$where = to_sqls ( $sids, '', 'id' );
				// 获取模型id
				$model_type_cache = S ( 'search/type_model' );
				$model_type_cache = array_flip ( $model_type_cache );
				$modelid = $model_type_cache [$typeid];
				// 是否读取其他模块接口
				if ($modelid) {
					$this->content_db->set_model ( $modelid );
					if ($setting ['sphinxenable']) {
						$data = $this->content_db->where( $where)->order( 'id DESC')->listinfo (1, $pagesize );
						$pages = Page::pages ( $totalnums, $page, $pagesize );
					} else {
						$data = $this->content_db->where($where)->select ();
						$pages = $this->db->pages;
						$totalnums = $this->db->number;
					}
					// 如果分词结果为空
					if (! empty ( $segment_q )) {
						$replace = explode ( ' ', $segment_q );
						foreach ( $replace as $replace_arr_v ) {
							$replace_arr [] = '<font color=red>' . $replace_arr_v . '</font>';
						}
						foreach ( $data as $_k => $_v ) {
							$data [$_k] ['title'] = str_replace ( $replace, $replace_arr, $_v ['title'] );
							$data [$_k] ['description'] = str_replace ( $replace, $replace_arr, $_v ['description'] );
						}
					} else {
						foreach ( $data as $_k => $_v ) {
							$data [$_k] ['title'] = str_replace ( $q, '<font color=red>' . $q . '</font>', $_v ['title'] );
							$data [$_k] ['description'] = str_replace ( $q, '<font color=red>' . $q . '</font>', $_v ['description'] );
						}
					}
				} else {
					// 读取专辑搜索接口
					$special_api = Loader::lib ( 'special:search_api' );
					$data = $special_api->get_search_data ( $sids );
				}
			}
			G ( 'search_time' );
			$pages = isset ( $pages ) ? $pages : '';
			$totalnums = isset ( $totalnums ) ? $totalnums : 0;
			$data = isset ( $data ) ? $data : '';
			$execute_time = G ( 'begin_time', 'search_time' );
			include template ( 'search', 'list' );
		} else {
			include template ( 'search', 'index' );
		}
	}
	public function public_get_suggest_keyword() {
		$res = $this->public_suggest_search ( $_GET ['q'] );
		exit ( $res );
		$url = $_GET ['url'] . '&q=' . $_GET ['q'];
		$res = @file_get_contents ( $url );
		if (CHARSET != 'gbk') {
			$res = iconv ( 'gbk', CHARSET, $res );
		}
		echo $res;
	}

	/**
	 * 提示搜索接口
	 */
	public function public_suggest_search($q) {
		// 关键词转换为拼音
		$pinyin = string_to_pinyin ( $q );
		$this->keyword_db = Loader::model ( 'search_keyword_model' );
		$suggest = $this->keyword_db->where(array('pinyin'=>array('like',"$pinyin%")))->order('searchnums DESC')->limit(10)->select ();
		foreach ( $suggest as $v ) {
			echo $v ['keyword'] . "\n";
		}
	}
}