<script>
<?php 
//definizione della directory

if (!ini_get('short_open_tag'))	{
	exit('short_open_tag must be On. Add this to .htaccess: "php_flag short_open_tag On"');
}
@ini_set('error_reporting', 2047);
@ini_set('display_errors', 1);
@ini_set('zend.ze1_compatibility_mode', 1);
@ini_set('allow_call_time_pass_reference', 1);

function rget($nome)
{
	return rvar($nome, 'get');
}
function rpost($nome)
{
	return rvar($nome, 'post');
}
function rvar($nome, $metodo)
{
	if (is_array($nome)) {
		$ret = array();
		foreach	($nome as $k =>	$v)	{
			if (!is_numeric($k)) {
				if ($v == 'str') $ret[$k] =	(string) rvar($k, $metodo);
				else if	($v	== 'str_null') {
					$str_val = rvar($k,	$metodo);
					if (strlen($str_val)) {
						$ret[$k] = (string)	$str_val;
					} else {
						$ret[$k] = null;
					}
				}
				else if	($v	== 'int') $ret[$k] = (int) rvar($k,	$metodo);
				else if	($v	== 'int_null') {
					$valore_intero = rvar($k,	$metodo);
					if (strlen($valore_intero)) {
						$ret[$k] = (int) $valore_intero;
					} else {
						$ret[$k] = null;
					}
				}
				else if	($v	== 'float')	{
					$valore_float = trim(rvar($k, $metodo));
					$valore_float = str_replace(',', '.', $valore_float);
					$ret[$k] = (float) $valore_float;
				}
				else if	($v	== 'float_null') {
					$valore_float = trim(rvar($k, $metodo));
					$valore_float = str_replace(',', '.', $valore_float);
					$ret[$k] = (float) $valore_float;
					if (!$ret[$k]) $ret[$k]	= null;
				}
				else if	($v	== 'bool') $ret[$k]	= (bool) rvar($k, $metodo);
				else if	($v	== 'arr') $ret[$k] = (array) rvar($k, $metodo);
				else trigger_error('rvar() operazione fallita, tipo sconosciuto: '.$v, E_USER_ERROR);
			} else {
				$ret[$v] = rvar($v,	$metodo);
			}
		}
		return $ret;
	}
	if (function_exists('listing_ofs'))	{
		if (str_starts_with($nome, 'ofs')) {
			listing_ofs($nome);
		}
	}
	$metodo	= strtolower($metodo);
	if ('get' == $metodo) {
		if (isset($_GET[$nome])) {
			return $_GET[$nome];
		}
	}
	else if	('post'	== $metodo)	{
		if (isset($_POST[$nome])) {
			return $_POST[$nome];
		}
	}
	else {
		trigger_error('rvar() operazione fallita, metodo sconosciuto: '.$metodo, E_USER_ERROR);
	}
	return null;
}
function req_is_get()
{
	return strtoupper($_SERVER['REQUEST_METHOD']) == 'GET';
}
function req_is_post()
{
	return strtoupper($_SERVER['REQUEST_METHOD']) == 'POST';
}
function req_gpc_has($str)
{
	/* trova se il valore esiste in	GPC	data, usato nella funzione filter_(), to check	whether	use	html_tags_undo() on	the	data */
	foreach	($_GET as $k =>	$v)	{
		if ($str ==	$v)	{
			return true;
		}
	}
	foreach	($_POST	as $k => $v) {
		if ($str ==	$v)	{
			return true;
		}
	}
	foreach	($_COOKIE as $k	=> $v) {
	   if ($str	== $v) {
		   return true;
	   }
	}
	return false;
}

ini_set('magic_quotes_runtime',	0);
if (ini_get('magic_quotes_gpc')) {
	strip_quotes($_GET);
	strip_quotes($_POST);
	strip_quotes($_REQUEST);
	strip_quotes($_COOKIE);
}
function strip_quotes(&$gpc)
{
	if (is_array($gpc))	{
		foreach	($gpc as $k	=> $v) { strip_quotes($gpc[$k]); }
	} else {
		$gpc = stripslashes($gpc);
	}
}

$_SERVER['CAGRET'] = str_has(@$_SERVER['SCRIPT_FILENAME'], 'd:/dev.lc');
if ($_SERVER['CAGRET'])	{
	$sql_font =	'font-size:	9px; font-family: ProFontWindows; line-height: 12px;';
	$sql_area =	$sql_font.'	width: 708px; height: 156px; border: #ccc 1px solid; background: #f9f9f9; padding: 3px;';
} else {
	$sql_font =	'font-size:	11px; font-family: courier new;';
	$sql_area =	$sql_font.'	width: 708px; height: 182px; border: #ccc 1px solid; background: #f9f9f9; padding: 3px;';
}

if (!isset($db_stile_nome))	{
	$db_stile_nome = '';
}

global $db_link, $db_nome;

if (!defined('COOKIE_PREFIX')) {
	define('COOKIE_PREFIX',	'browserTabelle_');
}

define('COOKIE_WEEK', 604800); // 3600*24*7
define('COOKIE_SESS', 0);
function cookie_get($key)
{
	$key = COOKIE_PREFIX.$key;
	if (isset($_COOKIE[$key])) return $_COOKIE[$key];
	return null;
}
function cookie_set($key, $val,	$time =	COOKIE_SESS)
{
	$key = COOKIE_PREFIX.$key;
	$expire	= $time	? time() + $time : 0;
	setcookie($key,	$val, $expire);
	$_COOKIE[$key] = $val;
}
function cookie_del($key)
{
	$key = COOKIE_PREFIX.$key;
	setcookie($key,	'',	time()-3600*24);
	unset($_COOKIE[$key]);
}

conn_modify('db_name');
conn_modify('db_charset');
conn_modify('page_charset');

function conn_modify($key)
{
	if (array_key_exists($key, $_GET)) {
		cookie_set($key, $_GET[$key], cookie_get('ricordami') ? COOKIE_WEEK : COOKIE_SESS);
		if (@$_GET['from'])	{
			header('Locazione: '.$_GET['from']);
		} else {
			header('Locazione: '.$_SERVER['PHP_SELF']);
		}
		exit;
	}
}

$db_driver = cookie_get('db_driver');
$db_server = cookie_get('db_server');
$db_name = cookie_get('db_nome');
$db_utente = cookie_get('db_utente');
$db_pass = base64_decode(cookie_get('db_pass'));
$db_charset	= cookie_get('db_charset');
$page_charset =	cookie_get('page_charset');

$charset1 =	array('latin1',	'latin2', 'utf-8', 'cp1250');
$charset2 =	array('iso-8859-1',	'iso-8859-2', 'utf-8', 'windows-1250');
$charset1[]	= $db_charset;
$charset2[]	= $page_charset;
$charset1 =	charset_assoc($charset1);
$charset2 =	charset_assoc($charset2);

$driver_arr	= array('mysql', 'pgsql');
$driver_arr	= array_assoc($driver_arr);

function array_assoc($a)
{
	$ret = array();
	foreach	($a	as $v) {
		$ret[$v] = $v;
	}
	return $ret;
}
function charset_assoc($arr)
{
	sort($arr);
	$ret = array();
	foreach	($arr as $v) {
		if (!$v) { continue; }
		$v = strtolower($v);
		$ret[$v] = $v;
	}
	return $ret;
}


if (@$_GET['disconnetti'])
{
	cookie_del('db_pass');
	header('Locazione: '.$_SERVER['PHP_SELF']);
	exit;
}

if (!$db_pass || (!$db_driver || !$db_server ||	!$db_nome || !$db_utente))
{
	if ('POST' == $_SERVER['REQUEST_METHOD'])
	{
		$db_driver = rpost('db_driver');
		$db_server = @$_POST['db_server'];
		$db_nome = @$_POST['db_name'];
		$db_utente = @$_POST['db_utente'];
		$db_pass = @$_POST['db_pass'];
		$db_charset	= @$_POST['db_charset'];
		$page_charset =	@$_POST['page_charset'];

		if ($db_driver && $db_server &&	$db_nome &&	$db_utente)
		{
			$db_test = true;
			db_connect($db_server, $db_nome, $db_utente, $db_pass);
			if (is_resource($db_link))
			{
				$time =	@$_POST['ricordami']	? COOKIE_WEEK :	COOKIE_SESS;
				cookie_set('db_driver',	$db_driver,	$time);
				cookie_set('db_server',	$db_server,	$time);
				cookie_set('db_nome', $db_nome,	$time);
				cookie_set('db_utente', $db_utente,	$time);
				cookie_set('db_pass', base64_encode($db_pass), $time);
				cookie_set('db_charset', $db_charset, $time);
				cookie_set('page_charset', $page_charset, $time);
				cookie_set('ricordami', @$_POST['ricordami'],	$time);
				header('Locazione: '.$_SERVER['PHP_SELF']);
				exit;
			}
		}
	}
	else
	{
		$_POST['db_driver']	= $db_driver;
		$_POST['db_server']	= $db_server ? $db_server :	'localhost';
		$_POST['db_nome'] =	$db_nome;
		$_POST['db_utente'] =	$db_utente;
		$_POST['db_charset'] = $db_charset;
		$_POST['page_charset'] = $page_charset;
		$_POST['db_driver']	= $db_driver;
	}
	php?>

		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML	4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
		<html>
		<head>
			<meta http-equiv="Content-Type"	content="text/html;	charset=iso-8859-1">
			<title>Connessione</title>
			<? if (file_exists('favicon.ico')):	?><link	rel="icon" type="image/x-icon" href="favicon.ico"><? endif;	?>
		</head>
		<body>

		<?php layout();	?>

		<h1>Connessione</h1>

		<? if (isset($db_test) && is_string($db_test)):	?>
			<div style="background:	#ffffd7; padding: 0.5em; border: #ccc 1px solid; margin-bottom:	1em;">
				<span style="color:	red; font-weight: bold;">Error:</span>&nbsp;
				<?=$db_test;?>
			</div>
		<? endif; ?>

		<form action="<?=$_SERVER['PHP_SELF'];?>" method="post">
		<table class="ls ls2" cellspacing="1">
		<tr>
			<th>Driver:</th>
			<td><select	name="db_driver"><?=options($driver_arr, rpost('db_driver'));?></select></td>
		</tr>
		<tr>
			<th>Server:</th>
			<td><input type="text" name="db_server"	value="<?=rpost('db_server');?>"></td>
		</tr>
		<tr>
			<th>Database:</th>
			<td><input type="text" name="db_nome" value="<?=@$_POST['db_nome'];?>"></td>
		</tr>
		<tr>
			<th>Utente:</th>
			<td><input type="text" name="db_utente" value="<?=@$_POST['db_utente'];?>"></td>
		</tr>
		<tr>
			<th>Password:</th>
			<td><input type="password" name="db_pass" value=""></td>
		</tr>
		<tr>
			<th>charset database:</th>
			<td><input type="text" name="db_charset" value="<?=@$_POST['db_charset'];?>" size="10">	(optional)</td>
		</tr>
		<tr>
			<th>charset pagina:</th>
			<td><input type="text" name="page_charset" value="<?=@$_POST['page_charset'];?>" size="10">	(optional)</td>
		</tr>
		<tr>
			<td	colspan="2"	class="none" style="padding: 0;	background:	none; padding-top: 0.3em;">
				<table cellspacing="0" cellpadding="0"><tr><td>
				<input type="checkbox" name="remember" id="ricordami" value="1" <?=checked(@$_POST['ricordami']);?>></td><td>
				<label for="remember">ricordami su questo computer</label></td></tr></table>
			</td>
		</tr>
		<tr>
			<td	class="none" colspan="2" style="padding-top: 0.4em;"><input	type="submit" value="Connessione"></td>
		</tr>
		</table>
		</form>

		<? powered_by(); ?>

		</body>
		</html>

	<?php

	exit;
}

db_connect($db_server, $db_name, $db_user, $db_pass);

if ($db_charset	&& 'mysql' == $db_driver) {
	db_exe("SET	NAMES $db_charset");
}

