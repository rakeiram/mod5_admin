<?php
include 'config.php';

// Fetch filter values
$filter_date_from = $_GET['filter_date_from'] ?? '';
$filter_date_to = $_GET['filter_date_to'] ?? '';
$filter_action = $_GET['filter_action'] ?? '';
$filter_admin = $_GET['filter_admin'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$export_all = isset($_GET['export_all']) && $_GET['export_all'] === 'true';

// Calculate offset for pagination
$offset = ($page - 1) * $limit;

$filter_sql = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($filter_date_from) && !empty($filter_date_to)) {
    $filter_sql .= " AND al.timestamp BETWEEN ? AND ?";
    $params[] = $filter_date_from;
    $params[] = $filter_date_to;
    $types .= "ss";
}

if (!empty($filter_action)) {
    $filter_sql .= " AND al.action LIKE ?";
    $params[] = $filter_action . "%";
    $types .= "s";
}

if (!empty($filter_admin)) {
    $filter_sql .= " AND a.name LIKE ?";
    $params[] = "%" . $filter_admin . "%";
    $types .= "s";
}

// Fetch total records for pagination
$count_sql = "SELECT COUNT(*) AS total FROM activity_log al JOIN admins a ON al.admin_id = a.id $filter_sql";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$count_stmt->close();

$total_pages = ceil($total_records / $limit);

// Fetch logs with pagination (unless exporting all)
$sql = "SELECT al.id, al.admin_id, al.action, al.timestamp, a.name AS admin_name 
        FROM activity_log al 
        JOIN admins a ON al.admin_id = a.id 
        $filter_sql 
        ORDER BY al.timestamp DESC";
if (!$export_all) {
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = [
        'id' => $row['id'],
        'admin_name' => $row['admin_name'],
        'action' => $row['action'],
        'timestamp' => $row['timestamp']
    ];
}
$stmt->close();

// Fetch summary stats
$sql_stats = "SELECT 
              SUM(CASE WHEN action = 'Logged in' THEN 1 ELSE 0 END) as logins, 
              SUM(CASE WHEN action = 'Logged out' THEN 1 ELSE 0 END) as logouts 
              FROM activity_log al 
              JOIN admins a ON al.admin_id = a.id 
              $filter_sql";
$stmt_stats = $conn->prepare($sql_stats);
if (!empty($params) && count($params) > 2) { // Exclude limit and offset for stats
    $stmt_stats->bind_param(substr($types, 0, -2), ...array_slice($params, 0, -2));
}
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
$stmt_stats->close();

header('Content-Type: application/json');
echo json_encode([
    'logs' => $logs,
    'total_pages' => $total_pages,
    'total_records' => $total_records,
    'logins' => $stats['logins'],
    'logouts' => $stats['logouts']
]);
?>