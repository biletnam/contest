<? $page='time_zone' ?>
<? include_once 'before.php' ?>
<?
	//var_export(HOST_TZ);
	//var_export(date_default_timezone_get());
	//$date=date_create();
	//date_timezone_set();
	$str=mysql_query_single("
		SELECT NOW()
	");
	echo 'SQL time:';var_export($str); echo '<br />';
	$date=date_create($str,timezone_open(HOST_TZ));
	echo '(host time)SQL time:';var_export(date_format($date,'c')); echo '<br />';
	
	date_timezone_set($date,timezone_open(SITE_TZ));
	echo '(site time)SQL time:';var_export(date_format($date,'c')); echo '<br />';

	$tzlist=timezone_identifiers_list();
?>
<select>
<OPTGROUP LABEL="*">
<option value="*">по умолчанию</option>
<?
	$old=NULL;
	foreach($tzlist as $tz)
	if(($new=explode('/',$tz)) && $old!==($new=$new[0])){
?>
</OPTGROUP>
<OPTGROUP LABEL="<? echo $new ?>">
<option value="<? echo $tz ?>"><? echo $tz ?></option>	
<?
		$old=$new;
	}else{
?>
<option value="<? echo $tz ?>"><? echo $tz ?></option>	
<?
	}
?>
</select>
<? include_once 'after.php' ?>
