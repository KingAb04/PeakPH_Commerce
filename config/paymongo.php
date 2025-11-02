<?php
// PayMongo Configuration
return [
    'secret_key' => 'sk_test_XpBQuMz8wkvWQDLJAtKv5UVR',
    'public_key' => 'pk_test_6LoteMf3DA2wM14CohCCvXyQ',
    'base_url' => 'https://api.paymongo.com/v1',
    'webhook_signature_key' => 'whsec_your_webhook_secret',
    
    // Fee Configuration
    'gcash_fee_rate' => 0.035, // 3.5%
    'card_fee_rate' => 0.035, // 3.5%
    'fixed_fee' => 15, // ₱15 fixed fee
    
    // Environment
    'test_mode' => true, // Set false for production
    
    // Redirect URLs
    'success_url' => 'http://localhost/PeakPH_Commerce/payment/success.php',
    'failed_url' => 'http://localhost/PeakPH_Commerce/payment/failed.php',
    'webhook_url' => 'http://localhost/PeakPH_Commerce/webhooks/paymongo.php',
];
?>