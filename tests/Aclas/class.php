<?php

/**
 * Global headers
 */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

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
    protected ?string $endpoint = null;
    protected array $payload = [];
    protected string $mode = 'live';

    public function __construct(array $payload = [])
    {
        $this->payload  = $payload;
        $this->mode     = $payload['mode'] ?? 'sandbox';
        $this->endpoint = $payload['esd_device_endpoint'] ?? null;
    }

    /**
     * Entry point
     */
    public function postTransaction(array $data): array
    {
        $transaction = $this->payload ?? null;

        if (empty($transaction)) {
            return $this->fail(
                'invalid_payload',
                'Missing transaction data',
                []
            );
        }

        switch ($this->mode) {
            case 'test':
                return $this->handleTest($transaction);

            case 'sandbox':
                return $this->handleSandbox($transaction);

            default:
                return $this->handleLive($transaction);
        }
    }

    /**
     * LIVE MODE (real ESD)
     */
    protected function handleLive(array $transaction): array
    {
        if (empty($this->endpoint)) {
            return $this->fail(
                'missing_endpoint',
                'Missing ESD device endpoint',
                []
            );
        }

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($transaction),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);

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

        $decoded = json_decode($response, true);

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
    private function normalizeEsdResponse(array $resp, string $mode = 'test'): array
    {
        return [
            'Message'  => $resp['Message'] ?? ($mode === 'sandbox' ? 'Sandbox completed' : 'Test transaction accepted'),
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
    protected function handleTest(array $transaction): array
    {
        $resp = [
            'CUIN'              => 'TEST-' . strtoupper(bin2hex(random_bytes(4))),
            'ReceiptNumber'     => 'T-' . rand(100000, 999999),
            'DateTime'          => date('Y-m-d H:i:s'),
            'DeviceMode'        => 'test',
            'OriginalPayload'   => $transaction,
        ];
    
        return $this->success('Test transaction accepted', $this->normalizeEsdResponse($resp, 'test'));
    }
    
    /** 
     * SIMULATION MODE (dry-run) 
     */
    protected function handleSandbox(array $transaction): array
    {
        $resp = [
            'CUIN'            => null,
            'Simulated'       => true,
            'DeviceMode'      => 'sandbox',
            'Validated'       => true,
            'Totals'          => [
                'gross'       => $transaction['total'] ?? 0,
                'tax'         => $transaction['tax'] ?? 0,
            ],
            'OriginalPayload' => $transaction,
        ];
    
        return $this->success('Sandbox completed (no transaction committed)', $this->normalizeEsdResponse($resp, 'sandbox'));
    }    

    /**
     * Success response helper
     */
    protected function success(string $message, array $data): array
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
    protected function fail(string $code, string $message, array $data): array
    {
        return [
            'status' => 'failed',
            'error'  => $code,
            'msg'    => $message,
            'obj'    => $data,
        ];
    }
}

?>