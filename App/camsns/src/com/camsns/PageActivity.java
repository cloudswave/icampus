package com.camsns;


import cn.jpush.android.api.JPushInterface;
import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.ViewGroup.LayoutParams;
import android.widget.TextView;

public class PageActivity extends Activity {
	TextView tv;
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        tv = new TextView(this);
        tv.setText("用户自定义打开的Activity");
        show();
    }
    
	@Override
	protected void onNewIntent(Intent intent) {
		Log.d("MainActivity", "onNewIntent----");
		super.onNewIntent(intent);
		setIntent(intent);
		show();
	}
	
    private void show() {
    	Intent intent = getIntent();
        if (null != intent) {
	        Bundle bundle = getIntent().getExtras();
	        String title = bundle.getString(JPushInterface.EXTRA_NOTIFICATION_TITLE);
	        String content = bundle.getString(JPushInterface.EXTRA_ALERT);
	        tv.setText("标题 : " + title + "  " + "内容 : " + content);
        }
        addContentView(tv, new LayoutParams(LayoutParams.FILL_PARENT, LayoutParams.FILL_PARENT));
		
	}

}
