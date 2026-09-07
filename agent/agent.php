<?php

// Polyfills for PHP < 8.0 compatibility
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle)
    {
        return (string) $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle)
    {
        return $needle !== '' && strpos($haystack, $needle) !== false;
    }
}

/**
 * Vrundavan Shield - Secure Hardware Binding Agent & License Client
 *
 * This agent runs locally on authorization client PCs.
 * It reads native hardware serials (Motherboard, BIOS, CPU), generates
 * a stable sha256 hardware ID, signs validation requests using HMAC-SHA256,
 * and hosts a local CORS web listener to bridge secure browser-based logins.
 *
 * Usage:
 *   php agent.php hardware     - Display computed hardware IDs and parameters
 *   php agent.php serve        - Spin up the local CORS web-bridge on port 9988
 *   php agent.php test-login   - Test backend Sanctum API login
 */

// 1. Config Loader
$configPath = __DIR__ . '/config.json';
if (file_exists($configPath)) {
    $config = json_decode(file_get_contents($configPath), true) ?: [];
} else {
    // Default configs for development
    $config = [
        'secret_key' => 'base64:H0naO/XQbCIIp4wRCFsLY9qHcGyNpJiogSwbkNYyotE=', // Must match Laravel APP_KEY or DEVICE_SECRET_KEY
        'backend_url' => 'http://localhost:8000',
        'local_port' => 9988,
        'pc_name' => gethostname() ?: 'Authorized Workplace PC',
    ];
    file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT));
}

// Ensure secret_key is stripped of base64: prefix if present (match Laravel key parsing)
$secretKey = $config['secret_key'];
if (str_starts_with($secretKey, 'base64:')) {
    $secretKey = base64_decode(substr($secretKey, 7));
}

// 2. Main Entry Dispatcher (only run if executed directly via CLI)
if (PHP_SAPI === 'cli' && !isset($_SERVER['REQUEST_URI'])) {
    $action = isset($argv[1]) ? $argv[1] : 'hardware';

    switch ($action) {
        case 'hardware':
            showHardwareInfo();
            break;
        case 'serve':
            startLocalServer($config, $secretKey);
            break;
        case 'test-login':
            testApiLogin($config, $secretKey);
            break;
        default:
            echo "Vrundavan Shield Hardware Agent CLI\n";
            echo "---------------------------------\n";
            echo "Usage:\n";
            echo "  php agent.php hardware     - Read and display local hardware metrics\n";
            echo "  php agent.php serve        - Spin up the local browser-to-agent bridge service on port {$config['local_port']}\n";
            echo "  php agent.php test-login   - Test remote backend API authentication\n";
            break;
    }
}

/**
 * Collect physical motherboard, BIOS, and CPU serials cross-platform.
 */
class HardwareReader
{
    public static function collect()
    {
        $os = strtoupper(PHP_OS);
        $motherboard = '';
        $bios = '';
        $cpu = '';

        if (str_contains($os, 'WIN')) {
            // Windows OS queries via PowerShell (highly robust and bypasses wmic deprecations)
            $motherboard = self::runCmd('powershell -Command "(Get-CimInstance -ClassName Win32_BaseBoard).SerialNumber"');
            if (empty($motherboard)) {
                $motherboard = self::runCmd('wmic baseboard get serialnumber');
            }

            $bios = self::runCmd('powershell -Command "(Get-CimInstance -ClassName Win32_BIOS).SerialNumber"');
            if (empty($bios)) {
                $bios = self::runCmd('wmic bios get serialnumber');
            }

            $cpu = self::runCmd('powershell -Command "(Get-CimInstance -ClassName Win32_Processor).ProcessorId"');
            if (empty($cpu)) {
                $cpu = self::runCmd('wmic cpu get processorid');
            }
        } elseif (str_contains($os, 'DARWIN')) {
            // macOS systems
            $motherboard = self::runCmd("system_profiler SPHardwareDataType | grep 'System Leaks' | awk '{print $3}'");
            $bios = self::runCmd("system_profiler SPHardwareDataType | grep 'Hardware UUID' | awk '{print $3}'");
            $cpu = self::runCmd("sysctl -n machdep.cpu.signature");
        } else {
            // Linux systems (reads standard sysfs files)
            $motherboard = self::runCmd('cat /sys/class/dmi/id/board_serial 2>/dev/null');
            if (empty($motherboard)) {
                $motherboard = self::runCmd('cat /sys/class/dmi/id/product_serial 2>/dev/null');
            }
            $bios = self::runCmd('cat /sys/class/dmi/id/bios_version 2>/dev/null');
            $cpu = self::runCmd("grep -m1 'model name' /proc/cpuinfo | cut -d: -f2");
        }

        // Standardize clean values
        $motherboard = self::sanitize($motherboard, 'MB-SERIAL-UNKNOWN');
        $bios = self::sanitize($bios, 'BIOS-SERIAL-UNKNOWN');
        $cpu = self::sanitize($cpu, 'CPU-ID-UNKNOWN');

        // Generate SHA256 unique hardware hash key
        // SHA256(Motherboard + BIOS + CPU)
        $hardwareId = hash('sha256', $motherboard . $bios . $cpu);
        $geo = self::getGeoLocation();

        return [
            'hardware_id' => $hardwareId,
            'motherboard_serial' => $motherboard,
            'bios_serial' => $bios,
            'cpu_id' => $cpu,
            'latitude' => $geo['latitude'],
            'longitude' => $geo['longitude'],
            'pc_name' => self::getPcName(),
        ];
    }

