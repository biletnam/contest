<?php
	define('SITE_ROOT', dirname(__FILE__));
	// link to browser
	define('CONTENT_LINK', "content");
	define('CONTEST_LINK', CONTENT_LINK."/contest");
	// file system server
	define('CONTENT_ROOT', SITE_ROOT.'/'.CONTENT_LINK);
	define('CONTEST_ROOT', CONTENT_ROOT."/contest");
	define('CORE_CHARSET','UTF-8'); // кодировка для сайта
	define('FS_CHARSET','cp1251'); // кодировка для файловой системы
	define('WORK', "/work");
	define('HOST_TZ',date_default_timezone_get()); // часовой пояс сервера
	define('SITE_TZ','Europe/Moscow'); // часовой пояс сайта
	date_default_timezone_set(SITE_TZ); // часовой пояс для сайта


//var_export(md5(php_uname('n'))); // !Запусти это, чтоб посмотреть что у тебя!

// специальный конфиг файл
require_once( 'config/'.md5(php_uname('n')).'.php' );

list($dblocation,$dbname,$dbuser,$dbpasswd,$allow_debug)=array_values($dbconf);

	$CORE = new stdClass;
	$CORE->version='0.6.13';
	global $page;
	$CORE->title = isset($page)?$page:'Страница без заголовка';
	$CORE->allow_debug=$allow_debug; // разрешить режим отладки ( включает упрощённую авторизацию )

	$link = @mysql_connect($dblocation,$dbuser,$dbpasswd);
	unset($dblocation,$dbuser,$dbpasswd);
	mysql_query("SET NAMES 'utf8'"); /* кодировка для обмена с MySQL */
	mb_internal_encoding(CORE_CHARSET);
	if (!$link) 
	{
		header('Content-Type: text/html; charset=utf-8');
		echo( "<P> В настоящий момент сервер базы данных не доступен, поэтому корректное отображение страницы невозможно. </P>" );
		exit();
	}
	if (!@mysql_select_db($dbname, $link)) 
	{
		header('Content-Type: text/html; charset=utf-8');
		echo( "<P> В настоящий момент база данных не доступна, поэтому корректное отображение страницы невозможно.</P>" );
		exit();
	}
	unset($dblocation,$dbname,$dbuser,$dbpasswd,$allow_debug,$dbconf);
?>
