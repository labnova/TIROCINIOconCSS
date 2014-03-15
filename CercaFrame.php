<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title></title>
		<meta name="author" content="Innocenzo Tremamondo" >
		<meta name="generator" content="screem 0.16.1" >
		<meta name="description" content="" >
		<meta name="keywords" content="" >
		<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" >
		<meta http-equiv="Content-Script-Type" content="text/javascript" >
		<meta http-equiv="Content-Style-Type" content="text/css" >
	</head>
	<body>
		<?
		
		$conn= mysql_connect('localhost', 'root', 'enzo');
if(!$conn) { echo '<p>non è possibile collegarsi al server in questo momento</p>';
exit(); }

if(!@mysql_select_db('tirocinio')){
exit('<p>non è possibile inserire dati nel database in questo momento.</p>'); }
	
	$frequenza='unchecked';
	$MI='unchecked';
	$SimpleLL='unchecked';
	$numero=$_POST["idVerbo"];
	
	if(isset($_POST['cerca']))  {
	
	$selezione=$_POST['tipo']; 
	
	if($selezione=='frequenza') { 
	
	$selezione=$_POST['tipo']; 
	$frequenza='checked';
	
	
	$sql="SELECT * FROM verboframe, frame WHERE verboframe.idVerbo='$numero' AND frame.idFrame= verboframe.idFrame ORDER BY frequenzaFrame";
	$risultato=@mysql_query($sql);
	if(!$risultato) { exit ('<p>errore durante il processo: ' .mysql_error() . '</p>'); }
	
while($riga= mysql_fetch_array($risultato)) {
		echo "<table border='1'>";
		echo '<tr>';
		echo '<td><p>frequenza del Frame:' . $riga['frequenzaFrame'] . '</td></p>';
		echo '<td><p> idVerboFrame: ' . $riga['idVerboFrame'] . '</td></p>';
		echo '<td><p> LogLikelihood: ' . $riga['LL'] . '</td></p>';
		echo '<td><p> idFrame: ' . $riga['idFrame'] . '</td></p>';
		echo '<td><p> IdVerbo: ' . $riga['idVerbo'] . '</td></p>';
		
		
		echo '<td><p> ID del Frame: ' . $riga['idFrame'] . '</td></p>';
		echo '<td><p> nome del Frame: ' . $riga['nomeFrame'] . '</td></p>';
		echo '</tr>';
		echo '</table>';
		
		 	}
		 	
		 }
		 	
		 }	elseif($selezione=='MI') { 
		 
		 $MI='checked';
		 
		 	$sql="SELECT * FROM verboframe, frame WHERE verboframe.idVerbo='$numero' AND frame.idFrame= verboframe.idFrame ORDER BY frequenza";
	$risultato=@mysql_query($sql);
	if(!$risultato) { exit ('<p>errore durante il processo: ' .mysql_error() . '</p>'); }
	
	while($riga= mysql_fetch_array($risultato)) {
		echo "<table border='1'>";
		echo '<tr>';
		echo '<td><p> frequenza: ' . $riga['frequenza'] . '</td></p>';
		echo '<td><p> idVerboFrame: ' . $riga['idVerboFrame'] . '</td></p>';
		echo '<td><p> LogLikelihood: ' . $riga['LL'] . '</td></p>';
		echo '<td><p> idFrame: ' . $riga['idFrame'] . '</td></p>';
		echo '<td><p> IdVerbo: ' . $riga['idVerbo'] . '</td></p>';
		
		
		echo '<td><p> ID del Frame: ' . $riga['idFrame'] . '</td></p>';
		echo '<td><p> nome del Frame: ' . $riga['nomeFrame'] . '</td></p>';
		echo '<td><p>frequenza del Frame:' . $riga['frequenzaFrame'] . '</td></p>';
		echo '</tr>';
		echo '</table>';
		
		 	}
		 	
		 } else 
 {  $SimpleLL=='checked';
 	
		 
		 $sql="SELECT * FROM verboframe, frame WHERE verboframe.idVerbo='$numero' AND frame.idFrame= verboframe.idFrame ORDER BY LL";
	$risultato=@mysql_query($sql);
	if(!$risultato) { exit ('<p>errore durante il processo: ' .mysql_error() . '</p>'); } 
	
	
	

		while($riga= mysql_fetch_array($risultato)) {
		echo "<table border='1'>";
		echo '<tr>';
		echo '<td><p> LogLikelihood: ' . $riga['LL'] . '</td></p>';
		echo '<td><p> idVerboFrame: ' . $riga['idVerboFrame'] . '</td></p>';
		echo '<td><p> frequenza: ' . $riga['frequenza'] . '</td></p>';
		echo '<td><p> idFrame: ' . $riga['idFrame'] . '</td></p>';
		echo '<td><p> IdVerbo: ' . $riga['idVerbo'] . '</td></p>';
		
		
		echo '<td><p> ID del Frame: ' . $riga['idFrame'] . '</td></p>';
		echo '<td><p> nome del Frame: ' . $riga['nomeFrame'] . '</td></p>';
		echo '<td><p>frequenza del Frame:' . $riga['frequenzaFrame'] . '</td></p>';
		echo '</tr>';
		echo '</table>';
		
		 	}
		 	
		 }
		 	
		
	 	
		 	
		 	
		 	?>
		
		<form action="CercaSlot.php"method='post'>
		<label>cerca gli slot del frame con ID: <input type="text" name="idFrame"></label>
		<fieldset>
			<legend>Cerca per</legend>
				<input type="radio" name="frequenza" value="frequenza" />frequenza<br />
				<input type="radio" name="MI" value="MI" />MI<br />
				<input type="radio" name="SimpleLL" value="SimpleLL" />SimpleLL<br />
		</fieldset>
			<option >
		<input type='submit' value='cerca' />
		 </form>
		
		
		
	</body>
</html>
