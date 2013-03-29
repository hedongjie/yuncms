<?php
/**
 * 关联连接表
 * @author Tongle Xu <xutongle@gmail.com> 2012-5-31
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: keylink_model.php 256 2012-11-08 01:49:40Z xutongle $
 */
class keylink_model extends Model {
    public $table_name = '';

    public function __construct() {
        $this->setting = 'default';
        $this->table_name = 'keylink';
        parent::__construct ();
    }
}