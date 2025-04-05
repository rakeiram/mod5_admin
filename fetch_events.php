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

// Build the base WHERE clause
$where_clause = "WHERE r.status = 'Confirmed'";
if (!empty($filter_month)) $where_clause .= " AND MONTH(r.start_date) = '$filter_month'";
if (!empty($filter_year)) $where_clause .= " AND YEAR(r.start_date) = '$filter_year'";
if (!empty($filter_resort)) $where_clause .= " AND r.resort_id = '$filter_resort'";
if (!empty($filter_status)) $where_clause .= " AND r.payment_status = '$filter_status'";
if (!empty($filter_payment)) $where_clause .= " AND r.payment_method = '$filter_payment'";

// Build the SQL query with filters for events
$sql = "SELECT r.id, r.full_name, res.name AS resort_name, r.event_type, r.num_guests, 
        r.start_date, r.time_in, r.end_date, r.time_out, 
        r.payment_method, r.payment_status 
        FROM reservations r 
        JOIN resorts res ON r.resort_id = res.id 
        $where_clause";
$sql .= " ORDER BY r.start_date ASC, r.time_in ASC LIMIT $start, $limit";
$result = $conn->query($sql);

// Get total records count for pagination
$count_sql = "SELECT COUNT(*) AS total FROM reservations r 
              JOIN resorts res ON r.resort_id = res.id 
              $where_clause";
$count_result = $conn->query($count_sql);
$count_row = $count_result->fetch_assoc();
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $limit);

// Fetch paid and pending counts with filters
$paid_sql = "SELECT COUNT(*) AS paid FROM reservations r 
             JOIN resorts res ON r.resort_id = res.id 
             $where_clause AND r.payment_status = 'Paid'";
$paid_result = $conn->query($paid_sql);
$total_paid = $paid_result->fetch_assoc()['paid'];

$pending_sql = "SELECT COUNT(*) AS pending FROM reservations r 
                JOIN resorts res ON r.resort_id = res.id 
                $where_clause AND r.payment_status = 'Pending'";
$pending_result = $conn->query($pending_sql);
$total_pending = $pending_result->fetch_assoc()['pending'];

// Fetch all records for export and search
$all_sql = "SELECT r.id, r.full_name, res.name AS resort_name, r.event_type, r.num_guests, 
            r.start_date, r.time_in, r.end_date, r.time_out, 
            r.payment_method, r.payment_status 
            FROM reservations r 
            JOIN resorts res ON r.resort_id = res.id 
            $where_clause";
$all_sql .= " ORDER BY r.start_date ASC, r.time_in ASC";
$all_result = $conn->query($all_sql);

$events = [];
while ($row = $all_result->fetch_assoc()) {
    $events[] = [
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
    'events' => $events,
    'total_pages' => $total_pages,
    'total_paid' => $total_paid,
    'total_pending' => $total_pending
]);
?>