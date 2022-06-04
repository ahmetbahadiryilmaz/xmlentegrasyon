<?


use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


Class Good{
    public $GoodId;
    public $SKU;
    public $Title;
    public $Description;
    public $Brand;
    public $REFERENCE;
    public $Category;
    public $CategoryTree;
    public $Stock;
    public $Price;
    public $SellingPrice="Fiyat1";
    public $TotalSellingPrice="Fiyat2";
    public $TotalArrivalPrice="Fiyat3";
    public $Images=[];
    public $Variants=[];
    
    public function __construct( ) {
        $this->Images["Src"]=[];
    }
    public function addImage($url)
    {
        $this->Images["Src"][]=$url;
    }
    public function addVariant(variant $variant)
    {
        $this->Variants[]=$variant;
    }

    public function toArr()
    {
    
            /** @var good $good */
    
    $good=$this;
    
    $variantsarr= [];
    foreach ($good->Variants as $variant) {
        if(!$variant->VaryantReference)unset($variant->VaryantReference);
    
        $variantsarr["Variant"][]= $variant ;
    }
    $good->Variants=$variantsarr;

    if(count($good->Images["Src"])<1){
        if(isset($good->Variants["Variant"])){
            $good->Images=$good->Variants["Variant"][0]->Images;
        }
    } 

    if($good->REFERENCE=="")unset($good->REFERENCE);
    if($good->CategoryTree=="")unset($good->CategoryTree);
  
    
    return $good;
    }

     public function toXml()
     {
         
                
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->serialize(   $this->toArr()  , 'xml',[
            'xml_format_output' => true, 
            'xml_encoding' => 'utf-8',
            'xml_root_node_name' => 'Good'
          ]);

         
     
        echo str_replace('<?xml version="1.0" encoding="utf-8"?>','',$jsonContent); // or return it in a Response

     }
}

 

class variant{
    public $VariantId;
    public $VaryantReference;
    public $SKU;
    public $Stock;
    public $Price;
    public $SellingPrice;
    public $TotalSellingPrice;
    public $TotalArrivalPrice;
    public $Images;
    public $Features;
    public function __construct($size,$color) {
        $size=   [ "Key"=>"Size","Value"=>$size];
        $color=   [ "Key"=>"Color","Value"=>$color];
         
        $this->Features["Feature"][]= $size;
        $this->Features["Feature"][]= $color;
        $this->Images["Src"]=[];
    }
    public function addImage($url)
    {
        $this->Images["Src"][]=$url;


    }


 
}

class feature{
    public $key;
    public $value;
    public function __construct($key,$value) {
        $this->key = $key;
        $this->value = $value;
    }
   
}



  

 