<?php
include 'config.php';

$event_type = $_POST['event_type'] ?? '';
$year = $_POST['year'] ?? '';

$sql = "SELECT MONTH(start_date) AS month, 
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status = 'Confirmed' THEN 1 ELSE 0 END) AS confirmed,
        SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) AS cancelled
        FROM reservations";
if (!empty($event_type)) $sql .= " WHERE event_type = '" . $conn->real_escape_string($event_type) . "'";
if (!empty($year)) {
    $condition = !empty($event_type) ? " AND" : " WHERE";
    $sql .= "$condition YEAR(start_date) = '$year'";
}
$sql .= " GROUP BY MONTH(start_date) ORDER BY month ASC";

$result = $conn->query($sql);

$months = [];
$pending = [];
$confirmed = [];
$cancelled = [];

$month_names = [
    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
    7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
];

for ($i = 1; $i <= 12; $i++) {
    $months[$i] = $month_names[$i];
    $pending[$i] = 0;
    $confirmed[$i] = 0;
    $cancelled[$i] = 0;
}

while ($row = $result->fetch_assoc()) {
    $month = (int)$row['month'];
    $pending[$month] = (int)$row['pending'];
    $confirmed[$month] = (int)$row['confirmed'];
    $cancelled[$month] = (int)$row['cancelled'];
}

header('Content-Type: application/json');
echo json_encode([
    'months' => array_values($months),
    'pending' => array_values($pending),
    'confirmed' => array_values($confirmed),
    'cancelled' => array_values($cancelled)
]);
?>