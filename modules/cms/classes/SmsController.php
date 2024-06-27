<?php namespace Cms\Classes;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Cms\Models\SmsLog;

class SmsController extends Controller
{
    public function receive_xml(Request $request)
    {
        $xml = $request->getContent();

        $log = new SmsLog();
        $log->content = $xml;
        $log->type = 0;
        $log->save();

        $xml = simplexml_load_string($xml);
//        Storage::put('xml.txt', json_encode($xml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));

        if (!$xml->soapenvBody->messageReceiver->Message) {
            $code = -1;
        } else {
            $code = 1;
        }

        if (@$code!=-1) {
            $sms = explode(' ', $xml->soapenvBody->messageReceiver->Message);

    //        if (!is_array($sms) || @$sms[1]=='' || (@$sms[0]!='dadong' && @$sms[0]!='sotien')) {
    //            $code = -1;
    //        }

            if (!isset($sms[1])) {
                $code2 = false;
            } else {
                switch (strtoupper($sms[0])) {
                    case 'DADONG':
                        $api = 'https://api.toyotafinancial.com.vn:8083/api/GetPayAMTByPlateNo/' . $sms[1];
                        $code2 = 'DADONG';
                        break;
                    case 'SOTIEN':
                        $api = 'https://api.toyotafinancial.com.vn:8083/api/GetAmountDueByPlateNo/' . $sms[1];
                        $code2 = 'SOTIEN';
                        break;
                    default:
                        $code2 = false;
                        break;
                }

                if ($code2!==false) {
                    $client = new Client();
                    $response = $client->post($api, [
                        'headers' => [
                            'SMSAPIKEY'     => 'KydnrwKgTUggtfAuZCeLXrbXspQmQnpXgTLbpTFEEzqHUFsvXAGcPTW'
                        ],
                        'verify' => false
                    ]);

                    $body = $response->getBody()->getContents();
                    $log = new SmsLog();
                    $log->content = $body;
                    $log->type = 1;
                    $log->save();
                    $body = substr($body, 1, -1);
                    $body = json_decode($body);

                    switch ($body->MONEYAMOUNT) {
                        case 0:
                            switch ($code2) {
                                case 'DADONG':
                                    $message = '
TFSVN chua nhan duoc khoan thanh toan nao. Vui long gui lai SMS sau 1 ngay dong tien hoac lien he 02873090998. Cam on Quy khach!
';
                                    break;
                                case 'SOTIEN':
                                    $message = '
Quy khach khong no qua han. Cam on Quy khach da su dung dich vu cua TFSVN. Moi thac mac xin lien he 02873090998. Tran Trong
';
                                    break;
                            }
                            break;
                        default:
                            switch ($code2) {
                                case 'DADONG':
                                    $message = '
TFSVN da nhan '.$body->MONEYAMOUNT.'d Quy khach dong cho hop dong vay xe '.$body->PLATENO.' vao ngay '.$body->PAYDT.'. Cam on quy khach
';
                                    break;
                                case 'SOTIEN':
                                    $message = '
Quy khach khong no qua han. Cam on Quy khach da su dung dich vu cua TFSVN. Moi thac mac xin lien he 02873090998. Tran Trong
';
                                    break;
                            }
                            break;

                    }
                }

            }

            if ($code2 === false) {
                $message = '
Quy khach vui long soan theo cu phap: - Truy van so tien qua han : SOTIEN BKS_kiem tra so tien da dong thang nay: DADONG BSK
';
            }
        }

        if (@$body->NOTE=='PARAMETER ERROR') {
            $messageType = 0;
        } elseif ($code2 == false) {
            $messageType = 2;
        } else {
            $messageType = 1;
        }

        $result = $this->send_sms($xml->soapenvBody->messageReceiver, $message, $messageType);
        $xml = simplexml_load_string($result, NULL, NULL, "http://schemas.xmlsoap.org/soap/envelope/");
        $xml->registerXPathNamespace('soap-env', 'http://schemas.xmlsoap.org/soap/envelope/');
        $nodes = $xml->xpath('/soapenv:Envelope/soapenv:Body/multiRef');
        $ack = (string) $nodes[0];

        $xml = '
<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/">
    <SOAP-ENV:Body>
        <ns1:messageReceiverResponse xmlns:ns1="urn:MOReceiver">
            <return xsi:type="xsd:string">'.$ack.'</return>
        </ns1:messageReceiverResponse>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
';
        return $xml;
    }


//    đúng cp = 1 (sai bs 0), 2
//160 = 1mt
//1
//1mt=0, 1
//0

    public function send_sms($body, $message, $messageType) {
        $totalMessage = ceil(strlen($message)/160);
        $isMore = ($totalMessage==1) ? 0 : 1;

        $xml = '
<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mt="http://mt.webservice.ems.vmg.com">
    <soapenv:Header/>
    <soapenv:Body>
        <mt:sendMT soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
            <userID xsi:type="soapenc:string" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">'.$body->User_ID.'</userID>
            <message xsi:type="soapenc:string" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">'.$message.'</message>
            <serviceID xsi:type="soapenc:string" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">'.$body->Service_ID.'</serviceID>
            <commandCode xsi:type="soapenc:string" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">'.$body->Command_Code.'</commandCode>
            <messageType xsi:type="soapenc:string" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">'.$messageType.'</messageType>
            <requestID xsi:type="soapenc:string" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">'.$body->Request_ID.'</requestID>
            <totalMessage xsi:type="soapenc:string" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">'.$totalMessage.'</totalMessage>
            <messageIndex xsi:type="soapenc:string" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">1</messageIndex>
            <isMore xsi:type="soapenc:string" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">'.$isMore.'</isMore>
            <contentType xsi:type="soapenc:string" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">0</contentType>
        </mt:sendMT>
    </soapenv:Body>
</soapenv:Envelope>
';

        $client = new Client();
        $response = $client->request('POST', 'http://sendmt.vmgmedia.vn/api/services/sendMT', [
            'headers' => [
//                'SMSAPIKEY'     => 'KydnrwKgTUggtfAuZCeLXrbXspQmQnpXgTLbpTFEEzqHUFsvXAGcPTW'
                'soapaction' => ''
            ],
            'verify' => false,
            'body' => $xml
        ]);

        $body = $response->getBody()->getContents();
        $log = new SmsLog();
        $log->content = $body;
        $log->type = 2;
        $log->save();

        return $body;

//        $body = substr($body, 1, -1);
//        $body = json_decode($body);
    }
}
