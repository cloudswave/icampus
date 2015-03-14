<?php
/**
 * 微吧获取图片存在相对地址
 * @param integer $attachid 附件ID
 * @return string 附件存储相对地址
 */
function getImageUrlByAttachIdByWeiba ($attachid) {
    if ($attachInfo = model('Attach')->getAttachById($attachid)) {
        return $attachInfo['save_path'].$attachInfo['save_name'];
    } else {
        return false;
    }
}