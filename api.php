
<?php
$action = $_GET['action'] ?? '';
$dataFile = 'savings.txt'; 

function readData($file) {
    if (!file_exists($file)) return "0.00|0.00";
    return file_get_contents($file);
}

function writeData($file, $data) {
    return file_put_contents($file, $data) !== false;
}

header('Content-Type: application/json');

if ($action === 'get') {
    $data = readData($dataFile);
    [$member, $admin] = explode('|', $data);
    echo json_encode([
        'member' => $member,
        'admin' => $admin,
        'lastUpdated' => date(DATE_ISO8601)
    ]);
} else if ($action === 'set') {
    $member = $_GET['member'] ?? null;
    $admin = $_GET['admin'] ?? null;

    if ($member !== null && $admin !== null) {
        $newData = "{$member}|{$admin}";
        $success = writeData($dataFile, $newData);
        echo json_encode([
            'success' => $success,
            'data' => [
                'member' => floatval($member),
                'admin' => floatval($admin),
                'lastUpdated' => date(DATE_ISO8601)
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => "Missing parameters"
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => "Invalid action"
    ]);
}
?>
