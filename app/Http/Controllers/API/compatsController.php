<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


use App\Models\modules\compats\compats;
use App\Models\modules\compats\compats_product;

class compatsController extends Controller
{
    private static function validateToken($token)
    {
        $expectedToken = (string) config('allstars.api.tokens.compats');

        if($expectedToken === '' || !hash_equals($expectedToken, (string) $token)){
            $data =[
                'status' => 'FAIL',
                'message' => 'API token invalid'
                ];
                
            echo json_encode($data);
            exit;
        }
    }
    
    private static function validateBOtoken($token)
    {
        $expectedToken = (string) config('allstars.api.tokens.compats_backoffice');

        if($expectedToken === '' || !hash_equals($expectedToken, (string) $token)){
            $data =[
                'status' => 'FAIL',
                'message' => 'API token invalid'
                ];
                
            echo json_encode($data);
            exit;
        }
    }
    
    public function getBrands(Request $request)
    {
        self::validateToken($request->token);
        
        $brands = compats::getBrands($request->store);
        
        if( count( $brands ) > 0 ){
            $data = [ 'status' => 'SUCCESS', 'message' => count($brands) . ' BRANDS AVAILABLE', 'data' => $brands ];
        }else{
            $data = [ 'status' => 'SUCCESS', 'message' => 'NO BRANDS AVAILABLE', 'data' => $brands ];
        }
        
        echo json_encode($data);
        exit;
    }
    
    public function getModels(Request $request)
    {
        self::validateToken($request->token);
        
        $models = compats::getModels($request->id_brand, $request->store);
        
        if( count( $models ) > 0 ){
            $data = [ 'status' => 'SUCCESS', 'message' => count($models) . ' MODELS AVAILABLE', 'data' => $models ];
        }else{
            $data = [ 'status' => 'SUCCESS', 'message' => 'NO MODELS AVAILABLE', 'data' => $models ];
        }
        
        echo json_encode($data);
        exit;
    }
    
    public function getTypes(Request $request)
    {
        self::validateToken($request->token);
        
        $types = compats::getTypes($request->id_model, $request->store);
        
        if( count( $types ) > 0 ){
            $data = [ 'status' => 'SUCCESS', 'message' => count($types) . ' TYPES AVAILABLE', 'data' => $types ];
        }else{
            $data = [ 'status' => 'SUCCESS', 'message' => 'NO TYPES AVAILABLE', 'data' => $types ];
        }
        
        echo json_encode($data);
        exit;
    }
    
    
    public function getVersions(Request $request)
    {
        self::validateToken($request->token);
        
        $versions = compats::getVersions($request->id_type, $request->store);
        
        if( count( $versions ) > 0 ){
            $data = [ 'status' => 'SUCCESS', 'message' => count($versions) . ' VERSIONS AVAILABLE', 'data' => $versions ];
        }else{
            $data = [ 'status' => 'SUCCESS', 'message' => 'NO VERSIONS AVAILABLE', 'data' => $versions ];
        }
        
        echo json_encode($data);
        exit;
    }
    
    public function getCompatsFull(Request $request)
    {
        self::validateToken($request->token);
        
        $compat = compats::getCompatsFull($request->id_brand, $request->id_model, $request->id_type, $request->id_version, $request->store);
        
        if( count( $compat) > 0 ){
            $data = [
                'status'    => 'SUCCESS',
                'message'   => count($compat) . ' COMPATS AVAILABLE',
                'data'      => $compat
            ];
        }else{
            $data = [
                'status'    => 'SUCCESS',
                'message'   => 'NO COMPATS AVAILABLE',
                'data'      => $compat
            ];
        }
        
        echo json_encode($data);
        exit;
    }
    
    public function getCompats(Request $request)
    {
        self::validateToken($request->token);
        
        $compat = compats::getCompats($request->id_brand, $request->store);
        
        if( count( $compat) > 0 ){
            $data = [
                'status'    => 'SUCCESS',
                'message'   => count($compat) . ' COMPATS AVAILABLE',
                'data'      => $compat
            ];
        }else{
            $data = [
                'status'    => 'SUCCESS',
                'message'   => 'NO COMPATS AVAILABLE',
                'data'      => $compat
            ];
        }
        
        echo json_encode($data);
        exit;
    }
    
public function getProductCompatDetails(Request $request)
{
    self::validateToken($request->token);

    $compat = compats::getProductCompatDetails($request->id_product, $request->store);

    if (count($compat) > 0) {
        $data = [
            'status' => 'SUCCESS',
            'message' => count($compat) . ' COMPATS AVAILABLE',
            'data' => $compat,
        ];
    } else {
        $data = [
            'status' => 'SUCCESS',
            'message' => 'NO COMPATS AVAILABLE',
            'data' => $compat,
        ];
    }

    echo json_encode($data);
    exit;
}
    
