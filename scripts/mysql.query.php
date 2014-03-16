<?php
// libs/mysql/mysql.query.php
// (c) Yuri Popoff, May 2003, popoff.donetsk.ua
// A set of functions to execute MySQL queries

// LICENSE-VERIFICATION-BEGIN

// LICENSE-VERIFICATION-END

function mysql_query_log($q)
{
  global $mysql_query_counter;
  ++$mysql_query_counter;

  $r=mysql_query($q);
  if(!$r)
    trigger_error(mysql_error().' '.$q,E_USER_ERROR);
  return $r;
}

function mysql_query_array($q)
{
  $r=mysql_query_log($q);
  if(!$r) return false;
  $a=array();
  for($i=0;$i<mysql_num_rows($r);$i++)
    $a[]=mysql_fetch_assoc($r);
  mysql_free_result($r);
  return $a;
}

function mysql_query_assoc($q)
{
  $r=mysql_query_log($q);
  if(!$r) return false;
  if($r===true)
  {
    trigger_error('An update query is passed to mysql_query_row: '.$q);
    return false;
  }
  if(!mysql_num_rows($r))
  {
    mysql_free_result($r);
    return false;
  }
  $f=mysql_fetch_assoc($r);
  mysql_free_result($r);
  return $f;
}

function mysql_query_row($q)
{
  $r=mysql_query_log($q);
  if(!$r) return false;
  if($r===true)
  {
    trigger_error('An update query is passed to mysql_query_row: '.$q);
    return false;
  }
  if(!mysql_num_rows($r))
  {
    mysql_free_result($r);
    return false;
  }
  $f=mysql_fetch_row($r);
  mysql_free_result($r);
  return $f;
}

function mysql_query_single($q,$assoc=true)
{
  $r=mysql_query_log($q);
  if(!$r) return false;
  if($r===true)
  {
    trigger_error('An update query is passed to mysql_query_single: '.$q);
    return false;
  }
  if(!mysql_num_rows($r))
  {
    mysql_free_result($r);
    return false;
  }
  if($assoc&&mysql_num_fields($r)>1)
  {
    $f=mysql_fetch_assoc($r);
    mysql_free_result($r);
    return $f;
  }
  else
  {
    $f=mysql_fetch_row($r);
    mysql_free_result($r);
    if(count($f)>1)
      return $f;
    else
      return $f{0};
  }
}

global $mysql_query_counter;
$mysql_query_counter=0;

?>