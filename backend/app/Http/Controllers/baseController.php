<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use App\Http\Controllers\stdClass;
use Log;

class baseController extends Controller
{
    public function index(Request $request){
        if($request->session()->has('users')){
            return redirect('dashboard');
        }else{
            $header_tagline= DB::table('konten')->where('nama_konten', 'Tagline Header Home')->value('konten');
            $section_tagline = DB::table('konten')->where('nama_konten', 'Tagline Section Home')->value('konten');
            $about = DB::table('konten')->where('nama_konten', 'About Us')->value('konten');
            return view('pages.home', ['about'=>$about, 'header_tagline'=>$header_tagline, 'section_tagline'=>$section_tagline]);     
        }  
    }

    public function productDetails(){
        $product = DB::table('produk')->where()->value();
        $state_product = DB::table('state_produk')->where()->value();
        $concentration_product = DB::table('concentration_produk')->where()->value();    
        return view('pages.product-details', ['product'=>$product, 'state_product'=>$state_product, 'concentration_product'=>$concentration_product]);
    }

    public function dashboard(Request $request){
        if($request->session()->has('users')){
            $products = DB::table('produk')->simplePaginate(10);
            $services = DB::table('service')->simplePaginate(10);
            $contents = DB::table('konten')->get();
            $category = DB::table('kategori')->get();
            
            return view('pages.dashboard',['products'=> $products, 'categories'=>$category, 'services'=>$services, 'contents'=>$contents]);    
        }else{
            return redirect('home');
        }
    }

    public function details(){
        $product_name= Input::get('product');
        $product_info=DB::table('produk')->where('nama',$product_name)->value('deskripsi');
        $concentration= DB::table('produk')->where('nama',$product_name)->value('concentration');
        $state= DB::table('produk')->where('nama',$product_name)->value('state');
        $packaging_product = DB::table('produk')->where('nama',$product_name)->value('packaging'); 
        $header_tagline= DB::table('konten')->where('nama_konten', 'Tagline Header Home')->value('konten');   
        //$concentration= DB::table('concentration_produk')->where('nama',$product_name)->get();
        //$state =DB::table('state_produk')->where('nama',$product_name)->get();
        return view('pages.details',['product'=>$product_name,'concentration'=>$concentration,'state'=>$state,'deskripsi'=>$product_info, 'packaging'=>$packaging_product, 'header_tagline'=>$header_tagline]);
    }

    public function detailAdmin(Request $request){
        $product_name= Input::get('product');
        $concentration= DB::table('produk')->where('nama',$product_name)->value('concentration');
        $state= DB::table('produk')->where('nama',$product_name)->value('state');
        $product_info=DB::table('produk')->where('nama',$product_name)->value('deskripsi');
        $packaging_product = DB::table('produk')->where('nama',$product_name)->value('packaging');    
        //$concentration= DB::table('concentration_produk')->where('nama',$product_name)->get();
        //$state =DB::table('state_produk')->where('nama',$product_name)->get();
        return view('pages.detail-admin',['product'=>$product_name,'concentration'=>$concentration,'state'=>$state, 'packaging'=>$packaging_product, 'deskripsi'=>$product_info]);
    }
    public function login() {
        return view('pages.login');
    }

    public function about(){
        $about = DB::table('konten')->where('nama_konten', 'About Us')->value('konten');
        return view('pages.about', ['about'=>$about]);
    }

    public function products(Request $request){
        $about = DB::table('konten')->where('nama_konten', 'About Us')->value('konten');
        $header_tagline= DB::table('konten')->where('nama_konten', 'Tagline Header Product')->value('konten');
        $category = DB::table('kategori')->get();
        

        if($request->has('product_name')){
            $product_name = $request->input('product_name');
            // $products = DB::select(DB::raw("SELECT * FROM PRODUK WHERE nama IN (SELECT nama_produk FROM kategori_produk WHERE nama_kategori='$product_category') "));
           $products = DB::select(DB::raw("SELECT * FROM PRODUK WHERE nama LIKE '%$product_name%'"));
           $jumlahProduk = count($products);
           $pesan = "Maaf, produk tidak tersedia";

           if ($jumlahProduk==0) {
             return view('pages.products-filter', ['products' => $products, 'header_tagline'=>$header_tagline, 'about'=>$about, 'categories'=>$category,'pesan'=>$pesan]); 
           } else {
             return view('pages.products-filter', ['products' => $products, 'header_tagline'=>$header_tagline, 'about'=>$about, 'categories'=>$category]);
           }      
           
        }else{
            $products = DB::table('produk')->simplePaginate(10);

            return view('pages.products', ['products' => $products, 'header_tagline'=>$header_tagline, 'about'=>$about]);
        }

    }

