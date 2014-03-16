<? $page='upgrade_from_v0.6.7_to_v0.6.8' ?>
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
		ALTER TABLE `user`
		ADD `time_zone` VARCHAR( 128 ) NOT NULL DEFAULT 'Europe/Moscow'
	");

?>
</body>
</html>
