<?php
class VoteModel extends BaseModel
{

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * addVote
     * 添加投票
     * @param mixed $data
     * @param mixed $opt
     * @access public
     * @return void
     */
    public function addVote($data,$opt){
        $deadline = $data['deadline'];

        if($deadline < time()) {
            throw new ThinkException('投票截止时间不能早于发起投票的时间！');
        }
        //检测选项是否重复
        $opt_test = array_filter($opt);
        foreach($opt as $value){
            if(get_str_length($value) >200){
                throw new ThinkException("投票选项不能超过200个字符");
            }
        }

        $opt_test_count = count(array_unique( $opt_test ));
        if( $opt_test_count < count($opt_test) ) throw new ThinkException( '投票不允许有重复项' );

        $vote_id = $this->add($data);

        if($vote_id){
            $voteUser = D( "VoteUser" );
            $voteUser->uid      = $data['uid'];
            $voteUser->vote_id  = $vote_id;
            $voteUser->cTime    = time();
            $voteUser->add();

            //选项表
            $optDao = D("VoteOpt");
            foreach($_POST["opt"] as $v) {
                if(!$v) continue;
                $data["vote_id"]    =    $vote_id;
                $data["name"]       =    t($v);
                $add = $optDao->add($data);
            }
        }
        return $vote_id;

    }

    /**
     * getVoteList
     * 通过userId获取到用户列表
     * @param array|string|int $userId
     * @param array|object $options 查询参数
     * @access public
     * @return object|array
     */
    public function getVoteList($map = null,$field=null,$order = null,$limit = 20) {
        //处理where条件
        $map = $this->merge( $map );
        //连贯查询.获得数据集
        $result         = $this->where( $map )->field( $field )->order( $order )->findPage($limit) ;
        return $result;
    }

    public function merge ( $map = null ){
        if( isset( $map ) ){
            $map = array_merge( $this->data,$map );
        }else{
            $map = $this->data;
        }

        return $map;
    }

    public function doDeleteVote($id){
        $voteUser        = D( 'VoteUser' );
        $voteOpt         = D( 'VoteOpt' );

        $map2['vote_id'] = $map1['id'] = $id;


        //删除投票
        $result1 = $this->where( $map1 )->delete();

        //删除投票选项库
        $result2 = $voteOpt->where( $map2 )->delete();

        //删除投票参与人员库
        $result3 = $voteUser->where( $map2 )->delete();

        if( $result1 && $result2 && $result3){
            return true;
        }else{
            return false;
        }

    }

         /**
         * DateToTimeStemp
         * 时间换算成时间戳返回
         * @param mixed $stime
         * @param mixed $etime
         * @access public
         * @return void
         */
        public function DateToTimeStemp( $stime,$etime ) {
            $stime = strval( $stime );
            $etime = strval( $etime );

           //如果输入时间是YYMMDD格式。直接换算成时间戳
            if( isset( $stime[7] ) && isset( $etime[7] ) ){
                //开始时间
                $syear  = substr( $stime,0,4 );
                $smonth = substr( $stime,4,2 );
                $sday   = substr( $stime,6,2 );
                $stime  = mktime( 0, 0, 0, $smonth,$sday,$syear );

                //结束时间
                $eyear  = substr( $etime,0,4 );
                $emonth = substr( $etime,4,2 );
                $eday   = substr( $etime,6,2 );
                $etime  = mktime( 0, 0, 0, $emonth,$eday,$eyear );

                return array( 'between',array( $stime,$etime ) );
            }

            //如果输入时间是YYYYMM格式
            $start_temp   = $this->paramData( $stime );
            $end_temp     = $this->paramData( $etime );
            $start        = $start_temp[0];
            $end          = $end_temp[1];

            return array( 'between',array( $start,$end ) );
        }


        /**
         * paramData
         * 处理归档查询的时间格式
         * @param string $findTime 200903这样格式的参数
         * @static
         * @access protected
         * @return void
         */
        protected function paramData( $findTime ){
            //处理年份
            $year = $findTime[0].$findTime[1].$findTime[2].$findTime[3];
            //处理月份
            $month_temp = explode( $year,$findTime);
            $month = $month_temp[1];
            //归档查询
            if ( !empty( $month ) ){

                //判断时间.处理结束日期
                switch (true) {
                    case ( in_array( $month,array( 1,3,5,7,8,10,12 ) ) ):
                        $day = 31;
                        break;
                    case ( 2 == $month ):
                        if( 0 != $year % 4 ){
                            $day = 28;
                        }else{
                            $day = 29;
                        }
                        break;
                    default:
                        $day = 30;
                        break;
                }
                //被查询区段开始时期的时间戳
                $start = mktime( 0, 0, 0 ,$month,1,$year  );

                //被查询区段的结束时期时间戳
                $end   = mktime( 24, 0, 0 ,$month,$day,$year  );

                //反之,某一年的归档
            }elseif( isset( $year[4] ) ){
                $start = mktime( 0, 0, 0, 1, 1, $year );
                $end = mktime( 24, 0, 0, 12,31, $year  );
            }else{
                //其它操作
            }

            //fd( array( friendlyDate($start),friendlyDate($end) ) );
            return array( $start,$end );

        }

        /**
         * doIsHot
         * 设置推荐
         * @param mixed $model
         * @access protected
         * @return void
         */
		 public function doIsHot( $map,$act ) {
			if( empty($map) ) {
				throw new ThinkException( "不允许空条件操作数据库" );
			}
			switch( $act ) {
				case "recommend":   //推荐
					$field = array( 'isHot','rTime' );
					$val = array( 1,time() );
					$result = $this->setField( $field,$val,$map );
					break;
				case "cancel":   //取消推荐
					$field = array( 'isHot','rTime' );
					$val = array( 0,0 );
					$result = $this->setField( $field,$val,$map );
					break;

			}
			return $result;
		 }
        /**
         * getConfig
         * 获取配置
         * @param mixed $index
         * @access public
         * @return void
         */
/*        public function getConfig( $index ){
            $config = $this->config->$index;
            return $config;
        }*/
}
?>
