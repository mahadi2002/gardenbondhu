<?php
declare(strict_types=1);

return [
    'body_parts' => [
        'leaf'   => 'পাতা',
        'stem'   => 'কাণ্ড',
        'root'   => 'শিকড়',
        'flower' => 'ফুল',
        'fruit'  => 'ফল',
        'soil'   => 'মাটি',
        'whole'  => 'পুরো গাছ',
    ],
    'sunlight'   => ['full' => 'পূর্ণ রোদ', 'partial' => 'আংশিক রোদ', 'shade' => 'ছায়া'],
    'water'      => ['low' => 'কম পানি', 'medium' => 'মাঝারি পানি', 'high' => 'বেশি পানি'],
    'difficulty' => ['easy' => 'সহজ', 'medium' => 'মাঝারি', 'hard' => 'কঠিন'],
    'space'      => ['balcony' => 'বারান্দা', 'rooftop' => 'ছাদ', 'indoor' => 'ঘরের ভেতর', 'yard' => 'উঠান', 'pot' => 'টব'],
    'severity'   => ['low' => 'কম', 'medium' => 'মাঝারি', 'high' => 'বেশি'],
    'growth_habit' => [
        'herb' => 'বীরুৎ', 'shrub' => 'গুল্ম', 'tree' => 'বৃক্ষ',
        'climber' => 'লতা', 'succulent' => 'সাকুলেন্ট', 'grass' => 'ঘাস',
    ],
    'problem_type' => [
        'pest' => 'পোকা', 'fungal' => 'ছত্রাক', 'bacterial' => 'ব্যাকটেরিয়া', 'viral' => 'ভাইরাস',
        'nutrient' => 'পুষ্টির ঘাটতি', 'cultural' => 'পরিচর্যার ভুল', 'environmental' => 'আবহাওয়া',
    ],
    'guide_category' => [
        'basic' => 'শুরুর পাঠ', 'soil' => 'মাটি', 'water' => 'পানি', 'fertilizer' => 'সার',
        'pest' => 'পোকা ও রোগ', 'propagation' => 'চারা তৈরি', 'tools' => 'যন্ত্রপাতি',
        'rooftop' => 'ছাদবাগান', 'indoor' => 'ইনডোর', 'season' => 'ঋতু',
    ],
    'care_task' => [
        'water' => 'পানি দিন', 'fertilize' => 'সার দিন', 'prune' => 'ছাঁটাই করুন',
        'repot' => 'টব বদলান', 'spray' => 'স্প্রে করুন', 'check' => 'দেখে নিন',
    ],
    'activity' => [
        'sow' => 'বীজ বপন', 'plant' => 'চারা রোপণ', 'fertilize' => 'সার',
        'prune' => 'ছাঁটাই', 'harvest' => 'ফসল', 'repot' => 'টব বদল', 'protect' => 'সুরক্ষা',
    ],
    'sortable' => [  // SQL ORDER BY allowlist — never accept raw user input
        'plants'   => ['name_bn', 'difficulty', 'created_at', 'view_count'],
        'problems' => ['name_bn', 'severity', 'type'],
        'guides'   => ['published_at', 'title_bn', 'view_count'],
    ],
];
