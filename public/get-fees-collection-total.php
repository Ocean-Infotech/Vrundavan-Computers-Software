<?php
// =============================================================
// Standalone Fee Collection Total API (Raw PHP & PDO)
// =============================================================

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 1. Database Configuration (Loads from Laravel .env if available, or defaults)
$db_host = env_get('DB_HOST', '127.0.0.1');
$db_name = env_get('DB_DATABASE', 'bca_16_2026');
$db_user = env_get('DB_USERNAME', 'root');
$db_pass = env_get('DB_PASSWORD', '');

function env_get($key, $default = '') {
    $possibleEnvPaths = [
        __DIR__ . '/../.env',
        __DIR__ . '/../Vrundavan-Computers-Software/.env',
        'D:/xampp/htdocs/Vrundavan-Computers-Software/.env',
        __DIR__ . '/.env'
    ];

    foreach ($possibleEnvPaths as $envFile) {
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                list($name, $value) = explode('=', $line, 2) + [NULL, NULL];
                if (trim($name) === $key) {
                    return trim($value, " \"'");
                }
            }
        }
    }
    return $default;
}

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => "false",
        "message" => "Database connection failed: " . $e->getMessage()
    ]);
    exit();
}

// 2. Read Request Parameters (GET, POST JSON, POST Form Data)
$rawInput = json_decode(file_get_contents('php://input'), true) ?? [];

$date = $_GET['date'] ?? $_POST['date'] ?? $rawInput['date'] ?? $_GET['request_date'] ?? $_POST['request_date'] ?? $rawInput['request_date'] ?? null;
$fromDate = $_GET['from_date'] ?? $_POST['from_date'] ?? $rawInput['from_date'] ?? null;
$toDate = $_GET['to_date'] ?? $_POST['to_date'] ?? $rawInput['to_date'] ?? null;
$courseId = $_GET['course_id'] ?? $_POST['course_id'] ?? $rawInput['course_id'] ?? null;

function parse_request_date($dateStr) {
    if (empty($dateStr)) return null;
    $dateStr = trim($dateStr);
    
    // Format YYYY-MM-DD
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
        return $dateStr;
    }
    
    // Format DD-MM-YYYY or DD/MM/YYYY
    if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $dateStr, $matches)) {
        $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $year = $matches[3];
        return "$year-$month-$day";
    }

    return date('Y-m-d', strtotime(str_replace('/', '-', $dateStr)));
}

if (empty($date) && (empty($fromDate) || empty($toDate))) {
    echo json_encode([
        "status" => "false",
        "message" => "Please select date."
    ], JSON_PRETTY_PRINT);
    exit();
}

// 3. Build Query
$params = [];
$sql = "SELECT SUM(fees) as total_fees_collected FROM fees_collections WHERE deleted_at IS NULL";

$requestDateLabel = "";

if (!empty($date)) {
    $formattedDate = parse_request_date($date);
    $sql .= " AND DATE(date) = :date";
    $params[':date'] = $formattedDate;
    $requestDateLabel = date('d-m-Y', strtotime($formattedDate));
} elseif (!empty($fromDate) && !empty($toDate)) {
    $from = parse_request_date($fromDate);
    $to = parse_request_date($toDate);
    $sql .= " AND DATE(date) BETWEEN :from_date AND :to_date";
    $params[':from_date'] = $from;
    $params[':to_date'] = $to;
    $requestDateLabel = date('d-m-Y', strtotime($from)) . " to " . date('d-m-Y', strtotime($to));
}

if (!empty($courseId)) {
    $sql .= " AND course_id = :course_id";
    $params[':course_id'] = $courseId;
}

// 4. Execute Query & Output Result
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();

    $totalFees = $result['total_fees_collected'] !== null ? (float) $result['total_fees_collected'] : 0.0;

    echo json_encode([
        // "status" => "true",
        // "message" => "Fees collection total fetched successfully.",
        "data" => [
            "request_date" => $requestDateLabel,
            "total_fees_collected" => $totalFees
        ]
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        "status" => "false",
        "message" => "Error: " . $e->getMessage()
    ]);
}
