<?

require "inc/header.php";
require "inc/botayar.php";

if( isset($_GET["site"]) && in_array($_GET["site"],$siteler)){
  $botclass=$_GET["site"];
} else{
  foreach($siteler as $site){
     ?>
     <a href="?site=<?=$site?>"><?=$site?></a><br>
    <?
  }
  exit();
}

 
header("Content-type: text/xml");

$site=$_GET["site"];
$veriler=array();
 
 
 
$sth = db::$conn->prepare("SELECT * from veri  where site=:site and active=1",array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false));
$sth->execute(array("site"=> ($_GET["site"])));
