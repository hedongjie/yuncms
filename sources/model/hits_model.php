<?php
/**
 * 点击量统计表
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-3
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: hits_model.php 105 2013-03-24 10:35:32Z 85825770@qq.com $
 */
class hits_model extends Model {
    public $table_name = '';
    public function __construct() {
        $this->setting = 'default';
        $this->table_name = 'hits';
        parent::__construct();
    }
}