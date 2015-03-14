
	function mousemove(id){
		$("#"+id).css({'background':'#F8F8F8','border':'1px solid #DDD'});
		$("#"+id).mouseout(function(){
			$("#"+id).css({'background':'none','border':'1px solid #FFF'});
		});
		$("#"+id).click(function(){
			//$("#"+id).css({'background':'none','border':'none'});
			sid=id.split('shop_item_');
			//location.href="index.php?app=shop&mod=Index&act=shop_item&sid="+sid[1];
		});
	}

	function friend_card(id){
		$("#"+id).css({'background':'#F3F3F3'});
		$("#"+id).mouseout(function(){
			$("#"+id).css({'background':'none'});
		});
		$("#"+id).click(function(){
			//$("#"+id).css({'background':'none','border':'none'});
			sid=id.split('shop_item_');
			//location.href="index.php?app=shop&mod=Index&act=shop_item&sid="+sid[1];
		});
	}
	
	function follow(uid){
		$.post("index.php?app=public&mod=Follow&act=doFollow&fid="+uid,{ fid:uid },function(data){
			if(data.status){
				ui.success("关注成功");
				$("#follow_"+uid).text("已关注");
			}
		},'json');
	}
	
	function showlast(SysSecond,id){
		if(SysSecond>0){
			var second = Math.floor(SysSecond % 60);             // 计算秒     
			var minite = Math.floor((SysSecond / 60) % 60);      //计算分 
			var hour = Math.floor((SysSecond / 3600) % 24);      //计算小时 
			var day = Math.floor((SysSecond / 3600) / 24);        //计算天 
			$("#"+id).html(day + "天" + hour + "小时" + minite + "分" + second + "秒");
			SysSecond = SysSecond - 1; 
			window.setTimeout(function(){showlast(SysSecond,id)},1000);
		}else{
			$("#last_time").html("已经结束");
		}
	}
	
	$(document).ready(function(){
		$("#submit").click(function(){
			var shop_num = $("#shop_num").attr('value');
			$.post("index.php?app=shop&mod=Index&act=checkuserinfo",{uid:UID,sid:SID,num:shop_num},function(data){
				if(data.status==1){
					$.post("index.php?app=shop&mod=Index&act=convert",{uid:UID,sid:SID,num:shop_num},function(data){
						ui.success("兑换成功");
					},'json');
				}else if(data.status==0){
					ui.error("积分不足，兑换失败");
				}else{
					ui.error("商品剩余不足，无法兑换");
				}
			},'json');
		});
		//SysSecond1 = $("#show_time").html();
		//showlast();
		
	});