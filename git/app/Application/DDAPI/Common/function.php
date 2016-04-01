<?php
/**
 * 判断是否合法的下载地址，包含http,https,ftp
 * @param string $url
 * @return boolean
 * @author liuliu
 */
function ckeckDURL($url)
{
    if (substr($url, 0, 7) === 'http://' || substr($url, 0, 8) === 'https://' || substr($url, 0, 6) === 'ftp://') {
        return true;
    }

    return false;

}
