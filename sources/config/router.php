<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-10
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: router.php 2 2013-01-14 07:14:05Z xutongle $
 */
return array (
		'default' => array ('application' => 'content','controller' => 'index','action' => 'init','data' => array ('POST' => array ('catid' => 1 ),'GET' => array ('contentid' => 1 ) ) ),
		'cli' => array ('controller' => 'index','action' => 'init' ),
);