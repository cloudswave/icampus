<?php
/**********************
一个简单的目录递归函数
第一种实现办法：用dir返回对象
***********************/
function tree($directory) 
{ 
	$mydir = dir($directory); 
	echo "<ul>\n"; 
	while($file = $mydir->read())
	{ 
		if((is_dir("$directory/$file")) AND ($file!=".") AND ($file!="..")) 
		{
			echo "<li><font color=\"#ff00cc\"><b>$file</b></font></li>\n"; 
			tree("$directory/$file"); 
		} 
		else 
		echo "<li>$file</li>\n"; 
	} 
	echo "</ul>\n"; 
	$mydir->close(); 
} 
//开始运行

echo "<h2>目录为粉红色</h2><br>\n"; 
tree("./"); 

/***********************
第二种实现办法：用readdir()函数
************************/
function listDir($dir)
{
	if(is_dir($dir))
   	{
     	if ($dh = opendir($dir)) 
		{
        	while (($file = readdir($dh)) !== false)
			{
     			if((is_dir($dir."/".$file)) && $file!="." && $file!="..")
				{
     				echo "<b><font color='red'>文件名：</font></b>",$file,"<br><hr>";
     				listDir($dir."/".$file."/");
     			}
				else
				{
         			if($file!="." && $file!="..")
					{
         				echo $file."<br>";
      				}
     			}
        	}
        	closedir($dh);
     	}
   	}
}
//开始运行
listDir("./");
?>
