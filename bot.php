<?

 
require_once "inc/header.php";



 

$botclass="";
$urller="";


if(isset($_GET["site"]) && $_GET["site"]=="hm"){
    header("location:bot2.php?site=hm");
}elseif( isset($_GET["site"]) && in_array($_GET["site"],$siteler)){
    $urller=file_get_contents($sitemapUrller[$_GET["site"]])   ;
    $botclass=$_GET["site"];
} else{
    foreach($siteler as $site){
       ?>
       <a href="?site=<?=$site?>"><?=$site?></a><br>
      <?
    }
    exit();
}



$d = new DOMDocument();
if($loglama){
    file_put_contents(__DIR__.'/loglar/'.$_GET["site"].".txt","");
} 


 $d->loadXML($urller);
 $d = $d->getElementsByTagName("loc"); 
 $ix=0;
 $time=time();
 for ($i=0; $i <$d->count() ; $i++) { 
    
     $dex= $d->item($i);
     $url=$dex->nodeValue;
     
     echo $url."\n";
 
     if(veri::isUrlExists($url)){  
        echo "<span style='color:blue'> exists </span><br>";
        continue;
    } 
    $bot= new $botclass();
    $good = $bot->goodGetir($url);
   
    
    /// hatali product cekme check
    $active=1;
    $stockid="";
    if(!$good){
        $active="0";

          echo "<span style='color:red'>".$bot->error." false</span>";
        $json= " ";
    }else{
        $json= serialize($good);    
    }


    //
    
    
    $veri= new veri(get_class($bot) , $url,$json,(isset($good)&&isset($good->GoodId))?$good->GoodId:null);
    $veri->active=$active;
    $veri->error=$bot->error;
    if($loglama){
        $fark = time()-$time;
        if($bot->error){
            file_put_contents(__DIR__.'/loglar/'.$_GET["site"].".txt","[hata]".$url."|".(isset($good->GoodId)?$good->GoodId:"")."|".(isset($good->Brand)?$good->Brand:"")."|".$bot->error."\n",FILE_APPEND);
            
        }else{
            file_put_contents(__DIR__.'/loglar/'.$_GET["site"].".txt","[eklendi]".$url."|".$good->GoodId."|".$good->Brand ."\n",FILE_APPEND);
        }
        $time = time();
    }


    $veri->insert();
    
    
   //if($ix++>10) exit();
    echo "<br>";
   
 
 }
 


 
 

