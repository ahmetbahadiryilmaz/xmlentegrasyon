<?

 
class bershaka{
     public $error="";

     public  function goodGetir($url){


        $curl=new curl();
        $getPage= $curl->getPage($url);    
         
        echo $curl->getHttpcode()."<br>";
        if($curl->getHttpcode()=="404" || $curl->getHttpcode()=="410"  ){
          $this->error=fetchError::$sayfabulunamadi;
          return false;
       }
     
        $f=  f::clean(($getPage));
     
        $goodobj= str_get_html( $f);
        
        $good= new good();
        
      
        preg_match('#"sku"\:"(.*?)",#',$goodobj,$k);
        if(count($k)<1){
          $this->error=$this->error=fetchError::$skubulunamadi;
          return false;
        }
        $goodid=explode("-",$k[1]);
        //$good->GoodId=$goodid[1]; 
        $good->Title=   $goodobj->find('.product-title',0)->innertext;
        $ref=$goodobj->find('.product-reference');
        if(count($ref)==0){
          $ref=$goodobj->find('.mini-ref',0)->innertext;
          $this->error=fetchError::$refbulunamadi;
          return false;
        }else{
          $ref=$ref[0]->innertext;
        };
        $ref= str_replace("Ref ","",$ref);
        $good->REFERENCE= $ref;
         
       $good->Brand="Bershka";
       $fiyat = $goodobj->find('.current-price-elem',0)->innertext;
       $fiyat= str_replace("TL","",$fiyat);
       $price= str_replace(",",".",$fiyat);
       $good->Price= $price;
       $good->SellingPrice=$price;
       $good->TotalArrivalPrice= $price;
       $good->TotalSellingPrice=$price;
       /* 
       $images= $goodobj->find('.image-item');
    
       foreach($images as $img){
         $good->addImage($img->{"data-original"});
       }*/
  
       $desc= ($goodobj->find('.about-product',0));
       $desc=str_replace("        ","\n",strip_tags($desc->outertext));
       $desc= implode("<br>", array_map('trim', explode("\n", $desc)));
       $good->Description=$desc;
      ///// varyantlar
       preg_match('#iStoreId\"\:(.*?),#',$goodobj,$kx);
       preg_match('#iCatalogId\"\:(.*?),#',$goodobj,$kx2);
       preg_match('#iCategoryId\"\:(.*?),#',$goodobj,$kx3);
       preg_match('#iProductId\"\:(.*?),#',$goodobj,$kx4);

       $storeId=$kx["1"];
       $catalogId=$kx2["1"];
   
       $categoriId=$kx3["1"];
       $productId=$kx4["1"];
       
       $produrl="https://www.bershka.com/itxrest/3/catalog/store/"       .$storeId. "/".$catalogId."/productsArray?productIds=".$productId."&languageId=-43";
   
       
       $prod = json_decode($curl->getPage($produrl));
       
       $good->GoodId=$goodid[0];
 
        if(veri::isStockIdExists($good->GoodId,get_class($this))){  
            $this->error= fetchError::$stokzatenvar;
            return false;
        } 

       $good->SKU=$prod->products[0]->id;
        $colors= $prod->products[0]->detail->colors;
        //echo $prod->familyName;
   
        if(isset($prod->products[0]->detail->subfamilyInfo->subFamilyName)){
          $good->Category=  $prod->products[0]->detail->subfamilyInfo->subFamilyName;
        }elseif(isset($prod->products[0]->detail->familyInfo->familyName)){
          $good->Category=  $prod->products[0]->detail->familyInfo->familyName;
        }else{
          $this->error=fetchError::$kategoribulunamadi;
          return false;
        }
          //$prod->products[0]->bundleProductSummaries[0]->detail->subfamilyInfo->subFamilyName;
       
        $bershakaimgurlprefix="https://static.bershka.net/4/photos2";
         if($prod->products[0]->image!=null) exit();
        foreach($colors as $color){         
            foreach($color->sizes as $size){
              if($size->backSoon!=0)continue;
              $variant= new variant($size->name,$color->name);
              $variant->SKU=$size->sku;
              $sizep = explode("-",$size->partnumber);
              $variant->VariantId=$sizep[0];
              $variant->VaryantReference=$ref;
              $size->price= $size->price/100;
              $variant->Price=$size->price;
              $variant->SellingPrice=$size->price;
              $variant->TotalArrivalPrice=$size->price;
              $variant->SellingPrice=$size->price;
             // $variant->Stock=$size->isBuyable?100:0;  
          
              $variant->Stock=100;//$size->visibilityValue=="SHOW"?100:0;            
              $good->Stock+=$variant->Stock;
              $image= $color->image;
          

              /////resimler
              
              if(variantimages::isExists($variant->VariantId,get_class($this))){
                $variantImages  = variantimages::fetch($variant->VariantId);
                $variantImages =   json_decode($variantImages->images);
                foreach($variantImages as $image){
                  $variant->addImage($image);
                }
               
              }else{
                $resimler= $this->resimGetir( $prod->products["0"],$color->id);
              
                /*
                foreach ($prod->products["0"]->detail->xmedia as $media ){
                  if($media->colorCode==$color->id){

                        for ($imedialt=0; $imedialt < count($media->xmediaItems); $imedialt++) { 
                          foreach ($media->xmediaItems[$imedialt]->medias as $mediaalt){
                              $md5hash=isset($mediaalt->extraInfo->hash[0]->md5Hash)?$mediaalt->extraInfo->hash[0]->md5Hash."-":"";
                        
                              $httpcode=  curl::getHttpCodeOnly($bershakaimgurlprefix.$media->path."/".$md5hash.$mediaalt->idMedia."0.jpg");
                              if($httpcode=="200")
                              {
                                $variant->addImage( $bershakaimgurlprefix.$media->path."/".$md5hash.$mediaalt->idMedia."0.jpg");
                              }
                            };//medialtson
                          }//forimedialtson
                          
                          
                          
                        };
                  }*/



                  foreach($resimler as $resim){
                    $variant->addImage($resim);
                  }
                  $variantImages= new variantimages($variant->VariantId,json_encode($variant->Images["Src"]),get_class($this));
                  $variantImages->insert();
              
                }
             $good->addVariant($variant);
        }


     


      }
 

     //////
     
    

       return $good;
    }


function resimGetir($detay,$colorcode){
       $resimler=[];
 
      if ($detay->detail->xmedia){
          foreach ($detay->detail->xmedia as $detaya) {
              if($detaya->colorCode!=$colorcode) continue;
              $patch=$detaya->path; 
              foreach ($detaya->xmediaLocations[0]->locations[0] as $detayx) { 
                  if($detayx) {
                      foreach ($detayx as $value){
                          $hash =  $this->araMedia($detaya->xmediaItems,$value);
                          if($hash!=""){
                            $resimler[]= "https://static.bershka.net/4/photos2".$patch."/".$hash."-".$value."0.jpg";
                          }
                      }
                      
                  }
              }
          }
      }else {
          foreach ($detay->bundleProductSummaries as $detaya) {
              foreach ($detaya->detail->xmedia as $detayx) {
                  $patch=$detayx->path;
                  if($detayx) {
                     if($detayx->colorCode!=$colorcode) continue;
                      foreach ($detayx->xmediaLocations as $values){
                          foreach ($values->locations[0]->mediaLocations as $value){
                              $hash = $this-> araMediam($detayx->xmediaItems,$value);
                                  if($hash!=""){
                                    $resimler[]=  "https://static.bershka.net/4/photos2".$patch."/".$hash."-".$value."0.jpg";
                                  }
                          }
                         
                      }
                  }
    
              }
          }
    
      }
  
    return $resimler;
}

    




    function araMedia($mediaItems,$idMedia) {

    foreach ($mediaItems as $detayx) {

        foreach ($detayx->medias as $eleman) {

            if($eleman->idMedia==$idMedia) {

                if(isset($eleman->extraInfo->hash[0]->md5Hash)) {
                    return $eleman->extraInfo->hash[0]->md5Hash;
                }else{
                    continue;
                }
                
            }

        }


    }

}


function araMediam($mediaItems,$idMedia) {

    foreach ($mediaItems as $detayx) {
        //print_r($detayx);

        foreach ($detayx->medias as $eleman) {

            if($eleman->idMedia==$idMedia) {

                if(isset($eleman->extraInfo->hash[0]->md5Hash)) {
                    return $eleman->extraInfo->hash[0]->md5Hash;
                }else{
                    continue;
                }
                
            }

        }
    }



}



}
?>