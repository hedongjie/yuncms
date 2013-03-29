<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-6
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: block_history_model.php 103 2013-03-24 10:34:20Z 85825770@qq.com $
 */
class block_history_model extends Model {

    public function __construct() {
        $this->setting = 'default';
        $this->table_name = 'block_history';
        parent::__construct();
    }
}