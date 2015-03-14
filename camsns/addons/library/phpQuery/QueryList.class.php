<?php

  /**
  *通用列表采集类
  *版本V1.3
  *作者:JAE
  *博客:http://blog.jaekj.com
  */
    require_once 'phpQuery.php';
    class QueryList{
        
        private $pageURL;
         private $regArr = array();
         public $jsonArr = array();
         private $regRange;
         private $html;
         /************************************************
         * 参数: 页面地址 选择器数组 块选择器
         * 【选择器数组】说明：格式array("名称"=>array("选择器","类型"),.......)
         * 【类型】说明：值 "text" ,"html" ,"属性" 
         *【块选择器】：指 先按照规则 选出 几个大块 ，然后再分别再在块里面 进行相关的选择
         *************************************************/
         function QueryList($pageURL,$regArr=array(),$regRange='')
         {
             $this->pageURL = $pageURL;
    
             //为了能获取https://
               $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,$this->pageURL);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); 
                $this->html = curl_exec($ch);
               curl_close($ch);
               
             if(!empty($regArr))
             {
            
                  $this->regArr = $regArr;
                 $this->regRange = $regRange;
                 $this->getList();
             }
               
         }
         function setQuery($regArr,$regRange='')
         {
             $this->jsonArr=array();
             $this->regArr = $regArr;
             $this->regRange = $regRange;
             $this->getList();
         }
        private function getList()
         {
             
             $hobj = phpQuery::newDocumentHTML($this->html);
             if(!empty($this->regRange))
             {
             $robj = pq($hobj)->find($this->regRange);
            
              $i=0;
             foreach($robj as $item)
             {
                 
                 while(list($key,$reg_value)=each($this->regArr))
                 {
                     $iobj = pq($item)->find($reg_value[0]);
                    
                       switch($reg_value[1])
                       {
                           case 'text':
                                 $this->jsonArr[$i][$key] = trim(pq($iobj)->text());
                                 break;
                           case 'html':
                                 $this->jsonArr[$i][$key] = trim(pq($iobj)->html());
                                 break;
                           default:
                                $this->jsonArr[$i][$key] = pq($iobj)->attr($reg_value[1]);
                                break;
                           
                        }
                 }
                 //重置数组指针
                 reset($this->regArr);
                 $i++;
              }
             }
             else
             {
            while(list($key,$reg_value)=each($this->regArr))
             {
                $lobj = pq($hobj)->find($reg_value[0]);
                   
                   
                   $i=0;
                   foreach($lobj as $item)
                   {
                       switch($reg_value[1])
                       {
                           case 'text':
                                 $this->jsonArr[$i++][$key] = trim(pq($item)->text());
                                 break;
                           case 'html':
                                 $this->jsonArr[$i++][$key] = trim(pq($item)->html());
                                 break;
                           default:
                                $this->jsonArr[$i++][$key] = pq($item)->attr($reg_value[1]);
                                break;
                           
                        }
                      
                     
                   }
                 
        
             }
           }
         }  
         function getJSON()
         {
             return json_encode($this->jsonArr);
         } 
        
}

