<?php  
// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    http_response_code(200);
    exit;
}

// Global CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Expose-Headers: Content-Length, X-JSON");

/**
 * ACLAS API
 * Handles TIMS / ESD device communication
 *
 * Modes:
 *  - live        => real ESD device
 *  - test        => validates + mock CUIN
 *  - sandbox  => dry-run, no ESD call
 *
 * @author Giceha
 * @version 1.2
 */
class ACLAS_API
{
    public ?string $endpoint = null;
    public array $transaction = [];
    public string $mode;

    public function __construct($payload = [])
    {
        $this->mode     = $payload['mode'] ?? 'sandbox';
        $this->endpoint = $payload['esd_device_endpoint'] ?? null;
        file_put_contents('debug_log.text', $this->mode);

        // Handle transaction_data in various formats
        if (isset($payload['transaction_data'])) {
            $txData = $payload['transaction_data'];
            
            // If it's a JSON string, decode it
            if (is_string($txData)) {
                $decoded = json_decode($txData, true);
                $this->transaction = is_array($decoded) ? $decoded : [];
            }
            // If it's already an array, use it directly
            elseif (is_array($txData)) {
                $this->transaction = $txData;
            }
            // If it's an object, convert to array recursively
            elseif (is_object($txData)) {
                $this->transaction = $this->objectToArray($txData);
            }
        }
        
        // Debug: Log what we received at construction
        file_put_contents('constructor_debug.json', json_encode([
            'mode' => $this->mode,
            'endpoint' => $this->endpoint,
            'has_transaction_data_key' => isset($payload['transaction_data']),
            'transaction_data_type' => isset($payload['transaction_data']) ? gettype($payload['transaction_data']) : 'not_set',
            'parsed_transaction' => $this->transaction,
            'full_payload_keys' => array_keys($payload)
        ], JSON_PRETTY_PRINT));
    }

    /**
     * Recursively convert objects to arrays
     */
    private function objectToArray($data): array
    {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }
        
        if (is_array($data)) {
            return array_map([$this, 'objectToArray'], $data);
        }
        
        return $data;
    }

    /**
     * Entry point
     */
    public function postTransaction(): array
    { 
        if (empty($this->transaction)) {
            return $this->fail(
                'invalid_payload',
                'Missing transaction data',
                []
            );
        }
        
        switch ($this->mode) {
            case 'test':
                return $this->handleTest();

            case 'sandbox':
                return $this->handleSandbox();

            default:
                return $this->handleLive();
        }
    }

    /**
     * LIVE MODE (real ESD)
     */
    private function handleLive(): array
    { 
        file_put_contents('debug.json', json_encode($this->transaction, JSON_PRETTY_PRINT));

        if (empty($this->endpoint)) {
            return $this->fail(
                'missing_endpoint',
                'Missing ESD device endpoint',
                []
            );
        }
        
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $this->endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($this->transaction),
    
            CURLOPT_HTTPHEADER => array (
                'Content-Type: application/json',
                'Access-Control-Allow-Origin: *',
                'Accept-Language: *'
            ),
        ));

        $response = curl_exec($ch); 

        file_put_contents('debug_response.text', $response);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);

            return $this->fail(
                'connection_failed',
                $error,
                []
            );
        }

        curl_close($ch);

        $decoded = array();
        $result = json_decode($response, true);  

        foreach ($result as $key => $val) { 
            $decoded[$key] = $val;
        } 

        if (!is_array($decoded)) {
            return $this->fail(
                'invalid_response',
                'Invalid JSON response from ESD device',
                ['raw' => $response]
            );
        }

        if (!empty($decoded['CUIN'])) {
            return $this->success(
                'Transaction successfully saved',
                $decoded
            );
        }

        return $this->fail(
            'transaction_rejected',
            $decoded['Message'] ?? 'Transaction rejected by ESD',
            $decoded
        );
    }

    /**
     * Prepares a standardized ESD response for sandbox/test
     */
    private function normalizeEsdResponse(array $resp): array
    {
        return [
            'Message'  => $resp['Message'] ?? ($this->mode === 'sandbox' ? 'Sandbox completed' : 'Test transaction accepted'),
            'TSIN'     => $resp['TSIN'] ?? 'TSIN-' . strtoupper(bin2hex(random_bytes(3))),
            'CUIN'     => $resp['CUIN'] ?? 'CUIN-' . strtoupper(bin2hex(random_bytes(4))),
            'QRCode'   => $resp['QRCode'] ?? 'https://fake.qr.code/' . rand(1000,9999),
            'dtStmp'   => $resp['dtStmp'] ?? date('Y-m-d H:i:s'),

            // Include the original payload for reference
            'OriginalPayload' => $resp['OriginalPayload'] ?? null,
        ];
    }

    /** 
     * TEST MODE (acts like real device, but safe) 
     */
    private function handleTest(): array
    {
        $resp = [
            'CUIN'              => 'TEST-' . strtoupper(bin2hex(random_bytes(4))),
            'ReceiptNumber'     => 'T-' . rand(100000, 999999),
            'DateTime'          => date('Y-m-d H:i:s'),
            'DeviceMode'        => 'test',
            'OriginalPayload'   => $this->transaction,
        ];
    
        return $this->success('Test transaction accepted', $this->normalizeEsdResponse($resp));
    }
    
    /** 
     * SIMULATION MODE (dry-run) 
     */
    private function handleSandbox(): array
    {
        $resp = [
            'CUIN'            => null,
            'Simulated'       => true,
            'DeviceMode'      => 'sandbox',
            'Validated'       => true,
            'Totals'          => [
                'gross'       => $this->transaction['total'] ?? 0,
                'tax'         => $this->transaction['tax'] ?? 0,
            ],
            'OriginalPayload' => $this->transaction,
        ];
    
        return $this->success('Sandbox completed (no transaction committed)', $this->normalizeEsdResponse($resp));
    }    

    /**
     * Success response helper
     */
    private function success(string $message, array $data): array
    {
        return [
            'status' => 'success',
            'error'  => null,
            'msg'    => $message,
            'obj'    => $data,
        ];
    }

    /**
     * Failure response helper
     */
    private function fail(string $code, string $message, array $data): array
    {
        return [
            'status' => 'failed',
            'error'  => $code,
            'msg'    => $message,
            'obj'    => $data,
        ];
    }
}

try {
    /**
     * Bootstrap / Execution
     */ 
    $input = json_decode($_POST['data'], true);

    $api = new ACLAS_API($input);
    $response = $api->postTransaction();

    echo json_encode($response);
} catch (\Throwable $th) {
    echo json_encode([
        'status' => 'failed',
        'error'  => 500,
        'msg'    => $th->getMessage(),
        'obj'    => [],
    ]);
}
?>