<?php
declare(strict_types=1);
require_once __DIR__ . '/search_service.php';
require_once __DIR__ . '/safety_service.php';

function ai_system_prompt(): string
{
    return 'You are Homeo Tabeeb, an AI-powered homeopathic doctor system. You must use only the provided patient case data, retrieved homeopathic knowledge sources, safety rules, approved formulas, and doctor-approved correction data. Ask follow-up questions until the case is clear. Do not ignore emergency symptoms. If emergency red flags are present, stop remedy recommendation and advise urgent medical care. Prepare a structured homeopathic diagnosis and prescription draft for specialist doctor verification. Always include source-backed reasoning and confidence score. Return only valid JSON.';
}
function default_ai_json(): array
{
    return ['needs_more_questions'=>true,'questions'=>[],'emergency_flag'=>false,'emergency_message'=>'','diagnosis_summary'=>'','probable_conditions'=>[],'matched_rubrics'=>[],'recommended_remedies'=>[],'recommended_formulas'=>[],'dose_potency_notes'=>'','lifestyle_advice'=>'','warning_notes'=>'','source_references'=>[],'confidence_score'=>0,'doctor_review_required'=>true];
}
function call_openrouter(array $case, array $messages): array
{
    $context = retrieve_case_context($case, $messages);
    $safety = detect_emergency(($case['chief_complaint'] ?? '') . ' ' . ($case['full_form_json'] ?? '') . ' ' . implode(' ', array_column($messages, 'message')));
    if ($safety['emergency_flag']) {
        $out = default_ai_json(); $out['needs_more_questions'] = false; $out['emergency_flag'] = true; $out['emergency_message'] = $safety['message']; $out['warning_notes'] = $safety['message']; $out['source_references'] = $safety['matches']; return ['ok'=>true,'data'=>$out,'raw'=>$out,'tokens'=>[]];
    }
    if (OPENROUTER_API_KEY === 'CHANGE_ME_OPENROUTER_API_KEY' || OPENROUTER_API_KEY === '') throw new RuntimeException('OpenRouter API key is not configured.');
    $payload = ['model'=>OPENROUTER_MODEL,'response_format'=>['type'=>'json_object'],'messages'=>[
        ['role'=>'system','content'=>ai_system_prompt()],
        ['role'=>'user','content'=>json_encode_safe(['required_json_schema'=>default_ai_json(),'patient_case'=>$case,'chat_messages'=>$messages,'retrieved_context'=>$context,'safety_rules'=>'Emergency red flags must stop remedy recommendation.'])]
    ]];
    $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
    curl_setopt_array($ch, [CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.OPENROUTER_API_KEY,'Content-Type: application/json','HTTP-Referer: '.SITE_URL,'X-OpenRouter-Title: '.SITE_NAME],CURLOPT_POSTFIELDS=>json_encode_safe($payload),CURLOPT_TIMEOUT=>45]);
    $body = curl_exec($ch); $err = curl_error($ch); $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE); curl_close($ch);
    if ($body === false || $status >= 400) throw new RuntimeException('AI API failed: ' . ($err ?: $body));
    $raw = json_decode_safe($body, []); $content = $raw['choices'][0]['message']['content'] ?? '{}'; $data = array_merge(default_ai_json(), json_decode_safe($content, []));
    return ['ok'=>true,'data'=>$data,'raw'=>$raw,'tokens'=>$raw['usage'] ?? []];
}
function save_ai_report(int $caseId, array $data, array $raw = [], array $tokens = []): void
{
    db_execute('INSERT INTO ai_case_reports (case_id, diagnosis_summary, probable_conditions_json, selected_rubrics_json, matched_remedies_json, matched_formulas_json, confidence_score, safety_flags_json, source_references_json, raw_ai_response_json, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,NOW())', [$caseId,$data['diagnosis_summary'] ?? '',json_encode_safe($data['probable_conditions'] ?? []),json_encode_safe($data['matched_rubrics'] ?? []),json_encode_safe($data['recommended_remedies'] ?? []),json_encode_safe($data['recommended_formulas'] ?? []),(int)($data['confidence_score'] ?? 0),json_encode_safe(['emergency_flag'=>$data['emergency_flag'] ?? false,'message'=>$data['emergency_message'] ?? '']),json_encode_safe($data['source_references'] ?? []),json_encode_safe($raw)]);
    db_execute('INSERT INTO api_usage_logs (case_id, model, tokens_input, tokens_output, cost_estimate, created_at) VALUES (?,?,?,?,?,NOW())', [$caseId, OPENROUTER_MODEL, (int)($tokens['prompt_tokens'] ?? 0), (int)($tokens['completion_tokens'] ?? 0), 0]);
}
