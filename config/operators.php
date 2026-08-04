<?php
declare(strict_types=1);

// SINGLE SOURCE OF TRUTH. Update here only.
//
// ⚠️ VERIFY BEFORE LAUNCH (spec §7.1): per BTRC allocation, Robi and Airtel share
// 016/018, 017/013 are Grameenphone and 019/014 are Banglalink. The marketing copy
// supplied in the brief says Robi = 016/019 and Airtel = 017. Confirm the current
// assignment with the carrier billing provider, then update `map` AND `display_note` together.
return [
    'allowed' => ['robi', 'airtel'],
    'map' => [
        '016' => 'robi',
        '018' => 'robi',     // ex-Airtel block, merged into Robi
        '017' => 'grameenphone',
        '013' => 'grameenphone',
        '019' => 'banglalink',
        '014' => 'banglalink',
        '015' => 'teletalk',
    ],
    // Marketing copy shown to users (kept separate from validation on purpose)
    'display_note' => 'শুধু Robi (016/019) ও Airtel (017) Number',
];
