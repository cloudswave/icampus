<?php
class PosterSmallTypeModel extends Model{
	public function getSmallType(){
		$rs = $this->field('distinct(label)')->findAll();
		return $rs;
	}
	
	public function getAllSmallType($label){
	    $result = $this->where("label='".$label."'")->findAll();
	    return $result;
	}
	
    public function getPosterSmallTypeByIdArray(){
        $posterSmallType = $this->field('id,name')->findAll();
        
        $posterST = array();
        foreach($posterSmallType as $value){
            $posterST[$value['id']] = $value['name'];
        }
        return $posterST;
    }
	public function getPosterSmallType($label){
		$map['label'] = $label;
		$rs = $this->where($map)->field('id,name')->findAll();
		return $rs;
	}
   public function getTypeName($id){
        $map['id'] = $id;
        $rs = $this->where($map)->field('name')->find();
        $rs = $rs['name'];
        return $rs;
    }
}