
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Secret4U -- Message has been stored</title>
<link rel="stylesheet" href="res/pepper-grinder/jquery-ui.css">
<script src="http://code.jquery.com/jquery-1.10.2.js"></script>
<script src="http://code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
<link rel="stylesheet" href="res/style.css">
<script>

</script>
</head>
<style>

</style>
<body>
<h1>Message has been stored</h1>
<?php
try{
//$db = new PDO('sqlite::memory:');
  $db = new PDO('sqlite:/var/www/sqlite/messages.sqlite3',NULL,NULL,array(PDO::ATTR_PERSISTENT => true));
  //$db = new PDO('sqlite::memory:', NULL, NULL, array(PDO::ATTR_PERSISTENT => true));
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $db->exec("CREATE TABLE IF NOT EXISTS messages (hash CHAR(40) PRIMARY KEY,message TEXT,read BOOLEAN)");

  $insert = "INSERT INTO messages (hash, message, read)
             VALUES (:hash, :message, :read)";
  $stmt = $db->prepare("INSERT INTO messages (hash,message,read) VALUES (:hash,:message,:read)");
  $stmt->execute(array(':hash'=>$_POST["hash"],
                       ':message'=>$_POST["encmsg"],
                       ':read'=>false ));

  echo "SUCCESS <br/>";

//$query = $db->prepare("SELECT * FROM messages");
//$query->execute();
//$result = $query->fetchall();

//    foreach($result as $row)
//{
//    echo $row['hash'] . ' -- ' . $row['message'] . " <br/>";
//}

//echo $_SERVER['REMOTE_ADDR'];
//
}
 catch(PDOException $e) {
   // Print PDOException message
     if ( $e->getCode() == 23000 ) {
         echo "Did you send a message twice? Under this link a message had been deposited already, please go back, change the key and try again.";
     } else {
         echo $e->getMessage();
         echo "<br/>";
         echo $e->getCode();
     }
  }
?>

</body>
</html>
