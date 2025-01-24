<?php

class SpringCourier
{
    private const API_URL = "https://mtapi.net/?testMode=1";

    /**
     * Create a new shipment
     *
     * @param array $order Associative array containing sender and recipient data
     * @param array $params Additional data for shipment creation (API key, service, etc.)
     *
     * @return array
     */
    public function newPackage(array $order, array $params): array
    {
        $data = [
            "Apikey" => $params["apiKey"],
            "Command" => "OrderShipment",
            "Shipment" => [
                "LabelFormat" => $params["labelFormat"],
                "ShipperReference" => $params["shipperReference"],
                "Service" => $params["service"],
                "ConsignorAddress" => $order["sender"],
                "ConsigneeAddress" => $order["recipient"],
            ],
        ];

        $response = $this->curlExec($data);

        if (empty($response) || !isset($response["ErrorLevel"]) || $response["ErrorLevel"] !== 0) {
            return $this->formatErrorResponse($response, "Error creating shipment");
        }

        $trackingNumber = $response["Shipment"]["TrackingNumber"] ?? null;
        if (empty($trackingNumber)) {
            return [
                "error" => true,
                "message" => "Missing tracking number in API response.",
            ];
        }

        return [
            "success" => true,
            "trackingNumber" => $trackingNumber,
        ];
    }

    /**
     * Get a shipping label
     *
     * @param string $trackingNumber The tracking number from the shipment
     * @param string|null $apiKey Optional API key override
     *
     * @return array
     */
    public function packagePDF(string $trackingNumber, string $apiKey = null): array
    {
        $data = [
            "Apikey" => $apiKey ?: "f16753b55cac6c6e",
            "Command" => "GetShipmentLabel",
            "Shipment" => [
                "LabelFormat" => "PDF",
                "TrackingNumber" => $trackingNumber,
            ],
        ];

        $response = $this->curlExec($data);

        if (empty($response) || !isset($response["ErrorLevel"]) || $response["ErrorLevel"] !== 0) {
            return $this->formatErrorResponse($response, "Error getting shipping label");
        }

        $labelImage = $response["Shipment"]["LabelImage"] ?? null;
        if (empty($labelImage)) {
            return [
                "error" => true,
                "message" => "Missing label image in API response.",
            ];
        }

        return [
            "success" => true,
            "labelImage" => $labelImage,
            "trackingNumber" => $response["Shipment"]["TrackingNumber"] ?? "",
        ];
    }

    /**
     * Execute a connection to the API using cURL
     *
     * @param array $data Data to send to the API
     *
     * @return array
     */
    private function curlExec(array $data): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => self::API_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => $this->isHttps(),
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                "ErrorLevel" => 2,
                "Error" => "Connection error: {$error}",
            ];
        }

        return json_decode($response, true) ?? [];
    }

    /**
     * Check if the connection is HTTPS
     *
     * @return bool
     */
    private function isHttps(): bool
    {
        return isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on";
    }

    /**
     * Format an error response for better readability
     *
     * @param array|null $response API response data
     * @param string $defaultMessage Default error message
     *
     * @return array
     */
    private function formatErrorResponse(?array $response, string $defaultMessage): array
    {
        $errorMessage = $response["Error"] ?? "Unknown error";
        return [
            "error" => true,
            "message" => "{$defaultMessage}: {$errorMessage}",
        ];
    }
}
