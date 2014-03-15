<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
<title>Untitled</title>
</head>
<body>
<form action="<? $_SERVER['PHP_SELF']; ?>"  method='post' enctype="multipart/form-data">
<p><label>Upload file:<br />
<input type="text" name="desc" maxlength="255" /></label></p>
<p><input type="submit" value="upload" /></p>
</form>

<?
//ESCE SE IL FILE NON E' STATO CARICATO VERAMENTE.
if(!is_uploaded file($_FILES['upload']['tmp_name'])) {
exit('nessun file caricato!');
						 }
$uploadfile=$_FILES['upload']['tmp_name'];
$uploadname=$_FILES['upload']['name'];
$uploadtype=$_FILES['upload']['type'];
$uploaddesc=$_FILES['desc'];
//APRE IL FILE PER LA LETTURA BINARIA ('RB')
$fileTemp= fopen($uploadfile, 'rb');
//LEGGE L'INTERO FILE IN MEMORIA USANDO LA FUNZIONE DI PHP 'FILESIZE', PER OTTENERE LA SUA DIMENSIONE
$fileData= fread($fileTemp, filesize($uploadfile));
//PREPARA L'INSERIMENTO NEL DATABASE AGGIUNGENDO I BACKSLASH PRIMA DEI CARATTERI SPECIALI.
$fileData=addslashes($fileData);
//CREA LA QUERY SQL
$sql="INSERT INTO File SET nomeFile='$uploadfile', mimetype='$uploadtype', descrizione='$uploaddesc', fileData='$fileData'";
//EFFETTUA L'INSERIMENTO
$ok=@mysql_query($sql);
if(!ok){ exit('errore nel database: ' .mysql_error()));

$id=$_GET['id'];
//L'UTENTE CERCA UN FILE
$sql="SELECT nomeFile, mimetype, fileData FROM File WHERE ID='$id'";
$risultato=@mysql_query($sql);
if(!risultato) {
							 exit('errore nel database ' .mysql_error()); }
							 
$file= mysql_fetch_array($risultato);
if($file) {
					exit('nessun ID trovato nel database'); }
$nomeFile=$file['nomeFile'];
$mimetype=$file['mimetype'];
$fileData=$file['fileData'];

header("disposizione-contenuto: inline; nomeFile=$nomeFile");
header("tipo-contenuto: $mimetype");
header('lunghezza-contenuto: ' .strlen($fileData));

echo $fileData;
exit();
</body>
</html>
