
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title> Deposit for "James-Bond alike" self-destroying secret messages</title>
<link rel="stylesheet" href="res/pepper-grinder/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.10.2.js"></script>
<script src="https://code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
<script src="res/aes.js"></script>
<script src="res/sha1.js"></script>
<link rel="stylesheet" href="res/style.css">
<script>

$(function() {
$( "#accordion" ).accordion({ active: 1 });
});

//$( "#accordion" ).accordion({ active: 2 });
//$("#accordion").accordion({

//heightStyle: "content";
//autoHeight: false,
//heightStyle: "content"

//});



function decode(control)
{
    var ckey = $("#symmkey").val();
    var x=document.getElementById(control).value;
    var decrypted = CryptoJS.AES.decrypt(x, ckey);
    decrypted = decrypted.toString(CryptoJS.enc.Utf8);
    document.getElementById("decoded-message").value=decrypted;
    document.getElementById("decoded-reply").value="> \n> "+decrypted.replace(/\n/,"\n> ");
}


$(function(){
$('#symmkey').keyup(function() {
    decode("encoded-message");
})});

$(function(){
$('#symmkey').on('paste',function() {
    setTimeout(function() { decode("encoded-message"); }, 200 );
})});


</script>
</head>
<style>
</style>
<body>
<h1>Deposit for "James-Bond alike" self-destroying secret messages</h1>
<div id="accordion">
<h3>Encrypted Message</h3>
<div>
This is the encrypted message retrieved from our server:
<p>
    <?php

    try {
      $db = new PDO('sqlite:/var/www/sqlite/messages.sqlite3',NULL,NULL,array(PDO::ATTR_PERSISTENT => true));
    //$db = new PDO('sqlite::memory:', NULL, NULL, array(PDO::ATTR_PERSISTENT => true));
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $db->beginTransaction();
      $query = $db->prepare("SELECT * FROM messages WHERE hash=?");
      $query->bindParam(1, $_GET["h"], PDO::PARAM_STR);

      $query->execute();
      $result = $query->fetchall();

      if ($result != null) // entry was found
      {
	      $query2 = $db->prepare("UPDATE messages SET message=NULL WHERE hash=?");
	      $query2->bindParam(1, $_GET["h"], PDO::PARAM_STR);
	      $query2->execute();
      }
      $db->commit();

      foreach($result as $row)
      {
	$msg=$row['message'] || "";
	
    ?>
        <textarea readonly id='encoded-message' cols=80 rows=10><?php echo $row['message'];?></textarea>
    <?php
      }
      //$query = $db->prepare("SELECT * FROM messages");
      //$query->execute();
      //$result = $query->fetchall();

      //foreach($result as $row)
      //{
      //   echo $row['read'] . $row['hash'] . ' -- ' . " <br/>";
      //}

    }
    catch(PDOException $e) {
         echo "Ouch!<br>";
         echo $e->getMessage();
         echo "<br/>";
         echo $e->getCode();
    }
    ?>
</p>
</div>
<h3>Message</h3>
<div>
Find the message of your friend below. A subsequent access will not be possible later.
<p>
<textarea readonly id='decoded-message' cols=80 rows=10">
</textarea>
<br>
<label for="symmkey">Encryption key </label> <input id="symmkey" value="" title="Copy&paste the key from your email">.
You can send a reply message immediately.
</p>
</div>
<h3>Reply</h3>
<div>
<p>
<textarea id='decoded-reply' cols=80 rows=10">
</textarea>
</p>
</div>
<h3>Frequently Asked Questions</h3>
<div>
<p>
<br/>
How does it work?
<ul>
    <li>Write your message or insert it via copy'n'paste.</li>
    <li>Click "Send" to deposit the encrypted message on our server. Simultaneously an email is being generated containing retrieval link and decryption key.</li>
    <li>Use your contacts to send the email to your friend!</li>
</ul>
What about privacy?
<ul>
    <li>The only information stored on our servers are the encrypted messages</li>
    <li>Encryption and decryption is performed on your local computer using the browsers javascript</li>
    <li>A message is destroyed as soon as its retrieval link is accessed, or at the latest 30 days after posting</li>
</ul>
(c) 2014 by <a href="mailto:stefan.kombrink@mailbox.org">Stefan Kombrink</a>
</p>
</div>
</div>

<div id="send-email">
    <button onclick='sendEMail(document.getElementById("email-notification-preview").innerHTML)'>
        Send...
    </button>
</div>

</body>
</html>
