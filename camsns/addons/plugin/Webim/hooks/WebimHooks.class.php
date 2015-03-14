<?php

class WebimHooks extends Hooks
{

    //钩子
    public function public_head($param) {
        //头部钩子，预留接口，否则添加新钩子不会载入钩子，必须重装才有效
    }

    public function public_footer($param) {
        echo '<script src="'. SITE_URL .'/addons/plugin/Webim/index.php?action=run"></script> ';
    }

	public function config(){
		$imc = require_once SITE_PATH . '/addons/plugin/Webim/conf/config.php';
		$this->assign('IMC', $imc);
		$this->display('config');
	}

	public function saveConfig() {
        $cfg = require SITE_PATH . '/addons/plugin/Webim/conf/config.php';
        if(!$_POST['domain']) {
			$this->error('注册域名不能为空');
            return;
        }
        $cfg['domain'] = $_POST['domain'];
        if(!$_POST['apikey']) {
			$this->error('ApiKey不能为空');
            return;
        }
        $cfg['apikey'] = $_POST['apikey'];
        if(!$_POST['host'] || !$_POST['port']) {
			$this->error('IM服务器和端口不能为空');
            return;
        }
		$cfg['isopen'] = $this->toBool($_POST['isopen']);
        $cfg['host'] = $_POST['host'];
        $cfg['port'] = $_POST['port'];
        $cfg['local'] = $_POST['local'];
        $cfg['emot'] = $_POST['emot'];
        $cfg['opacity'] = $_POST['opacity'];
        $cfg['show_realname'] = $this->toBool($_POST['show_realname']);
        $cfg['enable_room'] = $this->toBool($_POST['enable_room']);
        $cfg['enable_chatlink'] = $this->toBool($_POST['enable_chatlink']);
        $cfg['enable_menu'] = $this->toBool($_POST['enable_menu']);
		$cfg['enable_noti'] = $this->toBool($_POST['enable_noti']); 
		$cfg['admin_uids'] = $_POST['admin_uids'];
		$cfg['visitor'] = $this->toBool($_POST['visitor']);
		$cfg['show_unavailable'] = $this->toBool($_POST['show_unavailable']);
        $this->writeConfig($cfg);
        $this->success('设置成功');
	}

	public function writeConfig($cfg) {
		$data = '<?php return ' . var_export($cfg, true) . ';';
		$file = fopen(SITE_PATH. '/addons/plugin/Webim/conf/config.php', "wb");
		fwrite($file, $data);  
		@fclose($file);
	}

    public function scanDir( $dir ) {
        $d = dir( $dir."/" );
        $dn = array();
        while ( false !== ( $f = $d->read() ) ) {
            if(is_dir($dir."/".$f) && $f!='.' && $f!='..') $dn[]=$f;
        }
        $d->close();
        return $dn;
    }

	public function skin() {
		$cfg = require SITE_PATH. '/addons/plugin/Webim/conf/config.php';
        $path = SITE_PATH. '/addons/plugin/Webim/static/themes';
		$theme_url = SITE_URL. '/addons/plugin/Webim/static/themes';

        $files = $this->scanDir($path);
        $themes = array();
        foreach ($files as $k => $v){
            $t_path = $path.'/'.$v;
            if(is_dir($t_path) && is_file($t_path."/jquery.ui.theme.css")) {
                $cur = $v == $cfg['theme'] ? " class='current'" : "";
				$themes[] = "<li$cur><a href=\"javascript:;\" onclick=\"fChange('{$v}',$(this));\"><img width=100 height=134 src='$theme_url/images/$v.png' alt='$v' title='$v'/></a></li>";
            }
        }
		$this->assign('themes', $themes);
	    $this->display('skin');
	}

	public function saveSkin() {
		if($_POST) {
			$cfg = require SITE_PATH. '/addons/plugin/Webim/conf/config.php';
			$cfg['theme'] = $_POST['theme'];
			$this->writeConfig($cfg);
		    $this->success('设置成功, 主题设置为: ' . $_POST['theme']);
		}
	}

	public function history() {
	    $this->display('history');
	}

	public function clearHistory() {
		if($_POST) {
		    switch( $_POST['ago'] ) {
			case 'weekago':
				$ago = 7*24*60*60;break;
			case 'monthago':
				$ago = 30*24*60*60;break;
			case '3monthago':
				$ago = 3*30*24*60*60;break;
			default:
				$ago = 0;
			}
			$ago = ( time() - $ago ) * 1000;
			$db_prefix = C('DB_PREFIX');
			$sql = "DELETE FROM `{$db_prefix}webim_histories` WHERE `timestamp` < {$ago}";
		    D()->execute($sql);
		    $this->success('清除成功: ' . $sql);
	    }
	}

	private function toBool($s) {
		return $s == 'true' ? true : false;
	}

}
