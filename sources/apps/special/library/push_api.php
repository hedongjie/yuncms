<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-13
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: push_api.php 883 2012-06-13 06:05:36Z 85825770@qq.com $
 */

class push_api {
    private $special_api;

    public function __construct() {
        $this->special_api = Loader::lib ( 'special:special_api' );
    }

    /**
     * 信息推荐至专题接口
     *
     * @param array $param
     *            属性 请求时，为模型、栏目数组。 例：array('modelid'=>1, 'catid'=>12);
     *            提交添加为二维信息数据 。例：array(1=>array('title'=>'多发发送方法', ....))
     * @param array $arr
     *            参数 表单数据，只在请求添加时传递。
     * @return 返回专题的下拉列表
     */
    public function _push_special($param = array(), $arr = array()) {
        return $this->special_api->_get_special ( $param, $arr );
    }

    public function _get_type($specialid) {
        return $this->special_api->_get_type ( $specialid );
    }
}