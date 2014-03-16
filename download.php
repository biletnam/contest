<?  $page='скачивание' ?>
<? include_once 'before.php' ?>
<div class="page">
<?
$download = new stdClass;
$download->act = isset($_REQUEST['act'])?$_REQUEST['act']:'view';

// конкурс
$contest = new stdClass;
$contest->id = isset($_REQUEST['contest'])?$_REQUEST['contest']:false; // выбранн конкурс
$contest->valid=false; // существует ли
if($contest->id){ // выбранн конкурс
	$_REQUEST['id']=$contest->id;
	$_REQUEST['of']='contest';
	include 'core/about.php';
	global $OUT;
	if(!process_errors()){
		$contest->valid=(bool)count($OUT->result);
	}
	if($contest->valid){ // подгружаем данные
		$contest->name=$OUT->result[0]['name'];
		$contest->state=$OUT->result[0]['state'];
		$contest->content=$OUT->result[0]['content'];
		$contest->down_count=$OUT->result[0]['down_count'];
	}
}
switch($download->act){
	case 'view': // обзор закачек, простой переход на эту страницу
	break;
	case 'contest_works':
		$_REQUEST['act']='contest_download_work';
		$_REQUEST['id_contest']=$contest->id;
		include 'core/submit.php';
		process_errors();
?>
<div style="text-align:center; padding-top:200px;">
уже <span style="color:#009900;"><? echo $contest->down_count ?></span> скачиваний<br />
<?
		echo "<a href='{$contest->content}'>скачать конкурсную работу</a>";
?>
</div>
<?
	break;
}
?>
</div>
<? include_once 'after.php' ?>
