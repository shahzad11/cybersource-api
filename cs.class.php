<?php
/**
 * CyberSource REST API PHP Wrapper Class
 * 
 * A simple, lightweight PHP library for integrating CyberSource Payment Gateway
 * using the modern REST API with HTTP Signature Authentication.
 * 
 * Developed by: WebZeto (webzeto.com) & Shahzad Mirza (shahzadmirza.com)
 * 
 * @author Shahzad Mirza
 * @website https://webzeto.com
 * @portfolio https://shahzadmirza.com
 * @version 2.0.0
 * @license MIT
 */

class CyberSourceAPI {

    /**
     * Configuration Constants
     * Update these with your actual CyberSource credentials
     */
    
    // Test Environment
    const TEST_API_ENDPOINT = 'https://apitest.cybersource.com';
    
    // Production Environment  
    const PRODUCTION_API_ENDPOINT = 'https://api.cybersource.com';
    
    /**
     * Merchant Configuration
     * Get these from your CyberSource Business Center
     */
    private $merchantID;
    private $apiKeyID;
    private $secretKey;
    private $apiEndpoint;
    private $environment;
    
    /**
     * Request Data Storage
     */
    private $requestData = [];
    private $clientReferenceCode;
    
    /**
     * API Version
     */
    const API_VERSION = 'pts/v2';
    
    /**
     * Constructor
     * 
     * @param array $config Configuration array with merchantID, apiKeyID, secretKey
     * @param string $environment 'test' or 'production'
     */
    public function __construct($config = [], $environment = 'test') {
        $this->environment = strtolower($environment);
        
        // Set API endpoint based on environment
        $this->apiEndpoint = ($this->environment === 'production') 
            ? self::PRODUCTION_API_ENDPOINT 
            : self::TEST_API_ENDPOINT;
        
        // Set credentials from config or constants
        $this->merchantID = isset($config['merchantID']) ? $config['merchantID'] : '';
        $this->apiKeyID = isset($config['apiKeyID']) ? $config['apiKeyID'] : '';
        $this->secretKey = isset($config['secretKey']) ? $config['secretKey'] : '';
        
        // Initialize request structure
        $this->initializeRequest();
    }
    
    /**
     * Initialize the base request structure
     */
    private function initializeRequest() {
        $this->requestData = [
            'clientReferenceInformation' => [
                'code' => $this->generateReferenceCode()
            ],
            'processingInformation' => [
                'commerceIndicator' => 'internet',
                'capture' => true  // Auto-capture by default
            ]
        ];
    }
    
    /**
     * Generate a unique client reference code
     */
    private function generateReferenceCode() {
        return 'ORDER_' . uniqid() . '_' . time();
    }
    
    /**
     * Set client reference code (order ID)
     * 
     * @param string $code Your order ID or reference code
     * @return $this
     */
    public function setClientReferenceCode($code) {
        $this->clientReferenceCode = $code;
        $this->requestData['clientReferenceInformation']['code'] = $code;
        return $this;
    }
    
    /**
     * Add credit card details
     * 
     * @param array $cc_data Credit card information
     *   - number: Card number (string)
     *   - expirationMonth: MM format (string)
     *   - expirationYear: YYYY format (string)
     *   - securityCode: CVV/CVC (string, optional)
     * @return $this
     */
    public function addCreditCardDetails($cc_data) {
        $this->requestData['paymentInformation']['card'] = [
            'number' => isset($cc_data['number']) ? $cc_data['number'] : (isset($cc_data['accountNumber']) ? $cc_data['accountNumber'] : ''),
            'expirationMonth' => isset($cc_data['expirationMonth']) ? $cc_data['expirationMonth'] : '',
            'expirationYear' => isset($cc_data['expirationYear']) ? $cc_data['expirationYear'] : '',
        ];
        
        // Add CVV if provided (mapped to securityCode in new API)
        if (isset($cc_data['securityCode']) || isset($cc_data['cvv'])) {
            $this->requestData['paymentInformation']['card']['securityCode'] = 
                isset($cc_data['securityCode']) ? $cc_data['securityCode'] : $cc_data['cvv'];
        }
        
        return $this;
    }
    
