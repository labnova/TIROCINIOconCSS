<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
<title>CMS Tirocinio:cancella Lemma</title>
</head>
<body>
<?php
$conn= mysql_connect('localhost', 'root', 'mypass');
if(!$conn) { echo '<p>non è possibile collegarsi al server in questo momento</p>');
exit(); }

if(!@mysql_select_db('tirocinio')){
exit('<p>non è possibile inserire dati nel database in questo momento.</p>'); }


//CANCELLA TUTTI I DATI RELATIVI AD UN LEMMA NELLE TABELLE "VERBOFRAMESLOT" E "VERBOFRAMESLOTREAL"
$id=$_GET['id'];
$ok1=@mysql_query("DELETE FROM verbo WHERE idVerbo='$id'");
$ok2=@mysql_query("DELETE FROM VerboFrameSlot WHERE IdVerbo='$id'");
$ok3=@mysql_query("DELETE FROM VerboFrameSlotReal WHERE IdVerbo='$id'");
if ($ok1 and $ok2 and $ok3){
 echo '<p>dati cancellati</p>'; } else { echo '<p>errore durante la cancellazione! <br />' . 'errore: ' .mysql_error() .'</p>';
 }
?>
<p><a href="ListaLemmi.php">ritorna alla lista dei Lemmi</a></p>
</body>
</html>
