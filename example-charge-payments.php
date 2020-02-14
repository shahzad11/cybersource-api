<?php

	include('cs.class.php');

  try {
    $cs_request	=	new cyber_source();

    /******* Add credit card details *******/
    $cs_request->add_credit_card_details(
      array(
        "accountNumber" => 'Credit Card Number',
        "expirationMonth"=>'Credit Card Expiration Month',
        "expirationYear"=>'Credit Card Expireation Year',
        "cvv"=>'Credit Card Security Code'
      )
    );


    /******* Add billing address info *******/
    $cs_request->add_billing_info(
                    array(
                    "firstName" 	=>	'Billing first name',
                    "lastName"		=>	'Billing last name',
                    "street1"		=>	'Billing Street address',
                    "city" 			=>	'Billing city',
                    "state"			=>	'Billing state',
                    "postalCode"	=>	'Billing ZipCode/postalCode',
                    "country" 		=>	'Billing country',
                    "email"			=>	'Billing email address',
                    "ipAddress"		=>	$_SERVER['REMOTE_ADDR']/* pulling customer's IP and sending to CS*/
                    )
                  );


    /******* Add product price and quantity *******/
    $cs_request->add_item(
                    array(
                    "unitPrice" 	=>	'9.80',
                    "quantity"		=>	1,
                    "id"			=>	'order_id' /* a unique order ID which you want to send and get back from CS for tracking*/
                    )
                  );
    /*******  now charge payment using CS Merchant Account *******/
    $reply = $cs_request->runTransaction();

    print_r($reply);

  } catch (SoapFault $exception) {
    print_r($reply);
    print_r($exception);
  }


?>
