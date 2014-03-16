<?php
include dirname(__FILE__)."/init.php";
try{
	switch($of=$_REQUEST['of']){
		case 'user':
			if(false !== $ret=mysql_query_array("
				SELECT *
				FROM `user`
				ORDER BY `name`
			")){
				$OUT->result=$ret;
			};
		break;
		case 'team':
			if(false !== $ret=mysql_query_array("
				SELECT *
				FROM `team`
				ORDER BY `name`
			")){
				$OUT->result=$ret;
			};
		break;		
		case 'contest':
			if(false !== $ret=mysql_query_array("
				SELECT *
				FROM `contest`
				ORDER BY `mktime`
			")){
				$OUT->result=$ret;
			};
		break;
		case 'contest_asc':
			if(false !== $ret=mysql_query_array("
				SELECT *
				FROM `contest`
				ORDER BY `name`
			")){
				$OUT->result=$ret;
			};
		break;
		case 'teams_for_user': // спиcок команд в которых участвует пользователь
			if(!isset($_REQUEST['id']) || !(int)$_REQUEST['id']){
				$OUT->errorcode[]=array_merge($INVALID['id_user'],array('параметр'=>'id(код пользователя)'));
			}else{$id=(int)$_REQUEST['id'];}
			if(!count($OUT->errorcode)){ // нет ошибок
				$OUT->result=mysql_query_array("
					# user -> teams()
					SELECT m.id, m.team as 'team', t.name as 'team_name',
						m.role, r.name as 'role_name', r.desc as 'role_desc'
					FROM (
						SELECT @user:=$id, @state:='accept'
					) as `setup`, user as u, membership as m, team as t, role as r
					WHERE @user=m.user AND @user=u.id AND u.mktime<=m.mktime AND @state=m.state
						AND m.team=t.id AND t.mktime<=m.mktime AND m.role=r.id
					ORDER BY m.role, `team_name`;
				");
			}
		break;		
		case 'teams_for_leader': // спиcок команд в которых лидер
			if(!isset($_REQUEST['id']) || !(int)$_REQUEST['id']){
				$OUT->errorcode[]=array_merge($INVALID['id_user'],array('параметр'=>'id(код пользователя)'));
			}else{$id=(int)$_REQUEST['id'];}
			if(!count($OUT->errorcode)){ // нет ошибок
				$OUT->result=mysql_query_array("
					# user -> teams()
					SELECT m.id, m.team as 'team', t.name as 'team_name',
						m.role, r.name as 'role_name', r.desc as 'role_desc'
					FROM (
						SELECT @user:=$id, @state:='accept'
					) as `setup`, user as u, membership as m, team as t, role as r
					WHERE @user=m.user AND 1=m.role AND @user=u.id AND u.mktime<=m.mktime AND @state=m.state
						AND m.team=t.id AND t.mktime<=m.mktime AND m.role=r.id
					ORDER BY `team_name`;
				");
			}
		break;
		case 'users_in_team': // спиок участников команды
			if(!isset($_REQUEST['id']) || !(int)$_REQUEST['id']){
				$OUT->errorcode[]=array_merge($INVALID['id_team'],array('параметр'=>'id(код команды)'));
			}else{$id=(int)$_REQUEST['id'];}
			if(!count($OUT->errorcode)){// нет ошибок
				if(false !== $ret=mysql_query_array("
					# team -> users()
					SELECT m.id, m.user as 'user', u.name as 'user_name', m.role,
						r.name as 'role_name', r.desc as 'role_desc'
					FROM (
						SELECT @team:=$id, @state:='accept'
					) as `setup`,membership as m
					LEFT JOIN user as u ON ( m.user=u.id ),
					team as t, role as r
					WHERE @team=m.team AND @team=t.id AND m.role=r.id
						AND t.mktime<=m.mktime AND u.mktime<=m.mktime # history valid check
						AND @state=m.state
					ORDER BY m.role, `user_name`
				")){
					$OUT->result=$ret;
				}
			}
		break;
		case 'candidate_in_team': // спиок кандидатов в участники команды
			if(!isset($_REQUEST['id']) || !(int)$_REQUEST['id']){
				$OUT->errorcode[]=array_merge($INVALID['id_team'],array('параметр'=>'id(код команды)'));
			}else{$id=(int)$_REQUEST['id'];}
			if(!count($OUT->errorcode)){// нет ошибок
				$OUT->result=mysql_query_array("
					# team -> users()
					SELECT m.id, m.user as 'user', u.name as 'user_name', m.role,
						r.name as 'role_name', r.desc as 'role_desc'
					FROM (
						SELECT @team:=$id, @state:='candidate'
					) as `setup`,membership as m
					LEFT JOIN user as u ON ( m.user=u.id ),
					team as t, role as r
					WHERE @team=m.team AND @team=t.id AND m.role=r.id
						AND t.mktime<=m.mktime AND u.mktime<=m.mktime # history valid check
						AND @state=m.state
					ORDER BY `user_name`
				");
			};
		break;
		case 'user_invite_to_team': // спиок приглашений в команды
			if(!isset($_REQUEST['id']) || !(int)$_REQUEST['id']){
				$OUT->errorcode[]=array_merge($INVALID['id_user'],array('параметр'=>'id(код пользователя)'));
			}else{$id=(int)$_REQUEST['id'];}
			if(!count($OUT->errorcode)){// нет ошибок
				$OUT->result=mysql_query_array("
					# user -> teams()
					SELECT m.id, m.team as 'team', t.name as 'team_name',
						m.role, r.name as 'role_name', r.desc as 'role_desc'
					FROM (
						SELECT @user:=$id, @state:='invite'
					) as `setup`, user as u, membership as m, team as t, role as r
					WHERE @user=m.user AND @user=u.id AND u.mktime<=m.mktime AND @state=m.state
						AND m.team=t.id AND t.mktime<=m.mktime AND m.role=r.id
					ORDER BY m.role, `team_name`
				");
			};
		break;
		case 'contest_team_members':
			if(!isset($_REQUEST['id']) || !(int)$_REQUEST['id']){
				$OUT->errorcode[]=array_merge($INVALID['id_contest'],array('параметр'=>'id(код конкурса)'));
			}else{$id=(int)$_REQUEST['id'];}
			if(!isset($_REQUEST['id_team']) || !(int)$_REQUEST['id_team']){
				$OUT->errorcode[]=array_merge($INVALID['id_team'],array('параметр'=>'id_team(код команды)'));
			}else{$id_team=(int)$_REQUEST['id_team'];}
			if(!count($OUT->errorcode)){// нет ошибок
				$OUT->result=mysql_query_array("
					# contest -> team_members()
					SELECT p.id, p.user, p.user_name, user.id as 'user_live', p.role, p.role_name
					FROM (
						SELECT @contest:=$id, @team:=$id_team
					) as `setup`,participation as p
					LEFT JOIN `user` ON( user.id=p.user AND user.mktime<p.mktime )
					WHERE @contest=p.contest AND p.team=@team
					ORDER BY `role`, `user_name`;
				");
			};
		break;
		case 'activ_participations_in_contest':
			if(!isset($_REQUEST['id']) || !(int)$_REQUEST['id']){
				$OUT->errorcode[]=array_merge($INVALID['id_contest'],array('параметр'=>'id(код конкурса)'));
			}else{$id=(int)$_REQUEST['id'];}
			if(!count($OUT->errorcode)){// нет ошибок
				$OUT->result=mysql_query_array("
					# contest -> participations()
					SELECT p.id, p.user, p.user_name, user.id as 'user_live', p.team, p.team_name, team.id as 'team_live', p.role, p.role_name, p.num_members
					FROM 
					(
							SELECT @contest:=$id
					) as `setup`,
					(
						(
							SELECT p.id, p.user, p.user_name, p.team, p.team_name, p.role, p.role_name, p.mktime,
							NULL as 'num_members'
							FROM participation as p
							WHERE @contest=p.contest AND p.team IS NULL
						)UNION(
							SELECT p.id, p.user, p.user_name, p.team, p.team_name, p.role, p.role_name, p.mktime,
							COUNT(p.team) as 'num_members'
							FROM participation as p
							WHERE @contest=p.contest AND p.team IS NOT NULL
							GROUP BY p.team
						)
					) as p
					LEFT JOIN `user` ON( user.id=p.user AND user.mktime<p.mktime )
					LEFT JOIN `team` ON( team.id=p.team AND team.mktime<p.mktime )
					ORDER BY p.id
				");
			};
		break;
		case 'contest_for_user': // спиcок конкурсов в которых участвует пользователь
			if(!isset($_REQUEST['id']) || !(int)$_REQUEST['id']){
				$OUT->errorcode[]=array_merge($INVALID['id_user'],array('параметр'=>'id(код пользователя)'));
			}else{$id=(int)$_REQUEST['id'];}
			if(!count($OUT->errorcode)){// нет ошибок
				$OUT->result=mysql_query_array("
					SELECT p.id, p.contest, p.contest_name, p.team, p.team_name, team.id as 'team_live', p.role, p.role_name
					FROM (
						SELECT @user:=$id
					) as `setup`, user, participation as p
					LEFT JOIN `team` ON( team.id=p.team AND team.mktime<p.mktime )
					WHERE @user=p.user AND @user=user.id AND user.mktime<p.mktime
					ORDER BY p.id;
				");
			}
		break;
		case 'contest_for_team': // спиcок конкурсов в которых участвует пользователь
			if(!isset($_REQUEST['id']) || !(int)$_REQUEST['id']){
				$OUT->errorcode[]=array_merge($INVALID['id_team'],array('параметр'=>'id(код команды)'));
			}else{$id=(int)$_REQUEST['id'];}
			if(!count($OUT->errorcode)){// нет ошибок
				$OUT->result=mysql_query_array("
					# team -> contests()
					SELECT p.contest, p.contest_name, p.team, p.team_name, COUNT(p.contest) as 'num_members'
					FROM (
						SELECT @team:=$id
					) as `setup`, team, participation as p
					WHERE @team=p.team AND @team=team.id AND team.mktime<p.mktime
					GROUP BY p.contest
					ORDER BY p.contest_name;
				");
			}
		break;
		case 'vote_participation': // спиcок участников конкурса для голосования
			if(!isset($_REQUEST['id_user']) || !(int)$_REQUEST['id_user']){
				$OUT->errorcode[]=array_merge($INVALID['id_user'],array('параметр'=>'id_user(код пользователя)'));
			}else{$id_user=(int)$_REQUEST['id_user'];}
			if(!isset($_REQUEST['id_contest']) || !(int)$_REQUEST['id_contest']){
				$OUT->errorcode[]=array_merge($INVALID['id_contest'],array('параметр'=>'id_contest(код конкурса)'));
			}else{$id_contest=(int)$_REQUEST['id_contest'];}
			if(!count($OUT->errorcode)){// нет ошибок
				$OUT->result=mysql_query_array("
					# проверка введёных участников по списку требуемых id
					SELECT id, IF(p.role IS NULL, p.user_name, p.team_name) as 'name'
					FROM 
						(
							SELECT @contest:=$id_contest
						) as `setup`, participation as p
					WHERE @contest=p.contest AND IF(p.role IS NULL, 1, p.role=1) AND p.user!=@user
					ORDER BY p.id
				");
			}
		break;
		case 'contest_participation': // спиcок участников конкурса
			if(!isset($_REQUEST['id']) || !(int)$_REQUEST['id']){
				$OUT->errorcode[]=array_merge($INVALID['id_contest'],array('параметр'=>'id(код конкурса)'));
			}else{$id=(int)$_REQUEST['id'];}
			if(!count($OUT->errorcode)){// нет ошибок
				$OUT->result=mysql_query_array("
					# contest -> participations()
					SELECT p2.id as 'id', CAST( IF( p2.team_name IS NULL,
							p2.user_name,
							CONCAT_WS(' ',p2.team_name,'[',p2.num_members,']','{',p2.user_name,'...','}')
						) AS CHAR ) as 'name'
					FROM 
						(
							SELECT @contest:=$id
						) as `setup`,
						(
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
						) as p2
					ORDER BY p2.mktime;
				");
			}
		break;
		case 'contest_vote_table': // таблица результатов голосования конкурса
			if(!isset($_REQUEST['id_contest']) || !(int)$_REQUEST['id_contest']){
				$OUT->errorcode[]=array_merge($INVALID['id_contest'],array('параметр'=>'id_contest(код конкурса)'));
			}else{$id_contest=(int)$_REQUEST['id_contest'];}
			if(!count($OUT->errorcode)){// нет ошибок
				$OUT->result=mysql_query_array("
					# contest -> vote_table() with users
					# таблица голосования для конкурса с голосами зрителей
					SELECT
						IF(p_to.role IS NULL, p_to.user_name, p_to.team_name) as 'to',
						IF(my.role IS NULL, my.user_name,
							IF(my.role=1,my.team_name,CONCAT(my.team_name,'::',my.user_name))
						) as 'from',
						v.value, v.desc
					FROM
						(
							SELECT @contest:=$id_contest
						) as `setup`,
						( # я
							( # участники и члены команд
								SELECT p.id, p.user, p.user_name, p.team_name, p.role
								FROM participation as p
								WHERE @contest=p.contest #AND IF(p.role IS NULL, 1, p.role=1)
							)
							UNION 
							( # голосующие зрители
								SELECT
									NULL as 'id',
									u.id as 'user',
									u.name as 'user_name',
									'' as 'team_name',
									3 as 'role'
								FROM user as u
									LEFT JOIN participation as p 
									ON (u.id=p.user AND @contest=p.contest )
									LEFT JOIN vote as v 
									ON ( u.id=v.from AND @contest=v.contest )
								WHERE p.id IS NULL AND v.from IS NOT NULL
								GROUP BY v.from
							)
						) as my,
						vote as v,
						participation as p_to
					WHERE @contest=v.contest AND v.from=my.user AND v.to=p_to.id
					ORDER BY p_to.user, my.role, 'from'
				");
			}
		break;
		case 'user_vote_in_contest': // таблица голосования от пользователя
			if(!isset($_REQUEST['id_user']) || !(int)$_REQUEST['id_user']){
				$OUT->errorcode[]=array_merge($INVALID['id_user'],array('параметр'=>'id_user(код пользователя)'));
			}else{$id_user=(int)$_REQUEST['id_user'];}
			if(!isset($_REQUEST['id_contest']) || !(int)$_REQUEST['id_contest']){
				$OUT->errorcode[]=array_merge($INVALID['id_contest'],array('параметр'=>'id_contest(код конкурса)'));
			}else{$id_contest=(int)$_REQUEST['id_contest'];}
			if(!count($OUT->errorcode)){// нет ошибок
				$OUT->result=mysql_query_array("
					# user -> my_vote
					# таблица голосования от пользователя
					SELECT
						p_to.id as 'to',
						IF(p_to.role IS NULL, p_to.user_name, p_to.team_name) as 'to_name',
						IF(my.role IS NULL, my.user_name,
							IF(my.role=1,my.team_name,CONCAT(my.team_name,'::',my.user_name))
						) as 'from',
						v.value, v.desc
					FROM
						(
							SELECT @contest:=$id_contest, @user:=$id_user
						) as `setup`,
						( # я
							( # участники и члены команд
								SELECT p.id, p.user, p.user_name, p.team_name, p.role
								FROM participation as p
								WHERE @contest=p.contest #AND IF(p.role IS NULL, 1, p.role=1)
							)
							UNION 
							( # голосующие зрители
								SELECT
									NULL as 'id',
									u.id as 'user',
									u.name as 'user_name',
									'' as 'team_name',
									3 as 'role'
								FROM user as u
									LEFT JOIN participation as p 
									ON (u.id=p.user AND @contest=p.contest )
									LEFT JOIN vote as v 
									ON ( u.id=v.from AND @contest=v.contest )
								WHERE p.id IS NULL AND v.from IS NOT NULL
								GROUP BY v.from
							)
						) as my,
						vote as v,
						participation as p_to
					WHERE @contest=v.contest AND @user=my.user AND v.from=my.user AND v.to=p_to.id
					ORDER BY p_to.id
				");
			}
		break;
		case 'contest_vote_table_list':
			if(false !== $ret=mysql_query_array("
				# contest -> vote_tables_list()
				# список конкурсов с таблицами голосов
				SELECT c.id, c.name
				FROM contest as c
				WHERE c.state='close'
			")){
				$OUT->result=$ret;
			};
		break;
		case 'contest_vote_statistic': // статистика голосования в конкурсе с учётом зрителей
			if(!isset($_REQUEST['id_contest']) || !(int)$_REQUEST['id_contest']){
				$OUT->errorcode[]=array_merge($INVALID['id_contest'],array('параметр'=>'id_contest(код конкурса)'));
			}else{$id_contest=(int)$_REQUEST['id_contest'];}
			if(!count($OUT->errorcode)){// нет ошибок
				mysql_query_log('begin');
				mysql_query_log("SET @contest:=$id_contest");				
				$OUT->result=mysql_query_array("
					# contest -> vote_statistic with users
					# статистика голосования в конкурсе с учётом зрителей
					SELECT
						IF(my.role IS NULL, my.user_name,
							IF(my.role=1,my.team_name,CONCAT(my.team_name,'::',my.user_name))
						) as 'parti', myv.vc as 'parti_vote_count', v4my.vc as 'vote_count',
						v4my.sum as 'score',vu4my.vc as 'vote_viewer_count',
						vu4my.sum as 'viewer_score'
					FROM
						( # я
							( # участники и члены команд
								SELECT p.id, p.user, p.user_name, p.team_name, p.role
								FROM participation as p
								WHERE @contest=p.contest #AND IF(p.role IS NULL, 1, p.role=1)
							)
							UNION 
							( # голосующие зрители
								SELECT
									NULL as 'id',
									u.id as 'user',
									u.name as 'user_name',
									'' as 'team_name',
									3 as 'role'
								FROM user as u
									LEFT JOIN participation as p 
									ON (u.id=p.user AND @contest=p.contest )
									LEFT JOIN vote as v 
									ON ( u.id=v.from AND @contest=v.contest )
								WHERE p.id IS NULL AND v.from IS NOT NULL
								GROUP BY v.from
							)
						) as my
						LEFT JOIN ( # голоса от меня
							SELECT u.id, COUNT(v.to) as 'vc'
							FROM user as u, vote as v
							WHERE @contest=v.contest AND v.from=u.id
							GROUP BY v.from
						) as `myv` ON ( my.user=myv.id )
						LEFT JOIN ( # голоса участников для меня
							SELECT p.id, COUNT(v.to) as 'vc', SUM(v.value) as 'sum'
							FROM participation as part, # некоторый участник
								participation as p, # я 
								vote as v
							WHERE p.contest=@contest AND part.contest=@contest AND v.contest=@contest
								AND p.id=v.to AND part.user = v.from
								AND IF( part.role IS NULL, 1, part.role=1 )
							GROUP BY v.to
						) as `v4my` ON ( my.id=v4my.id )
						LEFT JOIN ( # голоса зрителей для меня
							SELECT p.id, COUNT(v.to) as 'vc', SUM(v.value) as 'sum'
							FROM (
									SELECT u.id
									FROM user as u
										LEFT JOIN participation as p 
										ON ( u.id=p.user AND @contest=p.contest )
										LEFT JOIN vote as v 
										ON ( u.id=v.from AND @contest=v.contest )
									WHERE (p.id IS NULL OR p.role=2) AND v.from IS NOT NULL
									GROUP BY v.from
								) as u, # некоторый зритель
								participation as p, # я 
								vote as v
							WHERE p.contest=@contest AND v.contest=@contest
								AND p.id=v.to AND u.id = v.from
							GROUP BY v.to
						) as `vu4my` ON ( my.id=vu4my.id )
					WHERE IF(my.role IS NULL,1,IF(my.role=2,myv.id IS NOT NULL,1))
					ORDER BY `score` DESC, `parti`
				");
				mysql_query_log('commit');
			}
		break;
		case 'my_vote_contest_list':
			if(!isset($_REQUEST['id_user']) || !(int)$_REQUEST['id_user']){
				$OUT->errorcode[]=array_merge($INVALID['id_user'],array('параметр'=>'id_user(код пользователя)'));
			}else{$id_user=(int)$_REQUEST['id_user'];}
			if(!count($OUT->errorcode) && false !== $ret=mysql_query_array("
				# user -> my_vote_contest_list
				SELECT c.id, c.name
				FROM 
					(
						SELECT @user:=$id_user
					) as `setup`,
					contest as c,
					vote as v
				WHERE c.id=v.contest AND @user=v.from
				GROUP BY v.contest
			")){
				$OUT->result=$ret;
			};
		break;
		case 'my_vote_list':
			if(!isset($_REQUEST['id_user']) || !(int)$_REQUEST['id_user']){
				$OUT->errorcode[]=array_merge($INVALID['id_user'],array('параметр'=>'id_user(код пользователя)'));
			}else{$id_user=(int)$_REQUEST['id_user'];}
			if(!isset($_REQUEST['id_contest']) || !(int)$_REQUEST['id_contest']){
				$OUT->errorcode[]=array_merge($INVALID['id_contest'],array('параметр'=>'id_contest(код конкурса)'));
			}else{$id_contest=(int)$_REQUEST['id_contest'];}
			if(!count($OUT->errorcode) && false !== $ret=mysql_query_array("
				# user -> my_vote
				# мои голоса в конкурсе
				SELECT
					p_to.id as 'to',
					IF(p_to.role IS NULL, p_to.user_name, p_to.team_name) as 'to_name',
					IF(my.role IS NULL, my.user_name,
						IF(my.role=1,my.team_name,CONCAT(my.team_name,'::',my.user_name))
					) as 'from',
					v.value, v.desc
				FROM
					(
						SELECT @contest:=$id_contest, @user:=$id_user
					) as `setup`,
					( # я
						( # участники и члены команд
							SELECT p.id, p.user, p.user_name, p.team_name, p.role
							FROM participation as p
							WHERE @contest=p.contest #AND IF(p.role IS NULL, 1, p.role=1)
						)
						UNION 
						( # голосующие зрители
							SELECT
								NULL as 'id',
								u.id as 'user',
								u.name as 'user_name',
								'' as 'team_name',
								3 as 'role'
							FROM user as u
								LEFT JOIN participation as p 
								ON (u.id=p.user AND @contest=p.contest )
								LEFT JOIN vote as v 
								ON ( u.id=v.from AND @contest=v.contest )
							WHERE p.id IS NULL AND v.from IS NOT NULL
							GROUP BY v.from
						)
					) as my,
					vote as v,
					participation as p_to
				WHERE @contest=v.contest AND @user=my.user AND v.from=my.user AND v.to=p_to.id
				ORDER BY v.value DESC
			")){
				$OUT->result=$ret;
			};
		break;
		case 'vote_for_my_contest_list':
			if(!isset($_REQUEST['id_user']) || !(int)$_REQUEST['id_user']){
				$OUT->errorcode[]=array_merge($INVALID['id_user'],array('параметр'=>'id_user(код пользователя)'));
			}else{$id_user=(int)$_REQUEST['id_user'];}
			if(!count($OUT->errorcode) && false !== $ret=mysql_query_array("
				# user -> vote_for_my_contest_list
				SELECT c.id, c.name
				FROM 
					(
						SELECT @user:=$id_user
					) as `setup`,
					contest as c,
					participation as p,
					vote as v
				WHERE c.id=v.contest AND @user=p.user AND v.to=p.id
				GROUP BY v.contest
			")){
				$OUT->result=$ret;
			};
		break;
		case 'vote_for_my_list': // голоса для меня с голосами зрителей
			if(!isset($_REQUEST['id_user']) || !(int)$_REQUEST['id_user']){
				$OUT->errorcode[]=array_merge($INVALID['id_user'],array('параметр'=>'id_user(код пользователя)'));
			}else{$id_user=(int)$_REQUEST['id_user'];}
			if(!isset($_REQUEST['id_contest']) || !(int)$_REQUEST['id_contest']){
				$OUT->errorcode[]=array_merge($INVALID['id_contest'],array('параметр'=>'id_contest(код конкурса)'));
			}else{$id_contest=(int)$_REQUEST['id_contest'];}
			if(!count($OUT->errorcode)){// нет ошибок
				mysql_query_log('begin');
				mysql_query_log("SET @contest:=$id_contest, @user:=$id_user");				
				$OUT->result=mysql_query_array("
					# user -> vote_for_my
					# голоса для меня с голосами зрителей
					SELECT
						IF(my.role IS NULL, my.user_name, my.team_name) as 'to',
						IF(v4my.role IS NULL, v4my.user_name,
							IF(v4my.role=1,v4my.team_name,CONCAT(v4my.team_name,'::',v4my.user_name))
						) as 'from',
						v.value, v.desc
					FROM
						( # я
							( # участники и члены команд
								SELECT p.id, p.user, p.user_name, p.team_name, p.role
								FROM participation as p
								WHERE @contest=p.contest #AND IF(p.role IS NULL, 1, p.role=1)
							)
							UNION 
							( # голосующие зрители
								SELECT
									NULL as 'id',
									u.id as 'user',
									u.name as 'user_name',
									'' as 'team_name',
									3 as 'role'
								FROM user as u
									LEFT JOIN participation as p 
									ON (u.id=p.user AND @contest=p.contest )
									LEFT JOIN vote as v 
									ON ( u.id=v.from AND @contest=v.contest )
								WHERE p.id IS NULL AND v.from IS NOT NULL
								GROUP BY v.from
							)
						) as my
						LEFT JOIN
						(
							(# голоса участников для меня
								SELECT 
									part.id,
									part.user,
									part.user_name,
									part.team_name,
									part.role,
									p.id as 'pid',
									v.id as 'vote',
									1 as 'order'
								FROM participation as part, # некоторый участник
									participation as p, # я 
									vote as v
								WHERE p.contest=@contest AND part.contest=@contest AND v.contest=@contest
									AND p.id=v.to AND part.user = v.from
									AND IF( part.role IS NULL, 1, part.role=1 )
							)
							UNION
							(# голоса участников, входящих в состав команды, для меня
								SELECT 
									part.id,
									part.user,
									part.user_name,
									part.team_name,
									part.role,
									p.id as 'pid',
									v.id as 'vote',
									2 as 'order'
								FROM participation as part, # некоторый участник
									participation as p, # я 
									vote as v
								WHERE p.contest=@contest AND part.contest=@contest AND v.contest=@contest
									AND p.id=v.to AND part.user = v.from AND part.role=2
							)
							UNION
							( # голоса зрителей для меня
								SELECT 
									NULL as 'id',
									u.id as 'user',
									u.name as 'user_name',
									'' as 'team_name',
									3 as 'role',
									p.id as 'pid',
									v.id as 'vote',
									2 as 'order'
								FROM (
										SELECT u.id, u.name
										FROM user as u
											LEFT JOIN participation as p 
											ON ( u.id=p.user AND @contest=p.contest )
											LEFT JOIN vote as v 
											ON ( u.id=v.from AND @contest=v.contest )
										WHERE p.id IS NULL AND v.from IS NOT NULL
										GROUP BY v.from
									) as u, # некоторый зритель
									participation as p, # я 
									vote as v
								WHERE p.contest=@contest AND v.contest=@contest
									AND p.id=v.to AND u.id = v.from
							) 
						) as `v4my` ON ( my.id=v4my.pid ),
						vote as v
					WHERE @contest=v.contest AND @user=my.user AND v.id=v4my.vote
					ORDER BY v4my.order, v.value DESC
				");
				mysql_query_log('commit');
			}
		break;
		case 'top_parti_v_parti':
			if(!count($OUT->errorcode) && false !== $ret=mysql_query_array("
				# топ конкурсантов // по версии участников
				SELECT
					pto.user,
					pto.team,
					IFNULL(pto.team_name, pto.user_name) as 'to',
					COUNT(DISTINCT v.contest) as 'contest_count',
					COUNT(v.value) as 'count',
					SUM(v.value) as 'score'
				FROM
				 contest as c,
				 participation as pto,
				 user as u,
				 vote as v
				LEFT JOIN participation as pfrom ON(v.from=pfrom.user AND v.contest=pfrom.contest)
				WHERE v.contest=c.id AND
					v.to=pto.id AND v.contest=pto.contest AND IF(pto.role IS NULL, 1, pto.role=1) AND v.from=u.id
					AND pfrom.id IS NOT NULL AND IF(pfrom.role IS NULL, 1, pfrom.role=1)
				GROUP BY CONCAT(pto.user,'|',IFNULL(pto.team,''))
				ORDER BY `score` DESC
			")){
				$OUT->result=$ret;
			};
		break;
		case 'each_contest_stat_v_parti':
			if(!count($OUT->errorcode) && false !== $ret=mysql_query_array("
				# результаты каждого конкурса // по версии участников
				SELECT
					v.contest,
					c.name as 'contest_name',
					pto.user,
					pto.team,
					IFNULL(pto.team_name, pto.user_name) as 'to',
					COUNT(v.value) as 'count',
					SUM(v.value) as 'score'
				FROM
				 contest as c,
				 participation as pto,
				 user as u,
				 vote as v
				LEFT JOIN participation as pfrom ON(v.from=pfrom.user AND v.contest=pfrom.contest)
				WHERE v.contest=c.id AND
					v.to=pto.id AND v.contest=pto.contest AND IF(pto.role IS NULL, 1, pto.role=1) AND v.from=u.id
					AND pfrom.id IS NOT NULL AND IF(pfrom.role IS NULL, 1, pfrom.role=1)
				GROUP BY v.to
				ORDER BY `contest_name`,`score` DESC
			")){
				$OUT->result=$ret;
			};
		break;
		default:
			$OUT->errorcode[]=array_merge($INVALID['unknown_action'],array('действие'=>"`$of` -  неизвестно",'file:'=>'core/list.php'));
	}
	unset($of);
	response();
}catch (Exception $e) {
	exception_handler($e);
}
?>