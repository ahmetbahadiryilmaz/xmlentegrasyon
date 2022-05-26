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





 $veriler = veri::fetchBySiteAll($botclass);
 $ix=0;
 


 
 foreach ($veriler as $veri) {
 
   /**
    *  @var veri $veri
    */
    if($veri->active){
      $veri->veri=str_replace('"src"','"Src"',$veri->veri);
      $good = unserialize($veri->veri);
      
      
      /** @var good $good */
      /*  foreach ($good->Variants as $variant){
        if(!variantimages::isExists($variant->VariantId,$veri->site)){
          $variantImages = new variantimages($variant->VariantId,json_encode($variant->Images["Src"]),$veri->site);
          $variantImages->insert();
        }
      }*/
      goods::addGood($good);

      if(isset($_GET["limit"])){
        if($ix++>$_GET["limit"])break;
      }
      }
      
 }
 
 
 goods::printXml();
 
 
 


 
 

