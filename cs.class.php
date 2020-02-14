<?php


	class cyber_source extends SoapClient {

    /*******
    *
    * For Test API */

		/*const MERCHANT_ID		=	'';
    const MERCHANT_ID		=	'Your Merchant ID';
    const TRANSACTION_KEY	=	'Your CyberSourceTransaction Key';
		const WSDL_URL			=	'https://ics2wstest.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_1.26.wsdl';*/

		/*******
		*
		* For Production API */
		const MERCHANT_ID		=	'Your Merchant ID';
		const TRANSACTION_KEY	=	'Your CyberSourceTransaction Key';
		const WSDL_URL			=	'https://ics2wsa.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_1.26.wsdl';


		var $document_version	=	'1.0';
		var $request;


		function __construct($options = array()) {
			$this->request		=	new stdClass();
			$this->request->merchantID = self::MERCHANT_ID;
			$this->request->merchantReferenceCode = "your_merchant_reference_code";
			$this->request->clientLibrary = "PHP";
			$this->request->clientLibraryVersion = phpversion();
			$this->request->clientEnvironment = php_uname();
			$this->request->ccAuthService->run	=	"true";
			$this->request->ccCaptureService->run	=	"true";
			$this->request->purchaseTotals->currency		=	'USD';
		 	parent::__construct(self::WSDL_URL, $options);/**/
		}


		function __doRequest($request, $location, $action, $version) {

			 $user = self::MERCHANT_ID;
			 $password = self::TRANSACTION_KEY;
			 $soapHeader = "<SOAP-ENV:Header xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"><wsse:Security SOAP-ENV:mustUnderstand=\"1\"><wsse:UsernameToken><wsse:Username>$user</wsse:Username><wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">$password</wsse:Password></wsse:UsernameToken></wsse:Security></SOAP-ENV:Header>";
			 $requestDOM = new DOMDocument($this->document_version);
			 $soapHeaderDOM = new DOMDocument($this->document_version);
			 try {
				$requestDOM->loadXML($request);
				$soapHeaderDOM->loadXML($soapHeader);
				$node = $requestDOM->importNode($soapHeaderDOM->firstChild, true);
				$requestDOM->firstChild->insertBefore(
				$node, $requestDOM->firstChild->firstChild);
				$request = $requestDOM->saveXML();

			 // printf( "Modified Request:\n*$request*\n" );

			 } catch (DOMException $e) {
				 die( 'Error adding UsernameToken: ' . $e->code);
			 }

			 return parent::__doRequest($request, $location, $action, $version);
		   }

		function	add_credit_card_details($cc_data){
			$this->request->card		=	(object) $cc_data;
		}

		function	add_billing_info($billing_info){
			$this->request->billTo		=	(object) $billing_info;
		}

		function	add_item($item_info){
			$this->request->item[]		=	(object) $item_info;
		}

		function 	runTransaction(){
			return parent::runTransaction($this->request);
		}

	}



?>
