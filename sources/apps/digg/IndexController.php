<?php
defined('IN_YUNCMS') or exit('No permission resources.');
class IndexController {
	protected  $db,$db_log,$contentid;
	public function __construct() {
		Loader::func('digg:global');
		$this->contentid = isset($_GET['contentid']) && trim(urldecode($_GET['contentid'])) ? trim(urldecode($_GET['contentid'])) : $this->_show_msg(L('illegal_parameters'));
		$this->contentid = safe_replace($this->contentid);
		$this->db = Loader::model('digg_model');
		$this->db_log = Loader::model('digg_log_model');

	}

	/**
	 * Digg首页
	 */
	public function init(){

	}

	/**
	 * Ajax 数据处理
	 */
	public function digg(){
		if(isset($_GET['flag'])){
			$data = $this->update(intval($_GET['flag']));
			echo $_GET['jsoncallback'].'('.$data.');';
			exit;
		}
		$r = $this->db->get($this->contentid);
		echo $_GET['jsoncallback'].'('.json_encode(array('done'=>($this->db_log->is_done($this->contentid) ? true : false), 'supports'=>$r['supports'], 'againsts'=>$r['againsts'])).')';
		exit;
	}

	/**
	 * 更新或插入数据
	 */
	private function update($flag = 1){
		$flag = $flag == 1 ? 1 : 0;
		if($this->db_log->is_done($this->contentid)) return false;
		$r = $this->db->get($this->contentid, '*');
		if($flag){
			$this->db->where(array('id'=>$this->contentid))->update(array('supports'=>'+=1','updatetime'=>TIME));
			$this->db_log->add($this->contentid,'1');
			return $r['supports'] + 1;
		}else{
			$this->db->where(array('id'=>$this->contentid))->update(array('againsts'=>'+=1','updatetime'=>TIME));
			$this->db_log->add($this->contentid,'0');
			return $r['againsts'] + 1;
		}
	}
}