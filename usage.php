<?php

require_once 'SpringCourier.php';

$apiKey = "YOUR_API_KEY"; // ToDo: Ask for the API key

$order = [
    "sender" => [
        "Name" => "Jane Doe",
        "Company" => "Webshop JD",
        "AddressLine1" => "123 Main St",
        "City" => "Amsterdam",
        "Country" => "NL",
        "Zip" => "1012AB",
    ],
    "recipient" => [
        "Name" => "John Smith",
        "AddressLine1" => "456 Park Ave",
        "City" => "New York",
        "State" => "NY",
        "Zip" => "10001",
        "Country" => "US",
        "Phone" => "555-123-4567",
        "Email" => "john.smith@example.com",
    ],
];

$params = [
    "apiKey" => $apiKey,
    "labelFormat" => "PDF",
    "shipperReference" => "Ref_" . time(),
    "service" => "STANDARD",
];

$courier = new SpringCourier();

// Create a new shipment
$newPackageResponse = $courier->newPackage($order, $params);

if ($newPackageResponse['success'] ?? false) {
    echo "Shipment created successfully! Tracking number: " . $newPackageResponse['trackingNumber'] . "<br>";

    // Retrieve the shipping label
    $labelResponse = $courier->packagePDF($newPackageResponse['trackingNumber']);

    if ($labelResponse['success'] ?? false) {
        header('Content-type: application/pdf');
        echo base64_decode($labelResponse["labelImage"]);
    } else {
        echo "Error getting label: " . $labelResponse['message'] . "<br>";
    }
} else {
    echo "Error creating shipment: " . $newPackageResponse['message'] . "<br>";
}
