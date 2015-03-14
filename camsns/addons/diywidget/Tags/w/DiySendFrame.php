<?php
class DiySendFrame extends TagsAbstract{
	/**
	 * 是否是封闭的标签
	 * @var unknown_type
	 */
	static $TAG_CLOSED = false;
	
	public function getTagStatus(){
		return self::$TAG_CLOSED;
	}
	/* (non-PHPdoc)
	 * @see TagsAbstract::getTemplateFile()
	 */
	function getTemplateFile($tpl = '') {
		//返回需要渲染的模板
		return dirname(__FILE__).'/DiySendFrame/sendframe.html';
	}

	/* (non-PHPdoc)
	 * @see TagsAbstract::replace()
	 */
	function replace() {
		// TODO Auto-generated method stub
		return $this->attr;
	}

	
}