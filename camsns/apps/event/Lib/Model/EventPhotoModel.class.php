<?php
    /**
     * EventPhotoModel 
     * 活动图片处理类
     * @uses BaseModel
     * @package 
     * @version $id$
     * @copyright 2009-2011 SamPeng 
     * @author SamPeng <sampeng87@gmail.com> 
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
    class EventPhotoModel extends BaseModel{
        protected $tableName = 'event_photo';

        public function addPhoto( $data,$eventId,$name ){
            //组装多条语句查询
            //字段

            //要插入的值
            $val  = array();
            $time = time();
            foreach( $data as $value ){
                $val[] = sprintf( '(%s,%s,\'%s\',\'%s\',\'%s\',%s,%s,\'%s\')',$eventId,$this->mid,$name,$value['savename'],$value['savepath'],$value['id'],$time,$value['name'] );
            }

            $sql = "INSERT INTO {$this->tablePrefix}{$this->tableName}
                ( `eventId`,`uid`,`name`,`filename`,`filepath`,`aid`,`cTime`,`savename`)
                 VALUE ".implode( ',',$val )."
                    ";
            //返回插入成功条数
            $add = $this->execute( $sql );
            return $add;
        }

        public function getPhoto( $photoid){
            $map['id'] = intval($photoid);
            return $this->where( $map )->field( 'filename,filepath,uid,name,eventId' )->order('id DESC')->find();
        }

        public function getPhotos( $eventId,$limit){
            $map['eventId'] = intval( $eventId );
            return  $this->where( $map )->limit( '0,'.$limit )->order('id DESC')->findAll();
        }

        public function editName( $id,$name ){
            if( empty( $name ) ){
                return false;
            }
            $condition['id'] = $id;
            $map['savename'] = $name;
            return $this->where( $condition )->save( $map );
        }

    }
