<?php
include dirname(__FILE__)."/init.php";
try{
	switch($act=$_REQUEST['act']){
		case 'contest':
		mysql_query_log("
			#contest -> tick()
			# выполняет закулисные действия со статусом конкурса, если пришла пора
			UPDATE IGNORE `contest`
			SET `state`=CASE
				WHEN (@t:=NOW())>`close_date` THEN 'close'
				WHEN @t>`vote_date` THEN 'vote'
				WHEN @t>`wait_date` THEN 'wait'
				WHEN @t>`open_date` THEN 'open'
				WHEN 1 THEN 'nominate'
				END
			WHERE `auto_tick`
		");
		break;
		default:
			$OUT->errorcode[]=array_merge($INVALID['unknown_action'],array('действие'=>"`$act` -  неизвестно",'file:'=>'core/tick.php'));
	}
	unset($act);
	response();
}catch (Exception $e) {
	exception_handler($e);
}
?>