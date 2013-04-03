<?php
defined('IN_YUNCMS') or exit('No permission resources.');
/**
 * 解析DiggID
 * @param $diggid DiggID
 */
function decode_diggid($contentid) {
	return explode('-', $contentid);
}