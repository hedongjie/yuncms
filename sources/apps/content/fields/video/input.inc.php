    function video($field, $value) {
        $array = $array0 = $temp = $temp0 = $arrv = array ();
        $pictures = $_POST [$field . '_url']; // 取得视频列表
        $playerid = $_POST [$field . '_defaultplayer'];
        if ($_POST [$field]) {
            $arrurl = array_filter ( explode ( "\n", $_POST [$field] ) );
            $k = 0;
            foreach ( $arrurl as $ka => $pa ) {
                $pa = str_ireplace ( array ("\r\n","\r","\n" ), array ("","","" ), $pa );
                if (! $pa) {
                    continue;
                }
                ++ $k;
                $arrv = explode ( '$', $pa );
                $temp0 ['url'] = $arrv [1] ? $arrv [1] : $arrv [0]; // 懒家伙没有|
                $temp0 ['alt'] = $arrv [1] ? $arrv [0] : '第' . $k . '集';
                $temp ['p'] = $playerid;
                $array0 [$ka] = $temp0;
            }
        }
        if (! empty ( $pictures )) {
            $pictures_alt = isset ( $_POST [$field . '_alt'] ) ? $_POST [$field . '_alt'] : array (); // 取得视频说明
            if (! empty ( $pictures )) {
                foreach ( $pictures as $key => $pic ) {
                    $temp ['url'] = $pic;
                    $temp ['alt'] = $pictures_alt [$key] ? $pictures_alt [$key] : '第' . ++ $key . '集';
                    $temp ['p'] = $playerid;
                    $array [$key] = $temp;
                }
            }
        }

        $array = array2string ( array_merge ( $array, $array0 ) );
        $array0 = $temp = $temp0 = $arrv = $k = null;
        return $array;
    }