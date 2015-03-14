<?php
    /**
     * EventOptsModel 
     * 活动的选项模型
     * @uses BaseModel
     * @package 
     * @version $id$
     * @copyright 2009-2011 SamPeng 
     * @author SamPeng <sampeng87@gmail.com> 
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
    class EventOptsModel extends BaseModel{
        public function getOpts( $optId ){
            $map['id'] = intval($optId);
            return $this->where( $map )->find();
        }
    }
