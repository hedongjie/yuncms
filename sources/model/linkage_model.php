<?php
defined('IN_YUNCMS') or exit('No permission resources.');
/**
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-27
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: linkage_model.php 32 2012-11-05 11:36:54Z xutongle $
 */
class linkage_model extends Model {

    public function __construct() {
        $this->setting = 'default';
        $this->table_name = 'linkage';
        parent::__construct();
    }

}