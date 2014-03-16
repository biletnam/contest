<?php
	include_once dirname(__FILE__)."/../config.php";
	include_once SITE_ROOT.'/scripts/mysql.query.php';
	include_once SITE_ROOT.'/core/resource.php';

	define('PAGE_NAME',basename($_SERVER['PHP_SELF']));
	$LOGIN = new stdClass; // аутентификация

// доступно всегда
	$LOGIN->act=@$_REQUEST['login_act'];
	$LOGIN->name=// GET || POST || COOKIE
		isset($_GET['login_name'])
		?$_GET['login_name']
		:(isset($_POST['login_name'])
			?$_POST['login_name']
			:@$_COOKIE['login_name']
		);
	$LOGIN->passw=// GET || POST || COOKIE
		isset($_GET['login_passw'])
		?$_GET['login_passw']
		:(isset($_POST['login_passw'])
			?$_POST['login_passw']
			:@$_COOKIE['login_passw']
		);
	$LOGIN->time_zone=SITE_TZ;
	$LOGIN->valid=false; // статус аутентификации
	global $CORE;
	$LOGIN->debug=$CORE->allow_debug && @$_COOKIE['login_debug']; // отладка, включается режим упрощённой аутентификация

// доступно только после удачной аутентификации
	$LOGIN->id=NULL; 
	$LOGIN->mktime=NULL;

	switch($LOGIN->act){ // действия
		case 'enter': // войти
			if($LOGIN->debug){
				list($LOGIN->id, $LOGIN->mktime, $LOGIN->name_translit, $LOGIN->time_zone)=mysql_query_row("
					SELECT `id`,`mktime`,`name_translit`,`time_zone`
					FROM `user`
					WHERE `name`='".mysql_real_escape_string($LOGIN->name)."'
					LIMIT 1
				");
			}else{
				list($LOGIN->id, $LOGIN->mktime, $LOGIN->name_translit, $LOGIN->time_zone)=mysql_query_row("
					SELECT `id`,`mktime`,`name_translit`,`time_zone`
					FROM `user`
					WHERE `name`='".mysql_real_escape_string($LOGIN->name)."'
						AND `passw`=MD5('".mysql_real_escape_string($LOGIN->passw)."')
					LIMIT 1
				");	
			}
			$LOGIN->valid=(bool)$LOGIN->id;
			if($LOGIN->valid){
				setcookie('login_name',$LOGIN->name,time()+77777777);
				setcookie('login_passw',$LOGIN->passw,time()+77777777);
			}else{
				header("Location: login.php?view=error");
			}
		break;		
		case 'leave': // выйти
			$LOGIN->name=NULL;
			$LOGIN->passw=NULL;
			setcookie('login_name',NULL);
			setcookie('login_passw',NULL);
			$LOGIN->valid=false;
		break;	
		default: // проверка статуса
			if($LOGIN->debug){
				list($LOGIN->id, $LOGIN->mktime, $LOGIN->name_translit, $LOGIN->time_zone)=mysql_query_row("
					SELECT `id`, `mktime`,`name_translit`,`time_zone`
					FROM `user`
					WHERE `name`='".mysql_real_escape_string($LOGIN->name)."'
					LIMIT 1
				");
			}else{
				list($LOGIN->id, $LOGIN->mktime, $LOGIN->name_translit, $LOGIN->time_zone)=mysql_query_row("
					SELECT `id`, `mktime`,`name_translit`,`time_zone`
					FROM `user`
					WHERE `name`='".mysql_real_escape_string($LOGIN->name)."'
						AND `passw`=MD5('".mysql_real_escape_string($LOGIN->passw)."')
					LIMIT 1
				");
			}
			$LOGIN->valid=(bool)$LOGIN->id;
		break;			
	}
	if(!$LOGIN->time_zone)$LOGIN->time_zone=SITE_TZ;
	$LOGIN->date_now=date_format(date_create('now',timezone_open($LOGIN->time_zone)),'Y-m-d H:i:s');
	function post_reload($url=false,$sec=3){
?>
<form id="post_reload_form" name="post_reload_form" method="post" action="<?
	echo ($_GET?'?'.http_build_query($_GET):'');
?>" class="input_form">
<input type="submit" name="reload" value="далее" />
<?
		if(!function_exists('put_data')){
			function put_data($arr=array(),$prefix=false){
				foreach($arr as $key=>$value)
				if(is_array($value)){
					put_data($value,$key);
				}else{
?>
<input type="hidden" name="<? echo ($prefix===false?$key:"{$prefix}[{$key}]") ?>" value="<? echo htmlspecialchars($value, ENT_QUOTES); ?>" style="display:none;" />
<?
				}
			}
		}
		put_data(array_diff_key($_POST,array('act'=>1)));
?>
</form>
<?		
		if($url!==false){
?>
<script type="text/javascript">
	setTimeout("document.getElementById('post_reload_form').submit()", <? echo $sec ?>000);
</script>
<?
		}
	}
	function get_reload($url,$sec=3){
		$sec=($sec===true?3:$sec);
		if($sec){
?>
	<meta http-equiv="refresh" content="<? echo $sec ?>;URL=<? echo $url ?>">
<?
		}
?>
	<a class="input" href="<? echo $url ?>">далее</a>
<?
}
 // функция превода текста с кириллицы в траскрипт
function rus2lat($text) {
	$text = substr($text, 0, 250); //обрезаем текст до приличных размеров(для URL)
	$rus = array('ё','й','ц','у','к','е','н','г','ш','щ','з','х','ъ','ф','ы','в', 'а','п','р','о','л','д','ж','э', 'я','ч','с','м','и','т','ь','б','ю', ' '); //задаем массив русских букв
	$eng = array('yo','y','c','u','k','e','n','g','sh','sch','z','h','_','f','i','v', 'a','p','r','o','l','d','zh','e', 'ya','ch','s','m','i','t','_','b','yu', '_'); //соразмерный массив транслита
	$count = count($rus);
	for($i = 0; $i<$count; $i++) { //временно пережимаем всю кириллицу в WIN-1251, для корректной работы
	$russ[] = iconv('UTF-8', 'CP1251//IGNORE',$rus[$i]); //IGNORE - символы, которых нет в конечной кодировке, будут опущены
	}
	$word = iconv('UTF-8', 'CP1251//IGNORE', $text); //пережимаем текст в WIN-1251
	$word = str_replace($russ, $eng, strtolower($word)); //меняем кириллические символы на символы транслита. strtolower можно убрать, тогда придется добавить большие буквы в массивы $rus и $eng.
	return iconv('CP1251//IGNORE','UTF-8', $word); //возращаем строку в UTF-8
}

function date_pos($start,$end){
	if($start==$end)
		return 0;
	global $LOGIN;
	$now_date = date_format(date_create($LOGIN->date_now),'U');
	$cur_date = date_format(date_create($start),'U');
	$next_date = date_format(date_create($end),'U');
	$pos=($now_date-$cur_date)/($next_date-$cur_date);
	return $pos<0?0:($pos<1?$pos:1);
}

function load_date($date){
	global $LOGIN;
	$d=date_create($date,timezone_open(HOST_TZ)); 
	date_timezone_set($d,timezone_open($LOGIN->time_zone));
	return date_format($d,'Y-m-d H:i:s');
}
function save_date($date){
	global $LOGIN;
	$d=date_create($date,timezone_open($LOGIN->time_zone)); 
	date_timezone_set($d,timezone_open(HOST_TZ));
	return date_format($d,'Y-m-d H:i:s');
}
?>
