<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-6
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: attachment_index_model.php 97 2013-03-24 09:41:53Z 85825770@qq.com $
 */
defined('IN_YUNCMS') or exit('No permission resources.');
class attachment_index_model extends Model {

    public function __construct() {
        $this->setting = 'default';
        $this->table_name = 'attachment_index';
        parent::__construct();
    }
}