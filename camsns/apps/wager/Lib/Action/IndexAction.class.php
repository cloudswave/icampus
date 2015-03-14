<?php
/**
 * 分歧终端机
 * 这个只是一个初级版本,花了几个小时做出来,后面准备推出商业版,需要的站长可以联系我QQ:317431625.
 * 欢迎加入ThinkSNS二次开发技术交流群197167657
 * 为了更好的提供产品,请大家尊重作者的辛苦编程
 * @author  Yovae<yovae@qq.com>
 * @website www.ithinksns.org
 * @version TS3.0
 */
class IndexAction extends Action {
	public function index() {
		$this->setTitle( '分歧终端机' );
		$this->setKeywords( '分歧终端机' );
        $userCredit=model('Credit')->getUserCredit($this->mid);
        $this->assign('score',$userCredit['credit']['score']);
		$this->display();
	}
    public function ajax_wager()
    {
        $userCredit=model('Credit')->getUserCredit($this->mid);
        $cash=intval($_REQUEST['cash']);
        if($userCredit['credit']['score']['value']>=$cash){
            $array=array(0,1,2);
            $rate = '1:1:2';
            $result=$this->getRandom($array,$rate);
            $ret_data['ret']=1;
            $ret_data['msg']='';
            switch($result){
                case 0:
                    $ret_data['html']='这次打了平手,继续努力吧';
                    $ret_data['display']='eq';
                    $ret_data['coin']=$userCredit['credit']['score']['value'];
                    break;
                case 1:
                    $ret_data['html']='你赢了'.$cash.'的'.$userCredit['credit']['score']['alias'];
                    $ret_data['display']='win';
                    $ret_data['wincoin']=$cash;
                    $ret_data['coin']=$userCredit['credit']['score']['value']+$cash;
                    //增加积分
                    model('Credit')->addTaskCredit(0,$cash,$this->mid);
                    break;
                case 2:
                    $ret_data['html']='你输了'.$cash.'的'.$userCredit['credit']['score']['alias'];
                    $ret_data['display']='nowin';
                    $ret_data['wincoin']=$cash;
                    $ret_data['coin']=$userCredit['credit']['score']['value']-$cash;
                    //减少积分
                    model('Credit')->addTaskCredit(0,0-$cash,$this->mid);
                    break;

            }
            exit(json_encode($ret_data));
        }else{
            $ret_data['ret']=3;
            $ret_data['html']='';
            $ret_data['msg']='您没有足够的'.$userCredit['credit']['alias'].'下注';
            exit(json_encode($ret_data));
        }
    }
    private function getRandom($array,$rate)
    {
        $rate = explode(':',$rate);
        $sum = 0;
        $left = 0;
        $right = 0;
        foreach($rate as $value){
            $sum+=$value*10;
        }
        $temp = rand(0,$sum);
        foreach($rate as $key=>$value){
            $right+=$value*10;
            if($left<=$temp && $temp<$right){
                return $array[$key];
            }
            $left+=$value*10;
        }
    }
}
?>