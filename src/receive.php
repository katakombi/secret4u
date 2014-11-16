
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

function encode(control)
{
    var ckey = $("#symmkey").val();
    var x=document.getElementById(control).value;
    var encrypted = CryptoJS.AES.encrypt(x, ckey);
    document.getElementById("encoded-message").value=encrypted;
}

function decode(control)
{
    var ckey = $("#symmkey").val();
    var x=document.getElementById(control).value;
    var decrypted = CryptoJS.AES.decrypt(x, ckey);
    decrypted = decrypted.toString(CryptoJS.enc.Utf8);
    document.getElementById("decoded-message").value=decrypted;
    document.getElementById("decoded-reply").value="> \n> "+decrypted.replace(/\n/,"\n> ");
}

function encodeReply(control)
{
    var ckey = $("#symmkey").val();
    var x=document.getElementById(control).value;
    var encrypted = CryptoJS.AES.encrypt(x, ckey);
    document.getElementById("encoded-reply").value=encrypted;
}

function post(path, params, method) {
    method = method || "post"; // Set method to post by default if not specified.

    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);

    for(var key in params) {
        if(params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);

            form.appendChild(hiddenField);
         }
    }

    document.body.appendChild(form);
    form.submit();
}

function sendEMail() {
    key=Math.floor(Math.random()*10000000000000000);
    $('#symmkey').val(key);

    sha="LONGHASH";
    txtMail='I deposited a reply to your secret message which is accessible under https://secret4u.eu/r.php?h='+sha+'\n\n'+
            'The encryption key is:\n\n'+key+'\n\n'+
            'Please note that this message will be destroyed once the link has been accessed for the first time!\n\n'

    sha=CryptoJS.SHA1(txtMail+document.getElementById("decoded-reply").value);

    txtMail=txtMail.replace(/LONGHASH/,sha);
    txtMail=txtMail.replace(/\n/g,'%0D%0A');
    txtMail=txtMail.replace(/ /g,'%20');

    msg=document.getElementById("encoded-reply").value;
    if (!msg) {
        encodeReply('decoded-reply');
        msg=document.getElementById("encoded-reply").value;
    }
    post('deposit.php', { hash:sha, encmsg:msg }, "post");
    window.open('mailto:myfriend?subject=A reply to your secret message is waiting for you&body=' + txtMail);
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
         echo "Ouch!<br/>";
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
<br/>
<label for="symmkey">Encryption key </label> <input id="symmkey" value="" title="Copy&paste the key from your email">.
You can send a reply message immediately.
</p>
</div>
<h3>Reply</h3>
<div>
<p>
<textarea id='decoded-reply' cols=80 rows=10">
</textarea>
<br/>
For safety reasons a new key will be randomly generated!
</p>
</div>
<h3>Encoded Reply</h3>
<div>
<p>
<textarea id='encoded-reply' cols=80 rows=10">
</textarea>
This will be stored on our servers.
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
    <button onclick='sendEMail()'>
        Send...
    </button>
</div>

</body>
</html>
