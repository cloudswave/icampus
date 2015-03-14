<?php
$path = dirname(__FILE__);
$files = scandir($path);
$themes = array();
$theme_colors = array();
foreach ($files as $k => $v){
	$t_path = $path.DIRECTORY_SEPARATOR.$v;
	$css_file = $t_path.DIRECTORY_SEPARATOR."jquery.ui.theme.css";
	if(is_dir($t_path) && is_file($css_file)){
		array_push($themes, $v);
		$cont = file_get_contents($css_file);
		preg_match('/bgColorHeader=(\w+)/', $cont, $color);
		if(isset($color[1])){
			$theme_colors[$v] = $color[1];
		}
	}
}
//var_export($theme_colors);
echo "var themes = \"".implode(",", $themes)."\"";
?>
