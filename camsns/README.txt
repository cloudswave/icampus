#
# ThinkSNS 安装说明.txt
#

+ 常用路径
  - 安装路径: http://yoursite/install
  - 前台登录: http://yoursite
  - 后台登录: http://yoursite/index.php?app=admin

+ 其他说明
  - 如果安装后遇到数据库链接错误、页面提示_NO_DB_CONFIG_可以执行 /cleancache.php
  - 安装完成后，请到后台全局配置中，对网站logo、登录页图片进行配置管理
  - 开启伪静态和个性化域名:  参见"开启URL伪静态的方法.txt"

+ 注意事项
  - PHP需要开启mysql, gd, curl, mbstring支持
  - _runtime、data、config、install目录需要可写权限(777)
  - 升级用户，请看升级说明（注意升级前做好备份）

+ ThinkSNS V3 安装、升级说明
  http://demo.thinksns.com/t3/index.php?app=weiba&mod=Index&act=postDetail&post_id=640

+ ThinkSNS V3 常见问题解答
  http://demo.thinksns.com/t3/index.php?app=weiba&mod=Index&act=postDetail&post_id=641