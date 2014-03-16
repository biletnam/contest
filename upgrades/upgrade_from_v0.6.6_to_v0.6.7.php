<? $page='upgrade_from_v0.6.6_to_v0.6.7' ?>
<?php
include_once dirname(__FILE__)."/../config.php";
include_once SITE_ROOT."/core/engine.php";
global $CORE;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><? echo $CORE->title ?></title>
</head>
<body>
<?
	mysql_query_log("
		ALTER TABLE `contest`
		ADD `down_count` INT( 10 ) NOT NULL DEFAULT '0' AFTER `content` ,
		ADD `open_date` DATETIME NOT NULL ,
		ADD `wait_date` DATETIME NOT NULL ,
		ADD `vote_date` DATETIME NOT NULL ,
		ADD `close_date` DATETIME NOT NULL ,
		ADD `auto_tick` BOOL NOT NULL DEFAULT '1'
	");
	function sql_add_translit($table){
		mysql_query_log("
			ALTER TABLE `$table` ADD `name_translit` VARCHAR( 128 ) NULL AFTER `name` ,
			ADD UNIQUE (
			`name_translit`
			)
		");
	}
	function upadate_translit($table)
	{
		$ret=mysql_query_array("
			SELECT *
			FROM `$table`
		");
		$sql="
			REPLACE `$table`
			VALUES
		";
		foreach($ret as &$c){
			$c['name_translit']=rus2lat($c['name']);
			$text='(';
			foreach($c as &$v) $v="'".mysql_real_escape_string($v)."'";
			$text.=implode(",",array_values($c));
			$text.=')';
			$c=$text;
		}
		$sql.=implode(",\n",array_values($ret));
		//var_export($sql);
		$ret=mysql_query_log($sql);	
	}
	function sql_edit_translit($table){
		mysql_query_log("
			ALTER TABLE `$table` CHANGE `name_translit` `name_translit` VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL 
		");
	}
	foreach(array('user','team','contest') as $table){
		sql_add_translit($table);
		upadate_translit($table);
		sql_edit_translit($table);
	}
?>
</body>
</html>
