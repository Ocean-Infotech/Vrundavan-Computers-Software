<?php
    // Router code for local PHP agent CORS bridge
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Accept");

    if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
        exit(0);
    }

    if (parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) === "/hardware") {
        require_once __DIR__ . "/agent.php";
        $hw = HardwareReader::collect();
        $sig = SignatureGenerator::sign($hw["hardware_id"], $secretKey);
        
        $response = array_merge($hw, $sig);
        
        header("Content-Type: application/json");
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit(0);
    }

    header("HTTP/1.1 404 Not Found");
    echo "404 Route Not Found";
    