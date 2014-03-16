<?  $page='Вход на сайт' ?>
<? include_once 'before.php' ?>
<div class="page" align="center" style="padding-top:50px">
<?
	$login =& new stdClass;
	$login->view=isset($_REQUEST['view'])?$_REQUEST['view']:'form'; // что показывать
	switch($login->view){
		case 'error':
?>
<p class="response fail">Ошибка при вводе логина и пароля</p>
<?
		break;
		case 'form':
		break;
		default:
?>
<div class="access_denied"><p><? echo $MESSAGE['bug'] ?></p></div>		
<?
	}	
?>
	<div id="login">
  <form id="form_login" name="form_login" method="post" action="login.php">
<?	
	if($LOGIN->debug){
?>
	<label for="login_name">пользователь: </label>
	<select name="login_name" id="login_name">
		<option></option>
<?
		$_REQUEST['of']='user';
		include "core/list.php";
		if(!process_errors()){
			foreach($OUT->result as $item){
				echo "<option value='{$item['name']}' ".($LOGIN->id==$item['id']?"selected":"").">{$item['name']}</option>";
			}
		};	
?>
	</select><br />
<?
	}
	if($LOGIN->valid){	
		if(!$LOGIN->debug){ // без отладки
			echo "<b>".$LOGIN->name."</b>";
		} // без отладки
	}else{
		if(!$LOGIN->debug){	// без отладки
?>
	<label for="login_name">логин</label>
	<input type="text" name="login_name" id="login_name" value="<? echo $LOGIN->name; ?>"/><br />
	<label for="login_passw">пароль</label>
	<input type="password" name="login_passw" id="login_passw" value="<? echo $LOGIN->passw; ?>"/><br />
<?		
		} // без отладки
?>
	<label for="login_act_enter">войти</label>
	<input type="submit" name="login_act" id="login_act_enter" value="enter" />
	<a href="registry.php">зарегистрироваться</a><br />
<? } ?>
	<label for="login_act_leave">выйти</label>
	<input type="submit" name="login_act" id="login_act_leave" value="leave" /><br />
<? if($CORE->allow_debug){ ?>
	<label for="debug">режим отладки</label>
	<input type="checkbox" class="debug"/><br />
<? } ?>
	</form>
</div>
</div>
<? include_once 'after.php' ?>
