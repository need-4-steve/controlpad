<?php namespace App\Repositories\Eloquent;

use Auth;
use Config;
use Input;
use Log;
use Response;
use Session;
use Validator;
use App\Models\SalesTax;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;

class SalesTaxRepository
{
    use CommonCrudTrait;

    /**
     * SalesTaxRepository constructor.
     */
    public function __construct()
    {
        // any logic/data that may need to be available on every method
    }

    /**
    *  Get sales tax
    */
    public function getSalesTax($billing_zip, $user_id, $total_price_cents, $invoiceid, $phone)
    {

        /* SureTax credentials */
        $ClientNumber = Config::get('suretax.ClientNumber');
        $ValidationKey = Config::get('suretax.ValidationKey');
        $suretax_url = Config::get('suretax.base_url');

        $Zipcode = (string) $billing_zip;
        // we use Total Price in cents so that we have no decimal point issues
        $TotalRevenueCents = (int) $total_price_cents;
        $TotalRevenue = ($TotalRevenueCents / 100);
        $TotalRevenue = number_format($TotalRevenue, 2);
        $InvoiceNumber = (string) $invoiceid;
        $CustomerNumber = (string) $user_id;
        $Phone = (string) $phone;
        $DataYear = date('Y');
        $DataMonth = date('m');
        $ReturnFileCode = '0';
        $ClientTracking = 'track';
        $IndustryExemption = "";
        $ResponseType = 'D';
        $ResponseGroup = '03';
        $TransDate = date('m-d-Y');
        $Seconds = date('s');
        $BusinessUnit = 'BusinessUnit';

        $new_header = array(
            'DataMonth' => $DataMonth,
            'ResponseType' => 'D',
            'ResponseGroup' => '03',
            'ValidationKey' => $ValidationKey,
        );


        $tax_exemption_code_list = array(
            'TaxExemptionCodeList' => array('00', '00')
        );

        $item_list = array(
            'LineNumber' => '1',
            'Plus4' => '0000',
            'UnitType' => '00',
            'Seconds' => $Seconds,
            'SalesTypeCode' => 'R',
            'TaxExemptionCodeList' => array('00', '00'),
            'BillToNumber' => $Phone,
            'TransTypeCode' => '990101',
            'OrigNumber' => $Phone,
            'P2PZipcode' => '',
            'P2PPlus4' => '0000',
            'RegulatoryCode' => '99',
            'InvoiceNumber' => $InvoiceNumber,
            'Zipcode' => $Zipcode,
            'Units' => 1,
            'CustomerNumber' => $CustomerNumber,
            'TermNumber' => $Phone,
            'TransDate' => $TransDate,
            'TaxIncludedCode' => '0',
            'TaxSitusRule' => '04',
            'Revenue' => $TotalRevenue,
        );

        $order_info = array(
            'IndustryExemption' => '',
            'DataYear' => $DataYear,
            'ReturnFileCode' => '0',
            'TotalRevenue' => $TotalRevenue,
            'ClientTracking' => 'track',
            'ClientNumber' => $ClientNumber,
            'BusinessUnit' => ''
        );

        // JSON request
        $json = json_encode($new_header);

        $json = substr($json, 0, strlen($json)-1);
        $json .= ',"ItemList": [';
        $json .= json_encode($item_list);
        $json .= '],';

        $json_order_info = json_encode($order_info);
        $json_order_info = str_replace('{', '', $json_order_info);
        $json .= $json_order_info;

        $my_data = $json;

        //Initiate cURL request
        $ch = curl_init();
        // Set Headers
        curl_setopt($ch, CURLOPT_POSTFIELDS, "request=$my_data");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL, $suretax_url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout option
        $response = curl_exec($ch);
        curl_close($ch);

        // response may be wrapped in XML string tags
        $response = str_replace("\r\n", '', $response);
        $response = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $response);
        $response = str_replace('<string xmlns="http://tempuri.org/">', '', $response);
        $response = str_replace('</string>', '', $response);

        $objResponse = json_decode($response);

        $ResponseCode = isset($objResponse->ResponseCode) ? $objResponse->ResponseCode : null;
        $TotalTax = isset($objResponse->TotalTax) ? $objResponse->TotalTax : null;
        $Successful = isset($objResponse->Successful) ? $objResponse->Successful : null;
        $HeaderMessage = isset($objResponse->HeaderMessage) ? $objResponse->Successful : null;

        $GroupList = isset($objResponse->GroupList) ? $objResponse->GroupList : null;
        $StateCode = isset($GroupList[0]->StateCode) ? $GroupList[0]->StateCode : null;
        $TaxList = isset($GroupList[0]->TaxList) ? $GroupList[0]->TaxList : null;

        /* save the tax list */
        if (isset($TaxList)) {
            foreach ($TaxList as $tax) {
                SalesTax::create([
                    'invoiceid' => $invoiceid,
                    'taxtypecode' => $tax->TaxTypeCode,
                    'taxtypedesc' => $tax->TaxTypeDesc,
                    'taxamount' => $tax->TaxAmount,
                    'statecode' => $StateCode
                ]);
            }
        }

        $response_array = array(
            'total_tax' => $TotalTax,
        );

        $response_json = json_encode($response_array);

