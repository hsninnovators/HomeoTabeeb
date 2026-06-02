<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

function extract_keywords(string $text): array
{
    $words = preg_split('/[^\p{L}\p{N}]+/u', normalize_text($text));
    $stop = array_flip(['the','and','for','with','hai','hey','mera','meri','pain','since','from','very','please','میں','ہے','اور']);
    $out = [];
    foreach ($words ?: [] as $w) if (mb_strlen($w) >= 3 && !isset($stop[$w])) $out[$w] = true;
    return array_slice(array_keys($out), 0, 12);
}
function like_search(string $table, string $field, string $label, string $text, int $limit = 5): array
{
    $keywords = extract_keywords($text); if (!$keywords) return [];
    $clauses = []; $params = [];
    foreach ($keywords as $kw) { $clauses[] = "$field LIKE ?"; $params[] = '%' . $kw . '%'; }
    $sql = "SELECT *, '$label' AS source_table FROM $table WHERE " . implode(' OR ', $clauses) . " LIMIT $limit";
    try { return db_fetch_all($sql, $params); } catch (Throwable $e) { error_log('search failed: '.$e->getMessage()); return []; }
}
function retrieve_case_context(array $case, array $messages = []): array
{
    $form = json_decode_safe($case['full_form_json'] ?? '{}', []);
    $text = ($case['chief_complaint'] ?? '') . ' ' . json_encode_safe($form) . ' ' . implode(' ', array_column($messages, 'message'));
    $context = [];
    foreach (like_search('symptoms_rubrics', 'rubric_text', 'rubric', $text) as $r) $context[] = ['type'=>'rubric','title'=>$r['rubric_text'],'excerpt'=>$r['category'] ?? ''];
    foreach (like_search('remedies', 'remedy_name', 'remedy', $text) as $r) $context[] = ['type'=>'remedy','title'=>$r['remedy_name'],'excerpt'=>$r['normalized_name'] ?? ''];
    foreach (like_search('remedy_aliases', 'alias_name', 'remedy_alias', $text) as $r) $context[] = ['type'=>'remedy_alias','title'=>$r['alias_name'],'excerpt'=>$r['language'] ?? ''];
    foreach (like_search('materia_medica_pages', 'full_text', 'materia_medica', $text, 4) as $r) $context[] = ['type'=>'materia_medica','title'=>($r['remedy_name'] ?? '').' '.$r['title'],'excerpt'=>excerpt($r['full_text'] ?? '')];
    foreach (like_search('extra_sections', 'section_text', 'extra_section', $text, 4) as $r) $context[] = ['type'=>'extra_section','title'=>$r['section_title'],'excerpt'=>excerpt($r['section_text'] ?? '')];
    foreach (like_search('formula_candidates', 'formula_text', 'formula_candidate', $text, 4) as $r) $context[] = ['type'=>'formula_candidate','title'=>$r['formula_name'],'excerpt'=>excerpt(($r['condition_text'] ?? '').' '.$r['formula_text'].' '.$r['dose_text'])];
    foreach (like_search('approved_formulas', 'condition_text', 'approved_formula', $text, 4) as $r) $context[] = ['type'=>'approved_formula','title'=>$r['formula_name'],'excerpt'=>excerpt(($r['condition_text'] ?? '').' '.$r['dose_text'])];
    foreach (like_search('ai_corrections', 'corrected_value', 'doctor_correction', $text, 4) as $r) if ((int)$r['approved_for_learning'] === 1) $context[] = ['type'=>'doctor_correction','title'=>$r['correction_type'],'excerpt'=>excerpt(($r['corrected_value'] ?? '').' Reason: '.($r['reason'] ?? ''))];
    return array_slice($context, 0, 24);
}
