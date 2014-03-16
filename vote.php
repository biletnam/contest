<? $page='Голосование' ?>
<? include_once 'before.php' ?>
<div class="page"><?
/*----------------------------------------------------------------------------------------
 Установки
 ----------------------------------------------------------------------------------------*/
$vote = new stdClass;
$vote->act=isset($_REQUEST['act'])?$_REQUEST['act']:false; // действия
$vote->view=isset($_REQUEST['view'])?$_REQUEST['view']:'contest_vote_table_list'; //[about, edit] что показывать
$vote->contest=isset($_REQUEST['contest'])?$_REQUEST['contest']:false; // конкурс
$vote->goto=isset($_REQUEST['goto'])?$_REQUEST['goto']:''; // перейти по адресу

$vote->to=@$_REQUEST['to'];
$vote->score=@$_REQUEST['score'];
$vote->comments=@$_REQUEST['comments'];

// конкурс
$contest = new stdClass;
$contest->id = $vote->contest; // выбранн конкурс
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
	}
}
// дополнения к профилю в рамках конкурса
$LOGIN->in_contest=false; // в конкурсе
$LOGIN->voted_in_contest=false; // проголосовал в конкурсе
$LOGIN->contest_access=false; // права конкурсанта
$LOGIN->role=false; // роль конкурсанта
if($contest->valid && $LOGIN->valid){ // авторизованн
	$_REQUEST['id_user']=$LOGIN->id;
	$_REQUEST['id_contest']=$contest->id;
	$_REQUEST['of']='user_data_for_contest';
	include 'core/about.php';
	if(!process_errors()){
		$LOGIN->in_contest=(bool)count($OUT->result); // пред-установка
	}
	if($LOGIN->in_contest){ // подгружаем данные
		$LOGIN->uid=$OUT->result[0]['uid']; // id участника конкурса
		$LOGIN->pid=$OUT->result[0]['pid']; // id конкурсанта с правами
		$LOGIN->user=$OUT->result[0]['user']; // id пользователя
		$LOGIN->user_name=$OUT->result[0]['user_name']; // имя пользователя
		$LOGIN->team=$OUT->result[0]['team']; // id команды
		$LOGIN->team_name=$OUT->result[0]['team_name']; // имя команды
		$LOGIN->role=$OUT->result[0]['role']; // id роль в команде
		$LOGIN->role_name=$OUT->result[0]['role_name']; // имя роли в команде
		$LOGIN->role_desc=$OUT->result[0]['role_desc']; // описание роли
		$LOGIN->in_contest=(bool)$LOGIN->uid; // в конкурсе
		$LOGIN->contest_access =$LOGIN->in_contest && $LOGIN->pid==$LOGIN->uid; // есть права
		$LOGIN->name_in_contest=$OUT->result[0]['name']; // имя в конкурсе
		$LOGIN->num_vote=$OUT->result[0]['num_vote']; // кол-во голосов
		$LOGIN->voted_in_contest=(bool)$LOGIN->num_vote; // проголосовал
		$LOGIN->work=$OUT->result[0]['work']; // id работы
		$LOGIN->worked=(bool)$LOGIN->work; // работа отправлена
	}
}
if($LOGIN->voted_in_contest && !isset($vote->to)){ // подгружаем голоса
	$_REQUEST['id_user']=$LOGIN->id;
	$_REQUEST['id_contest']=$contest->id;
	$_REQUEST['of']='user_vote_in_contest';
	include 'core/list.php';
	process_errors();
	$vote->to=array();
	$vote->score=array();
	$vote->comments=array();
	foreach($OUT->result as $item){
		$vote->to[]=$item['to'];
		$vote->score[]=$item['value'];
		$vote->comments[]=$item['desc'];
	}
}
?>
<ul class="page_menu">
	<li><span class="header">cписок: </span><a
		href="?view=contest_vote_table_list">таблица голосов</a></li>
	<li><span class="header">cписок: </span><a
		href="?view=contest_vote_statistic_list">статистика голосов</a></li>
		<? if($LOGIN->valid){ ?>
	<li><span class="header">список: </span><a
		href="?view=contest_my_vote_list">мои голоса</a></li>
	<li><span class="header">список: </span><a
		href="?view=contest_vote_for_my_list">голоса за меня</a></li>
		<? } ?>
	<li><span class="header">cписок: </span><a
		href="?view=top_parti_v_parti">топ конкурсантов [по версии участников]</a></li>
