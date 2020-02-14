# Cybersource Rest API Wrapper Class
If you want to integrate Cybersource payment gateway in your web applications, we have developed a really simple **Cybersource Rest API Wrapper Class in PHP** which you can use conveniently and start charging credit cards using Cybersource Payment Gateway.

## Cybersource Payment Gateway Integration
Integrating Cybersource is neverthless easier for you. You may want to intgrate simple order API. There are two types of Cybersource Rest API endpoints which you can use for testing or in production. Here are the Cybersouce Rest API endpoints.

[Download Cybersource API](https://github.com/shahzad11/cybersource-api/archive/master.zip)

**1. Rest API Endpoint for Testing**
`https://ics2wstest.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_1.26.wsdl`

**2. Rest API Endpoint for Production**
`https://ics2wsa.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_1.26.wsdl`

## Cybersource Test Account
If you want to use this API library, you should have a Cybersource test merchant account in hand. It will allow you to generate the following essential credetials.
`MERCHANT_ID` and `MERCHANT_ID` [Signup for Cybersource Test Account](https://developer.cybersource.com/hello-world/sandbox.html).

## Cybersource Simple Order Api
Here is how you can use this Cybersource Simple Order API in your PHP web applications.

First of all, you may want to include the cybersource library like the following.
`include('cs.class.php');`
Now initialize Cybersource object.
`$cs_request	=	new cyber_source();`

Add credit card number you want to charge.
```sh
$cs_request->add_credit_card_details(
  array(
    "accountNumber" 	=> 'Credit Card Number',
    "expirationMonth"	=>'Credit Card Expiration Month',
    "expirationYear"	=>'Credit Card Expiration Year',
    "cvv" 		=>'Credit Card Security Code'
  )
);
```

Now add the billing address of the customer.

```sh

$cs_request->add_billing_info(
   array(
   "firstName" 		=>	'Billing first name',
   "lastName"		=>	'Billing last name',
   "street1"		=>	'Billing Street address',
   "city" 		=>	'Billing city',
   "state"		=>	'Billing state',
   "postalCode"		=>	'Billing ZipCode/postalCode',
   "country" 		=>	'Billing country',
   "email"		=>	'Billing email address',
   "ipAddress"		=>	$_SERVER['REMOTE_ADDR']/* pulling customer's IP and sending to CS*/
   )
 );
```

Its time to add product(s) data like the following.

```sh

$cs_request->add_item(
  array(
  "unitPrice" 	=>	'9.80',
  "quantity"	=>	1,
  "id"		=>	'order_id' /* a unique order ID which you want to send and get back from CS for tracking*/
  )
);

```

Run transansaction and you are done.

```sh

$reply = $cs_request->runTransaction();

print_r($reply);

```

The `$reply` string holds Cybersource Rest API response, If you receive `$reply['reasonCode']==100` The payment is charged, you can now use it in case you want to mark your payment completed in your database.


## Cybersource Test Credit Card Numbers
| Credit Card Type | Credit Card Number |  Credit Card Number | CVV |
| ------ | ------ | ------ | ------ |
| VISA | 4111 1111 11111 1111 | 10/2026 | 999 |
| MasterCard | 2222 4200 0000 1113 | 10/2026 | 999 |
|  | 2222 6300 0000 1125 | 10/2026 | 999 |
|  | 5555 5555 5555 4444 | 10/2026 | 999 |
| American Express  | 3782 8224 6310 005 | 10/2026 | 999 |
| Discover  | 6011 1111 1111 1117 | 10/2026 | 999 |
| JCB | 3566 1111 1111 1113 | 10/2026 | 999 |

## Cybersource Error Codes

## Checkout My Other Libraries on Github

Here are few more PHP libraries programmed by me, have a look if you need it.

| Site Name | URL |
| ------ | ------ |
| Authorize.net API (SIM) Wrapper Class | https://github.com/shahzad11/Authorize.net-SIM-Wrapper |
| PHP Gender Predictor | https://github.com/shahzad11/php-gender-predictor-class |
| MySql CRUD | https://github.com/shahzad11/Mysql-CRUD |
