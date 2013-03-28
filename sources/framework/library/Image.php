<?php
/**
 * 图像处理
 * @author Tongle Xu <xutongle@gmail.com> 2012-11-1
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Image.php 125 2013-03-24 14:55:02Z 85825770@qq.com $
 */
class Image {
	public $w_pct = 100;
	public $w_quality = 80;
	public $w_minwidth = 300;
	public $w_minheight = 300;
	public $thumb_enable;
	public $watermark_enable;
	public $interlace = 0;

	public function __construct($thumb_enable = 0) {
		$this->thumb_enable = $thumb_enable;
		$this->setting = C ( 'attachment' );
		$this->watermark_enable = $this->setting ['watermark_enable'];
		$this->set ( $this->setting ['watermark_img'], $this->setting ['watermark_pos'], $this->setting ['watermark_minwidth'], $this->setting ['watermark_minheight'], $this->setting ['watermark_quality'], $this->setting ['watermark_pct'] );
	}

	/**
	 * 设置参数
	 *
	 * @param unknown_type $w_img
	 * @param unknown_type $w_pos
	 * @param unknown_type $w_minwidth
	 * @param unknown_type $w_minheight
	 * @param unknown_type $w_quality
	 * @param unknown_type $w_pct
	 */
	public function set($w_img, $w_pos, $w_minwidth = 300, $w_minheight = 300, $w_quality = 80, $w_pct = 100) {
		$this->w_img = $w_img;
		$this->w_pos = $w_pos;
		$this->w_minwidth = $w_minwidth;
		$this->w_minheight = $w_minheight;
		$this->w_quality = $w_quality;
		$this->w_pct = $w_pct;
	}

	/**
	 * 获取图片信息
	 *
	 * @param unknown_type $img
	 * @return boolean multitype:unknown number Ambigous <>
	 */
	public function info($img) {
		$imageinfo = getimagesize ( $img );
		if ($imageinfo === false) return false;
		$imagetype = strtolower ( substr ( image_type_to_extension ( $imageinfo [2] ), 1 ) );
		$imagesize = filesize ( $img );
		$info = array ('width' => $imageinfo [0],'height' => $imageinfo [1],'type' => $imagetype,'size' => $imagesize,'mime' => $imageinfo ['mime'] );
		return $info;
	}

	public function getpercent($srcwidth, $srcheight, $dstw, $dsth) {
		if (empty ( $srcwidth ) || empty ( $srcheight ) || ($srcwidth <= $dstw && $srcheight <= $dsth)) $w = $srcwidth;
		$h = $srcheight;
		if ((empty ( $dstw ) || $dstw == 0) && $dsth > 0 && $srcheight > $dsth) {
			$h = $dsth;
			$w = round ( $dsth / $srcheight * $srcwidth );
		} elseif ((empty ( $dsth ) || $dsth == 0) && $dstw > 0 && $srcwidth > $dstw) {
			$w = $dstw;
			$h = round ( $dstw / $srcwidth * $srcheight );
		} elseif ($dstw > 0 && $dsth > 0) {
			if (($srcwidth / $dstw) < ($srcheight / $dsth)) {
				$w = round ( $dsth / $srcheight * $srcwidth );
				$h = $dsth;
			} elseif (($srcwidth / $dstw) > ($srcheight / $dsth)) {
				$w = $dstw;
				$h = round ( $dstw / $srcwidth * $srcheight );
			} else {
				$h = $dstw;
				$w = $dsth;
			}
		}
		$array ['w'] = $w;
		$array ['h'] = $h;
		return $array;
	}

	/**
	 *
	 * @param unknown_type $image
	 * @param unknown_type $filename
	 * @param unknown_type $maxwidth
	 * @param unknown_type $maxheight
	 * @param unknown_type $suffix
	 * @param unknown_type $autocut
	 * @param unknown_type $ftp
	 * @return boolean string
	 */
	public function thumb($image, $filename = '', $maxwidth = 200, $maxheight = 200, $suffix = '', $autocut = 0, $ftp = 0) {
		if (! $this->thumb_enable || ! $this->check ( $image )) return false;
		$info = Image::info ( $image );
		if ($info === false) return false;
		$srcwidth = $info ['width'];
		$srcheight = $info ['height'];
		$pathinfo = pathinfo ( $image );
		$type = $pathinfo ['extension'];
		if (! $type) $type = $info ['type'];
		$type = strtolower ( $type );
		unset ( $info );
		$creat_arr = $this->getpercent ( $srcwidth, $srcheight, $maxwidth, $maxheight );
		$createwidth = $width = $creat_arr ['w'];
		$createheight = $height = $creat_arr ['h'];
		$psrc_x = $psrc_y = 0;
		if ($autocut && $maxwidth > 0 && $maxheight > 0) {
			if ($maxwidth / $maxheight < $srcwidth / $srcheight && $maxheight >= $height) {
				$width = $maxheight / $height * $width;
				$height = $maxheight;
			} elseif ($maxwidth / $maxheight > $srcwidth / $srcheight && $maxwidth >= $width) {
				$height = $maxwidth / $width * $height;
				$width = $maxwidth;
			}
			$createwidth = $maxwidth;
			$createheight = $maxheight;
		}
		$createfun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);
		$srcimg = $createfun ( $image );
		if ($type != 'gif' && function_exists ( 'imagecreatetruecolor' ))
			$thumbimg = imagecreatetruecolor ( $createwidth, $createheight );
		else
			$thumbimg = imagecreate ( $width, $height );