</ul>
		<?
		if($LOGIN->valid && $vote->act){ // if действия
			/*----------------------------------------------------------------------------------------
			 Выполнение действий
			 ----------------------------------------------------------------------------------------*/
			switch($vote->act){ // switch действия
				case 'user_vote':
					$_REQUEST['id_contest']=$contest->id;
					include 'core/submit.php';
					if(!process_errors()){
						echo "<p class='response ".(($v=$OUT->result[0]->valid)?'success':'fail')."'>".($v?"Выполнено успешно":"Не выполнено")."</p>";
						if($v)
						$vote->goto='contest.php?view=about&id='.$_REQUEST['id_contest'];
					}
					break;
				default:
					global $MESSAGE;
					?>
<div class="access_denied">
<p><? echo $MESSAGE['action_not_found'] ?></p>
</div>
					<?
			} // switch действия
			if($vote->goto)
			get_reload($vote->goto);
			else
			post_reload();
		}else{ // if действия
			/*----------------------------------------------------------------------------------------
			 Отображение страницы
			 ----------------------------------------------------------------------------------------*/
			switch($vote->view){ // что показывать
				case 'contest_vote_table_list':
					$_REQUEST['of']='contest_vote_table_list';
					include 'core/list.php';
					process_errors();
					?> <span class="header">cписок таблиц голосов</span>
<ul class="list">
<?
foreach($OUT->result as $i=>$item){
	echo "<li><a href='?view=contest_vote_table&contest={$item['id']}'>#{$item['id']}        {$item['name']}</a></i></li>\n";
}
?>
</ul>
<?
break;
case 'contest_vote_table':
	if($contest->state=='close'){ // если известны результаты
		$_REQUEST['id_contest']=$contest->id;
		$_REQUEST['of']='contest_vote_table';
		include 'core/list.php';
		process_errors();
		$table=array();
		foreach($OUT->result as $item){
			if(!isset($table[$item['to']])){
				$table[$item['to']]=
				array(
					'name'=>$item['to'],
					'count'=>0,
					'score'=>0,
					'count_viewer'=>0,
					'score_viewer'=>0,
					'votes'=>array()
				);
			}
			$part =& $table[$item['to']];
			if(false !== strpos($item['from'],'::')){ // is_viewer
				$part['count_viewer']++;
				$part['score_viewer']+=$item['value'];
			}else{
				$part['count']++;
				$part['score']+=$item['value'];
			}
			$part['votes'][]=$item;
			unset($part);
		} // foreach
		function cmp($a, $b){
			return $b['score']-$a['score'];
		}
		usort($table, "cmp");
		?> <span class="header">Результаты голосования <a
	href="contest.php?view=about&id=<? echo $contest->id ?>"><? echo $contest->name ?></a></span>
<br />
<table width="100%" class="tbl">
	<thead>
		<tr>
			<td colspan="3">
			<div align="center">Участник</div>
			</td>
		</tr>
		<tr>
			<td width="200">Голосующий</td>
			<td width="50" align="center">Оценка</td>
			<td>Коментарий</td>
		</tr>
	</thead>
	<tbody>
	<?
	unset($part);
	foreach($table as $part){
		?>
		<tr>
			<td colspan="3" bgcolor="#FFCC33">
			<div align="center"><? echo $part['name'] ?></div>
			</td>
		</tr>
		<?
		foreach($part['votes'] as $i=>$item){
			?>
		<tr <? echo $i%2?'class="odd"':'' ?>>
			<td><? echo $item['from'] ?></td>
			<td><? echo $item['value'] ?></td>
			<td><? echo wordwrap($item['desc'], 64, "<br />\n",1) ?></td>
		</tr>
		<?
		}
		?>
		<tr class="dark">
			<td><i>Баллы участников [ <? echo $part['count'] ?> ] :</i></td>
			<td><? echo $part['score'] ?></td>
			<td>&nbsp;</td>
		</tr>
		<tr class="dark">
			<td><i>Баллы зрителей [ <? echo $part['count_viewer'] ?> ] :</i></td>
			<td><? echo $part['score_viewer'] ?></td>
			<td>&nbsp;</td>
		</tr>
		<?
	}
	?>
	</tbody>
</table>
	<?
	} // если известны результаты
	break;
case 'contest_vote_statistic_list':
	$_REQUEST['of']='contest_vote_table_list';
	include 'core/list.php';
	process_errors();
	?> <span class="header">cписок таблиц со статистикой голосов</span>
<ul class="list">
<?
foreach($OUT->result as $i=>$item){
	echo "<li><a href='?view=contest_vote_statistic&contest={$item['id']}'>#{$item['id']}        {$item['name']}</a></i></li>\n";
}
?>
</ul>
<?
break;
case 'contest_vote_statistic':
	if($contest->state=='close'){ // если известны результаты
		$_REQUEST['id_contest']=$contest->id;
		$_REQUEST['of']='contest_vote_statistic';
		include 'core/list.php';
		process_errors();
		?> <span class="header">статистика голосования в конкурсе <a
	href="contest.php?view=about&id=<? echo $contest->id ?>"><? echo $contest->name ?></a></span><br />
<table width="100%" class="tbl">
	<thead>
		<tr>
			<th scope="col">некто</th>
			<th width="50" scope="col">кол-во его голосов</th>
			<th width="50" scope="col">кол-во голосов участников</th>
			<th width="50" scope="col">баллы</th>
			<th width="50" scope="col">кол-во голосов зрителей</th>
			<th width="50" scope="col">баллы зрителей</th>
		</tr>
	</thead>
	<tbody>
	<?
	foreach($OUT->result as $i=>$item){
		?>
		<tr <? echo $i%2?'class="odd"':'' ?>>
			<td><? echo $item['parti'] ?></td>
			<td><? echo $item['parti_vote_count'] ?></td>
			<td class="dark"><? echo $item['vote_count'] ?></td>
			<td><? echo $item['score'] ?></td>
			<td class="dark"><? echo $item['vote_viewer_count'] ?></td>
			<td><? echo $item['viewer_score'] ?></td>
		</tr>
		<?
	}//foreach
	?>
	</tbody>
</table>
	<?
	}
	break;
case 'contest_my_vote_list':
	if($LOGIN->valid){
		$_REQUEST['id_user']=$LOGIN->id;
		$_REQUEST['of']='my_vote_contest_list';
		include 'core/list.php';
		process_errors();
		?> <span class="header">cписок таблиц с моими голосами в конкурсе</span>
<ul class="list">
<?
foreach($OUT->result as $i=>$item){
	echo "<li><a href='?view=my_vote_list&contest={$item['id']}'>#{$item['id']}        {$item['name']}</a></i></li>\n";
}
?>
</ul>
<?
	}
	break;
case 'my_vote_list': # мои голоса в конкурсе
	if($contest->valid && $LOGIN->valid){ // если известны результаты
		$_REQUEST['id_user']=$LOGIN->id;
		$_REQUEST['id_contest']=$contest->id;
		$_REQUEST['of']='my_vote_list';
		include 'core/list.php';
		process_errors();
		?> <span class="header">голоса <a
	href="<? echo ($LOGIN->team?'team.php?view=about&id='.$LOGIN->team:'user.php?view=about&id='.$LOGIN->user) ?>"><? echo $LOGIN->name_in_contest ?></a>
в конкурсе <a href="contest.php?view=about&id=<? echo $contest->id ?>"><? echo $contest->name ?></a></span><br />
<table width="100%" class="tbl">
	<thead>
		<tr>
			<th width="200" scope="col">кому</th>
			<th width="50" scope="col">баллы</th>
			<th scope="col">комментарий</th>
		</tr>
	</thead>
	<tbody>
	<?
	foreach($OUT->result as $i=>$item){
		?>
		<tr <? echo $i%2?'class="odd"':'' ?>>
			<td><? echo $item['to_name'] ?></td>
			<td><? echo $item['value'] ?></td>
			<td><? echo $item['desc'] ?></td>
		</tr>
		<?
	}//foreach
	?>
	</tbody>
</table>
	<?
	}
	break;
case 'contest_vote_for_my_list':
	if($LOGIN->valid){
		$_REQUEST['id_user']=$LOGIN->id;
		$_REQUEST['of']='vote_for_my_contest_list';
		include 'core/list.php';
		process_errors();
		?> <span class="header">cписок таблиц с голосами за меня в конкурсе</span>
<ul class="list">
<?
foreach($OUT->result as $i=>$item){
	echo "<li><a href='?view=contest_vote_for_my&contest={$item['id']}'>#{$item['id']}        {$item['name']}</a></i></li>\n";
}
?>
</ul>
<?
	}
	break;
case 'contest_vote_for_my':
	if($contest->valid && $LOGIN->valid){ // если известны результаты
		$_REQUEST['id_user']=$LOGIN->id;
		$_REQUEST['id_contest']=$contest->id;
		$_REQUEST['of']='vote_for_my_list';
		include 'core/list.php';
		process_errors();
		?> <span class="header">голоса за <a
	href="<? echo ($LOGIN->team?'team.php?view=about&id='.$LOGIN->team:'user.php?view=about&id='.$LOGIN->user) ?>"><? echo $LOGIN->name_in_contest ?></a>
в конкурсе <a href="contest.php?view=about&id=<? echo $contest->id ?>"><? echo $contest->name ?></a></span><br />
<table width="100%" class="tbl">
	<thead>
		<tr>
			<th width="200" scope="col">от кого</th>
			<th width="50" scope="col">баллы</th>
			<th scope="col">комментарий</th>
		</tr>
	</thead>
	<tbody>
	<?
	foreach($OUT->result as $i=>$item){
		?>
		<tr <? echo $i%2?'class="odd"':'' ?>>
			<td><? echo $item['from'] ?></td>
			<td><? echo $item['value'] ?></td>
			<td><? echo $item['desc'] ?></td>
		</tr>
		<?
	}//foreach
	?>
	</tbody>
</table>
	<?
	}
	break;
case 'top_parti_v_parti':
	$_REQUEST['of']='top_parti_v_parti';
	include 'core/list.php';
	process_errors();
	global $OUT;
	$top=$OUT->result;
	unset($OUT);

	$_REQUEST['of']='each_contest_stat_v_parti';
	include 'core/list.php';
	process_errors();
	$contests=array();
	foreach($OUT->result as $item){
		if(!isset($contests[$item['contest']])){
			$contests[$item['contest']]=array();
		}
		foreach($top as $key=>&$parti){
			if($parti['user']==$item['user']
			&& $parti['team']==$item['team']){
				if(!isset($parti['contests']))
				$parti['contests']=array();
				$p = $item;
				$contests[$item['contest']][]=&$p;
				$parti['contests'][]=&$p;
				$p['part_key']=$key;
				$parti[1]=0;
				$parti[2]=0;
				$parti[3]=0;
				$parti['score']=0;
				unset($p);
				break;
			}
		}
		unset($parti);
	}
	unset($item);
	unset($OUT);
	foreach($contests as &$citem){
		$place=0;
		$score=0x7FFFFFFF;
		foreach($citem as &$item){
			if($item['score']<$score){
				$score=$item['score'];
				$place++;
			}
			$item['place']=$place;
			if($item['place']<4){
				$top[$item['part_key']][$item['place']]++;
			}
			switch ($place) {
				case 1:
					$top[$item['part_key']]['score']+=5;
				break;
				case 2:
					$top[$item['part_key']]['score']+=3;
				break;
				case 3:
					$top[$item['part_key']]['score']+=2;
				break;
				default:
					$top[$item['part_key']]['score']+=1;
				break;
			}
		}
		unset($item);
	}
	unset($citem);
	$place=0;
	$score=0x7FFFFFFF;
	function cmp($a,$b){
		return $b['score']-$a['score'];
	}
	usort($top,'cmp');
	foreach($top as &$parti){
		if($parti['score']<$score){
			$score=$parti['score'];
			$place++;
		}
		$parti['place']=$place;
	}
	unset($parti);
	//var_export($top);
	?> <span class="header">ТОП участников</span> <br />
<table width="100%" class="tbl">
	<thead>
		<tr>
			<th width="50" scope="col">#</th>
			<th width="100" scope="col">Участник</th>
			<th scope="col">Конкурсы (места)</th>
			<th width="50" scope="col">1st</th>
			<th width="50" scope="col">2nd</th>
			<th width="50" scope="col">3rd</th>
			<th width="50" scope="col">Участий</th>
			<th width="50" scope="col">~</th>
		</tr>
	</thead>
	<tbody>
	<?
	foreach($top as $i=>$item){
		?>
		<tr <? echo $i%2?'class="odd"':'' ?>>
			<td><? echo $item['place'] ?></td>
			<td><? 
				if(isset($item['team']))
echo "<a href='team.php?view=about&id={$item['team']}'>{$item['to']}</a>";
				else 
echo "<a href='user.php?view=about&id={$item['user']}'>{$item['to']}</a>";
			?></td>
			<td><? foreach($item['contests'] as $v){
echo "<a href='contest.php?view=about&id={$v['contest']}' title='{$v['contest_name']}'>{$v['contest']}({$v['place']}) </a>";
			} ?></td>
			<td><? echo $item[1] ?></td>
			<td><? echo $item[2] ?></td>
			<td><? echo $item[3] ?></td>
			<td><? echo $item['contest_count'] ?></td>
			<td><? echo $item['score'] ?></td>
		</tr>
		<?
	}//foreach
	?>
	</tbody>
</table>
	<?
	break;
case 'edit':
	if(!$LOGIN->valid){
		?>
<div class="access_denied">
<p><? echo $MESSAGE['user_access'] ?></p>
</div>
		<?
	}else if($contest->valid){
		?>
<p>Расставьте баллы для участников конкурса. Баллы выставляютя от 1 до
(кол-ва участников). Чем выше балл : тем выше в рейтинге окажется
участник</p>
<form action="" method="post" name="form_vote" class="input_form">
<table width="100%">
<?
$_REQUEST['id']=$vote->contest;
$_REQUEST['of']='contest_participation';
include 'core/list.php';
process_errors();
@reset($vote->score);
@reset($vote->comments);
foreach($OUT->result as $item)
if($item['id']!=$LOGIN->pid){
	?>
	<tr>
		<td><input type="hidden" style="display: none" name="to[]"
			value="<? echo $item['id'] ?>" />
		<div>
		<div style="float: left; width: 300px;"><span class="page_menu"><? echo $item['name'] ?></span>
		</div>
		<div style="float: left; margin-left: 5px; width: 50px;"><label>балл</label>
		</div>
		<div style="margin-left: 355px;"><input type="text" name="score[]"
			value="<? echo @current($vote->score) ?>" /></div>
		</div>
		<div style="clear: left"><label>коментарий</label> <textarea
			name="comments[]"><? echo @current($vote->comments) ?></textarea></div>
		<div style="height: 20px;"></div>
		</td>
	</tr>
	<?
	@next($vote->score);
	@next($vote->comments);
};
?>
</table>
<input id="vote" type="submit" name="act" value="user_vote" /></form>
<?
	}else{
		?>
<div class="access_denied">
<p><? echo $MESSAGE['contest_not_found'] ?></p>
</div>
		<?
	}
	break;
default:
	?>
<div class="access_denied">
<p><? echo $MESSAGE['bug'] ?></p>
</div>
	<?
			} // switch что показывать
		} // else действия
		?></div>
<? include_once 'after.php' ?>