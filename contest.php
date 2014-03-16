<?  $page='Конкурс' ?>
<? include_once "before.php" ?>
<div class="page">
<?
/*----------------------------------------------------------------------------------------
									Установки
----------------------------------------------------------------------------------------*/
	// конкурс
	$contest = new stdClass;
	$contest->act=isset($_REQUEST['act'])?$_REQUEST['act']:false; // действия
	$contest->view=isset($_REQUEST['view'])?$_REQUEST['view']:'list'; // что показывать
	$contest->id=isset($_REQUEST['id'])?$_REQUEST['id']:false; // выбранн конкурс
	$contest->valid=false; // существует ли
	if($contest->id){ // выбранн конкурс
		$_REQUEST['of']='contest';
		include 'core/about.php';
		global $OUT;
		if(!process_errors()){
			$contest->valid=(bool)count($OUT->result);
		}
		if($contest->valid){ // подгружаем данные
			$contest->name=$OUT->result[0]['name'];
			$contest->rules=$OUT->result[0]['rules'];
			$contest->desc=$OUT->result[0]['desc'];
			$contest->content=$OUT->result[0]['content'];
			//$contest->mktime=$OUT->result[0]['mktime'];
			$contest->state=$OUT->result[0]['state'];

			$contest->open_date=load_date($OUT->result[0]['open_date']);
			$contest->wait_date=load_date($OUT->result[0]['wait_date']);
			$contest->vote_date=load_date($OUT->result[0]['vote_date']);
			$contest->close_date=load_date($OUT->result[0]['close_date']);

			$contest->auto_tick=$OUT->result[0]['auto_tick'];
		}
	}

	// дополнения к профилю в рамках конкурса
	$LOGIN->in_contest=false; // в конкурсе
	$LOGIN->voted_in_contest=false; // проголосовал в конкурсе
	$LOGIN->contest_access=false; // права конкурсанта
	$LOGIN->role=false; // роль конкурсанта
	if($contest->valid && $LOGIN->valid){ // авторизованн
		$_REQUEST['id_user']=$LOGIN->id;
		$_REQUEST['id_contest']=$contest->id;
		$_REQUEST['of']='user_data_for_contest';
		include 'core/about.php';
		if(!process_errors()){
			$LOGIN->in_contest=(bool)count($OUT->result); // пред-установка
		}
		if($LOGIN->in_contest){ // подгружаем данные
			$LOGIN->uid=$OUT->result[0]['uid']; // id участника конкурса
			$LOGIN->pid=$OUT->result[0]['pid']; // id конкурсанта с правами
			$LOGIN->user=$OUT->result[0]['user']; // id пользователя
			$LOGIN->user_name=$OUT->result[0]['user_name']; // имя пользователя
			$LOGIN->team=$OUT->result[0]['team']; // id команды
			$LOGIN->team_name=$OUT->result[0]['team_name']; // имя команды
			$LOGIN->role=$OUT->result[0]['role']; // id роль в команде
			$LOGIN->role_name=$OUT->result[0]['role_name']; // имя роли в команде
			$LOGIN->role_desc=$OUT->result[0]['role_desc']; // описание роли
			$LOGIN->in_contest=(bool)$LOGIN->uid; // в конкурсе
			$LOGIN->contest_access =$LOGIN->in_contest && $LOGIN->pid==$LOGIN->uid; // есть права
			$LOGIN->name_in_contest=$OUT->result[0]['name']; // имя в конкурсе
			$LOGIN->num_vote=$OUT->result[0]['num_vote']; // кол-во голосов
			$LOGIN->voted_in_contest=(bool)$LOGIN->num_vote; // проголосовал
			$LOGIN->work=$OUT->result[0]['work']; // id работы
			$LOGIN->worked=(bool)$LOGIN->work; // работа отправлена
		}		
	}
	if($LOGIN->in_contest){
		$_REQUEST['what']='contest';
		include 'core/tick.php';	
		process_errors();
	};
