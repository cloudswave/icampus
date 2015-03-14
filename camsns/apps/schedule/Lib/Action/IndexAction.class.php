<?php
class IndexAction extends Action
{
	public function _initialize()
	{
        $this->appCssList[] = 'schedule.css';
        $this->appCssList[] = 'bootstrap.css';
	}

	public function index()
	{
        $page = D('AppSchedule')->where(array('uid'=>$this->uid))->order('date asc')->findPage();
        $owner = D('User')->getuserinfo($this->uid);
        $this->assign('page',$page);
        $this->assign('owner',$owner);
		$this->display();
	}

    public function getAreaList() {
        $area = $_REQUEST['area'];
        if(!$area) $area = 0;
        $areaList = D('Area')->getAreaList($area);
        $result = array();
        foreach($areaList as $e) {
            $result[] = array(
                'id'=>$e['area_id'],
                'title'=>$e['title'],
            );
        }
        echo json_encode($result);
        exit;
    }

    public function editSchedule() {
        $id = intval($_REQUEST['id']);
        //读取数据库
        $schedule = D('AppSchedule')->where(array('id'=>$id))->find();
        if(!$schedule) {
            $this->error('参数错误');
        }
        //解析日期
        $date = date_parse($schedule['date']);
        $schedule['year'] = $date['year'];
        $schedule['month'] = $date['month'];
        $schedule['day'] = $date['day'];
        //显示页面
        $this->assign('schedule', $schedule);
        $this->display();
    }

    public function doEditSchedule() {
        //读取参数
        $id = intval($_REQUEST['id']);
        $year = intval($_REQUEST['year']);
        $month = intval($_REQUEST['month']);
        $day = intval($_REQUEST['day']);
        $area2 = intval($_REQUEST['area']);
        $area4 = $_REQUEST['area4'];
        $event = $_REQUEST['event'];
        //确认权限
        if($id)
            $this->ensureOwner($id);
        //获取area1
        $area = D('Area')->where(array('area_id'=>$area2))->find();
        $area1 = $area['pid'];
        if(!$area1) $area1 = $area2;
        //将日期转换成统一格式
        $date = sprintf('%02d-%02d-%02d', $year, $month, $day);
        //写入数据库
        $data = array(
            'date'=>$date,
            'area1'=>$area1,
            'area2'=>$area2,
            'area4'=>$area4,
            'uid'=>$this->mid,
            'event'=>$event,
        );
        if($id) {
            $result = D('AppSchedule')->where(array('id'=>$id))->save($data);
            if($result) {
                $this->ajaxReturn('编辑成功',null,1);
            } else {
                $this->ajaxReturn('编辑失败',null,0);
            }
        } else {
            $data['ctime'] = time();
            $result = D('AppSchedule')->add($data);
            if($result) {
                $this->ajaxReturn('发布成功',0,1);
            } else {
                $this->ajaxReturn('发布失败',0,0);
            }
        }
    }

    public function deleteSchedule() {
        //获取参数
        $id = intval($_REQUEST['id']);
        //检查权限
        $this->ensureOwner($id);
        //写入数据库
        $result = D('AppSchedule')->where(array('id'=>$id))->delete();
        if($result) {
            $this->ajaxReturn('删除成功',0,1);
        } else {
            $this->ajaxReturn('删除失败',0,0);
        }
    }

    private function ensureOwner($id) {
        $schedule = D('AppSchedule')->where(array('id'=>$id))->find();
        if($schedule['uid'] != $this->mid) {
            $this->ajaxReturn('没有权限',0,0);
        }
    }
}