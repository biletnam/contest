<?php
include dirname(__FILE__)."/init.php";
global $LOGIN;
try{
	switch($act=$_REQUEST['act']){
		case 'add_user':
			if(!isset($_REQUEST['user_name']) || !preg_match($VALIDATOR['user_name'],$_REQUEST['user_name'])){
				$OUT->errorcode[]=array_merge($INVALID['user_name'],array('параметр'=>'user_name(имя пользователя)'));
			}
			if(!isset($_REQUEST['user_passw']) || mb_strlen($_REQUEST['user_passw'])<6){
				$OUT->errorcode[]=array_merge($INVALID['user_passw'],array('параметр'=>'user_passw(пароль пользователя)'));			
			}
			if(!isset($_REQUEST['user_time_zone']) || !in_array($_REQUEST['user_time_zone'],timezone_identifiers_list())){
				$OUT->errorcode[]=array_merge($INVALID['user_time_zone'],array('параметр'=>'user_time_zone(временная зона пользователя)'));			
			}
			if(!count($OUT->errorcode)){// нет ошибок
				$nt=mysql_real_escape_string(rus2lat($_REQUEST['user_name']));
				mysql_query_log("
					INSERT IGNORE INTO `user`(`name`,`passw`,`name_translit`,`time_zone`)
					SELECT @name as 'name',@passw as 'passw',
						'$nt' as 'name_translit',
						'".mysql_real_escape_string($_REQUEST['user_time_zone'])."' as 'time_zone'
					FROM (
						SELECT
						@name:='{$_REQUEST['user_name']}',
						@passw:=MD5('".mysql_real_escape_string($_REQUEST['user_passw'])."')
					) as `setup`,
					(
						SELECT COUNT(*) as 'c'
						FROM user as u, team as t, contest as c
						WHERE u.`name_translit`='$nt' OR t.`name_translit`='$nt' OR c.`name_translit`='$nt'
					) as ex
					WHERE ex.c=0
				");
				unset($nt);
				$OUT->result[]=(object)array('idUser'=>$id=mysql_insert_id(),'valid'=>(bool)$id);
			}
		break;
		case 'add_team':
			//input test
			if(!$LOGIN->valid){
				$OUT->errorcode[]=$INVALID['login_error'];	
			}
			if(!isset($_REQUEST['team_name']) || !preg_match($VALIDATOR['team_name'],$_REQUEST['team_name'])){
				$OUT->errorcode[]=array_merge($INVALID['team_name'],array('параметр'=>'team_name(название команды)'));	
			}
			if(!count($OUT->errorcode)){// нет ошибок
				$nt=mysql_real_escape_string(rus2lat($_REQUEST['team_name']));
				$team_desc=isset($_REQUEST['team_desc'])?htmlspecialchars($_REQUEST['team_desc']):'';
				mysql_query_log("begin");
				mysql_query_log("
					INSERT IGNORE INTO team (`name`,`name_translit`,`desc`)
					SELECT 
						@name as 'name',
						'$nt' as 'name_translit',
						@desc as 'desc'
					FROM (
						SELECT @user:={$LOGIN->id},
						@name:='".mysql_real_escape_string($_REQUEST['team_name'])."',
						@desc:='".mysql_real_escape_string($team_desc)."'
					) as `setup`,
					(
						SELECT COUNT(*) as 'c'
						FROM user as u, team as t, contest as c
						WHERE u.`name_translit`='$nt' OR t.`name_translit`='$nt' OR c.`name_translit`='$nt'
					) as ex
					WHERE ex.c=0
				");
				unset($nt);
				$r=(bool) $idTeam=mysql_insert_id();
				$item=(object)array('idTeam'=>$idTeam,'valid'=>false,'valid_team'=>(bool)$idTeam);
				$OUT->result[] =& $item;
				if(!$r){
					mysql_query_log("rollback");
					break;				
				}
				mysql_query_log("
					INSERT IGNORE INTO membership
					SET `user`=@user, `team`=LAST_INSERT_ID(), `role`=1, state='accept';
				");
				$r=(bool)$item->idMembership=mysql_insert_id();
				$item->valid_membership=!mysql_errno();
				if(!$r){
					mysql_query_log("rollback");
					break;				
				}else{
					$item->valid=true;
					mysql_query_log("commit");
				}
			}
		break;	
		case 'member_leave_team':
			if(!$LOGIN->valid){
				$OUT->errorcode[]=$INVALID['login_error'];
			}
			if(!isset($_REQUEST['id_member']) || !(int)$_REQUEST['id_member']){
				$OUT->errorcode[]=array_merge($INVALID['id_member'],array('параметр'=>'id_member(код участника)'));
			}else{$id_member=(int)$_REQUEST['id_member'];}
			if(!count($OUT->errorcode)){// нет ошибок
				mysql_query_log("
					UPDATE IGNORE `membership`
					SET `state`='leave'
					WHERE id='$id_member' AND `user`='".$LOGIN->id."'
					LIMIT 1
				");
				$item=(object)array('valid'=>(bool)mysql_affected_rows());
				$OUT->result[] =& $item;
			}				
		break;
		case 'edit_team':
			if(!$LOGIN->valid){
				$OUT->errorcode[]=$INVALID['login_error'];
			}
			if(!isset($_REQUEST['id']) || !(int)$_REQUEST['id']){
				$OUT->errorcode[]=array_merge($INVALID['id_team'],array('параметр'=>'id(код команды)'));
			}else{$id=(int)$_REQUEST['id'];}
			if(!isset($_REQUEST['team_desc'])){
				$OUT->errorcode[]=array_merge($INVALID['team_desc'],array('параметр'=>'team_desc(описание команды)'));
			}else{$team_desc=htmlspecialchars($_REQUEST['team_desc']);}
			if(!count($OUT->errorcode)){// нет ошибок
				mysql_query_log("
					UPDATE IGNORE `team`,(
						SELECT @valid:='1'
						FROM `membership`
						WHERE `user`='{$LOGIN->id}'
							AND `team`='$id' AND `role`=1 AND `state`='accept'
						LIMIT 1
					) as `check`
					SET `desc`='".mysql_real_escape_string($team_desc)."'
					WHERE @valid=1 AND `id`='$id'
				");		
				$item=(object)array('valid'=>(bool)mysql_affected_rows());
				$OUT->result[] =& $item;
			}				
		break;
		case 'team-leader_remove_member': // лидер команды удаляет участника
			if(!$LOGIN->valid){
				$OUT->errorcode[]=$INVALID['login_error'];
			}
			if(!isset($_REQUEST['member']) || !(int)$_REQUEST['member']){
				$OUT->errorcode[]=array_merge($INVALID['id_member'],array('параметр'=>'member(код участника)'));
			}else{$member=(int)$_REQUEST['member'];}
			if(!count($OUT->errorcode)){// нет ошибок
				mysql_query_log("
					UPDATE IGNORE `membership` as m,(
						SELECT m.team
						FROM `membership` as m
						WHERE `user`='{$LOGIN->id}'
							AND `role`=1 AND `state`='accept'
					) as `check`
					SET m.`state`='leave'
					WHERE m.`id`='$member' AND `check`.team=m.team
				");		
				$item=(object)array('valid'=>(bool)mysql_affected_rows());
				$OUT->result[] =& $item;
			}				
		break;
		// пользователь отправляет предложение всутпить в команду 
		case 'user_enter-to_team_offer': 
			if(!$LOGIN->valid){
				$OUT->errorcode[]=$INVALID['login_error'];
			}
			if(!isset($_REQUEST['id']) || !(int)$_REQUEST['id']){
				$OUT->errorcode[]=array_merge($INVALID['id_team'],array('параметр'=>'id(код команды)'));
			}else{$id=(int)$_REQUEST['id'];}
			if(!count($OUT->errorcode)){// нет ошибок
				mysql_query_log("
					# user -> team? {team += user}
					INSERT IGNORE INTO `membership` (`team`,`user`,`role`,`state`)
					SELECT @team as 'team', @user as 'user', @role as 'role', 'candidate' as 'state'
					FROM (
						SELECT @team:=$id, @user:={$LOGIN->id}, @role:=2
					)as `setup`, team as t, user as u
					LEFT JOIN(
						SELECT m.user
						FROM membership as m, team as t
						WHERE @team=m.team AND m.state IN ('accept','candidate','invite')
							AND @team=t.id AND t.mktime <= m.mktime
					) as e ON (e.user=u.id),
					role as r
					WHERE @team=t.id AND @user=u.id AND @role=r.id AND e.user IS NULL
					LIMIT 1;
				");		
				$item=(object)array('valid'=>(bool)mysql_affected_rows());
				$OUT->result[] =& $item;	
			}			
		break;
		// команда отправляет предложение пользователю всутпить в команду
		case 'team-leader_invite-user_to-team': 
			if(!$LOGIN->valid){
				$OUT->errorcode[]=$INVALID['login_error'];
			}
			if(!isset($_REQUEST['id']) || !(int)$_REQUEST['id']){
				$OUT->errorcode[]=array_merge($INVALID['id_team'],array('параметр'=>'id(код команды)'));
			}else{$id=(int)$_REQUEST['id'];}
			if(!isset($_REQUEST['id_user']) || !(int)$_REQUEST['id_user']){
				$OUT->errorcode[]=array_merge($INVALID['id_user'],array('параметр'=>'id_user(код пользователя)'));
			}else{$id_user=(int)$_REQUEST['id_user'];}
			if(!count($OUT->errorcode)){// нет ошибок
				mysql_query_log("
					# team -> user? {team += user}
					INSERT IGNORE INTO `membership` (`team`,`user`,`role`,`state`)
					SELECT @team as 'team', @user as 'user', @role as 'role', 'invite' as 'state'
					FROM (
						SELECT @team_leader:={$LOGIN->id}, @team:=$id, @user:=$id_user, @role:=2
					)as `setup`,
					team as t, membership as m, user as leader, user as u
					LEFT JOIN(
						SELECT m.user
						FROM membership as m, team as t
						WHERE @team=m.team AND m.state IN ('accept','candidate','invite')
							AND @team=t.id AND t.mktime <= m.mktime
					) as e ON (e.user=u.id)
					WHERE @team=t.id AND @user=u.id AND e.user IS NULL # exist user?
						AND @team=m.team AND @team_leader=m.user AND m.state='accept' AND m.role=1 # have access?
					LIMIT 1;
				");		
				$item=(object)array('valid'=>(bool)mysql_affected_rows());
				$OUT->result[] =& $item;	
			}			
		break;

		// пользователь отвечает на предложение всутпить в команду
		case 'user_invite_to_team_response':
			if(!$LOGIN->valid){
				$OUT->errorcode[]=$INVALID['login_error'];
			}
			if(!isset($_REQUEST['id_member']) || !(int)$_REQUEST['id_member']){
				$OUT->errorcode[]=array_merge($INVALID['id_member'],array('параметр'=>'id_member(код участника)'));
			}else{$id_member=(int)$_REQUEST['id_member'];}
			if(!isset($_REQUEST['value']) || !in_array($_REQUEST['value'],array('accept','decline'))){
				$OUT->errorcode[]=array_merge($INVALID['response_to_invite'],array('параметр'=>'value(ответ на приглашение)'));
			}
			if(!count($OUT->errorcode)){// нет ошибок
				mysql_query_log("
					UPDATE IGNORE `membership`
					SET `state`='{$_REQUEST['value']}'
					WHERE id='$id_member' AND `user`='".$LOGIN->id."'
					LIMIT 1
				");		
				$item=(object)array('valid'=>(bool)mysql_affected_rows());
				$OUT->result[] =& $item;	
			}			
		break;
		// ответ лидера команды на заявку кандидата
		case 'team-leader_to-candidate_response':
			if(!$LOGIN->valid){
				$OUT->errorcode[]=$INVALID['login_error'];
			}
			if(!isset($_REQUEST['member']) || !(int)$_REQUEST['member']){
				$OUT->errorcode[]=array_merge($INVALID['id_member'],array('параметр'=>'member(код участника)'));
			}else{$member=(int)$_REQUEST['member'];}
			if(!isset($_REQUEST['value']) || !in_array($_REQUEST['value'],array('accept','decline'))){
				$OUT->errorcode[]=array_merge($INVALID['response_to_invite'],array('параметр'=>'value(ответ на заявку)'));
			}
			if(!count($OUT->errorcode)){// нет ошибок
				mysql_query_log("
					UPDATE IGNORE `membership` as m,(
						SELECT m.team
						FROM `membership` as m
						WHERE `user`='{$LOGIN->id}'
							AND `role`=1 AND `state`='accept'
					) as `check`
					SET m.`state`='{$_REQUEST['value']}'
					WHERE `check`.team=m.`team` AND m.id='$member'
				");		
				$item=(object)array('valid'=>(bool)mysql_affected_rows());
				$OUT->result[] =& $item;	
			}			
		break;
		case 'add_contest':
			if(!$LOGIN->valid){
				$OUT->errorcode[]=$INVALID['login_error'];
			}
			if(!isset($_REQUEST['contest_name']) || !preg_match($VALIDATOR['contest_name'],$_REQUEST['contest_name'])){
				$OUT->errorcode[]=array_merge($INVALID['contest_name'],array('параметр'=>'contest_name(название конкурса)'));
			}
			if(!isset($_REQUEST['contest_open_date']) || !preg_match($VALIDATOR['date'],$_REQUEST['contest_open_date'])){
				$OUT->errorcode[]=array_merge($INVALID['date'],array('параметр'=>'contest_open_date(дата открытия конкурса)'));
			}else{$contest_open_date=save_date($_REQUEST['contest_open_date']);}

			if(!isset($_REQUEST['contest_wait_date']) || !preg_match($VALIDATOR['date'],$_REQUEST['contest_wait_date'])){
				$OUT->errorcode[]=array_merge($INVALID['date'],array('параметр'=>'contest_wait_date(начало сдачи работ)'));
			}else{$contest_wait_date=save_date($_REQUEST['contest_wait_date']);}

			if(!isset($_REQUEST['contest_vote_date']) || !preg_match($VALIDATOR['date'],$_REQUEST['contest_vote_date'])){
				$OUT->errorcode[]=array_merge($INVALID['date'],array('параметр'=>'contest_vote_date(начало голосования)'));
			}else{$contest_vote_date=save_date($_REQUEST['contest_vote_date']);}

			if(!isset($_REQUEST['contest_close_date']) || !preg_match($VALIDATOR['date'],$_REQUEST['contest_close_date'])){
				$OUT->errorcode[]=array_merge($INVALID['date'],array('параметр'=>'contest_close_date(закрытие, подведение итогов)'));
			}else{$contest_close_date=save_date($_REQUEST['contest_close_date']);}

			if(!isset($_REQUEST['contest_auto_tick'])){
				$OUT->errorcode[]=array_merge($INVALID['bool'],array('параметр'=>'contest_auto_tick(автоматический перевод статуса конкурса)'));
			}else{$contest_auto_tick=(int)(bool)$_REQUEST['contest_auto_tick'];}

			if(!count($OUT->errorcode)){// нет ошибок
				$nt=mysql_real_escape_string(rus2lat($_REQUEST['contest_name']));
				$contest_rules=isset($_REQUEST['contest_rules'])?htmlspecialchars($_REQUEST['contest_rules']):'';
				$contest_desc=isset($_REQUEST['contest_desc'])?htmlspecialchars($_REQUEST['contest_desc']):'';
				mysql_query_log("begin");
				$item= new stdClass;

				$down_link = CONTEST_LINK."/".rus2lat($_REQUEST['contest_name']);
				$item->mkdir_contest = mkdir(SITE_ROOT.'/'.$down_link);
				$down_link.=WORK;
				$item->mkdir_work = mkdir(SITE_ROOT.'/'.$down_link);
				$item->valid_name=!mysql_query_single("
					SELECT COUNT(*) as 'c'
					FROM user as u, team as t, contest as c
					WHERE u.`name_translit`='$nt' OR t.`name_translit`='$nt' OR c.`name_translit`='$nt'
				");
				$item->valid_name && mysql_query_log("
					INSERT IGNORE INTO `contest`
					SET `name`='".mysql_real_escape_string($_REQUEST['contest_name'])."',
						`name_translit`='$nt',
						`rules`='".mysql_real_escape_string($contest_rules)."',
						`desc`='".mysql_real_escape_string($contest_desc)."',
						`content`='".mysql_real_escape_string($down_link)."',
						`open_date`='".mysql_real_escape_string($contest_open_date)."',
						`wait_date`='".mysql_real_escape_string($contest_wait_date)."',
						`vote_date`='".mysql_real_escape_string($contest_vote_date)."',
						`close_date`='".mysql_real_escape_string($contest_close_date)."',
						`auto_tick`='".$contest_auto_tick."'
				");
				unset($nt);
				$r=(bool) $id=mysql_insert_id();
				$item->id=$id;
				$item->valid=(bool)$id;
				$OUT->result[] =& $item;
				if(!$r){
					mysql_query_log("rollback");
					break;				
				}else{
					$item->valid=true;
					mysql_query_log("commit");
				}
			}
		break;
		case 'edit_contest':
			if(!$LOGIN->valid){
				$OUT->errorcode[]=$INVALID['login_error'];
			}
			if(!isset($_REQUEST['contest_name']) || !preg_match($VALIDATOR['contest_name'],$_REQUEST['contest_name'])){
				$OUT->errorcode[]=array_merge($INVALID['contest_name'],array('параметр'=>'contest_name(название конкурса)'));
			}else{$contest_name=(string)$_REQUEST['contest_name'];}
			if(!isset($_REQUEST['id']) || !(int)$_REQUEST['id']){
				$OUT->errorcode[]=array_merge($INVALID['id_contest'],array('параметр'=>'id(код конкурса)'));
			}else{$id=(int)$_REQUEST['id'];}
			if(!isset($_REQUEST['state']) || !in_array($_REQUEST['state'],array('nominate','open','wait','vote','close'))){
				$OUT->errorcode[]=array_merge($INVALID['contest_state'],array('параметр'=>'state(состояние конкурса)'));
			}
			if(!isset($_REQUEST['contest_open_date']) || !preg_match($VALIDATOR['date'],$_REQUEST['contest_open_date'])){
				$OUT->errorcode[]=array_merge($INVALID['date'],array('параметр'=>'contest_open_date(дата открытия конкурса)'));
			}else{$contest_open_date=save_date($_REQUEST['contest_open_date']);}

			if(!isset($_REQUEST['contest_wait_date']) || !preg_match($VALIDATOR['date'],$_REQUEST['contest_wait_date'])){
				$OUT->errorcode[]=array_merge($INVALID['date'],array('параметр'=>'contest_wait_date(начало сдачи работ)'));
			}else{$contest_wait_date=save_date($_REQUEST['contest_wait_date']);}

			if(!isset($_REQUEST['contest_vote_date']) || !preg_match($VALIDATOR['date'],$_REQUEST['contest_vote_date'])){
				$OUT->errorcode[]=array_merge($INVALID['date'],array('параметр'=>'contest_vote_date(начало голосования)'));
			}else{$contest_vote_date=save_date($_REQUEST['contest_vote_date']);}

			if(!isset($_REQUEST['contest_close_date']) || !preg_match($VALIDATOR['date'],$_REQUEST['contest_close_date'])){
				$OUT->errorcode[]=array_merge($INVALID['date'],array('параметр'=>'contest_close_date(закрытие, подведение итогов)'));
			}else{$contest_close_date=save_date($_REQUEST['contest_close_date']);}

			if(!isset($_REQUEST['contest_auto_tick'])){
				$OUT->errorcode[]=array_merge($INVALID['bool'],array('параметр'=>'contest_auto_tick(автоматический перевод статуса конкурса)'));
			}else{$contest_auto_tick=(int)(bool)$_REQUEST['contest_auto_tick'];}
			if(!count($OUT->errorcode)){// нет ошибок
				$contest_rules=isset($_REQUEST['contest_rules'])?htmlspecialchars($_REQUEST['contest_rules']):'';
				$contest_desc=isset($_REQUEST['contest_desc'])?htmlspecialchars($_REQUEST['contest_desc']):'';
				$contest_content=isset($_REQUEST['contest_content'])?htmlspecialchars($_REQUEST['contest_content']):'';
				$nt=mysql_real_escape_string(rus2lat($_REQUEST['contest_name']));
				$r=mysql_query_log("
					UPDATE IGNORE `contest` as c, (
						SELECT COUNT(*) as 'c'
						FROM user as u, team as t, contest as c
						WHERE u.`name_translit`='$nt' OR t.`name_translit`='$nt' OR (c.`name_translit`='$nt' AND c.id != $id)
					) as ex
					SET
						c.`name`= '".mysql_real_escape_string($contest_name)."',
						c.`name_translit`='$nt',
						c.`rules`= '".mysql_real_escape_string($contest_rules)."',
						c.`desc`='".mysql_real_escape_string($contest_desc)."',
						c.`content`='".mysql_real_escape_string($contest_content)."',
						c.`state`='".mysql_real_escape_string($_REQUEST['state'])."',
						c.`open_date`='".mysql_real_escape_string($contest_open_date)."',
						c.`wait_date`='".mysql_real_escape_string($contest_wait_date)."',
						c.`vote_date`='".mysql_real_escape_string($contest_vote_date)."',
						c.`close_date`='".mysql_real_escape_string($contest_close_date)."',
						c.`auto_tick`='".$contest_auto_tick."'
					WHERE c.`id`=$id AND ex.c=0
				");
				unset($nt);
				$item=(object)array('valid'=>(bool)mysql_affected_rows());
				$OUT->result[] =& $item;
			}
		break;
		case 'user_send_contest_offer': 
			if(!$LOGIN->valid){
				$OUT->errorcode[]=$INVALID['login_error'];	
			}
			if(!isset($_REQUEST['id']) || !(int)$_REQUEST['id']){
				$OUT->errorcode[]=array_merge($INVALID['id_contest'],array('параметр'=>'id(код конкурса)'));
			}else{$id=(int)$_REQUEST['id'];}
			if(!count($OUT->errorcode)){// нет ошибок
				mysql_query_log("
					# contest += user
					INSERT IGNORE INTO participation (`contest`,`contest_name`,`user`,`user_name`)
					SELECT @contest as 'contest', c.name as 'contest_name', @user as 'user', u.name as 'user_name'
					FROM (
						SELECT @contest:=$id, @user:={$LOGIN->id}
					) as `setup`,
					`contest` as c, user as u
					LEFT JOIN (
						SELECT p.user
						FROM participation as p
						WHERE @contest=p.contest
					) as `exist` ON `exist`.user=u.id
					WHERE @contest=c.id AND c.state='open' AND (`exist`.user IS NULL ) AND @user=u.id;
				");		
				$id_p=mysql_insert_id();
				$item=(object)array('id'=>$id_p,'valid'=>(bool)$id_p);
				$OUT->result[] =& $item;	
			}			
		break;
		case 'team_send_contest_offer': 
			if(!$LOGIN->valid){
				$OUT->errorcode[]=$INVALID['login_error'];	
			}
			if(!isset($_REQUEST['id_team']) || !(int)$_REQUEST['id_team']){
				$OUT->errorcode[]=array_merge($INVALID['id_team'],array('параметр'=>'id_team(код команды)'));
			}else{$id_team=(int)$_REQUEST['id_team'];}
			if(!isset($_REQUEST['id_contest']) || !(int)$_REQUEST['id_contest']){
				$OUT->errorcode[]=array_merge($INVALID['id_contest'],array('параметр'=>'id_contest(код конкурса)'));
			}else{$id_contest=(int)$_REQUEST['id_contest'];}
			if(!count($OUT->errorcode)){// нет ошибок
				$item = new stdClass;
				$r = mysql_query_log("begin");
				$r = $r && $res=mysql_query_log("
					# contest += team
					SELECT (leader.team IS NOT NULL) as 'access'
					FROM (
						SELECT @contest:=$id_contest, @team:=$id_team, @team_leader:={$LOGIN->id}
					) as `setup`, team as t
					LEFT JOIN ( # список команд для @team_leader
						SELECT m.team
						FROM membership as m
						WHERE @team_leader=m.user AND m.role=1 AND m.state='accept'
					) as `leader` ON ( leader.team = t.id)
					WHERE @team=t.id
				");
				list($item->access)=mysql_fetch_row($res);
				if(!mysql_affected_rows() || !$item->access){
					$OUT->errorcode[]=array('type'=>'Нет доступа','лидер команды'=>'недостаточно прав');						
					$r = false;
				}
				$r = $r && $res=mysql_query_log("
					# список тех кто повторно пытается поучаствовать
					SELECT m.user, u.name as 'user_name', @fail:=1
					FROM user as u, membership as m
					LEFT JOIN (
						SELECT p.user
						FROM participation as p
						WHERE @contest=p.contest
						ORDER BY p.user_name
					) as `exist` ON `exist`.user=m.user
					WHERE @team=m.team AND m.state='accept' AND ( `exist`.user IS NOT NULL ) AND m.user=u.id
				");
				while(false !== $usr=mysql_fetch_object($res)){
					$OUT->errorcode[]=array('type'=>'Ошибка ввода','Повторное участие'=>"{$usr->user}:{$usr->user_name}");	
					$r = false;
				}
				$r = $r && mysql_query_log("
					#
					INSERT IGNORE INTO participation (`contest`,`contest_name`,`user`,`user_name`,`team`,`team_name`, `role`, `role_name`)
					SELECT @contest as 'contest', c.name as 'contest_name', u.id as 'user', u.name as 'user_name', @team as 'team', t.name as 'team_name', r.id as 'role', r.name as 'role_name'
					FROM contest as c, membership as m, user as u, team as t, `role` as r
					WHERE @fail IS NULL AND @contest=c.id AND c.state='open' AND @team=t.id AND @team=m.team AND m.user=u.id AND m.role=r.id AND m.state='accept'
				");		
				if($r){
					$item->id=mysql_insert_id();
					$item->valid=(bool)$item->id;
					mysql_query_log("commit");
				}
				else
					mysql_query_log("rollback");
				$OUT->result[] =& $item;	
			}			
		break;
		case 'user_vote':
			if(!$LOGIN->valid){
				$OUT->errorcode[]=$INVALID['login_error'];	
			}
			if(!isset($_REQUEST['id_contest']) || !(int)$_REQUEST['id_contest']){
				$OUT->errorcode[]=array_merge($INVALID['id_contest'],array('параметр'=>'id_contest(код конкурса)'));
			}else{$id_contest=(int)$_REQUEST['id_contest'];}
			if(!isset($_REQUEST['to']) || !is_array($_REQUEST['to'])){
				$OUT->errorcode[]=array_merge($INVALID['array_participation'],array('параметр'=>'to(массив конкурсантов)'));
			}else{$to=$_REQUEST['to'];}	
			if(!isset($_REQUEST['score']) || !is_array($_REQUEST['score'])){
				$OUT->errorcode[]=array_merge($INVALID['array_score'],array('параметр'=>'score(массив баллов)'));
			}else{$score=$_REQUEST['score'];}
			if(!isset($_REQUEST['comments']) || !is_array($_REQUEST['comments'])){
				$OUT->errorcode[]=array_merge($INVALID['array_comments'],array('параметр'=>'comments(массив комментариев)'));
			}else{$comments=$_REQUEST['comments'];}
			if(!count($OUT->errorcode)){// нет ошибок
				$item = new stdClass;		
				$r = mysql_query_log("begin");
				$r = $r && $res=mysql_query_log("
					# user -> contest_vote()
					SET @contest:=$id_contest,
						@user:={$LOGIN->id}
				");
				$r = $r && $res=mysql_query_log("
					SELECT p.id, IF(p.role IS NULL, p.user_name, p.team_name) as 'name'
					FROM participation as p
						LEFT JOIN participation as p1 ON p1.contest=@contest AND p1.team=p.team AND p1.user=@user
					WHERE @contest=p.contest AND IF(p.role IS NULL, p.user!=@user, p.role=1) AND p1.id IS NULL
				");
				$pids=array();
				$pids_attr=array();
				while(false !== $part=mysql_fetch_object($res)){
					$pids[]=$part->id;
					$pids_attr[$part->id]=$part->name;
				}			
				// дополнительная проверка ввода
				foreach($to as $k=>$v){
					if(!in_array($v,$pids))
						$OUT->errorcode[]=array_merge($INVALID['bad_vote'],array("to[$k]"=>"$v"));
				}
				$to_cv=array_count_values($to);
				$score_cv=array_count_values($score);
				$td_to=array(); // true data 'to'
				$td_comments=array(); // true data 'comments'
				foreach($pids as $pid){
					$inf=array();
					if(!isset($to_cv[$pid])){
						$OUT->errorcode[]=array_merge($INVALID['need_vote'		],array("конкурсант `".$pids_attr[$pid]."`"=>"$pid"));
						continue;
					}else if($to_cv[$pid]!=1){
						$OUT->errorcode[]=array_merge($INVALID['duplicate_vote'	],array("конкурсант `".$pids_attr[$pid]."`"=>"$pid"));
						continue;
					}
					$inf['to']=$pid;
					$key=array_search($pid,$to);
					if(!isset($score[$key]) || $score[$key]===''){
						$OUT->errorcode[]=array_merge($INVALID['need_score'		],array("конкурсант `".$pids_attr[$pid]."`"=>"$pid"));
					}else if($score_cv[$score[$key]]!=1){
						$OUT->errorcode[]=array_merge($INVALID['duplicate_score'],array("конкурсант `".$pids_attr[$pid]."`"=>"$pid"));											
					}else if( 1<=$score[$key] && $score[$key]<=count($pids)){
						$inf['score']=$score[$key];
					}else{
						$OUT->errorcode[]=array_merge($INVALID['bad_score'		],array("конкурсант `".$pids_attr[$pid]."`"=>"$pid",
						"балл `{$score[$key]}`"=>"должен быть в интервале [1,".count($pids)."]"));
					}
					if(!isset($comments[$key])){
						$OUT->errorcode[]=array_merge($INVALID['need_comment'	],array("конкурсант `".$pids_attr[$pid]."`"=>"$pid"));	
					}else{
						$inf['comment']=$comments[$key];
					}
					if(!count($OUT->errorcode)){
						$td_to[$inf['score']]=$pid;
						$td_comments[$inf['score']]=$inf['comment'];
					}
				}
				$r = $r && !count($OUT->errorcode);
				$to_str='';
				$comments_str='';
				if($r){
					krsort($td_to);
					$to_str=mysql_real_escape_string(implode(',',$td_to));
					krsort($td_comments);
					foreach($td_comments as &$v){
						$v=str_replace(',',mysql_real_escape_string('&#8218;'),htmlspecialchars($v));
					}
					$comments_str=mysql_real_escape_string( implode(',',$td_comments) );
				}
				$r = $r && $res=mysql_query_log("
					SET 
						@to:='$to_str',
						@comments:='$comments_str'
				");
				$r = $r && $res=mysql_query_log("
					# проверка на повторное голосование
					SELECT
						@valid:=(COUNT(v.from)=0)
					FROM
						user as u
						LEFT JOIN vote as v ON (v.from=u.id AND @contest=v.contest)
					WHERE @user=u.id
					GROUP BY v.from
				");
				$r = $r && $res=mysql_query_log("
					# удаляю старые голоса
					DELETE
					FROM vote as v
					WHERE @valid=0 AND @bad IS NULL AND @contest=v.contest AND @user=v.from
				");
				$r = $r && $res=mysql_query_log("
					# голосование
						INSERT INTO vote (`contest`,`from`,`to`,`value`,`desc`)
						SELECT @contest as 'contest', @user as 'from', p.id as 'to',
							@count-(@pos:=FIND_IN_SET(p.id,@to))+1 as 'value',
							SUBSTRING_INDEX(SUBSTRING_INDEX(@comments,',',@pos),',',-1) as 'desc'
						FROM
						(
							SELECT @count:=COUNT(p.id)
							FROM participation as p
								LEFT JOIN participation as p1 ON p1.contest=@contest AND p1.team=p.team AND p1.user=@user
							WHERE @contest=p.contest AND IF(p.role IS NULL, p.user!=@user, p.role=1) AND p1.id IS NULL
						) as `cnt`,
						(
							SELECT p.*
							FROM participation as p
								LEFT JOIN participation as p1 ON p1.contest=@contest AND p1.team=p.team AND p1.user=@user
							WHERE @contest=p.contest AND IF(p.role IS NULL, p.user!=@user, p.role=1) AND p1.id IS NULL
						) as p
						WHERE @bad IS NULL
						ORDER BY `value` DESC
				");
				if($r){
					$item->id=mysql_insert_id();
					$item->valid=(bool)$item->id;
					mysql_query_log("commit");
				}
				else
					mysql_query_log("rollback");
				$OUT->result[] =& $item;	
			}
		break;
		case 'user_send_work':
			if(!$LOGIN->valid){
				$OUT->errorcode[]=$INVALID['login_error'];	
			}
			if(!isset($_REQUEST['pid']) || !(int)$_REQUEST['pid']){
				$OUT->errorcode[]=array_merge($INVALID['id_participation'],array("параметр"=>"pid(код участника)"));
			}else{$pid=(int)$_REQUEST['pid'];}
			if(!isset($_REQUEST['work']) || !(string)$_REQUEST['work']){
				$OUT->errorcode[]=array_merge($INVALID['work'],array("параметр"=>"work(работа участника)"));
			}else{$work=(string)$_REQUEST['work'];}
			$r = isset($_FILES[$work]) && $_FILES[$work]['name'] && !$_FILES[$work]['error'];
			if(!$r){
				$OUT->errorcode[]=$INVALID['work_error'];
			}
			if(!count($OUT->errorcode)){// нет ошибок
				$r=(bool)$data=mysql_query_assoc("
					# pid->contest_data
					SELECT c.name as 'contest_name', IF( p.team IS NULL, p.user_name, p.team_name) as 'name'
					FROM
						(
							SELECT @pid:=$pid, @user:={$LOGIN->id}
						) as `setup`,
						(
							SELECT p.id, p.team, p.user_name, p.team_name, @contest:=p.contest as 'contest'
							FROM participation as p
							WHERE @pid=p.id AND @user=p.user AND IF(p.role IS NOT NULL, p.role=1, 1)
						) as p
						LEFT JOIN work as w ON w.pid=p.id,
						contest as c
					WHERE @contest=c.id AND w.pid IS NULL;
				");
				if(!$r){
					$OUT->errorcode[]=$INVALID['work_acces_forbidden'];
				}else{
					$work_name = rus2lat($data['name'].substr($_FILES[$work]['name'],strrpos($_FILES[$work]['name'],'.')));
					$work_filename=CONTEST_ROOT."/";
					$work_filename.=rus2lat($data['contest_name']);
					if(!file_exists($work_filename))
						mkdir($work_filename);
					$work_filename.=WORK;
					if(!file_exists($work_filename))
						mkdir($work_filename);				
					$work_filename.="/".$work_name;	
				}
				$r=$r && move_uploaded_file($_FILES[$work]['tmp_name'],$work_filename);
				$r=$r && mysql_query_log("
					# pid->send_work
					INSERT IGNORE INTO `work` (`name`,`filename`,`pid`)
					SELECT @name as 'name', @filename as 'filename', @pid as 'pid'
					FROM 
						(
							SELECT @pid:=$pid, @user:={$LOGIN->id}, @name:='".mysql_real_escape_string($work_name)."', @filename:='".mysql_real_escape_string($work_filename)."'
						) as `setup`,
						participation as p
						LEFT JOIN work as w ON w.pid=p.id
					WHERE @pid=p.id AND @user=p.user AND IF(p.role IS NOT NULL, p.role=1, 1) AND w.pid IS NULL
				");
				$item = new stdClass;
				$item->id=mysql_insert_id();
				$item->valid = $r && (bool)$item->id;
				$OUT->result[] = $item;	
			}
		break;
		case 'contest_download_work':
			if(!isset($_REQUEST['id_contest']) || !(int)$_REQUEST['id_contest']){
				$OUT->errorcode[]=array_merge($INVALID['id_contest'],array('параметр'=>'id_contest(код конкурса)'));
			}else{$id_contest=(int)$_REQUEST['id_contest'];}
			if(!count($OUT->errorcode)){// нет ошибок
				mysql_query_log("
					UPDATE IGNORE `contest`
					SET `down_count`=`down_count`+1
					WHERE `id`=$id_contest
					LIMIT 1
				");
				$item = new stdClass;
				$item->valid = (bool)mysql_affected_rows();
				$OUT->result[] = $item;	
			}
		break;
		default:
			$OUT->errorcode[]=array_merge($INVALID['unknown_action'],array('действие'=>"`$act` -  неизвестно",'file:'=>'core/submit.php'));
	}
	unset($act);
	response();
}catch (Exception $e) {
	exception_handler($e);
}
?>
