<head>
<title>Slot</title>
</head>
<body>
<?php 
//SE L'UTENTE VUOLE AGGIUNGERE UNO SLOT
if(isset($_GET['aggiungiSlot'])): ?> 

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<label>inserisci lo slot:< br />
<input type="text" name="frame">
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

//SE UNO SLOT VIENE INVIATO AGGIUNGILO AL DATABASE
if(isset($_POST['slot']){
$slot=$_POST['slot'];
$sql="INSERT INTO slot SET nomeSlot='$slot', data=CURDATE()";
if(@mysql_query($sql)) {
echo '<p>lo slot è stato inserito.</p>'} else { echo '<p>errore durante il processo: ' .mysql_error() .</p>; 
		 }
	}
	
//SE UNO SLOT E' STATO CANCELLATO RIMUOVILO DAL DATABASE
if(isset($_GET['cancellaSlot'])) {
$idSlot=$_GET['cancellaSlot'];
$sql="DELETE FROM Slot WHERE nomeSlot=$nomeSlot";
if(@mysql_query($sql)){ 
echo '<p>lo slot è stato cancellato.</p>'; } else { 
echo '<p>errore durante la cancellazione dello slot ' .mysql_error() '</p>';
		 }
	}

//RICHIEDI L'ID E IL TESTO DI TUTTI GLI SLOT 
$risultato=@mysql_query('SELECT idSlot,nomeSlot FROM Slot');
 if(!$risultato){
 exit('<p>errore nel processare la query:' .mysql_error() .'</p>);
 }
//MOSTRA LA FREQUENZA DI OGNI SLOT IN UN PARAGRAFO CON UN PULSANTE DI CANCELLAZIONE A FIANCO DI OGNUNO.
while($riga=mysql_fetch_array($risultato)) {
$idSlot=$riga['idSlot'];
$Slot=$riga['Slot'];
$frequenzaSlot=$riga['frequenzaSlot'];
echo '<p>' .$Slot . '<a href=" ' .$_SERVER['PHP_SELF'] .'?cancellaSlot=' .$Slot .'">' . 'cancella questo Slot</a></p>';

 //QUESTO LINK CARICHERA' LA PAGINA CON IL FORM D'INVIO DELLO SLOT
 echo '<p><a href=" ' .$_SERVER['PHP_SELF']. ?aggiungiFrame=1">AGGIUNGI UNO SLOT</a></p>';
 
 endif;
 ?>
</body>
</html>
