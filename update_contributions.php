<?php
header('Content-Type: application/json');

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$jsonFile = 'contributions.json';

// Handle GET requests - return current values
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists($jsonFile)) {
        echo file_get_contents($jsonFile);
    } else {
        echo json_encode([
            'member' => 0.00,
            'admin' => 0.00,
            'lastUpdated' => date('c')
        ]);
    }
    exit;
}

// Handle POST requests - update values
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the request body
    $input = json_decode(file_get_contents('php://input'), true);
    
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
        
        // Write to file
        if (file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT))) {
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to write to file']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
    }
    exit;
}

// Handle other request methods
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
?>