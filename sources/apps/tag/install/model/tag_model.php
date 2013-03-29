<?php
/**
 * 标签表
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-5
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: tag_model.php 304 2012-11-11 01:22:54Z xutongle $
 */
class tag_model extends Model {

    public function __construct() {
        $this->options = 'default';
        $this->table_name = 'tag';
        parent::__construct();
    }
}