<?  $page='Команда' ?>
<? include_once "before.php" ?>
<div class="page">
<?
/*----------------------------------------------------------------------------------------
									Установки
----------------------------------------------------------------------------------------*/
	$team =& new stdClass;
	$team->act=isset($_REQUEST['act'])?$_REQUEST['act']:false; // действия
	$team->view=isset($_REQUEST['view'])?$_REQUEST['view']:'list'; // что показывать
	$team->id=isset($_REQUEST['id'])?$_REQUEST['id']:false; // выбрана команда
	$team->valid=false; // существует ли         свои команды
?>
<ul class="page_menu"><li><span class="header">Список: </span><a href="?view=list">команды</a></li>
<?
	if($LOGIN->valid){ // авторизованн
?>
<li><span class="header">Профиль: </span><a href="?view=list_main">свои команды</a></li>
<?
	}
?>
</ul>
<? 
	if($LOGIN->valid && $team->act){ // if действия
/*----------------------------------------------------------------------------------------
									Выполнение действий
----------------------------------------------------------------------------------------*/
		$goto="?view={$team->view}&id={$team->id}"; // переход на страницу в случае успешных действий
		$v=false; // успешность
		switch($team->act){ // switch действия
			case 'edit_team':
				include 'core/submit.php';
				if(!process_errors()){
					echo "<p class='response ".(($v=$OUT->result[0]->valid)?'success':'fail')."'>".($v?"Выполнено успешно":"Не выполнено")."</p>";
				}else{
					$goto=false; // рано ещё уходить, есть ошибки
				}				
			break;
			case 'team-leader_remove_member':
				include 'core/submit.php';
				if(!process_errors()){
					echo "<p class='response ".(($v=$OUT->result[0]->valid)?'success':'fail')."'>".($v?"Выполнено успешно":"Не выполнено")."</p>";
				}					
			break;
			case 'team-leader_to-candidate_response':
				include 'core/submit.php';
				if(!process_errors()){
					echo "<p class='response ".(($v=$OUT->result[0]->valid)?'success':'fail')."'>".($v?"Выполнено успешно":"Не выполнено")."</p>";
				}					
			break;
			case 'team-leader_invite-user_to-team':
				include 'core/submit.php';
				if(!process_errors()){
					echo "<p class='response ".(($v=$OUT->result[0]->valid)?'success':'fail')."'>".($v?"Выполнено успешно":"Не выполнено")."</p>";
				}					
			break;
			case 'team_send_contest_offer':
				include 'core/submit.php';
				if(!process_errors()){
					echo "<p class='response ".(($v=$OUT->result[0]->valid)?'success':'fail')."'>".($v?"Выполнено успешно":"Не выполнено")."</p>";
				}					
			break;
			default:
?>
	<div class="access_denied"><p><? echo $MESSAGE['action_not_found'] ?></p></div>
<?			
		} // switch действия
		if($goto)
			get_reload($goto,$v);
		else
			post_reload($v);
	}else{ // if действия
/*----------------------------------------------------------------------------------------
								Отображение страницы
----------------------------------------------------------------------------------------*/
		switch($team->view){ // что показывать
			case 'list':
				if($LOGIN->valid){ // авторизованн
?>
<a class="input add" href="team-maker.php">создать команду</a>
<?
				} // авторизованн
				$_REQUEST['of']='team';
				include 'core/list.php';
				process_errors();
?>
<span class="header">список команд</span>
<ul class="list">
<?
				foreach($OUT->result as $i=>$item){
					echo "<li><a href='?view=about&id={$item['id']}'>#{$item['id']}	{$item['name']}</a></li>\n";
				}
?>
</ul>
<?			
			break;
			case 'list_main':
				if($LOGIN->valid){ // авторизованн
					$_REQUEST['id']=$LOGIN->id;
					$_REQUEST['of']='teams_for_user';
					include 'core/list.php';
					process_errors();
?>
<span class="header">свои команды</span>
<ul class="list">
<?
					foreach($OUT->result as $i=>$item){
						echo "<li><a href='?id={$item['team']}&view=".($item['role']==1?"edit' class='new'":"about'").">{$item['team_name']}</a></li>\n";
					}
?>
</ul>
<?			
				}else{ // авторизованн
?>
	<div class="access_denied"><p><? echo $MESSAGE['user_access'] ?></p></div>
<?					
				}
			break;
			case 'about':
				if($team->id){ // выбрана команда
					$_REQUEST['of']='team';
					include 'core/about.php';
					if(!process_errors()){
						$team->valid=(bool)count($OUT->result);
					}
					if($team->valid){ // подгружаем данные
						$team->name=$OUT->result[0]['name'];
						$team->desc=$OUT->result[0]['desc'];
						$team->mktime=$OUT->result[0]['mktime'];
					}
				}	
				if($team->valid){ // существует ли
?>
<span class='header'>О команде:</span><br />
<div align="center">
	<h1><? echo $team->name ?></h1>
</div>
<p class='text'><? echo nl2br($team->desc) ?></p>
<br />
<span class='header'>участники команды:</span>
<ul class="list">
<?
					$_REQUEST['of']='users_in_team';
					include 'core/list.php';
					process_errors();
					foreach($OUT->result as $i=>$item){
						echo "<li><a href='user.php?view=about&id={$item['user']}'>{$item['user_name']}</a> <i title='{$item['role_desc']}'>{$item['role_name']}</i></li>";
					}		
?>
</ul><br />
    <span class="header">Участвует в конкурсах:</span><br />	
	<ul class="list">	
<?
					$_REQUEST['of']='contest_for_team';
					include "core/list.php";
					process_errors();
					foreach($OUT->result as $item){
						echo "<li><a href='contest.php?view=about&id={$item['contest']}'>{$item['contest_name']}</a> <b>{$item['team_name']}</b>[{$item['num_members']}]</li>";
					}
?>
	</ul><br />
<?
				}else{ // существует ли
?>
<div class="access_denied"><p><? echo $MESSAGE['team_not_found'] ?></p></div>
<?
				}
			break;
			case 'edit':
				if($LOGIN->valid){ // авторизованн
					if($team->id){ // выбрана команда
						$_REQUEST['id_user']=$LOGIN->id;
						$_REQUEST['id_team']=$team->id;	
						$_REQUEST['of']='teams_for_user';
						include 'core/about.php';
						if(!process_errors()){
							$team->valid=(bool)count($OUT->result);
						}
					}	
					if($team->valid){ // подгружаем данные
						$team->name=$OUT->result[0]['team_name'];
						$team->desc=$OUT->result[0]['team_desc'];
						$team->role=$OUT->result[0]['role'];
						$team->role_name=$OUT->result[0]['role_name'];
						
						if($team->role==1){ // лидер команды
?>
<span class='header'>редактируется команда:</span><br />
<div align="center">
	<h1><? echo $team->name ?></h1>
</div>
<form id="form2" name="form2" method="post" action="?view=edit&id=<? echo $team->id ?>" class="input_form">
    <label for="team_desc">описание команды</label>
    <textarea name="team_desc" rows="5" id="team_desc"><? echo $team->desc ?></textarea>
    <label for="edit_team">редактировать команду</label>
    <input type="submit" name="act" id="edit_team" value="edit_team" />
</form>
<br />
<span class='header'>Участники команды:</span>
<ul class="list">
<?
							$_REQUEST['of']='users_in_team';
							include 'core/list.php';
							process_errors();
							foreach($OUT->result as $i=>$item){
								echo "<li><a href='user.php?view=".($LOGIN->id==$item['user']?"profile' class='new'":"about&id={$item['user']}'").">{$item['user_name']}</a> <i title='{$item['role_desc']}'>{$item['role_name']}</i>".($item['role']!=1?"<a class='dimness' href='?view={$team->view}&id={$team->id}&act=team-leader_remove_member&member={$item['id']}'>[удалить]</a>":"")."</li>";
							}		
?>
</ul><br />
    <span class="header">Спиок кандидатов в участники команды: </span>
    <ul class="list">
<? 					
					$_REQUEST['id']=$team->id;
					$_REQUEST['of']='candidate_in_team';
					include "core/list.php";
					process_errors();
					foreach($OUT->result as $item){
						echo "<li><a href='user.php?view=about&id={$item['user']}'>{$item['user_name']}</a> <i title='{$item['role_desc']}'>{$item['role_name']}</i> <a href='?view={$team->view}&id={$team->id}&act=team-leader_to-candidate_response&id={$team->id}&member={$item['id']}&value=accept'>[принять]</a> <a href='?view={$team->view}&id={$team->id}&act=team-leader_to-candidate_response&id={$team->id}&member={$item['id']}&value=decline'>[отклонить]</a></li>";
					}
?>
	</ul><br />
	<span class="header">Предложить участие в команде: </span>
<form id="form2" name="form2" method="post" action="">
	<label for="id_user">Пользователь</label>
	<select name="id_user"  id="id_user">
<?
					$_REQUEST['of']='user';
					include "core/list.php";
					if(!process_errors()){
						foreach($OUT->result as $item){
							echo "<option value='{$item['id']}'>{$item['name']}</option>";
						}
					};	
?>
	</select>
    <br />
    <label for="send_user_offer">отправить предложение</label>
    <input type="submit" name="act" id="send_user_offer" value="team-leader_invite-user_to-team" />
</form><br />
	<span class="header">Принять участие в конкурсе: </span>
<form id="form3" name="form3" method="post" action="">
	<input type="hidden" name="id_team" value="<? echo $team->id ?>" />
	<label for="id_contest">конкурс</label>
	<select name="id_contest" id="id_contest">
<?	
					$_REQUEST['of']='contest';
					include "core/list.php";
					if(!process_errors()){
						foreach($OUT->result as $item){
							echo "<option value='{$item['id']}'>{$item['name']}</option>";
						}
					};				
?>
	</select>
    <br />
    <label for="team_send_contest_offer">принять участие</label>
    <input type="submit" name="act" id="team_send_contest_offer" value="team_send_contest_offer" />
</form><br />
    <span class="header">Участвует в конкурсах:</span><br />	
	<ul class="list">	
<?
					$_REQUEST['of']='contest_for_team';
					include "core/list.php";
					process_errors();
					foreach($OUT->result as $item){
						echo "<li><a href='contest.php?view=about&id={$item['contest']}'>{$item['contest_name']}</a> <b>{$item['team_name']}</b>[{$item['num_members']}]</li>";
					}
?>
	</ul><br />
<?
						}else{ // лидер команды
?>
<div class="access_denied"><p><? echo $MESSAGE['team_leader_access'] ?></p></div>		
<?			
						} // else лидер команды
					}else{ // else подгружаем данные
?>
<div class="access_denied"><p><? echo $MESSAGE['team_not_found'] ?></p></div>		
<?
					}
				}else{ // авторизованн
?>
	<div class="access_denied"><p><? echo $MESSAGE['user_access'] ?></p></div>
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