?>
<ul class="page_menu"><li><span class="header">Список: </span><a href="?view=list">конкурсов</a></li><li><span class="header">Список: </span><a href="?view=list_asc">конкурсов по алфавиту</a></li>
</ul>
<? 
	if($LOGIN->valid && $contest->act){ // if действия
/*----------------------------------------------------------------------------------------
									Выполнение действий
----------------------------------------------------------------------------------------*/
		$goto="?view={$contest->view}&id={$contest->id}"; // переход на страницу в случае успешных действий
		$v=false; // успешность
		switch($contest->act){ // switch действия
			case 'edit_contest':
				include 'core/submit.php';
				if(!process_errors()){
					echo "<p class='response ".(($v=$OUT->result[0]->valid)?'success':'fail')."'>".($v?"Выполнено успешно":"Не выполнено")."</p>";
				}else{
					$goto=false; // рано ещё уходить, есть ошибки
				}			
			break;
			case 'user_send_contest_offer':
				include "core/submit.php";		
				if(!process_errors()){
					echo "<p class='response ".(($v=$OUT->result[0]->valid)?'success':'fail')."'>".($v?"Выполнено успешно":"Не выполнено")."</p>";
				}		
			break;
			case 'team_send_contest_offer':
				include "core/submit.php";		
				if(!process_errors()){
					echo "<p class='response ".(($v=$OUT->result[0]->valid)?'success':'fail')."'>".($v?"Выполнено успешно":"Не выполнено")."</p>";
				}					
			break;
			case 'user_send_work':
				$_REQUEST['work']='work_rar';
				$_REQUEST['pid']=$LOGIN->pid;
				include "core/submit.php";
				if(!process_errors()){
					echo "<p class='response ".(($v=$OUT->result[0]->valid)?'success':'fail')."'>".($v?"Выполнено успешно":"Не выполнено")."</p>";
				}				
			break;
			default:
				global $MESSAGE;
?>
	<div class="access_denied"><p><? echo $MESSAGE['action_not_found'] ?></p></div>
<?			
		} // switch действия
		if($goto)
			get_reload($goto, $v);
		else
			post_reload($v);
	}else{ // if действия
/*----------------------------------------------------------------------------------------
								Отображение страницы
----------------------------------------------------------------------------------------*/
		switch($contest->view){ // что показывать
			case 'list':
				if($LOGIN->valid && $LOGIN->name_translit=='admin'){ // авторизованн как админ
?>
<a class="input add" href="contest-maker.php">создать конкурс</a>
<?
				} // авторизованн
				$_REQUEST['of']='contest';
				include 'core/list.php';
				process_errors();
?>
<span class="header">список конкурсов</span>
<ul class="list">
<?
				foreach($OUT->result as $i=>$item){
					echo "<li><a href='?view=about&id={$item['id']}'>#{$item['id']}	{$item['name']}</a></i></li>\n";
				}
?>
</ul>
<?			
			break;
			case 'list_asc':
				if($LOGIN->valid && $LOGIN->name_translit=='admin'){ // авторизованн как админ
?>
<a class="input add" href="contest-maker.php">создать конкурс</a>
<?
				} // авторизованн
				$_REQUEST['of']='contest_asc';
				include 'core/list.php';
				process_errors();
?>
<span class="header">список конкурсов</span>
<ul class="list">
<?
				foreach($OUT->result as $i=>$item){
					echo "<li><a href='?view=about&id={$item['id']}'>#{$item['id']}	{$item['name']}</a></i></li>\n";
				}
?>
</ul>
<?			
			break;
			case 'about':	
				if($contest->valid){ // существует ли
?>
<span class='header'>О конкурсе:</span><br />
<div align="center">
	<h1><? echo $contest->name ?></h1><? 
	if($LOGIN->valid && $LOGIN->name_translit=='admin'){ // авторизованн как админ	
?><span class="response new"><a href="?view=edit&id=<? echo $contest->id ?>" class="new">править</a></span><?
	}
?>
</div>
<span class="header">Состояние конкурса:</span>
<span class='state <? if($contest->state=='open')echo 'true' ?>'><? echo nl2br($contest->state) ?></span><br />
<br />
<span class="header">Правила конкурса:</span>
<p class='text'><? echo nl2br($contest->rules) ?></p>
<span class="header">Описание конкурса:</span>
<p class='text'><? echo nl2br($contest->desc) ?></p>

<span class="header">дата открытия конкурса:</span>
<?
	$width=(int)(date_pos($contest->open_date,$contest->wait_date)*100);
	$width.='%';
?>
<div style="background-color:#8CF876; text-align:center; height:20px; width:<? echo $width?>"><? echo $width ?></div>
<p class='text'><? echo $contest->open_date ?></p>
<span class="header">дата начала сдачи работ:</span>
<?
	$width=(int)(date_pos($contest->wait_date,$contest->vote_date)*100);
	$width.='%';
?>
<div style="background-color:#8CF876; text-align:center; height:20px; width:<? echo $width?>"><? echo $width ?></div>
<p class='text'><? echo $contest->wait_date ?></p>
<span class="header">дата начала голосования:</span>
<?
	$width=(int)(date_pos($contest->vote_date,$contest->close_date)*100);
	$width.='%';
?>
<div style="background-color:#8CF876; text-align:center; height:20px; width:<? echo $width?>"><? echo $width ?></div>
<p class='text'><? echo $contest->vote_date ?></p>
<span class="header">дата закрытия, подведения итогов:</span>
<p class='text'><? echo $contest->close_date ?></p>
<span class="header">живой статус:</span>
<p class='text'><? echo $contest->auto_tick ?></p>

<? if(in_array($contest->state,array('vote','close'))){ ?>
<span class="header">Архивчек:</span>
<a href="download.php?act=contest_works&contest=<? echo $contest->id; ?>">скачать</a><br />
<? } ?>
<br />
<?
?>
<span class='header'>Участники конкурса:</span>
<ul class="list">
<?
					$_REQUEST['of']='activ_participations_in_contest';
					include 'core/list.php';
					process_errors();
					$part=$OUT->result;
					foreach($part as $item){
						if(@$item['team']){
							if(@$item['team_live']){
								echo "<li><a href='team.php?view=about&id={$item['team_live']}'>{$item['team_name']}</a>";
							}else{
								echo "<li><span class='remove'>{$item['team_name']}</span>";	
							}
							echo " [{$item['num_members']}] <span class='header'>{ ";
							// подгружаем участников команды
							$_REQUEST['id_team']=$item['team'];
							$_REQUEST['of']='contest_team_members';
							include 'core/list.php';
							process_errors();
							foreach($OUT->result as $member){
								if(@$member['user_live']){
									echo "<a ".(@$member['role']==1?" class='team_leader' ":"")." href='user.php?view=about&id={$member['user_live']}'>{$member['user_name']}</a>";
								}else{
									echo "<span class='remove'>{$member['user_name']}</span>";
								}
								echo ", ";
							}
							echo " }</span></li>";													
						}else{
							if(@$item['user_live']){
								echo "<li><a href='user.php?view=about&id={$item['user_live']}'>{$item['user_name']}</a></li>";
							}else{
								echo "<li><span class='remove'>{$item['user_name']}</span></li>";
							}
						}
					}
?>
</ul><br />
<?				
					if($contest->state=='open' && $LOGIN->valid && !$LOGIN->in_contest){ // если не был
?>
<span class='header'>Поучавствовать в конкурсе:</span>
<div class="response"><span class="header">лично</span><a class="input" href="?view=about&id=<? echo $contest->id ?>&act=user_send_contest_offer"><? echo $LOGIN->name ?></a></div>
<?
						$_REQUEST['id']=$LOGIN->id;
						$_REQUEST['of']='teams_for_leader';
						include 'core/list.php';
						if(!process_errors() && count($OUT->result)){
?>
<div class="response"><span class="header">своей командой</span>
<form id="form_team_send" name="form3" method="post" enctype="multipart/form-data" action="" class="input_form">
<input type="hidden" name="id_contest" style="display:none" value="<? echo $contest->id ?>" />
<label for="fts_id_team">выберите команду</label>
<select name="id_team" id="fts_id_team">
<?
							foreach($OUT->result as $item){
								echo "<option value='{$item['team']}'>{$item['team_name']}</option>";
							}						
?>
</select><label for="fts_act">отправить</label>
<input name="act" id="fts_act" type="submit" value="team_send_contest_offer" />
</form>
<?
						};
?>
</div>
<?
					} // если не был
					if($contest->state=='wait' && $LOGIN->contest_access && !$LOGIN->worked){
?>
<span class='header'>Сдать конкурсную работу:</span>
<form id="form_seng_work" name="form_seng_work" enctype="multipart/form-data" method="post" action="" class="input_form">
	<label>Архив с работой</label>
	<input name="work_rar" type="file" />
	<label>Отправить</label>
	<input name="act" value="user_send_work" type="submit" />
</form>
<?
					}
					if($contest->state=='vote' && $LOGIN->valid){
?>	
<span class='header'><? echo $LOGIN->voted_in_contest?'Пере':'' ?>Голосование:</span>
<a class="input" href="vote.php?view=edit&contest=<? echo $contest->id ?>&user=<? echo $LOGIN->id ?>">
<?
						echo(
							is_null($LOGIN->role)?
							$LOGIN->user_name:
							($LOGIN->role==1 ?
								$LOGIN->team_name :
								$LOGIN->team_name.'::'.$LOGIN->user_name
							)
						);
?></a>
<?
					}
					if($contest->state=='close'){ // если известны результаты
						$_REQUEST['id_contest']=$contest->id;
						$_REQUEST['of']='contest_vote_table';
						include 'core/list.php';
						process_errors();
						$table=array();
						foreach($OUT->result as $item){
							if(!isset($table[$item['to']])){
								$table[$item['to']]=
									array(
										'name'=>$item['to'],
										'count'=>0,
										'score'=>0,
										'count_viewer'=>0,
										'score_viewer'=>0,
										'votes'=>array()
									);
							}	
							$part =& $table[$item['to']];
							if(false !== strpos($item['from'],'::')){ // is_viewer
								$part['count_viewer']++;
								$part['score_viewer']+=$item['value'];
							}else{
								$part['count']++;
								$part['score']+=$item['value'];						
							}
							$part['votes'][]=$item;
							unset($part);
						} // foreach
						function cmp($a, $b){
							return $b['score']-$a['score'];
						}
						usort($table, "cmp");
?>
<span class="header">Результаты голосования</span><br />
<table width="100%" class="tbl">
<thead>
	<tr>
		<td colspan="3"><div align="center">Участник</div></td>
	</tr>
	<tr>
		<td width="200">Голосующий</td>
		<td width="50" align="center">Оценка</td>
		<td>Коментарий</td>
	</tr>
</thead>
<tbody>
<?
						unset($part);
						foreach($table as $part){
?>
	<tr>
		<td colspan="3" bgcolor="#FFCC33"><div align="center"><? echo $part['name'] ?></div></td>
	</tr>
<?
							foreach($part['votes'] as $i=>$item){
?>
	<tr <? echo $i%2?'bgcolor="#eee"':'' ?> >
		<td><? echo $item['from'] ?></td>
		<td><? echo $item['value'] ?></td>
		<td><? echo nl2br($item['desc']) ?></td>
	</tr>
<?
							}
?>
	<tr bgcolor="#DDDDDD">
		<td><i>Баллы участников [ <? echo $part['count'] ?> ] :</i></td>
		<td><? echo $part['score'] ?></td>
		<td>&nbsp;</td>
	</tr>
	<tr bgcolor="#DDDDDD">
		<td><i>Баллы зрителей [ <? echo $part['count_viewer'] ?> ] :</i></td>
		<td><? echo $part['score_viewer'] ?></td>
		<td>&nbsp;</td>
	</tr>
<?				
						}
?>
</tbody>
</table>
<?
					} // если известны результаты
				}else{ // существует ли
?>
<div class="access_denied"><p><? echo $MESSAGE['contest_not_found'] ?></p></div>
<?
				}
			break;
			case 'edit':
			if($LOGIN->valid && $LOGIN->name_translit=='admin'){ // авторизованн как админ
				if($contest->id){ // выбранн конкурс
					$_REQUEST['of']='contest';
					include 'core/about.php';
					if(!process_errors()){
						$contest->valid=(bool)count($OUT->result);
					}
					if($contest->valid){ // подгружаем данные
						$contest->name=$OUT->result[0]['name'];
						$contest->rules=$OUT->result[0]['rules'];
						$contest->desc=$OUT->result[0]['desc'];
						$contest->content=$OUT->result[0]['content'];
						$contest->mktime=$OUT->result[0]['mktime'];
						$contest->state=$OUT->result[0]['state'];
					}
				}	
				if($contest->valid){ // существует ли
?>
<span class='header'>О конкурсе:</span><br />
<div align="center">
	<span class="response new"><a href="?view=about&id=<? echo $contest->id ?>" class="new">просмотр</a></span>
</div>
<form id="ecf" name="form2" method="post" action="?view=edit&id=<? echo $contest->id ?>" class="input_form">
    <label for="contest_name">имя конкурса</label>
    <input type="text" name="contest_name" id="contest_name" value="<? echo $contest->name ?>" />
	<label for="ecf_state">cостояние конкурса:</label>
	<select name="state" id="ecf_state">
<?
					foreach(array('nominate','open','wait','vote','close') as $v){
						echo "<option value='$v'".($v==$contest->state?"selected='selected'":"").">$v</option>";
					}
?>
	</select> <br />
    <label for="contest_rules">правила конкурса:</label>
    <textarea name="contest_rules" rows="5" id="contest_rules"><? echo $contest->rules ?></textarea>
    <label for="contest_desc">описание конкурса:</label>
    <textarea name="contest_desc" rows="5" id="contest_desc"><? echo $contest->desc ?></textarea>
  <label for="contest_open_date">дата(YYYY-MM-DD HH:MM:SS) открытия конкурса</label>
  <input type="text" name="contest_open_date" id="contest_open_date" value="<? echo $contest->open_date ?>" />
  <label for="contest_wait_date">дата(YYYY-MM-DD HH:MM:SS) начала сдачи работ</label>
  <input type="text" name="contest_wait_date" id="contest_wait_date" value="<? echo $contest->wait_date ?>" />
  <label for="contest_vote_date">дата(YYYY-MM-DD HH:MM:SS) начала голосования</label>
  <input type="text" name="contest_vote_date" id="contest_vote_date" value="<? echo $contest->vote_date ?>" />
  <label for="contest_close_date">дата(YYYY-MM-DD HH:MM:SS) закрытия, подведения итогов</label>
  <input type="text" name="contest_close_date" id="contest_close_date" value="<? echo $contest->close_date ?>" />
  <label for="contest_auto_tick">разрешить живой статус (автоматический переход состояния конкурса по достижении контрольного времени события)</label>
  <select name="contest_auto_tick" id="contest_auto_tick">
     <option value="0" <? if($contest->auto_tick==0)echo 'selected="selected"'; ?>>нет</option>
     <option value="1" <? if($contest->auto_tick==1)echo 'selected="selected"'; ?>>да</option>
  </select><br />
    <label for="contest_content">архивчек</label>
	<p><? echo CONTEST_LINK."/".rus2lat($contest->name).WORK ?></p>
    <input type="text" name="contest_content" id="contest_content" value="<? echo $contest->content ?>" />
    <label for="team_name">редактировать конкурс</label>
    <input type="submit" name="act" id="edit_contest" value="edit_contest" />
</form>
<?
				}else{ // существует ли
?>
<div class="access_denied"><p><? echo $MESSAGE['contest_not_found'] ?></p></div>
<?
				}
			}else{
?>
<div class="access_denied"><p><? echo $MESSAGE['admin_access'] ?></p></div>
<?
			}
			break;
			default:
?>
<div class="access_denied"><p><? echo $MESSAGE['bug'] ?></p></div>		
<?
		} // switch что показывать
	} // else действия
?>
</div>
<? include_once 'after.php' ?>
