<?php
/**
 * CyberSource REST API - Example Payment Processing
 * 
 * This example demonstrates how to process a payment using the CyberSource REST API.
 * 
 * Developed by: WebZeto (webzeto.com) & Shahzad Mirza (shahzadmirza.com)
 */

require_once 'cs.class.php';

try {
    // Configuration - Get these from your CyberSource Business Center
    $config = [
        'merchantID'  => 'your_merchant_id',           // Your CyberSource Merchant ID
        'apiKeyID'    => 'your_api_key_id',            // Your API Key ID (from Business Center)
        'secretKey'   => 'your_secret_key'           // Your Secret Key (base64 encoded)
    ];
    
    // Initialize CyberSource API (use 'test' for sandbox, 'production' for live)
    $cs = new CyberSourceAPI($config, 'test');
    
    /******* Add credit card details *******/
    $cs->addCreditCardDetails([
        'number'          => '4111111111111111',       // Test Visa card number
        'expirationMonth' => '12',                     // MM format
        'expirationYear'  => '2030',                   // YYYY format
        'securityCode'    => '999'                     // CVV/CVC code
    ]);
    
    /******* Add billing address info *******/
    $cs->addBillingInfo([
        'firstName'          => 'John',
        'lastName'           => 'Doe',
        'address1'           => '1 Market Street',     // Can also use 'street1' for legacy compatibility
        'locality'           => 'San Francisco',        // Can also use 'city' for legacy compatibility
        'administrativeArea' => 'CA',                   // Can also use 'state' for legacy compatibility
        'postalCode'         => '94105',
        'country'            => 'US',                   // 2-letter ISO country code
        'email'              => 'customer@example.com',
        'phoneNumber'        => '4155550123'           // Optional
    ]);
    
    /******* Add order amount *******/
    // Option 1: Direct amount specification (recommended)
    $cs->addOrderAmount([
        'totalAmount' => '99.99',
        'currency'    => 'USD'
    ]);
    
    // Option 2: Legacy method using item (for backward compatibility)
    // $cs->addItem([
    //     'unitPrice' => '99.99',
    //     'quantity'  => 1,
    //     'id'        => 'ORDER-12345'
    // ]);
    
    /******* Set optional processing parameters *******/
    $cs->setProcessingOptions([
        'capture'           => true,   // Auto-capture (auth + charge)
        'commerceIndicator' => 'internet'
    ]);
    
    /******* Set custom client reference code (order ID) *******/
    $cs->setClientReferenceCode('ORDER-' . uniqid());
    
    /******* Execute the payment *******/
    $response = $cs->runTransaction();
    
    /******* Process the response *******/
    if ($response['success']) {
        echo "Payment Successful!\n";
        echo "Transaction ID: " . $response['transactionId'] . "\n";
        echo "Status: " . $response['status'] . "\n";
        echo "Reason Code: " . $response['reasonCode'] . "\n";
        echo "Amount: " . $response['amount'] . " " . $response['currency'] . "\n";
        
        if (isset($response['approvalCode'])) {
            echo "Approval Code: " . $response['approvalCode'] . "\n";
        }
        
        if (isset($response['cardType'])) {
            echo "Card Type: " . $response['cardType'] . "\n";
        }
        
        // Save transaction details to your database
        // $response['transactionId'] - Store this for future reference
        // $response['referenceNumber'] - Your order reference
        
    } else {
        echo "Payment Failed!\n";
        echo "Status: " . $response['status'] . "\n";
        echo "Reason Code: " . $response['reasonCode'] . "\n";
    }
    
    // Print full response for debugging
    echo "\n--- Full Response ---\n";
    print_r($response);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    // Log the error for troubleshooting
    error_log('CyberSource Payment Error: ' . $e->getMessage());
}

?>
