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

if (!empty($newPackageResponse['success']) && $newPackageResponse['success']) {
    echo "Shipment created successfully! Tracking number: " . $newPackageResponse['trackingNumber'] . "<br>";

    // Retrieve the shipping label
    $labelResponse = $courier->packagePDF($newPackageResponse['trackingNumber']);

    if (!empty($labelResponse['success']) && $labelResponse['success']) {
        // Set PDF headers
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="shipping_label.pdf"');
        
        // Display the label in the browser
        echo base64_decode($labelResponse["labelImage"]);
    } else {
        // Display error message if label generation fails
        echo "Error getting label: " . ($labelResponse['message'] ?? 'Unknown error') . "<br>";
    }
} else {
    // Display error message if shipment creation fails
    echo "Error creating shipment: " . ($newPackageResponse['message'] ?? 'Unknown error') . "<br>";
}
