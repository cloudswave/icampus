<?php

/**
 * getPosterShort 
 * 去除标签，截取blog的长度
 * @param mixed $content 
 * @param mixed $length 
 * @access public
 * @return void
 */
function getPosterShort($content,$length = 60) {
	$content	=	stripslashes($content);
	$content	=	strip_tags($content);
	$content	=	getShort($content,$length);
	return $content;
}

/**
 * 获取完整的地区
 *
 * @param unknown_type $area   '23,45,64' 字串形式传入
 * @param unknown_type $type
 */
function getAreaInfo($areaid) {
    $areaDao = M('area');
    $arrArea = explode(',',$areaid);
    foreach ($arrArea as $key=>$val) {
        if($val) {
            $area_name = $areaDao->where('area_id='.$val)->field('title')->find();
            $str[] = $area_name['title'];
        }
    }
    return implode(' ',$str);
}