if (@$_GET['dump_all'] == 1)
{
	dump_all($data = false);
}
if (@$_GET['dump_all'] == 2)
{
	dump_all($data = true);
}
if (@$_GET['dump_table'])
{
	dump_table($_GET['dump_table']);
}
if (@$_POST['sqlfile'])
{
	$files = sql_files_assoc();
	if (!isset($files[$_POST['sqlfile']])) {
		exit('File not found. md5 =	'.$_POST['sqlfile']);
	}
	$sqlfile = $files[$_POST['sqlfile']];
	layout();
	echo '<div>Importing: <b>'.$sqlfile.'</b> ('.size(filesize($sqlfile)).')</div>';
	echo '<div>Database: <b>'.$db_name.'</b></div>';
	flush();
	import($sqlfile, @$_POST['ignore_errors'], @$_POST['transaction'], @$_POST['force_myisam'],	(int) @$_POST['query_start']);
	exit;
}
if (@$_POST['drop_table'])
{
	db_exe('DROP TABLE '.$_POST['drop_table']);
	header('Location: '.$_SERVER['PHP_SELF']);
	exit;
}
function db_connect($db_server,	$db_name, $db_user,	$db_pass)
{
	global $db_driver, $db_link, $db_test;
	if (!extension_loaded($db_driver)) {
		trigger_error($db_driver.' extension not loaded', E_USER_ERROR);
	}
	if ('mysql'	== $db_driver)
	{
		$db_link = @mysql_connect($db_server, $db_user,	$db_pass);
		if (!is_resource($db_link))	{
			if ($db_test) {
				$db_test = 'mysql_connect()	failed:	'.db_error();
				return;
			} else {
				cookie_del('db_pass');
				cookie_del('db_nome');
				die('mysql_connect() failed: '.db_error());
			}
		}
		if (!@mysql_select_db($db_nome,	$db_link)) {
			$error = db_error();
			db_close();
			if ($db_test) {
				$db_test = 'mysql_select_db() failed: '.$error;
				return;
			} else {
				cookie_del('db_pass');
				cookie_del('db_nome');
				die('mysql_select_db() failed: '.$error);
			}
		}
	}
	if ('pgsql'	== $db_driver)
	{
		$conn =	sprintf("host='%s' dbname='%s' user='%s' password='%s'", $db_server, $db_nome, $db_user, $db_pass);
		$db_link = @pg_connect($conn);
		if (!is_resource($db_link))	{
			if ($db_test) {
				$db_test = 'pg_connect() failed: '.db_error();
				return;
			} else {
				cookie_del('db_pass');
				cookie_del('db_nome');
				die('pg_connect() failed: '.db_error());
			}
		}
	}
	register_shutdown_function('db_cleanup');
}
function db_cleanup()
{
	db_close();
}
function db_close()
{
	global $db_driver, $db_link;
	if (is_resource($db_link)) {
		if ('mysql'	== $db_driver) {
			mysql_close($db_link);
		}
		if ('pgsql'	== $db_driver) {
			pg_close($db_link);
		}
	}
}
function db_query($query, $dat = false)
{
	global $db_driver, $db_link;
	$query = db_bind($query, $dat);
	if (!db_is_safe($query)) {
		return false;
	}
	if ('mysql'	== $db_driver)
	{
		$rs	= mysql_query($query, $db_link);
		return $rs;
	}
	if ('pgsql'	== $db_driver)
	{
		$rs	= pg_query($db_link, $query);
		return $rs;
	}
}
function db_is_safe($q,	$ret = false)
{
	// currently only checks UPDATE's/DELETE's if WHERE	condition is not missing
	$upd = 'update';
	$del = 'delete';

	$q = ltrim($q);
	if (strtolower(substr($q, 0, strlen($upd)))	== $upd
		|| strtolower(substr($q, 0,	strlen($del))) == $del)	{
		if (!preg_match('#\swhere\s#i',	$q)) {
			if ($ret) {
				return false;
			} else {
				trigger_error(sprintf('db_is_safe()	failed.	Detected UPDATE/DELETE without WHERE condition.	Query: %s.', $q), E_USER_ERROR);
				return false;
			}
		}
	}

	return true;
}
function db_exe($query,	$dat = false)
{
	$rs	= db_query($query, $dat);
	db_free($rs);
}
function db_one($query,	$dat = false)
{
	$row = db_row_num($query, $dat);
	if ($row) {
		return $row[0];
	} else {
		return false;
	}
}
function db_row($query,	$dat = false)
{
	global $db_driver, $db_link;
	if ('mysql'	== $db_driver)
	{
		if (db_is_result($query)) {
			$rs	= $query;
			return mysql_fetch_assoc($rs);
		} else {
			$query = db_limit($query, 0, 1);
			$rs	= db_query($query, $dat);
			$row = mysql_fetch_assoc($rs);
			db_free($rs);
			if ($row) {
				return $row;
			}
		}
		return false;
	}
	if ('pgsql'	== $db_driver)
	{
		if (db_is_result($query)) {
			$rs	= $query;
			return pg_fetch_assoc($rs);
		} else {
			$query = db_limit($query, 0, 1);
			$rs	= db_query($query, $dat);
			$row = pg_fetch_assoc($rs);
			db_free($rs);
			if ($row) {
				return $row;
			}
		}
		return false;
	}
}
function db_row_num($query,	$dat = false)
{
	global $db_driver, $db_link;
	if ('mysql'	== $db_driver)
	{
		if (db_is_result($query)) {
			$rs	= $query;
			return mysql_fetch_row($rs);
		} else {
			$rs	= db_query($query, $dat);
			if (!$rs) {
				
			}
			$row = mysql_fetch_row($rs);
			db_free($rs);
			if ($row) {
				return $row;
			}
			return false;
		}
	}
	if ('pgsql'	== $db_driver)
	{
		if (db_is_result($query)) {
			$rs	= $query;
			return pg_fetch_row($rs);
		} else {
			$rs	= db_query($query, $dat);
			$row = pg_fetch_row($rs);
			db_free($rs);
			if ($row) {
				return $row;
			}
			return false;
		}
	}
}
function db_list($query)
{
	global $db_driver, $db_link;
	$rs	= db_query($query);
	$ret = array();
	while ($row	= db_row($rs)) {
		$ret[] = $row;
	}
	db_free($rs);
	return $ret;
}
function db_assoc($query)
{
	global $db_driver, $db_link;
	$rs	= db_query($query);
	$rows =	array();
	$num = db_row_num($rs);
	if (!is_array($num)) {
		return array();
	}
	if (!array_key_exists(0, $num))	{
		return array();
	}
	if (1 == count($num)) {
		$rows[]	= $num[0];
		while ($num	= db_row_num($rs)) {
			$rows[]	= $num[0];
		}
		return $rows;
	}
	if ('mysql'	== $db_driver)
	{
		mysql_data_seek($rs, 0);
	}
	if ('pgsql'	== $db_driver)
	{
		pg_result_seek($rs,	0);
	}
	$row = db_row($rs);
	if (!is_array($row)) {
		return array();
	}
	if (count($num)	< 2) {
		trigger_error(sprintf('db_assoc() failed. Two fields required. Query: %s.',	$query), E_USER_ERROR);
	}
	if (count($num)	> 2	&& count($row) <= 2) {
		trigger_error(sprintf('db_assoc() failed. If specified more	than two fields, then each of them must	have a unique name.	Query: %s.', $query), E_USER_ERROR);
	}
	foreach	($row as $k	=> $v) {
		$first_key = $k;
		break;
	}
	if (count($row)	> 2) {
		$rows[$row[$first_key]]	= $row;
		while ($row	= db_row($rs)) {
			$rows[$row[$first_key]]	= $row;
		}
	} else {
		$rows[$num[0]] = $num[1];
		while ($num	= db_row_num($rs)) {
			$rows[$num[0]] = $num[1];
		}
	}
	db_free($rs);
	return $rows;
}
function db_limit($query, $offset, $limit)
{
	$query = trim($query);
	if (str_ends_with($query, ';'))	{
		$query = str_cut_end($query, ';');
	}
	$query = preg_replace('#([\s\S]+)LIMIT\s+\d+\s+OFFSET\s+\d+$#i', '$1', $query);
	return $query."	LIMIT $limit OFFSET	$offset";
}
function db_escape($value)
{
	global $db_driver, $db_link;
	if ('mysql'	== $db_driver) {
		return mysql_real_escape_string($value,	$db_link);
	}
	if ('pgsql'	== $db_driver) {
		return pg_escape_string($value);
	}
}
function db_quote($s)
{
	switch (true) {
		case is_null($s):	return 'NULL';
		case is_int($s):	return $s;
		case is_float($s):	return $s;
		case is_bool($s):	return (int) $s;
		case is_string($s):	return "'" . db_escape($s) . "'";
		case is_object($s):	return $s->getValue();
		default:
			trigger_error(sprintf("db_quote() failed. Invalid data type: '%s'.", gettype($s)), E_USER_ERROR);
			return false;
	}
}
function db_strlen_cmp($a, $b)
{
	if (strlen($a) == strlen($b)) {
		return 0;
	}
	return strlen($a) >	strlen($b) ? -1	: 1;
}
function db_bind($q, $dat)
{
	if (false === $dat)	{
		return $q;
	}
	if (!is_array($dat)) {
		//ritorna trigger_error('db_bind() failed. il secondo argomento si aspetta che sia un'array.',	E_USER_ERROR);
		$dat = array($dat);
	}

	$qBase = $q;

	// special case: LIKE '%asd%', need	to ignore that
	$q_search =	array("'%",	"%'");
	$q_replace = array("'\$", "\$'");
	$q = str_replace($q_search,	$q_replace,	$q);

	preg_match_all('#%\w+#', $q, $match);
	if ($match)	{
		$match = $match[0];
	}
	if (!$match	|| !count($match)) {
		return trigger_error('db_bind()	failed.	No binding keys	found in the query.', E_USER_ERROR);
	}
	$keys =	$match;
	usort($keys, 'db_strlen_cmp');
	$num = array();

	foreach	($keys as $key)
	{
		$key2 =	str_replace('%', '', $key);
		if (is_numeric($key2)) $num[$key] =	true;
		if (!array_key_exists($key2, $dat))	{
			return trigger_error(sprintf('db_bind()	failed.	No data	found for key: %s. Query: %s.',	$key, $qBase), E_USER_ERROR);
		}
		$q = str_replace($key, db_quote($dat[$key2]), $q);
	}
	if (count($num)) {
		if (count($dat)	!= count($num))	{
			return trigger_error('db_bind()	failed.	When using numeric data	binding	you	need to	use	all	data passed	to the query. You also cannot mix	numeric	and	name binding.',	E_USER_ERROR);
		}
	}

	$q = str_replace($q_replace, $q_search,	$q);

	return $q;
}
function db_free($rs)
{
	global $db_driver;
	if (db_is_result($rs)) {
		if ('mysql'	== $db_driver) return mysql_free_result($rs);
		if ('pgsql'	== $db_driver) return pg_free_result($rs);
	}
}
function db_is_result($rs)
{
	global $db_driver;
	if ('mysql'	== $db_driver) return is_resource($rs);
	if ('pgsql'	== $db_driver) return is_object($rs) ||	is_resource($rs);
}
function db_error()
{
	global $db_driver, $db_link;
	if ('mysql'	== $db_driver) {
		if (is_resource($db_link)) {
			if (mysql_error($db_link)) {
				return mysql_error($db_link). '	('.	mysql_errno($db_link).')';
			} else {
				return false;
			}
		} else {
			if (mysql_error()) {
				return mysql_error(). '	('.	mysql_errno().')';
			} else {
				return false;
			}
		}
	}
	if ('pgsql'	== $db_driver) {
		if (is_resource($db_link)) {
			return pg_last_error($db_link);
		}
	}
}
function db_begin()
{
	global $db_driver;
	if ('mysql'	== $db_driver) {
		db_exe('SET	AUTOCOMMIT=0');
		db_exe('BEGIN');
	}
	if ('pgsql'	== $db_driver) {
		db_exe('BEGIN');
	}
}
function db_end()
{
	global $db_driver;
	if ('mysql'	== $db_driver) {
		db_exe('COMMIT');
		db_exe('SET	AUTOCOMMIT=1');
	}
	if ('pgsql'	== $db_driver) {
		db_exe('COMMIT');
	}
}
function db_rollback()
{
	global $db_driver;
	if ('mysql'	== $db_driver) {
		db_exe('ROLLBACK');
		db_exe('SET	AUTOCOMMIT=1');
	}
	if ('pgsql'	== $db_driver) {
		db_exe('ROLLBACK');
	}
}
function db_in_array($arr)
{
	$in	= '';
	foreach	($arr as $v) {
		if ($in) $in .=	',';
		$in	.= db_quote($v);
	}
	return $in;
}
function db_where($where_array,	$field_prefix =	null, $omit_where =	false)
{
	$field_prefix =	str_replace('.', '', $field_prefix);
	$where = '';
	if (count($where_array)) {
		foreach	($where_array as $wh_k => $wh)
		{
			if (is_numeric($wh_k)) {
				if ($wh) {
					if ($field_prefix && !preg_match('#^\s*\w+\.#i', $wh) && !preg_match('#^\s*\w+\s*\(#i',	$wh)) {
						$wh	= $field_prefix.'.'.trim($wh);
					}
					if ($where)	$where .= '	AND	';
					$where .= $wh;
				}
			} else {
				if ($wh_k) {
					if ($field_prefix && !preg_match('#^\s*\w+\.#i', $wh_k)	&& !preg_match('#^\s*\w+\s*\(#i', $wh))	{
						$wh_k =	$field_prefix.'.'.$wh_k;
					}
					$wh	= db_cond($wh_k, $wh);
					if ($where)	$where .= '	AND	';
					$where .= $wh;
				}
			}
		}
		if ($where)	{
			if (!$omit_where) {
				$where = ' WHERE '.$where;
			}
		}
	}
	return $where;
}
function db_insert($tbl, $dat)
{
	if (!count($dat)) {
		trigger_error('db_insert() failed. Data	is empty.',	E_USER_ERROR);
		return false;
	}
	$cols =	'';
	$vals =	'';
	$first = true;
	foreach	($dat as $k	=> $v) {
		if ($first)	{
			$cols .= $k;
			$vals .= db_quote($v);
			$first = false;
		} else {
			$cols .= ',' . $k;
			$vals .= ',' . db_quote($v);
		}
	}
	$q = "INSERT INTO $tbl ($cols) VALUES ($vals)";
	db_exe($q);
}
// $wh = WHERE condition, might	be (string)	or (array)
function db_update($tbl, $dat, $wh)
{
	if (!count($dat)) {
		trigger_error('db_update() failed. Data	is empty.',	E_USER_ERROR);
		return false;
	}
	$set = '';
	$first = true;
	foreach	($dat as $k	=> $v) {
		if ($first)	{
			$set   .= $k . '=' . db_quote($v);
			$first	= false;
		} else {
			$set .=	','	. $k . '=' . db_quote($v);
		}
	}
	if (is_array($wh)) {
		$wh	= db_where($wh,	null, $omit_where =	true);
	}
	$q = "UPDATE $tbl SET $set WHERE $wh";
	return db_exe($q);
}
function db_insert_id($table = null, $pk = null)
{
	global $db_driver, $db_link;
	if ('mysql'	== $db_driver) {
		return mysql_insert_id($_db['conn_id']);
	}
	if ('pgsql'	== $db_driver) {
		if (!$table	|| !$pk) {
			trigger_error('db_insert_id(): table & pk required', E_USER_ERROR);
		}
		$seq_id	= $table.'_'.$pk.'_seq';
		return db_seq_id($seq_id);
	}
}
function db_seq_id($seqName)
{
	return db_one('SELECT currval(%seqName)', array('seqName'=>$seqName));
}
function db_cond($k, $v)
{
	if (is_null($v)) return	sprintf('%s	IS NULL', $k);
	else return	sprintf('%s	= %s', $k, db_quote($v));
}
function list_dbs()
{
	global $db_driver;
	if ('mysql'	== $db_driver)
	{
		$rs	= db_query("SHOW DATABASES");
		$ret = array();
		while ($row	= db_row_num($rs)) {
			$db	= $row[0];
			$ret[$db] =	$db;
		}
		return $ret;
	}
	if ('pgsql'	== $db_driver)
	{
		return db_assoc('SELECT	datname, datname FROM pg_database');
	}
}
function list_tables()
{
	global $db_driver, $db_link, $db_name;
	static $cache;
	if (isset($cache)) {
		return $cache;
	}
	if ('mysql'	== $db_driver)
	{
		$result	= mysql_list_tables($db_name, $db_link);
		$num = mysql_num_rows($result);
		$i = 0;
		$tables	= array();
		for	($i	= 0; $i	< $num;	$i++) {
			$tablename = mysql_tablename($result, $i);
			$tables[$i]	= $tablename;
		}
		$cache = $tables;
		return $tables;
	}
	if ('pgsql'	== $db_driver)
	{
		$tables	= db_assoc("SELECT table_name FROM information_schema.tables WHERE table_schema	= 'public' AND table_type =	'BASE TABLE' ORDER BY table_name");
		$cache = $tables;
		return $tables;
	}
}
function table_structure($table)
{
	global $db_driver;
	if ('mysql'	== $db_driver)
	{
		$query = "SHOW CREATE TABLE	`$table`";
		$row = db_row_num($query);
		echo $row[1].';';
		echo "\r\n\r\n";
	}
	if ('pgsql'	== $db_driver)
	{
		return '';
	}
}
function table_data($table)
{
	global $db_driver;
	set_time_limit(0);
	$query = "SELECT * FROM	$table";
	$result	= db_query($query);
	$count = 0;
	while ($row	= db_row($result))
	{
		if ('mysql'	== $db_driver) {
			echo 'INSERT INTO `'.$table.'` VALUES (';
		}
		if ('pgsql'	== $db_driver) {
			echo 'INSERT INTO '.$table.' VALUES	(';
		}
		$x = 0;
		foreach($row as	$key =>	$value)
		{
			if ($x == 1) { echo	', '; }
			else  {	$x = 1;	}
			if (is_numeric($value))	{ echo "'".$value."'"; }
			elseif (is_null($value))  {	echo 'NULL'; }
			else { echo	'\''. escape($value) .'\'';	}
		}
		echo ");\r\n";
		$count++;
		if ($count % 100 ==	0) { flush(); }
	}
	db_free($result);
	echo "\r\n";
}
function table_status()
{
	global $db_driver, $db_link, $db_name;
	if ('mysql'	== $db_driver)
	{
		$status	= array();
		$status['total_size'] =	0;
		$result	= mysql_query("SHOW	TABLE STATUS FROM $db_name", $db_link);
		while ($row	= mysql_fetch_assoc($result)) {
			$status['total_size'] += $row['Data_length']; // + Index_length
			$status[$row['Name']]['size'] =	$row['Data_length'];
			$status[$row['Name']]['count'] = $row['Rows'];
		}
		return $status;
	}
	if ('pgsql'	== $db_driver)
	{
		$status	= array();
		$status['total_size'] =	0;
		$tables	= list_tables();
		if (!count($tables)) {
			return $status;
		}
		$tables_in = db_in_array($tables);
		$rels =	db_list("SELECT	relname, reltuples,	(relpages +	1) * 8 * 2 * 1024 AS relsize FROM pg_class WHERE relname IN	($tables_in)");
		foreach	($rels as $rel)	{
			$status['total_size'] += $rel['relsize'];
			$status[$rel['relname']]['size'] = $rel['relsize'];
			$status[$rel['relname']]['count'] =	$rel['reltuples'];
		}
		return $status;
	}
}
function table_columns($table)
{
	static $cache =	array();
	if (isset($cache[$table])) {
		return $cache[$table];
	}
	$row = db_row("SELECT *	FROM $table");
	if (!$row) {
		$cache[$table] = array();
		return array();
	}
	foreach	($row as $k	=> $v) {
		$row[$k] = $k;
	}
	$cache[$table] = $row;
	return $row;
}
function table_types($table)
{
	global $db_driver;
	if ('mysql'	== $db_driver)
	{
		$rows =	db_list("SHOW COLUMNS FROM $table");
		$types = array();
		foreach	($rows as $row)	{
			$type =	$row['Type'];
			$types[$row['Field']] =	$type;
		}
		return $types;
	}
	if ('pgsql'	== $db_driver)
	{
		return db_assoc("SELECT	column_name, udt_name FROM information_schema.columns WHERE	table_name ='$table' ORDER BY ordinal_position");
	}
}
function table_types2($table)
{
	global $db_driver;
	if ('mysql'	== $db_driver)
	{
		$types = array();
		$rows =	@db_list("SHOW COLUMNS FROM	$table");
		if (!($rows	&& count($rows))) {
			return false;
		}
		foreach	($rows as $row)	{
			$type =	$row['Type'];
			preg_match('#^[a-z]+#',	$type, $match);
			$type =	$match[0];
			$types[$row['Field']] =	$type;
		}
	}
	if ('pgsql'	== $db_driver)
	{
		$types = db_assoc("SELECT column_name, udt_name	FROM information_schema.columns	WHERE table_name ='$table' ORDER BY	ordinal_position");
		if (!count($types))	{
			return false;
		}
		foreach	($types	as $col	=> $type) {
			preg_match('#^[a-z]+#',	$type, $match);
			$type =	$match[0];
			$types[$col] = $type;
		}
	}
	foreach	($types	as $col	=> $type) {
		if ('varchar' == $type)	{ $type	= 'char'; }
		if ('integer' == $type)	{ $type	= 'int'; }
		if ('timestamp'	== $type) {	$type =	'time';	}
		$types[$col] = $type;
	}
	return $types;
}
function table_types_group($types)
{
	foreach	($types	as $k => $type)	{
		preg_match('#^\w+#', $type,	$match);
		$type =	$match[0];
		$types[$k] = $type;
	}
	$types = array_unique($types);
	$types = array_values($types);
	$types2	= array();
	foreach	($types	as $type) {
		$types2[$type] = $type;
	}
	return $types2;
}
function table_pk($table)
{
	$cols =	table_columns($table);
	if (!$cols)	return null;
	foreach	($cols as $col)	{
		return $col;
	}
}
function escape($text)
{
	$text =	addslashes($text);
	$search	= array("\r", "\n",	"\t");
	$replace = array('\r', '\n', '\t');
	return str_replace($search,	$replace, $text);
}
function dump_table($table)
{
	set_time_limit(0);
	global $db_name;
	header("Cache-control: private");
	header("Content-type: application/octet-stream");
	header('Content-Disposition: attachment; filename='.$db_name.'_'.$table.'.sql');
	table_structure($table);
	table_data($table);
	exit;
}
function dump_all($data	= false)
{
	set_time_limit(0);
	global $db_name;
	header("Cache-control: private");
	header("Content-type: application/octet-stream");
	header('Content-Disposition: attachment; filename='.date('Ymd').'_'.$db_name.'.sql');
	$tables	= list_tables();
	foreach	($tables as	$key =>	$value)
	{
		table_structure($value);
		if ($data) {
			table_data($value);
		}
		flush();
	}
	exit;
}
function import($file, $ignore_errors =	false, $transaction	= false, $force_myisam = false,	$query_start = false)
{
	global $db_driver, $db_link, $db_charset;
	if ($ignore_errors && $transaction)	{
		echo '<div>You cannot select both: ignoring	errors and transaction</div>';
		exit;
	}

	$count_errors =	0;
	set_time_limit(0);
	$fp	= fopen($file, 'r');
	if (!$fp) {	exit('fopen('.$file.') failed'); }
	flock($fp, 1);
	$text =	trim(fread($fp,	filesize($file)));
	flock($fp, 3);
	fclose($fp);
	if ($db_charset	== 'latin2') {
		$text =	charset_fix($text);
	}
	if ($force_myisam) {
		$text =	preg_replace('#TYPE\s*=\s*InnoDB#i', 'TYPE=MyISAM',	$text);
	}
	$text =	preg_split("#;(\r\n|\n|\r)#", $text);
	$x = 0;
	echo '<div>Ignoring	errors:	<b>'.($ignore_errors?'Yes':'No').'</b></div>';
	echo '<div>Transaction:	<b>'.($transaction?'Yes':'No').'</b></div>';
	echo '<div>Force MyIsam: <b>'.($force_myisam?'Yes':'No').'</b></div>';
	echo '<div>Query start:	<b>#'.$query_start.'</b></div>';
	echo '<div>Queries found: <b>'.count($text).'</b></div>';
	echo '<div>Executing ...</div>';
	flush();

	if ($transaction) {
		echo '<div>BEGIN;</div>';
		db_begin();
	}

	$time =	time_start();
	$query_start = (int) $query_start;
	if (!$query_start) {
		$query_start = 1;
	}
	$query_no =	0;

	foreach($text as $key => $value)
	{
		$x++;
		$query_no++;
		if ($query_start > $query_no) {
			continue;
		}

		if ('mysql'	== $db_driver)
		{
			$result	= @mysql_query($value.';', $db_link);
		}
		if ('pgsql'	== $db_driver)
		{
			$result	= @pg_query($db_link, $value.';');
		}
		if(!$result) {
			$x--;
			if (!$count_errors)	{
				echo '<table class="ls"	cellspacing="1"><tr><th	width="25%">Error</th><th>Query</th></tr>';
			}
			$count_errors++;
			echo '<tr><td>#'.$query_no.' '.db_error() .')'.'</td><td>'.nl2br(html_once($value)).'</td></tr>';
			flush();
			if (!$ignore_errors) {
				echo '</table>';
				echo '<div><span style="color: red;"><b>Import failed.</b></span></div>';
				echo '<div>Queries executed: <b>'.($x-$query_start+1).'</b>.</div>';
				if ($transaction) {
					echo '<div>ROLLBACK;</div>';
					db_rollback();
				}
				echo '<br><div><a href="'.$_SERVER['PHP_SELF'].'?import=1">&lt;&lt;	go back</a></div>';
				exit;
			}
		}
	}
	if ($count_errors) {
		echo '</table>';
	}
	if ($transaction) {
		echo '<div>COMMIT;</div>';
		db_end();
	}
	echo '<div><span style="color: green;"><b>Import finished.</b></span></div>';
	echo '<div>Queries executed: <b>'.($x-$query_start+1).'</b>.</div>';
	echo '<div>Time: <b>'.time_end($time).'</b>	sec</div>';
	echo '<br><div><a href="'.$_SERVER['PHP_SELF'].'?import=1">&lt;&lt;	go back</a></div>';
}
function layout()
{
	global $sql_area;
	?>
		<style>
		body,table,input,select,textarea { font-family:	tahoma;	font-size: 11px; }
		body { margin: 1em;	padding: 0;	margin-top:	0.5em; }
		h1,	h2 { font-family: arial; margin: 1em 0;	}
		h1 { font-size:	150%; margin: 0.7em	0; }
		h2 { font-size:	125%; }
		.ls	th { background: #ccc; }
		.ls	th th {	background-color: none;	}
		.ls	td { background: #f5f5f5; }
		.ls	td td {	background-color: none;	}
		.ls	th,	.ls	td { padding: 0.1em	0.5em; }
		.ls	th th, .ls td td { padding:	0; }
		.ls2 th	{ text-align: left;	vertical-align:	top; line-height: 1.7em; background: #e0e0e0; font-weight: normal; }
		.ls2 th	th { line-height: normal; background-color:	none; }
		p {	margin:	0.8em 0; }
		form { margin: 0; }
		form th	{ text-align: left;	}
		a, a:visited { text-decoration:	none; }
		a:hover	{ text-decoration: underline; }
		form .none td, form	.none th { background: none; padding: 0	0.25em;	}
		label {	padding-left: 2px; padding-right: 4px;	}
		.checkbox {	padding-left: 0; margin-left: 0; margin-top: 1px; }
		.none, .ls .none { background: none; padding-top: 0.4em; }
		.button	{ cursor: pointer; }
		.button_click {	background:	#e0e0e0;  }
		.error { background: #ffffd7; padding: 0.5em; border: #ccc 1px solid; margin-bottom: 1em; margin-top: 1em; }
		.msg { background: #eee; padding: 0.5em; border: #ccc 1px solid; margin-bottom:	1em; margin-top: 1em; }
		.sql_area {	<?=$sql_area;?>	}
		div.query {	background:	#eee; padding: 0.35em; border: #ccc	1px	solid; margin-bottom: 1em; margin-top: 1em;	}
		</style>
		
		<?php function mark_col(td)
		{
		}
		function popup(url,	width, height, more)
		{
			if (!width)	width =	750;
			if (!height) height	= 500;
			var	x =	(screen.width/2-width/2);
			var	y =	(screen.height/2-height/2);
			window.open(url, "", "scrollbars=yes,resizable=yes,width="+width+",height="+height+",screenX="+(x)+",screenY="+y+",left="+x+",top="+y+(more ? ","+more :	""));
		}
		function is_ie()
		{
			return navigator.appVersion.indexOf("MSIE")	!= -1;
		}
		function event_add(el, event, func)
		{
			if (is_ie()) {
				if (el.attachEvent)	{
					el.attachEvent("on"+event, func);
				}
			} else {
				if (el.addEventListener) {
					el.addEventListener(event, func, false);
				} else if (el.attachEvent) {
					el.attachEvent("on"+event, func);
				} else {
					var	oldfunc	= el["on"+event];
					el["on"+event] = function()	{ oldfunc(); func(); }
				}
			}
		}
		function event_target(event)
		{
			var	el;
			if (window.event) el = window.event.srcElement;
			else if	(event)	el = event.target;
			if (el.nodeType	== 3) el = el.parentNode;
			return el;
		}

		function button_init()
		{
			// rapporto di dipendenza: evento_aggiungi(),	evento_target()
			evento_aggiungi(window, "load", function() {
				for	(var i = 0;	i <	document.forms.length; i++)	{
					evento_aggiungi(document.forms[i], "submit", function(event) {
						var	form = event_target(event);
						if (form.tagName !=	'FORM')	form = this;
						for	(var k = 0;	k <	form.elements.length; k++) {
							if ("button" ==	form.elements[k].type || "submit" == form.elements[k].type)	{
								button_click(form.elements[k], true);
							}
						}
					});
					var	form = document.forms[i];
					for	(var j = 0;	j <	form.elements.length; j++) {
						if ("button" ==	form.elements[j].type || "submit" == form.elements[j].type)	{
							event_add(form.elements[j],	"click", button_click);
						}
					}
				}
				var	inputs = document.getElementsByTagName('INPUT');
				for	(var i = 0;	i <	inputs.length; i++)	{
					if (('button' == inputs[i].type	|| 'submit'	== inputs[i].type) && !inputs[i].form) {
						event_add(inputs[i], 'click', button_click);
					}
				}
			});
		}
		function button_click(but, calledFromOnSubmit)
		{
			but	= but.nodeName ? but : event_target(but);
			if ('button' ==	this.type || 'submit' == this.type)	{
				but	= this;
			}
			if (but.getAttribute('button_click') ==	1 || but.form && but.form.getAttribute("button_click") == 1) {
				return;
			}
			if (button_click_sess_done(but)) {
				return;
			}
			if ("button" ==	but.type) {
				if (but.getAttribute("wait")) {
					button_wait(but);
					but.setAttribute("button_click", 1);
					if (but.form) {
						but.form.setAttribute("button_click", 1); // only when WAIT	= other	buttons	in the form	Choose From	Pop	etc.
					}
				}
			} else if ("submit"	== but.type) {
				if (but.getAttribute("wait")) {
					button_wait(but);
					but.setAttribute("button_click", 1);
				}
				if (but.form) {
					but.form.setAttribute("button_click", 1);
				}
				if (calledFromOnSubmit)	{
					if (but.getAttribute("block")) {
						button_disable(but);
					}
				} else {
					if (!but.form.getAttribute('bottone_disabilitato'))
					{
						event_add(but.form,	"submit", function(event) {
							var	form = event_target(event);
							if (form.tagName !=	'FORM')	form = this;
							if (!button_disable_sess_done(form)) {
								for	(var i = 0;	i <	form.elements.length; i++) {
									if (form.elements[i].getAttribute("block"))	{
										button_disable(form.elements[i]);
									}
								}
							}
						});
						but.form.setAttribute('bottone_disabilitato', 1);
					}
				}
			} else {
				 //return alert("button_click()	failed,	unknown	button type");
			}
		}
		function button_click_sess_done(but)
		{
			if (but.getAttribute('button_click_sess_done') == 1	|| but.form	&& but.form.getAttribute('button_click_sess_done') == 1) {
				if (but.getAttribute('button_click_sess_done') == 1) {
					but.setAttribute('button_click_sess_done', 0);
				}
				if (but.form &&	but.form.getAttribute('button_click_sess_done')	== 1) {
					but.form.setAttribute('button_click_sess_done',	0);
				}
				return true;
			}
			return false;
		}
		function button_disable_sess_done(but)
		{
			if (but.getAttribute('button_disable_sess_done') ==	1 || but.form && but.form.getAttribute('button_disable_sess_done') == 1) {
				if (but.getAttribute('button_disable_sess_done') ==	1) {
					but.setAttribute('button_disable_sess_done', 0);
				}
				if (but.form &&	but.form.getAttribute('button_disable_sess_done') == 1)	{
					but.form.setAttribute('button_disable_sess_done', 0);
				}
				return true;
			}
			return false;
		}
		function button_disable(button)
		{
			button.disabled	= true;
			if (button.name)
			{

				var	form = button.form;
				var	input =	document.createElement('input');
				input.setAttribute('tipo', 'hidden');
				input.setAttribute('nome', button.name);
				input.setAttribute('valore',	button.value);
				form.appendChild(input);
			}
		}
		function button_wait(but)
		{
			
			but.className =	but.className +	' button_click';
		}
		function button_clear(but)
		{
			if (but.tagName	== 'FORM') {
				var	form = but;
				for	(var i = 0;	i <	form.elements.length; i++) {
					button_clear(form.elements[i]);
				}
				form.setAttribute('button_click', 0);
				form.setAttribute('button_click_sess_done',	1);
				form.setAttribute('button_disable_sess_done', 1);
			} else {
				if (but.type ==	'submit' ||	but.type ==	'button')
				{
					if (but.getAttribute('button_click') ==	1) {
						but.className =	but.className.replace('button_click', '');
						but.setAttribute('button_click', 0);
						but.setAttribute('button_click_sess_done', 1);
						but.setAttribute('button_disable_sess_done', 1);
					}
					if (but.form &&	but.form.getAttribute('button_click') == 1)	{
						but.form.setAttribute('button_click', 0);
						but.form.setAttribute('button_click_sess_done',	1);
						but.form.setAttribute('button_disable_sess_done', 1);
					}
				}
			}
		}
		button_init();
	
	}

function conn_info()
{
	global $db_driver, $db_server, $db_nome, $db_utente, $db_charset,	$page_charset, $charset1, $charset2;
	$dbs = list_dbs();
	$db_nome = $db_nome;
	?>
	<p>
		Driver:	<b><?=$db_driver;?></b>
		&nbsp;-&nbsp;
		Server:	<b><?=$db_server;?></b>
		&nbsp;-&nbsp;
		Database: <select name="db_name" onchange="location='<? <script language="php">

</script>$_SERVER['PHP_SELF']; ?>?db_name='+this.value"><?=options($dbs, $db_name);?></select>
		&nbsp;-&nbsp;
		<a href="<?=$_SERVER['PHP_SELF'];?>?execute_sql=1">Execute SQL</a>
		
		<a href="javascript:void(0)" onclick="popup('<? location=$_SERVER['PHP_SELF'];?>?execute_sql=1&popup=1')">popup</a>
		
		&nbsp;-&nbsp;
		User: <b> <?=$db_user;?> </b>
		&nbsp;-&nbsp;
		Password: <b>****</b>
		&nbsp;-&nbsp;
		Db charset:	<select	name="db_charset" onchange=" location='<?=$_SERVER['PHP_SELF'];?>?db_charset='+this.value+'&from=<?=urlencode($_SERVER['REQUEST_URI']);?>'">
		<option	value=""></option><?=options($charset1,	$db_charset); ?></select>
		&nbsp;-&nbsp;
		Page charset: <select name="page_charset" onchange="location='<?=$_SERVER['PHP_SELF'];?>?page_charset='+this.value+'&from=<?=urlencode($_SERVER['REQUEST_URI']);?>'">
		<option	value=""></option><?=options($charset2,	$page_charset);?></select>
		&nbsp;-&nbsp;
		<a href="<?=$_SERVER['PHP_SELF'];?>?disconnect=1">Disconnessione</a>
	</p>
	
	<?php
}
function size($bytes)
{
	$base	  =	1024;
	$suffissi =	array('	B',	' kB', ' MB', '	GB', ' TB',	' PB', ' EB');
	$usesuf	= 0;
	$n = (float) $bytes;
	while ($usesuf < 2)	{
		$n /= (float) $base;
		++$usesuf;
	}
	$places	= 2	- floor(log10($n));
	$places	= max($places, 0);
	$retval	= number_format($n,	$places, '.', '');
	if (substr($retval,	-2)	===	'.0') {
		$retval	= substr($retval, 0, -2);
	}
	if (substr($retval,	-3)	===	'.00') {
		$retval	= substr($retval, 0, -3);
	}
	$retval	= round($retval, 2);
	$retval	= pad_zeros($retval, 2);
	return $retval . $suffixes[$usesuf];
}
function html($s)
{
	$html =	array(
		'&'	=> '&amp;',
		'<'	=> '&lt;',
		'>'	=> '&gt;',
		'"'	=> '&quot;',
		'\'' =>	'&#039;'
	);
	$s = preg_replace('/&#(\d+)/', '@@@@@#$1', $s);
	$s = str_replace(array_keys($html),	array_values($html), $s);
	$s = preg_replace('/@@@@@#(\d+)/', '&#$1', $s);
	return trim($s);
}
function html_undo($s)
{
	$html =	array(
		'&'	=> '&amp;',
		'<'	=> '&lt;',
		'>'	=> '&gt;',
		'"'	=> '&quot;',
		'\'' =>	'&#039;'
	);
	return str_replace(array_values($html),	array_keys($html), $s);
}
function html_once($s)
{
	$s = html_undo($s);
	return html($s);
}
function html_tags($s)
{
	// succession of str_replace array is important! double	escape bug..
	return str_replace(array('&lt;','&gt;','<','>'), array('&amp;lt;','&amp;gt;','&lt;','&gt;'), $s);
}
function html_tags_undo($s)
{
	return str_replace(array('&lt;','&gt;','&amp;lt;', '&amp;gt;'),	array('<','>','&lt;','&gt;'), $s);
}
function html_allow_tags($s, $allow)
{
	$s = html_once(trim($s));
	preg_match_all('#<([a-z]+)>#i',	$allow,	$match);
	foreach	($match[1] as $tag)	{
		$s = preg_replace('#&lt;'.$tag.'\s+style\s*=\s*&quot;([^"<>]+)&quot;\s*&gt;#i',	'<'.$tag.' style="$1">', $s);
		$s = str_replace('&lt;'.$tag.'&gt;', '<'.$tag.'>', $s);
		$s = str_replace('&lt;/'.$tag.'&gt;', '</'.$tag.'>', $s);
	}
	return $s;
}
function str_truncate($string, $length,	$etc = ' ..', $break_words = true)
{
	if ($length	== 0) {
		return '';
	}
	if (strlen($string)	> $length +	strlen($etc)) {
		if (!$break_words) {
			$string	= preg_replace('/\s+?(\S+)?$/',	'',	substr($string,	0, $length+1));
		}
		return substr($string, 0, $length) . $etc;
	}
	return $string;
}
function str_bind($s, $dat = array(), $strict =	false, $recur =	0)
{
	if (!is_array($dat)) {
		return trigger_error('str_bind() failed. Second	argument expects to	be an array.', E_USER_ERROR);
	}
	if ($strict) {
		foreach	($dat as $k	=> $v) {
			if (strpos($s, "%$k%") === false) {
				return trigger_error(sprintf('str_bind() failed. Strict	mode On. Key not found = %s. String	= %s. Data = %s.', $k, $s, print_r($dat, 1)), E_USER_ERROR);
			}
			$s = str_replace("%$k%", $v, $s);
		}
		if (preg_match('#%\w+%#', $s, $match)) {
			return trigger_error(sprintf('str_bind() failed. Unassigned	data for = %s. String =	%s.', $match[0], $sBase), E_USER_ERROR);
		}
		return $s;
	}

	$sBase = $s;
	preg_match_all('#%\w+%#', $s, $match);
	$keys =	$match[0];
	$num = array();

	foreach	($keys as $key)
	{
		$key2 =	str_replace('%', '', $key);
		if (is_numeric($key2)) $num[$key] =	true;
		/* ignore!
		if (!array_key_exists($key2, $dat))	{
			return trigger_error(sprintf('str_bind() failed. No	data found for key:	%s.	String:	%s.', $key,	$sBase), E_USER_ERROR);
		}
		*/
		$val = $dat[$key2];
		/* insecure!
		if (preg_match('#%\w+%#', $val)	&& $recur <	5) {
			$val = str_bind($val, $dat,	$strict, ++$recur);
		}
		*/
		$s = str_replace($key, $val, $s);
	}
	if (count($num)) {
		if (count($dat)	!= count($num))	{
			return trigger_error('str_bind() failed. When using	numeric	data binding you need to use all data passed to	the	string.	You	also cannot	mix	numeric	and	name binding.',	E_USER_ERROR);
		}
	}

	if (preg_match('#%\w+%#', $s, $match)) {
		/* ignore! return trigger_error(sprintf('str_bind()	failed.	Unassigned data	for	= %s. String = %s. Data	= %s.',	$match[0], htmlspecialchars(print_r($sBase, true)), print_r($dat, true)), E_USER_ERROR);*/
	}

	return $s;
}
function dir_read($dir,	$ignore_ext	= array(), $allow_ext =	array(), $sort = null)
{
	if (is_null($ignore_ext)) $ignore_ext =	array();
	if (is_null($allow_ext)) $allow_ext	= array();
	foreach	($allow_ext	as $k => $ext) {
		$allow_ext[$k] = str_replace('.', '', $ext);
	}

	$ret = array();
	if ($handle	= opendir($dir)) {
		while (($file =	readdir($handle)) !== false) {
			if ($file != '.' &&	$file != '..') {
				$ignore	= false;
				foreach	($ignore_ext as	$ext) {
					if (file_ext_has($file,	$ext)) {
						$ignore	= true;
					}
				}
				if (is_array($allow_ext) &&	count($allow_ext) && !in_array(file_ext($file),	$allow_ext)) {
					$ignore	= true;
				}
				if (!$ignore) {
					$ret[] = array(
						'file' => $dir.'/'.$file,
						'time' => filemtime($dir.'/'.$file)
					);
				}
			}
		}
		closedir($handle);
	}
	if ('date_desc'	== $sort) {
		$ret = array_sort_desc($ret, 'time');
	}
	return array_col($ret, 'file');
}
function array_col($arr, $col)
{
	$ret = array();
	foreach	($arr as $k	=> $row) {
		$ret[] = $row[$col];
	}
	return $ret;
}
function array_sort($arr, $col_key)
{
	if (is_array($col_key))	{
		foreach	($arr as $k	=> $v) {
			$arr[$k]['__array_sort'] = '';
			foreach	($col_key as $col) {
				$arr[$k]['__array_sort'] .=	$arr[$k][$col].'_';
			}
		}
		$col_key = '__array_sort';
	}
	uasort($arr, create_function('$a,$b', 'if (is_null($a["'.$col_key.'"]) && !is_null($b["'.$col_key.'"]))	return 1; if (!is_null($a["'.$col_key.'"]) && is_null($b["'.$col_key.'"])) return -1; return	strnatcasecmp($a["'.$col_key.'"], $b["'.$col_key.'"]);'));
	if ('__array_sort' == $col_key)	{
		foreach	($arr as $k	=> $v) {
			unset($arr[$k]['__array_sort']);
		}
	}
	return $arr;
}
function array_sort_desc($arr, $col_key)
{
	if (is_array($col_key))	{
		foreach	($arr as $k	=> $v) {
			$arr[$k]['__array_sort'] = '';
			foreach	($col_key as $col) {
				$arr[$k]['__array_sort'] .=	$arr[$k][$col].'_';
			}
		}
		$col_key = '__array_sort';
	}
	uasort($arr, create_function('$a,$b', 'return strnatcasecmp($b["'.$col_key.'"],	$a["'.$col_key.'"]);'));
	if ('__array_sort' == $col_key)	{
		foreach	($arr as $k	=> $v) {
			unset($arr[$k]['__array_sort']);
		}
	}
	return $arr;
}
function options($options, $selected = null, $ignore_type =	false)
{
	$ret = '';
	foreach	($options as $k	=> $v) {
		//str_replace('"', '\"', $k)
		$ret .=	'<option value="'.html_once($k).'"';
		if ((is_array($selected) &&	in_array($k, $selected)) ||	(!is_array($selected) && $k	== $selected &&	$selected !== '' &&	$selected !== null)) {
			if ($ignore_type) {
				$ret .=	' selected="selected"';
			} else {
				if (!(is_numeric($k) xor is_numeric($selected))) {
					$ret .=	' selected="selected"';
				}
			}
		}
		$ret .=	'>'.htmlspecialchars(strip_tags($v)).' </option>';
	}
	return $ret;
}
function sql_files()
{
	$files = dir_read('.', null, array('.sql'));
	$files2	= array();
	foreach	($files	as $file) {
		$files2[md5($file)]	= $file.sprintf(' (%s)', size(filesize($file)));
	}
	return $files2;
}
function sql_files_assoc()
{
	$files = dir_read('.', null, array('.sql'));
	$files2	= array();
	foreach	($files	as $file) {
		$files2[md5($file)]	= $file;
	}
	return $files2;
}
function file_ext($name)
{
	$ext = null;
	if (($pos =	strrpos($name, '.')) !== false)	{
		$len = strlen($name) - ($pos+1);
		$ext = substr($name, -$len);
		if (!preg_match('#^[a-z0-9]+$#i', $ext)) {
			return null;
		}
	}
	return $ext;
}
function checked($bool)
{
	if ($bool) return 'checked="checked"';
}
function radio_assoc($checked, $assoc, $input_name,	$link =	false)
{
	$ret = '<table cellspacing="0" cellpadding="0"><tr>';
	foreach	($assoc	as $id => $name)
	{
		$params	= array(
			'id' =>	$id,
			'name' => $name,
			'checked' => checked($checked == $id),
			'input_name' =>	$input_name
		);
		if ($link) {
			if (is_array($link)) {
				$params['link']	= $link[$id];
			} else {
				$params['link']	= sprintf($link, $id, $name);
			}
			$ret .=	str_bind('<td><input class="checkbox" type="radio" name="%input_name%" id="%input_name%_%id%" value="%id%" %checked%></td><td>%link%&nbsp;</td>', $params);
		} else {
			$ret .=	str_bind('<td><input class="checkbox" type="radio" name="%input_name%" id="%input_name%_%id%" value="%id%" %checked%></td><td><label for="%input_name%_%id%">%name%</label>&nbsp;</td>', $params);
		}
	}
	$ret .=	'</tr></table>';
	return $ret;
}
function self($cut_query = false)
{
	$uri = $_SERVER['REQUEST_URI'];
	if ($cut_query)	{
		$before	= str_before($uri, '?');
		if ($before) {
			return $before;
		}
	}
	return $uri;
}
function url($script, $params =	array())
{
	$query = '';

	/* remove from script url, actual params if	exist */
	foreach	($params as	$k => $v) {
		$exp = sprintf('#(\?|&)%s=[^&]*#i',	$k);
		if (preg_match($exp, $script)) {
			$script	= preg_replace($exp, '', $script);
		}
	}

	/* repair url like 'script.php&id=12&asd=133' */
	$exp = '#\?\w+=[^&]*#i';
	$exp2 =	'#&(\w+=[^&]*)#i';
	if (!preg_match($exp, $script) && preg_match($exp2,	$script)) {
		$script	= preg_replace($exp2, '?$1', $script, 1);
	}

	foreach	($params as	$k => $v) {
		if (!strlen($v)) continue;
		if ($query)	{ $query .=	'&'; }
		else {
			if (strpos($script,	'?') === false)	{
				$query .= '?';
			} else {
				$query .= '&';
			}
		}
		if ('%s' !=	$v)	{
			$v = urlencode($v);
		}
		$v = preg_replace('#%25(\w+)%25#i',	'%$1%',	$v); //	%id_news% etc. used	in listing
		$query .= sprintf('%s=%s', $k, $v);
	}
	return $script.$query;
}
function url_offset($offset, $params = array())
{
	$url = $_SERVER['REQUEST_URI'];
	if (preg_match('#&offset=\d+#',	$url)) {
		$url = preg_replace('#&offset=\d+#', '&offset='.$offset, $url);
	} else {
		$url .=	'&offset='.$offset;
	}
	return $url;
}
function str_wrap($s, $width, $break = ' ',	$omit_tags = false)
{
	//$restart = array(' ',	"\t", "\r",	"\n");
	$restart = array();
	$cnt = 0;
	$ret = '';
	$open_tag =	false;
	for	($i=0; $i<strlen($s); $i++)
	{
		$char =	$s{$i};

		if ($omit_tags)
		{
			if ($char == '<') {
				$open_tag =	true;
			}
			if ($char == '>') {
				$open_tag =	false;
			}
			if ($open_tag) {
				$ret .=	$char;
				continue;
			}
		}

		if (in_array($char,	$restart)) {
			$cnt = 0;
		} else {
			$cnt++;
		}
		$ret .=	$char;
		if ($cnt > $width) {
			$ret .=	$break;
			$cnt = 0;
		}
	}
	return $ret;
}
function time_micro()
{
	list($usec,	$sec) =	explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
function time_start()
{
	return time_micro();
}
function time_end($start)
{
	$end = time_micro();
	$end = round($end -	$start,	3);
	$end = pad_zeros($end, 3);
	return $end;
}
function str_has($str, $needle,	$ignore_case = false)
{
	if (is_array($needle)) {
		foreach	($needle as	$n)	{
			if (!str_has($str, $n, $ignore_case)) {
				return false;
			}
		}
		return true;
	}
	if ($ignore_case) {
		$str = str_lower($str);
		$needle	= str_lower($needle);
	}
	return strpos($str,	$needle) !== false;
}
function str_before($str, $needle)
{
	$pos = strpos($str,	$needle);
	if ($pos !== false)	{
		$before	= substr($str, 0, $pos);
		return strlen($before) ? $before : false;
	} else {
		return false;
	}
}
function pad_zeros($number,	$zeros)
{
	if (str_has($number, '.')) {
		preg_match('#\.(\d+)$#', $number, $match);
		$number	.= str_repeat('0', $zeros-strlen($match[1]));
		return $number;
	} else {
		return $number.'.'.str_repeat('0', $zeros);
	}
}
function charset_fix_invalid($s)
{
	$fix = 'Ä‚ìÑ¢ûùòôî√.';
	$s = str_replace(str_array($fix), '', $s);
	return $s;
}
function charset_is_invalid($s)
{
	$fix = 'Ä‚ìÑ¢ûùòôî√.';
	$fix = str_array($fix);
	foreach	($fix as $char)	{
		if (str_has($s,	$char))	{
			return true;
		}
	}
	return false;
}
function charset_fix($string)
{
	// UTF-8 &&	WIN-1250 =>	ISO-8859-2
	// todo: is	checking required? redundant computing?
	if (charset_win_is($string)) {
		$string	= charset_win_fix($string);
	}
	if (charset_utf_is($string)) {
		$string	= charset_utf_fix($string);
	}
	return $string;
}
function charset_win_is($string)
{
	$win = 'π•Ê∆Í ≥£Ò—Û”úåüèøØ';
	$iso = '±°Ê∆Í ≥£Ò—Û”∂¶º¨øØ';
	for	($i=0; $i<strlen($win);	$i++) {
		if ($win{$i} !=	$iso{$i}) {
			if (strstr($string,	$win{$i}) !== false) {
				return true;
			}
		}
	}
	return false;
}
function charset_win_fix($string)
{
	$win = 'π•Ê∆Í ≥£Ò—Û”úåüèøØ';
	$iso = '±°Ê∆Í ≥£Ò—Û”∂¶º¨øØ';
	$srh = array();
	$rpl = array();
	for	($i	= 0; $i	< strlen($win);	$i++) {
		if ($win{$i} !=	$iso{$i}) {
			$srh[] = $win{$i};
			$rpl[] = $iso{$i};
		}
	}
	$string	= str_replace($srh,	$rpl, $string);
	return $string;
}
function charset_utf_is($string)
{
	$utf_iso = array(
	   "\xc4\x85" => "\xb1",
	   "\xc4\x84" => "\xa1",
	   "\xc4\x87" => "\xe6",
	   "\xc4\x86" => "\xc6",
	   "\xc4\x99" => "\xea",
	   "\xc4\x98" => "\xca",
	   "\xc5\x82" => "\xb3",
	   "\xc5\x81" => "\xa3",
	   "\xc3\xb3" => "\xf3",
	   "\xc3\x93" => "\xd3",
	   "\xc5\x9b" => "\xb6",
	   "\xc5\x9a" => "\xa6",
	   "\xc5\xba" => "\xbc",
	   "\xc5\xb9" => "\xac",
	   "\xc5\xbc" => "\xbf",
	   "\xc5\xbb" => "\xaf",
	   "\xc5\x84" => "\xf1",
	   "\xc5\x83" => "\xd1",
		// xmlhttprequest utf-8	encoding
	   "%u0104"	=> "\xA1",
	   "%u0106"	=> "\xC6",
	   "%u0118"	=> "\xCA",
	   "%u0141"	=> "\xA3",
	   "%u0143"	=> "\xD1",
	   "%u00D3"	=> "\xD3",
	   "%u015A"	=> "\xA6",
	   "%u0179"	=> "\xAC",
	   "%u017B"	=> "\xAF",
	   "%u0105"	=> "\xB1",
	   "%u0107"	=> "\xE6",
	   "%u0119"	=> "\xEA",
	   "%u0142"	=> "\xB3",
	   "%u0144"	=> "\xF1",
	   "%u00D4"	=> "\xF3",
	   "%u015B"	=> "\xB6",
	   "%u017A"	=> "\xBC",
	   "%u017C"	=> "\xBF"
	);
	foreach	($utf_iso as $k	=> $v) {
		if (strpos($string,	$k)	!==	false) {
			return true;
		}
	}
	return false;
}
function charset_utf_fix($string)
{
	$utf_iso = array(
	   "\xc4\x85" => "\xb1",
	   "\xc4\x84" => "\xa1",
	   "\xc4\x87" => "\xe6",
	   "\xc4\x86" => "\xc6",
	   "\xc4\x99" => "\xea",
	   "\xc4\x98" => "\xca",
	   "\xc5\x82" => "\xb3",
	   "\xc5\x81" => "\xa3",
	   "\xc3\xb3" => "\xf3",
	   "\xc3\x93" => "\xd3",
	   "\xc5\x9b" => "\xb6",
	   "\xc5\x9a" => "\xa6",
	   "\xc5\xba" => "\xbc",
	   "\xc5\xb9" => "\xac",
	   "\xc5\xbc" => "\xbf",
	   "\xc5\xbb" => "\xaf",
	   "\xc5\x84" => "\xf1",
	   "\xc5\x83" => "\xd1",
		// xmlhttprequest uses different encoding
	   "%u0104"	=> "\xA1",
	   "%u0106"	=> "\xC6",
	   "%u0118"	=> "\xCA",
	   "%u0141"	=> "\xA3",
	   "%u0143"	=> "\xD1",
	   "%u00D3"	=> "\xD3",
	   "%u015A"	=> "\xA6",
	   "%u0179"	=> "\xAC",
	   "%u017B"	=> "\xAF",
	   "%u0105"	=> "\xB1",
	   "%u0107"	=> "\xE6",
	   "%u0119"	=> "\xEA",
	   "%u0142"	=> "\xB3",
	   "%u0144"	=> "\xF1",
	   "%u00D4"	=> "\xF3",
	   "%u015B"	=> "\xB6",
	   "%u017A"	=> "\xBC",
	   "%u017C"	=> "\xBF"
	);
	return str_replace(array_keys($utf_iso), array_values($utf_iso), $string);
}
function str_starts_with($str, $start, $ignore_case	= false)
{
	if ($ignore_case) {
		$str = str_upper($str);
		$start = str_upper($start);
	}
	if (!strlen($str) && !strlen($start)) {
		return true;
	}
	if (!strlen($start)) {
		trigger_error('str_starts_with() failed, start arg cannot be empty', E_USER_ERROR);
	}
	if (strlen($start) > strlen($str)) {
		return false;
	}
	for	($i	= 0; $i	< strlen($start); $i++)	{
		if ($start{$i} != $str{$i})	{
			return false;
		}
	}
	return true;
}
function str_ends_with($str, $end, $ignore_case	= false)
{
	if ($ignore_case) {
		$str = str_upper($str);
		$end = str_upper($end);
	}
	if (!strlen($str) && !strlen($end))	{
		return true;
	}
	if (!strlen($end)) {
		trigger_error('str_ends_with() failed, end arg cannot be empty', E_USER_ERROR);
	}
	if (strlen($end) > strlen($str)) {
		return false;
	}
	return str_starts_with(strrev($str), strrev($end));
	return true;
}
function str_cut_start($str, $start)
{
	if (str_starts_with($str, $start)) {
		$str = substr($str,	strlen($start));
	}
	return $str;
}
function str_cut_end($str, $end)
{
	if (str_ends_with($str,	$end)) {
		$str = substr($str,	0, -strlen($end));
	}
	return $str;
}
function file_get($file)
{
	return file_get_contents($file);
}
function file_put($file, $s)
{
	$fp	= fopen($file, 'wb') or	trigger_error('fopen() failed: '.$file,	E_USER_ERROR);
	if ($fp) {
		fwrite($fp,	$s);
		fclose($fp);
	}
}
function file_date($file)
{
	return date('Y-m-d H:i:s', filemtime($file));
}
function dir_exists($dir)
{
	return file_exists($dir) &&	!is_file($dir);
}
function dir_delete_old_files($dir,	$ext = array(),	$sec)
{
	// older than x	seconds
	$files = dir_read($dir,	null, $ext);
	$time =	time() - $sec;
	foreach	($files	as $file) {
		if (file_time($file) < $time) {
			unlink($file);
		}
	}
}
global $_error,	$_error_style;
$_error	= array();
$_error_style =	'';

function error($msg	= null)
{
	if (isset($msg)	&& func_num_args() > 1)	{
		$args =	func_get_args();
		$msg = call_user_func_array('sprintf', $args);
	}
	global $_error,	$_error_style;
	if (isset($msg)) {
		$_error[] =	$msg;
	}
	if (!count($_error)) {
		return null;
	}
	if (count($_error) == 1) {
		return sprintf('<div class="error" style="%s">%s</div>', $_error_style,	$_error[0]);
	}
	$ret = '<div class="error" style="'.$_error_style.'">Following errors appeared:<ul>';
	foreach	($_error as	$msg) {
		$ret .=	sprintf('<li>%s</li>', $msg);
	}
	$ret .=	'</ul></div>';
	return $ret;
}
function timestamp($time, $span	= true)
{
	$time_base = $time;
	$time =	substr($time, 0, 16);
	$time2 = substr($time, 0, 10);
	$today = date('Y-m-d');
	$yesterday = date('Y-m-d', time()-3600*24);
	if ($time2 == $today) {
		if (substr($time_base, -8) == '00:00:00') {
			$time =	'Today';
		} else {
			$time =	'Today'.substr($time, -6);
		}
	} else if ($time2 == $yesterday) {
		$time =	'Yesterday'.substr($time, -6);
	}
	return '<span style="white-space: nowrap;">'.$time.'</span>';
}
function str_lower($str)
{
	/* strtolower iso-8859-2 compatible	*/
	$lower = str_array(iso_chars_lower());
	$upper = str_array(iso_chars_upper());
	$str = str_replace($upper, $lower, $str);
	$str = strtolower($str);
	return $str;
}
function str_upper($str)
{
	/* strtoupper iso-8859-2 compatible	*/
	$lower = str_array(iso_chars_lower());
	$upper = str_array(iso_chars_upper());
	$str = str_replace($lower, $upper, $str);
	$str = strtoupper($str);
	return $str;
}
function str_array($str)
{
	$arr = array();
	for	($i	= 0; $i	< strlen($str);	$i++) {
		$arr[$i] = $str{$i};
	}
	return $arr;
}
function iso_chars()
{
	return iso_chars_lower().iso_chars_upper();
}
function iso_chars_lower()
{
	return '±ÊÍ≥ÒÛ∂ºø';
}
function iso_chars_upper()
{
	return '°∆ £—”¶¨Ø';
}
function array_first_key($arr)
{
	$arr2 =	$arr;
	reset($arr);
	list($key, $val) = each($arr);
	return $key;
}
function array_first($arr)
{
	return array_first_value($arr);
}
function array_first_value($arr)
{
	$arr2 =	$arr;
	return array_shift($arr2);
}
function array_col_values($arr,	$col)
{
	$ret = array();
	foreach	($arr as $k	=> $row) {
		$ret[] = $row[$col];
	}
	return $ret;
}
function array_col_values_unique($arr, $col)
{
	return array_unique(array_col_values($arr, $col));
}
function array_col_match($rows,	$col, $pattern)
{
	if (!count($rows)) {
		trigger_error('array_col_match(): array	is empty', E_USER_ERROR);
	}
	$ret = true;
	foreach	($rows as $row)	{
		if (!preg_match($pattern, $row[$col])) {
			return false;
		}
	}
	return true;
}
function array_col_match_unique($rows, $col, $pattern)
{
	if (!array_col_match($rows,	$col, $pattern)) {
		return false;
	}
	return count($rows)	== count(array_col_values_unique($rows,	$col));
}
function redirect($url)
{
	$url = url($url);
	header("Location: $url");
	exit;
}
function redirect_notify($url, $msg)
{
	if (strpos($msg, '<') === false) {
		$msg = sprintf('<b>%s</b>',	$msg);
	}
	cookie_set('flash_notify', $msg);
	redirect($url);
}
function redirect_ok($url, $msg)
{
	if (strpos($msg, '<') === false) {
		$msg = sprintf('<b>%s</b>',	$msg);
	}
	cookie_set('flash_ok', $msg);
	redirect($url);
}
function redirect_error($url, $msg)
{
	if (strpos($msg, '<') === false) {
		$msg = sprintf('<b>%s</b>',	$msg);
	}
	cookie_set('flash_error', $msg);
	redirect($url);
}
function flash()
{
	static $is_style = false;

	$flash_error = cookie_get('flash_error');
	$flash_ok =	cookie_get('flash_ok');
	$flash_notify =	cookie_get('flash_notify');

	$flash_error = filter_allow_tags($flash_error, '<b><i><u><br><span>');
	$flash_ok =	filter_allow_tags($flash_ok, '<b><i><u><br><span>');
	$flash_notify =	filter_allow_tags($flash_notify, '<b><i><u><br><span>');

	if (!($flash_error || $flash_ok	|| $flash_notify)) {
		return false;
	}

	ob_start();
	?>

	<?php if (!$is_style): ?>
		<style type="text/css">
		#flash { background: #ffffd7; padding: 0.3em; padding-bottom: 0.15em; border: #ddd 1px solid; margin-bottom: 1em; }
		#flash div { padding: 0em 0em; }
		#flash table { font-weight:	normal;	}
		#flash td {	text-align:	left; }
		</style>
	<?php endif; ?>

	<div id="flash"	ondblclick="document.getElementById('flash').style.display='none';">
		<table width="100%"	ondblclick="document.getElementById('flash').style.display='none';"><tr>
		<td	style="line-height:	14px;"><?= $flash_error	? $flash_error : ($flash_ok	? $flash_ok	: $flash_notify); ?></td></tr></table>
	</div>

	<?php
	$cont =	ob_get_contents();
	ob_end_clean();

	if ($flash_error) cookie_del('flash_error');
	else if	($flash_ok)	cookie_del('flash_ok');
	else if	($flash_notify)	cookie_del('flash_notify');

	$is_style =	true;

	return $cont;
}
function filter($post, $filters)
{
	if (is_string($filters))
	{
		$filter	= $filters;
		$func =	'filter_'.$filter;
		foreach	($post as $key => $val)	{
			$post[$key]	= call_user_func($func,	$post[$key]);
		}
		return $post;
	}
	foreach	($filters as $key => $filter)
	{
		if (!array_key_exists($key,	$post))	{
			return trigger_error(sprintf('filter() failed. Key missing = %s.', $key), E_USER_ERROR);
		}
		$func =	'filter_'.$filter;
		if (!function_exists($func)) {
			return trigger_error(sprintf('filter() failed. Filter missing =	%s.', $func), E_USER_ERROR);
		}
		$post[$key]	= call_user_func($func,	$post[$key]);
	}
	return $post;
}
function filter_html($s)
{
	if (req_gpc_has($s)) {
		$s = html_tags_undo($s);
	}
	return html(trim($s));
}
function filter_allow_tags($s, $allow)
{
	if (req_gpc_has($s)) {
		$s = html_tags_undo($s);
	}
	return html_allow_tags($s, $allow);
}
function filter_allow_html($s)
{
	global $SafeHtml;
	if (!isset($SafeHtml)) {
		include_once 'inc/SafeHtml.php';
	}
	if (req_gpc_has($s)) {
		$s = html_tags_undo($s);
	}
	if (in_array(trim(strtolower($s)), array('<br>', '<p>&nbsp;</p>')))	{
		return '';
	}
	$SafeHtml->clear();
	$s = $SafeHtml->parse($s);
	return trim($s);
}
function filter_allow_html_script($s)
{
	if (in_array(trim(strtolower($s)), array('<br>', '<p>&nbsp;</p>')))	{
		return '';
	}
	if (req_gpc_has($s)) {
		$s = html_tags_undo($s);
	}
	return trim($s);
}
function filter_editor($s)
{
	return filter_allow_html($s);
}
function date_now()
{
	return date('Y-m-d H:i:s');
}
function guess_pk($rows)
{
	if (!count($rows)) {
		return false;
	}
	$patterns =	array('#^\d+$#', '#^[^\s]+$#');
	$row = array_first($rows);
	foreach	($patterns as $pattern)
	{
		foreach	($row as $col => $v) {
			if ($v && preg_match($pattern, $v))	{
				if (array_col_match_unique($rows, $col,	$pattern)) {
					return $col;
				}
			}
		}
	}
	return false;
}
function layout_start($title='')
{
	global $page_charset;
	$flash = flash();
	?>

	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML	4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
		<meta http-equiv="Content-Type"	content="text/html;	charset=<?=$page_charset;?>">
		<title><?=$title;?></title>
		<? if (file_exists('favicon.ico')):	?><link	rel="icon" type="image/x-icon" href="favicon.ico"><? endif;	?>
		<script>
		function $(id)
		{
			if (typeof id == 'string') return document.getElementById(id);
			return id;
		}
		</script>
	</head>
	<body>

	<? layout(); ?>

	<? if ($flash) { echo $flash; }	?>

	<?php
}
function layout_end()
{
	?>
	<? powered_by(); ?>
	</body>
	</html>
	<?php
}
function powered_by()
{
	?>
		<div style="text-align:	center;	margin-top:	2em; border-top: #ccc 1px solid; padding-top: 0.5em;">Powered by <a	href="http://www.gosu.pl/dbkiss/">DBKiss - php database	browser</a></div>
	<?
}

?>
<?php if (rget('import')): ?>

	<?php

	// ----------------------------------------------------------------
	// IMPORT
	// ----------------------------------------------------------------

	?>

	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML	4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
		<meta http-equiv="Content-Type"	content="text/html;	charset=<?=$page_charset;?>">
		<title><?=$db_name;?> &gt; Import</title>
		<? if (file_exists('favicon.ico')):	?><link	rel="icon" type="image/x-icon" href="favicon.ico"><? endif;	?>
	</head>
	<body>

	<?php layout();	?>
	<h1><a style="<?=$db_name_style;?>"	href="<?=$_SERVER['PHP_SELF'];?>"><?=$db_name;?></a> &gt; Import</h1>
	<?php conn_info(); ?>

	<?php $files = sql_files();	?>

	<?php if (count($files)): ?>
		<form action="<?=$_SERVER['PHP_SELF'];?>" method="post">
		<table class="none"	cellspacing="0"	cellpadding="0">
		<tr>
			<td>SQL	file:</th>
			<td><select	name="sqlfile"><option value=""	selected="selected"></option><?=options($files);?></select></td>
			<td><input type="checkbox" name="ignore_errors"	id="ignore_errors" value="1"></td>
			<td><label for="ignore_errors">ignore errors</label></td>
			<td><input type="checkbox" name="transaction" id="transaction" value="1"></td>
			<td><label for="transaction">transaction</label></td>
			<td><input type="checkbox" name="force_myisam" id="force_myisam" value="1"></td>
			<td><label for="force_myisam">force	myisam</label></td>
			<td><input type="text" size="5"	name="query_start" value=""></td>
			<td>query start</td>
			<td><input type="submit" value="Import"></td>
		</tr>
		</table>
		</form>
		<br>
	<?php else:	?>
		No sql files found in current directory.
	<?php endif; ?>

	<? powered_by(); ?>

	</body></html>

<?php exit;	endif; ?>
<?php if ('editrow'	== @$_GET['action']): ?>
<?
	function dbkiss_filter_id($id)
	{
		if (preg_match('#^[_a-z][a-z0-9_]*$#i',	$id)) {
			return $id;
		}
		return false;
	}

	$get = rget(array(
		'table'	=> 'str',
		'pk' =>	'str',
		'id' =>	'str'
	));

	$get['table'] =	html_once($get['table']);
	$get['pk'] = html_once($get['pk']);

	$title_edit	= sprintf('Edit	row	(%s=%s)', $get['pk'], $get['id']);
	$title = ' &gt;	'.$get['table'].' &gt; '.$title_edit;

	if (!dbkiss_filter_id($get['table'])) {
		error('Invalid table name');
	}
	if (!dbkiss_filter_id($get['pk']))	{
		error('Invalid pk');
	}

	$row = false;

	if (!error())
	{
		$test =	db_row("SELECT * FROM {$get['table']}");
		if ($test) {
			if (!array_key_exists($get['pk'], $test)) {
				error('Invalid pk');
			}
		}
		if (!error())
		{
			$query = db_bind("SELECT * FROM	{$get['table']}	WHERE {$get['pk']} = %0", $get['id']);
			$query = db_limit($query, 0, 2);
			$rows =	db_list($query);
			if (count($rows) > 1) {
				error('Invalid pk: found more than one row with	given id');
			} else if (count($rows)	== 0) {
				error('Row not found');
			} else {
				$row = $rows[0];
				$row_id	= $row[$get['pk']];
			}
		}
	}

	if ($row) {
		$types = table_types2($get['table']);
	}

	$edit_actions_assoc	= array(
		'update' =>	'Update',
		'update_pk'	=> 'Overwrite pk',
		'insert' =>	'Copy row (insert)',
		'delete' =>	'Delete'
	);

	$edit_action = rpost('dbkiss_action');

	if (req_is_get())
	{
		$edit_action = array_first_key($edit_actions_assoc);
		$post =	$row;
	}

	if (req_is_post())
	{
		if (!array_key_exists($edit_action,	$edit_actions_assoc)) {
			$edit_action = '';
			error('Invalid action');
		}

		$post =	array();
		foreach	($row as $k	=> $v) {
			if (array_key_exists($k, $_POST)) {
				$val = (string)	$_POST[$k];
				if ('null' == $val)	{
					$val = null;
				}
				if ('int' == $types[$k]) {
					if (!strlen($val)) {
						$val = null;
					}
					if (!(preg_match('#^-?\d+$#', $val)	|| is_null($val))) {
						error('%s: invalid value', $k);
					}
				}
				if ('float'	== $types[$k]) {
					if (!strlen($val)) {
						$val = null;
					}
					$val = str_replace(',',	'.', $val);
					if (!(is_numeric($val) || is_null($val))) {
						error('%s: invalid value', $k);
					}
				}
				if ('time' == $types[$k]) {
					if (!strlen($val)) {
						$val = null;
					}
					if ('now' == $val) {
						$val = date_now();
					}
				}
				$post[$k] =	$val;
			} else {
				error('Missing key:	%s in POST', $k);
			}
		}

		if ('update' ==	$edit_action)
		{
			if ($post[$get['pk']] != $row[$get['pk']]) {
				error('%s: cannot change pk	on UPPDATE', $get['pk']);
			}
		}
		if ('update_pk'	== $edit_action)
		{
			if ($post[$get['pk']] == $row[$get['pk']]) {
				error('%s: selected	action Overwrite pk, but pk	value has not changed',	$get['pk']);
			}
		}
		if ('insert' ==	$edit_action)
		{
			if (strlen($post[$get['pk']])) {
				$test =	db_row("SELECT * FROM {$get['table']} WHERE	{$get['pk']} = %0",	array($post[$get['pk']]));
				if ($test) {
					error('%s: there is	already	a record with that id',	$get['pk']);
				}
			}
		}

		if (!error())
		{
			$post2 = $post;
			if ('update' ==	$edit_action)
			{
				unset($post2[$get['pk']]);
				db_update($get['table'], $post2, array($get['pk'] => $row_id));
				if (db_error())	{
					error('<font color="red"><b>DB error</b></font>: '.db_error());
				} else {
					redirect_ok(self(),	'Row updated');
				}
			}
			if ('update_pk'	== $edit_action)
			{
				@db_update($get['table'], $post2, array($get['pk'] => $row_id));
				if (db_error())	{
					error('<font color="red"><b>DB error</b></font>: '.db_error());
				} else {
					$url = url(self(), array('id' => $post[$get['pk']]));
					redirect_ok($url, 'Row updated (pk overwritten)');
				}
			}
			if ('insert' ==	$edit_action)
			{
				$new_id	= false;
				if (!strlen($post2[$get['pk']])) {
					unset($post2[$get['pk']]);
				} else {
					$new_id	= $post2[$get['pk']];
				}
				@db_insert($get['table'], $post2);
				if (db_error())	{
					error('<font color="red"><b>DB error</b></font>: '.db_error());
				} else {
					if (!$new_id) {
						$new_id	= db_insert_id($get['table'], $get['pk']);
					}
					$url = url(self(), array('id'=>$new_id));
					$msg = sprintf('Row	inserted (%s=%s)', $get['pk'], $new_id);
					redirect_ok($url, $msg);
				}
			}
			if ('delete' ==	$edit_action)
			{
				@db_exe("DELETE	FROM {$get['table']} WHERE {$get['pk']}	= %0", $get['id']);
				if (db_error())	{
					error('<font color="red"><b>DB error</b></font>: '.db_error());
				} else {
					redirect_ok(self(),	'Row deleted');
				}
			}
		}
	}

	?>
<? layout_start($title_edit); ?>
	<h1><span style="<?=$db_name_style;?>"><?=$db_name;?></span><?=$title;?></h1>

	<?=error();?>

	<? if ($row): ?>

		<form action="<?=self();?>"	method="post">

		<?=radio_assoc($edit_action, $edit_actions_assoc, 'dbkiss_action');?></td>
		<br>

		<table cellspacing="1" class="ls ls2">
		<? foreach ($post as $k	=> $v):	if (is_null($v)) { $v =	'null';	} ?>
			<tr>
				<th><?=$k;?>:</th>
				<td>
					<? if ('int' ==	$types[$k]): ?>
						<input type="text" name="<?=$k;?>" value="<?=html_once($v);?>" size="11">
					<? elseif ('char' == $types[$k]): ?>
						<input type="text" name="<?=$k;?>" value="<?=html_once($v);?>" size="50">
					<? elseif ('text' == $types[$k]	|| str_has($types[$k], 'blob')): ?>
						<textarea name="<?=$k;?>" cols="80"	rows="<?=$k=='notes'?10:10;?>"><?=html_once($v);?></textarea>
					<? else: ?>
						<input type="text" name="<?=$k;?>" value="<?=html_once($v);?>" size="30">
					<? endif; ?>
				</td>
				<td	valign="top"><?=$types[$k];?></td>
			</tr>
		<? endforeach; ?>
		<tr>
			<td	colspan="3"	class="none">
				<input type="submit" wait="1" block="1"	class="button" value="Edit">
			</td>
		</tr>
		</table>

		</form>

	<? endif; ?>

	<? layout_end(); ?>

<?php exit;	endif; ?>
<?php if (@$_GET['execute_sql']): ?>
<?php

function query_color($query)
{
	$color = 'red';
	$words = array('SELECT', 'UPDATE', 'DELETE', 'FROM', 'LIMIT', 'OFFSET',	'AND', 'LEFT JOIN',	'WHERE', 'SET',
		'ORDER BY',	'GROUP BY',	'GROUP', 'DISTINCT', 'COUNT', 'COUNT\(\*\)', 'IS', 'NULL', 'IS NULL', 'AS',	'ON', 'INSERT INTO', 'VALUES', 'BEGIN',	'COMMIT', 'CASE', 'WHEN', 'THEN', 'END', 'ELSE', 'IN', 'NOT', 'LIKE', 'ILIKE', 'ASC', 'DESC', 'LOWER', 'UPPER');
	$words = implode('|', $words);

	$query = preg_replace("#^({$words})(\s)#i",	'<font color="'.$color.'">$1</font>$2',	$query);
	$query = preg_replace("#(\s)({$words})$#i",	'$1<font color="'.$color.'">$2</font>',	$query);
	// replace twice, some words when preceding	other are not replaced
	$query = preg_replace("#([\s\(\),])({$words})([\s\(\),])#i", '$1<font color="'.$color.'">$2</font>$3', $query);
	$query = preg_replace("#([\s\(\),])({$words})([\s\(\),])#i", '$1<font color="'.$color.'">$2</font>$3', $query);
	$query = preg_replace("#^($words)$#i", '<font color="'.$color.'">$1</font>', $query);

	preg_match_all('#<font[^>]+>('.$words.')</font>#i',	$query,	$matches);
	foreach	($matches[0] as	$k => $font) {
		$font2 = str_replace($matches[1][$k], strtoupper($matches[1][$k]), $font);
		$query = str_replace($font,	$font2,	$query);
	}

	return $query;
}
function query_upper($sql)
{
	return $sql;
	// todo: don't upper quoted	' and '	values
	$queries = preg_split("#;(\s*--[ \t\S]*)?(\r\n|\n|\r)#U", $sql);
	foreach	($queries as $k	=> $query) {
		$strip = query_strip($query);
		$color = query_color($strip);
		$sql = str_replace($strip, $color, $sql);
	}
	$sql = preg_replace('#<font	color="\w+">([^>]+)</font>#iU',	'$1', $sql);
	return $sql;
}
function html_spaces($string)
{
	$inside_tag	= false;
	for	($i	= 0; $i	< strlen($string); $i++)
	{
		$c = $string{$i};
		if ('<'	== $c) {
			$inside_tag	= true;
		}
		if ('>'	== $c) {
			$inside_tag	= false;
		}
		if (' '	== $c && !$inside_tag) {
			$string	= substr($string, 0, $i).'&nbsp;'.substr($string, $i+1);
			$i += strlen('&nbsp;')-1;
		}
	}
	return $string;
}
function query_cut($query)
{
	// removes sub-queries and string values from query
	$brace_start = '(';
	$brace_end = ')';
	$quote = "'";
	$inside_brace =	false;
	$inside_quote =	false;
	$depth = 0;
	$ret = '';
	$query = str_replace('\\\\', '', $query);

	for	($i	= 0; $i	< strlen($query); $i++)
	{
		$prev_char = isset($query{$i-1}) ? $query{$i-1}	: null;
		$char =	$query{$i};
		if ($char == $brace_start) {
			if (!$inside_quote)	{
				$depth++;
			}
		}
		if ($char == $brace_end) {
			if (!$inside_quote)	{
				$depth--;
				if ($depth == 0) {
					$ret .=	'(...)';
				}
				continue;
			}
		}
		if ($char == $quote) {
			if ($inside_quote) {
				if ($prev_char != '\\')	{
					$inside_quote =	false;
					if (!$depth) {
						$ret .=	"'...'";
					}
					continue;
				}
			} else {
				$inside_quote =	true;
			}
		}
		if (!$depth	&& !$inside_quote) {
			$ret .=	$char;
		}
	}
	return $ret;
}
function table_from_query($query)
{
	if (preg_match('#\sFROM\s+"?(\w+)"?#i',	$query,	$match)) {
		$cut = query_cut($query);
		if (preg_match('#\sFROM\s+"?(\w+)"?#i',	$cut, $match2))	{
			$table = $match2[1];
		} else {
			$table = $match[1];
		}
	} else if (preg_match('#UPDATE\s+"?(\w+)"?#i', $query, $match))	{
		$table = $match[1];
	} else if (preg_match('#INSERT\s+INTO\s+"?(\w+)"?#', $query, $match)) {
		$table = $match[1];
	} else {
		$table = false;
	}
	return $table;
}
function is_select($query)
{
	return preg_match('#^\s*SELECT\s+#i', $query);
}
function query_strip($query)
{
	// strip comments and ';' from the end of query
	$query = trim($query);
	if (str_ends_with($query, ';'))	{
		$query = str_cut_end($query, ';');
	}
	$lines = preg_split("#(\r\n|\n|\r)#", $query);
	foreach	($lines	as $k => $line)	{
		$line =	trim($line);
		if (!$line || str_starts_with($line, '--'))	{
			unset($lines[$k]);
		}
	}
	$query = implode("\r\n", $lines);
	return $query;
}
function listing($base_query, $md5_get = false)
{
	global $db_driver, $db_link;

	$md5_i = false;
	if ($md5_get) {
		preg_match('#_(\d+)$#',	$md5_get, $match);
		$md5_i = $match[1];
	}

	$base_query	= trim($base_query);
	$base_query	= str_cut_end($base_query, ';');

	$query = $base_query;
	$ret = array('msg'=>'',	'error'=>'', 'data_html'=>false);
	$limit = 25;
	$offset	= (int)	@$_GET['offset'];
	$page =	floor($offset /	$limit + 1);

	if ($query)	{
		if (is_select($query) && !preg_match('#\s+LIMIT\s+\d+#i', $query)) {
			$query = db_limit($query, $offset, $limit);
		} else {
			$limit = false;
		}
		$time =	time_start();
		if (!db_is_safe($query,	true)) {
			$ret['error'] =	'Detected UPDATE/DELETE	without	WHERE condition	(put WHERE 1=1 if you want to execute this query)';
			return $ret;
		}
		$rs	= @db_query($query);
		if ($rs) {
			if ($rs	===	true) {
				if ('mysql'	== $db_driver)
				{
					$affected =	mysql_affected_rows($db_link);
					$time =	time_end($time);
					$ret['data_html'] =	'<b>'.$affected.'</b> rows affected.<br>Time: <b>'.$time.'</b> sec';
					return $ret;
				}
			} else {
				if ('pgsql'	== $db_driver)
				{
					$affected =	@pg_affected_rows($rs);
					if ($affected || preg_match('#^\s*(DELETE|UPDATE)\s+#i', $query)) {
						$time =	time_end($time);
						$ret['data_html'] =	'<p><b>'.$affected.'</b> rows affected.	Time: <b>'.$time.'</b> sec</p>';
						return $ret;
					}
				}
			}

			$rows =	array();
			while ($row	= db_row($rs)) {
				$rows[]	= $row;
				if ($limit)	{
					if (count($rows) ==	$limit)	{ break; }
				}
			}
			db_free($rs);

			if (is_select($base_query))	{
				$found = @db_one("SELECT COUNT(*) FROM ($base_query) AS	sub");
				if (!is_numeric($found)	|| (count($rows) &&	!$found)) {
					global $COUNT_ERROR;
					$COUNT_ERROR = ' (COUNT	ERROR) ';
					$found = count($rows);
				}
			} else {
				if (count($rows)) {
					$found = count($rows);
				} else {
					$found = false;
				}
			}
			if ($limit)	{
				$pages = ceil($found / $limit);
			} else {
				$pages = 1;
			}
			$time =	time_end($time);

		} else {
			$ret['error'] =	db_error();
			return $ret;
		}
	} else {
		$ret['error'] =	'No	query found.';
		return $ret;
	}

	ob_start();
?>
	<? if (is_numeric($found)):	?>
		<p>
			Found: <b><?=$found;?></b><?=@$GLOBALS['COUNT_ERROR'];?>.
			Time: <b><?=$time;?></b> sec.
			<?
				$params	= array('md5'=>$md5_get, 'offset'=>(int)@$_GET['offset']);
				if (rget('only_marked')	|| rpost('only_marked')) { $params['only_marked'] =	1; }
				if (rget('only_select')	|| rpost('only_select')) { $params['only_select'] =	1; }
			?>
			/ <a href="<?=url(self(), $params);?>">Refetch</a>
		</p>
	<? else: ?>
		<p>Result: <b>OK</b>. Time:	<b><?=$time;?></b> sec</p>
	<? endif; ?>

	<?php if (is_numeric($found)): ?>

		<?php if ($pages > 1): ?>
		<p>
			<?php if ($page	> 1): ?>
				<? $ofs	= ($page-1)*$limit-$limit; ?>
				<?
					$params	= array('md5'=>$md5_get, 'offset'=>$ofs);
					if (rget('only_marked')	|| rpost('only_marked')) { $params['only_marked'] =	1; }
					if (rget('only_select')	|| rpost('only_select')) { $params['only_select'] =	1; }
				?>
				<a href="<?=url(self(),	$params);?>">&lt;&lt; Prev</a> &nbsp;
			<?php endif; ?>
			Page <b><?=$page;?></b>	of <b><?=$pages;?></b> &nbsp;
			<?php if ($pages > $page): ?>
				<? $ofs	= $page*$limit;	?>
				<?
					$params	= array('md5'=>$md5_get, 'offset'=>$ofs);
					if (rget('only_marked')	|| rpost('only_marked')) { $params['only_marked'] =	1; }
					if (rget('only_select')	|| rpost('only_select')) { $params['only_select'] =	1; }
				?>
				<a href="<?=url(self(),	$params);?>">Next &gt;&gt;</a>
			<?php endif; ?>
		</p>
		<?php endif; ?>

		<script>
		function mark_row(tr)
		{
			var	els	= tr.getElementsByTagName('td');
			if (tr.marked) {
				for	(var i = 0;	i <	els.length;	i++) {
					els[i].style.backgroundColor = '';
				}
				tr.marked =	false;
			} else {
				tr.marked =	true;
				for	(var i = 0;	i <	els.length;	i++) {
					els[i].style.backgroundColor = '#ddd';
				}
			}
		}
		</script>

		<? if ($found):	?>

			<?
				$edit_table	= table_from_query($base_query);
				if ($edit_table) {
					$edit_pk = array_first_key($rows[0]);
					if (is_numeric($edit_pk)) {	$edit_table	= false; }
				}
				if ($edit_table) {
					$types = table_types2($edit_table);
					if ($types && count($types)) {
						if (in_array($edit_pk, array_keys($types)))	{
							if (!array_col_match_unique($rows, $edit_pk, '#^\d+$#')) {
								$edit_pk = guess_pk($rows);
								if (!$edit_pk) {
									$edit_table	= false;
								}
							}
						} else {
							$edit_table	= false;
						}
					} else {
						$edit_table	= false;
					}
				}
				$edit_url =	'';
				if ($edit_table) {
					$edit_url =	url(self(true),	array('action'=>'editrow', 'table'=>$edit_table, 'pk'=>$edit_pk, 'id'=>'%s'));
				}
			?>

			<table class="ls" cellspacing="1">
			<tr>
				<? if ($edit_url): ?><th>#</th><? endif; ?>
				<?php foreach ($rows[0]	as $col	=> $v):	?>
					<th><?=$col;?></th>
				<?php endforeach; ?>
			</tr>
			<?php foreach ($rows as	$row): ?>
			<tr	ondblclick="mark_row(this)">
				<? if ($edit_url): ?>
					<td><a href="javascript:void(0)" onclick="popup('<?=sprintf($edit_url, $row[$edit_pk]);?>',	620, 500)">Edit</a>&nbsp;</td>
				<? endif; ?>
				<?
					$count_cols	= 0;
					foreach	($row as $v) { $count_cols++; }
				?>
				<?php foreach ($row	as $k => $v): ?>
					<?php
						if (preg_match('#^\s*<a[^>]+>[^<]+</a>\s*$#iU',	$v)	&& strlen(strip_tags($v)) <	50)	{
							$v = strip_tags($v,	'<a>');
						} else {
							$v = strip_tags($v);
							$v = str_replace('&nbsp;', ' ',	$v);
							$v = preg_replace('#[ ]+#',	' ', $v);
							if (!@$_GET['full_content']	&& strlen($v) >	50)	{
								if (1 == $count_cols) {
									$v = str_truncate($v, 255);
								} else {
									$v = str_truncate($v, 50);
								}
							}
							$v = html_once($v);
						}
						$nl2br = @$_GET['nl2br'];
						if (@$_GET['full_content'])	{
							$v = str_wrap($v, 80, '<br>');
						}
						if (@$_GET['nl2br']) {
							$v = nl2br($v);
						}
						$v = stripslashes(stripslashes($v));
						if (@$types[$k]	== 'int' &&	(preg_match('#time#i', $k) || preg_match('#date#i',	$k))
							&& preg_match('#^\d+$#', $v))
						{
							$tmp = @date('Y-m-d	H:i', $v);
							if ($tmp) {
								$v = $tmp;
							}
						}
						global $post;
						if (str_has($post['sql'], '@gethostbyaddr')	&& (preg_match('#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#', $v))) {
							$v = $v.'<br>'.@gethostbyaddr($v);
						}
					?>
					<td	onclick="mark_col(this)" <?=$nl2br?'valign="top"':'';?>	nowrap><?=is_null($row[$k])?'-':$v;?></td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
			</table>

		<? endif; ?>

		<?php if ($pages > 1): ?>
		<p>
			<?php if ($page	> 1): ?>
				<? $ofs	= ($page-1)*$limit-$limit; ?>
				<?
					$params	= array('md5'=>$md5_get, 'offset'=>$ofs);
					if (rget('only_marked')	|| rpost('only_marked')) { $params['only_marked'] =	1; }
					if (rget('only_select')	|| rpost('only_select')) { $params['only_select'] =	1; }
				?>
				<a href="<?=url(self(),	$params);?>">&lt;&lt; Prev</a> &nbsp;
			<?php endif; ?>
			Page <b><?=$page;?></b>	of <b><?=$pages;?></b> &nbsp;
			<?php if ($pages > $page): ?>
				<? $ofs	= $page*$limit;	?>
				<?
					$params	= array('md5'=>$md5_get, 'offset'=>$ofs);
					if (rget('only_marked')	|| rpost('only_marked')) { $params['only_marked'] =	1; }
					if (rget('only_select')	|| rpost('only_select')) { $params['only_select'] =	1; }
				?>
				<a href="<?=url(self(),	$params);?>">Next &gt;&gt;</a>
			<?php endif; ?>
		</p>
		<?php endif; ?>

	<?php endif; ?>

<?php
	$cont =	ob_get_contents();
	ob_end_clean();
	$ret['data_html'] =	$cont;
	return $ret;
}

?>
<?php

	// ----------------------------------------------------------------
	// EXECUTE SQL
	// ----------------------------------------------------------------

	set_time_limit(0);

	$template =	@$_GET['template'];
	$msg = '';
	$error = '';
	$top_html =	'';
	$data_html = '';

	$get = rget(array(
		'popup'=> 'int',
		'md5' => 'str',
		'only_marked' => 'bool',
		'only_select' => 'bool'
	));
	$post =	rpost(array(
		'sql' => 'str',
		'perform' => 'str',
		'only_marked' => 'bool',
		'only_select' => 'bool',
		'save_as' => 'str',
		'load_from'	=> 'str'
	));

	if ($get['md5']) {
		$get['only_select']	= true;
		$post['only_select'] = true;
	}

	if ($get['only_marked']) { $post['only_marked']	= 1; }
	if ($get['only_select']) { $post['only_select']	= 1; }

	$sql_dir = false;
	if (defined('DBKISS_SQL_DIR')) {
		$sql_dir = DBKISS_SQL_DIR;
	}

	if ($sql_dir) {
		if (!(dir_exists($sql_dir) && is_writable($sql_dir))) {
			if (!dir_exists($sql_dir) && is_writable('.')) {
				mkdir($sql_dir);
			} else {
				exit('You must create "'.$sql_dir.'" directory with	write permission.');
			}
		}
		if (!file_exists($sql_dir.'/.htacccess')) {
			file_put($sql_dir.'/.htaccess',	'deny from all');
		}
	}

	if ('GET' == $_SERVER['REQUEST_METHOD']) {
		if ($sql_dir)
		{
			if ($get['md5']	&& preg_match('#^(\w{32,32})_(\d+)$#', $get['md5'],	$match)) {
				$md5_i = $match[2];
				$md5_tmp = sprintf($sql_dir.'/zzz_%s.dat', $match[1]);
				$post['sql'] = file_get($md5_tmp);
				$_SERVER['REQUEST_METHOD'] = 'POST';
				$post['perform'] = 'execute';
			} else if ($get['md5'] && preg_match('#^(\w{32,32})$#',	$get['md5'], $match)) {
				$md5_tmp = sprintf($sql_dir.'/zzz_%s.dat', $match[1]);
				$post['sql'] = file_get($md5_tmp);
				$get['md5']	= '';
			} else {
				if ($get['md5']) {
					trigger_error('invalid md5', E_USER_ERROR);
				}
			}
		}
	} else {
		$get['md5']	= '';
	}

	if (str_has($post['sql'], '@nl2br')) {
		$_GET['nl2br'] = 1;
	}
	if (str_has($post['sql'], '@full_content'))	{
		$_GET['full_content'] =	1;
	}

	$post['sql'] = trim($post['sql']);
	$md5 = md5($post['sql']);
	$md5_file =	sprintf($sql_dir.'/zzz_%s.dat',	$md5);
	if ($sql_dir &&	$post['sql']) {
		file_put($md5_file,	$post['sql']);
	}

	if ($sql_dir &&	'save' == $post['perform'] && $post['save_as'] && $post['sql'])
	{
		$post['save_as'] = str_replace('.sql', '', $post['save_as']);
		if (preg_match('#^[\w ]+$#', $post['save_as']))	{
			$file =	$sql_dir.'/'.$post['save_as'].'.sql';
			$overwrite = '';
			if (file_exists($file))	{
				$overwrite = ' - <b>overwritten</b>';
				$bak = $sql_dir.'/zzz_'.$post['save_as'].'_'.md5(file_get($file)).'.dat';
				copy($file,	$bak);
			}
			$msg .=	sprintf('<div>Sql saved: %s	%s</div>', basename($file),	$overwrite);
			file_put($file,	$post['sql']);
		} else {
			error('Saving sql failed: only alphanumeric	chars are allowed');
		}
	}

	if ($sql_dir) {
		$load_files	= dir_read($sql_dir, null, array('.sql'), 'date_desc');
	}
	$load_assoc	= array();
	if ($sql_dir) {
		foreach	($load_files as	$file) {
			$file_path = $file;
			$file =	basename($file);
			$load_assoc[$file] = '('.substr(file_date($file_path), 0, 10).')'.'	' .$file;
		}
	}

	if ($sql_dir &&	'load' == $post['perform'])
	{
		$file =	$sql_dir.'/'.$post['load_from'];
		if (array_key_exists($post['load_from'], $load_assoc) && file_exists($file)) {
			$msg .=	sprintf('<div>Sql loaded: %s (%s)</div>', basename($file), timestamp(file_date($file)));
			$post['sql'] = file_get($file);
			$post['save_as'] = basename($file);
			$post['save_as'] = str_replace('.sql', '', $post['save_as']);
		} else {
			error('<div>File not found:	%s</div>', $file);
		}
	}

	// after load -	md5	may	change
	$md5 = md5($post['sql']);

	if ($sql_dir &&	'load' == $post['perform'] && !error())	{
		$md5_tmp = sprintf($sql_dir.'/zzz_%s.dat', $md5);
		file_put($md5_tmp, $post['sql']);
	}

	$is_sel	= false;

	$queries = preg_split("#;(\s*--[ \t\S]*)?(\r\n|\n|\r)#U", $post['sql']);
	foreach	($queries as $k	=> $query) {
		$query = query_strip($query);
		if (str_starts_with($query,	'@')) {
			$is_sel	= true;
		}
		$queries[$k] = $query;
		if (!trim($query)) { unset($queries[$k]); }
	}

	$sql_assoc = array();
	$sql_selected =	false;
	$i = 0;

	$params	= array(
		'md5' => $md5,
		'only_marked' => $post['only_marked'],
		'only_select' => $post['only_select'],
		'offset' =>	''
	);
	$sql_main_url =	url(self(),	$params);

	foreach	($queries as $query) {
		$i++;
		$query = str_cut_start($query, '@');
		if (!is_select($query))	{
			continue;
		}
		$query = preg_replace('#\s+#', ' ',	$query);
		$params	= array(
			'md5' => $md5.'_'.$i,
			'only_marked' => $post['only_marked'],
			'only_select' => $post['only_select'],
			'offset' =>	''
		);
		$url = url(self(), $params);
		if ($get['md5']	&& $get['md5'] == $params['md5']) {
			$sql_selected =	$url;
		}
		$sql_assoc[$url] = str_truncate(strip_tags($query),	80);
	}

	if ('POST' == $_SERVER['REQUEST_METHOD'])
	{
		if (!$post['perform']) {
			$error = 'No action	selected.';
		}
		if (!$error)
		{
			$time =	time_start();
			switch ($post['perform']) {
				case 'execute':
					$i = 0;
					db_begin();
					$commit	= true;
					foreach	($queries as $query)
					{
						$i++;
						if ($post['only_marked'] &&	!$is_sel) {
							if (!$get['md5']) {	continue; }
						}
						if ($is_sel) {
							if (str_starts_with($query,	'@')) {
								$query = str_cut_start($query, '@');
							} else {
								if (!$get['md5']) {	continue; }
							}
						}
						if ($post['only_select'] &&	!is_select($query))	{
							continue;
						}
						if ($get['md5']	&& $i != $md5_i) {
							continue;
						}
						if ($get['md5']	&& $i == $md5_i) {
							if (!is_select($query))	{
								trigger_error('not select query', E_USER_ERROR);
							}
						}

						$exec =	listing($query,	$md5.'_'.$i);
						$query_trunc = str_truncate(html_once($query), 1000);
						$query_trunc = query_color($query_trunc);
						$query_trunc = nl2br($query_trunc);
						$query_trunc = html_spaces($query_trunc);
						if ($exec['error'])	{
							$exec['error'] = preg_replace('#error:#i', '', $exec['error']);
							$top_html .= sprintf('<div style="background: #ffffd7; padding:	0.5em; border: #ccc	1px	solid; margin-bottom: 1em; margin-top:	1em;"><b style="color:red">Error</b>: %s<div style="margin-top:	0.25em;"><b>Query %s</b>: %s</div></div>', $exec['error'],	$i,	$query_trunc);
							$commit	= false;
							break;
						} else {
							$query_html	= sprintf('<div	class="query"><b style="font-size: 10px;">Query	%s</b>:<div	style="'.$sql_font.' margin-top:	0.35em;">%s</div></div>', $i, $query_trunc);
							$data_html .= $query_html;
							$data_html .= $exec['data_html'];
						}
					}
					if ($commit) {
						db_end();
					} else {
						db_rollback();
					}
					break;
			}
			$time =	time_end($time);
		}
	}

	if ($post['only_marked'] &&	!$is_sel) {
		error('No queries marked');
	}

?>
<? layout_start($db_name.' &gt;	Execute	SQL'); ?>
	<? if ($get['popup']): ?>
		<h1><span style="<?=$db_name_style;?>"><?=$db_name;?></span> &gt; Execute SQL</h1>
	<? else: ?>
		<h1><a style="<?=$db_name_style;?>"	href="<?=$_SERVER['PHP_SELF'];?>"><?=$db_name;?></a> &gt; Execute SQL</h1>
	<? endif; ?>

	<?=error();?>

	<script>
	function sql_submit(form)
	{
		if (form.perform.value.length) {
			return true;
		}
		return false;
	}
	function sql_execute(form)
	{
		form.perform.value='execute';
		form.submit();
	}
	function sql_preview(form)
	{
		form.perform.value='preview';
		form.submit();
	}
	function sql_save(form)
	{
		form.perform.value='save';
		form.submit();
	}
	function sql_load(form)
	{
		if (form.load_from.selectedIndex)
		{
			form.perform.value='load';
			form.submit();
			return true;
		}
		button_clear(form);
		return false;
	}
	</script>

	<? if ($msg): ?>
		<div class="msg"><?=$msg;?></div>
	<? endif; ?>

	<?=$top_html;?>

	<? if (count($sql_assoc)): ?>
		<p>
			SELECT queries:
			<select	name="sql_assoc" onchange="if (this.value.length) location=this.value">
				<option	value="<?=html_once($sql_main_url);?>"></option>
				<?=options($sql_assoc, $sql_selected);?>
			</select>
		</p>
	<? endif; ?>

	<? if ($get['md5']): ?>
		<?=$data_html;?>
	<? endif; ?>

	<form action="<?=$_SERVER['PHP_SELF'];?>?execute_sql=1&popup=<?=$get['popup'];?>" method="post"	onsubmit="return sql_submit(this);"	style="margin-top: 1em;">
	<input type="hidden" name="perform"	value="">
	<div style="margin-bottom: 0.25em;">
		<textarea id="sql_area"	name="sql" class="sql_area"><?=html_once(query_upper($post['sql']));?></textarea>
	</div>
	<table cellspacing="0" cellpadding="0"><tr>
	<td	nowrap>
		<input type="button" wait="1" class="button" value="Execute" onclick="sql_execute(this.form); ">
	</td>
	<td	nowrap>
		&nbsp;
		<input type="button" wait="1" class="button" value="Preview" onclick="sql_preview(this.form); ">
	</td>
	<td	nowrap>
		&nbsp;
		<input type="checkbox" name="only_marked" id="only_marked" value="1" <?=checked($post['only_marked'] ||	$get['only_marked']);?>>
	</td>
	<td	nowrap>
		<label for="only_marked">only marked</label>
	</td>
	<td	nowrap>
		&nbsp;
		<input type="checkbox" name="only_select" id="only_select" value="1" <?=checked($post['only_select'] ||	$get['only_select']);?>>
	</td>
	<td	nowrap>
		<label for="only_select">only SELECT</label>
		&nbsp;&nbsp;&nbsp;
	</td>
	<td	nowrap>
		<input type="text" name="save_as" value="<?=html_once($post['save_as']);?>">
		&nbsp;
	</td>
	<td	nowrap>
		<input type="button" wait="1" class="button" value="Save" onclick="sql_save(this.form);	">
		&nbsp;&nbsp;&nbsp;
	</td>
	<td	nowrap>
		<select	name="load_from" style="width: 140px;"><option value=""></option><?=options($load_assoc);?></select>
		&nbsp;
	</td>
	<td	nowrap>
		<input type="button" wait="1" class="button" value="Load" onclick="return sql_load(this.form);">
	</td>
	</tr></table>
	</form>

	<?

		if ('preview' == $post['perform'])
		{
			echo '<h2>Preview</h2>';
			$i = 0;
			foreach	($queries as $query)
			{
				$i++;
				$query = str_cut_start($query, '@');
				$query = html_once($query);
				$query = query_color($query);
				$query = nl2br($query);
				$query = html_spaces($query);
				printf('<div class="query"><b style="font-size:	10px;">Query %s</b>:<div style="'.$sql_font.' margin-top: 0.35em;">%s</div></div>',	$i,	$query);
			}
		}

	?>

	<? if (!$get['md5']): ?>
		<script>$('sql_area').focus();</script>
		<?=$data_html;?>
	<? endif; ?>

	<? layout_end(); ?>

<?php exit;	endif; ?>
<?php if (@$_GET['viewtable']):	?>

	<?

		set_time_limit(0);

		// ----------------------------------------------------------------
		// VIEW	TABLE
		// ----------------------------------------------------------------

		$table = $_GET['viewtable'];
		$count = db_one("SELECT	COUNT(*) FROM $table");

		$types = table_types2($table);
		$columns = table_columns($table);
		if (!count($columns)) {
			$columns = array_assoc(array_keys($types));
		}
		$columns2 =	$columns;

		foreach	($columns2 as $k =>	$v)	{
			$columns2[$k] =	$v.' ('.$types[$k].')';
		}
		$types_group = table_types_group($types);
		$_GET['search']	= trim(@$_GET['search']);

		$where = '';
		$found = $count;
		if (@$_GET['search']) {
			$search	= $_GET['search'];
			$cols2 = array();

			if (@$_GET['column']) {
				$cols2[] = $_GET['column'];
			} else {
				$cols2 = $columns;
			}
			$where = '';
			$search	= db_escape($search);

			$column_type = '';
			if (!@$_GET['column']) {
				$column_type = @$_GET['column_type'];
			} else {
				$_GET['column_type'] = '';
			}

			$ignore_int	= false;
			$ignore_time = false;

			foreach	($columns as $col)
			{
				if (!@$_GET['column'] && $column_type) {
					if ($types[$col] !=	$column_type) {
						continue;
					}
				}
				if (!$column_type && !is_numeric($search) && str_has($types[$col], 'int')) {
					$ignore_int	= true;
					continue;
				}
				if (!$column_type && is_numeric($search) &&	str_has($types[$col], 'time')) {
					$ignore_time = true;
					continue;
				}
				if (@$_GET['column'] &&	$col !=	$_GET['column']) {
					continue;
				}
				if ($where)	{ $where .=	' OR ';	}
				if (is_numeric($search)) {
					$where .= "$col	= '$search'";
				} else {
					if ('mysql'	== $db_driver) {
						$where .= "$col	LIKE '%$search%'";
					} else if ('pgsql' == $db_driver) {
						$where .= "$col	ILIKE '%$search%'";
					} else {
						trigger_error('db_driver not implemented');
					}
				}
			}
			if (($ignore_int ||	$ignore_time) && !$where) {
				$where .= '	1=2	';
			}
			$where = 'WHERE	'.$where;
		}

		if ($where)	{
			$found = db_one("SELECT	COUNT(*) FROM $table $where");
		}

		$limit = 50;
		$offset	= (int)	@$_GET['offset'];
		$page =	floor($offset /	$limit + 1);
		$pages = ceil($found / $limit);

		$pk	= table_pk($table);

		$order = "ORDER	BY";
		if (@$_GET['order_by'])	{
			$order .= '	'.$_GET['order_by'];
		} else {
			if ($pk) {
				$order .= '	'.$pk;
			} else {
				$order = '';
			}
		}
		if (@$_GET['order_desc']) {	$order .= '	DESC'; }

		$rs	= db_query("SELECT * FROM $table $where	$order LIMIT $limit	OFFSET $offset");

		if ($count && $rs) {
			$rows =	array();
			while ($row	= db_row($rs)) {
				$rows[]	= $row;
			}
			db_free($rs);
			if (count($rows) &&	!array_col_match_unique($rows, $pk,	'#^\d+$#'))	{
				$pk	= guess_pk($rows);
			}
		}
	?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML	4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type"	content="text/html;	charset=<?=$page_charset;?>">
	<title><?=$db_name;?> &gt; Table: <?=$table;?></title>
	<? if (file_exists('favicon.ico')):	?><link	rel="icon" type="image/x-icon" href="favicon.ico"><? endif;	?>
</head>
<body>

	<?php layout();	?>

	<h1><a style="<?=$db_name_style;?>"	href="<?=$_SERVER['PHP_SELF'];?>"><?=$db_name;?></a> &gt; Table: <?=$table;?></h1>

	<?php conn_info(); ?>

	<p>
		<a href="<?=$_SERVER['PHP_SELF'];?>">All tables</a>
		&nbsp;&gt;&nbsp;
		<a href="<?=$_SERVER['PHP_SELF'];?>?viewtable=<?=$table;?>"><b><?=$table;?></b></a>	(<?=$count;?>)
	</p>

	<form action="<?=$_SERVER['PHP_SELF'];?>" method="get" style="margin-bottom: 1em;">
	<input type="hidden" name="viewtable" value="<?=$table;?>">
	<table class="ls" cellspacing="1">
	<tr>
		<td><input type="text" name="search" value="<?=html_once(@$_GET['search']);?>"></td>
		<td><select	name="column"><option value=""></option><?=options($columns2, @$_GET['column']);?></select></td>
		<td><select	name="column_type"><option value=""></option><?=options($types_group, @$_GET['column_type']);?></select></td>
		<td><input type="submit" value="Search"></td>
		<td>
			order by:
			<select	name="order_by"><option	value=""></option><?=options($columns, @$_GET['order_by']);?></select>
			<input type="checkbox" name="order_desc" id="order_desc" value="1" <?=checked(@$_GET['order_desc']);?>>
			<label for="order_desc">desc</label>
		</td>
		<td>
			<input type="checkbox" name="full_content" id="full_content" <?=checked(@$_GET['full_content']);?>>
			<label for="full_content">full content</label>
		</td>
		<td>
			<input type="checkbox" name="nl2br"	id="nl2br" <?=checked(@$_GET['nl2br']);?>>
			<label for="nl2br">nl2br</label>
		</td>
	</tr>
	</table>
	</form>

	<?php if ($count): ?>

		<? if ($count && $count	!= $found):	?>
			<p>Found: <b><?=$found;?></b></p>
		<? endif; ?>

		<?php if ($found): ?>

			<?php if ($pages > 1): ?>
			<p>
				<?php if ($page	> 1): ?>
					<a href="<?=url_offset(($page-1)*$limit-$limit);?>">&lt;&lt; Prev</a> &nbsp;
				<?php endif; ?>
				Page <b><?=$page;?></b>	of <b><?=$pages;?></b> &nbsp;
				<?php if ($pages > $page): ?>
					<a href="<?=url_offset($page*$limit);?>">Next &gt;&gt;</a>
				<?php endif; ?>
			</p>
			<?php endif; ?>

			
			function mark_row(tr)
			{
				var	els	= tr.getElementsByTagName('td');
				if (tr.marked) {
					for	(var i = 0;	i <	els.length;	i++) {
						els[i].style.backgroundColor = '';
					}
					tr.marked =	false;
				} else {
					tr.marked =	true;
					for	(var i = 0;	i <	els.length;	i++) {
						els[i].style.backgroundColor = '#ddd';
					}
				}
			}
			</script>

			<table class="ls" cellspacing="1">
			<tr>
				<? if ($pk): ?><th>#</th><?	endif; ?>
				<?php foreach ($columns	as $col): ?>
					<?
						$params	= array('order_by'=>$col);
						$params['order_desc'] =	0;
						if (rget('order_by') ==	$col) {
							$params['order_desc'] =	rget('order_desc') ? 0 : 1;
						}
					?>
					<th><a style="color: #000;"	href="<?=url(self(), $params);?>"><?=$col;?></a></th>
				<?php endforeach; ?>
			</tr>
			<?php foreach ($rows as	$row): ?>
			<tr	ondblclick="mark_row(this)">
				<? if ($pk): ?>
					<? $edit_url = url(self(true), array('action'=>'editrow', 'table'=>$table, 'pk'=>$pk, 'id'=>$row[$pk])); ?>
					<td><a href="javascript:void(0)" onclick="popup('<?=$edit_url;?>', 620,	500)">Edit</a>&nbsp;</td>
				<? endif; ?>
				<?php foreach ($row	as $k => $v): ?>
					<?php
						if (!@$_GET['full_content']) {
							$v = str_truncate($v, 50);
						}
						$v = html_once($v);
						$nl2br = @$_GET['nl2br'];
						if (@$_GET['full_content'])	{
							$v = str_wrap($v, 80, '<br>');
						}
						if (@$_GET['nl2br']) {
							$v = nl2br($v);
						}
						$v = stripslashes(stripslashes($v));
						if (@$_GET['search']) {
							$search	= @$_GET['search'];
							$search_quote =	preg_quote($search);
							$v = preg_replace('#('.$search_quote.')#i',	'<span style="background: yellow;">$1</span>', $v);
						}
						if ($types[$k] == 'int'	&& (preg_match('#time#i', $k) || preg_match('#date#i', $k))
							&& preg_match('#^\d+$#', $v))
						{
							$tmp = @date('Y-m-d	H:i', $v);
							if ($tmp) {
								$v = $tmp;
							}
						}
					?>
					<td	onclick="mark_col(this)" <?=$nl2br?'valign="top"':'';?>	nowrap><?=is_null($row[$k])?'-':$v;?></td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
			</table>

			<?php if ($pages > 1): ?>
			<p>
				<?php if ($page	> 1): ?>
					<a href="<?=url_offset(($page-1)*$limit-$limit);?>">&lt;&lt; Prev</a> &nbsp;
				<?php endif; ?>
				Page <b><?=$page;?></b>	of <b><?=$pages;?></b> &nbsp;
				<?php if ($pages > $page): ?>
					<a href="<?=url_offset($page*$limit);?>">Next &gt;&gt;</a>
				<?php endif; ?>
			</p>
			<?php endif; ?>

		<?php endif; ?>

	<?php endif; ?>

<? powered_by(); ?>
</body>
</html>
<?php exit;	endif; ?>
<?php if (rget('searchdb')): ?>
<?php

	// ----------------------------------------------------------------
	// SEARCH DB
	// ----------------------------------------------------------------

	$get = rget(array(
		'types'	=> 'arr',
		'search' =>	'str',
		'md5' => 'bool',
		'table_filter' => 'str'
	));
	$get['search'] = trim($get['search']);

	$tables	= list_tables();

	if ($get['table_filter']) {
		foreach	($tables as	$k => $table) {
			if (!str_has($table, $get['table_filter'], $ignore_case	= true)) {
				unset($tables[$k]);
			}
		}
	}

	$all_types = array();
	$colonne  =	array();
	foreach	($tables as	$table)	{
		$types = table_types2($table);
		$columns[$table] = $types;
		$types = array_values($types);
		$all_types = array_merge($all_types, $types);
	}
	$all_types = array_unique($all_types);

	if ($get['search'] && $get['md5']) {
		$get['search'] = md5($get['search']);
	}

?>
<? layout_start(sprintf('%s	&gt; Search', $db_nome)); ?>
	<h1><a style="<?=$db_name_style;?>"	href="<?=$_SERVER['PHP_SELF'];?>"><?=$db_nome;?></a> &gt; Cerca</h1>
	<?php conn_info();	?>

	<form action="<?=$_SERVER['PHP_SELF'];?>" method="get">
	<input type="hidden" name="searchdb" value="1">
	<table class="ls" cellspacing="1">
	<tr>
		<th>Cerca:</th>
		<td>
			<input type="text" name="search" value="<?=html_once($get['search']);?>" size="40">
			<?php if ($get['search'] && $get['md5']): ?>
				md5(<?=html_once(rget('search'));?>)
			<? endif; ?>
			<input type="checkbox" name="md5" id="md5_label" value="1">
			<label for="md5_label">md5</label>
		</td>
	</tr>
	<tr>
		<th>Filtro Tabelle:</th>
		<td><input type="text" name="table_filter" value="<?=html_once($get['table_filter']);?>">
	</tr>
	<tr>
		<th>Colonne:</th>
		<td>
			<? foreach ($all_types as $type): ?>
				<input type="checkbox" id="type_<?=$type;?>" name="types[<?=$type;?>]" value="1" <?=checked(isset($get['types'][$type]));?>>
				<label for="type_<?=$type;?>"><?=$type;?></label>
			<? endforeach; ?>
		</td>
	</tr>
	<tr>
		<td	colspan="2"	class="none">
			<input type="submit" value="Search">
		</td>
	</tr>
	</table>
	</form>

	<? if ($get['search'] && !count($get['types'])): ?>
		<p>No columns selected.</p>
	<? endif; ?>

	<? if ($get['search'] && count($get['types'])):	?>

		<p>Searching <b><?=count($tables);?></b> tables	for: <b><?=html_once($get['search']);?></b></p>

		<? $found_any =	false; ?>

		<? set_time_limit(0); ?>

		<? foreach ($tables	as $table):	
			

				$where = '';
				$cols2 = array();

				$where = '';
				$search	= db_escape($get['search']);

				foreach	($columns[$table] as $col => $type)
				{
					if (!in_array($type, array_keys($get['types']))) {
						continue;
					}
					if ($where)	{
						$where .= '	OR ';
					}
					if (is_numeric($search)) {
						$where .= "$col	= '$search'";
					} else {
						if ('mysql'	== $db_driver) {
							$where .= "$col	LIKE '%$search%'";
						} else if ('pgsql' == $db_driver) {
							$where .= "$col	ILIKE '%$search%'";
						} else {
							trigger_error('db_driver not implemented');
						}
					}
				}

				$found = false;

				if ($where)	{
					$where = 'WHERE	'.$where;
					$found = db_one("SELECT	COUNT(*) FROM $table $where");
				}

				if ($found)	{
					$found_any = true;
				}

		

		
				if ($where && $found) {
					$limit = 10;
					$offset	= 0;
					$pk	= table_pk($table);

					$order = "ORDER	BY $pk";
					$rs	= db_query("SELECT * FROM $table $where	$order LIMIT $limit	OFFSET $offset");

					$rows =	array();
					while ($row	= db_row($rs)) {
						$rows[]	= $row;
					}
					db_free($rs);
					if (count($rows) &&	!array_col_match_unique($rows, $pk,	'#^\d+$#'))	{
						$pk	= guess_pk($rows);
					}
				}
			?>

			<? if ($where && $found): ?>

				<p>
					Table: <a href="<?=$_SERVER['PHP_SELF'];?>?viewtable=<?=$table;?>&search=<?=urlencode($get['search']);?>"><b><?=$table;?></b></a><br>
					Found: <b><?=$found;?></b>
					<? if ($found >	$limit): ?>
						&nbsp;<a href="<?=$_SERVER['PHP_SELF'];?>?viewtable=<?=$table;?>&search=<?=urlencode($get['search']);?>">show all &gt;&gt;</a>
					<? endif; ?>
				</p>

				<table class="ls" cellspacing="1">
				<tr>
					<? if ($pk): ?><th>#</th><?	endif; ?>
					<?php foreach ($columns[$table]	as $col	=> $type): ?>
						<th><?=$col;?></th>
					<?php endforeach; ?>
				</tr>
				<?php foreach ($rows as	$row): ?>
				<tr>
					<? if ($pk): ?>
						<? $edit_url = url(self(true), array('action'=>'editrow', 'table'=>$table, 'pk'=>$pk, 'id'=>$row[$pk])); ?>
						<td><a href="javascript:void(0)" onclick="popup('<?=$edit_url;?>', 620,	500)">Edit</a>&nbsp;</td>
					<? endif; ?>
					<?php foreach ($row	as $k => $v): ?>
						<?php
							$v = str_truncate($v, 50);
							$v = html_once($v);
							$v = stripslashes(stripslashes($v));
							$search	= $get['search'];
							$search_quote =	preg_quote($search);
							if ($columns[$table][$k] ==	'int' && (preg_match('#time#i',	$k)	|| preg_match('#date#i', $k)) && preg_match('#^\d+$#', $v)) {
								$tmp = @date('Y-m-d	H:i', $v);
								if ($tmp) {
									$v = $tmp;
								}
							}
							$v = preg_replace('#('.$search_quote.')#i',	'<span style="background: yellow;">$1</span>', $v);
						?>
						<td	nowrap><?=$v;?></td>
					<?php endforeach; ?>
				</tr>
				<?php endforeach; ?>
				</table>

			<? endif; ?>

		<? endforeach; ?>

		<? if (!$found_any): ?>
			<p>No rows found.</p>
		<? endif; ?>

	<? endif; ?>

	<? layout_end(); ?>
<?php exit;	endif; ?>

<?php

// ----------------------------------------------------------------
// LIST	TABLES
// ----------------------------------------------------------------

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML	4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type"	content="text/html;	charset=<?=$page_charset;?>">
	<title><?=$db_name;?></title>
	<? if (file_exists('favicon.ico')):	?><link	rel="icon" type="image/x-icon" href="favicon.ico"><? endif;	?>
</head>
<body>

<?php layout();	?>
<h1	 style="<?=$db_name_style;?>"><?=$db_nome;?></h1>

<?php conn_info(); ?>

<?php $tables =	list_tables(); ?>
<?php $status =	table_status();	?>

<p>
	Tables:	<b><?=count($tables);?></b>
	&nbsp;-&nbsp;
	Total size:	<b><?=size($status['total_size']);?></b>
	&nbsp;-&nbsp;
	<a href="<?=$_SERVER['PHP_SELF'];?>?searchdb=1&table_filter=<?=html_once(rget('table_filter'));?>">Search</a>
	&nbsp;-&nbsp;
	<a href="<?=$_SERVER['PHP_SELF'];?>?import=1">Import</a>
	&nbsp;-&nbsp;
	Export all:
	&nbsp;
	<a href="<?=$_SERVER['PHP_SELF'];?>?dump_all=1">structure</a>
	&nbsp;/&nbsp;
	<a href="<?=$_SERVER['PHP_SELF'];?>?dump_all=2">structure &	data</a>
	<? if ('pgsql' == $db_driver): ?>
		&nbsp;
		<small>(Note: pgsql	driver does	not	support	export of structure)</small>
	<? endif; ?>
</p>

<?
	$get = rget(array('table_filter'=>'str'));
	$get['table_filter'] = trim($get['table_filter']);
	if ($get['table_filter']) {
		foreach	($tables as	$k => $table) {
			if (!str_has($table, $get['table_filter'], $ignore_case	= true)) {
				unset($tables[$k]);
			}
		}
	}
?>

<form action="<?=$_SERVER['PHP_SELF'];?>" method="get" style="margin-bottom: 0.5em;">
<table cellspacing="0" cellpadding="0"><tr>
<td	style="padding-right: 3px;">Table name:</td>
<td	style="padding-right: 3px;"><input type="text" name="table_filter" value="<?=html_once($get['table_filter']);?>"></td>
<td	style="padding-right: 3px;"><input type="submit" class="button"	wait="1" value="Filter"></td>
</tr></table>
</form>

<? if ($get['table_filter']): ?>
	<p>Found: <b><?=count($tables);?></b></p>
<? endif; ?>

<table class="ls" cellspacing="1">
<tr>
	<th>Name</th>
	<th>Count</th>
	<th>Size</th>
	<th>Options</th>
</tr>
<?php foreach ($tables as $table): ?>
<tr>
	<td><a href="<?=$_SERVER['PHP_SELF'];?>?viewtable=<?=$table;?>"><?=$table;?></a></td>
	<?
		if ('mysql'	== $db_driver) {
			//$count = db_one("SELECT COUNT(*) FROM	$table");
			$count = $status[$table]['count'];
		}
		if ('pgsql'	== $db_driver) {
			$count = $status[$table]['count'];
			if (!$count) {
				$count = db_one("SELECT	COUNT(*) FROM $table");
			}
		}
	?>
	<td	align="right"><?=$count;?></td>
	<td	align="right"><?=size($status[$table]['size']);?></td>
	<td>
		<a href="<?=$_SERVER['PHP_SELF'];?>?dump_table=<?=$table;?>">Esporta</a>
		&nbsp;-&nbsp;
		<form action="<?=$_SERVER['PHP_SELF'];?>" name="drop_<?=$table;?>" method="post" style="display: inline;"><input type="hidden" name="drop_table" value="<?=$table;?>"></form>
		<a href="javascript:void(0)" onclick="if (confirm('DROP TABLE <?=$table;?> ?'))	document.forms['drop_<?=$table;?>'].submit();">Drop</a>
	</td>
</tr>
<?php endforeach; ?>
</table>

<? powered_by(); ?>
</script>
</body>
</html>