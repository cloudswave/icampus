<?php
    /**
     * GiftCategoryModel
     * 礼物分类数据模型
     *
     * @uses 
     * @package 
     * @version 
     * @copyright 2009-2011 SamPeng 水上铁
     * @author SamPeng <sampeng87@gmail.com> 水上铁<wxm201411@163.com> 
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
	class GiftCategoryModel extends Model {
		private $gift;   //礼品表模型
		
		/**
		 * _initialize
		 * 初始化函数
		 * @return void
		 */
		public function _initialize(){
            parent::_initialize();
		}
		public function setGift($gift){
			$this->gift = $gift;   //赋值礼品表模型
		}

		/**
		 * GiftToCategory
		 * 获取已经分组的礼物
		 * @return unknown_type
		 */
		public function GiftToCategory(){
			//初始化礼物分组的变量，礼物数据的变量和最后结果的变量
			$gift = $gift_category = array();
			
			//获取礼物分组和礼物数据
			$gift_category = $this->where('status=1')->order('id ASC')->findAll();
			$gifts = $this->gift->where('status=1')->order('id DESC')->findAll();
			
			//根据分组重组数据
			foreach ($gift_category as &$category){
				foreach($gifts as $gift){
					if($category['id'] == $gift['categoryId'])
						$category['gifts'][] = $gift;
				}
			}
			
			//返回结果
			return $gift_category;
		}
		
		/**
		 * GiftToCategory
		 * 获取分组列表
		 * @return unknown_type
		 */
		public function __getCategory(){
			$categorys = $this->order('id ASC')->findAll();
			foreach($categorys as $v){
				$categorys_[$v['id']] = $v['name'];
			}
			return $categorys_;
		}
	}
?>