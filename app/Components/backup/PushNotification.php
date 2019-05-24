<?php
namespace App\Components;

use Illuminate\Support\Facades\App;
use Aws\Sns\Exception\SnsException;
use Aws\Credentials\Credentials;
use Aws\Sns\SnsClient;
/**
 * Class Push Notification
 */
class PushNotification {

    const TYPE_ANDROID = 0;
    const TYPE_IOS = 1;
    const ENV_IOS_ATTR_PROD = 'APNS';
    const ENV_IOS_ATTR_DEV = 'APNS_SANDBOX';

    /**
     * Send Android/IOS Notification
     */
    public static function send($type,$data,$screenType) 
    {
        if ($type == self::TYPE_IOS){
            $platformApplicationArn = env('IOS_APPLICATION_ARN');
             $payloads = [
                            "aps" => [
                                'alert' => $data['message'],
                                'sound' => 'default',
                                'badge' => 1,
                                'title-loc-key'=> $screenType,
                                'userInfo'=> [ 'screen'=> $screenType ],
                                'foreground'=>true,
                                'data'=> [ 'screen'=> $screenType ],
                            ],
                            'sound' => 'default',
                            'screen'=> $screenType,
                        ];

            $payloads = json_encode($payloads);
            $message = json_encode(["default" => $data['message'], self::ENV_IOS_ATTR_PROD => $payloads]);
        }
        else
        {
            $platformApplicationArn = env('ANDROID_APPLICATION_ARN');
            $payloads = [
                            "data" => [
                                'message' => $data['message'],
                                'sound' => 'default',
                                'ongoing'=> true,
                                'userInfo'=> [ 'screen'=> $screenType ],
                            ]
                        ];
            $payloads = json_encode($payloads);
            $message = json_encode(["default" => $data['message'], 'GCM' => $payloads]);
        }
        try {
            $endPointArn = self::getEndpointArn($type,$data['registrationID'],$platformApplicationArn);

            $response = false;
            if(!empty($endPointArn)){
                $endPointArn2 = ["EndpointArn" => $endPointArn];
                $sns = App::make('aws')->createClient('sns');
                $endpointAtt = $sns->getEndpointAttributes($endPointArn2);
                $response = $sns->publish([
                            'TargetArn' => $endPointArn,
                            'Message' => $message,
                            'MessageStructure' => 'json'
                        ]);
            }
            return $response;
        } catch (SnsException $e) {
            return $e->getMessage();
        }
    }
    private static function getEndpointArn($type,$deviceToken,$platformApplicationArn){
        $endpointArnTempFlag = 1;
        $endpointArnTemp = '';
        try {
            $sns = App::make('aws')->createClient('sns');
            $deviceModel = $sns->listEndpointsByPlatformApplication(array('PlatformApplicationArn' => $platformApplicationArn)); 
            foreach ($deviceModel['Endpoints'] as $key => $value) {
                if($deviceToken == $value['Attributes']['Token']){
                    $endpointArnTempFlag = 2;
                    if($value['Attributes']['Enabled'] == 'true')
                       $endpointArnTemp = $value['EndpointArn'];
                    break;
                }
              }  
        } catch (SnsException $e) {
            
        }
        if($endpointArnTempFlag === 1 && empty($endpointArnTemp)){
            try {
                $client = App::make('aws')->createClient('sns');
                $result = $client->createPlatformEndpoint(array(
                        'PlatformApplicationArn' => $platformApplicationArn,
                        'Token' => $deviceToken,
                    ));
                $endpointArnTemp = isset($result['EndpointArn']) ? $result['EndpointArn'] : '';
            } catch (SnsException $e) {
                // return $e->getMessage();
            }
        }
        return $endpointArnTemp;
    }

}

?>
