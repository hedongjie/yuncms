<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
// 模型原型存储路径
define ( 'MODEL_PATH', APPS_PATH . 'feedback' . DIRECTORY_SEPARATOR . 'fields' . DIRECTORY_SEPARATOR );
// 模型缓存路径
define ( 'CACHE_MODEL_PATH', DATA_PATH . 'model' . DIRECTORY_SEPARATOR );
/**
 * 更新form表单模型类
 *
 * @author
 *
 *
 */
class feedback {

	public function __construct() {

	}

	/**
	 * 更新模型缓存方法
	 */
	public function public_cache() {
		require MODEL_PATH . 'fields.inc.php';
		// 更新内容模型类：表单生成、入库、更新、输出
		$classtypes = array ('form','input','update','output' );
		foreach ( $classtypes as $classtype ) {
			$cache_data = file_get_contents ( MODEL_PATH . 'feedback_' . $classtype . '.php' );
			$cache_data = str_replace ( '}?>', '', $cache_data );
			foreach ( $fields as $field => $fieldvalue ) {
				if (file_exists ( MODEL_PATH . $field . DIRECTORY_SEPARATOR . $classtype . '.inc.php' )) {
					$cache_data .= file_get_contents ( MODEL_PATH . $field . DIRECTORY_SEPARATOR . $classtype . '.inc.php' );
				}
			}
			$cache_data .= "\r\n } \r\n?>";
			file_put_contents ( CACHE_MODEL_PATH . 'feedback_' . $classtype . '.php', $cache_data );
			@chmod ( CACHE_MODEL_PATH . 'feedback_' . $classtype . '.php', 0777 );
		}
		return true;
	}
}
?>