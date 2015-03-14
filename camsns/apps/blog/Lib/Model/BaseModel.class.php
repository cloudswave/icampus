<?php

    /**
     * BaseModel 
     * 心情的base类
     *
     * @uses Model
     * @package Model::Mini
     * @version $id$
     * @copyright 2009-2011 SamPeng 
     * @author SamPeng <sampeng87@gmail.com> 
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
    class BaseModel extends Model{

        /**
         * checkNull 
         * 检查变量是否为空,暂时只能检查一维数组
         * @param mixed $value 
         * @access public
         * @return void
         */
        protected static function checkNull( $value ){
            if( !isset( $value ) || empty( $value ) ){
                return true;            
            }else{
                return false;
            }
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
         * merge 
         * 合并条件
         * @param mixed $map 
         * @access private
         * @return void
         */
        protected function merge ( $map = null ){
            if( isset( $map ) ){
                $map = array_merge( $this->data,$map );
            }else{
                $map = $this->data;
            }

            return $map;
        }
        
        /**
         * getBlogContent 
         * 获得某一条日志的详细页面
         * @param mixed $id 
         * @access public
         * @return void
         */
        public function getBlogContent( $id,$how =null,$uid = null  ){
            $mention  = self::factoryModel( 'mention' );
            $category = self::factoryModel( 'category' );//获取分类的实例对象
            

            isset( $uid ) && $map['uid'] = $uid;

                switch( $how ){
                    case "gt":
                        $map['id']  = array( $how,$id );
                        $order = "ID ASC";
                        break;
                    case "lt":
                        $map['id']  = array( $how,$id );
                        $order = "ID DESC";
                        break;
                    case "first":
                        $order = "ID ASC";
                        break;
                    case "last":
                        $order = "ID DESC";
                        break;
                    default:
                        $map['id']  = $id;
                        break;
                }
           
                $map['uid'] = $uid;
            //组装查询条件
            $map    = $this->merge( $map );
         
            $result = $this->where( $map )->order( $order )->find();
            if( false == $result ){
                if( "gt" == $how ){
                    return $this->getBlogContent( $id,'first',$uid );
                }

                if( "lt" == $how ){
                    return $this->getBlogContent( $id,'last',$uid );
                }
                return false;
            }
            //清除data。防止污染
            $this->data   = null;
            $this->status = 1;
            

            //关联查询分类
            $result['category'] = array( 
                                    "name" => $category->getCategoryName( $result['category'] ),  //获取所有的分类
                                    "id"   => $result['category']
                                        );
            //追加日志中提到的内容
            $result['count']    = $this->where( "uid = '".$result['uid']."' AND status = 1 " )->count();
            $result['num']      = $this->where( 'id <'.$result['id']." AND status = 1 AND uid =".$uid )->count()+1;
            $result['content']  = h( $result['content'] );
            return $result;
        }

        /**
         * factoryModel 
         * 工厂方法
         * @param mixed $name 
         * @static
         * @access private
         * @return void
         */
        public static function factoryModel( $name ){
            return D("Blog".ucfirst( $name ));
        }

        public function getOneName($uid){
            return getUserName($uid);
        }
}
