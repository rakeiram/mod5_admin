<?php
include 'config.php';

// Fetch filter values from GET parameters
$filter_resort = $_GET['filter_resort'] ?? '';
$filter_event_type = $_GET['filter_event_type'] ?? '';
$filter_year = $_GET['filter_year'] ?? '';
$filter_month = $_GET['filter_month'] ?? '';

$sql = "SELECT r.id, r.full_name, r.start_date, r.time_in, r.end_date, r.time_out, res.name AS resort_name 
        FROM reservations r 
        JOIN resorts res ON r.resort_id = res.id 
        WHERE r.status = 'Confirmed'";
if (!empty($filter_resort)) $sql .= " AND r.resort_id = '$filter_resort'";
if (!empty($filter_event_type)) $sql .= " AND r.event_type = '$filter_event_type'";
if (!empty($filter_year)) $sql .= " AND YEAR(r.start_date) = '$filter_year'";
if (!empty($filter_month)) $sql .= " AND MONTH(r.start_date) = '$filter_month'";
$result = $conn->query($sql);

$events = [];

while ($row = $result->fetch_assoc()) {
    // Combine date and time for FullCalendar
    $start = $row['start_date'] . 'T' . $row['time_in'];
    $end = $row['end_date'] && $row['time_out'] ? $row['end_date'] . 'T' . $row['time_out'] : null;

    $events[] = [
        'id' => $row['id'],
        'title' => $row['full_name'] . ' - ' . $row['resort_name'],
        'start' => $start,
        'end' => $end,
        'backgroundColor' => '#C5824B',
        'borderColor' => '#A97155',
        'extendedProps' => [
            'time_in' => $row['time_in'],
            'time_out' => $row['time_out']
        ]
    ];
}

header('Content-Type: application/json');
echo json_encode($events);
?>