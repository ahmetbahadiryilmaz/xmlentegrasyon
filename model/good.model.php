<?




Class Good{
    public $GoodId;
    public $SKU;
    public $Title;
    public $Description;
    public $Brand;
    public $REFERENCE;
    public $Category;
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



  

 