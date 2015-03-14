<?php
    /**
     * UserGiftModel
     * 用户送礼数据模型
     *
     * @uses 
     * @package 
     * @version 
     * @copyright 2009-2011 SamPeng 
     * @author SamPeng <sampeng87@gmail.com> 
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
class UserGiftModel extends Model{
		var $tableName = 'gift_user';
		private $gift;        //礼品表模型
		private $category;    //礼品类型表模型

		public function setGift($gift){
			$this->gift = $gift;  //赋值礼品表模型
		}
		public function setCategory($category){
			$this->category = $category;  //赋值礼品类型表模型
		}	
			
		/**
		 * receiveGift
		 * 获得某个人收取的礼物
		 * @param $uid
		 * @return Gift;
		 */
		public function receiveList($uid){
			$map['toUserId'] = $uid;
			return $this->where($map)->order('id desc')->findPage(15);
		}
		
		/**
		 * sendGift
		 * 获得某个人发送的礼物列表
		 * @param $uid
		 * @return unknown_type
		 */
		public function sendList($uid){
			$map['fromUserId'] = $uid;
			return $this->where($map)->order('id desc')->findPage(15);
		}
		
		/**
		 * sendGift
		 * 发送礼物
		 * 
		 * @param array $toUid  接收礼品人的ID（可以多个，以，分隔）
		 * @param $fromUid  送礼者ID
		 * @param $sendInfo  附加信息和发送方式
		 * @param $giftInfo  礼品信息
		 */
		public function sendGift($toUid,$fromUid,array $sendInfo,array $giftInfo){
			//判断参数是否合法.不合法返回false
			if(!is_numeric($fromUid)){				
				return '非法操作！';
			}

			$toUser = explode(',',$toUid);
			$userNum = count($toUser);
	
			//判断是否是自己给自己送礼物
			if(in_array($fromUid,$toUser)){
				return '不能给自己送礼物！';
			}
			// //判断是否有足够的礼物数
			if($this->gift->assertNumAreEmpty($giftInfo['id'],$userNum)){
				return '礼物库存不足，发送礼品失败！';
			}

			//扣除相应积分
			$giftPrice = intval($giftInfo['price']);
			$prices = $userNum*$giftPrice;			
			$moneyType = gift_getConfig('credit');
			//积分操作			
			$setCredit = model('Credit');
			//检测积分是否足够
			$userCredit = $setCredit->getUserCredit($fromUid);
			// 测试时注释以下代码（积分不够）
			if($userCredit['credit'][$moneyType]['value']<$prices){
				return $userCredit['credit'][$moneyType]['alias'].'不足，不能赠送~~';	}
			$setCredit->setUserCredit($fromUid,array($moneyType=>$prices),-1);
			
			
			$map['giftPrice']    = $giftPrice;
			$map['giftImg']      = t($giftInfo['img']);
			$map['sendInfo']     = t($sendInfo['sendInfo']);
			$map['sendWay']      = intval($sendInfo['sendWay']);
			$map['fromUserId']   = intval($fromUid);
			$map['cTime']        = time();

			$res = $this->__insertData($toUser,$map);
			
			//如果入库过程成功.则做相应的处理
			if($res){
				//礼物数减
				$this->gift->setDec('num','id='.$giftInfo['id'],$userNum);
				//给接收人发送通知
				$this->__doNotify($toUser,$sendInfo,$giftInfo,$fromUid,$appId);

				return true;
			}else{
				return '发送礼品失败！';
			}	
			
		}
		// 我送给@雪儿 一份礼物:【小人】 参与送礼http://192.168.1.100/ts2/index.php?app=gift&mod=Index&act=index&uid=23481

		/**
		 * __insertData
		 * 把数据插入数据库
		 * @param $toUser 发送对象ID $map 数据组
		 * @return $add 插入结果集;
		 */		
		private function __insertData($toUser,$map){
			foreach ($toUser as $_touid){
				//组成数据集
				$map['toUserId']     = intval($_touid);
				//将信息入库
				$res = $this->add($map);
			}
			return $res;
		}

		/**
		 * __doNotify
		 * 发送系统通知
		 * @param $sendInfo 附加信息 $giftInfo 礼品信息 $toUser 发送对象ID
		 * @return $feedId 插入结果;
		 */			
		private function __doNotify($toUser,$sendInfo,$giftInfo,$fromUid,$appId){
				//礼品图片
				$data['img']         = realityImage($giftInfo['img'],$giftInfo['name']);
				//附加消息，用文本过滤t函数过滤危险代码
				$sendInfo['sendInfo'] && $data['content'] = '并对TA说“'.t($sendInfo['sendInfo']).'”';
				//赠送的对象名称 用于公开赠送微博
				$toUserName = NULL;
               //根据赠送方式组装数据
				foreach ($toUser as $fid){
					switch ($sendInfo['sendWay']){
						case 1:   //公开
							$username = getUserName($fromUid);
							$data['sendback']     = '<br/><a href="'.U('gift/Index/index',array('uid'=>$fromUid)).'">给'.$username.'回赠礼物</a>';
							// 通知
							model('Notify')->send($fid,'gift_send',$data,$fromUid);
							//赠送对象名称
							$toUserName .= '@'.getUserName($fid).' ';
							break;
						case 2:   //私下
							$username = getUserName($fromUid);
							$data['sendback']     = '<br/><a href="'.U('gift/Index/index',array('uid'=>$fromUid)).'">给'.$username.'回赠礼物</a>';
							// 通知
							model('Notify')->send($fid,'gift_send',$data,$fromUid);
							break;							
						case 3:   //匿名
							$data['actor'] = '神秘人物';
							$data['sendback']  = '';
							// 通知
							// model('Notify')->sendIn($fid,'gift_send',$data);
							model('Notify')->send($fid,'gift_send',$data,$fromUid);
							break;
						default:
							continue;
					}
				}
				//公开则发微薄
				if($toUserName){
					// $_SESSION['gift_send_weibo']=urlencode(serialize(array('user'=>$toUserName,'title'=>$giftInfo['name'],'content'=>$data['content'],'url'=>U('gift/Index/index',array('uid'=>$fid)),'type'=>1,'type_data'=>realityImageURL($giftInfo['img']))));
					$_SESSION['gift_send_weibo']=serialize(array('user'=>$toUserName,'title'=>$giftInfo['name'],'content'=>$data['content'],'url'=>U('gift/Index/index'),'type'=>1,'type_data'=>realityImageURL($giftInfo['img'])));
				}
		}
}