<? php
require 'vendor/autoload.php';
use Aws\Sdk;
use Aws\DynamoDb\Marshaler;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;

class dynamoDB
{
    public function recommend_function($jobs) {
        $marshaler = new Marshaler();
        
        $for_dynamoDB = array();
        
        
        foreach($jobs as $mainjob){
            $sub_array= array();
            $min_array= array();
            $main_job_metas = json_decode($mainjob->metas);
            foreach($jobs as $subjob){
                if($mainjob->ID == $subjob->ID){
                    continue;
                }
                $sub_job_metas = json_decode($subjob->metas);
                //比較するカラム名の指定　
                if($main_job_metas->original_job_position == $sub_job_metas->original_job_position){
                    $min_array[]=$subjob->ID;
                }
            }
            $for_dynamoDB[] = ["post_id" => $mainjob->ID, "recommend_id" => $min_array];
        }
        // $for_dynamoDB =json_encode($for_dynamoDB);
        var_dump($for_dynamoDB);   
        $this->dynamoDB_controller($for_dynamoDB);
    }
    public function dynamoDB_controller($params){
            $marshaler = new Marshaler();
            $dynamodb = DynamoDbClient::factory([
                'credentials' => [
                    'key' => 'アマゾンで発行するKey',
                    'secret' => 'アマゾンから発行するシークレットKey',
                ],
                //東京リージョン
                'region' => 'ap-northeast-1',
                'version' => 'latest'
            ]);

            try {
                foreach ($params as $param) {
                    //テーブル作成
                    var_dump($param);
                    $response = $dynamodb->putItem([
                        'TableName' => 'recommendForIndeed',
                        'Item' => $marshaler->marshalJson(json_encode($param))
                    ]);
            
                    if ($response['@metadata']['statusCode'] !== 200) {
                        echo 'Failed.';
                        exit();
                    }
            
                }
            //すでにテーブルが作成されている場合、例外処理が発生する
            } catch (DynamoDbException $e) {
                var_dump($e->getMessage());
                exit();
            }
            
            echo 'Successfully.';
    }
}