var JPushUtil = function() { };
 
JPushUtil.prototype.SetAlias = function(name, successCallback, failureCallback ) {
    return cordova.exec(successCallback, failureCallback,  'JPushUtilPlugin', 'setAlias', [name]);
};
 
if(!window.plugins) {
    window.plugins = {};
}

if (!window.plugins.JPushUtil) {
    window.plugins.JPushUtil = new JPushUtil();
}