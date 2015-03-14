<?php
function getTagName($tagid){
	$tag = get_X_Tag();
	$tag = $tag['name'];
	
	return $tag[$tagid];
}

//过虑如123，456这样的数字串
function intMember($val){
	$isArr = is_array($val);
	
	if(!$isArr){
		$val = explode(',', $val);
	}
	
	foreach ($val as $k=>&$v){
		$v = intval($v);
		if(empty($v))	unset($val[$k]);
	}
	
	if($isArr){
		return $val;
	}

	return implode(',', $val);
}

function dealTag($tagname){
	$tagname	=	str_replace(array(' ','，',';','；'),',',$tagname);
	$tagnames	=	explode(',',$tagname);
	foreach ($tagnames as $v){
		if(empty($v)) continue;
		
		$arr[] = $v;
	}
	return implode(',', $arr);
}
/**
 * 判断一个字符串是否是整数
 * @param unknown_type $pString
 * @return string|string|string
 */
function isNumber($pString){
	$length = strlen($pString);
	//空字符串返回不是整数
	if($length==0)
	{
		return false;
	}
	for($i=0;$i<$length;$i++)
	{
		//根据ASCII判断是否字符串中的每个字符都是数字
		if($pString[$i]<"0" || $pString[$i]>"9")
		{
			return false;
		}
	}
	return true;
}
/**
 * 以数组中的一个字段的值为唯一索引返回一个三维数组
 * @param $pArray 一个二维数组
 * @param $pFieldBy 作为索引的字段的KEY值
 * @param $pIncludeFileld 可以定义返回的数组的包含的原数组的字段
 * @return 返回新的三维数组
 */
function group($pArray, $pFieldBy, $pIncludeFileld=""){
	if($pIncludeFileld!="")
		$fields = explode(",", $pIncludeFileld);
	$result_array = array();

	for($i=0; $i<count($pArray); $i++){
		$group_key = $pArray[$i][$pFieldBy];
		if( !isset( $result_array[$group_key] ) ){
			$result_array[$group_key] = array();
		}

		if($pIncludeFileld!=""){
			$temp = array();
			foreach($fields as $field){
				$temp[$field] = $pArray[$i][$field];
			}
			$result_array[$group_key][] = $temp;
		}else{
			$result_array[$group_key][] = $pArray[$i];
		}
	}
	return $result_array;
}