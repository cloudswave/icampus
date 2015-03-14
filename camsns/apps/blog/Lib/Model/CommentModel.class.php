<?php
    /**
     * CommentModel 
     * 评论心情的model
     * @uses BaseModel
     * @package 
     * @version $id$
     * @copyright 2009-2011 SamPeng 
     * @author SamPeng <sampeng87@gmail.com> 
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
    class CommentModel extends BaseModel{

        public function addComment( $data ){
            foreach( $data as $key => $value ){
                    $map[$key] = $value;
            }

            fd( $map );
            if( $this->add($map) ){
                $map2['appid'] = $map['appid'] ;
                $map2['type'] = $map['type'];
                $count = $this->where($map2)->field( 'count(*)' )->findAll();
                fd( $count );
                return $count[0]['count(*)'];
            }else{
                return false;
            }
        }

        public function deleteComment( $data ){
            //排除空条件清空整个表
            if( empty( $data ) ){
                throw new ThinkException( "删除数据必须有条件" );
            }
            
            foreach( $data as $key=>$value ){
                $this->$key = $value;
            }
            return $this->delete();
        }

        public function getComment( $type,$miniId,$odd = false, $count = null,$time=null ){
            $map['type']  = $type;
            $map['appid'] = $miniId;

            //根据参数决定返回什么样的评论集合
            if( true == $odd ){
                //如果设置了时间。。
                if( isset( $time ) )
                    $map['cTime'] = array( "lt",$time );
                $result = $this->where( $map )->findAll();
                fd( $this->getLastSql() );
            }else{
                $result['first'] = $this->where($map)->find();

                if( $count>1 ){
                    $result['last'] = $this->where( $map )->order( 'cTime desc' )->find();
                    $result['count']=$count;
                }

                $result['id'] = $miniId;
            }
            return $result;
        }
    }
