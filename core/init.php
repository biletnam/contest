<?
	include_once dirname(__FILE__).'/../config.php';
	include_once SITE_ROOT.'/scripts/mysql.query.php';
	include_once SITE_ROOT.'/core/engine.php';	

	$OUT = new stdClass;
	$OUT->error=0;
	$OUT->errorcode=array();
	$OUT->result=array();

	if(!function_exists('response')){
		function response(){
			global $OUT;
			$OUT->error=(bool)count($OUT->errorcode);
			$type=isset($_REQUEST['type'])?$_REQUEST['type']:'none';
			switch($type){
				case 'none':
					break;
				case 'htm':
					echo "<pre>".var_export($OUT,1)."</pre>";
					break;
				case 'json':
					echo $_REQUEST['callback'].'('.json_encode($OUT).')';
					break;
				default:
					break;
			}
			restore_error_handler();
			restore_exception_handler();
		}
	}
	/* Обработчик ошибок */
	if(!function_exists('myErrorHandler')){
		function myErrorHandler($errno, $errstr, $errfile, $errline)
		{
			global $OUT;
			$OUT->error=1;
			$OUT->errorcode[]=array(
				'type'=>'PHP error',
				'errno'=>$errno,
				'errstr'=>$errstr,
				'errfile'=>$errfile,
				'errline'=>$errline
			);
			if($errno & E_ERROR)
				response();
			return true;
		}
	}
	set_error_handler("myErrorHandler");
		
	/* Обработчик исключений */
	if(!function_exists('exception_handler')){
		function exception_handler($exception) {
			global $OUT;
			$OUT->error=1;
			$OUT->errorcode[]=array(
				'type'=>'PHP exception',
				'message'=>$exception->getMessage()
			);
			response();
		}
	}
	set_exception_handler('exception_handler');

	if(!function_exists('process_errors')){
		function process_errors() {
			global $OUT;
			if(!$OUT->error)
				return false;
			foreach($OUT->errorcode as $item){
				echo "<p class='response error'>";
				foreach($item as $key=>$value){
					echo "<b>$key</b>: $value<br />\n";
				}
				echo "</p>\n";
			}
			return true;
		}	
	}
?>
