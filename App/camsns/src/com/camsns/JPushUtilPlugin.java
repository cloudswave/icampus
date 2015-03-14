package com.camsns;

import org.apache.cordova.api.CallbackContext;
import org.apache.cordova.api.CordovaPlugin;
import org.json.JSONArray;
import org.json.JSONException;

import android.content.Context;
import android.util.Log;

import cn.jpush.android.api.JPushInterface;

public class JPushUtilPlugin extends CordovaPlugin {

	@Override 
	public boolean execute(String action, JSONArray data, CallbackContext callbackContext) throws JSONException {
		Log.d("JPushUtilPlugin", "[JPushUtilPlugin] get name:"+data.getString(0));
		Context context = cordova.getActivity().getApplicationContext();
		JPushInterface.setAliasAndTags(context, data.getString(0), null);
		return true;
	}

}