    public function getProducts(Request $request)
    {
        self::validateToken($request->token);
        
        $products = compats_product::getProducts($request->id_compat, $request->store);
        
        if( count( $products) > 0 ){
            $data =[
                'status' => 'SUCCESS',
                'message'   => count($products) . ' PRODUCTS AVAILABLE',
                'compat' => compats::getCompatInfo($request->id_compat, $request->store),
                'data' => $products
            ];
        }else{
            $data = [
                'status'    => 'SUCCESS',
                'message'   => 'NO PRODUCTS AVAILABLE',
                'compat' => compats::getCompatInfo($request->id_compat, $request->store),
                'data'      => $products
            ];
        }
        
        echo json_encode($data);
        exit;
    }
    
    public function getProductCompats(Request $request)
    {
        self::validateToken($request->token);
        
        $compats = compats_product::getCompats($request->id_product, $request->store);
        
        if( count( $compats) > 0 ){
            $data =[
                'status' => 'SUCCESS',
                'message'   => count($compats) . ' COMPATS AVAILABLE',
                'data' => $compats
            ];
        }else{
            $data = [
                'status'    => 'SUCCESS',
                'message'   => 'NO COMPATS AVAILABLE',
                'data'      => $compats
            ];
        }
        
        echo json_encode($data);
        exit;
    }
    
    public function getBObrands(Request $request)
    {
        self::validateBOtoken($request->token);
        
        $brands = compats::getBObrands($request->store);
        
        if( count( $brands ) > 0 ){
            $data = [
                'status'    => 'SUCCESS',
                'message'   => count($brands) . ' BRANDS AVAILABLE',
                'data'      => $brands
            ];
        }else{
            $data = [
                'status'    => 'SUCCESS',
                'message'   => 'NO BRANDS AVAILABLE',
                'data'      => $brands
            ];
        }
        
        echo json_encode($data);
        exit;
    }
    
    public function getBOmodels(Request $request)
    {
        self::validateBOtoken($request->token);

        $models = compats::getBOmodels($request->brand, $request->store);
        
        if( count( $models ) > 0 ){
            $data = [
                'status'    => 'SUCCESS',
                'message'   => count($models) . ' MODELS AVAILABLE',
                'data'      => $models
            ];
        }else{
            $data = [
                'status'    => 'SUCCESS',
                'message'   => 'NO MODELS AVAILABLE',
                'data'      => $models
            ];
        }
        
        echo json_encode($data);
        exit;
    }
    
    public function getBOtypes(Request $request)
    {
        self::validateBOtoken($request->token);
        
        $types = compats::getBOtypes($request->brand, $request->model, $request->store);
        
        if( count( $types ) > 0 ){
            $data = [
                'status'    => 'SUCCESS',
                'message'   => count($types) . ' TYPES AVAILABLE',
                'data'      => $types
            ];
        }else{
            $data = [
                'status'    => 'SUCCESS',
                'message'   => 'NO TYPES AVAILABLE',
                'data'      => $types
            ];
        }
        
        echo json_encode($data);
        exit;
    }
    
    public function getBOversions(Request $request)
    {
        self::validateBOtoken($request->token);
        
        $versions = compats::getBOversions($request->brand, $request->model, $request->type, $request->store);
        
        if( count( $versions ) > 0 ){
            $data = [
                'status'    => 'SUCCESS',
                'message'   => count($versions) . ' VERSIONS AVAILABLE',
                'data'      => $versions
            ];
        }else{
            $data = [
                'status'    => 'SUCCESS',
                'message'   => 'NO VERSIONS AVAILABLE',
                'data'      => $versions
            ];
        }
        
        echo json_encode($data);
        exit;
    }
    
    public function createCompats(Request $request)
    {
        self::validateBOtoken($request->token);
        
        $created = compats_product::createCompat($request->brand, $request->model, $request->type, $request->version, $request->product, $request->store);

        if( $created == 1 ){
            $data = [ 'status' => 'SUCCESS', 'message' => 'COMPAT CREATED SUCCESSFULLY!' ];
        }else{
            $data = [ 'status' => 'FAIL',    'message' => 'COMPAT FAIL TO CREATE!' ];
        }
        
        echo json_encode($data);
        exit;
    }
    
    public function removeCompats(Request $request)
    {
        
        self::validateBOtoken($request->token);
        
        $created = compats_product::removeCompat($request->compat, $request->store);

        if( $created == 1 ){
            $data = [ 'status' => 'SUCCESS', 'message' => 'COMPAT REMOVED!' ];
        }else{
            $data = [ 'status' => 'FAIL',    'message' => 'COMPAT FAIL TO REMOVE!' ];
        }
        
        echo json_encode($data);
        exit;
    }
    
    public function getAllCompats(Request $request)
    {
        self::validateBOtoken($request->token);
        
        $compat = compats::getAllCompatsBO($request->store);
        
        if( count( $compat) > 0 ){
            $data = [
                'status'    => 'SUCCESS',
                'message'   => count($compat) . ' COMPATS AVAILABLE',
                'data'      => $compat
            ];
        }else{
            $data = [
                'status'    => 'SUCCESS',
                'message'   => 'NO COMPATS AVAILABLE',
                'data'      => $compat
            ];
        }
        
        echo json_encode($data);
        exit;
    }
}
