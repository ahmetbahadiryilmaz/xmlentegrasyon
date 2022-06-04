<?

require_once "inc/header.php";
$curl = new curl();
$time=time();
if($_GET["site"]=="hm"){

    $botclass="hm";
    $url="";
    $hm=  $curl->getPage("https://www2.hm.com/tr_tr.sitemap.xml");
    $d= new SimpleXMLElement($hm);
    $i=0;
    
    
    
      foreach ($d->sitemap as $dd) {
        $url=$dd->loc;
        if(strpos($url,"product")=== false){ continue;}
        
        $urlget=  $curl->getPage($url);
        $d2= new SimpleXMLElement($urlget);
    
         foreach ($d2->url as $dd2) {
            $url = $dd2->loc;
            echo $url."<br>\n";
        
            if(veri::isUrlExists($url)){  
                echo "exists<br>";
                continue;
            }  
            $bot= new $botclass();
         
            /** @var hm $bot */
            $good = $bot->goodGetir($url);
       
            
            /// hatali product cekme check
            $active=1;
            $stockid="";
            if(!$good){
                $active="0";
                $json= " ";
            }else{
                $json= serialize($good);    
            }
    
    
       
            
            
            $veri= new veri(get_class($bot) , $url,$json,isset($good)?(isset($good->GoodId)?$good->GoodId:null):null);
            $veri->active=$active;
            $veri->error=$bot->error;
            $veri->insert();
       
    
     
            if($loglama){
                $fark = time()-$time;
                if($bot->error){
                    file_put_contents(__DIR__.'/loglar/'.$_GET["site"].".txt","[hata]".$url."|".(isset($good->GoodId)?$good->GoodId:"")."|".(isset($good->Brand)?$good->Brand:"")."|".$bot->error."\n",FILE_APPEND);
                    
                }else{
                    file_put_contents(__DIR__.'/loglar/'.$_GET["site"].".txt","[eklendi]".$url."|".$good->GoodId."|".$good->Brand ."\n",FILE_APPEND);
                }
                $time = time();
            }
    
            
            
         }
         unset($d2);
     
    }
     
}elseif($_GET["site"]=="mango"){
    $botclass="mango";
    $url="";
    $curl->setCacheFolder(__DIR__.'/cache/'.$botclass."/");
    $mango=  $curl->getPage("https://shop.mango.com/sitemap.xml");


    $d= new SimpleXMLElement($mango);
    $i=0;

    foreach ($d->sitemap as $dd) {
    
    $url=$dd->loc;
    $url=str_replace(" ","%20",$url);
    if(strpos($url,"tr-Mango")=== false){ continue;}
    
    //echo $url."<br>";
    
    $urlget=  $curl->getPage($url);
    

    $gz= gzdecode($urlget);
        
    
    $d2= new SimpleXMLElement( $gz);

        foreach ($d2->url as $dd2) {
        $url = $dd2->loc;
        if(!is_numeric(substr($url, -2)) ){
            continue;
        }   
        //echo $url."<br>";
        
        $producturls = mango::getProductUrls($curl->getPage($url));    
        $ixc=0;
        foreach($producturls as $url){ 
            
            if(++$ixc<3) continue;
    
    
            if(veri::isUrlExists($url)){  
                echo "exists<br>";
                continue;
            }  
            $bot= new mango();
   
            /** @var mango $bot */
            $good = $bot->goodGetir($url);
    
            
            /// hatali product cekme check
            $active=1;
            $stockid="";
            if(!$good){
                $active="0";
                $json= " ";
            }else{
                $json= serialize($good);    
            }
    
    
    
            
            
            $veri= new veri(get_class($bot) , $url,$json,isset($good)?(isset($good->GoodId)?$good->GoodId:null):null);
            $veri->active=$active;
            $veri->error=$bot->error;
            $veri->insert();
    
    
    
            if($loglama){
                $fark = time()-$time;
                if($bot->error){
                    file_put_contents(__DIR__.'/loglar/'.$_GET["site"].".txt","[hata]".$url."|".(isset($good->GoodId)?$good->GoodId:"")."|".(isset($good->Brand)?$good->Brand:"")."|".$bot->error."\n",FILE_APPEND);
                    
                }else{
                    file_put_contents(__DIR__.'/loglar/'.$_GET["site"].".txt","[eklendi]".$url."|".$good->GoodId."|".$good->Brand ."\n",FILE_APPEND);
                }
                $time = time();
            }
    
            
        }
        }
        unset($d2);
    
}
    
}

 