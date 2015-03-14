<?php
    /**
     * BlogOutlineModel 
     * 日志草稿箱
     * @uses BaseModel
     * @package 
     * @version $id$
     * @copyright 2009-2011 SamPeng 
     * @author SamPeng <sampeng87@gmail.com> 
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
    class BlogOutlineModel extends BaseModel{
        /**
         * doAddOutline 
         * 添加草稿
         * @param mixed $map 
         * @access public
         * @return void
         */
        public function doAddOutline ( $map ){
              $map['cTime'] = isset( $map['cTime'] )?$map['cTime']:time();
            $map['type']  = isset( $map['type'])?$map['type']:0;
            unset( $map['password'] );  //TODO 密码存储
            //添加blog相关好友
            $friendsId = isset( $map['mention'] )?explode(',',$map['mention']):null;

            if( !empty( $friendsId ) ){
                $result  = $map['friendId'] = serialize( $friendsId ) ;
            }

            unset( $map['mention'] );
            $map    = $this->merge( $map );
            $addId  = $this->add( $map );

            
            if( !$result && !empty( $friendsId ) ){
                return false;
            }

            return $addId;

        }

        public function doUpdateOutline( $map,$id ){
            $map['cTime'] = isset( $map['cTime'] )?$map['cTime']:time();
            $map['type']  = isset( $map['type'])?$map['type']:0;
            unset( $map['password'] );  //TODO 密码存储
            //添加blog相关好友
            $friendsId = isset( $map['mention'] )?explode(',',$map['mention']):null;
            unset( $map['mention'] );

            if( !empty( $friendsId ) ){
                $result  = $map['friendId'] = serialize( $friendsId ) ;
            }
            $map    = $this->merge( $map );

            $addId  = $this->where( "id =".$id )->save( $map );

                     
            if( !$result && !empty( $friendsId ) ){
                return false;
            }

            return $addId;

        }

        /**
         * getList 
         * 获得列表
         * @param mixed $uid 
         * @access public
         * @return void
         */
        public function getList( $uid ){
            $map['uid'] = $uid;
            $map['status'] = 1;
            return $this->where( $map )->order( 'cTime DESC' )->findPage(10);
        }
    }
