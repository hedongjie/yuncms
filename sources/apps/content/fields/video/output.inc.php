    function video($field, $value, $isplay = 0) {
        global $modelid, $id;
        $n = intval ( $_GET ['n'] );
        $getf = htmlspecialchars ( $_GET ['f'] );
        $id = $id ? $id : ($this->id ? $this->id : ($_POST ['id'] ? $_POST ['id'] : intval ( $_GET ['id'] )));
        // 修正url
        if (! $this->data ['url'] && $id) {
            $this->cdb = Loader::model ( 'content_model' );
            $this->cdb->table_name = str_ireplace ( '_data', '', $this->cdb->table_name );
            $ra = $this->cdb->get_one ( array ('id' => $id ), 'url' );
        }
        $modelid = $modelid ? $modelid : $this->modelid;
        $this->fields = $this->fields ? $this->fields : S ( 'model/model_field_' . $modelid ); // 防止动态播放页有静态播放地址
        $this->data ['url'] = $this->data ['url'] ? $this->data ['url'] : $ra ['url']; // here get url!
        $cpath = str_replace ( SITE_URL, SOURCE_PATH, substr ( $this->data ['url'], 0, strripos ( $this->data ['url'], '/' ) ) . '/' );
        $catid = $this->catid ? $this->catid : intval ( $_GET ['catid'] );
        $CATEGORYS = $this->categorys;
        extract ( $this->data );
        extract ( string2array ( $this->fields [$field] ['setting'] ) ); // 字段设置
        $arrvideo = string2array ( $value );

        if (! $isplay) {
            // 字段处理
            $info = array ();
            $data = $this->data;
            foreach ( $this->fields as $f => $v ) {
                if (! isset ( $data [$f] ) || $v ['formtype'] == 'video')
                    continue;
                $func = $v ['formtype'];
                $vs = $data [$f];
                $result = method_exists ( $this, $func ) ? $this->$func ( $f, $vs ) : $vs;
                if ($result !== false)
                    $info [$f] = $result;
            }
            @extract ( $info );
            // end
        }

        $strvideo = '';
        $this->player = loader::model ( 'player_model' );
        foreach ( $arrvideo as $k => $v ) {
            $vurl = $ishtml ? (($purl ? $field : '') . $ljf . ++ $k . $fileext) : SITE_URL . 'index.php?app=content&controller=player&catid=' . $catid . '&id=' . $id . '&f=' . $field . '&n=' . ++ $k; // url_rule
            $classname = ($n == $k) && ($getf == $field) ? ' class="currentj"' : '';
            $httpvurl = $ishtml ? str_replace ( SOURCE_PATH, SITE_URL, $cpath ) . $vurl : $vurl;
            $strvideo .= '<a href="' . $httpvurl . '" target="' . ($isplay ? '_self' : '_blank') . '"' . $classname . '>' . $v ['alt'] . '</a>';
            $jstitle = $v ['alt'];
            $filepath = $v ['url'];
            $playerid = $v ['p'] ? $v ['p'] : 8; // 8->qvod is default player
            $play = $this->player->get_one ( array ('playerid' => $playerid ), 'code' );
            $playcode = str_ireplace ( array ('{$filepath}','{$siteurl}' ), array ($filepath,SITE_URL ), $play ['code'] );

            $seo_keywords = is_array ( $keywords ) ? implode ( ',', $keywords ) : $keywords;
            $SEO = seo ( $catid, $title . $v ['alt'] . $field, $description, $seo_keywords );

            // create_html
            if (! $isplay && $ishtml && ! $n) {
                $file = $cpath . $vurl;
                ob_start ();
                include template ( 'content', 'player' );
                $data = ob_get_contents ();
                ob_clean ();
                $dir = dirname ( $file );
                if (! is_dir ( $dir )) {
                    mkdir ( $dir, 0777, 1 );
                }
                file_put_contents ( $file, $data );
                @chmod ( $file, 0777 );
                if (! is_writable ( $file )) {
                    $file = str_replace ( SOURCE_PATH, '', $file );
                    showmessage ( L ( 'file' ) . '：' . $file . '<br>' . L ( 'not_writable' ) );
                }
            }
            // create_html end
        }
        return $strvideo;
    }