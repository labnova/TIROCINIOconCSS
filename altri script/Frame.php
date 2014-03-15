<head>
<title>Frame</title>
</head>
<body>
<?php 
//SE L'UTENTE VUOLE AGGIUNGERE UN FRAME
if(isset($_GET['aggiungiFrame'])): ?> 

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<label>inserisci il frame:< br />
<input type="text" name="frame">
<label>inserisci la frequenza:<br />
<input type="text" name="frequenzaFrame">
</label><br />
<input type="submit" value="SUBMIT" />
</form>
<?php  else: //PAGINA DI DEFAULT 

//CONNESSIONE AL SERVER DATABASE
$conn= mysql_connect('localhost', 'root', 'mypass');
if(!$conn) { echo '<p>non è possibile collegarsi al server in questo momento</p>');
exit(); }

//CONNESSIONE AL DATABASE
mysql_select_db('tirocinio',$conn){
exit('<p>non è possibile inserire dati nel database in questo momento.</p>'); }

//SE UN FRAME VIENE INVIATO AGGIUNGILO AL DATABASE
if(isset($_POST['frame']){
$frame=$_POST['frame'];
$frequenzaFrame=$_POST['frequenzaFrame'];
$sql="INSERT INTO frame SET nomeframe='$frame', frequenzaFrame='$frequenzaFrame, data=CURDATE()";
if(@mysql_query($sql)) {
echo '<p>il frame è stato inserito.</p>'} else { echo '<p>errore durante il processo: ' .mysql_error() .</p>; 
		 }
	}
	
//SE UN FRAME E' STATO CANCELLATO RIMUOVILO DAL DATABASE
if(isset($_GET['cancellaFrame'])) {
$idFrame=$_GET['cancellaFrame'];
$sql="DELETE FROM frame WHERE nomeframe=$nomeFrame";
if(@mysql_query($sql)){ 
echo '<p>il frame è stato cancellato.</p>'; } else { 
echo '<p>errore durante la cancellazione del frame ' .mysql_error() '</p>';
		 }
	}

//RICHIEDI L'ID E IL TESTO DI TUTTE I FRAME
$risultato=@mysql_query('SELECT idFrame,Frame FROM verbo');
 if(!$risultato){
 exit('<p>errore nel processare la query:' .mysql_error() .'</p>);
 }
//MOSTRA LA FREQUENZA DI OGNI FRAME IN UN PARAGRAFO CON UN PULSANTE DI CANCELLAZIONE A FIANCO DI OGNUNO.
while($riga=mysql_fetch_array($risultato)) {
$idFrame=$riga['idFrame'];
$frame=$riga['frame'];
$frequenzaFrame=$riga['frequenzaFrame'];
echo '<p>' .$frame . '<a href=" ' .$_SERVER['PHP_SELF'] .'?cancellaFrame=' .$Frame .'">' . 'cancella questo Frame</a></p>';

 //QUESTO LINK CARICHERA' LA PAGINA CON IL FORM D'INVIO DEL FRAME
 echo '<p><a href=" ' .$_SERVER['PHP_SELF']. ?aggiungiFrame=1">AGGIUNGI UN FRAME</a></p>';
 
 endif;
 ?>
</body>
</html>