    public function editProduct(Request $request){
        $nama = $request->input('nama');
        //$kategori = $request->input('kategori');
        $deskripsi = $request->input('deskripsi');
        
        DB::table('produk')
                ->where('nama', $nama)
                ->update(array('deskripsi' => $deskripsi));
        return redirect('dashboard');

    }
    public function tes(){
        return view('pages.tes');
    }

    public function services(){
        $about = DB::table('konten')->where('nama_konten', 'About Us')->value('konten');
        $header_tagline= DB::table('konten')->where('nama_konten', 'Tagline Header Service')->value('konten');
        $services = DB::table('service')->simplePaginate(10);
        return view('pages.services', ['services' => $services, 'header_tagline'=>$header_tagline, 'about'=>$about]);
    }

    public function addService(Request $request) {
        error_log('message');
        $serviceName = $request->input('service_name');
        $serviceDesc = $request->input('service_desc');
        DB::table('service')->insert(['nama'=> $serviceName, 'deskripsi'=>$serviceDesc]);
        return redirect('dashboard');
    }

  
    public function editService(Request $request) {
        $serviceName = $request->input('service_name_edit');
        $serviceDesc = $request->input('service_desc_edit');
        DB::table('service')
                ->where('nama', $serviceName)
                ->update(array('deskripsi' => $serviceDesc));
        return redirect('dashboard');
    }

    public function deleteService(Request $request) {
        $serviceName = $request->input('service_name_delete');
        DB::table('service')->where('nama','=',$serviceName)->delete();
        return redirect('dashboard');
    }

    public function deleteProduct(Request $request) {
        $productName = $request->input('product_name_delete');
        //DB::table('state_produk')->where('nama','=',$productName)->delete();
        //DB::table('concentration_produk')->where('nama','=',$productName)->delete();
        DB::table('kategori_produk')->where('nama_produk','=',$productName)->delete();
        DB::table('produk')->where('nama','=',$productName)->delete();
        
        return redirect('dashboard');
    }

    public function editContent(Request $request) {
        $contentName = $request->input('content_name_edit');
        $contentDesc = $request->input('content_desc_edit');
        DB::table('konten')
                ->where('nama_konten', $contentName)
                ->update(array('konten' => $contentDesc));
       // DB::table('service')->insert(['nama'=> $serviceName, 'deskripsi'=>$serviceDesc]);
        return redirect('dashboard');
    }

    public function loginAdmin(Request $request) {
        $email = $request->input('email');
        $password = $request->input('password');

        $account = DB::table('akun')->where('email',$email)->count();
        $passwordDB = DB::table('akun')->where('email',$email)->value('password');

        if ($account==1 && $passwordDB==$password) {
            $request->session()->put('users', '$email');
            return redirect('dashboard');
        }else {
             return redirect('admin-login');    
        }
    }

    public function logout(Request $request){
        $request->session()->flush();
        return redirect('home');   
    }
    public function addProduct(Request $request){
        error_log('message');
        
        $product_name= $request->input('product_name');
        $product_desc = $request->input('product_desc');
        $product_state = $request->input('product_state');
        $product_concentration = $request->input('product_concentration');
        $category = $request->input('product_category');
        $counter_category = count($category);
        $product_packaging = $request->input('product_packaging');
        
      
        DB::table('produk')->insert(['nama'=> $product_name, 'deskripsi'=>$product_desc,'state'=>$product_state,'concentration'=>$product_concentration, 'packaging'=>$product_packaging]);
        for($i=0;$i<$counter_category;$i++){
            DB::table('kategori_produk')->insert(['nama_produk'=>$product_name, 'nama_kategori'=>$category[$i]]);
        } 
        return redirect('dashboard');
       
    }

}
