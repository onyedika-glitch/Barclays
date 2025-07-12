<?php
// Set appropriate headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS requests (for CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Define the data file path
$jsonFile = 'contributions.json';

// Debug information
$debug = [];
$debug['method'] = $_SERVER['REQUEST_METHOD'];
$debug['file_exists'] = file_exists($jsonFile) ? 'yes' : 'no';
$debug['file_writable'] = is_writable($jsonFile) ? 'yes' : 'no';
$debug['directory_writable'] = is_writable('.') ? 'yes' : 'no';

// Default data if file doesn't exist
$defaultData = [
    'member' => 0.00,
    'admin' => 0.00,
    'lastUpdated' => date('c')
];

// Handle GET requests - return current values
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists($jsonFile)) {
        $content = file_get_contents($jsonFile);
        if ($content === false) {
            $debug['error'] = 'Failed to read file';
            echo json_encode($defaultData + ['debug' => $debug]);
        } else {
            $data = json_decode($content, true);
            if ($data === null) {
                $debug['error'] = 'Invalid JSON in file';
                echo json_encode($defaultData + ['debug' => $debug]);
            } else {
                echo json_encode($data + ['debug' => $debug]);
            }
        }
    } else {
        // Create the file if it doesn't exist
        $result = file_put_contents($jsonFile, json_encode($defaultData, JSON_PRETTY_PRINT));
        $debug['file_created'] = $result !== false ? 'yes' : 'no';
        echo json_encode($defaultData + ['debug' => $debug]);
    }
    exit;
}

// Handle POST requests - update values
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the request body
    $rawInput = file_get_contents('php://input');
    $debug['raw_input'] = $rawInput;
    
    $input = json_decode($rawInput, true);
    $debug['decoded_input'] = $input;
    
    // Validate input
    if (isset($input['member']) && isset($input['admin'])) {
        $member = floatval($input['member']);
        $admin = floatval($input['admin']);
        
        // Create the data structure
        $data = [
            'member' => $member,
            'admin' => $admin,
            'lastUpdated' => date('c')
        ];
        
        // Try to write to file
        $result = file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
        
        if ($result !== false) {
            echo json_encode(['success' => true, 'data' => $data, 'debug' => $debug]);
        } else {
            // Try to diagnose the issue
            $debug['error'] = 'Failed to write to file';
            $debug['file_put_contents_result'] = $result;
            $debug['disk_free_space'] = function_exists('disk_free_space') ? disk_free_space('.') : 'unknown';
            
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to write to file', 'debug' => $debug]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input', 'debug' => $debug]);
    }
    exit;
}

// Handle other request methods
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed', 'debug' => $debug]);
?>