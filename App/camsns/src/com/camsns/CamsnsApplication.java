package com.camsns;

import android.app.Application;
import android.util.Log;
import cn.jpush.android.api.JPushInterface;

/**
 * For developer startup JPush SDK
 * 
 * 一般建议在自定义 Application 类里初始化。也可以在主 Activity 里。
 */
public class CamsnsApplication extends Application {
    private static final String TAG = "JPush";

    @Override
    public void onCreate() {    	     
    	 Log.d(TAG, "[CamsnsApplication] onCreate");
         super.onCreate();
         
         JPushInterface.setDebugMode(true); 	// 设置开启日志,发布时请关闭日志
         JPushInterface.init(this);     		// 初始化 JPush
         String regIDString=JPushInterface.getRegistrationID(getApplicationContext());
         Log.d(TAG, "[CamsnsApplication] RegistrationID:"+regIDString);
         
    }
}
