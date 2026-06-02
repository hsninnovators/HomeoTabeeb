<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

function detect_emergency(string $text): array
{
    $normalized = normalize_text($text);
    $matches = [];
    $rules = db_fetch_all('SELECT * FROM safety_rules WHERE status="active"');
    if (!$rules) {
        $rules = [['rule_key'=>'default_red_flags','trigger_keywords'=>'chest pain,severe breathing difficulty,stroke,unconscious,severe dehydration,pregnancy emergency,severe allergic reaction,suicidal,severe bleeding,high fever confusion,seizure,poisoning,infant not feeding','severity'=>'emergency','instruction'=>'Seek urgent medical care immediately.']];
    }
    foreach ($rules as $rule) {
        foreach (explode(',', (string)$rule['trigger_keywords']) as $kw) {
            $kw = normalize_text($kw);
            if ($kw !== '' && str_contains($normalized, $kw)) { $matches[] = ['rule'=>$rule['rule_key'],'keyword'=>$kw,'severity'=>$rule['severity'],'instruction'=>$rule['instruction']]; }
        }
    }
    return ['emergency_flag'=>count($matches)>0, 'matches'=>$matches, 'message'=>count($matches) ? 'Emergency symptoms may be present. Please seek urgent medical care immediately before using any medicine recommendation.' : ''];
}
