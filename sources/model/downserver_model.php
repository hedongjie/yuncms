<?php
/**
 * 下载服务器
 * @author Tongle Xu <xutongle@gmail.com> 2012-5-31
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: downserver_model.php 98 2013-03-24 09:42:32Z 85825770@qq.com $
 */
class downserver_model extends Model {
    public $table_name = '';

    public function __construct() {
        $this->setting = 'default';
        $this->table_name = 'downserver';
        parent::__construct ();
    }
}