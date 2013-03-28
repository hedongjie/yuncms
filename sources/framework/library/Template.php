<?php
/**
 * YUNCMS 模板类
 * @author Tongle Xu <xutongle@gmail.com> 2012-11-1
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Template.php 497 2012-12-02 15:28:36Z xutongle $
 */
final class Template extends Core_View {
	public static $object;
	public $style; // 当前使用的模板
	public static function &instance() {
		if (empty ( self::$object )) self::$object = new self ();
		return self::$object;
	}

	/**
	 * 更新视图缓存
	 *
	 * @param $tplfile 视图原文件路径
	 * @param $compiledtplfile 编译完成后，写入文件名
	 * @return $strlen 长度
	 */
	public function refresh($tplfile, $compiledtplfile) {
		$str = @file_get_contents ( $tplfile );
		$str = $this->template_parse ( $str, $compiledtplfile );
		$strlen = file_put_contents ( $compiledtplfile, $str );
		@chmod ( $compiledtplfile, 0777 );
		return $strlen;
	}

	/**
	 * 解析模板
	 *
	 * @param $str 模板内容
	 * @return ture
	 */
	public function template_parse($str, $compiledtplfile) {
		$str = $this->parse ( $str, $compiledtplfile );
		$str = "<?php\n//Leaps Cache @ " . date ( 'Y-m-d H:i:s' ) . "\n" . "defined('LEAPS_VERSION') or exit('No permission resources.');\nerror_reporting ( E_ERROR );\n\$Head = S('common/common');\n\$CSS_PATH = SKIN_PATH.'$this->style/css/';\n\$IMG_PATH = SKIN_PATH.'$this->style/images/';\n\$JS_PATH = SKIN_PATH.'$this->style/js/';\n?>\n" . $str;
		$str = preg_replace ( "/\{template\s+(.+)\}/", "<?php include template(\\1); ?>", $str );
		$str = preg_replace ( "/\{yun:(\w+)\s+([^}]+)\}/ie", "self::yun_tag('$1','$2', '$0')", $str );
		$str = preg_replace ( "/\{\/yun\}/ie", "self::end_yun_tag()", $str );
		$str = preg_replace ( "/\<\/title>/", base64_decode ( 'LSBQb3dlcmVkIGJ5IFlVTkNNUw==' ) . "</title>", $str );
		$str = preg_replace ( "/\<\/body\>/", "\r\n<div style='display:none'><?php echo tjcode(); ?></div>\r\n</body>", $str );
		$str = preg_replace ( "/\<\/body\>/", "\r\n<?php echo Sonline(); ?>\r\n</body>", $str );
		return $str;
	}

