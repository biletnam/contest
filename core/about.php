<?php
include dirname(__FILE__)."/init.php";
try{
	switch($of=@$_REQUEST['of']){
		case 'user':
			if(false !== $ret=mysql_query_array("
				SELECT *
				FROM `user`
				WHERE `id`='".mysql_escape_string($_REQUEST['id'])."'
				LIMIT 1
			")){
				$OUT->result=$ret;
			};
		break;
		case 'team':
			if(false !== $ret=mysql_query_array("
				SELECT *
				FROM `team`
				WHERE `id`='".mysql_escape_string($_REQUEST['id'])."'
				LIMIT 1
			")){
				$OUT->result=$ret;
			};
		break;	
		case 'contest':
			if(false !== $ret=mysql_query_array("
				SELECT *
				FROM `contest`
				WHERE `id`='".mysql_escape_string($_REQUEST['id'])."'
				LIMIT 1
			")){
				$OUT->result=$ret;
			};
		break;	
		case 'teams_for_user':
			if(!isset($_REQUEST['id_user']) || !(int)$_REQUEST['id_user'])
				$OUT->errorcode[]=array_merge($INVALID['id_user'],array('параметр'=>'id_user(код пользователя)'));
			if(!isset($_REQUEST['id_team']) || !(int)$_REQUEST['id_team'])
				$OUT->errorcode[]=array_merge($INVALID['id_team'],array('параметр'=>'id_team(код команды)'));
			if(!count($OUT->errorcode)){// нет ошибок
				if(false!==$ret=mysql_query_assoc("
					# user -> team_access()
					SELECT m.id, t.id as 'team', t.name as 'team_name', t.desc as 'team_desc',
						m.role, r.name as 'role_name', r.desc as 'role_desc'
					FROM (
						SELECT @user:=$id_user, @team:=$id_team
					) as `setup`, team as t
					LEFT JOIN (
						SELECT m.*
						FROM membership as m, team as t, user as u
						WHERE @team=m.team AND @user=m.user AND m.state='accept'
							AND @user=u.id AND u.mktime<=m.mktime # check history user
							AND @team=t.id AND t.mktime<=m.mktime # check history team
					) as m ON ( m.team=t.id )
					LEFT JOIN role as r ON ( m.role=r.id )
					WHERE @team=t.id
					LIMIT 1;
				")){
					$OUT->result[]=$ret;
				};
			}
		break;
		case 'user_data_for_contest':
			if(!isset($_REQUEST['id_user']) || !(int)$_REQUEST['id_user'])
				$OUT->errorcode[]=array_merge($INVALID['id_user'],array('параметр'=>'id_user(код пользователя)'));
			if(!isset($_REQUEST['id_contest']) || !(int)$_REQUEST['id_contest']){
				$OUT->errorcode[]=array_merge($INVALID['id_contest'],array('параметр'=>'id_contest(код конкурса)'));
			}else{$id_contest=(int)$_REQUEST['id_contest'];}
			if(!count($OUT->errorcode)){// нет ошибок
				if(false!==$ret=mysql_query_assoc("
					# user -> data_for_contest()
					# информация об участнике конкурса
					/* 
						name - имя участника ( личное или командное )
						pid - id конкурсанта с правами ( одиночка или лидер команды )
						uid - id конкурсанта причастного к конкурсу ( регистрационное )
						user - id пользователя
						user_name - имя пользователя
						team - id команды
						team_name - имя команды
						role - id роль в команде
						role_name - имя роли в команде
						role_desc - описание роли
						num_vote - кол-во голосов за конкурс
						work - сдана ли работа
					*/
					SELECT CAST( IF( p2.team_name IS NULL,
							p2.user_name,
							CONCAT_WS(' ',p2.team_name,'[',p2.num_members,']','{',p2.user_name,'...','}')
						) AS CHAR ) as 'name',
						p2.id as 'pid',
						p1.id as 'uid',
						u1.id as 'user',
						u1.name as 'user_name',
						p1.team,
						p1.team_name,
						r.id as 'role',
						r.name as 'role_name',
						r.desc as 'role_desc',
						COUNT(v.from) as 'num_vote',
						w.id as 'work'
					FROM 
						(
							SELECT @contest:=$id_contest, @user:=$id_user
						) as `setup`,
						user as u1 # login
						LEFT JOIN (
							SELECT *
							FROM participation as p
							WHERE @contest=p.contest AND @user=p.user
						) as p1 ON ( p1.user = u1.id)
						LEFT JOIN (
							(
								SELECT p.id, p.user, p.user_name, p.team, p.team_name, p.role, p.role_name, p.mktime,
								NULL as 'num_members'
								FROM participation as p
								WHERE @contest=p.contest AND p.team IS NULL
							) UNION (
								SELECT p.id, p.user, p.user_name, p.team, p.team_name, p.role, p.role_name, p.mktime,
								COUNT(p.team) as 'num_members'
								FROM participation as p
								WHERE @contest=p.contest AND p.team IS NOT NULL
								GROUP BY p.team
							)
						) as p2 ON ( IF( p1.team IS NULL, p2.id=p1.id, p2.team=p1.team AND p2.role=1 ) )
						LEFT JOIN (
							SELECT v.from
							FROM vote as v
							WHERE @contest=v.contest
						) as v ON (v.from=u1.id)
						LEFT JOIN work as w ON p2.id=w.pid
						LEFT JOIN role as r ON (IF(p1.id IS NULL, r.id=3, r.id=p1.role))
					WHERE @user=u1.id
					GROUP BY v.from
				")){
					$OUT->result[]=$ret;
				};
			}
		break;
		default:
			$OUT->errorcode[]=array_merge($INVALID['unknown_action'],array('действие'=>"`$of` -  неизвестно",'file:'=>'core/about.php'));
	}
	unset($of);
	response();
}catch (Exception $e) {
	exception_handler($e);
}
?>
