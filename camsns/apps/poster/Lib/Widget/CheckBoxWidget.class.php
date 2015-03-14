<?php
class CheckBoxWidget extends Widget{
	public $name = "多选框";
	public $explain = "输入值为数据项。每行为一个数据项。[selected]标记的数据项表示默认选中。";
	public function render($data){
		if(isset($data['value'])){
			$temp_data = explode(',',$data['value']);
			foreach($data['data'] as &$value){
				if(in_array($value,$temp_data)){
					$value = $value.'[selected]';
				}
			}
		}
		return $this->renderFile(dirname(__FILE__) . '/CheckBox.html',$data);
	}
	
    public function getData($data){
        $data = nl2br($data);
        $result = explode('<br />',$data);
        return $result;
    }
}