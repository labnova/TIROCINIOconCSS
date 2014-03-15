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
	$idFrame=$_POST['idFrame'];
	
	
	if(isset($_POST['cerca']))  {
	$selezione=$_POST['tipo']; 
	
	if($selezione=='frequenza') { 
	
	$selezione=$_POST['tipo']; 
	$frequenza='checked';
	
	
	
	$sql=mysql_query("SELECT * FROM tirocinio.verboframeslotreal, tirocinio.filler WHERE tirocinio.verboframeslotreal.idFrame='$idFrame'  AND filler.idFiller= verboframeslotreal.idFiller  ORDER BY frequenzaFiller");
	
	if(!$sql) { exit ('<p>errore durante il processo: ' .mysql_error() . '</p>'); }
	
	while($riga= mysql_fetch_array($sql)) {
		echo "<table border='1'>";
		echo '<tr>';
		echo '<td><p> Frequenza del Filler: ' . $riga['frequenzaFiller'] . '</td></p>';
		echo '<td><p> ID:  ' . $riga['id'] . '</td></p>';
		echo '<td><p> LogLikelihood:  ' . $riga['LL'] . '</td></p>';
		echo '<td><p> ID dello Slot:  ' . $riga['idSlot'] . '</td></p>';
		echo '<td><p> ID del Filler:  ' . $riga['idFiller'] . '</td></p>';
		echo '<td><p> Polisemia:  ' . $riga['polisemia'] . '</td></p>';
		echo '<td><p> ID del Filler:  ' . $riga['idFiller'] . '</td></p>';
		echo '<td><p> Nome del Filler: ' . $riga['nomeFiller'] . '</td></p>';
		echo '<td><p> Dispersione: ' . $riga['dispersione'] . '</td></p>';
		echo"<form action='informazioni.php' method='post>";
		echo"<input type='checkbox' name='classWN' value='classWN'>classWN<br />";
		echo"<input type='checkbox' name='PoS' value='classWN'>PoS<br />";
		echo"<input type='submit' value='cerca'>";
		
		echo '</tr>';
		echo '</table>';
		
		 	}
		 	
		
		
		
		
		 } if ($selezione=='SimpleLL') 
 { 
  $selezione=$_POST['tipo']; 
  $frequenza='unchecked';
  $SimpleLL='checked'; 
		
		 /*$sql=mysql_query("SELECT IdFrame FROM verboframeslotreal");
		$risultato=mysql_fetch_array($sql); 
		if($risultato['idFrame']=="") {echo 'nessun filler trovato nel database!';} else {
		*/
		
		$sql="SELECT * FROM tirocinio.verboframeslotreal, tirocinio.filler WHERE  tirocinio.verboframeslotreal.idFrame='$idFrame' AND filler.idFiller= verboframeslotreal.idFiller  ORDER BY LL";
	$risultato=@mysql_query($sql);
	if(!$risultato) { exit ('<p>errore durante il processo: ' .mysql_error() . '</p>'); }
	
	while($riga= mysql_fetch_array($risultato)) {
		echo "<table border='1'>";
		echo '<tr>';
		echo '<td><p> LogLikelihood:  ' . $riga['LL'] . '</td></p>';
		echo '<td><p> ID:  ' . $riga['id'] . '</td></p>';
		echo '<td><p> ID dello Slot:  ' . $riga['idSlot'] . '</td></p>';
		echo '<td><p> ID del Filler:  ' . $riga['idFiller'] . '</td></p>';
		echo '<td><p> ID del Frame:  ' . $riga['idFrame'] . '</td></p>';
		echo '<td><p> Polisemia:  ' . $riga['polisemia'] . '</td></p>';
		echo '<td><p> ID del Filler:  ' . $riga['idFiller'] . '</td></p>';
		echo '<td><p> Nome del Filler: ' . $riga['nomeFiller'] . '</td></p>';
		echo '<td><p> Frequenza del Filler: ' . $riga['frequenzaFiller'] . '</td></p>';
		echo '<td><p> Dispersione: ' . $riga['dispersione'] . '</td></p>';
		echo"<form action='informazioni.php' method='post>";
		echo"<input type='checkbox' name='classWN' value='classWN'>classWN<br />";
		echo"<input type='checkbox' name='PoS' value='classWN'>PoS<br />";
		echo"<input type='submit' value='cerca'>";
		
		echo '</tr>';
		echo '</table>'; }
		}
		
		 }	if ($selezione=='MI') { 
		 
		 $selezione=$_POST['tipo']; 
		 $MI='checked';
		 
		
		
		
		
		
		 $sql="SELECT * FROM tirocinio.verboframeslotreal, tirocinio.filler WHERE  tirocinio.verboframeslotreal.idFrame='$idFrame' AND filler.idFiller= verboframeslotreal.idFiller  ORDER BY frequenza";
	$risultato=@mysql_query($sql);
	if(!$risultato) { exit ('<p>errore durante il processo: ' .mysql_error() . '</p>'); }
		 while($riga= mysql_fetch_array($risultato)) {
		echo "<table border='1'>";
		echo '<tr>';
		echo '<td><p> Frequenza: ' . $riga['frequenza'] . '</td></p>';
		echo '<td><p> LogLikelihood:  ' . $riga['LL'] . '</td></p>';
		echo '<td><p> ID:  ' . $riga['id'] . '</td></p>';
		echo '<td><p> ID dello Slot:  ' . $riga['idSlot'] . '</td></p>';
		echo '<td><p> ID del Filler:  ' . $riga['idFiller'] . '</td></p>';
		echo '<td><p> Polisemia:  ' . $riga['polisemia'] . '</td></p>';
		echo '<td><p> ID del Filler:  ' . $riga['idFiller'] . '</td></p>';
		echo '<td><p> Nome del Filler: ' . $riga['nomeFiller'] . '</td></p>';
		echo '<td><p> Frequenza del Filler: ' . $riga['frequenzaFiller'] . '</td></p>';
		echo '<td><p> Dispersione: ' . $riga['dispersione'] . '</td></p>';
		echo'<td><fieldset><legend>opzioni di ricerca:</legend><br>';
		echo"<form action='informazioni.php' method='post>";
		
		echo "<input type='checkbox' name='classWN' value='classWN'>classWN<br />
		<input type='checkbox' name='PoS' value='classWN'>PoS<br />
		<input type='submit' value='cerca'>
		 </tr>
		 </form>
		 </table>"; }
		  }
	
	
	
	//echo '<td><p> ClassWN: ' . $riga['classWN'] . '</td></p>';
		//echo '<td><p> PoS: ' . $riga['PoS'] . '</td></p>';	
		
		?>
	
	
	
	
	
	
	
	
	
	
	
		
	</body>
</html>

