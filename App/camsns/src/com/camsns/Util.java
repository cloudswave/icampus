package com.camsns;

import org.json.JSONException;
import org.json.JSONObject;

public class Util {
	/**
	 * 
	 * @param strResult jpush的额外字段json
	 * @return
	 */
	public static String getGoUrl(String strResult) {
		String go_url="";
		String app="";
		String posterDetail_id="";
		try {
			JSONObject jsonObj = new JSONObject(strResult);
			go_url=jsonObj.getString("go_url");//jpush额外字段
			app = jsonObj.getString("app");//jpush额外字段
			posterDetail_id =  jsonObj.getString("posterDetail");
					
			if (app!=null) {
				go_url=go_url+"/index.php?app="+app;
				if (posterDetail_id!=null) {
					go_url=go_url+"&mod=Index&act=posterDetail&id="+posterDetail_id;
				}
			}else {
				
			}
			
		} catch (JSONException e) {
			System.out.println("Json parse error");
			e.printStackTrace();
		}
		
		return go_url;

	}
	
	
}
