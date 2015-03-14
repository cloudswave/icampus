<?php
class PosterModel extends Model{
	private $api;
	protected $fields = array(0=>'id',1=>'pid',2=>'title',3=>'type',4=>'uid',
			5=>'address_province',6=>'address_city',7=>'address_area',8=>'area',
			9=>'cTime',10=>'deadline',11=>'content',12=>'private',
			13=>'cover',14=>'recommend',15=>'contact',16=>'private',17=>'cover',
			18=>'extra1',19=>'extra2',20=>'extra3',21=>'extra4',22=>'extra5',23=>'attach_id'
	);

	public function _initialize(){
	     	//$this->api = new TS_API();
	}

	public function getPosterList($pid=null,$type=null,$uid = null,$title = null){
		isset($pid) && $map['pid'] = $pid;
		isset($type) && $map['type'] = $type;
        isset($title) && $map['title'] = array( 'like','%'.$title.'%' );
		if(is_array($uid)&&!empty($uid)){
			$map['uid'] = array('in',$uid);
		}elseif(intval($uid)){
			$map['uid'] = $uid;
		}
	    $result = $this->where($map)->order('cTime DESC')->field('id,pid,type,uid,content,title,deadline,private,cover,cTime,recommend')->findPage(20);
	    $result['data'] = $this->replace($result['data']);
	    return $result;
	}
	public function getPoster($id,$mid){
		$map['id'] = $id;
        $result = $this->where($map)->find();
        $posterSmallTypeDao = D('PosterSmallType');
        $posterTypeDao = D('PosterType');
        if(!$result) return false;
        //if($mid !=$result['uid'] && $result['private'] == 1 && 'unfollow' == getFollowState($result['uid'],$mid)) return false;
        $result['posterType'] = $posterTypeDao->getTypeName($result['pid']);
        $result['posterSmallType'] = $posterSmallTypeDao->getTypeName($result['type']);
        isset($result['cover']) && $result['cover'] = DATA_URL.'/upload/'.$result['cover'];
        $result['address'] = getAreaInfo($result['address_province'].','.$result['address_city'].','.$result['address_area']);
        return $result;
	}

	public function deletePoster($id,$mid){
		$poster = $this->where('id='.$id)->find();
		if(!$poster) return -2;
		if($poster['uid'] != $mid) return -1;

		$rs = $this->where('id='.$id)->delete();
		if($rs){
			return 1;
		}else{
			return 0;
		}
	}

    public function replace($data){
    	$result = $data;
    	$posterSmallTypeDao = D('PosterSmallType');
    	$posterTypeDao = D('PosterType');
    	$posterST = $posterSmallTypeDao->getPosterSmallTypeByIdArray();
    	$posterT = $posterTypeDao->getPosterTypeByIdArray();
        $posterType = D('PosterType');
    	foreach($result as &$value){
           $value['type'] = $posterST[$value['type']];
           $value['content'] = getPosterShort($value['content'],20);
           $value['posterType'] = $posterT[$value['pid']];
           $value['cover'] && $value['cover'] = DATA_URL.'/upload/'.$value['cover'];
    	}
    	return $result;
    }
}