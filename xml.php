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


header("Content-type: text/xml");
echo '<?xml version="1.0" encoding="utf-8"?>';
echo "<Goods>";
$ix=0;
while ($r = $sth->fetch(PDO::FETCH_ASSOC)) {
    
    $veri = new veri($r["site"],$r["url"],$r["veri"],$r["stockid"],  $r["id"]);
    $veri->veri=str_replace('"src"','"Src"',$veri->veri);
    
    $r["active"]?"":$veri->disabled();
    /** @var good $good */
    
    $good = unserialize($veri->veri);
    $good->toXml();
    unset($veri);
   // if($ix++>3)break;
}

echo "</Goods>";



 

 /*
 
 foreach ($veriler as $veri) {
 
 
    if($veri->active){
        $veri->veri=str_replace('"src"','"Src"',$veri->veri);
        $good = unserialize($veri->veri);
        
        
       
        goods::addGood($good);
 
        if(isset($_GET["limit"])){
          if($ix++>$_GET["limit"])break;
        }
        unset($good);
      }
      
 }
 */
 
 //goods::printXml();
 
 
 


 
 

