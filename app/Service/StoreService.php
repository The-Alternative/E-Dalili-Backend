<?php


namespace App\Service;
use App\category;
use App\City;
use App\Governorate;
use App\product;
use App\product_image;
use App\Store;
use App\Street;
use App\User;
use Illuminate\Http\Request;


class StoreService
{
    private $product;
    private $storeModel;
    private $pimage;

    public function __construct(product $product,Store $store,product_image $product_image)
    {

        $this->storeModel=$store;
        $this->product=$product;
        $this->pimage=$product_image;

    }

    public function createStore(Request $request) {
        if ($request->is_active){
            $is_active=true;
        }else{
            $is_active=false;
        }
        $request['default_lang']=(int)$request['default_lang'];
        $request['user']=(int)$request['user'];
        $response = $this->storeModel::create([
            'title'              => $request->title,
            'is_active'          => $is_active,
            'is_approved'        => false,
            'default_lang'       => $request->default_lang,
            'phone_number'       => $request->phone_number,
            'bussiness_email'    => $request->bussiness_email,
            'logo'               => $request->logo,
            'address'            => $request->address,
            'location'           => $request->location,
            'working_hours'      => $request->working_hours,
            'working_days'       => $request->working_days,
            'user_id'            => $request->user

        ]);
        return $response;
    }

    public function addProductsToStore(Request $request,Store $store){

        for ($i=0;$i<(int)$request->counter;$i++){
            $store->products()->attach($request->product[$i],[
                'is_active' => (int)$request->is_active,
                'price'     => (float)$request->price,
                'qty'       => (int)$request->qty,
            ]);
        }
    }



    public function deleteStore(Store $store){
        $response = $store->update([
            'is_approved' => false,
            'is_activve'  => false
        ]);

    }

    public function update (Request $request,Store $store){

        if ($request->is_active){
            $is_active=true;
        }else{
            $is_active=false;
        }
        $response = $store->update([
            'title'              => $request->title,
            'is_active'          => $is_active,
            'is_approved'        => false, //when user update his store he needs to approve this updates by admin
            'default_lang'       => $request->default_lang,
            'phone_number'       => $request->phone_number,
            'bussiness_email'    => $request->bussiness_email,
            'logo'               => $request->logo,
            'address'            => $request->address,
            'location'           => $request->location,
            'working_hours'      => $request->working_hours,
            'working_days'       => $request->working_days,
            'user_id'            => $request->user
        ]);
        if($response=true){
            return "success";
        }else{
            return "faild";
        }
    }

    public function updateProducts (Request $request,Store $store){
        $links=[];
        for($i=0;$i<$request->counter;$i++){
            $links[$request->product[$i]] = ['is_active'=>(int)$request->product_active[$i],'is_approve'=>(int)$request->product_approve[$i],'price'=>(float)$request->price[$i],'qty'=>(int)$request->qty[$i]];
        }
        $store->products()->sync($links);
    }

    public function stores(){
        return $this->storeModel::all();
    }

    public function approvedStores(){
        return $this->storeModel::all()->where('is_approved',1);
    }
    public function activeStores(){
        return $this->storeModel::all()->where('is_active',1);
    }

    public function storeDetailes($id){
        return $this->storeModel::all()->where('id',$id);
    }

    public function comparePrices($id){
        $stores = $this->product->where('id',$id)->first()->stores()->get();
            foreach ($stores as $store){
                $x[]=$store->pivot->price;
             }
        $y=999999999999999999999999999999999999;
        $e=0;
        foreach ($x as $t){
            if ($t<$y){
                $y=$t;
            }
            if ($t>$e){
                $e=$t;
            }
        }

        $min = $y;
        $max = $e;
        return 'min='.$min.'and max='.$max;
    }

    public function index(){
        return view('stores.index')->with('stores',Store::all()->where('is_active',true))
            ->with('cities',City::all()->where('is_active',true))
            ->with('governorates',Governorate::all()->where('is_active',true))
            ->with('streets',Street::all()->where('is_active',true))
            ->with('categories',category::all()->where('is_active',true))
            ->with('products',product::all()->where('is_active',true));
    }

    public function create(){
        return view('stores.create')->with('users',User::all());
    }

    public function edit($store){
        return view(stores.edit,[
            'store'      => $store,
            'users'      =>User::all(),
            'products'   =>product::all()
        ]);
    }

    public function addProductToDataBase(Request $request)
    {
        $request['brand_id']=(int)$request['brand_id'];
        $request->validate([
            "title"          => "required:products",
            "slug"           => "required:products",
            "barcode"        => "required:products",
            "productcol"     => "required:products",
            "meta"           => "required:products",
            "description"    => "required:products",
        ]);
        if ($request->is_active){
            $is_active=true;
        }else{
            $is_active=false;
        }
        if ($request->is_appear){
            $is_appear=true;
        }else{
            $is_appear=false;
        }

        //var_dump($request);
        $response=$this->product::create([
            'title'         => $request->title,
            'slug'          => $request->slug,
            'brand_id'      => $request->brand_id,
            'barcode'       => $request->barcode,
            'productcol'    => $request->productcol,
            'meta'          => $request->meta,
            'is_active'     => $is_active,
            'is_appear'     => false,
            'description'   => $request->description,
        ]);
        for ($i=0;$i<(int)$request->counter;$i++){
            $response->customfields()->attach($request->custom_field[$i],[
                'value' => $request->value[$i],
                'description' => "sssssss",
            ]);
        }
        for ($i=0;$i<(int)$request->ccounter;$i++){
            $response->categories()->attach($request->category[$i],['description'=>$request->cdescription[$i]]);
        }



        for ($i = 0;$i< (int)$request->icounter;$i++){
            $e =$i + 1;
            if ( (int)$request->iscover == $e ){
                $xx[$i]=true;
            }else{
                $xx[$i]=false;
            }
        }
        for ($i = 0;$i< (int)$request->icounter;$i++) {

            $this->pimage::create([
                'product_id' => $response->id,
                'image'      =>$request->image[$i]->store('images','public'),
                'is_cover'   =>$xx[$i]
            ]);



        }

        session()->flash('success','product created successfuly');

        return $request;

    }
//    public function deleteProductsfromStore(Request $request,Store $store){
//
//        for ($i=0;$i<(int)$request->counter;$i++){
//            $store->products()->deattach($request->product[$i]);
//        }
//    }

}
