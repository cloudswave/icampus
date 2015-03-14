<?php 
    /**
     * EventTypeModel 
     * 
     * @uses BaseModel
     * @package 
     * @version $id$
     * @copyright 2009-2011 SamPeng 
     * @author SamPeng <sampeng87@gmail.com> 
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
    class EventTypeModel extends BaseModel{

        /**
         * getType 
         * 获取所有分类
         * @access public
         * @return void
         */
        public function getType(){
            //先从缓存里面获取
             $result = $this->field('id,name')->findAll();

                //重组数据集结构
                $newresult = array();
                foreach ( $result as $value ){
                    $newresult[$value['id']] = $value['name'];
                }
                return $newresult;
        }

        /**
         * addType 
         * 增加分类
         * @param mixed $map 
         * @access public
         * @return void
         */
        public function addType( $map ){
            if(empty($map['name'])){
                return -1;
            }
            return $this->add( $map );
        }

        /**
         * deleteType 
         * 删除分类
         * @param mixed $map 
         * @access public
         * @return void
         */
        public function deleteType( $map,$formCate = null,$obj = null ){
            //先判断合法性
            if( empty( $map ) )
                throw new ThinkException( "不能是空条件删除" );
            //如果这个分类下有内容，就不允许删除
            $id   = D( 'Event' )->field( 'distinct(type)' )->findAll();
            $temp = array();

            foreach ( $id as $value ){
                $temp[] = $value['type'];
            }

            if( strpos( $map['id'][1],',' ) ){
                $temp2 = explode( ',',$map['id'][1] );
                $map['id'] = array('in',array_diff( $temp2,$temp));
                if(count($map['id'][1])==count($temp2)){
                    return $this->where( $map )->delete();
                }else{
                	return 0;
                }
            }else{
                //如果选择的分类中已有了的内容
                if( !in_array( $map['id'][1],$temp ) ){
                    return $this->where( $map )->delete();
                }else{
                	return 0;
                }
            }
            return false;

        }

        /**
         * editType 
         * 编辑分类
         * @param mixed $map 
         * @access public
         * @return void
         */
        public function editType( $data ){
            $query = $this->save( $data );
            return $query;
        }

        /**
         * getTypeName 
         * 通过id获得名字
         * @param mixed $id 
         * @access public
         * @return void
         */
        public function getTypeName( $id ){
            $map['id'] = $id;
            $result = $this->where( $map )->field('name')->find();
            return $result['name'];
        }

    }
