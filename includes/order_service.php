<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
function create_order(int $caseId, ?int $prescriptionId, int $patientId, array $data): int
{
    $code = generate_code('ORD');
    db_execute('INSERT INTO orders (case_id,prescription_id,patient_id,order_code,delivery_name,delivery_phone,delivery_whatsapp,delivery_address,city,payment_method,status,admin_notes,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?, ?,NOW(),NOW())', [$caseId,$prescriptionId,$patientId,$code,$data['delivery_name'],$data['delivery_phone'],$data['delivery_whatsapp'],$data['delivery_address'],$data['city'],'Cash on Delivery','pending_doctor_verification',$data['admin_notes'] ?? '']);
    $id = (int)db()->lastInsertId();
    db_execute('INSERT INTO order_status_logs (order_id,status,note,created_at) VALUES (?,?,?,NOW())', [$id,'pending_doctor_verification','Medicine requested by patient.']);
    db_execute('UPDATE patient_cases SET order_status="pending_doctor_verification" WHERE id=?', [$caseId]);
    return $id;
}
function update_order_status(int $orderId, string $status, string $note, ?int $userId): void
{
    db_execute('UPDATE orders SET status=?, updated_at=NOW() WHERE id=?', [$status,$orderId]);
    db_execute('INSERT INTO order_status_logs (order_id,status,note,changed_by,created_at) VALUES (?,?,?,?,NOW())', [$orderId,$status,$note,$userId]);
}
