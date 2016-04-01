<?php
/**
 * 数据加密
 *
 * @param string  $string    加密字符
 * @param string  $key       密匙
 * @param string  $operation 1:ENCODE加密 2:DECODE解密
 * @param interge $expiry    生效时间
 *                           return 签名结果
 */
function encry($string, $key = '', $operation = 'ENCODE', $expiry = 0)
{
    if (empty($string) || empty($key)) {
        return;
    }

    $ckey_length = 4;
    $key = md5($key);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

/**
 * 签名字符串
 *
 * @param $prestr 需要签名的字符串
 * @param $key    私钥
 *                return 签名结果
 */
function md5Sign($prestr, $key)
{
    $prestr = $prestr . $key;

    return md5($prestr);
}

function int_to_string(array &$data, $map = array())
{
    if (empty($data) || false == $data || null === $data) {
        return $data;
    }

    empty($map) && $map = array('status' => array(-1 => '删除', 0 => '禁用', 1 => '正常', 2 => '未审核', 3 => '草稿'));

    foreach ($data as $key => $row) {
        foreach ($map as $col => $pair) {
            if (isset($row[$col]) && isset($pair[$row[$col]])) {
                $data[$key][$col . '_text'] = $pair[$row[$col]];
            }
        }
    }

    return $data;
}


/**
 * getModelInfo
 *
 * 模型信息
 *
 * @param interge $modelid 模型id
 * @return array
 */
function getModelInfo($modelid = '')
{
    $model = F('modelRelati');
    if (!$model) {
        modelRelatiGet();
        $model = F('modelRelati');
    }

    if ($modelid !== '') {
        if (isset($model[$modelid])) {
            return [$modelid => $model[$modelid]];
        } else {
            return false;
        }
    }

    return $model;
}

/**
 * modelRelatiGet
 *
 * 模型关系缓存
 *
 * @return array
 */
function modelRelatiGet()
{
    /* 允许统计模型定义 */
    $allowModel = [
        // 模型id => [模型展示名称, 模型表名称]
        1 => ['文章', 'DocumentArticle'],
        2 => ['图库', 'GalleryAlbum'],
        3 => ['软件下载', 'DownDmain'],
    ];

    $mod = M('model')->where("status = 1")->field('id,name,extend,title')->order('id ASC')->select();

    $model = $relati = $_relati = array();
    foreach ($mod as $k => $params) {
        $params['name'] = ucwords($params['name']);
        $mod[$k]['name'] = $params['name'];
        $model[$params['name']] = $params;
    }

    foreach ($mod as $params) {
        if (!$params['extend']) {
            if ($params['name'] == 'Document') {
                $_relati[$params['id']] = array('Category', $params['name']);
            } else {
                $_relati[$params['id']] = array($params['name'] . 'Category', $params['name']);
            }
        }
    }

    foreach ($_relati as $id => $args) {
        foreach ($mod as $params) {
            if ($params['extend'] == $id) {
                $_relati[$id][] = ucfirst($params['name']);
            }
        }
    }

    foreach ($_relati as $id => $args) {
        if (isset($args[2])) {
            if (isset($model[$args[2]]['id'])) {
                $relati[$model[$args[2]]['id']] = array_slice($args, 0, 3);
                $relati[$model[$args[2]]['id']][2] = $args[1] . $args[2];
            }

            $affiliate = array_slice($args, 3);

            if ($affiliate) {
                foreach ($affiliate as $name) {
                    $relati[$model[$name]['id']] = array_slice($_relati[$id], 0, 3);
                    $relati[$model[$name]['id']][2] = $_relati[$id][1] . $name;
                }
            }
        }
    }

    $result = [];
    foreach ($relati as $key => $value) {
        foreach ($allowModel as $vars) {
            if ($vars[1] == $value[2]) {
                $value['name'] = $vars[0];
                $result[$key] = $value;
            }
        }
    }

    F('modelRelati', $result);
}

/**
 * Sorts
 *
 * 数组排序
 *
 * @return array
 */
function Sorts($array, $field = 'sort', $sort = 'ASC')
{
    $sort = strtoupper($sort);
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            $keyvalue[$key] = $value[$field];
        }
        if ($sort == 'DESC') {
            asort($keyvalue);
        } elseif ($sort == 'ASC') {
            arsort($keyvalue);
        }
        reset($keyvalue);
        foreach ($keyvalue as $k => $v) {
            $result[$k] = $array[$k];
        }

        return $result;
    }
}
