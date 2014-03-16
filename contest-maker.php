<?  $page='Создание конкурса' ?>
<? include_once 'before.php' ?>
<div class="page">
<?
/*----------------------------------------------------------------------------------------
									Установки
----------------------------------------------------------------------------------------*/
	$reg =& new stdClass;
	$reg->act=isset($_REQUEST['act'])?$_REQUEST['act']:false; // действия
	$reg->name=@$_REQUEST['contest_name'];
	$reg->rules=@$_REQUEST['contest_rules'];
	$reg->desc=@$_REQUEST['contest_desc'];

	$reg->open_date=@$_REQUEST['contest_open_date'];
	$reg->wait_date=@$_REQUEST['contest_wait_date'];
	$reg->vote_date=@$_REQUEST['contest_vote_date'];
	$reg->close_date=@$_REQUEST['contest_close_date'];
	$reg->auto_tick=@$_REQUEST['contest_auto_tick'];

	$reg->goto=isset($_REQUEST['goto'])?$_REQUEST['goto']:''; // перейти по адресу
	if(!$LOGIN->valid){ // авторизованн
?>
	<div class="access_denied"><p><? echo $MESSAGE['user_access'] ?></p></div>
<?
	}else if( $reg->act ){ // действия
		$v=false; // есть ошибки
		switch($_REQUEST['act']){ // выбранное действие
			case 'check_contest-name':
				include 'core/check.php';
				if(!process_errors()){
					echo "<p class='response ".(($v=$OUT->result[0]->valid)?'success':'fail')."'>".($v?"имя конкурса свободно":"имя конкурса занято")."</p>";
				}
			break;
			case 'add_contest':
				include 'core/submit.php';
				if(!process_errors()){
					echo "<p class='response ".(($v=$OUT->result[0]->valid)?'success':'fail')."'>".($v?"конкурс успешно зарегистрированн":"конкурс не зарегистрированн")."</p>";
					if($v)
						$reg->goto='contest.php?view=edit&id='.$OUT->result[0]->id;
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
  <label for="contest_name">имя конкурса</label>
  <input type="text" name="contest_name" id="contest_name" value="<? echo $reg->name ?>" />
  <label for="contest_rules">правила конкурса</label>
  <textarea name="contest_rules" rows="5" id="contest_rules"><? echo $reg->rules ?></textarea>
  <label for="contest_desc">описание конкурса</label>
  <textarea name="contest_desc" rows="5" id="contest_desc"><? echo $reg->desc ?></textarea>
  <label for="contest_open_date">дата(YYYY-MM-DD HH:MM:SS) открытия конкурса</label>
  <input type="text" name="contest_open_date" id="contest_open_date" value="<? echo $reg->open_date ?>" />
  <label for="contest_wait_date">дата(YYYY-MM-DD HH:MM:SS) начала сдачи работ</label>
  <input type="text" name="contest_wait_date" id="contest_wait_date" value="<? echo $reg->wait_date ?>" />
  <label for="contest_vote_date">дата(YYYY-MM-DD HH:MM:SS) начала голосования</label>
  <input type="text" name="contest_vote_date" id="contest_vote_date" value="<? echo $reg->vote_date ?>" />
  <label for="contest_close_date">дата(YYYY-MM-DD HH:MM:SS) закрытия, подведения итогов</label>
  <input type="text" name="contest_close_date" id="contest_close_date" value="<? echo $reg->close_date ?>" />
  <label for="contest_auto_tick">разрешить живой статус (автоматический переход состояния конкурса по достижении контрольного времени события)</label>
  <select name="contest_auto_tick" id="contest_auto_tick">
     <option value="0" <? if($reg->auto_tick==0)echo 'selected="selected"'; ?>>нет</option>
     <option value="1" <? if($reg->auto_tick==1)echo 'selected="selected"'; ?>>да</option>
  </select><br />
  <label for="check_contest-name">проверить имя конкурса</label>
  <input type="submit" name="act" id="check_contest-name" value="check_contest-name" />
  <label for="add_contest">зарегистрировать конкурс</label>
  <input type="submit" name="act" id="add_contest" value="add_contest" />
</form>
<? 
	} // действий нет
?>
</div>
<? include_once 'after.php' ?>