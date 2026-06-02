<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

function create_prescription_from_review(int $caseId, int $doctorId, array $payload): int
{
    $code = generate_code('RX');
    db_execute('INSERT INTO prescriptions (case_id, doctor_id, prescription_code, diagnosis_text, instructions, warning_notes, verified_text, status, created_at) VALUES (?,?,?,?,?,?,?,?,NOW())', [$caseId,$doctorId,$code,$payload['diagnosis_text'] ?? '',$payload['instructions'] ?? '',$payload['warning_notes'] ?? 'Use medicines only as directed. In emergency symptoms, seek urgent medical care.','Verified by Specialist Homeopathic Doctor','approved']);
    $pid = (int)db()->lastInsertId();
    foreach (($payload['items'] ?? []) as $item) {
        if (trim((string)($item['medicine_name'] ?? $item['formula_name'] ?? '')) === '') continue;
        db_execute('INSERT INTO prescription_items (prescription_id,item_type,medicine_name,formula_name,potency,dose,frequency,duration,instructions,source_reference,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,NOW())', [$pid,$item['item_type'] ?? 'remedy',$item['medicine_name'] ?? '',$item['formula_name'] ?? '',$item['potency'] ?? '',$item['dose'] ?? '',$item['frequency'] ?? '',$item['duration'] ?? '',$item['instructions'] ?? '',$item['source_reference'] ?? '']);
    }
    db_execute('UPDATE patient_cases SET doctor_status="approved", status="prescription_approved" WHERE id=?', [$caseId]);
    return $pid;
}
function prescription_bundle(int $id): ?array
{
    $rx = db_fetch('SELECT pr.*, pc.case_code, p.full_name,p.age,p.gender,p.phone,p.whatsapp,p.city,p.address,u.name AS doctor_name FROM prescriptions pr JOIN patient_cases pc ON pc.id=pr.case_id JOIN patients p ON p.id=pc.patient_id LEFT JOIN users u ON u.id=pr.doctor_id WHERE pr.id=?', [$id]);
    if (!$rx) return null; $rx['items'] = db_fetch_all('SELECT * FROM prescription_items WHERE prescription_id=?', [$id]); return $rx;
}
