<?  $page='Импортировать' ?>
<? include_once 'before.php' ?>
<div class="page">
<pre>
<?
	$topka=mb_convert_encoding(file_get_contents(mb_convert_encoding(dirname(__FILE__).'/top users.txt','cp1251')),"UTF-8","cp1251");
	echo $topka."<br/>";
	if(preg_match_all('/^(\d+) \t([^\t]+) \t/um',$topka,$data,PREG_SET_ORDER)){
		$users = array();
		foreach($data as $k=>$val){
			$users[]=$val[2];
			// --мегарегистрация----
				$v=false; // есть ошибки
				$_REQUEST['act']='add_user';
				$_REQUEST['user_name']=$val[2];
				$_REQUEST['user_passw']='123123';
				$_REQUEST['user_time_zone']='Europe/Moscow';
				include 'core/submit.php';
				if(!process_errors()){
					echo "<p class='response ".(($v=$OUT->result[0]->valid)?'success':'fail')."'>`{$val[2]}` ".($v?"успешно зарегистрированн":"не зарегистрированн, возможно логин уже занят")."</p>";
				}	
			// ---------------------------
		}
		var_export($users);
	}
	else
		echo 'КосяГ не работает';
?>
</pre>
</div>
<? include_once 'after.php' ?>
