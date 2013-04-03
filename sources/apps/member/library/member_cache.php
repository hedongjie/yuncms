<?php
/**
 * 会员缓存
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-8
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: member_cache.php 294 2013-04-02 09:24:57Z 85825770@qq.com $
 */
class member_cache {
	/**
	 * 更新模型缓存
	 */
	public static function update_cache_model() {
		$model_db = Loader::model ( 'model_model' );
		$data = $model_db->where(array ('type' => 2 ))->order('sort ASC')->key('modelid')->select ();
		S ( 'common/member_model', $data );
		if (! defined ( 'MODEL_PATH' )) {
			// 模型原型存储路径
			define ( 'MODEL_PATH', APPS_PATH . 'member' . DIRECTORY_SEPARATOR . 'fields' . DIRECTORY_SEPARATOR );
		}
		if (! defined ( 'CACHE_MODEL_PATH' )) {
			define ( 'CACHE_MODEL_PATH', DATA_PATH . 'model' . DIRECTORY_SEPARATOR ); // 模型缓存路径
		}
		require MODEL_PATH . 'fields.inc.php';
		// 更新内容模型类：表单生成、入库、更新、输出
		$classtypes = array ('form','input','update','output' );
		foreach ( $classtypes as $classtype ) {
			$cache_data = file_get_contents ( MODEL_PATH . 'member_' . $classtype . '.php' );
			$cache_data = str_replace ( '}?>', '', $cache_data );
			foreach ( $fields as $field => $fieldvalue ) {
				if (file_exists ( MODEL_PATH . $field . DIRECTORY_SEPARATOR . $classtype . '.inc.php' )) {
					$cache_data .= file_get_contents ( MODEL_PATH . $field . DIRECTORY_SEPARATOR . $classtype . '.inc.php' );
				}
			}
			$cache_data .= "\r\n } \r\n?>";
			File::write ( CACHE_MODEL_PATH . 'member_' . $classtype . '.php', $cache_data );
		}
		return true;
	}

}