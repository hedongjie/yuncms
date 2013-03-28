<?php
/**
 * 会员组
 * @author Tongle Xu <xutongle@gmail.com> 2012-5-31
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: member_group_model.php 92 2013-03-23 07:56:26Z 85825770@qq.com $
 */
class member_group_model extends Model {

    public function __construct() {
        $this->setting = 'default';
        $this->table_name = 'member_group';
        parent::__construct();
    }
}