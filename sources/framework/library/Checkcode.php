<?php
/**
 * 验证码类
 * @author Tongle Xu <xutongle@gmail.com> 2012-11-1
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Checkcode.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Checkcode {

	/**
	 * 输出类型
	 *
	 * 可以是png,gif,jpeg
	 *
	 * @var string
	 */
	protected $image_type = 'png';

	// 默认配置
	public $config = array (
			'width' => 150,
			'height' => 50,
			'complexity' => 4,
			'background' => '',
			'fonts' => array (
					'DejaVuSerif.ttf',
					'FetteSteinschrift.ttf',
					'STXINWEI.TTF'
			),
			'promote' => false,
			'life' => 1800
	);

	protected $image;

	protected $response = '';

	function __construct($config = false) {
		if (is_array ( $config )) {
			$this->config = array_merge ( $this->config, $config );
		}
	}

	public function render() {
		if (empty ( $this->response )) {
			$this->creat_code ();
		}
		$this->image_create ( $this->config ['background'] );
		if (empty ( $this->config ['background'] )) {
			$color1 = imagecolorallocate ( $this->image, mt_rand ( 0, 100 ), mt_rand ( 0, 100 ), mt_rand ( 0, 100 ) );
			$color2 = imagecolorallocate ( $this->image, mt_rand ( 0, 100 ), mt_rand ( 0, 100 ), mt_rand ( 0, 100 ) );
			$this->image_gradient ( $color1, $color2 );
		}
		for($i = 0, $count = mt_rand ( 10, $this->config ['complexity'] * 3 ); $i < $count; $i ++) {
			$color = imagecolorallocatealpha ( $this->image, mt_rand ( 0, 255 ), mt_rand ( 0, 255 ), mt_rand ( 0, 255 ), mt_rand ( 80, 120 ) );
			$size = mt_rand ( 5, $this->config ['height'] / 3 );
			imagefilledellipse ( $this->image, mt_rand ( 0, $this->config ['width'] ), mt_rand ( 0, $this->config ['height'] ), $size, $size, $color );
		}
		$default_size = min ( $this->config ['width'], $this->config ['height'] * 2 ) / strlen ( $this->response );
		$spacing = ( int ) ($this->config ['width'] * 0.9 / strlen ( $this->response ));
		$color_limit = mt_rand ( 96, 160 );
		$chars = 'ABEFGJKLPQRTVY';
		for($i = 0, $strlen = strlen ( $this->response ); $i < $strlen; $i ++) {
			$font = FW_PATH . 'resource' . DIRECTORY_SEPARATOR . 'font' . DIRECTORY_SEPARATOR . $this->config ['fonts'] [array_rand ( $this->config ['fonts'] )];
			$angle = mt_rand ( - 40, 20 );
			$size = $default_size / 10 * mt_rand ( 8, 12 );
			if (! function_exists ( 'imageftbbox' )) {
				throw new Core_Exception ( 'function imageftbbox not exist.' );
			}
			$box = imageftbbox ( $size, $angle, $font, $this->response [$i] );
			$x = $spacing / 4 + $i * $spacing;
			$y = $this->config ['height'] / 2 + ($box [2] - $box [5]) / 4;
			$color = imagecolorallocate ( $this->image, mt_rand ( 150, 255 ), mt_rand ( 200, 255 ), mt_rand ( 0, 255 ) );
			imagefttext ( $this->image, $size, $angle, $x, $y, $color, $font, $this->response [$i] );
			$text_color = imagecolorallocatealpha ( $this->image, mt_rand ( $color_limit + 8, 255 ), mt_rand ( $color_limit + 8, 255 ), mt_rand ( $color_limit + 8, 255 ), mt_rand ( 70, 120 ) );
			$char = substr ( $chars, mt_rand ( 0, 14 ), 1 );
			imagettftext ( $this->image, $size * 1.4, mt_rand ( - 45, 45 ), ($x - (mt_rand ( 5, 10 ))), ($y + (mt_rand ( 5, 10 ))), $text_color, $font, $char );
		}
		return $this->image_render ();
	}

	protected function image_render() {
		header ( "Cache-Control:no-cache,must-revalidate" );
		header ( "Pragma:no-cache" );
		header ( 'Content-Type: image/' . $this->image_type );
		header ( "Connection:close" );
		$function = 'image' . $this->image_type;
		$function ( $this->image );
		imagedestroy ( $this->image );
	}

	/**
	 * 创建图片
	 *
	 * @param string $background
	 */
	protected function image_create($background = null) {
		if (! function_exists ( 'imagegd2' )) {
			throw new LeapsException ( '[captcha.requires_GD2' );
		}
		$this->image = imagecreatetruecolor ( $this->config ['width'], $this->config ['height'] );
		if (! empty ( $background )) {
			// 设置背景色
			$background = imagecolorallocate ( $this->image, hexdec ( substr ( $background, 1, 2 ) ), hexdec ( substr ( $background, 3, 2 ) ), hexdec ( substr ( $background, 5, 2 ) ) );
			// 画一个柜形，设置背景颜色。
			imagefilledrectangle ( $this->image, 0, $this->config ['height'], $this->config ['width'], 0, $background );
		}
	}

	protected function image_gradient($color1, $color2, $direction = null) {
		$directions = array (
				'horizontal',
				'vertical'
		);
		if (! in_array ( $direction, $directions )) {
			$direction = $directions [array_rand ( $directions )];
			if (mt_rand ( 0, 1 ) === 1) {
				$temp = $color1;
				$color1 = $color2;
				$color2 = $temp;
			}
		}

		$color1 = imagecolorsforindex ( $this->image, $color1 );
		$color2 = imagecolorsforindex ( $this->image, $color2 );

		$steps = ($direction === 'horizontal') ? $this->config ['width'] : $this->config ['height'];

		$r1 = ($color1 ['red'] - $color2 ['red']) / $steps;
		$g1 = ($color1 ['green'] - $color2 ['green']) / $steps;
		$b1 = ($color1 ['blue'] - $color2 ['blue']) / $steps;

		$i = null;
		if ($direction === 'horizontal') {
			$x1 = & $i;
			$y1 = 0;
			$x2 = & $i;
			$y2 = $this->config ['height'];
		} else {
			$x1 = 0;
			$y1 = & $i;
			$x2 = $this->config ['width'];
			$y2 = & $i;
		}
		for($i = 0; $i <= $steps; $i ++) {
			$r2 = $color1 ['red'] - floor ( $i * $r1 );
			$g2 = $color1 ['green'] - floor ( $i * $g1 );
			$b2 = $color1 ['blue'] - floor ( $i * $b1 );
			$color = imagecolorallocate ( $this->image, $r2, $g2, $b2 );
			imageline ( $this->image, $x1, $y1, $x2, $y2, $color );
		}
	}

	/**
	 * 生成随机验证码。
	 */
	function creat_code() {
		$this->response = random ( $this->config ['complexity'], 4 );
		Loader::session ();
		$_SESSION ['code'] = strtolower ( $this->response );
	}
}