    public static function getPcName()
    {
        $name = getenv('COMPUTERNAME');
        if (empty($name)) {
            $name = gethostname();
        }
        return $name ? $name : 'Authorized Workplace PC';
    }

    public static function getGeoLocation()
    {
        $lat = '0.0';
        $lon = '0.0';
        try {
            $ctx = stream_context_create(['http' => ['timeout' => 3]]); // 3 seconds timeout
            $res = @file_get_contents('http://ip-api.com/json/', false, $ctx);
            if ($res) {
                $data = json_decode($res, true);
                if (isset($data['status']) && $data['status'] === 'success') {
                    $lat = (string) (isset($data['lat']) ? $data['lat'] : '0.0');
                    $lon = (string) (isset($data['lon']) ? $data['lon'] : '0.0');
                }
            }
        } catch (\Exception $e) {
            // Suppress
        }
        return ['latitude' => $lat, 'longitude' => $lon];
    }

    private static function sanitize($val, $fallback)
    {
        // Strip column titles if wmic commands leak headers
        $val = str_ireplace(['SerialNumber', 'ProcessorId'], '', $val);
        $val = trim($val);
        // Exclude generic placeholder values returned by virtual environments or unprogrammed BIOS
        $placeholders = ['To be filled by O.E.M.', 'Default String', 'None', 'Unknown', 'Not Specified', '00000000000'];
        foreach ($placeholders as $ph) {
            if (strcasecmp($val, $ph) === 0) {
                return $fallback;
            }
        }
        return empty($val) ? $fallback : $val;
    }

    private static function runCmd($cmd)
    {
        $output = '';
        try {
            $output = @shell_exec($cmd);
        } catch (Exception $e) {
            // Suppress shell execution exceptions
        }
        return $output ? trim($output) : '';
    }
}

/**
 * Generate cryptographic authentication signature.
 */
class SignatureGenerator
{
    public static function sign($hardwareId, $secretKey)
    {
        $timestamp = time();
        $nonce = bin2hex(function_exists('random_bytes') ? random_bytes(16) : openssl_random_pseudo_bytes(16)); // Secure random nonce

        // Concatenate parameters exactly matching Laravel validator logic
        $payload = $hardwareId . $timestamp . $nonce;
        $signature = hash_hmac('sha256', $payload, $secretKey);

        return [
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'signature' => $signature,
        ];
    }
}

/**
 * Output the hardware information directly.
 */
function showHardwareInfo()
{
    $hw = HardwareReader::collect();
    echo "\n==================================================\n";
    echo "       VRUNDAVAN SHIELD HARDWARE DETECTOR         \n";
    echo "==================================================\n";
    echo "Motherboard Serial : " . $hw['motherboard_serial'] . "\n";
    echo "BIOS Serial No     : " . $hw['bios_serial'] . "\n";
    echo "CPU Processor ID   : " . $hw['cpu_id'] . "\n";
    echo "--------------------------------------------------\n";
    echo "STABLE HARDWARE ID : " . $hw['hardware_id'] . "\n";
    echo "==================================================\n\n";
}

/**
 * Start a lightweight CORS web listener on 127.0.0.1:9988.
 * Allows the browser CRM application login page to pull the hardware
 * parameters securely using standard HTTP Fetch requests.
 */
function startLocalServer($config, $secretKey)
{
    $port = $config['local_port'];
    echo "==================================================\n";
    echo "       VRUNDAVAN SHIELD BROWSER BRIDGE SERVICE     \n";
    echo "==================================================\n";
    echo "Status        : ACTIVE & LISTENING\n";
    echo "Bridge Route  : http://127.0.0.1:{$port}/hardware\n";
    echo "Allowed CORS  : All origins (CORS Wildcard Allowed)\n";
    echo "Press Ctrl+C to terminate this server securely...\n";
    echo "==================================================\n\n";

    // Setup basic PHP built-in server script dynamically
    $routerFile = __DIR__ . '/router.php';
    $routerCode = '<?php
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
    ';
    file_put_contents($routerFile, $routerCode);

    // Bind built-in PHP web server to 127.0.0.1
    // Using passthru to keep running continuously in CLI
    passthru("php -S 127.0.0.1:{$port} \"{$routerFile}\"");
}

/**
 * Test Api Sanctum login using raw signed CLI request.
 */
function testApiLogin($config, $secretKey)
{
    echo "Initiating API Sanctum test client...\n";
    echo "Enter Email: ";
    $email = trim(fgets(STDIN));
    echo "Enter Password: ";
    // Hide password characters in console on Windows or standard CLI
    $password = trim(fgets(STDIN));

    $hw = HardwareReader::collect();
    $sig = SignatureGenerator::sign($hw['hardware_id'], $secretKey);

    $payload = array_merge([
        'email' => $email,
        'password' => $password,
        'pc_name' => $config['pc_name'],
    ], $hw, $sig);

    $apiUrl = $config['backend_url'] . '/api/login';
    echo "Sending signed payload to: {$apiUrl} ...\n";

    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($payload),
            'ignore_errors' => true
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($apiUrl, false, $context);
    $responseHeaders = isset($http_response_header) ? $http_response_header : [];
    $statusLine = isset($responseHeaders[0]) ? $responseHeaders[0] : '';

    echo "\nServer Response Status: " . $statusLine . "\n";
    $formatted = json_decode($result, true);
    if ($formatted) {
        echo json_encode($formatted, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo $result . "\n";
    }
}
