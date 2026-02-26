<?php
namespace App\Utils\Custom;

use \DaphasBulkSmS;
use \GuruTechBulkSmS;

/**
 * Build to be utilized by main Util Class.
 * 
 * why build? To avoid other(though known!) third party updates to tamper with the side self upgrades
 * 
 * @author Giceha - https://www.github.com/Gicehajunior
 * @version v1.0
 */
class CustomUtil {

    public function sendSmsViaUjumbeDaphasSmS($data)
    {
        $sms_settings = $data['sms_settings'];

        if (empty($sms_settings['daphas_sms_sender_id']) || 
            empty($sms_settings['daphas_sms_api_key']) ||  
            empty($sms_settings['daphas_sms_email'])) {
            return false;
        }

        $daphasSmS = new DaphasBulkSmS(
            $sms_settings['daphas_sms_sender_id'], 
            $sms_settings['daphas_sms_api_key'], 
            $sms_settings['daphas_sms_email']
        );
        
        $numbers = explode(',', trim($data['mobile_number']));
        $response = $daphasSmS->send($data['sms_body'], $numbers);

        return $response;
    }

    public function sendSmsViaDaphasGuruTechSmS($data)
    {
        $sms_settings = $data['sms_settings'];

        if (empty($sms_settings['gurutech_sms_sender_id']) || 
            empty($sms_settings['gurutech_sms_api_key']) || 
            empty($sms_settings['gurutech_sms_userid']) ||
            empty($sms_settings['gurutech_sms_password'])) {
            return false;
        }

        $daphasSmS = new GuruTechBulkSmS(
            strtoupper($sms_settings['gurutech_sms_sender_id']), 
            strtoupper($sms_settings['gurutech_sms_userid']), 
            $sms_settings['gurutech_sms_password']
        );

        $numbers = explode(',', trim($data['mobile_number']));
        foreach ($numbers as $number) {
            $daphasSmS->send($data['sms_body'], [$number]);
        }

        return true;
    }

}



