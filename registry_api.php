<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

try {
    $pdo = new PDO("mysql:host=localhost;port=3306;dbname=bd;charset=utf8mb4", "haibaneden", "Stilesmile1");
} catch(Exception $e) {
    echo json_encode(["success" => false, "error" => "DB Error"]);
    exit;
}

$page = max(1, (int)($_GET["page"] ?? 1));
$limit = min((int)($_GET["limit"] ?? 50), 50);
$search = trim($_GET["search"] ?? "");

$where = "";
$params = [];

if (!empty($search)) {
    $where = "WHERE company_name LIKE ? OR product_name LIKE ?";
    $params = ["%$search%", "%$search%"];
}

$countSql = "SELECT COUNT(*) FROM registry_entries $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total = $countStmt->fetchColumn();

$offset = ($page - 1) * $limit;
$dataSql = "SELECT * FROM registry_entries $where ORDER BY id DESC LIMIT $limit OFFSET $offset";
$dataStmt = $pdo->prepare($dataSql);
$dataStmt->execute($params);
$records = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($records as &$record) {
    if ($record["inclusion_date"]) {
        $record["inclusion_date"] = date("d.m.Y", strtotime($record["inclusion_date"]));
    }
}

echo json_encode([
    "success" => true,
    "data" => $records,
    "pagination" => [
        "current_page" => $page,
        "total_records" => $total,
        "total_pages" => ceil($total / $limit),
        "per_page" => $limit
    ]
]);
?>