    /**
     * Add billing information
     * 
     * @param array $billing_info Billing address information
     *   - firstName: First name (string)
     *   - lastName: Last name (string)
     *   - address1: Street address (string)
     *   - locality: City (string)
     *   - administrativeArea: State/Province (string)
     *   - postalCode: ZIP/Postal code (string)
     *   - country: Country code (string, 2-letter ISO)
     *   - email: Email address (string)
     *   - phoneNumber: Phone number (string, optional)
     * @return $this
     */
    public function addBillingInfo($billing_info) {
        // Map old field names to new API field names
        $this->requestData['orderInformation']['billTo'] = [
            'firstName' => isset($billing_info['firstName']) ? $billing_info['firstName'] : '',
            'lastName' => isset($billing_info['lastName']) ? $billing_info['lastName'] : '',
            'address1' => isset($billing_info['address1']) ? $billing_info['address1'] : (isset($billing_info['street1']) ? $billing_info['street1'] : ''),
            'locality' => isset($billing_info['locality']) ? $billing_info['locality'] : (isset($billing_info['city']) ? $billing_info['city'] : ''),
            'administrativeArea' => isset($billing_info['administrativeArea']) ? $billing_info['administrativeArea'] : (isset($billing_info['state']) ? $billing_info['state'] : ''),
            'postalCode' => isset($billing_info['postalCode']) ? $billing_info['postalCode'] : '',
            'country' => isset($billing_info['country']) ? $billing_info['country'] : '',
            'email' => isset($billing_info['email']) ? $billing_info['email'] : '',
        ];
        
        // Add phone if provided
        if (isset($billing_info['phoneNumber'])) {
            $this->requestData['orderInformation']['billTo']['phoneNumber'] = $billing_info['phoneNumber'];
        }
        
        return $this;
    }
    
    /**
     * Add order amount and currency
     * 
     * @param array $amount_data Amount details
     *   - totalAmount: Transaction amount (string)
     *   - currency: Currency code (string, 3-letter ISO, default: USD)
     * @return $this
     */
    public function addOrderAmount($amount_data) {
        $this->requestData['orderInformation']['amountDetails'] = [
            'totalAmount' => isset($amount_data['totalAmount']) ? $amount_data['totalAmount'] : (isset($amount_data['amount']) ? $amount_data['amount'] : ''),
            'currency' => isset($amount_data['currency']) ? $amount_data['currency'] : 'USD'
        ];
        
        return $this;
    }
    
    /**
     * Legacy method: Add item (maps to order amount for backward compatibility)
     * 
     * @param array $item_info Item information
     *   - unitPrice: Price per unit (string)
     *   - quantity: Quantity (int)
     *   - id: Product/Order ID (string)
     * @return $this
     */
    public function addItem($item_info) {
        $unitPrice = isset($item_info['unitPrice']) ? $item_info['unitPrice'] : '0.00';
        $quantity = isset($item_info['quantity']) ? (int)$item_info['quantity'] : 1;
        $totalAmount = number_format((float)$unitPrice * $quantity, 2, '.', '');
        
        // Set the order amount
        $this->addOrderAmount([
            'totalAmount' => $totalAmount,
            'currency' => 'USD'
        ]);
        
        // If id is provided, use it as client reference code
        if (isset($item_info['id'])) {
            $this->setClientReferenceCode($item_info['id']);
        }
        
        return $this;
    }
    
    /**
     * Set processing options
     * 
     * @param array $options Processing options
     *   - capture: Auto-capture (boolean, default: true)
     *   - commerceIndicator: Commerce indicator (string, default: 'internet')
     * @return $this
     */
    public function setProcessingOptions($options) {
        if (isset($options['capture'])) {
            $this->requestData['processingInformation']['capture'] = (bool)$options['capture'];
        }
        if (isset($options['commerceIndicator'])) {
            $this->requestData['processingInformation']['commerceIndicator'] = $options['commerceIndicator'];
        }
        return $this;
    }
    
    /**
     * Generate HTTP Signature for authentication
     * 
     * @param string $method HTTP method
     * @param string $target Request target path
     * @param string $date RFC7231 formatted date
     * @param string $digest SHA-256 digest for POST requests
     * @return string Signature header value
     */
    private function generateHttpSignature($method, $target, $date, $digest = '') {
        $host = str_replace(['https://', 'http://'], '', $this->apiEndpoint);
        
        // Build headers parameter based on method
        if (strtoupper($method) === 'POST' || strtoupper($method) === 'PUT' || strtoupper($method) === 'PATCH') {
            $headers = 'host v-c-date request-target digest v-c-merchant-id';
            $signatureBase = sprintf(
                "host: %s\nv-c-date: %s\nrequest-target: %s %s\ndigest: %s\nv-c-merchant-id: %s",
                $host,
                $date,
                strtolower($method),
                $target,
                $digest,
                $this->merchantID
            );
        } else {
            $headers = 'host v-c-date request-target v-c-merchant-id';
            $signatureBase = sprintf(
                "host: %s\nv-c-date: %s\nrequest-target: %s %s\nv-c-merchant-id: %s",
                $host,
                $date,
                strtolower($method),
                $target,
                $this->merchantID
            );
        }
        
        // Generate HMAC-SHA256 signature
        $decodedSecret = base64_decode($this->secretKey);
        $signature = base64_encode(hash_hmac('sha256', $signatureBase, $decodedSecret, true));
        
        // Build the Signature header
        $signatureHeader = sprintf(
            'keyid="%s", algorithm="HmacSHA256", headers="%s", signature="%s"',
            $this->apiKeyID,
            $headers,
            $signature
        );
        
        return $signatureHeader;
    }
    
