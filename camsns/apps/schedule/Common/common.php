<?php
/**
 * 所属项目 ts1108.
 * 开发者: 想天
 * 创建日期: 1/3/14
 * 创建时间: 3:15 PM
 * 版权所有 想天工作室(www.ourstu.com)
 */

/**带省略号的限制字符串长
 * @param $str
 * @param $num
 * @return string
 */
function getShortSp($str, $num)
{
    if (utf8_strlen($str) > $num) {
        $tag = '...';
    }
    $str = getShort($str, $num) . $tag;
    return $str;
}

function utf8_strlen($string = null)
{
// 将字符串分解为单元
    preg_match_all("/./us", $string, $match);
// 返回单元个数
    return count($match[0]);
}

/**正则表达式获取html中首张图片
 * @param $str_img
 * @return mixed
 */
function getpic($str_img)
{
    preg_match_all("/<img.*\>/isU", $str_img, $ereg); //正则表达式把图片的整个都获取出来了
    $img = $ereg[0][0]; //图片
    $p = "#src=('|\")(.*)('|\")#isU"; //正则表达式
    preg_match_all($p, $img, $img1);
    $img_path = $img1[2][0]; //获取第一张图片路径
    return $img_path;
}


/**应用下的取后台配置函数
 * @param $name 直接填写后台的配置项key值
 * @return array
 */
function appC($name)
{
    global $ts;
    $app_name=$ts['_define']['APP_NAME'];
    $conf = D('Xdata')->lget("{$app_name}_Admin");
    foreach ($conf as $v) {
        if (isset($v[$name])) {
            return arrayComplie($v[$name]);
        } else {
            continue;
        }
    }

    return arrayComplie($conf);
}

/**把逗号分隔文本分解为数组
 * @param $data
 * @return array
 */
function arrayComplie($data)
{
    $rs = explode(',', $data);
    if (count($rs) == 1) {
        return $data;
    }
    return $rs;
}

/**获取图片缩略图，兼容又拍云
 * @param $id 图片ID
 * @param int $width
 * @param int $heigth
 * @return string
 */
function getImageSCById($id, $width = 180, $heigth = 180)
{
    $attach = model('Attach')->getAttachById($id);
    return getImageUrl($attach['save_path'] . $attach['save_name'], $width, $heigth, true);
}

