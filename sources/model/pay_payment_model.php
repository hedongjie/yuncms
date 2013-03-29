<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-4
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: pay_payment_model.php 44 2012-11-05 12:17:14Z xutongle $
 */
class pay_payment_model extends Model {

    public $table_name = '';

    public function __construct() {
        $this->setting = 'default';
        $this->table_name = 'pay_payment';
        parent::__construct();
    }
}