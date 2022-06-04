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

 
 

$site=$_GET["site"];
$veriler=array();
 
 
 
$sth = db::$conn->prepare("SELECT * from veri  where site=:site and active=1 limit 1",array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false));
$sth->execute(array("site"=> ($_GET["site"])));
while ($r = $sth->fetch(PDO::FETCH_OBJ)) {
     /** @var veri $r */
    $botclass=$r->site;
    $bot= new $botclass();
    /** @var mango $bot */
    $bot->setMode(modes::$update);
    $yeniveri= $bot->goodGetir($r->url);
    db::update("veri",[
      "veri"=> $this->yeniveri,
      "error"=>$this->error
    ],["id"=>$r->id]);

    if($loglama){
      $fark = time()-$time;
      if($bot->error){
          file_put_contents(__DIR__.'/loglar/update_'.$_GET["site"].".txt","[hata]".$url."|".(isset($good->GoodId)?$good->GoodId:"")."|".(isset($good->Brand)?$good->Brand:"")."|".$bot->error."\n",FILE_APPEND);
          
      }else{
          file_put_contents(__DIR__.'/loglar/update_'.$_GET["site"].".txt","[guncellendi]".$url."|".$good->GoodId."|".$good->Brand ."\n",FILE_APPEND);
      }
      $time = time();
  }



}