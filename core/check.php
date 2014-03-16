<?php
include dirname(__FILE__)."/init.php";
try{
	switch($act=@$_REQUEST['act']){
		case 'check_login':
			if(!isset($_REQUEST['user_name']) || !preg_match($VALIDATOR['user_name'],$_REQUEST['user_name'])){
				$OUT->errorcode[]=array_merge($INVALID['user_name'],array('параметр'=>'user_name(имя пользователя)'));
			}
			if(!count($OUT->errorcode)){// нет ошибок
				$nt=mysql_real_escape_string(rus2lat($_REQUEST['user_name']));
				$OUT->result[]=(object)array('valid'=>!mysql_query_single("
					SELECT COUNT(*)
					FROM `user`as u, `team` as t, `contest` as c
					WHERE u.`name_translit`='$nt' OR t.`name_translit`='$nt' OR c.`name_translit`='$nt'
					LIMIT 1
				"));
				unset($nt);
			}
		break;
		case 'check-in_user': //проверяет можно ли зайти под юзером
			$item=(object)array('valid'=>(bool)mysql_query_single("
				SELECT `id`
				FROM `user`
				WHERE `name`='".mysql_real_escape_string($_REQUEST['user_name'])."'
					AND `passw`=MD5('".mysql_real_escape_string($_REQUEST['user_passw'])."')
				LIMIT 1
			"));
			$OUT->result[] = $item;
		break;	
		case 'check_team-name':
			if(!isset($_REQUEST['team_name']) || !preg_match($VALIDATOR['team_name'],$_REQUEST['team_name'])){
				$OUT->errorcode[]=array_merge($INVALID['team_name'],array('параметр'=>'team_name(название команды)'));	
			}
			if(!count($OUT->errorcode)){// нет ошибок
				$nt=mysql_real_escape_string(rus2lat($_REQUEST['team_name']));
				$OUT->result[]=(object)array('valid'=>!mysql_query_single("
					SELECT COUNT(*)
					FROM `user`as u, `team` as t, `contest` as c
					WHERE u.`name_translit`='$nt' OR t.`name_translit`='$nt' OR c.`name_translit`='$nt'
					LIMIT 1
				"));
				unset($nt);
			}
		break;	
		case 'check_contest-name':
			if(!isset($_REQUEST['contest_name']) || !preg_match($VALIDATOR['contest_name'],$_REQUEST['contest_name'])){
				$OUT->errorcode[]=array_merge($INVALID['contest_name'],array('параметр'=>'contest_name(название конкурса)'));
			}
			if(!count($OUT->errorcode)){// нет ошибок	
				$nt=mysql_real_escape_string(rus2lat($_REQUEST['contest_name']));
				$OUT->result[]=(object)array('valid'=>!mysql_query_single("
					SELECT COUNT(*)
					FROM `user`as u, `team` as t, `contest` as c
					WHERE u.`name_translit`='$nt' OR t.`name_translit`='$nt' OR c.`name_translit`='$nt'
					LIMIT 1
				"));
				unset($nt);
			}			
		break;
		default:
			$OUT->errorcode[]=array_merge($INVALID['unknown_action'],array('действие'=>"`$act` -  неизвестно",'file:'=>'core/check.php'));
	}
	response();
	unset($act);
}catch (Exception $e) {
	exception_handler($e);
}
?>
