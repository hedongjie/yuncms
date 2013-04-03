<?php
defined('IN_YUNCMS') or exit('No permission resources.');
/**
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-13
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: mood_model.php 199 2013-03-29 23:07:40Z 85825770@qq.com $
 */

class mood_model extends model {

    public function __construct() {
        $this->setting = 'default';
        $this->table_name = 'mood';
        parent::__construct();
    }
}