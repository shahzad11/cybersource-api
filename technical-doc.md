# CyberSource API Library - Technical Documentation

## Overview

**Library:** CyberSource REST API PHP Wrapper  
**Version:** 2.0.0  
**Author:** Shahzad Mirza  
**Websites:** [WebZeto](https://webzeto.com) | [Shahzad Mirza Portfolio](https://shahzadmirza.com)  
**License:** MIT  

---

## Architecture

### Migration from SOAP to REST API

| Aspect | Legacy (v1.x) | Current (v2.0+) |
|--------|---------------|-----------------|
| Protocol | SOAP/WSDL | REST/JSON |
| Endpoint Format | `ics2ws*.ic3.com/.../.wsdl` | `api.cybersource.com` |
| Authentication | Username/Password in SOAP Header | HTTP Signature (HMAC-SHA256) |
| Credentials | Merchant ID + Transaction Key | Merchant ID + API Key ID + Secret Key |
| HTTP Method | SOAP POST | GET/POST/PATCH with proper verbs |
| Response Format | SOAP XML | JSON |

---

## HTTP Signature Authentication

The library implements HTTP Signature Authentication as required by CyberSource REST API.

### Signature Components

```
Signature: keyid="<API_KEY_ID>", algorithm="HmacSHA256", headers="<headers>", signature="<SIGNATURE>"
```

### Headers Used (POST requests)

```
host: apitest.cybersource.com
v-c-date: Tue, 30 Jul 2024 15:47:40 GMT
request-target: post /pts/v2/payments
digest: SHA-256=<base64_hash>
v-c-merchant-id: your_merchant_id
```

### Signature Base String Format

```
host: <host>
v-c-date: <date>
request-target: <method> <path>
digest: <digest>
v-c-merchant-id: <merchant_id>
```

### HMAC-SHA256 Generation

```php
$decodedSecret = base64_decode($secretKey);
$signature = base64_encode(hash_hmac('sha256', $signatureBase, $decodedSecret, true));
```

---

## Request/Response Flow

### Payment Processing Flow

```
1. Initialize CyberSourceAPI with credentials
2. Build request object:
   ├── clientReferenceInformation
   ├── processingInformation
   ├── paymentInformation (card details)
   └── orderInformation (billing + amount)
3. Generate HTTP Signature
4. Send POST to /pts/v2/payments
5. Receive JSON response
6. Normalize response for backward compatibility
```

### API Endpoints

| Operation | Endpoint | Method |
|-----------|----------|--------|
| Process Payment | `/pts/v2/payments` | POST |
| Get Transaction | `/pts/v2/payments/{id}` | GET |
| Refund | `/pts/v2/payments/{id}/refunds` | POST |
| Void | `/pts/v2/payments/{id}/voids` | POST |

---

## Code Structure

### Class: `CyberSourceAPI`

```
CyberSourceAPI
├── Private Properties
│   ├── merchantID
│   ├── apiKeyID
│   ├── secretKey
│   ├── apiEndpoint
│   ├── environment
│   ├── requestData[]
│   └── clientReferenceCode
├── Constants
│   ├── TEST_API_ENDPOINT
│   ├── PRODUCTION_API_ENDPOINT
│   └── API_VERSION
├── Public Methods
│   ├── __construct($config, $environment)
│   ├── addCreditCardDetails($cc_data)
│   ├── addBillingInfo($billing_info)
│   ├── addOrderAmount($amount_data)
│   ├── addItem($item_info) [legacy]
│   ├── setClientReferenceCode($code)
│   ├── setProcessingOptions($options)
│   ├── runTransaction()
│   ├── getTransaction($transactionId)
│   ├── refundTransaction($transactionId, $amount, $currency)
│   ├── voidTransaction($transactionId)
│   └── reset()
└── Private Methods
    ├── initializeRequest()
    ├── generateReferenceCode()
    ├── generateHttpSignature($method, $target, $date, $digest)
    ├── generateDigest($body)
    └── normalizeResponse($responseData, $success)
```

### Legacy Class Alias

```php
class cyber_source extends CyberSourceAPI { }
```

Provides backward compatibility for existing code.

---

## Request Schema

### Payment Request Structure

```json
{
  "clientReferenceInformation": {
    "code": "ORDER_abc123_1234567890"
  },
  "processingInformation": {
    "commerceIndicator": "internet",
    "capture": true
  },
  "paymentInformation": {
    "card": {
      "number": "4111111111111111",
      "expirationMonth": "12",
      "expirationYear": "2030",
      "securityCode": "999"
    }
  },
  "orderInformation": {
    "amountDetails": {
      "totalAmount": "99.99",
      "currency": "USD"
    },
    "billTo": {
      "firstName": "John",
      "lastName": "Doe",
      "address1": "1 Market Street",
      "locality": "San Francisco",
      "administrativeArea": "CA",
      "postalCode": "94105",
      "country": "US",
      "email": "john@example.com",
      "phoneNumber": "4155550123"
    }
  }
}
```

---

## Response Schema

### Normalized Response Structure

```php
[
  "success" => true|false,
  "status" => "ACCEPTED"|"REJECTED",
  "reasonCode" => 100|101|...,
  "transactionId" => "string",
  "referenceNumber" => "string",
  "amount" => "99.99",
  "currency" => "USD",
  "cardType" => "visa"|"mastercard"|...,
  "approvalCode" => "string",
  "reconciliationId" => "string",
  "submitTime" => "ISO8601 timestamp",
  "rawResponse" => [...]
]
```

### Raw API Response Fields

| Field | Type | Description |
|-------|------|-------------|
| id | string | Transaction ID |
| submitTimeUtc | string | Submission timestamp |
| status | string | Transaction status |
| reconciliationId | string | Reconciliation ID |
| clientReferenceInformation.code | string | Your order reference |
| processorInformation.approvalCode | string | Auth approval code |
| processorInformation.responseCode | string | Processor response |
| paymentInformation.card.type | string | Card brand |
| orderInformation.amountDetails.totalAmount | string | Transaction amount |
| orderInformation.amountDetails.currency | string | Currency code |
| errorInformation.reason | string | Error reason code |
| errorInformation.message | string | Error message |

---

## Error Handling

### Exception Types

| Exception | Cause | Handling |
|-----------|-------|----------|
| `Exception` | Missing credentials | Validate config before instantiation |
| `Exception` | Missing required fields | Check method calls before runTransaction() |
| `Exception` | cURL errors | Network/connectivity issues |
| `Exception` | API errors (4xx, 5xx) | Invalid data, auth failures, server errors |

### HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 400 | Bad Request - Invalid data |
| 401 | Unauthorized - Invalid signature |
| 403 | Forbidden - Invalid permissions |
| 404 | Not Found |
| 500 | Server Error |

---

## Security Considerations

### Credential Storage

- **DO NOT** hardcode credentials in source files
- **DO** use environment variables or secure config files
- **DO** ensure `secretKey` is base64 encoded as provided by CyberSource
- **DO** use HTTPS in production (enforced by library)

### Data Masking

The library does not log or expose sensitive data:
- CVV is never stored
- Card numbers are handled securely in transit
- Request/response logging is not implemented (user can add if needed)

### PCI Compliance

This library handles the communication layer only. You are responsible for:
- Securing your server environment
- Implementing proper card data handling
- Following PCI DSS requirements for your use case
- Using CyberSource Flex/Secure Acceptance for client-side tokenization when needed

---

## Configuration

### Environment Variables (Recommended)

```bash
# .env file
CYBERSOURCE_MERCHANT_ID=your_merchant_id
CYBERSOURCE_API_KEY_ID=your_api_key_id
CYBERSOURCE_SECRET_KEY=your_secret_key
CYBERSOURCE_ENVIRONMENT=test
```

### PHP Configuration

```php
$config = [
    'merchantID'  => getenv('CYBERSOURCE_MERCHANT_ID'),
    'apiKeyID'    => getenv('CYBERSOURCE_API_KEY_ID'),
    'secretKey'   => getenv('CYBERSOURCE_SECRET_KEY')
];

$cs = new CyberSourceAPI($config, getenv('CYBERSOURCE_ENVIRONMENT'));
```

---

## Testing

### Test Environment

Always use `'test'` environment for development:

```php
$cs = new CyberSourceAPI($config, 'test');
// Uses: https://apitest.cybersource.com
```

### Test Card Numbers

Use official CyberSource test cards. Full list in README.md.

### Debug Mode

For troubleshooting, enable verbose cURL:

```php
// Add to cs.class.php in runTransaction() method
curl_setopt($ch, CURLOPT_VERBOSE, true);
```

---

## Deployment Checklist

### Before Production

- [ ] Switch environment from `'test'` to `'production'`
- [ ] Update endpoints to `api.cybersource.com`
- [ ] Use production credentials (different from test)
- [ ] Enable SSL verification (already enabled by default)
- [ ] Remove debug logging
- [ ] Test with small transaction amounts
- [ ] Implement proper error handling
- [ ] Set up transaction monitoring

### Production Environment

```php
$cs = new CyberSourceAPI($config, 'production');
// Uses: https://api.cybersource.com
```

---

## Version History

### v2.0.0 (2025) - Current
- Complete migration from SOAP to REST API
- HTTP Signature Authentication
- New endpoints: `api.cybersource.com`
- New methods: `getTransaction()`, `refundTransaction()`, `voidTransaction()`
- Response normalization
- Backward compatibility layer

### v1.0 (Legacy)
- SOAP/WSDL API
- Basic authentication
- Old endpoints: `ics2ws*.ic3.com`

---

## Dependencies

### Required PHP Extensions

| Extension | Purpose |
|-----------|---------|
| curl | HTTP requests |
| openssl | Cryptographic functions |
| json | JSON encoding/decoding |
| hash | HMAC-SHA256 computation |

### No External Libraries

This library has zero Composer dependencies. It's fully self-contained.

---

## File Structure

```
cybersource-api/
├── cs.class.php              # Main library file
├── example-charge-payments.php # Usage example
├── README.md                 # User documentation
├── technical-doc.md          # This file
└── .git/                     # Git repository
```

---

## Support & Resources

- **Website:** https://webzeto.com
- **Portfolio:** https://shahzadmirza.com
- **CyberSource Docs:** https://developer.cybersource.com/
- **CyberSource Support:** https://support.cybersource.com/

---

## License

MIT License - Free for personal and commercial use.

**Copyright (c) 2025 Shahzad Mirza / WebZeto**
