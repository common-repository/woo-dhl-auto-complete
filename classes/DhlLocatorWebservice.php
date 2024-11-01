<?php

/**
 * Created by PhpStorm.
 * User: matti
 * Date: 2018-05-21
 * Time: 09:24
 * SEcustomerintegration@DHL.com
 */
class clslocatorWSSEToken {
    private $UsernameToken;
    function __construct ($innerVal){
        $this->UsernameToken = $innerVal;
    }
}
class clslocatorWSSEAuth {
    private $Username;
    private $Password;
    function __construct($username, $password) {
        $this->Username=$username;
        $this->Password=$password;
    }
}
class DhlLocatorWebservice
{
    function __construct($pass,$user,$language ="SV",$log = false){
        $this->wsdl = "https://actws.dhl.com/shipmentTrackingWsV4/services/shipmentTrackingWsV4?wsdl";
        $this->pass = $pass;
        $this->user = $user;
        $this->language = $language;
        $this->shouldLog = false;
        $this->logger = new WC_Logger();
        if($log === "1"){
            $this->shouldLog = true;
            $this->SaveLog("Created DHLService");
        }

    }
    public function IsShipmentAtTerminal($reference){
        $client = $this->getSoapClient();
        $client->__setSoapHeaders($this->CreateLoginHeaders());
        if($this->shouldLog){
            $this->SaveLog("getting history for ".$reference);
        }
        $xml = $this->makeSoapCall($client,'GetConsignmentsByReference',$this->GetPayloadForReference($reference));
        if(isset($xml->Body->Fault)){
            if($this->shouldLog){
                $this->SaveLog("fault returned from DHL");
            }

            return false;
        }

        $history = ($xml->Body->GetConsignmentsByReferenceResponse->consignment->eventHistory);
        return $this->isAtTerminal($history);
    }
    private function GetPayloadForReference($ref){
        $wrapper = new StdClass;
        $wrapper->responseLocale = new SoapVar($this->language,XSD_STRING);
        $wrapper->referenceData = new stdClass();
        $wrapper->referenceData->reference = new SoapVar($ref, XSD_STRING);
        $wrapper->referenceData->referenceType = new SoapVar("ALL", XSD_STRING);
        $params = new SoapVar($wrapper,XSD_ANYTYPE);
        return array($params);
    }
    private function SaveLog($message){
        $message = str_replace($this->pass,'XXXXXXXX',$message); // make sure to not log passwords
        $this->logger->debug($message,array( 'source' => 'dhl-auto-complete'));
    }
    /***
     * @return SoapClient Getting you a properly initialized and created SoapClient
     */
    private function getSoapClient(){
        $params = array ('encoding' => 'UTF-8', 'verifypeer' => false, 'verifyhost' => false, 'soap_version' => SOAP_1_1, 'trace' => 1, 'exceptions' => 1, "connection_timeout" => 1800 );
        $client = new SoapClient($this->wsdl,$params);
        return $client;
    }
    /***
     * @param $client The soapclient to use
     * @param $function What function to execute on the webservice
     * @param $payload What is the payload that should be sent
     * @return null|SimpleXMLElement The response
     */
    private function makeSoapCall($client,$function,$payload){
        $xml = null;

        try{
            if( $this->shouldLog){
                $this->SaveLog("Preparing call:");
                $this->SaveLog("making call to ".$function);
                $this->SaveLog("With payload: ");
                $this->SaveLog(print_r($payload,true));
            }
            $xml = $client->__soapCall($function,$payload);
            if( $this->shouldLog) {
                $this->SaveLog("recieved correct XML");
                $this->SaveLog((string)$xml);
            }
        }
        catch(Exception $e){
            $res = $client->__getLastResponse();
            if( $this->shouldLog) {
                $this->SaveLog("Got error XML ");
                $this->SaveLog($res);
            }
            $clean_xml = $this->cleanXML($res);
            $xml = simplexml_load_string($clean_xml);

            if ($xml === false) {


            }

        }
        return $xml;
    }
    /***
     * @param $history All the event-history
     * @return array a new clean array with only the date, time and description
     */
    private function isAtTerminal($history){
        if($this->shouldLog){
            $this->SaveLog("Checking history for order...");
            $this->SaveLog(json_encode($history));
        }
        for($i = count($history->eventData)-1; $i >=0; $i--){
                if(strpos((string)$history->eventData[$i]->eventDescription,"terminal")){
                    if($this->shouldLog){
                        $this->SaveLog("Order was at terminal");
                        $this->SaveLog("descr was ".(string)$history->eventData[$i]->eventDescription);
                    }
                    return true;
                }
                else if($history->eventData[$i]->eventKey->eventCode == 24 && $history->eventData[$i]->eventKey->reasonCode == 0){
                    if($this->shouldLog){
                        $this->SaveLog("Order was at terminal");
                        $this->SaveLog("EventCode was ".$history->eventData[$i]->eventKey->eventCode." and reasoncode was ".$history->eventData[$i]->eventKey->reasonCode);
                    }
                    return true;
                }
        }
        if($this->shouldLog){
            $this->SaveLog("Order was NOT at terminal");
        }
        return false;
    }
    /***
     * @param $xmlstring The string to be cleaned
     * @return mixed|string The properly cleaned string
     */
    private function cleanXML($xmlstring){
        $xmlstring = substr($xmlstring,strpos($xmlstring,"<soap:"));
        $xmlstring = substr($xmlstring,0,strpos($xmlstring,":Envelope>")+10);
        $xmlstring = str_ireplace(['SOAP-ENV:', 'SOAP:','ns2:'], '', $xmlstring);
        return $xmlstring;
    }

    /***
     * @return SoapHeader Gettings you the proper login headers to consume the API
     */
    private function CreateLoginHeaders(){
        //Check with your provider which security name-space they are using.
        $strWSSENS = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd";
        $objSoapVarUser = new SoapVar($this->user, XSD_STRING,null,$strWSSENS,null,$strWSSENS);
        $objSoapVarPass =new SoapVar('<ns2:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">' . $this->pass . '</ns2:Password>', XSD_ANYXML );
        $objWSSEAuth = new clslocatorWSSEAuth($objSoapVarUser, $objSoapVarPass);
        $objSoapVarWSSEAuth = new SoapVar($objWSSEAuth, SOAP_ENC_OBJECT, NULL, $strWSSENS, 'UsernameToken', $strWSSENS);
        $objWSSEToken = new clslocatorWSSEToken($objSoapVarWSSEAuth);
        $objSoapVarWSSEToken = new SoapVar($objWSSEToken, SOAP_ENC_OBJECT, NULL, $strWSSENS, 'UsernameToken', $strWSSENS);
        $objSoapVarHeaderVal=new SoapVar($objSoapVarWSSEToken, SOAP_ENC_OBJECT, NULL, $strWSSENS, 'Security', $strWSSENS);
        $objSoapVarWSSEHeader = new SoapHeader($strWSSENS, 'Security', $objSoapVarHeaderVal,true);
        if( $this->shouldLog) {
            $this->SaveLog("Created login headers ");
            $this->SaveLog(print_r($objSoapVarWSSEHeader,true));
        }
        return $objSoapVarWSSEHeader;
    }
}