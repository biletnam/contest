<?php
	$units=array('about.php','check.php','list.php','submit.php','tick.php');
	foreach($units as $unit){
?>
<h2><?php echo $unit ?></h2>
<ol>
<?php		
		$content=file_get_contents($unit);
		if(preg_match_all("/(?<=case ')([\w_-]+)/m",$content,$data,PREG_SET_ORDER))
		foreach($data as $k=>$val){
?>
<li>
<?php
			echo $val[1];
?>
</li>
<?php
		}
?>
</ol>
<?php
	}
?>