<?php

/**
 * Pathao Webhook Test Script
 *
 * This script helps you test the Pathao webhook status update functionality
 * without needing to receive an actual webhook from Pathao.
 *
 * Usage:
 * 1. Run this file in your browser: http://127.0.0.1:8000/test_pathao_webhook.php
 * 2. Or run via CLI: php test_pathao_webhook.php
 */

// Test configurations
$baseUrl = 'http://127.0.0.1:8000';
$webhookUrl = $baseUrl . '/admin/pathao/status-update';

// Test cases with different Pathao statuses
$testCases = [
    [
        'name' => 'Test Pending Status',
        'data' => [
            'consignment_id' => 'TEST123456',
            'order_status' => 'Pending',
            'order_status_slug' => 'pending',
            'message' => 'Order is pending',
        ]
    ],
    [
        'name' => 'Test Picked Up Status',
        'data' => [
            'consignment_id' => 'TEST123456',
            'order_status' => 'Picked Up',
            'order_status_slug' => 'picked_up',
            'message' => 'Parcel has been picked up',
        ]
    ],
    [
        'name' => 'Test In Transit Status',
        'data' => [
            'consignment_id' => 'TEST123456',
            'order_status' => 'In Transit',
            'order_status_slug' => 'in_transit',
            'message' => 'Parcel is in transit',
        ]
    ],
    [
        'name' => 'Test Delivered Status',
        'data' => [
            'consignment_id' => 'TEST123456',
            'order_status' => 'Delivered',
            'order_status_slug' => 'delivered',
            'message' => 'Parcel has been delivered',
        ]
    ],
];

function sendWebhookTest($url, $data, $signature) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-PATHAO-Signature: ' . $signature
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}

// Check if running from CLI or browser
$isCLI = php_sapi_name() === 'cli';

if ($isCLI) {
    echo "=== Pathao Webhook Test Script ===\n\n";
    echo "Webhook URL: $webhookUrl\n";
    echo "Signature: " . env('PATHAO_WEBHOOK_SIGNATURE', '123abcd') . "\n\n";

    foreach ($testCases as $i => $test) {
        echo "--- Test " . ($i + 1) . ": {$test['name']} ---\n";
        echo "Sending: " . json_encode($test['data']) . "\n";

        $result = sendWebhookTest(
            $webhookUrl,
            $test['data'],
            env('PATHAO_WEBHOOK_SIGNATURE', '123abcd')
        );

        echo "HTTP Status: {$result['http_code']}\n";
        echo "Response: {$result['response']}\n";

        if ($result['error']) {
            echo "Error: {$result['error']}\n";
        }

        echo "\n";
        sleep(1); // Small delay between tests
    }

    echo "\n=== Check Laravel Logs ===\n";
    echo "Run: tail -f storage/logs/laravel-$(date +%Y-%m-%d).log\n";
    echo "Look for: 'Pathao Webhook Status Update Received'\n";

} else {
    // Browser output
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Pathao Webhook Test</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .test-case { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
            .success { background-color: #d4edda; border-color: #c3e6cb; }
            .error { background-color: #f8d7da; border-color: #f5c6cb; }
            .pending { background-color: #fff3cd; border-color: #ffeeba; }
            button { padding: 10px 20px; margin: 5px; cursor: pointer; }
            pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
        </style>
    </head>
    <body>
        <h1>Pathao Webhook Test Tool</h1>
        <p>Use this tool to test your Pathao webhook integration.</p>

        <div class="test-case">
            <h3>Configuration</h3>
            <p><strong>Webhook URL:</strong> <?php echo htmlspecialchars($webhookUrl); ?></p>
            <p><strong>Signature:</strong> <?php echo htmlspecialchars(env('PATHAO_WEBHOOK_SIGNATURE', '123abcd')); ?></p>
        </div>

        <div class="test-case">
            <h3>Test Webhook</h3>
            <p>Enter a consignment ID from your database (pathao_delivery_id):</p>
            <input type="text" id="consignmentId" placeholder="e.g., PTH-123456" value="TEST123456">
            <select id="status">
                <option value="Pending">Pending</option>
                <option value="Pick Up In Progress">Pick Up In Progress</option>
                <option value="Picked Up">Picked Up</option>
                <option value="In Transit">In Transit</option>
                <option value="Delivered">Delivered</option>
                <option value="Cancelled">Cancelled</option>
                <option value="Returned">Returned</option>
            </select>
            <button onclick="sendTest()">Send Test Webhook</button>
            <button onclick="clearLogs()">Clear Logs</button>
        </div>

        <div class="test-case">
            <h3>Results</h3>
            <div id="results"></div>
        </div>

        <script>
            const webhookUrl = <?php echo json_encode($webhookUrl); ?>;
            const signature = <?php echo json_encode(env('PATHAO_WEBHOOK_SIGNATURE', '123abcd')); ?>;

            function sendTest() {
                const consignmentId = document.getElementById('consignmentId').value;
                const status = document.getElementById('status').value;
                const results = document.getElementById('results');

                const data = {
                    consignment_id: consignmentId,
                    order_status: status,
                    order_status_slug: status.toLowerCase().replace(/ /g, '_'),
                    message: 'Test status update: ' + status
                };

                results.innerHTML = '<div class="pending">Sending webhook...</div>';

                fetch(webhookUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-PATHAO-Signature': signature
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    const div = document.createElement('div');
                    div.className = response.ok ? 'success' : 'error';

                    let content = '<h4>' + (response.ok ? 'Success' : 'Error') + '</h4>';
                    content += '<p><strong>Status:</strong> ' + response.status + '</p>';
                    content += '<p><strong>Request:</strong></p>';
                    content += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';

                    response.json().then(json => {
                        content += '<p><strong>Response:</strong></p>';
                        content += '<pre>' + JSON.stringify(json, null, 2) + '</pre>';
                        div.innerHTML = content;
                    }).catch(() => {
                        content += '<p><strong>Response:</strong> Could not parse JSON</p>';
                        div.innerHTML = content;
                    });

                    results.innerHTML = '';
                    results.appendChild(div);
                })
                .catch(error => {
                    results.innerHTML = '<div class="error"><h4>Error</h4><p>' + error.message + '</p></div>';
                });
            }

            function clearLogs() {
                document.getElementById('results').innerHTML = '';
            }
        </script>

        <div class="test-case">
            <h3>How to Check Logs</h3>
            <p>Run this command in your terminal to see webhook logs:</p>
            <pre>tail -f storage/logs/laravel-$(date +%Y-%m-%d).log | grep -i pathao</pre>
        </div>

        <div class="test-case">
            <h3>Current Orders with Pathao Delivery</h3>
            <p>Check your database for orders with pathao_delivery_id:</p>
            <pre>SELECT id, code, pathao_delivery_id, delivery_status FROM orders WHERE pathao_delivery_id IS NOT NULL;</pre>
        </div>
    </body>
    </html>
    <?php
}