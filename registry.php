<?  $page='Регистрация' ?>
<? include_once 'before.php' ?>
<div class="page">
<?
/*----------------------------------------------------------------------------------------
									Установки
----------------------------------------------------------------------------------------*/
	$reg =& new stdClass;
	$reg->act=isset($_REQUEST['act'])?$_REQUEST['act']:false; // действия
	$reg->goto=isset($_REQUEST['goto'])?$_REQUEST['goto']:''; // перейти по адресу

	$user =& new stdClass;
	$user->login = isset($_REQUEST['user_name'])?$_REQUEST['user_name']:$LOGIN->name;
	$user->passw = isset($_REQUEST['user_passw'])?$_REQUEST['user_passw']:$LOGIN->passw;
	$user->time_zone = isset($_REQUEST['time_zone'])?$_REQUEST['time_zone']:SITE_TZ;

	if($LOGIN->valid){ // авторизованн
?>
	<div class="access_denied"><p>Недоступно, вы уже зарегистрированы</p></div>
<?
	}else if( $reg->act ){ // действия
		$v=false; // есть ошибки
		switch($_REQUEST['act']){ // выбранное действие
			case 'check_login':
				include 'core/check.php';
				if(!process_errors()){
					echo "<p class='response ".(($v=$OUT->result[0]->valid)?'success':'fail')."'>".($v?"логин не занят":"логин занят")."</p>";
				}
			break;
			case 'add_user':
				include 'core/submit.php';
				if(!process_errors()){
					echo "<p class='response ".(($v=$OUT->result[0]->valid)?'success':'fail')."'>".($v?"успешно зарегистрированн":"не зарегистрированн, возможно логин уже занят")."</p>";
					if($v)
						$reg->goto='login.php';
				}
			break;
		} // выбранное действие 
		if($reg->goto)
			get_reload($reg->goto);
		else
			post_reload($v);
	}else{ // действий нет
?>
<form id="form1" name="form1" method="post" action="" class="input_form">
  <label for="user_name">логин</label>
  <input type="text" name="user_name" id="user_name" value="<? echo $user->login ?>"/>
  <label for="user_passw">пароль</label>
  <input type="password" name="user_passw" id="user_passw" value="<? echo $user->passw; ?>"/>
  <label for="act">проверить логин</label>
  <input type="submit" name="act" id="check_login" value="check_login" />
  <label for="time_zone">часовой пояс</label>
  <select name="user_time_zone" id="time_zone">
  	<OPTGROUP LABEL="*">
  	<option value="*">по умолчанию</option>
<?
	$tzlist=timezone_identifiers_list();
	$old=NULL;
	foreach($tzlist as $tz){
		if(($new=explode('/',$tz)) && $old!==($new=$new[0])){
?>
    </OPTGROUP>
    <OPTGROUP LABEL="<? echo $new ?>">
<?
			$old=$new;
		}
?>
    <option value="<? echo $tz ?>" <? echo ($user->time_zone==$tz?'selected="selected"':'') ?>><? echo $tz ?></option>	
<?
	}
?>
  </select><br />
  <label for="add_user">зарегистрироваться</label>
  <input type="submit" name="act" id="add_user" value="add_user" />
</form>
<? 
	} // действий нет
?>
</div>
<? include_once 'after.php' ?>
