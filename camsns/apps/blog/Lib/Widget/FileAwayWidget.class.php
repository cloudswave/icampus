<?php
    /**
     * Pub_FileAwayWidget
     * 归档widget
     *
     * @uses BaseWiget
     * @package Widget
     * @version $id$
     * @copyright 2009-2011 SamPeng
     * @author SamPeng <sampeng87@gmail.com>
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
    class FileAwayWidget extends Widget{

        private $data;
        /**
         * render
         *
         * @param mixed $data
         * @access public
         * @return void
         */
        public function render( $data ){
        	global $ts;
            $this->data = $data;

            $date = date( 'Ym',time() );
            if( !isset($data['limit']) ){
                $date = self::paramData( $date,6,$data['tableName']);
            }else{
                $date = self::paramData( $date,$data['limit'],$data['tableName']);
            }

            $data['date'] = $date;
			$data['alldate'] = '全部时间';
			$data['title'] = $ts['app']['app_alias'].'存档';
            return $this->renderFile( dirname(__FILE__) . '/FileAwayWidget.html',$data );
        }


        /**
         * paramData
         * 解析日期
         * @param mixed $date 当前时间（200905格式）
         * @param mixed $object 需要查询数据的object名.
         * @static
         * @access private
         * @return void
         */
        private  function paramData( $date,$limit = 6,$tableName){
            $year     = $date[0].$date[1].$date[2].$date[3];
            $month    = $date[4].$date[5];
            $timestmp = mktime( 0,0,0,$month,1,$year );
            $object = $this->data['instance'];
          
            $condition = $this->data['condition'];

            foreach ( $condition as $key=>$value ){
                if( !is_numeric( $value ) ){
                    $where[] = " `{$key}` = `{$value}` ";
                }else{
                    $where[] = " `$key` = {$value}";
                }
            }


            if( !empty( $where ) ){
                $where = implode( ' AND ',$where )." AND ";
            }
            $dao = D( 'Smile' );
            //循环得到年月列表
            for( $i = 0; $i<$limit;$i++ ){
                $timestmp_temp    = $timestmp-( $i*28*24*60*60 );
                $key              = date( 'Ym',$timestmp_temp );
                $date             = date( 'Y年m月',$timestmp_temp );
                $time  = $this->getData( $key );
                $sql[] = "select '{$key}' as `time`,count(1) as count from  {$tableName} where {$where} cTime BETWEEN {$time[0]} AND {$time[1]}";
                $limit_time[$key]['content'] = $date;

//                获得记录数
//                if( $result = $object->fileAwayCount($key,$condition) ){
//                    $limit_time[$key]['count'] = $result[0]['count(*)'];
//                }else{
//                    $limit_time[$key]['count'] = 0;
//                }
            }
            $sql = implode( ' union all ',$sql );
            $result = $dao->query( $sql );
            foreach ( $result as $value ){
                $limit_time[$value['time']]['count'] = $value['count'];
            }
            return $limit_time;
        }
        /**
         * getData
         * 处理归档查询的时间格式
         * @param string $findTime 200903这样格式的参数
         * @static
         * @access protected
         * @return void
         */
        private function getData( $findTime ){
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

    }
