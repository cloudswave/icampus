webim-plugin-thinksns
=====================

Webim plugin for ThinkSNS V3

依赖
====

*	MySQL版本不低于4.1
*	需要PHP版本不低于5.1
*	PHP访问外部网络，WebIM连接时需要访问WebIM服务器, 请确保您的php环境是否可连接外部网络, 设置php.ini中`allow_url_fopen=ON`.
*	ThinkSNS V3.x


安装
=====

*   插件解压到addons/plugin/目录
*   修改配置文件可写: chmod 777 addons/plugin/Webim/conf/config.php
*   后台管理启动WebIM插件
*   WebIM管理界面配置相关参数  

源码
=====

ThinkIM.class.php: 与ThinkSNS集成类，读取ThinkSNS的用户关系、群组关系、通知信息


作者
====

Ery Lee (ery.lee at gmail.com)

http://nextalk.im


