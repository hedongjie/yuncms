<?php
defined('IN_YUNCMS') or exit('No permission resources.');
/**
 * RSS订阅
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-12
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: RssController.php 71 2012-11-05 12:51:29Z xutongle $
 */
class RssController {

    private $db;

    public function __construct() {
        $this->db = Loader::model('content_model');
        $this->rssid = isset($_GET['rssid']) ? intval($_GET['rssid']) : '';
    }

    public function init() {
        $siteurl = substr(SITE_URL,0,-1);
        if(empty($this->rssid)) {
            $catid = isset($_GET['catid']) ? intval($_GET['catid']) : '0';
            $CATEGORYS = S('common/category_content');
            $subcats = subcat($catid,0,1);
            foreach ($CATEGORYS as $r) if($r['parentid'] == 0) $channel[] = $r;
            include template('content','rss');
        } else {
            $CATEGORYS = S('common/category_content');
            $CAT = $CATEGORYS[$this->rssid];
            if(count($CAT) == 0) showmessage(L('missing_part_parameters'),'blank');
            $sitedomain = SITE_URL;  //获取站点域名
            $MODEL = S('common/model');
            $modelid = $CAT['modelid'];
            $encoding   =  CHARSET;
            $about      =  SITE_PROTOCOL.SITE_HOST;
            $title      =  $CAT['catname'];
            $description = $CAT['description'];
            $content_html = $CAT['content_ishtml'];
            $image_link =  "<![CDATA[".$CAT['image']."]]> ";
            $category   =  '';
            $cache      =  60;
            $rssfile    = new RSSBuilder($encoding, $about, $title, $description, $image_link, $category, $cache);
            $publisher  =  '';
            $creator    =  SITE_PROTOCOL.SITE_HOST;
            $date       =  date('r');
            $rssfile->addDCdata($publisher, $creator, $date);
            $ids = explode(",",$CAT['arrchildid']);
            if(count($ids) == 1 && in_array($this->rssid, $ids)) {
                $sql .= "`catid` = '$this->rssid' AND `status` = '99'";
            } else {
                $sql .= get_sql_catid('category_content',$this->rssid)." AND `status` = '99'";
            }

            $this->db->table_name = $this->db->get_prefix().$MODEL[$modelid]['tablename'];
            $info = $this->db->select($sql,'`title`, `description`, `url`, `inputtime`, `thumb`, `keywords`','0,20','id DESC');
            $siteinfo = S('common/common');
            foreach ($info as $r) {
                //添加项目
                if(!empty($r['thumb'])) $img = "<img src=".thumb($r['thumb'], 150, 150)." border='0' /><br />";else $img = '';
                $about          =  $link = (strpos($r['url'], 'http://') !== FALSE || strpos($r['url'], 'https://') !== FALSE) ? "<![CDATA[".$r['url']."]]> " : (($content_html == 1) ? "<![CDATA[".substr($sitedomain,0,-1).$r['url']."]]> " : "<![CDATA[".substr(SITE_URL,0,-1).$r['url']."]]> ");
                $title          =   "<![CDATA[".$r['title']."]]> ";
                $description    =  "<![CDATA[".$img.$r['description']."]]> ";
                $subject        =  '';
                $date           =  date('Y-m-d H:i:s' , $r['inputtime']);
                $author         =  $siteinfo['site_name'].' '.SITE_PROTOCOL.SITE_URL;
                $comments       =  '';//注释;

                $rssfile->addItem($about, $title, $link, $description, $subject, $date,	$author, $comments, $image);
            }
            $version = '2.00';
            $rssfile->outputRSS($version);
        }
    }
}