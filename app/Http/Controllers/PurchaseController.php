<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Carbon\Carbon;


class PurchaseController extends Controller{ //this controller handles purchase requests from user
    public function purchase($id)//this function handles the purchase request
    {
        try{
            (Int)$id;
        }
        catch(\Throwable $t){
            return array('FAILED! INT');
        }
        if($id<1||$id>2)return array('FAILED! ID');
        set_time_limit(60);
        $client=new Client(); //the first request checks if the item is
        try{                  //out of stock or not
            $response=json_decode($client->get('http://192.168.1.21:8000/info/'.$id)->getBody());
        }
        catch(\Throwable $th){
            return array('FAILED! INFO');
        }
        if($response->qty>0){
            $file=fopen('purchases.csv','a');
            $data=[
                [$id,Carbon::now()],
            ];
            foreach($data as $line)fputcsv($file,$line);
            try {
                $curl = curl_init();
 
curl_setopt_array($curl, array(
  CURLOPT_URL => '192.168.1.21:8000/purchase/'.$id,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'PUT',
));
 
$response = curl_exec($curl);
 
curl_close($curl);
            } catch (ConnectException $th) {
                error_log('Failed put');
            } catch (\Throwable $e){
                error_log('failed : '. $e->getMessage());
            }                                                      //the second request
            fclose($file);                                         //in case the item exists
            return array('DONE!');                                 //it is sent to inform the catalog server
        }                                                          //that the item was purchased 
        else return array('FAILED! QTY');
    }

    public function read()//this function is used for testing purposes only.
    {
        $file=fopen('purchases.csv','r');
        $data=[];
        while(($field=fgetcsv($file))!==FALSE){
            array_push($data,$field);
        }

        return $data;
    }
}