<?php
class InputWidget extends Widget{
	public $name = "输入框";
	public $explain = "输入值为默认值";
	public function render($data){
		if(isset($data['value'])){
			$data['data'] = $data['value'];
		}
		return $this->renderFile(dirname(__FILE__).'/Input.html',$data);
	}
	public function getData($data){
		return $data;
	}
}