		if (function_exists ( 'imagecopyresampled' ))
			imagecopyresampled ( $thumbimg, $srcimg, 0, 0, $psrc_x, $psrc_y, $width, $height, $srcwidth, $srcheight );
		else
			imagecopyresized ( $thumbimg, $srcimg, 0, 0, $psrc_x, $psrc_y, $width, $height, $srcwidth, $srcheight );
		if ($type == 'gif' || $type == 'png') {
			$background_color = imagecolorallocate ( $thumbimg, 0, 255, 0 ); // 指派一个绿色
			imagecolortransparent ( $thumbimg, $background_color ); // 设置为透明色，若注释掉该行则输出绿色的图
		}
		if ($type == 'jpg' || $type == 'jpeg') imageinterlace ( $thumbimg, $this->interlace );
		$imagefun = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
		if (empty ( $filename )) $filename = substr ( $image, 0, strrpos ( $image, '.' ) ) . $suffix . '.' . $type;
		$imagefun ( $thumbimg, $filename );
		imagedestroy ( $thumbimg );
		imagedestroy ( $srcimg );
		if ($ftp) {
			@unlink ( $image );
		}
		return $filename;
	}

	public function watermark($source, $target = '', $w_pos = '', $w_img = '', $w_text = 'YUNCMS', $w_font = 8, $w_color = '#ff0000') {
		$w_pos = $w_pos ? $w_pos : $this->w_pos;
		$w_img = $w_img ? $w_img : $this->w_img;
		if (! $this->watermark_enable || ! $this->check ( $source )) return false;
		if (! $target) $target = $source;
		$w_img = BASE_PATH . $w_img;
		$source_info = getimagesize ( $source );
		$source_w = $source_info [0];
		$source_h = $source_info [1];
		if ($source_w < $this->w_minwidth || $source_h < $this->w_minheight) return false;
		switch ($source_info [2]) {
			case 1 :
				$source_img = imagecreatefromgif ( $source );
				break;
			case 2 :
				$source_img = imagecreatefromjpeg ( $source );
				break;
			case 3 :
				$source_img = imagecreatefrompng ( $source );
				break;
			default :
				return false;
		}
		if (! empty ( $w_img ) && file_exists ( $w_img )) {
			$ifwaterimage = 1;
			$water_info = getimagesize ( $w_img );
			$width = $water_info [0];
			$height = $water_info [1];
			switch ($water_info [2]) {
				case 1 :
					$water_img = imagecreatefromgif ( $w_img );
					break;
				case 2 :
					$water_img = imagecreatefromjpeg ( $w_img );
					break;
				case 3 :
					$water_img = imagecreatefrompng ( $w_img );
					break;
				default :
					return;
			}
		} else {
			$ifwaterimage = 0;
			$temp = imagettfbbox ( ceil ( $w_font * 2.5 ), 0, FW_PATH . 'resource' . DIRECTORY_SEPARATOR . 'font' . DIRECTORY_SEPARATOR . 'elephant.ttf', $w_text );
			$width = $temp [2] - $temp [6];
			$height = $temp [3] - $temp [7];
			unset ( $temp );
		}
		switch ($w_pos) {
			case 1 :
				$wx = 5;
				$wy = 5;
				break;
			case 2 :
				$wx = ($source_w - $width) / 2;
				$wy = 0;
				break;
			case 3 :
				$wx = $source_w - $width;
				$wy = 0;
				break;
			case 4 :
				$wx = 0;
				$wy = ($source_h - $height) / 2;
				break;
			case 5 :
				$wx = ($source_w - $width) / 2;
				$wy = ($source_h - $height) / 2;
				break;
			case 6 :
				$wx = $source_w - $width;
				$wy = ($source_h - $height) / 2;
				break;
			case 7 :
				$wx = 0;
				$wy = $source_h - $height;
				break;
			case 8 :
				$wx = ($source_w - $width) / 2;
				$wy = $source_h - $height;
				break;
			case 9 :
				$wx = $source_w - $width;
				$wy = $source_h - $height;
				break;
			case 10 :
				$wx = rand ( 0, ($source_w - $width) );
				$wy = rand ( 0, ($source_h - $height) );
				break;
			default :
				$wx = rand ( 0, ($source_w - $width) );
				$wy = rand ( 0, ($source_h - $height) );
				break;
		}
		if ($ifwaterimage) {
			if ($water_info [2] == 3) {
				imagecopy ( $source_img, $water_img, $wx, $wy, 0, 0, $width, $height );
			} else {
				imagecopymerge ( $source_img, $water_img, $wx, $wy, 0, 0, $width, $height, $this->w_pct );
			}
		} else {
			if (! empty ( $w_color ) && (strlen ( $w_color ) == 7)) {
				$r = hexdec ( substr ( $w_color, 1, 2 ) );
				$g = hexdec ( substr ( $w_color, 3, 2 ) );
				$b = hexdec ( substr ( $w_color, 5 ) );
			} else {
				return;
			}
			imagestring ( $source_img, $w_font, $wx, $wy, $w_text, imagecolorallocate ( $source_img, $r, $g, $b ) );
		}

		switch ($source_info [2]) {
			case 1 :
				imagegif ( $source_img, $target );
				break;
			case 2 :
				imagejpeg ( $source_img, $target, $this->w_quality );
				break;
			case 3 :
				imagepng ( $source_img, $target );
				break;
			default :
				return;
		}

		if (isset ( $water_info )) unset ( $water_info );
		if (isset ( $water_img )) imagedestroy ( $water_img );
		unset ( $source_info );
		imagedestroy ( $source_img );
		return true;
	}

	/**
	 * 检测系统环境以及文件
	 *
	 * @param string $image
	 *        	文件路径
	 */
	private function check($image) {
		return extension_loaded ( 'gd' ) && preg_match ( "/\.(jpg|jpeg|gif|png)/i", $image, $m ) && file_exists ( $image ) && function_exists ( 'imagecreatefrom' . ($m [1] == 'jpg' ? 'jpeg' : $m [1]) );
	}

}