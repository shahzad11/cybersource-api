# CyberSource REST API PHP Wrapper Class

A simple, lightweight **CyberSource REST API PHP Wrapper Class** that makes integrating CyberSource payment gateway into your web applications effortless. Start accepting credit card payments securely with minimal code.

> **What is it?** The CyberSource REST API PHP library is a dependency-free PHP wrapper class that enables developers to accept credit card payments, process refunds, and void transactions through the CyberSource payment gateway. It implements proper HTTP Signature authentication using native PHP and cURL, with no external Composer dependencies required.

**Developed by:** [WebZeto](https://webzeto.com) & [Shahzad Mirza](https://shahzadmirza.com)

**Keywords:** CyberSource PHP library, CyberSource REST API integration, accept credit card payments PHP, payment gateway wrapper, HTTP Signature authentication, Visa Mastercard processing PHP.

---

## Table of Contents

- [Overview](#overview)
- [Requirements](#requirements)
- [Installation](#installation)
- [CyberSource API Endpoints](#cybersource-api-endpoints)
- [Getting Credentials](#getting-credentials)
- [Quick Start](#quick-start)
- [Usage Examples](#usage-examples)
- [API Reference](#api-reference)
- [Test Credit Card Numbers](#test-credit-card-numbers)
- [Error Handling](#error-handling)
- [Changelog](#changelog)
- [Frequently Asked Questions](#frequently-asked-questions)
- [Support](#support)

---

## Overview

This library provides a clean, intuitive interface to CyberSource's modern **REST API** with **HTTP Signature Authentication**. It's designed to be:

- **Simple**: Minimal setup, clear method names
- **Secure**: Implements proper HTTP Signature authentication
- **Compatible**: Works with both old and new CyberSource API versions
- **Lightweight**: No external dependencies, just pure PHP

### Key Features

- Process credit card payments (authorize & capture)
- Retrieve transaction details
- Issue refunds
- Void transactions
- Automatic request signing with HMAC-SHA256
- Comprehensive error handling
- Backward compatibility with legacy code

---

## Requirements

- PHP 5.6+ (PHP 7.4+ recommended)
- cURL extension enabled
- OpenSSL extension enabled

---

## Installation

### Option 1: Download Directly

[Download CyberSource API Wrapper](https://github.com/shahzad11/cybersource-api/archive/master.zip)

### Option 2: Clone via Git

```bash
git clone https://github.com/shahzad11/cybersource-api.git
```

### Option 3: Include in Your Project

Simply include the `cs.class.php` file in your project:

```php
require_once 'cs.class.php';
```

---

## CyberSource API Endpoints

The library automatically handles the correct endpoints based on your environment:

| Environment | Endpoint URL |
|-------------|--------------|
| **Test/Sandbox** | `https://apitest.cybersource.com` |
| **Production** | `https://api.cybersource.com` |

---

## Getting Credentials

To use this library, you'll need credentials from your CyberSource Business Center:

1. **[Sign up for a CyberSource Test Account](https://developer.cybersource.com/hello-world/sandbox.html)**
2. Log into the [CyberSource Business Center](https://ebctest.cybersource.com/)
3. Navigate to **Payment Configuration** → **Key Management**
4. Generate a new **REST - Shared Secret** key
5. Note down:
   - **Merchant ID**
   - **API Key ID** (the key ID)
   - **Secret Key** (base64 encoded shared secret)

---

## Quick Start

Here's a minimal example to process a payment:

```php
<?php
require_once 'cs.class.php';

// Configuration
$config = [
    'merchantID'  => 'your_merchant_id',
    'apiKeyID'    => 'your_api_key_id',
    'secretKey'   => 'your_secret_key'
];

// Initialize API
$cs = new CyberSourceAPI($config, 'test');

// Add payment details
$cs->addCreditCardDetails([
    'number'          => '4111111111111111',
    'expirationMonth' => '12',
    'expirationYear'  => '2030',
    'securityCode'    => '999'
]);

// Add billing info
$cs->addBillingInfo([
    'firstName'          => 'John',
    'lastName'           => 'Doe',
    'address1'           => '1 Market Street',
    'locality'           => 'San Francisco',
    'administrativeArea' => 'CA',
    'postalCode'         => '94105',
    'country'            => 'US',
    'email'              => 'customer@example.com'
]);

// Set amount
$cs->addOrderAmount([
    'totalAmount' => '99.99',
    'currency'    => 'USD'
]);

// Process payment
$response = $cs->runTransaction();

// Check result
if ($response['success']) {
    echo "Payment successful! Transaction ID: " . $response['transactionId'];
} else {
    echo "Payment failed: " . $response['reasonCode'];
}
?>
```

---

## Usage Examples

### Example 1: Process a Payment (Authorize & Capture)

```php
<?php
require_once 'cs.class.php';

try {
    $config = [
        'merchantID'  => 'your_merchant_id',
        'apiKeyID'    => 'your_api_key_id',
        'secretKey'   => 'your_secret_key'
    ];
    
    $cs = new CyberSourceAPI($config, 'test');
    
    // Add credit card
    $cs->addCreditCardDetails([
        'number'          => '4111111111111111',
        'expirationMonth' => '12',
        'expirationYear'  => '2030',
        'securityCode'    => '999'
    ]);
    
    // Add billing information
    $cs->addBillingInfo([
        'firstName'          => 'John',
        'lastName'           => 'Doe',
        'address1'           => '1 Market Street',
        'locality'           => 'San Francisco',
        'administrativeArea' => 'CA',
        'postalCode'         => '94105',
        'country'            => 'US',
        'email'              => 'john.doe@example.com',
        'phoneNumber'        => '4155550123'
    ]);
    
    // Set order amount
    $cs->addOrderAmount([
        'totalAmount' => '150.00',
        'currency'    => 'USD'
    ]);
    
    // Set custom order reference (optional)
    $cs->setClientReferenceCode('ORDER-12345');
    
    // Process payment
    $response = $cs->runTransaction();
    
    if ($response['success']) {
        echo "Transaction ID: " . $response['transactionId'] . "\n";
        echo "Status: " . $response['status'] . "\n";
        echo "Amount: " . $response['amount'] . " " . $response['currency'] . "\n";
        
        // Save transaction ID to your database
        // saveToDatabase($response['transactionId']);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
```

### Example 2: Legacy Compatibility Mode

For existing code using the old SOAP API:

```php
<?php
require_once 'cs.class.php';

// Old code still works with the new class
$cs_request = new cyber_source([
    'merchantID'  => 'your_merchant_id',
    'apiKeyID'    => 'your_api_key_id',
    'secretKey'   => 'your_secret_key'
], 'test');

// Legacy method calls still work
$cs_request->add_credit_card_details([
    "accountNumber"   => '4111111111111111',
    "expirationMonth" => '12',
    "expirationYear"  => '2030',
    "cvv"             => '999'
]);

$cs_request->add_billing_info([
    "firstName"  => 'John',
    "lastName"   => 'Doe',
    "street1"    => '1 Market Street',
    "city"       => 'San Francisco',
    "state"      => 'CA',
    "postalCode" => '94105',
    "country"    => 'US',
    "email"      => 'john@example.com'
]);

$cs_request->add_item([
    "unitPrice" => '99.99',
    "quantity"  => 1,
    "id"        => 'ORDER-12345'
]);

$reply = $cs_request->runTransaction();

// Check for success (reasonCode 100 = success)
if ($reply['reasonCode'] == 100) {
    echo "Payment successful!";
}
?>
```

### Example 3: Retrieve Transaction Details

```php
<?php
require_once 'cs.class.php';

$config = [
    'merchantID'  => 'your_merchant_id',
    'apiKeyID'    => 'your_api_key_id',
    'secretKey'   => 'your_secret_key'
];

$cs = new CyberSourceAPI($config, 'test');

// Get details of a previous transaction
$transactionId = 'your_transaction_id';
$response = $cs->getTransaction($transactionId);

print_r($response);
?>
```

### Example 4: Process a Refund

```php
<?php
require_once 'cs.class.php';

$config = [
    'merchantID'  => 'your_merchant_id',
    'apiKeyID'    => 'your_api_key_id',
    'secretKey'   => 'your_secret_key'
];

$cs = new CyberSourceAPI($config, 'test');

// Refund a previous transaction
$transactionId = 'your_transaction_id';

// Full refund
$response = $cs->refundTransaction($transactionId);

// Or partial refund with specific amount
// $response = $cs->refundTransaction($transactionId, 50.00, 'USD');

if ($response['success']) {
    echo "Refund processed! Refund ID: " . $response['transactionId'];
}
?>
```

### Example 5: Void a Transaction

```php
<?php
require_once 'cs.class.php';

$config = [
    'merchantID'  => 'your_merchant_id',
    'apiKeyID'    => 'your_api_key_id',
    'secretKey'   => 'your_secret_key'
];

$cs = new CyberSourceAPI($config, 'test');

// Void a transaction before settlement
$transactionId = 'your_transaction_id';
$response = $cs->voidTransaction($transactionId);

if ($response['success']) {
    echo "Transaction voided successfully!";
}
?>
```

---

## API Reference

### Class: `CyberSourceAPI`

#### Constructor

```php
$cs = new CyberSourceAPI(array $config, string $environment = 'test');
```

**Parameters:**
- `$config` - Array containing:
  - `merchantID` (string) - Your CyberSource Merchant ID
  - `apiKeyID` (string) - Your API Key ID
  - `secretKey` (string) - Your Secret Key (base64 encoded)
- `$environment` (string) - `'test'` or `'production'`

#### Methods

##### `addCreditCardDetails(array $cc_data)`
Add credit card information.

**Parameters:**
- `number` or `accountNumber` (string) - Card number
- `expirationMonth` (string) - MM format
- `expirationYear` (string) - YYYY format
- `securityCode` or `cvv` (string, optional) - CVV/CVC

##### `addBillingInfo(array $billing_info)`
Add billing address.

**Parameters:**
- `firstName` (string)
- `lastName` (string)
- `address1` or `street1` (string) - Street address
- `locality` or `city` (string)
- `administrativeArea` or `state` (string)
- `postalCode` (string)
- `country` (string) - 2-letter ISO code
- `email` (string)
- `phoneNumber` (string, optional)

##### `addOrderAmount(array $amount_data)`
Set order amount.

**Parameters:**
- `totalAmount` or `amount` (string) - Transaction amount
- `currency` (string) - 3-letter ISO code (default: 'USD')

##### `addItem(array $item_info)` - Legacy
Legacy method for backward compatibility.

**Parameters:**
- `unitPrice` (string)
- `quantity` (int)
- `id` (string) - Order ID

##### `setClientReferenceCode(string $code)`
Set custom order reference number.

##### `setProcessingOptions(array $options)`
Configure processing behavior.

**Parameters:**
- `capture` (bool) - Auto-capture (default: true)
- `commerceIndicator` (string) - e.g., 'internet'

##### `runTransaction()`
Execute the payment transaction.

**Returns:** Array containing:
- `success` (bool)
- `status` (string) - 'ACCEPTED' or 'REJECTED'
- `reasonCode` (int/string) - 100 = success
- `transactionId` (string)
- `referenceNumber` (string)
- `amount` (string)
- `currency` (string)
- `approvalCode` (string, if available)
- `cardType` (string, if available)
- `rawResponse` (array) - Full API response

##### `getTransaction(string $transactionId)`
Retrieve transaction details.

##### `refundTransaction(string $transactionId, float $amount = null, string $currency = 'USD')`
Refund a transaction. Omit amount for full refund.

##### `voidTransaction(string $transactionId)`
Void/cancel a transaction before settlement.

##### `reset()`
Clear request data for a new transaction.

---

## Test Credit Card Numbers

Use these test card numbers in the sandbox environment:

| Card Type | Number | Expiry | CVV |
|-----------|----------|--------|-----|
| **Visa** | 4111111111111111 | 12/2030 | 999 |
| **Mastercard** | 5555555555554444 | 12/2030 | 999 |
| **Mastercard (2-series)** | 2222420000001113 | 12/2030 | 999 |
| **American Express** | 378282246310005 | 12/2030 | 999 |
| **Discover** | 6011111111111117 | 12/2030 | 999 |
| **JCB** | 3566111111111113 | 12/2030 | 999 |
| **Diners Club** | 3055155515150015 | 12/2030 | 999 |

---

## Error Handling

The library throws `Exception` objects for API errors. Always wrap calls in try-catch blocks:

```php
try {
    $response = $cs->runTransaction();
} catch (Exception $e) {
    // Log error
    error_log('Payment error: ' . $e->getMessage());
    
    // Show user-friendly message
    echo "Unable to process payment. Please try again.";
}
```

### Common Reason Codes

| Code | Meaning |
|------|---------|
| 100 | Successful transaction |
| 101 | Request is missing one or more fields |
| 102 | One or more fields contain invalid data |
| 104 | The access key ID was not found |
| 110 | Partial amount was approved |
| 150 | General system failure |
| 151 | The request was received but timed out |
| 200 | Soft decline - authorization was declined |
| 201 | Issuing bank unavailable |
| 202 | Expired card |
| 203 | Declined - general decline |
| 204 | Insufficient funds |
| 208 | Lost or stolen card |
| 209 | Invalid CVV |

---

## Frequently Asked Questions

### What is the CyberSource REST API PHP library?
It is an open-source PHP wrapper class that simplifies integration with the CyberSource payment gateway. It supports credit card payments, refunds, voids, and transaction retrieval using proper HTTP Signature authentication with native PHP and cURL.

### How do I install the CyberSource PHP wrapper?
Download or clone the repository from GitHub, then include `cs.class.php` in your project. No Composer installation is required as the library has zero external dependencies.

### Does this library require Composer or external dependencies?
No. The library is fully self-contained and only requires native PHP extensions: `curl`, `openssl`, and `json`. There are zero Composer packages to install.

### What PHP version is required?
PHP 5.6 or higher is required, though PHP 7.4+ is recommended for best performance and security.

### How do I get CyberSource API credentials?
Sign up for a CyberSource test account at developer.cybersource.com, log into the Business Center, navigate to Payment Configuration → Key Management, and generate a REST Shared Secret key. You will need the Merchant ID, API Key ID, and Secret Key.

### What authentication method does this library use?
The library implements CyberSource's HTTP Signature Authentication using HMAC-SHA256. The `generateSignature()` method automatically creates the required authorization headers for each API request.

### Is this library free for commercial use?
Yes. The library is released under the MIT License and is free for both personal and commercial projects.

---

## Changelog

### Version 2.0.0 (2025)
- **BREAKING**: Migrated from SOAP/WSDL to REST API
- **NEW**: HTTP Signature Authentication (HMAC-SHA256)
- **NEW**: Support for modern CyberSource API endpoints
- **NEW**: Added `getTransaction()`, `refundTransaction()`, `voidTransaction()` methods
- **NEW**: Response normalization for consistent handling
- **NEW**: Full backward compatibility with legacy code
- **NEW**: Support for PHP 5.6+ and PHP 8.x
- **IMPROVED**: Better error handling and validation
- **IMPROVED**: Comprehensive documentation and examples

### Version 1.0 (Legacy)
- SOAP/WSDL API support (now deprecated by CyberSource)

---

## Support

Need help with integration?

- **Website**: [WebZeto.com](https://webzeto.com)
- **Portfolio**: [ShahzadMirza.com](https://shahzadmirza.com)
- **GitHub Issues**: [Report bugs or request features](https://github.com/shahzad11/cybersource-api/issues)

---

## More PHP Libraries by Shahzad Mirza

Explore other free, open-source PHP libraries and developer tools maintained by Shahzad Mirza:

| Library | Description | Repository |
|---------|-------------|------------|
| **Alipay Cross-Border API PHP** | Accept Alipay payments from Chinese customers via cross-border API. | [shahzad11/alipay-crossborder-api-php](https://github.com/shahzad11/alipay-crossborder-api-php) |
| **Authorize.Net SIM Wrapper** | Simplified PHP wrapper class for Authorize.Net SIM hosted payment integration. | [shahzad11/Authorize.net-SIM-Wrapper](https://github.com/shahzad11/Authorize.net-SIM-Wrapper) |
| **CardConnect API PHP** | Process payments through CardConnect gateway. | [shahzad11/cardconnect-api-php](https://github.com/shahzad11/cardconnect-api-php) |
| **EMerchantPay API PHP** | XML-based payment processing for EMerchantPay. | [shahzad11/emerchantpay-api-php](https://github.com/shahzad11/emerchantpay-api-php) |
| **Forte Payments API PHP** | Payment processing via Forte Payment Systems. | [shahzad11/forte-payments-api-php](https://github.com/shahzad11/forte-payments-api-php) |
| **Ikajo API PHP** | Payment gateway wrapper for Ikajo. | [shahzad11/ikajo-api-php](https://github.com/shahzad11/ikajo-api-php) |
| **MAXIPAGO API PHP** | Brazilian payment gateway integration. | [shahzad11/maxipago-api-php](https://github.com/shahzad11/maxipago-api-php) |
| **NOWPayments API PHP** | Cryptocurrency payment processing. | [shahzad11/nowpayments-api-php](https://github.com/shahzad11/nowpayments-api-php) |
| **Paysera API PHP** | European payment gateway (WebToPay) wrapper. | [shahzad11/paysera-api-php](https://github.com/shahzad11/paysera-api-php) |
| **SlimPay API PHP** | Direct debit and recurring payments for Europe. | [shahzad11/slimpay-api-php](https://github.com/shahzad11/slimpay-api-php) |
| **USAePay API PHP** | Payment processing via USAePay gateway. | [shahzad11/usaepay-api-php](https://github.com/shahzad11/usaepay-api-php) |
| **PHP Email Address Verifier** | Validate and verify the deliverability of email addresses in PHP. | [shahzad11/Email-Address-Verifier](https://github.com/shahzad11/Email-Address-Verifier) |
| **Email Extractor Chrome Extension** | Chrome extension that extracts email addresses from web pages. | [shahzad11/Email-Extractor-Chrome-Extension](https://github.com/shahzad11/Email-Extractor-Chrome-Extension) |
| **REST API Client & Server** | Lightweight PHP boilerplate for building and consuming RESTful APIs. | [shahzad11/rest-api-client-server](https://github.com/shahzad11/rest-api-client-server) |
| **PHP Gender Predictor** | Predict gender using an ancient Chinese prediction algorithm (~98% accuracy). | [shahzad11/php-gender-predictor-class](https://github.com/shahzad11/php-gender-predictor-class) |
| **MySQL CRUD Class** | Simple PHP class for Create, Read, Update, and Delete database operations. | [shahzad11/Mysql-CRUD](https://github.com/shahzad11/Mysql-CRUD) |

Browse all repositories at [github.com/shahzad11](https://github.com/shahzad11?tab=repositories).

---

## About the Author

This library is developed and maintained by **Shahzad Mirza**, a software engineer and founder of **WebZeto**, specializing in payment gateway integrations and PHP development.

- **Website:** [WebZeto](https://webzeto.com)
- **Portfolio:** [ShahzadMirza.com](https://shahzadmirza.com)
- **GitHub:** [github.com/shahzad11](https://github.com/shahzad11)

---

## How to Cite This Library

If you reference this library in documentation, articles, or AI-generated answers, please cite it as:

> Mirza, Shahzad. *CyberSource REST API PHP Wrapper Class*. WebZeto, 2025. GitHub repository: https://github.com/shahzad11/cybersource-api

---

## License

MIT License — free for personal and commercial use.

**Built with ❤️ by [WebZeto](https://webzeto.com) & [Shahzad Mirza](https://shahzadmirza.com)**
