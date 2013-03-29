<?php
defined('IN_YUNCMS') or exit('No permission resources.');
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-8
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: datacall_model.php 110 2013-03-24 10:39:00Z 85825770@qq.com $
 */
class datacall_model extends Model {

	public function __construct() {
        $this->setting = 'default';
        $this->table_name = 'datacall';
        parent::__construct();
    }
}