	/**
	 * 解析YUN标签
	 *
	 * @param string $op
	 *        	操作方式
	 * @param string $data
	 *        	参数
	 * @param string $html
	 *        	匹配到的所有的HTML代码
	 */
	public static function yun_tag($op, $data, $html) {
		preg_match_all ( "/([a-z]+)\=[\"]?([^\"]+)[\"]?/i", stripslashes ( $data ), $matches, PREG_SET_ORDER );
		$arr = array ('do','num','cache','page','pagesize','urlrule','return','start' );
		$tools = array ('json','xml','block','get' );
		$datas = array ();
		$tag_id = md5 ( stripslashes ( $html ) );
		// 可视化条件
		$str_datas = 'op=' . $op . '&tag_md5=' . $tag_id;
		foreach ( $matches as $v ) {
			$str_datas .= $str_datas ? "&$v[1]=" . ($op == 'block' && strpos ( $v [2], '$' ) === 0 ? $v [2] : urlencode ( $v [2] )) : "$v[1]=" . (strpos ( $v [2], '$' ) === 0 ? $v [2] : urlencode ( $v [2] ));
			if (in_array ( $v [1], $arr )) {
				$$v [1] = $v [2];
				continue;
			}
			$datas [$v [1]] = $v [2];
		}
		$str = '';
		$num = isset ( $num ) && intval ( $num ) ? intval ( $num ) : 20;
		$cache = isset ( $cache ) && intval ( $cache ) ? intval ( $cache ) : 0;
		$return = isset ( $return ) && trim ( $return ) ? trim ( $return ) : 'data';
		if (! isset ( $urlrule )) $urlrule = '';
		if (! empty ( $cache ) && ! isset ( $page )) {
			$str .= '$tag_cache_name = md5(implode(\'&\',' . self::arr_to_html ( $datas ) . ').\'' . $tag_id . '\');if(!$' . $return . ' = S($tag_cache_name)){';
		}
		if (in_array ( $op, $tools )) {
			switch ($op) {
				case 'json' :
					if (isset ( $datas ['url'] ) && ! empty ( $datas ['url'] )) {
						$str .= '$json = @file_get_contents(\'' . $datas ['url'] . '\');';
						$str .= '$' . $return . ' = json_decode($json, true);';
					}
					break;

				case 'xml' :
					$str .= '$xml = Loader::lib(\'Xml\');';
					$str .= '$xml_data = @file_get_contents(\'' . $datas ['url'] . '\');';
					$str .= '$' . $return . ' = $xml->xml_unserialize($xml_data);';
					break;

				case 'get' :
					if ($datas ['dbsource']) {
						$dbsource = S ( 'common/dbsource' );
						if (isset ( $dbsource [$datas ['dbsource']] )) {
							$str .= '$get_db = new get_model(' . var_export ( $dbsource, true ) . ', \'' . $datas ['dbsource'] . '\');';
						} else {
							return false;
						}
					} else {
						$str .= '$get_db = Loader::model("get_model");';
					}
					$num = isset ( $num ) && intval ( $num ) > 0 ? intval ( $num ) : 20;
					if (isset ( $start ) && intval ( $start )) {
						$limit = intval ( $start ) . ',' . $num;
					} else {
						$limit = $num;
					}
					if (isset ( $page )) {
						$str .= '$pagesize = ' . $num . ';';
						$str .= '$page = isset(' . $page . ') && intval(' . $page . ') ? intval(' . $page . ') : 1;if($page<=0){$page=1;}';
						$str .= '$offset = ($page - 1) * $pagesize;';
						$limit = '$offset,$pagesize';
						if ($sql = preg_replace ( '/select([^from].*)from/i', "SELECT COUNT(*) as count FROM ", $datas ['sql'] )) {
							$str .= '$res = $get_db->sql_query("' . $sql . '");$pages=Page::pages($s[\'count\'], $page, $pagesize, $urlrule);';
						}
					}
					$str .= '$res = $get_db->sql_query("' . $datas ['sql'] . ' LIMIT ' . $limit . '");$' . $return . ' = $res;unset($res);';
					break;

				case 'block' :
					$str .= '$block_tag = Loader::lib(\'block:block_tag\');';
					$str .= 'echo $block_tag->yun_tag(' . self::arr_to_html ( $datas ) . ');';
					break;
			}
		} else {
			if (! isset ( $do ) || empty ( $do )) return false;
			if (application_exists ( $op ) && file_exists ( APPS_PATH . $op . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . $op . '_tag.php' )) {
				$str .= '$' . $op . '_tag = Loader::lib("' . $op . ':' . $op . '_tag");if (method_exists($' . $op . '_tag, \'' . $do . '\')) {';
				if (isset ( $start ) && intval ( $start )) {
					$datas ['limit'] = intval ( $start ) . ',' . $num;
				} else {
					$datas ['limit'] = $num;
				}
				if (isset ( $page )) {
					$str .= '$pagesize = ' . $num . ';';
					$str .= '$page = isset(' . $page . ') ? intval(' . $page . ') : 1;if($page<=0){$page=1;}';
					$str .= '$offset = ($page - 1) * $pagesize;';
					$datas ['limit'] = '$offset.",".$pagesize';
					$datas ['do'] = $do;
					$str .= '$' . $op . '_total = $' . $op . '_tag->count(' . self::arr_to_html ( $datas ) . ');';
					$str .= '$pages = Page::pages($' . $op . '_total, $page, $pagesize, isset($urlrule) ? $urlrule:\'\');';
				}
				$str .= '$' . $return . ' = $' . $op . '_tag->' . $do . '(' . self::arr_to_html ( $datas ) . ');';
				$str .= '}';
			}
		}
		if (! empty ( $cache ) && ! isset ( $page )) {
			$str .= 'if(!empty($' . $return . ')){S(\'tpl_data/\'. $tag_cache_name, $' . $return . ','.$cache.');}';
			$str .= '}';
		}
		return "<" . "?php if(defined('IN_ADMIN')  && !defined('HTML')) {echo \"<div class=\\\"admin_piao\\\" yun_action=\\\"" . $op . "\\\" data=\\\"" . $str_datas . "\\\"><a href=\\\"javascript:void(0);\\\" class=\\\"admin_piao_edit\\\">" . ($op == 'block' ? L ( 'block_add' ) : L ( 'edit' )) . "</a>\";}" . $str . "?" . ">";
	}

	/**
	 * YUN标签结束
	 */
	private static function end_yun_tag() {
		return '<?php if(defined(\'IN_ADMIN\') && !defined(\'HTML\')) {echo \'</div>\';}?>';
	}

	/**
	 * 转换数据为HTML代码
	 *
	 * @param array $data
	 *        	数组
	 */
	private static function arr_to_html($data) {
		if (is_array ( $data )) {
			$str = 'array(';
			foreach ( $data as $key => $val ) {
				if (is_array ( $val )) {
					$str .= "'$key'=>" . self::arr_to_html ( $val ) . ",";
				} else {
					if (strpos ( $val, '$' ) === 0) {
						$str .= "'$key'=>$val,";
					} else {
						$str .= "'$key'=>'" . new_addslashes ( $val ) . "',";
					}
				}
			}
			return $str . ')';
		}
		return false;
	}
}