<?php
/**
 * 关注控制器
 * @author ethanzhu <ethanzhu@qq.com>
 * @version 1.0
 */
class FollowAction extends Action {

    /**
     * 关注状态修改接口
     * @return json 处理后返回的数据
     */
    public function upFollowStatus()
    {
        $uid = intval($_POST['uid']);
        $cid = intval($_POST['cid']);
        $type = t($_POST['type']);
        $f_target=intval($_POST['f_target']);

        if ($f_target==0) {
           $res = model('PosterTypeFollow')->upFollow($uid, $cid, $type);
        }else{
           $res = model('PosterSmallTypeFollow')->upFollow($uid, $cid, $type);
        }
        
        $result = array();
        if($res) {
            $result['status'] = 1;
            $result['info'] = '';
        } else {
            $result['status'] = 0;
            $result['info'] = '';
        }

        exit(json_encode($result));
    }
}