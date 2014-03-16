<?  $page='Создание команды' ?>
<? include_once 'before.php' ?>
<div class="page">
<?
/*----------------------------------------------------------------------------------------
									Установки
----------------------------------------------------------------------------------------*/
	$reg =& new stdClass;
	$reg->act=isset($_REQUEST['act'])?$_REQUEST['act']:false; // действия
	$reg->name=@$_REQUEST['team_name'];
	$reg->desc=@$_REQUEST['team_desc'];
	$reg->goto=isset($_REQUEST['goto'])?$_REQUEST['goto']:''; // перейти по адресу

	if(!$LOGIN->valid){ // авторизованн
?>
	<div class="access_denied"><p><? echo $MESSAGE['user_access'] ?></p></div>
<?
	}else if( $reg->act ){ // действия
		$v=false; // есть ошибки
		switch($_REQUEST['act']){ // выбранное действие
			case 'check_team-name':
				include 'core/check.php';
				if(!process_errors()){
					echo "<p class='response ".(($v=$OUT->result[0]->valid)?'success':'fail')."'>".($v?"имя команды свободно":"имя команды занято")."</p>";
				}
			break;
			case 'add_team':
				include 'core/submit.php';
				if(!process_errors()){
					echo "<p class='response ".(($v=$OUT->result[0]->valid)?'success':'fail')."'>".($v?"команда успешно зарегистрирована":"команда не зарегистрированна")."</p>";
					if($v)
						$reg->goto='team.php?view=edit&id='.$OUT->result[0]->idTeam;
				}
			break;
		} // выбранное действие 
		if($reg->goto)
			get_reload($reg->goto);
		else
			post_reload($v);
	}else{ // действий нет
?>
<form id="form2" name="form2" method="post" action="" class="input_form">
  <label for="team_name">имя команды</label>
  <input type="text" name="team_name" id="team_name" value="<? echo $reg->name ?>"  />
  <label for="team_desc">описание команды</label>
  <textarea name="team_desc" rows="5" id="team_desc"><? echo $reg->desc ?></textarea>
  <label for="check_team-name">проверить имя команды</label>
  <input type="submit" name="act" id="check_team-name" value="check_team-name" />
  <label for="add_team">зарегистрировать команду</label>
  <input type="submit" name="act" id="add_team" value="add_team" />
</form>
<? 
	} // действий нет
?>
</div>
<? include_once 'after.php' ?>
