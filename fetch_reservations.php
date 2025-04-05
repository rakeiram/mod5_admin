<?php
include 'config.php';

// Fetch filter values
$filter_month = $_GET['filter_month'] ?? '';
$filter_year = $_GET['filter_year'] ?? '';
$filter_resort = $_GET['filter_resort'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';
$filter_payment = $_GET['filter_payment'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = (int)($_GET['limit'] ?? 10);
$start = ($page - 1) * $limit;

// Build the SQL query with filters
$sql = "SELECT r.id, r.full_name, res.name AS resort_name, r.event_type, r.num_guests, 
        r.start_date, r.time_in, r.end_date, r.time_out, 
        r.payment_method, r.payment_status 
        FROM reservations r 
        JOIN resorts res ON r.resort_id = res.id 
        WHERE r.status = 'Pending'";
if (!empty($filter_month)) $sql .= " AND MONTH(r.start_date) = '$filter_month'";
if (!empty($filter_year)) $sql .= " AND YEAR(r.start_date) = '$filter_year'";
if (!empty($filter_resort)) $sql .= " AND r.resort_id = '$filter_resort'";
if (!empty($filter_status)) $sql .= " AND r.payment_status = '$filter_status'";
if (!empty($filter_payment)) $sql .= " AND r.payment_method = '$filter_payment'";
$sql .= " ORDER BY r.start_date ASC, r.time_in ASC LIMIT $start, $limit";
$result = $conn->query($sql);

// Get total records count for pagination
$count_sql = "SELECT COUNT(*) AS total FROM reservations r 
              JOIN resorts res ON r.resort_id = res.id 
              WHERE r.status = 'Pending'";
if (!empty($filter_month)) $count_sql .= " AND MONTH(r.start_date) = '$filter_month'";
if (!empty($filter_year)) $count_sql .= " AND YEAR(r.start_date) = '$filter_year'";
if (!empty($filter_resort)) $count_sql .= " AND r.resort_id = '$filter_resort'";
if (!empty($filter_status)) $count_sql .= " AND r.payment_status = '$filter_status'";
if (!empty($filter_payment)) $count_sql .= " AND r.payment_method = '$filter_payment'";
$count_result = $conn->query($count_sql);
$count_row = $count_result->fetch_assoc();
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $limit);

// Fetch all records for export and search
$all_sql = "SELECT r.id, r.full_name, res.name AS resort_name, r.event_type, r.num_guests, 
            r.start_date, r.time_in, r.end_date, r.time_out, 
            r.payment_method, r.payment_status 
            FROM reservations r 
            JOIN resorts res ON r.resort_id = res.id 
            WHERE r.status = 'Pending'";
if (!empty($filter_month)) $all_sql .= " AND MONTH(r.start_date) = '$filter_month'";
if (!empty($filter_year)) $all_sql .= " AND YEAR(r.start_date) = '$filter_year'";
if (!empty($filter_resort)) $all_sql .= " AND r.resort_id = '$filter_resort'";
if (!empty($filter_status)) $all_sql .= " AND r.payment_status = '$filter_status'";
if (!empty($filter_payment)) $all_sql .= " AND r.payment_method = '$filter_payment'";
$all_sql .= " ORDER BY r.start_date ASC, r.time_in ASC";
$all_result = $conn->query($all_sql);

$reservations = [];
while ($row = $all_result->fetch_assoc()) {
    $reservations[] = [
        'id' => $row['id'],
        'full_name' => $row['full_name'],
        'resort_name' => $row['resort_name'],
        'event_type' => $row['event_type'],
        'num_guests' => $row['num_guests'],
        'start_date' => $row['start_date'],
        'time_in' => $row['time_in'],
        'end_date' => $row['end_date'],
        'time_out' => $row['time_out'],
        'payment_method' => $row['payment_method'],
        'payment_status' => $row['payment_status']
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'reservations' => $reservations,
    'total_pages' => $total_pages
]);
?>