    /**
     * Generate SHA-256 digest for request body
     * 
     * @param string $body JSON request body
     * @return string Digest value
     */
    private function generateDigest($body) {
        return 'SHA-256=' . base64_encode(hash('sha256', $body, true));
    }
    
    /**
     * Execute the payment transaction
     * 
     * @return array Response data with status and transaction details
     * @throws Exception On API errors
     */
    public function runTransaction() {
        // Validate required fields
        if (empty($this->merchantID) || empty($this->apiKeyID) || empty($this->secretKey)) {
            throw new Exception('Missing required credentials. Please provide merchantID, apiKeyID, and secretKey.');
        }
        
        if (empty($this->requestData['paymentInformation']['card'])) {
            throw new Exception('Credit card details are required. Call addCreditCardDetails() before runTransaction().');
        }
        
        if (empty($this->requestData['orderInformation']['billTo'])) {
            throw new Exception('Billing information is required. Call addBillingInfo() before runTransaction().');
        }
        
        if (empty($this->requestData['orderInformation']['amountDetails'])) {
            throw new Exception('Order amount is required. Call addOrderAmount() or addItem() before runTransaction().');
        }
        
        // Build request
        $endpoint = '/' . self::API_VERSION . '/payments';
        $url = $this->apiEndpoint . $endpoint;
        $body = json_encode($this->requestData);
        $date = gmdate('D, d M Y H:i:s T');
        $digest = $this->generateDigest($body);
        
        // Generate HTTP Signature
        $signature = $this->generateHttpSignature('POST', $endpoint, $date, $digest);
        
        // Build headers
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'v-c-merchant-id: ' . $this->merchantID,
            'v-c-date: ' . $date,
            'Digest: ' . $digest,
            'Signature: ' . $signature,
            'User-Agent: WebZeto-CyberSource-PHP/2.0.0'
        ];
        
