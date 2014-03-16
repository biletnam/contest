<?  $page='Пользователь' ?>
<? include_once 'before.php' ?>
<div class="page">
<?
/*----------------------------------------------------------------------------------------
									Установки
----------------------------------------------------------------------------------------*/
	$user =& new stdClass;
	$user->act=isset($_REQUEST['act'])?$_REQUEST['act']:false; // действия
	$user->view=isset($_REQUEST['view'])?$_REQUEST['view']:'list'; // что показывать
	$user->id=isset($_REQUEST['id'])?$_REQUEST['id']:($user->view=='profile'?$LOGIN->id:false); // выбранн пользователь
	$user->valid=false; // существует ли
?>
<ul class="page_menu">
	<li><span class="header">Список: </span><a href="?view=list">пользователи</a></li>
<?
	if($LOGIN->valid){ // авторизованн
?>
	<li><span class="header">Профиль: </span><a href="?view=profile"><? echo $LOGIN->name ?></a></li>
<?
	} // авторизованн
?>
</ul>
<? 
	if($LOGIN->valid && $user->act){ // if действия
/*----------------------------------------------------------------------------------------
									Выполнение действий
----------------------------------------------------------------------------------------*/
		$goto="?view={$user->view}&id={$user->id}"; // переход на страницу в случае успешных действий
		$v=false; // успешность
		switch($user->act){ // switch действия
			case 'user_invite_to_team_response':
				include "core/submit.php";
				if(!process_errors()){
					echo "<p class='response ".(($v=$OUT->result[0]->valid)?'success':'fail')."'>".($v?"Выполнено успешно":"Не выполнено")."</p>";
				}				
			break;
			case 'member_leave_team':
				include "core/submit.php";
				if(!process_errors()){
					echo "<p class='response ".(($v=$OUT->result[0]->valid)?'success':'fail')."'>".($v?"Выполнено успешно":"Не выполнено")."</p>";
				}			
			break; 
			case 'user_enter-to_team_offer':
				include "core/submit.php";		
				if(!process_errors()){
					echo "<p class='response ".(($v=$OUT->result[0]->valid)?'success':'fail')."'>".($v?"Выполнено успешно":"Не выполнено")."</p>";
				}		
			break;
			case 'user_send_contest_offer':
				include "core/submit.php";		
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
			get_reload($goto);
		else
			post_reload($v);
	}else{ // if действия
/*----------------------------------------------------------------------------------------
								Отображение страницы
----------------------------------------------------------------------------------------*/
		switch($user->view){ // что показывать
			case 'list':
				$_REQUEST['of']='user';
				include 'core/list.php';
				process_errors();
?>
	<span class="header">список пользователей</span>
	<ul class="list">
<?
				foreach($OUT->result as $i=>$item){
					echo "<li><a href='?view=about&id={$item['id']}'>#{$item['id']} {$item['name']}</a></li>\n";
				}
?>
	</ul>
<?
			break;
			case 'about':
				$_REQUEST['of']='user';
				include "core/about.php";	
				if(!process_errors()){
					$user->valid=(bool)count($OUT->result);
				}
				if($user->valid){ // подгружаем данные
					$user->name=$OUT->result[0]['name'];
					$user->mktime=$OUT->result[0]['mktime'];
?>	
    <span class="header">О пользователе: </span>
    <div align="center"><h1><? echo $user->name ?></h1></div>
    <span class="header">Участвует в командах:</span><br />
	<ul class="list">	
<?
					$_REQUEST['of']='teams_for_user';
					include "core/list.php";
					process_errors();
					foreach($OUT->result as $item){
						echo "<li><a href='team.php?view=about&id={$item['team']}'>{$item['team_name']}</a> <i title='{$item['role_desc']}'>{$item['role_name']}</i></li>";
					}
?>
	</ul><br />
    <span class="header">Участвует в конкурсах:</span><br />	
	<ul class="list">	
<?
					$_REQUEST['of']='contest_for_user';
					include "core/list.php";
					process_errors();
					foreach($OUT->result as $item){
						echo "<li><a href='contest.php?view=about&id={$item['contest']}'>{$item['contest_name']}</a>";
						if($item['team']){
							echo " в команде ";
							if(@$item['team_live']){
								echo "<a href='team.php?view=about&id={$item['team_live']}'>{$item['team_name']}</a>";
							}else{
								echo "<span class='remove'>{$item['team_name']}</span>";					
							}
							echo " <i>{$item['role_name']}</i>";
						}
						echo "</li>";
					}
?>
	</ul><br />
<?		
				}else{ // подгружаем данные
?>
<div class="access_denied"><p><? echo $MESSAGE['user_not_found'] ?></p></div>	
<?
				}
			break;
			case 'profile':
				if($LOGIN->valid){ // авторизованн
?>
    <span class="header">Вы: </span><div align="center"><h1><? echo $LOGIN->name ?></h1></div>
    <span class="header">Участвуете в командах:</span><br />	
<?	
					$_REQUEST['id']=$LOGIN->id;
					$_REQUEST['of']='teams_for_user';
					include "core/list.php";
					process_errors();
?>
	<ul class="list">
<?
					foreach($OUT->result as $item){
						echo "<li>".($item['role']==1?"<a class='new'  href='team.php?view=edit&id={$item['team']}'>{$item['team_name']}</a>":"<a href='team.php?view=about&id={$item['team']}'>{$item['team_name']}</a>")." <i title='{$item['role_desc']}'>{$item['role_name']}</i> <a class='dimness' href='?view={$user->view}&id={$user->id}&act=member_leave_team&id_member={$item['id']}'>[выйти из команды]</a></li>";
					}
?>
	</ul><br />
    <span class="header">Список приглашений в команды: </span>
    <ul class="list">
<? 					
					$_REQUEST['id']=$LOGIN->id;
					$_REQUEST['of']='user_invite_to_team';
					include "core/list.php";
					process_errors();
					foreach($OUT->result as $item){
						echo "<li><a href='team.php?view=about&id={$item['team']}'>{$item['team_name']}</a> <i title='{$item['role_desc']}'>{$item['role_name']}</i>
							<a href='?view={$user->view}&id={$user->id}&act=user_invite_to_team_response&id_member={$item['id']}&value=accept'>[принять]</a> <a href='?view={$user->view}&id={$user->id}&act=user_invite_to_team_response&id_member={$item['id']}&value=decline'>[отклонить]</a></li>";
					}
?>
	</ul><br />
	<span class="header">Отправить заявку на вход в состав команды: </span>
<form id="form2" name="form2" method="post" action="">
	<label for="id_team">команда</label>
	<select name="id" id="id_team">
<?	
					$_REQUEST['of']='team';
					include "core/list.php";
					if(!process_errors()){
						foreach($OUT->result as $item){
							echo "<option value='{$item['id']}'>{$item['name']}</option>";
						}
					};				
?>
	</select>
    <br />
    <label for="send_team_offer">отправить предложение</label>
    <input type="submit" name="act" id="send_team_offer" value="user_enter-to_team_offer" />
</form><br />
	<span class="header">Принять участие в конкурсе: </span>
<form id="form3" name="form3" method="post" action="">
	<label for="id_contest">конкурс</label>
	<select name="id" id="id_contest">
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
    <label for="user_send_contest_offer">принять участие</label>
    <input type="submit" name="act" id="user_send_contest_offer" value="user_send_contest_offer" />
</form><br />
    <span class="header">Участвует в конкурсах:</span><br />	
	<ul class="list">	
<?
					$_REQUEST['of']='contest_for_user';
					include "core/list.php";
					process_errors();
					foreach($OUT->result as $item){
						echo "<li><a href='contest.php?view=about&id={$item['contest']}'>{$item['contest_name']}</a>";
						if($item['team']){
							echo " в команде ";
							if(@$item['team_live']){
								echo "<a href='team.php?view=about&id={$item['team_live']}'>{$item['team_name']}</a>";
							}else{
								echo "<span class='remove'>{$item['team_name']}</span>";					
							}
							echo " <i>{$item['role_name']}</i>";
						}
						echo "</li>";
					}
?>
	</ul><br />
<?
				} // авторизованн
			break;
			default:
?>
<div class="access_denied"><p><? echo $MESSAGE['bug'] ?></p></div>	
<?				
		} // что показывать
	} // else действия
?>
</div>
<? include_once 'after.php' ?>
