<?php
$f = fopen("word.txt",'r');

//连接数据库
            $hostname = SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT;
            $dbuser = SAE_MYSQL_USER;
            $dbpass = SAE_MYSQL_PASS;
            $dbname = SAE_MYSQL_DB;
            $link = mysql_connect($hostname, $dbuser, $dbpass);
            if (!$link) {
                die('Could not connect: ' . mysql_error());
            }

            mysql_select_db("app_sjturg",$link);
            

if ($f) {
	$i = 1;
    $num = '0';
    while (!feof($f)) {
		$line = fgets($f);
		$array = explode("\t",$line,3);
        if($num == $array[0])
            continue;
        $num = $array[0];
        $array[2] = str_replace("\t",'',$array[2]);
        mysql_query("set names utf8",$link);
		mysql_query("INSERT INTO tofel values('{$i}','{$array[1]}','{$array[2]}')",$link);
        $i++;
    }
}


mysql_close($link);
fclose($f);
echo "upload successed!";
?> 
