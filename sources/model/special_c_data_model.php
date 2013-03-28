<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-21
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: special_c_data_model.php 107 2013-03-24 10:37:31Z 85825770@qq.com $
 */
class special_c_data_model extends Model {

    public $table_name;

    public function __construct() {
        $this->setting = 'default';
        $this->table_name = 'special_c_data';
        parent::__construct();
    }
}