        // Execute cURL request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new Exception('cURL Error: ' . $curlError);
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            // Success - normalize response for backward compatibility
            return $this->normalizeResponse($responseData, true);
        } else {
            // Error
            $errorMessage = isset($responseData['message']) ? $responseData['message'] : 'API Error';
            $errorDetails = isset($responseData['details']) ? $responseData['details'] : [];
            throw new Exception(sprintf('CyberSource API Error (%d): %s %s', $httpCode, $errorMessage, json_encode($errorDetails)));
        }
    }
    
    /**
     * Get transaction details
     * 
     * @param string $transactionId Transaction ID to lookup
     * @return array Transaction details
     * @throws Exception On API errors
     */
    public function getTransaction($transactionId) {
        $endpoint = '/' . self::API_VERSION . '/payments/' . $transactionId;
        $url = $this->apiEndpoint . $endpoint;
        $date = gmdate('D, d M Y H:i:s T');
        
        // Generate HTTP Signature (no digest for GET)
        $signature = $this->generateHttpSignature('GET', $endpoint, $date);
        
        // Build headers
        $headers = [
            'Accept: application/json',
            'v-c-merchant-id: ' . $this->merchantID,
            'v-c-date: ' . $date,
            'Signature: ' . $signature,
            'User-Agent: WebZeto-CyberSource-PHP/2.0.0'
        ];
        
        // Execute cURL request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new Exception('cURL Error: ' . $curlError);
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return $this->normalizeResponse($responseData, true);
        } else {
            $errorMessage = isset($responseData['message']) ? $responseData['message'] : 'API Error';
            throw new Exception(sprintf('CyberSource API Error (%d): %s', $httpCode, $errorMessage));
        }
    }
    
    /**
     * Refund a transaction
     * 
     * @param string $transactionId Transaction ID to refund
     * @param float $amount Amount to refund (optional, defaults to full amount)
     * @param string $currency Currency code (optional, defaults to USD)
     * @return array Refund response
     * @throws Exception On API errors
     */
    public function refundTransaction($transactionId, $amount = null, $currency = 'USD') {
        $endpoint = '/' . self::API_VERSION . '/payments/' . $transactionId . '/refunds';
        $url = $this->apiEndpoint . $endpoint;
        
        $refundData = [
            'clientReferenceInformation' => [
                'code' => 'REFUND_' . $transactionId
            ]
        ];
        
        if ($amount !== null) {
            $refundData['orderInformation'] = [
                'amountDetails' => [
                    'totalAmount' => number_format((float)$amount, 2, '.', ''),
                    'currency' => $currency
                ]
            ];
        }
        
        $body = json_encode($refundData);
        $date = gmdate('D, d M Y H:i:s T');
        $digest = $this->generateDigest($body);
        
        // Generate HTTP Signature
        $signature = $this->generateHttpSignature('POST', $endpoint, $date, $digest);
        
        // Build headers
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'v-c-merchant-id: ' . $this->merchantID,
            'v-c-date: ' . $date,
            'Digest: ' . $digest,
            'Signature: ' . $signature,
            'User-Agent: WebZeto-CyberSource-PHP/2.0.0'
        ];
        
        // Execute cURL request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new Exception('cURL Error: ' . $curlError);
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return $this->normalizeResponse($responseData, true);
        } else {
            $errorMessage = isset($responseData['message']) ? $responseData['message'] : 'API Error';
            throw new Exception(sprintf('CyberSource API Error (%d): %s', $httpCode, $errorMessage));
        }
    }
    
    /**
     * Void/Cancel a transaction
     * 
     * @param string $transactionId Transaction ID to void
     * @return array Void response
     * @throws Exception On API errors
     */
    public function voidTransaction($transactionId) {
        $endpoint = '/' . self::API_VERSION . '/payments/' . $transactionId . '/voids';
        $url = $this->apiEndpoint . $endpoint;
        
        $voidData = [
            'clientReferenceInformation' => [
                'code' => 'VOID_' . $transactionId
            ]
        ];
        
        $body = json_encode($voidData);
        $date = gmdate('D, d M Y H:i:s T');
        $digest = $this->generateDigest($body);
        
        // Generate HTTP Signature
        $signature = $this->generateHttpSignature('POST', $endpoint, $date, $digest);
        
        // Build headers
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'v-c-merchant-id: ' . $this->merchantID,
            'v-c-date: ' . $date,
            'Digest: ' . $digest,
            'Signature: ' . $signature,
            'User-Agent: WebZeto-CyberSource-PHP/2.0.0'
        ];
        
        // Execute cURL request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new Exception('cURL Error: ' . $curlError);
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return $this->normalizeResponse($responseData, true);
        } else {
            $errorMessage = isset($responseData['message']) ? $responseData['message'] : 'API Error';
            throw new Exception(sprintf('CyberSource API Error (%d): %s', $httpCode, $errorMessage));
        }
    }
    
    /**
     * Normalize the API response for consistent handling
     * 
     * @param array $responseData Raw API response
     * @param bool $success Whether the request was successful
     * @return array Normalized response
     */
    private function normalizeResponse($responseData, $success) {
        $normalized = [
            'success' => $success,
            'status' => $success ? 'ACCEPTED' : 'REJECTED',
            'reasonCode' => $success ? 100 : (isset($responseData['errorInformation']['reason']) ? $responseData['errorInformation']['reason'] : 'ERROR'),
            'transactionId' => isset($responseData['id']) ? $responseData['id'] : null,
            'referenceNumber' => isset($responseData['clientReferenceInformation']['code']) ? $responseData['clientReferenceInformation']['code'] : null,
            'amount' => isset($responseData['orderInformation']['amountDetails']['totalAmount']) ? $responseData['orderInformation']['amountDetails']['totalAmount'] : null,
            'currency' => isset($responseData['orderInformation']['amountDetails']['currency']) ? $responseData['orderInformation']['amountDetails']['currency'] : null,
            'rawResponse' => $responseData
        ];
        
        // Add payment information if available
        if (isset($responseData['paymentInformation']['card']['type'])) {
            $normalized['cardType'] = $responseData['paymentInformation']['card']['type'];
        }
        
        // Add processor information if available
        if (isset($responseData['processorInformation']['approvalCode'])) {
            $normalized['approvalCode'] = $responseData['processorInformation']['approvalCode'];
        }
        
        // Add reconciliation ID if available
        if (isset($responseData['reconciliationId'])) {
            $normalized['reconciliationId'] = $responseData['reconciliationId'];
        }
        
        // Add submission time
        if (isset($responseData['submitTimeUtc'])) {
            $normalized['submitTime'] = $responseData['submitTimeUtc'];
        }
        
        return $normalized;
    }
    
    /**
     * Reset the request data for a new transaction
     * 
     * @return $this
     */
    public function reset() {
        $this->requestData = [];
        $this->initializeRequest();
        return $this;
    }
}

/**
 * Legacy class alias for backward compatibility
 * @deprecated Use CyberSourceAPI instead
 */
class cyber_source extends CyberSourceAPI {
    // This provides backward compatibility for existing code
}
?>
