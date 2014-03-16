<?php
include_once dirname(__FILE__)."/config.php";
include_once SITE_ROOT."/core/engine.php";
global $CORE;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><? echo $CORE->title ?></title>
<link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
<link rel="icon" href="/favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
<? if($CORE->allow_debug){ ?>
<script src="js/jquery-1.3.2.min.js" language="javascript"></script>
<script src="js/jquery.cookie.js" language="javascript"></script>
<script language="javascript">
	$(document).ready(function(){
		$('input.debug').click(function(){
			if($(this).attr('checked'))
				$.cookie('login_debug',true)
			else
				$.cookie('login_debug',null);
			setTimeout('window.location=window.location', 100);
		}).attr('checked',$.cookie('login_debug') != undefined);
		$("select[name=login_name]").change(function(){
			var form=$(this).parent().attr('action','?<? echo quotemeta($_SERVER['QUERY_STRING']) ?>');
			$('<input type="submit" name="login_act" value="enter" />').appendTo(form).click();
		});
	});
</script>
<? } ?>
</head>
<body>
<div align="center">
<h1>Система управления конкурсами [v<? echo $CORE->version ?>]</h1>
<span><? echo $LOGIN->date_now ?></span>
</div>
<div id="login">
<form id="form_login" name="form_login" method="post" action=""><?
global $LOGIN;
if($LOGIN->debug){
	?> <label for="login_name">пользователь: </label> <select
	name="login_name" id="login_name">
	<option></option>
	<?
	$_REQUEST['of']='user';
	include "core/list.php";
	if(!process_errors()){
		foreach($OUT->result as $item){
			echo "<option value='{$item['name']}' ".($LOGIN->id==$item['id']?"selected":"").">{$item['name']}</option>";
		}
	};
	?>
</select> <?
}
if($LOGIN->valid){
	if(!$LOGIN->debug){ // без отладки
		echo "<b>".$LOGIN->name."</b>";
	} // без отладки
}else{
	if(!$LOGIN->debug){	// без отладки
		?> <label for="login_name">логин</label> <input type="text"
	name="login_name" id="login_name" value="<? echo $LOGIN->name; ?>" /> <label
	for="login_passw">пароль</label> <input type="password"
	name="login_passw" id="login_passw" value="<? echo $LOGIN->passw; ?>" />
		<?
	} // без отладки
	?> <label for="login_act_enter">войти</label> <input type="submit"
	name="login_act" id="login_act_enter" value="enter" /> <a
	href="registry.php">зарегистрироваться</a> <? } ?> <label
	for="login_act_leave">выйти</label> <input type="submit"
	name="login_act" id="login_act_leave" value="leave" /> <? if($CORE->allow_debug){ ?>
<label for="debug">режим отладки</label> <input type="checkbox"
	class="debug" /> <? } ?></form>
</div>
<ul class="menu_bar">
<?
foreach(array(
		'index.php'=>'главная',
		'user.php'=>'пользователь',
		'team.php'=>'команда',
		'contest.php'=>'конкурс',
		'vote.php'=>'голосование',
		'release_notice.htm'=>'о релизе',		
) as $page=>$title){
	?>
	<li <? if($page==PAGE_NAME)echo "class='active_page'";?>><a
		href="<? echo $page;?>"><? echo $title ?></a></li>
		<?
}
?>
</ul>
<div align="center" class='header page_name'><? echo $CORE->title ?></div>