<?php
defined('IN_YUNCMS') or exit('No permission resources.');
/**
 * 搜索关键词模型
 * @author Tongle Xu <xutongle@gmail.com> 2012-7-6
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: search_keyword_model.php 102 2013-03-24 10:33:16Z 85825770@qq.com $
 */
class search_keyword_model extends Model {
    public $table_name = '';
    public function __construct() {
        $this->setting = 'default';
        $this->table_name = 'search_keyword';
        parent::__construct();
    }
}