        if ($TotalTax && $ResponseCode == '9999') {
            return $response_json;
        } else {
            return "Response Code: $ResponseCode, Successful: $Successful, HeaderMessage: $HeaderMessage";
        }
    }// getSalesTax

    /**
    *  Get cart sales tax
    */
    public function getCartSalesTax($billing_zip, $user_id, $total_price_cents, $invoiceid, $phone)
    {

        /* SureTax credentials */
        $ClientNumber = Config::get('suretax.ClientNumber');
        $ValidationKey = Config::get('suretax.ValidationKey');
        $suretax_url = Config::get('suretax.base_url');

        $Zipcode = (string) $billing_zip;

        // we use Total Price in cents so that we have no decimal point issues
        $TotalRevenueCents = (int) $total_price_cents;
        $TotalRevenue = ($TotalRevenueCents / 100);
        $TotalRevenue = number_format($TotalRevenue, 2);
        $InvoiceNumber = (string) $invoiceid;
        $CustomerNumber = (string) $user_id;
        $Phone = (string) $phone;
        $DataYear = date('Y');
        $DataMonth = date('m');
        $ReturnFileCode = '0';
        $ClientTracking = 'track';
        $IndustryExemption = "";
        $ResponseType = 'D';
        $ResponseGroup = '03';
        $TransDate = date('m-d-Y');
        $Seconds = date('s');
        $BusinessUnit = 'BusinessUnit';

        $new_header = array(
            'DataMonth' => $DataMonth,
            'ResponseType' => 'D',
            'ResponseGroup' => '03',
            'ValidationKey' => $ValidationKey,
        );


        $tax_exemption_code_list = array(
            'TaxExemptionCodeList' => array('00', '00')
        );

        $item_list = array(
            'LineNumber' => '1',
            'Plus4' => '0000',
            'UnitType' => '00',
            'Seconds' => $Seconds,
            'SalesTypeCode' => 'R',
            'TaxExemptionCodeList' => array('00', '00'),
            'BillToNumber' => $Phone,
            'TransTypeCode' => '990101',
            'OrigNumber' => $Phone,
            'P2PZipcode' => '',
            'P2PPlus4' => '0000',
            'RegulatoryCode' => '99',
            'InvoiceNumber' => $InvoiceNumber,
            'Zipcode' => $Zipcode,
            'Units' => 1,
            'CustomerNumber' => $CustomerNumber,
            'TermNumber' => $Phone,
            'TransDate' => $TransDate,
            'TaxIncludedCode' => '0',
            'TaxSitusRule' => '04',
            'Revenue' => $TotalRevenue,
        );

        $order_info = array(
            'IndustryExemption' => '',
            'DataYear' => $DataYear,
            'ReturnFileCode' => '0',
            'TotalRevenue' => $TotalRevenue,
            'ClientTracking' => 'track',
            'ClientNumber' => $ClientNumber,
            'BusinessUnit' => ''
        );

        // JSON request
        $json = json_encode($new_header);

        $json = substr($json, 0, strlen($json)-1);
        $json .= ',"ItemList": [';
        $json .= json_encode($item_list);
        $json .= '],';

        $json_order_info = json_encode($order_info);
            $json_order_info = str_replace('{', '', $json_order_info);
        $json .= $json_order_info ;

        $my_data = $json;

        //Initiate cURL request
        $ch = curl_init();
        // Set Headers
        curl_setopt($ch, CURLOPT_POSTFIELDS, "request=$my_data");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL, $suretax_url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout option
        $response = curl_exec($ch);
        curl_close($ch);
        // Log::info('SureTax RAW Response:'.print_r($response,true));
        // Note: No HTTP header should be provided
        // (no need to supply the cURL CURLOPT_HTTPHEADER option.

        // response may be wrapped in XML string tags
        $response = str_replace("\r\n", '', $response);
        $response = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $response);
        $response = str_replace('<string xmlns="http://tempuri.org/">', '', $response);
        $response = str_replace('</string>', '', $response);

        $objResponse = json_decode($response);

        $ResponseCode = $objResponse->ResponseCode;
        $TotalTax = $objResponse->TotalTax;
        $Successful = $objResponse->Successful;
        $HeaderMessage = $objResponse->HeaderMessage;

        $GroupList = $objResponse->GroupList;
        $StateCode = isset($GroupList[0]->StateCode) ? $GroupList[0]->StateCode : null;
        $TaxList = isset($GroupList[0]->TaxList) ? $GroupList[0]->TaxList : null;

        /* save the tax list */
        if (isset($TaxList)) {
            foreach ($TaxList as $tax) {
                SalesTax::create([
                    'invoiceid' => $invoiceid,
                    'taxtypecode' => $tax->TaxTypeCode,
                    'taxtypedesc' => $tax->TaxTypeDesc,
                    'taxamount' => $tax->TaxAmount,
                    'statecode' => $StateCode
                ]);
            }
        }

        $response_array = array(
        'total_tax' => $TotalTax,
        );

        $response_json = json_encode($response_array);

        if ($TotalTax && $ResponseCode == '9999') {
            return $response_json;
        } else {
            return "Response Code: $ResponseCode, Successful: $Successful, HeaderMessage: $HeaderMessage";
        }
    }
}
