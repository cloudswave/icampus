/*
       Licensed to the Apache Software Foundation (ASF) under one
       or more contributor license agreements.  See the NOTICE file
       distributed with this work for additional information
       regarding copyright ownership.  The ASF licenses this file
       to you under the Apache License, Version 2.0 (the
       "License"); you may not use this file except in compliance
       with the License.  You may obtain a copy of the License at

         http://www.apache.org/licenses/LICENSE-2.0

       Unless required by applicable law or agreed to in writing,
       software distributed under the License is distributed on an
       "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
       KIND, either express or implied.  See the License for the
       specific language governing permissions and limitations
       under the License.
 */

package com.camsns;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.Intent;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.net.Uri;
import android.os.Bundle;
import android.util.Log;
import android.view.KeyEvent;
import android.view.Menu;
import android.view.MenuItem;

import org.apache.cordova.*;


import cn.jpush.android.api.JPushInterface;


public class MainActivity extends DroidGap {
	public static boolean isForeground = false;
	//public String WEBSITE=this.getString(R.string.site_url);
	public static String WEBSITE="http://xlanlab.com";

	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);

		// Set by <content src="index.html" /> in config.xml
		// super.loadUrl(Config.getStartUrl());
		// super.setBooleanProperty("loadInWebView", true);

		// Set properties for activity
		// super.setStringProperty("loadingDialog", "Title,Message"); // show
		// loading dialog
		// super.setStringProperty("errorUrl",
		// "file:///android_asset/www/error.html"); // if error loading file in
		// super.loadUrl().
		super.init();
		// this.appView.setBackgroundResource(R.drawable.splash);//设置背景图片
		// super.setIntegerProperty("splashscreen", R.drawable.splash);


		if (netIsAvailable()) {
			 //super.loadUrl("file:///android_asset/www/login.html",10000);
			InitArg();
		} else {
			super.loadUrl("file:///android_asset/www/error.html", 10000);
		}

	}

	public boolean netIsAvailable() {
		ConnectivityManager cwjManager = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
		NetworkInfo info = cwjManager.getActiveNetworkInfo();
		if (info != null && info.isAvailable()) {
			return true;
		}
		return false;
	}

	public boolean onCreateOptionsMenu(Menu menu) {
		menu.add(0, 1, 1, R.string.about);
		menu.add(0, 2, 2, R.string.index);
		menu.add(0, 3, 3, R.string.exit);
		// TODO Auto-generated method stub
		return super.onCreateOptionsMenu(menu);
	}

	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
		if (item.getItemId() == 3) {
			finish();
		}
		if (item.getItemId() == 1) {
			if (netIsAvailable()) {
				super.loadUrl(WEBSITE+"/index.php?app=public&mod=Index&act=about");
			} else {
				super.loadUrl("file:///android_asset/www/error.html");
			}
		}
		if (item.getItemId() == 2) {
			if (netIsAvailable()) {
				super.loadUrl(WEBSITE);
			} else {
				super.loadUrl("file:///android_asset/www/error.html");
			}
		}
		return super.onOptionsItemSelected(item);
	}

	@Override
	protected void onResume() {
		Log.d(TAG, "onResume...");
//		Intent intent = getIntent();
//		if ("GoUrl" == intent.getAction()) {
//			Bundle bundle = getIntent().getExtras();
//			String title = bundle
//					.getString(JPushInterface.EXTRA_NOTIFICATION_TITLE);
//			String goUrl = bundle.getString(JPushInterface.EXTRA_EXTRA);
//			String content = bundle.getString(JPushInterface.EXTRA_ALERT);
//			Log.d("PageActivity", "[PageActivity] 内容：" + title + "|" + goUrl
//					+ "|" + content);
//
//		}

		super.onResume();
		isForeground = true;
		JPushInterface.onResume(this);

	}

	@Override
	protected void onPause() {
		super.onPause();
		isForeground = false;
		JPushInterface.onPause(this);
	}

	@Override
	protected void onNewIntent(Intent intent) {
		Log.d("MainActivity", "onNewIntent----");
		super.onNewIntent(intent);
		setIntent(intent);
		if ("GoUrl".equals(intent.getAction())) {
			InitArg();
		}
		
	}

	@SuppressLint("ParserError")
	private void InitArg() {
		String go_url="file:///android_asset/www/login.html";
		Intent intent = getIntent();
		if (null != intent) {
			Log.d("MainActivity", "[MainActivity] intent.getAction()：" + intent.getAction());
			if ("GoUrl".equals(intent.getAction())) {
				Bundle bundle = getIntent().getExtras();
				String title = bundle
						.getString(JPushInterface.EXTRA_NOTIFICATION_TITLE);
				String extra_field = bundle.getString(JPushInterface.EXTRA_EXTRA);
				String content = bundle.getString(JPushInterface.EXTRA_ALERT);
				Log.d("MainActivity", "[MainActivity] 内容：" + title + "|"
						+ extra_field + "|" + content);
				go_url=get_url(extra_field);
			}

		}
		Log.d("MainActivity", "[MainActivity] go to：" + go_url);
		super.loadUrl(go_url, 10000);
	}
	
	
	@SuppressLint("ParserError")
	private String get_url(String strResult) {
		String go_url=WEBSITE;
		go_url=Util.getGoUrl(strResult);
		
		return go_url;

	}
	
	

	@Override
	public boolean onKeyDown(int keyCode, KeyEvent event) {
	                if (keyCode == KeyEvent.KEYCODE_BACK) {
	                                moveTaskToBack(false);  
	                                return true;
	                }
	     return super.onKeyDown(keyCode, event);
    }
	
	
	private void openBrowser(String url){
		   //urlText是一个文本输入框，输入网站地址
		   //Uri  是统一资源标识符
		   Uri  uri = Uri.parse(url);
		   Intent  intent = new  Intent(Intent.ACTION_VIEW, uri);
		   startActivity(intent);
		}
	
}
