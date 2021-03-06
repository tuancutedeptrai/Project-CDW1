<?php

namespace App\Http\Controllers;

use App\Models\order_details;
use App\Models\review;
use App\Models\user_cart;
use Illuminate\Http\Request;
use App\Models\products;
use Illuminate\Support\Facades\DB;
use App\Models\categories;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = products::all()->toArray();
        $return = [];
        foreach ($products as $item) {
            $item['id'] = $this->Xulyid($item['id']);
            $return[] = $item;
        }
        return response()->json($return);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|min:2|max:40|unique:products,product_name',
            'price' => 'required',
           'product_image' => 'required',
            'description' => 'required|min:10|max:200',
            'quantity' => 'required',
            'category_id' => 'required'
        ]);
        if (!$request->hasFile('product_image')) {
            return  response()->json(['status' => 'Please Choose File'],400);
        }
        $image = $request->file('product_image');
        $array_image_type = ['png', 'jpg', 'jpeg', 'svg'];
        if (!in_array($image->getClientOriginalExtension(), $array_image_type)) {
            return  response()->json(['status' => 'Please Choose type image is png  or jpg  or jpeg or svg'],400);
        }
        $checksize = 2097152;
        if ($image->getSize() > $checksize) {
            return  response()->json(['status' => 'Please file is shorter than 2mb'],400);
        }
         $request->category_id = $this->DichId($request->category_id);
        try {
            categories::findOrFail($request->category_id);
        } catch (\Exception $exception) {
            return  response()->json(['status' => 'Invalid category - category must is a number in select'],400);
        }
        if ($validator->fails()) {
            return  response()->json($validator->messages(),400);
        }
        $pattern_Integer = '/^\d{1,}$/';
        // x??t pattern  quantity + categoryid
        if (!preg_match($pattern_Integer, $request->quantity) || !preg_match($pattern_Integer, $request->category_id)) {
            if (!preg_match($pattern_Integer, $request->quantity)) {
                return  response()->json(['status' => 'quantity must is positive integers'],400);
            } else {
                return  response()->json(['status' => 'Category id must is positive integers'],400);
            }
        }
        // Kh??c n??y ch?? ?? nh???p gi?? nh???p s???  th??? n??y :
        // EX : 123.22 b???t bu???c c?? 2 s??? sau d???u ch???m c??n s??? ??? tr?????c th?? bao nhi??u s??? c??ng dc
        // EX : 123.22(d???u ch???m not d???u ph???y ) , 88.32,99.12,642.88,54622.99
        $pattern_price = '/^\d{1,}\.{1,1}\d{2,2}$/';
        if (!preg_match($pattern_price, $request->price)) {
            return  response()->json(['status' => 'Price must have 2 number after dot and must is not negative '],400);
        }

        // dd(storage_path('public/' .'1638934974tong-hop-cac-mau-background-dep-nhat-10070-6.jpg'));
        // ??o???n code tr??n ko dc x??a , n?? l?? ???????ng link ???nh l??u l??n db ????
        $filename = time() . $image->getClientOriginalName();
        $duongdan = 'storage/' . $filename; // c??i n??y ????? l??u l??n database

        $request->file('product_image')->storeAs('public', $filename);
        $product = new products([
            'product_name' => $request->get('product_name'),
            'price' => $request->get('price'),
            'description' => $request->get('description'),
            'quantity' => $request->get('quantity'),
            'product_image' => $duongdan,
            'category_id' => $request->category_id,
        ]);
        $product->save();
        return  response()->json(['status' => 'Create Product Success'],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id) //get item by id
    {
        // dd($request);
        $pro_id = $this->DichId($id);
        $product = products::where('id', '=', $pro_id)->get();
//        dd($product[0]->category_id);

        if ($product) {
            $category_name = categories::where('id','=',$product[0]->category_id)->get('name');
            $product[0]->category_name= $category_name[0]->name;
            unset($product[0]->category_id);
            return response()->json([
                'message' => 'product found!',
                'product' => $product,
            ]);
        }
        return response()->json([
            'message' => 'product not found!',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) //update
    {


//        dd("Di vao ham update");
//        dd("Di v??o h??m update");
//        $list = products::all()->toArray();
//        $return = [];
//        foreach ($list as $item) {
//            $item['id'] = $this->Xulyid($item['id']);
//            $return[] = $item;
//        }
//        $pro_id = $this->DichId($id);
//        $product = products::find($pro_id);
//        if ($product) {
//
//
//            $validator = Validator::make($request->all(), [
//                'product_name' => 'required',
//            ]);
//
//            if ($validator->fails()) {
//                return response(['errors' => $validator->errors()->all()], 422);
//            }
//            //Kiem tra product_name da co hay chua, co bi trung khong
//            if ($request->product_name == $list[0]['product_name']) {
//                return response()->json([
//                    'message' => 'The product_name has been exits!!!',
//                ]);
//            } else {
//                $pro_name = $request->product_name;
//            }
//
//
//            $product->update([
//                $product->product_name = $pro_name,
//                $product->price = $request->get('price'),
//                $product->description = $request->get('description'),
//                $product->quantity = $request->get('quantity'),
//                // $product->pro_image = $request->get('pro_image');
//            ]);
//
//
//            $product->save();
//            return response()->json([
//                'message' => 'product updated! 121212',
//                'product' => $product
//            ]);
//        }
//
//        return response()->json([
//            'message' => 'product khong tim thay!!!'
//        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public  function my_update(Request $request,$id){
        $pro_id = $this->DichId($id);
        $data_from_query = products::find($pro_id);
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|min:2|max:40',
            'price' => 'required',
           'product_image' => 'required',
            'description' => 'required|min:10|max:200',
            'quantity' => 'required',
            'category_id' => 'required'
        ]);
         if (!$request->hasFile('product_image')) {
             return  response()->json(['status' => 'Please Choose File'],400);
         }
        $image = $request->file('product_image');
        $array_image_type = ['png', 'jpg', 'jpeg', 'svg'];
        if (!in_array($image->getClientOriginalExtension(), $array_image_type)) {
            return  response()->json(['status' => 'Please Choose type image is png  or jpg  or jpeg or svg'],400);
        }
        $checksize = 2097152;
        if ($image->getSize() > $checksize) {
            return  response()->json(['status' => 'Please file is shorter than 2mb'],400);
        }
         $request->category_id = $this->DichId($request->category_id);
        try {
            categories::findOrFail($request->category_id);
        } catch (\Exception $exception) {
            return  response()->json(['status' => 'Invalid category - category must is a number in select'],400);
        }
        if ($validator->fails()) {
            return  response()->json($validator->messages(),400);
        }
        $pattern_Integer = '/^\d{1,}$/';
        // x??t pattern  quantity + categoryid
        if (!preg_match($pattern_Integer, $request->quantity) || !preg_match($pattern_Integer, $request->category_id)) {
            if (!preg_match($pattern_Integer, $request->quantity)) {
                return  response()->json(['status' => 'quantity must is positive integers'],400);
            } else {
                return  response()->json(['status' => 'Category id must is positive integers'],400);
            }
        }
        // Kh??c n??y ch?? ?? nh???p gi?? nh???p s???  th??? n??y :
        // EX : 123.22 b???t bu???c c?? 2 s??? sau d???u ch???m c??n s??? ??? tr?????c th?? bao nhi??u s??? c??ng dc
        // EX : 123.22(d???u ch???m not d???u ph???y ) , 88.32,99.12,642.88,54622.99
        $pattern_price = '/^\d{1,}\.{1,1}\d{2,2}$/';
        if (!preg_match($pattern_price, $request->price)) {
            return  response()->json(['status' => 'Price must have 2 number after dot and must is not negative '],400);
        }

        // dd(storage_path('public/' .'1638934974tong-hop-cac-mau-background-dep-nhat-10070-6.jpg'));
        // ??o???n code tr??n ko dc x??a , n?? l?? ???????ng link ???nh l??u l??n db ????
        $filename = time() . $image->getClientOriginalName();

        if ($request->product_name != $data_from_query->product_name){
            // Ki???m tra d??? li???u ????a v??o input v???i d??? li???u query theo id
            // N???u 2 d??? li???u ko = nhau -> 1 c??i t??n m???i
            $list = products::where('id','!=',$pro_id)->get(['product_name'])->ToArray();

            if (in_array($request->product_name,$list)){
                return response()->json([
                    'message' => 'The product_name has been exits!!!',
                ],400);
            }
            else{
                $duongdan = 'storage/' . $filename; // c??i n??y ????? l??u l??n database
                $request->file('product_image')->storeAs('public', $filename);
                $data_from_query->update([
                    $data_from_query->product_name = $request->product_name,
                    $data_from_query->category_id = $request->category_id,
                    $data_from_query->quantity = $request->quantity,
                    $data_from_query->price = $request->price,
                    $data_from_query->description = $request->description,
                    $data_from_query->product_image = $duongdan
                ]);
                return response()->json([
                    'message' => 'Update success',
                ],200);
            }
        }
        else{
            // N???u d??? li???u input vs d??? li???u query theo id = nhau -> l?? d??? li???u c??
            // Ti???n h??nh update
            $pattern_Integer = '/^\d{1,}$/';
            if (!preg_match($pattern_Integer, $request->quantity) || !preg_match($pattern_Integer, $request->category_id)) {
                if (!preg_match($pattern_Integer, $request->quantity)) {
                    return  response()->json(['status' => 'quantity must is positive integers'],400);
                } else {
                    return  response()->json(['status' => 'Category id must is positive integers'],400);
                }
            }
            $pattern_price = '/^\d{1,}\.{1,1}\d{2,2}$/';
            if (!preg_match($pattern_price, $request->price)) {
                return  response()->json(['status' => 'Price must have 2 number after dot and must is not negative '],400);
            }
            $duongdan = 'storage/' . $filename; // c??i n??y ????? l??u l??n database
            $request->file('product_image')->storeAs('public', $filename);
            $data_from_query->update([
                $data_from_query->product_name = $request->product_name,
                $data_from_query->category_id = $request->category_id,
                $data_from_query->quantity = $request->quantity,
                $data_from_query->price = $request->price,
                $data_from_query->description = $request->description,
                $data_from_query->product_image = $duongdan
            ]);
            return response()->json([
                'message' => 'Update success',
            ],200);
        }
    }


    public function destroy($id) //remove
    {
        $id = $this->DichId($id);
        $pattern_id = '/^\d{1,}$/';
        if (!preg_match($pattern_id, $id)) {
            return  response()->json(['message' => 'Please type id is a number']);
        }
        $flag = true;
        $product = products::find($id);
        if ($product) {
            if (!$product) {
                return response()->json([
                    'message' => 'product not found !!!'
                ]);
            }

            $userCartListTemp = user_cart::where("product_id", "=", $id)->get();
            $ordersDetailListTemp = order_details::where("product_id", "=", $id)->get();

            if (count($userCartListTemp) !== 0) {
                $flag = false;
            }
            if (count($ordersDetailListTemp) !== 0) {
                $flag = false;
            }

            if ($flag) {
                $reviewsListRemove = review::where("product_id", "=", $id)->delete();
                $product->delete();
                return response()->json([
                    'message' => 'product and reviews depended deleted'
                ]);
               }
        }
        return response()->json([
            'message' => "can't delete product because have related ingredients."
        ],400);
    }

    private function getName($n)
    {
        $characters = '162379812362378dhajsduqwyeuiasuiqwy460123';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }

    public  function Xulyid($id): String
    {
        $dodaichuoi = strlen($id);
        $chuoitruoc = $this->getName(10);
        $chuoisau = $this->getName(22);
        $handle_id = base64_encode($chuoitruoc . $id . $chuoisau);
        return $handle_id;
    }

    public function DichId($id)
    {
        $id = base64_decode($id);
        $handleFirst = substr($id, 10);
        $idx = "";
        for ($i = 0; $i < strlen($handleFirst) - 22; $i++) {
            $idx .= $handleFirst[$i];
        }
        return  $idx;
    }

    public function filterProduct(Request $request)
    {
        $filterField = "product_name";
        $filterOption = "DESC";
        if ($request->key &&  $request->filter) {
            switch ($request->filter) {
                case "za": {
                        $filterOption = "ASC";
                        break;
                    }
                case "az": {
                        $filterOption = "DESC";
                        break;
                    }
                case "price-high-low": {
                        $filterField = "price";
                        $filterOption = "DESC";
                        break;
                    }
                case "price-low-high": {
                        $filterField = "price";
                        $filterOption = "ASC";
                        break;
                    }
                default: {
                        $filterField = "product_name";
                        $filterOption = "DESC";
                        break;
                    }
            }
            $productList = products::where('category_id', 'like', '%' . $request->key . '%')
                ->orderBy($filterField, $filterOption)
                ->get();
            return response()->json([
                'products' => $productList,
            ]);
        }
    }

    public function getSearch(Request $request)
    {
        if ($request->key) {
            $product = products::where('product_name', 'like', '%' . $request->key . '%')
            ->orwhere('price', 'like', '%' . $request->key . '%')
            ->get();
        //   return view('admin.product.search', compact('product'));
        } else {
            return response()->json([
                'message' => 'No product found',
            ]);
        }

        if ($product) {
            if (empty(count($product))) {
                return response()->json([
                    'message' => 'product not found!',
                ]);
            } else {
                return response()->json([
                    'message' => count($product) . ' product found!!!',
                    'item' => $product
                ]);
            }
        }
    }
    public function GetProductById($productId)
    {
        $id = $productId;
        $pattern_product_id = '/^\d{1,}$/';
        if (!preg_match($pattern_product_id, $id)) {
            return  response()->json(['status' => "Please Type Id is Correct is a Number"]);
        }
        try { // T??m ki???m product id n???u kh??ng ra th?? v?? c??i c???c catch th??i
            $product = products::findOrFail($id);
            $catename = categories::find($product->category_id);
            $category_SameType = $product->category_id;
            $sosp1trang = 4;
            $productSameType = DB::select("    SELECT * FROM `products` WHERE products.category_id = $category_SameType  ORDER BY RAND() LIMIT $sosp1trang;");
        } catch (\Exception $exception) {
            return  response()->json(['status' => "Not Found Product "]);
        }
        return  response()->json(['product' => $product, 'category' => $catename, 'relatedProducts' => $productSameType]);
    }
}
