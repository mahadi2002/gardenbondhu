<?php
declare(strict_types=1);

/**
 * Starter content set — run by database/migrate.php --seed after the SQL seeds.
 *
 * This is a launch-ready STARTER set, not the full spec §12 target (60 plants /
 * 40 problems / 20 guides). It covers the 20 beginner plants named in the brief
 * plus the most common problems and guides, written in plain conversational
 * Bangla (আপনি-form), so the product is genuinely usable and demoable today.
 * Growing this to the full minimum is tracked in PROGRESS.md as the next
 * content batch — spec §12 calls it correctly: this is the real bottleneck,
 * not the code, and it is best done by a native speaker editing AI drafts,
 * not by generating all 60 unattended.
 */

use App\Core\Db;

/** @var \PDO $pdo defined by migrate.php before this file is required */

fwrite(STDOUT, "  seeding plants…\n");

$categorySlugToId = [];
foreach (Db::all('SELECT id, slug FROM plant_categories') as $row) {
    $categorySlugToId[$row['slug']] = (int) $row['id'];
}

$plants = [
    [
        'slug' => 'pudina', 'name_bn' => 'পুদিনা', 'name_en' => 'Mint', 'scientific_name' => 'Mentha spicata',
        'category' => 'moshla', 'difficulty' => 'easy', 'space_type' => 'balcony,indoor,pot',
        'sunlight' => 'partial', 'water_need' => 'high', 'growth_habit' => 'herb',
        'pot_size_cm' => '২০-২৫ সেমি চওড়া, অগভীর', 'water_interval_days' => 2, 'fertilizer_interval_days' => 21,
        'summary_bn' => 'পুদিনা সবচেয়ে সহজ গাছগুলোর একটা — একবার লাগালে বারবার কাটতে পারবেন, গাছ নিজে থেকেই ছড়িয়ে যায়। রান্নায়, শরবতে রোজ লাগে বলে বারান্দায় একটা টব রাখলে বাজার থেকে কেনা লাগে না।',
        'body_bn' => "## মাটি ও টব\nদোআঁশ মাটির সাথে কম্পোস্ট মিশিয়ে দিন। পুদিনার শিকড় মাটির উপরের দিকে ছড়ায়, তাই গভীর টবের চেয়ে চওড়া, অগভীর টব ভালো — ২০-২৫ সেমি চওড়া হলেই যথেষ্ট।\n\n## পানি\nমাটি সবসময় একটু ভেজা রাখুন, কিন্তু পানি জমতে দেবেন না। গরমকালে প্রতিদিন, শীতে দুই-তিন দিন পরপর পানি দিন।\n\n## রোদ\nসকালের হালকা রোদ যথেষ্ট। পুরো দিন কড়া রোদে পাতা পুড়ে যেতে পারে।\n\n## সার\nপ্রতি তিন সপ্তাহে হালকা তরল সার বা কেঁচো সার দিন। বেশি সার দিলে পাতার স্বাদ কমে যায়।\n\n## ছাঁটাই\nনিয়মিত পাতা তুলুন — যত কাটবেন, গাছ তত ঘন হবে। ফুল আসার আগেই কাণ্ড ছেঁটে দিন, নাহলে পাতা তেতো হয়ে যায়।\n\n## সাধারণ সমস্যা\nবেশি পানিতে গোড়া পচা আর পাতায় মরিচা রোগ সবচেয়ে বেশি দেখা যায়।",
        'toxic_to_pets' => 0,
    ],
    [
        'slug' => 'dhonepata', 'name_bn' => 'ধনেপাতা', 'name_en' => 'Coriander', 'scientific_name' => 'Coriandrum sativum',
        'category' => 'moshla', 'difficulty' => 'easy', 'space_type' => 'balcony,rooftop,pot',
        'sunlight' => 'partial', 'water_need' => 'medium', 'growth_habit' => 'herb',
        'pot_size_cm' => '২৫-৩০ সেমি', 'water_interval_days' => 3, 'fertilizer_interval_days' => 25, 'harvest_days' => 35,
        'summary_bn' => 'ধনেপাতা বীজ থেকে দ্রুত বড় হয় — ৩৫ দিনেই কাটার মতো হয়ে যায়। গরমে তাড়াতাড়ি ফুল চলে আসে বলে শীতকালে লাগানোই সবচেয়ে ভালো ফল দেয়।',
        'body_bn' => "## মাটি ও টব\nঝুরঝুরে, পানি নিষ্কাশনযোগ্য মাটি দরকার। ২৫-৩০ সেমি টব যথেষ্ট, তবে শিকড় লম্বা হয় বলে গভীরতা ২০ সেমির কম না হওয়াই ভালো।\n\n## পানি\nমাটি শুকিয়ে গেলে পানি দিন, তবে জমতে দেবেন না। অতিরিক্ত পানি বা অতিরিক্ত গরমে গাছ দ্রুত ফুলে চলে যায়।\n\n## রোদ\nআংশিক রোদ সবচেয়ে ভালো। কড়া গরমে পাতা পুড়ে যায় আর গাছ তাড়াতাড়ি বীজে চলে যায়।\n\n## সার\nপ্রতি ২৫ দিনে হালকা নাইট্রোজেন-সমৃদ্ধ সার দিলে পাতা ঘন সবুজ হয়।\n\n## রোপণ কৌশল\nপ্রতি ১০-১৫ দিন পরপর নতুন বীজ ছিটিয়ে দিন — এতে সবসময় তাজা পাতা পাবেন, একসাথে সব ফুরিয়ে যাবে না।\n\n## সাধারণ সমস্যা\nজাব পোকা আর অতিরিক্ত পানিতে গোড়া পচা — এই দুটোই সবচেয়ে বেশি দেখা যায়।",
        'toxic_to_pets' => 0,
    ],
    [
        'slug' => 'moric', 'name_bn' => 'মরিচ', 'name_en' => 'Chili Pepper', 'scientific_name' => 'Capsicum annuum',
        'category' => 'sobji', 'difficulty' => 'easy', 'space_type' => 'balcony,rooftop,yard,pot',
        'sunlight' => 'full', 'water_need' => 'medium', 'growth_habit' => 'shrub',
        'pot_size_cm' => '৩০-৩৫ সেমি', 'water_interval_days' => 3, 'fertilizer_interval_days' => 20, 'harvest_days' => 75,
        'summary_bn' => 'মরিচ গাছ একবার ফল দেওয়া শুরু করলে মাসের পর মাস ফল দিতে থাকে। রোদ পেলে আর নিয়মিত ছোট ছোট সার পেলে একটা টবেই সংসারের মরিচের চাহিদা মিটে যায়।',
        'body_bn' => "## মাটি ও টব\nদোআঁশ মাটিতে কম্পোস্ট মিশিয়ে ৩০-৩৫ সেমি টবে লাগান। নিচে ভালো নিষ্কাশনের ছিদ্র থাকা জরুরি — পানি জমলে শিকড় পচে যায়।\n\n## পানি\nমাটির উপরের ১ ইঞ্চি শুকিয়ে গেলে পানি দিন। ফুল-ফল আসার সময় পানির ঘাটতি হলে ফুল ঝরে যায়।\n\n## রোদ\nদিনে অন্তত ৬ ঘণ্টা পূর্ণ রোদ দরকার — কম রোদে গাছ বাড়ে কিন্তু ফল কম দেয়।\n\n## সার\nপ্রতি ২০ দিনে NPK সার, ফুল আসার সময় ফসফরাস-সমৃদ্ধ সার বেশি ফল দিতে সাহায্য করে।\n\n## ছাঁটাই\nপ্রথম দিকের ফুল ফেলে দিলে গাছ আগে ঝোপালো হয়, পরে বেশি ফল দেয়।\n\n## সাধারণ সমস্যা\nসাদা মাছি, জাব পোকা আর ফুল ঝরে যাওয়া — এই তিনটে লক্ষণ সবচেয়ে বেশি দেখা যায়।",
        'toxic_to_pets' => 1,
    ],
    [
        'slug' => 'tomato', 'name_bn' => 'টমেটো', 'name_en' => 'Tomato', 'scientific_name' => 'Solanum lycopersicum',
        'category' => 'sobji', 'difficulty' => 'medium', 'space_type' => 'rooftop,yard,pot',
        'sunlight' => 'full', 'water_need' => 'medium', 'growth_habit' => 'shrub',
        'pot_size_cm' => '৪০-৪৫ সেমি', 'water_interval_days' => 2, 'fertilizer_interval_days' => 15, 'harvest_days' => 80,
        'summary_bn' => 'টমেটো একটু যত্ন চায়, কিন্তু বদলে দেয় প্রচুর ফল। বড় টব, নিয়মিত পানি আর একটা সাপোর্ট স্টিক থাকলে বাসার ছাদেই ভালো ফলন পাওয়া যায়।',
        'body_bn' => "## মাটি ও টব\nবড় টব (৪০ সেমি+) লাগবে — শিকড় অনেক দূর ছড়ায়। মাটিতে কম্পোস্ট আর কিছু বালি মিশিয়ে নিষ্কাশন ভালো করে নিন।\n\n## পানি\nনিয়মিত সমান পরিমাণে পানি দিন। অনিয়মিত পানিতে ফল ফেটে যায়।\n\n## রোদ\nপূর্ণ রোদ, দিনে ৬-৮ ঘণ্টা।\n\n## সার\nপ্রতি ১৫ দিনে সার দিন। ফুল আসার সময় ক্যালসিয়াম কম হলে ফলের নিচে কালো দাগ (Blossom End Rot) হতে পারে — চুন-জাতীয় সার বা ডিমের খোসা গুঁড়া করে মিশিয়ে দিন।\n\n## সাপোর্ট\nগাছ বড় হলে একটা কাঠি বা খাঁচা দিয়ে বেঁধে দিন, নাহলে ফলের ভারে ডাল ভেঙে যায়।\n\n## সাধারণ সমস্যা\nফল ফেটে যাওয়া, পাতায় কালো দাগ (আর্লি ব্লাইট) আর সাদা মাছি — এই তিনটে সবচেয়ে সাধারণ।",
        'toxic_to_pets' => 1,
    ],
    [
        'slug' => 'lebu', 'name_bn' => 'লেবু', 'name_en' => 'Lemon', 'scientific_name' => 'Citrus limon',
        'category' => 'fol', 'difficulty' => 'medium', 'space_type' => 'rooftop,yard,pot',
        'sunlight' => 'full', 'water_need' => 'medium', 'growth_habit' => 'tree',
        'pot_size_cm' => '৪৫-৫০ সেমি', 'water_interval_days' => 3, 'fertilizer_interval_days' => 30, 'harvest_days' => 270,
        'summary_bn' => 'লেবু গাছ বড় টবে বছরের পর বছর ফল দেয়। শুরুতে একটু ধৈর্য লাগে — প্রথম ফল পেতে দেড়-দুই বছর লাগতে পারে — কিন্তু এরপর নিয়মিত যত্নেই চলতে থাকে।',
        'body_bn' => "## মাটি ও টব\nবড়, গভীর টব দরকার। মাটি একটু অ্যাসিডিক (pH ৫.৫-৬.৫) পছন্দ করে — কম্পোস্টের সাথে সামান্য গোবর সার মিশিয়ে দিন।\n\n## পানি\nমাটি শুকিয়ে গেলে গভীর করে পানি দিন। শিকড়ের কাছে পানি জমতে দেবেন না।\n\n## রোদ\nপূর্ণ রোদ প্রয়োজন, দিনে অন্তত ৬ ঘণ্টা।\n\n## সার\nমাসে একবার সাইট্রাস-উপযোগী সার (নাইট্রোজেন বেশি) দিন। শীতে সার কমিয়ে দিন, গাছ তখন বিশ্রামে থাকে।\n\n## ছাঁটাই\nবছরে একবার শুকনো বা ভেতরের দিকে বাড়া ডাল ছেঁটে দিন, যাতে আলো-বাতাস ভেতরে ঢোকে।\n\n## সাধারণ সমস্যা\nপাতা হলুদ হওয়া (পুষ্টির ঘাটতি), লাল মাকড় আর মিলিবাগ — এই তিনটে বেশি দেখা যায়।",
        'toxic_to_pets' => 1,
    ],
    [
        'slug' => 'golap', 'name_bn' => 'গোলাপ', 'name_en' => 'Rose', 'scientific_name' => 'Rosa spp.',
        'category' => 'ful', 'difficulty' => 'medium', 'space_type' => 'rooftop,yard,pot',
        'sunlight' => 'full', 'water_need' => 'medium', 'growth_habit' => 'shrub',
        'pot_size_cm' => '৩৫-৪০ সেমি', 'water_interval_days' => 3, 'fertilizer_interval_days' => 20,
        'summary_bn' => 'গোলাপ নিয়মিত ছাঁটাই আর সঠিক সার পেলে বছরজুড়ে ফুল দেয়। শুরুতে অনেকেই ভয় পান, কিন্তু নিয়ম জানা থাকলে যত্নটা আসলে সহজ।',
        'body_bn' => "## মাটি ও টব\nদোআঁশ মাটিতে প্রচুর জৈব সার মিশিয়ে দিন। ৩৫-৪০ সেমি টবে ভালো বাড়ে।\n\n## পানি\nগোড়ায় পানি দিন, পাতায় নয় — পাতা ভিজলে ছত্রাক রোগ বাড়ে। মাটি শুকিয়ে গেলে পানি দিন।\n\n## রোদ\nপূর্ণ রোদ চাই, দিনে অন্তত ৬ ঘণ্টা। ছায়ায় ফুল কম আসে।\n\n## সার\nপ্রতি ২০ দিনে সার দিন। ফুল আসার সময় পটাশ-সমৃদ্ধ সার ফুলের রং আর আকার ভালো করে।\n\n## ছাঁটাই\nপ্রতিটা ফুল শুকিয়ে গেলে বোঁটাসহ কেটে দিন — এতে নতুন ফুল দ্রুত আসে। শীতের শেষে বড় করে ছাঁটাই করুন।\n\n## সাধারণ সমস্যা\nপাতায় কালো দাগ, পাউডারি মিলডিউ আর জাব পোকা সবচেয়ে বেশি দেখা যায়।",
        'toxic_to_pets' => 0,
    ],
    [
        'slug' => 'gada', 'name_bn' => 'গাঁদা', 'name_en' => 'Marigold', 'scientific_name' => 'Tagetes',
        'category' => 'ful', 'difficulty' => 'easy', 'space_type' => 'balcony,rooftop,yard,pot',
        'sunlight' => 'full', 'water_need' => 'low', 'growth_habit' => 'herb',
        'pot_size_cm' => '২০-২৫ সেমি', 'water_interval_days' => 4, 'fertilizer_interval_days' => 25, 'harvest_days' => 50,
        'summary_bn' => 'গাঁদা নতুন বাগানিদের জন্য সবচেয়ে নিরাপদ শুরু — বীজ থেকে দ্রুত ফুল আসে, রোগবালাই কম, আর ভুল হলেও সহজে টিকে যায়।',
        'body_bn' => "## মাটি ও টব\nযেকোনো সাধারণ দোআঁশ মাটিতেই ভালো হয়, খুব বেশি খুঁতখুঁতে না। ২০-২৫ সেমি টব যথেষ্ট।\n\n## পানি\nকম পানিতেই ভালো থাকে। মাটি শুকনো লাগলেই পানি দিন — বেশি পানি এর সবচেয়ে বড় শত্রু।\n\n## রোদ\nপূর্ণ রোদ পছন্দ করে। ছায়ায় গাছ লম্বা-দুর্বল হয়ে যায়, ফুল কম আসে।\n\n## সার\nহালকা সার দিলেই যথেষ্ট, বেশি সারে পাতা বেশি হয় ফুল কম হয়।\n\n## ফুল ধরে রাখা\nশুকনো ফুল নিয়মিত তুলে ফেলুন — নতুন ফুল আসতে সাহায্য করে।\n\n## সাধারণ সমস্যা\nবেশি পানিতে গোড়া পচা ছাড়া তেমন সমস্যা হয় না। মাঝেমধ্যে জাব পোকা আসতে পারে।",
        'toxic_to_pets' => 0,
    ],
    [
        'slug' => 'joba', 'name_bn' => 'জবা', 'name_en' => 'Hibiscus', 'scientific_name' => 'Hibiscus rosa-sinensis',
        'category' => 'ful', 'difficulty' => 'easy', 'space_type' => 'rooftop,yard,pot',
        'sunlight' => 'full', 'water_need' => 'medium', 'growth_habit' => 'shrub',
        'pot_size_cm' => '৩৫-৪০ সেমি', 'water_interval_days' => 3, 'fertilizer_interval_days' => 20,
        'summary_bn' => 'জবা আমাদের দেশের আবহাওয়ায় সবচেয়ে সহজে টিকে থাকা ফুল গাছগুলোর একটা। একটু রোদ আর নিয়মিত পানি পেলে প্রায় সারাবছরই ফুল দেয়।',
        'body_bn' => "## মাটি ও টব\nদোআঁশ মাটিতে কম্পোস্ট মিশিয়ে ৩৫-৪০ সেমি টবে লাগান।\n\n## পানি\nমাটি শুকিয়ে গেলে পানি দিন। গরমে বেশি ঘনঘন পানি লাগে।\n\n## রোদ\nপূর্ণ রোদ চাই — যত বেশি রোদ, তত বেশি ফুল।\n\n## সার\nপ্রতি ২০ দিনে পটাশ-সমৃদ্ধ সার দিলে ফুল বেশি আসে।\n\n## ছাঁটাই\nবছরে অন্তত একবার ডাল ছেঁটে দিন — নতুন ডালেই বেশি ফুল আসে।\n\n## সাধারণ সমস্যা\nমিলিবাগ আর পাতা কুঁকড়ে যাওয়া রোগ সবচেয়ে বেশি দেখা যায়।",
        'toxic_to_pets' => 0,
    ],
    [
        'slug' => 'money-plant', 'name_bn' => 'মানিপ্ল্যান্ট', 'name_en' => 'Money Plant', 'scientific_name' => 'Epipremnum aureum',
        'category' => 'indoor', 'difficulty' => 'easy', 'space_type' => 'indoor,pot',
        'sunlight' => 'shade', 'water_need' => 'low', 'growth_habit' => 'climber',
        'pot_size_cm' => '১৫-২০ সেমি (মাটি) বা যেকোনো পানির পাত্র', 'water_interval_days' => 7, 'fertilizer_interval_days' => 45,
        'summary_bn' => 'মানিপ্ল্যান্ট মাটি ছাড়া শুধু পানিতেও বেঁচে থাকে — এজন্যই নতুনদের প্রথম গাছ হিসেবে এত জনপ্রিয়। ভুল করে ভুলে গেলেও সহজে মরে না।',
        'body_bn' => "## মাটি না পানি\nদুই ভাবেই রাখা যায়। পানিতে রাখলে সপ্তাহে একবার পানি বদলান। মাটিতে রাখলে সাধারণ পটিং মিক্স যথেষ্ট।\n\n## পানি\nমাটিতে থাকলে উপরের মাটি শুকিয়ে গেলে পানি দিন। পানিতে রাখলে শিকড় সবসময় ডোবানো থাকা ঠিক আছে, শুধু পানি পরিষ্কার রাখুন।\n\n## রোদ\nসরাসরি রোদ লাগবে না — ঘরের ভেতর পরোক্ষ আলোতেই ভালো থাকে। কড়া রোদে পাতা পুড়ে যায়।\n\n## সার\nমাসে একবার হালকা সার যথেষ্ট, না দিলেও চলে।\n\n## যত্নের টিপস\nবাতাস পরিষ্কার রাখতে সাহায্য করে বলে বেডরুম বা অফিস ডেস্কে রাখা জনপ্রিয়। লতা লম্বা হয়ে গেলে কেটে নতুন টবে লাগাতে পারেন — সহজেই নতুন চারা হয়।\n\n## সাধারণ সমস্যা\nবেশি পানি বা কম আলোয় পাতা হলুদ হয়ে যায়। এছাড়া তেমন সমস্যা হয় না।",
        'toxic_to_pets' => 1,
    ],
    [
        'slug' => 'snake-plant', 'name_bn' => 'স্নেক প্ল্যান্ট', 'name_en' => 'Snake Plant', 'scientific_name' => 'Dracaena trifasciata',
        'category' => 'indoor', 'difficulty' => 'easy', 'space_type' => 'indoor,pot',
        'sunlight' => 'shade', 'water_need' => 'low', 'growth_habit' => 'succulent',
        'pot_size_cm' => '২০-২৫ সেমি', 'water_interval_days' => 12, 'fertilizer_interval_days' => 60,
        'summary_bn' => 'স্নেক প্ল্যান্ট সবচেয়ে কষ্টসহিষ্ণু ইনডোর গাছগুলোর একটা — সপ্তাহের পর সপ্তাহ পানি না দিলেও সমস্যা হয় না। যারা গাছ ভুলে যান, তাদের জন্য আদর্শ।',
        'body_bn' => "## মাটি ও টব\nদ্রুত পানি নিষ্কাশন হয় এমন মাটি লাগবে — সাধারণ মাটির সাথে বালি মিশিয়ে নিন। অতিরিক্ত পানি ধরে রাখা মাটি এই গাছের সবচেয়ে বড় শত্রু।\n\n## পানি\nমাটি পুরোপুরি শুকিয়ে গেলে তবেই পানি দিন — সপ্তাহে একবারের বেশি লাগে না, শীতে আরও কম। এই গাছ পানির অভাবে নয়, পানির আধিক্যে মরে।\n\n## রোদ\nকম আলোতেও ভালো থাকে, তবে পরোক্ষ উজ্জ্বল আলোয় সবচেয়ে ভালো বাড়ে।\n\n## সার\nখুব একটা লাগে না — দুই মাসে একবার হালকা সার যথেষ্ট।\n\n## সাধারণ সমস্যা\nগোড়া পচা — প্রায় সবসময় বেশি পানি দেওয়ার কারণে হয়। পাতা নরম হয়ে গেলে বুঝবেন পানি বেশি হয়ে গেছে।",
        'toxic_to_pets' => 1,
    ],
    [
        'slug' => 'aloe-vera', 'name_bn' => 'অ্যালোভেরা', 'name_en' => 'Aloe Vera', 'scientific_name' => 'Aloe vera',
        'category' => 'oushodhi', 'difficulty' => 'easy', 'space_type' => 'balcony,indoor,pot',
        'sunlight' => 'partial', 'water_need' => 'low', 'growth_habit' => 'succulent',
        'pot_size_cm' => '২০-২৫ সেমি', 'water_interval_days' => 10, 'fertilizer_interval_days' => 60,
        'summary_bn' => 'অ্যালোভেরা কম যত্নে টিকে থাকা একটা ভেষজ গাছ, আর পাতা কেটে সরাসরি ব্যবহার করা যায়। বেশি পানি ছাড়া তেমন কোনো সমস্যাই হয় না।',
        'body_bn' => "## মাটি ও টব\nদ্রুত পানি নিষ্কাশনকারী মাটি লাগবে — ক্যাকটাস মিক্স বা সাধারণ মাটির সাথে বালি মিশিয়ে নিন।\n\n## পানি\nমাটি পুরোপুরি শুকিয়ে গেলে তবেই পানি দিন। এই গাছ শুকনো থাকতে পছন্দ করে।\n\n## রোদ\nআংশিক থেকে পূর্ণ রোদ — সরাসরি কড়া রোদে হঠাৎ রাখলে পাতা বাদামি হয়ে যেতে পারে, ধীরে ধীরে অভ্যস্ত করান।\n\n## সার\nবছরে দুই-তিনবার হালকা সার যথেষ্ট।\n\n## ব্যবহার\nপাতা কাটলে ভেতরের জেল রোদে পোড়া চামড়ায় লাগানো যায়। একবারে পুরো পাতা না কেটে বাইরের দিকের পাতা থেকে কাটুন।\n\n## সাধারণ সমস্যা\nবেশি পানিতে গোড়া পচা সবচেয়ে সাধারণ সমস্যা। পাতা পাতলা-নেতিয়ে গেলে বুঝবেন পানি প্রয়োজন।",
        'toxic_to_pets' => 1,
    ],
    [
        'slug' => 'tulsi', 'name_bn' => 'তুলসী', 'name_en' => 'Holy Basil', 'scientific_name' => 'Ocimum tenuiflorum',
        'category' => 'oushodhi', 'difficulty' => 'easy', 'space_type' => 'balcony,rooftop,yard,pot',
        'sunlight' => 'full', 'water_need' => 'medium', 'growth_habit' => 'herb',
        'pot_size_cm' => '২৫-৩০ সেমি', 'water_interval_days' => 3, 'fertilizer_interval_days' => 25,
        'summary_bn' => 'তুলসী প্রায় প্রতিটা বাঙালি বাড়িতেই থাকে — সহজে বাঁচে, ভেষজ গুণ আছে, আর নিয়মিত পাতা তুললে গাছ আরও ঘন হয়।',
        'body_bn' => "## মাটি ও টব\nসাধারণ দোআঁশ মাটিতেই ভালো হয়। ২৫-৩০ সেমি টব যথেষ্ট।\n\n## পানি\nমাটি শুকিয়ে গেলে পানি দিন, তবে পানি জমতে দেবেন না।\n\n## রোদ\nপূর্ণ রোদ পছন্দ করে, দিনে অন্তত ৪-৬ ঘণ্টা।\n\n## সার\nপ্রতি ২৫ দিনে হালকা জৈব সার যথেষ্ট।\n\n## ছাঁটাই\nফুলের মঞ্জরি আসলে কেটে দিন — এতে গাছ বেশিদিন সতেজ থাকে আর পাতা বেশি হয়।\n\n## সাধারণ সমস্যা\nবেশি পানিতে গোড়া পচা আর জাব পোকা মাঝেমধ্যে দেখা যায়। সাধারণত খুব একটা সমস্যা হয় না।",
        'toxic_to_pets' => 0,
    ],
    [
        'slug' => 'pepe', 'name_bn' => 'পেঁপে', 'name_en' => 'Papaya', 'scientific_name' => 'Carica papaya',
        'category' => 'fol', 'difficulty' => 'medium', 'space_type' => 'yard,rooftop',
        'sunlight' => 'full', 'water_need' => 'medium', 'growth_habit' => 'tree',
        'pot_size_cm' => '৫০ সেমি+ বা মাটিতে সরাসরি', 'water_interval_days' => 3, 'fertilizer_interval_days' => 20, 'harvest_days' => 270,
        'summary_bn' => 'পেঁপে দ্রুত বাড়ে আর অল্প জায়গাতেও অনেক ফল দেয়। বড় টব বা সরাসরি মাটিতে লাগালে সবচেয়ে ভালো ফল পাওয়া যায়।',
        'body_bn' => "## মাটি ও জায়গা\nভালো নিষ্কাশনযোগ্য দোআঁশ মাটি দরকার। বড় টব বা সরাসরি উঠানে লাগানো ভালো — শিকড় অনেক দূর ছড়ায়।\n\n## পানি\nনিয়মিত পানি দিন, তবে গোড়ায় পানি জমতে দেবেন না — এই গাছ পানি জমা একদম সহ্য করে না।\n\n## রোদ\nপূর্ণ রোদ চাই।\n\n## সার\nপ্রতি ২০ দিনে সার দিন। ফুল-ফল আসার সময় পটাশ বেশি দিলে ফলন ভালো হয়।\n\n## বিশেষ পরামর্শ\nপেঁপে গাছে পুরুষ ও স্ত্রী ফুল আলাদা হতে পারে — কয়েকটা চারা একসাথে লাগালে ফল ধরার সম্ভাবনা বাড়ে।\n\n## সাধারণ সমস্যা\nগোড়া পচা (বেশি পানিতে) আর পাতায় দাগ পড়া রোগ সবচেয়ে সাধারণ।",
        'toxic_to_pets' => 0,
    ],
    [
        'slug' => 'kola', 'name_bn' => 'কলা', 'name_en' => 'Banana', 'scientific_name' => 'Musa spp.',
        'category' => 'fol', 'difficulty' => 'medium', 'space_type' => 'yard',
        'sunlight' => 'full', 'water_need' => 'high', 'growth_habit' => 'herb',
        'pot_size_cm' => 'বড় জায়গা বা সরাসরি মাটি', 'water_interval_days' => 2, 'fertilizer_interval_days' => 20, 'harvest_days' => 300,
        'summary_bn' => 'কলা গাছের জন্য জায়গা আর পানি বেশি লাগে, কিন্তু একবার ফল দিলে পুরো কাঁদি পাওয়া যায়। ছাদবাগানের চেয়ে উঠানে বেশি ভালো হয়।',
        'body_bn' => "## জায়গা ও মাটি\nবড় টব বা সরাসরি উঠানের মাটি লাগবে। জৈব সারযুক্ত দোআঁশ মাটি সবচেয়ে ভালো।\n\n## পানি\nপ্রচুর পানি লাগে — মাটি সবসময় ভেজা রাখুন, তবে পানি জমিয়ে রাখবেন না।\n\n## রোদ\nপূর্ণ রোদ প্রয়োজন।\n\n## সার\nপ্রতি ২০ দিনে ভারী মাত্রায় সার দিন — কলা গাছ প্রচুর পুষ্টি খায়।\n\n## যত্নের টিপস\nমূল কাণ্ডের চারপাশে যেসব ছোট চারা (সাকার) গজায়, বাড়তিগুলো কেটে ফেলুন — নাহলে মূল গাছ পুষ্টি ভাগ করে দুর্বল হয়ে যায়।\n\n## সাধারণ সমস্যা\nপাতার আগা শুকিয়ে যাওয়া (পানি বা পুষ্টির ঘাটতি) আর মিলিবাগ মাঝেমধ্যে দেখা যায়।",
        'toxic_to_pets' => 0,
    ],
    [
        'slug' => 'begun', 'name_bn' => 'বেগুন', 'name_en' => 'Eggplant', 'scientific_name' => 'Solanum melongena',
        'category' => 'sobji', 'difficulty' => 'medium', 'space_type' => 'rooftop,yard,pot',
        'sunlight' => 'full', 'water_need' => 'medium', 'growth_habit' => 'shrub',
        'pot_size_cm' => '৩৫-৪০ সেমি', 'water_interval_days' => 3, 'fertilizer_interval_days' => 20, 'harvest_days' => 70,
        'summary_bn' => 'বেগুন গাছ নিয়মিত ফল দিতে থাকে যদি সময়মতো ফল কেটে নেওয়া হয়। রোদ আর সারে একটু নজর দিলে দীর্ঘদিন ফলন পাওয়া যায়।',
        'body_bn' => "## মাটি ও টব\nজৈব সারযুক্ত দোআঁশ মাটিতে ৩৫-৪০ সেমি টবে লাগান।\n\n## পানি\nনিয়মিত পানি দিন, মাটি শুকাতে দেবেন না বেশিক্ষণ।\n\n## রোদ\nপূর্ণ রোদ চাই, দিনে অন্তত ৬ ঘণ্টা।\n\n## সার\nপ্রতি ২০ দিনে সার দিন। ফুল আসার সময় ফসফরাস বেশি দিলে ফলন বাড়ে।\n\n## ফসল তোলা\nফল পূর্ণবয়স্ক হওয়ার আগেই (চকচকে থাকতে থাকতে) কেটে নিন — দেরি করলে ফল শক্ত ও বীজবহুল হয়ে যায়, আর গাছও নতুন ফল কম দেয়।\n\n## সাধারণ সমস্যা\nফল ও কাণ্ড ছিদ্রকারী পোকা (Fruit Borer) আর সাদা মাছি সবচেয়ে বড় সমস্যা।",
        'toxic_to_pets' => 1,
    ],
    [
        'slug' => 'lau', 'name_bn' => 'লাউ', 'name_en' => 'Bottle Gourd', 'scientific_name' => 'Lagenaria siceraria',
        'category' => 'sobji', 'difficulty' => 'medium', 'space_type' => 'rooftop,yard',
        'sunlight' => 'full', 'water_need' => 'high', 'growth_habit' => 'climber',
        'pot_size_cm' => '৪০ সেমি+ বড় টব', 'water_interval_days' => 2, 'fertilizer_interval_days' => 18, 'harvest_days' => 90,
        'summary_bn' => 'লাউ গাছের জন্য মাচা বা রেলিং দরকার, লতা অনেক দূর ছড়ায়। জায়গা থাকলে অল্প যত্নেই প্রচুর ফলন দেয়।',
        'body_bn' => "## মাটি ও জায়গা\nবড় টব বা সরাসরি মাটিতে লাগান। জৈব সারযুক্ত মাটি আর মাচা করার মতো জায়গা লাগবে।\n\n## পানি\nপ্রচুর পানি লাগে, বিশেষ করে ফুল-ফল আসার সময়।\n\n## রোদ\nপূর্ণ রোদ প্রয়োজন।\n\n## সার\nপ্রতি ১৮ দিনে সার দিন — লতাজাতীয় সবজি প্রচুর পুষ্টি টানে।\n\n## মাচা\nলতা মাটিতে ছড়াতে দিলে ফল মাটি ছুঁয়ে পচে যেতে পারে — মাচা বা রেলিং দিয়ে উপরে তুলে দিন।\n\n## সাধারণ সমস্যা\nপাউডারি মিলডিউ (সাদা গুঁড়া রোগ) আর লাল মাকড় গরমকালে বেশি দেখা যায়।",
        'toxic_to_pets' => 0,
    ],
    [
        'slug' => 'shosha', 'name_bn' => 'শসা', 'name_en' => 'Cucumber', 'scientific_name' => 'Cucumis sativus',
        'category' => 'sobji', 'difficulty' => 'medium', 'space_type' => 'rooftop,yard,pot',
        'sunlight' => 'full', 'water_need' => 'high', 'growth_habit' => 'climber',
        'pot_size_cm' => '৩৫-৪০ সেমি', 'water_interval_days' => 2, 'fertilizer_interval_days' => 15, 'harvest_days' => 55,
        'summary_bn' => 'শসা দ্রুত বাড়ে আর মাত্র দুই মাসেই ফল দেওয়া শুরু করে। নিয়মিত পানি এই গাছের সবচেয়ে বড় চাহিদা।',
        'body_bn' => "## মাটি ও টব\nজৈব সারযুক্ত মাটিতে ৩৫-৪০ সেমি টবে লাগান, ছোট মাচা দিলে ভালো হয়।\n\n## পানি\nনিয়মিত ও পর্যাপ্ত পানি দরকার — পানির ঘাটতিতে ফল তেতো হয়ে যায়।\n\n## রোদ\nপূর্ণ রোদ চাই।\n\n## সার\nপ্রতি ১৫ দিনে সার দিন, ফুল আসার সময় পটাশ বাড়িয়ে দিন।\n\n## ফসল তোলা\nফল বেশি বড় হওয়ার আগেই কেটে নিন — এতে গাছ নতুন ফল দিতে উৎসাহিত হয়।\n\n## সাধারণ সমস্যা\nপাউডারি মিলডিউ আর ফল তেতো হওয়া (পানির অনিয়মে) সবচেয়ে বেশি দেখা যায়।",
        'toxic_to_pets' => 0,
    ],
    [
        'slug' => 'palong', 'name_bn' => 'পালং শাক', 'name_en' => 'Spinach', 'scientific_name' => 'Spinacia oleracea',
        'category' => 'sobji', 'difficulty' => 'easy', 'space_type' => 'balcony,rooftop,pot',
        'sunlight' => 'partial', 'water_need' => 'medium', 'growth_habit' => 'herb',
        'pot_size_cm' => '২৫-৩০ সেমি, অগভীর', 'water_interval_days' => 2, 'fertilizer_interval_days' => 18, 'harvest_days' => 30,
        'summary_bn' => 'পালং শাক শীতকালীন সবজি হিসেবে সবচেয়ে সহজ — মাত্র একমাসেই কাটার মতো হয়ে যায়, আর বারবার পাতা তোলা যায়।',
        'body_bn' => "## মাটি ও টব\nঝুরঝুরে দোআঁশ মাটি, অগভীর কিন্তু চওড়া টবে ভালো হয়।\n\n## পানি\nমাটি হালকা ভেজা রাখুন, তবে পানি জমতে দেবেন না।\n\n## রোদ\nআংশিক রোদ যথেষ্ট, শীতকালে পূর্ণ রোদেও ভালো হয়।\n\n## সার\nপ্রতি ১৮ দিনে হালকা নাইট্রোজেন-সমৃদ্ধ সার দিলে পাতা ঘন সবুজ হয়।\n\n## ফসল তোলা\nবাইরের বড় পাতাগুলো কেটে নিন, মাঝেরটা রেখে দিন — গাছ আবার নতুন পাতা দেবে।\n\n## সাধারণ সমস্যা\nপাতায় সাদা দাগ (ছত্রাক) আর জাব পোকা মাঝেমধ্যে দেখা যায়।",
        'toxic_to_pets' => 0,
    ],
    [
        'slug' => 'dherosh', 'name_bn' => 'ঢেঁড়স', 'name_en' => 'Okra', 'scientific_name' => 'Abelmoschus esculentus',
        'category' => 'sobji', 'difficulty' => 'easy', 'space_type' => 'rooftop,yard,pot',
        'sunlight' => 'full', 'water_need' => 'medium', 'growth_habit' => 'shrub',
        'pot_size_cm' => '৩০-৩৫ সেমি', 'water_interval_days' => 3, 'fertilizer_interval_days' => 20, 'harvest_days' => 60,
        'summary_bn' => 'ঢেঁড়স গরমকালে সবচেয়ে সহজে বেড়ে ওঠা সবজিগুলোর একটা। কম যত্নেও নিয়মিত ফলন দেয়।',
        'body_bn' => "## মাটি ও টব\nসাধারণ দোআঁশ মাটিতেই ভালো হয়। ৩০-৩৫ সেমি টব যথেষ্ট।\n\n## পানি\nমাটি শুকিয়ে গেলে পানি দিন। গরমকালে একটু বেশি পানি লাগে।\n\n## রোদ\nপূর্ণ রোদ পছন্দ করে, গরম সহ্য করার ক্ষমতা বেশি।\n\n## সার\nপ্রতি ২০ দিনে সার দিন।\n\n## ফসল তোলা\nফল ছোট-নরম থাকতেই কাটুন (৩-৪ ইঞ্চি) — বড় হয়ে গেলে শক্ত-আঁশযুক্ত হয়ে যায়।\n\n## সাধারণ সমস্যা\nপাতায় হলুদ শিরা রোগ (ভাইরাসঘটিত) আর জাব পোকা সবচেয়ে সাধারণ।",
        'toxic_to_pets' => 0,
    ],
    [
        'slug' => 'patharkuchi', 'name_bn' => 'পাথরকুচি', 'name_en' => 'Life Plant', 'scientific_name' => 'Kalanchoe pinnata',
        'category' => 'oushodhi', 'difficulty' => 'easy', 'space_type' => 'balcony,indoor,pot',
        'sunlight' => 'partial', 'water_need' => 'low', 'growth_habit' => 'succulent',
        'pot_size_cm' => '১৫-২০ সেমি', 'water_interval_days' => 8, 'fertilizer_interval_days' => 45,
        'summary_bn' => 'পাথরকুচি প্রায় নিজে নিজেই বেড়ে ওঠে — পাতার কিনারা থেকেই নতুন ছোট চারা গজায়। যত্ন প্রায় লাগেই না বললে চলে।',
        'body_bn' => "## মাটি ও টব\nদ্রুত পানি নিষ্কাশনকারী মাটি, ছোট টবেই ভালো হয়।\n\n## পানি\nমাটি শুকিয়ে গেলে তবেই পানি দিন — এই গাছও অতিরিক্ত পানিতে দ্রুত পচে যায়।\n\n## রোদ\nআংশিক রোদ বা উজ্জ্বল পরোক্ষ আলোতেই ভালো থাকে।\n\n## সার\nতেমন লাগে না, বছরে একবার-দুইবার হালকা সার যথেষ্ট।\n\n## নতুন চারা\nপাতার কিনারায় যে ছোট ছোট চারা গজায়, সেগুলো আলাদা করে অন্য টবে বসিয়ে দিলেই নতুন গাছ হয়ে যায়।\n\n## সাধারণ সমস্যা\nবেশি পানিতে পাতা নরম হয়ে পচে যাওয়া ছাড়া তেমন সমস্যা হয় না।",
        'toxic_to_pets' => 0,
    ],
];

$plantIds = [];
foreach ($plants as $p) {
    $exists = Db::value('SELECT id FROM plants WHERE slug = ?', [$p['slug']]);
    if ($exists) {
        $plantIds[$p['slug']] = (int) $exists;
        continue;
    }

    $id = Db::insert(
        'INSERT INTO plants (slug, name_bn, name_en, scientific_name, category_id, difficulty, space_type,
            sunlight, water_need, growth_habit, pot_size_cm, water_interval_days, fertilizer_interval_days,
            harvest_days, toxic_to_pets, summary_bn, body_bn, is_published)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)',
        [
            $p['slug'], $p['name_bn'], $p['name_en'], $p['scientific_name'],
            $categorySlugToId[$p['category']] ?? null, $p['difficulty'], $p['space_type'],
            $p['sunlight'], $p['water_need'], $p['growth_habit'], $p['pot_size_cm'],
            $p['water_interval_days'], $p['fertilizer_interval_days'] ?? null,
            $p['harvest_days'] ?? null, $p['toxic_to_pets'], $p['summary_bn'], $p['body_bn'],
        ]
    );
    $plantIds[$p['slug']] = $id;
}

fwrite(STDOUT, '  ' . count($plantIds) . " plants ready\n");

// ── Problems ──────────────────────────────────────────────────────────

fwrite(STDOUT, "  seeding problems…\n");

$symptomBySlug = [];
foreach (Db::all('SELECT id, slug FROM symptoms') as $row) {
    $symptomBySlug[$row['slug']] = (int) $row['id'];
}

$problems = [
    [
        'slug' => 'mealybug', 'name_bn' => 'মিলিবাগ', 'name_en' => 'Mealybug', 'type' => 'pest', 'severity' => 'medium',
        'description_bn' => 'মিলিবাগ ছোট সাদা তুলার মতো পোকা, সাধারণত পাতার গোড়ায় আর কাণ্ডের ফাঁকে জমে থাকে। গাছের রস চুষে খায়, ফলে গাছ দুর্বল হয়ে যায়।',
        'identification_bn' => 'পাতার নিচে বা কাণ্ডের জোড়ায় সাদা তুলার মতো দলা দেখা যায়। ছুঁয়ে দেখলে নরম, স্পঞ্জি লাগে।',
        'organic_remedy_bn' => "১. পরিষ্কার কাপড় বা তুলা মদ (isopropyl alcohol) ভিজিয়ে পোকাগুলো মুছে ফেলুন।\n২. নিমতেল পানিতে মিশিয়ে (৫ মিলি প্রতি লিটার) সপ্তাহে দুইবার স্প্রে করুন।\n৩. আক্রান্ত অংশ বেশি হলে সেই ডাল ছেঁটে ফেলে দিন।",
        'chemical_remedy_bn' => 'তীব্র আক্রমণে ইমিডাক্লোপ্রিড-জাতীয় কীটনাশক লেবেলের নির্দেশনা অনুযায়ী মাত্রায় স্প্রে করুন, ৭-১০ দিন পরপর পুনরাবৃত্তি করুন।',
        'prevention_bn' => 'নতুন গাছ আনার আগে ভালো করে দেখে নিন। নিয়মিত গাছ পরিদর্শন করলে শুরুতেই ধরা পড়ে।',
        'safety_note_bn' => 'কীটনাশক স্প্রে করার সময় হাতমোজা পরুন, শিশু-পোষা প্রাণী থেকে দূরে রাখুন।',
        'symptoms' => ['leaf-sticky' => 6, 'stem-white-cotton' => 10, 'whole-slow' => 4],
    ],
    [
        'slug' => 'aphid', 'name_bn' => 'জাব পোকা', 'name_en' => 'Aphid', 'type' => 'pest', 'severity' => 'medium',
        'description_bn' => 'জাব পোকা ছোট, নরম শরীরের পোকা যা দলবেঁধে নতুন পাতা আর কুঁড়িতে আক্রমণ করে, রস চুষে গাছ দুর্বল করে দেয়।',
        'identification_bn' => 'পাতার নিচে বা নতুন কুঁড়িতে সবুজ, কালো বা হলুদ রঙের ছোট ছোট পোকার দল দেখা যায়। পাতা আঠালো হয়ে যেতে পারে।',
        'organic_remedy_bn' => "১. পানির তীব্র ধারা দিয়ে পাতা ধুয়ে ফেলুন — অনেক পোকা ঝরে যাবে।\n২. সাবান পানি (হালকা তরল সাবান + পানি) স্প্রে করুন।\n৩. নিমতেল স্প্রে সপ্তাহে দুইবার ব্যবহার করুন।",
        'chemical_remedy_bn' => 'বেশি আক্রমণে ম্যালাথিয়ন বা ইমিডাক্লোপ্রিড লেবেলের নির্দেশনা মেনে প্রয়োগ করুন।',
        'prevention_bn' => 'গাঁদা ফুল আশেপাশে লাগালে জাব পোকা কম আসে বলে অনেকে মনে করেন। নিয়মিত পরিদর্শন করুন।',
        'safety_note_bn' => 'রাসায়নিক স্প্রে খাওয়ার আগে সবজি থেকে অন্তত এক সপ্তাহ দূরত্ব রাখুন।',
        'symptoms' => ['leaf-sticky' => 8, 'leaf-curl' => 5, 'flower-bud-drop' => 6],
    ],
    [
        'slug' => 'whitefly', 'name_bn' => 'সাদা মাছি', 'name_en' => 'Whitefly', 'type' => 'pest', 'severity' => 'medium',
        'description_bn' => 'সাদা মাছি পাতার নিচে বসে রস চুষে খায় এবং একটা আঠালো পদার্থ (হানিডিউ) ফেলে, যাতে পরে কালো ছত্রাক জমে।',
        'identification_bn' => 'পাতা নাড়ালে ছোট সাদা মাছির ঝাঁক উড়ে যায়। পাতার নিচে ডিম আর নরম শরীরের বাচ্চা দেখা যায়।',
        'organic_remedy_bn' => "১. হলুদ আঠালো ফাঁদ (Yellow Sticky Trap) ব্যবহার করুন।\n২. নিমতেল স্প্রে নিয়মিত করুন।\n৩. পাতার নিচে সাবান পানি স্প্রে করুন।",
        'chemical_remedy_bn' => 'তীব্র আক্রমণে স্পাইরোমেসিফেন বা ইমিডাক্লোপ্রিড লেবেল-নির্দেশিত মাত্রায় স্প্রে করুন।',
        'prevention_bn' => 'গাছের মধ্যে পর্যাপ্ত ফাঁকা রাখুন, বাতাস চলাচল ভালো রাখলে সংক্রমণ কম হয়।',
        'safety_note_bn' => 'স্প্রে করার পর হাত ভালো করে ধুয়ে নিন।',
        'symptoms' => ['leaf-sticky' => 7, 'leaf-yellow' => 5, 'whole-slow' => 4],
    ],
    [
        'slug' => 'red-spider-mite', 'name_bn' => 'লাল মাকড়', 'name_en' => 'Red Spider Mite', 'type' => 'pest', 'severity' => 'high',
        'description_bn' => 'লাল মাকড় অত্যন্ত ছোট, খালি চোখে প্রায় অদৃশ্য, কিন্তু দ্রুত ছড়িয়ে পাতার রস শুষে গাছকে দুর্বল করে দেয়। গরম-শুকনো আবহাওয়ায় বেশি হয়।',
        'identification_bn' => 'পাতার নিচে সূক্ষ্ম মাকড়সার জালের মতো সুতা দেখা যায়। পাতায় ছোট ছোট সাদাটে বা হলদেটে বিন্দু পড়ে।',
        'organic_remedy_bn' => "১. পাতায় নিয়মিত পানি স্প্রে করুন — এই পোকা শুকনো পরিবেশ পছন্দ করে।\n২. নিমতেল স্প্রে সপ্তাহে দুইবার দিন।\n৩. আক্রান্ত পাতা ছিঁড়ে ফেলে দিন।",
        'chemical_remedy_bn' => 'তীব্র আক্রমণে অ্যাবামেকটিন-জাতীয় মাইটনাশক লেবেল অনুযায়ী ব্যবহার করুন — সাধারণ কীটনাশকে এই পোকা মরে না।',
        'prevention_bn' => 'গরমকালে পাতায় নিয়মিত পানি ছিটিয়ে আর্দ্রতা বাড়িয়ে রাখুন।',
        'safety_note_bn' => 'মাইটনাশক স্প্রে করার সময় মুখোশ ব্যবহার করুন।',
        'symptoms' => ['leaf-webbing' => 10, 'leaf-pale' => 6, 'leaf-yellow' => 4],
    ],
    [
        'slug' => 'powdery-mildew', 'name_bn' => 'পাউডারি মিলডিউ', 'name_en' => 'Powdery Mildew', 'type' => 'fungal', 'severity' => 'medium',
        'description_bn' => 'পাউডারি মিলডিউ একটা ছত্রাক রোগ, পাতার উপর সাদা গুঁড়ার মতো আস্তরণ ফেলে। আর্দ্র-গুমোট আবহাওয়ায় দ্রুত ছড়ায়।',
        'identification_bn' => 'পাতার উপরিভাগে ময়দার মতো সাদা গুঁড়া দেখা যায়, ধীরে ধীরে পুরো পাতা ঢেকে ফেলতে পারে।',
        'organic_remedy_bn' => "১. বেকিং সোডা (১ চা চামচ) + তরল সাবান কয়েক ফোঁটা + ১ লিটার পানি মিশিয়ে স্প্রে করুন।\n২. আক্রান্ত পাতা কেটে ফেলে দিন, পাশের গাছে যেন না ছড়ায়।\n৩. পাতায় সরাসরি পানি না দিয়ে গোড়ায় পানি দিন।",
        'chemical_remedy_bn' => 'তীব্র হলে সালফার বা পটাশিয়াম বাইকার্বনেট-ভিত্তিক ছত্রাকনাশক লেবেল অনুযায়ী স্প্রে করুন।',
        'prevention_bn' => 'গাছের মধ্যে পর্যাপ্ত ফাঁকা রেখে বাতাস চলাচল নিশ্চিত করুন।',
        'safety_note_bn' => 'ছত্রাকনাশক স্প্রে করার পরপরই ফসল খাবেন না, লেবেলে বলা অপেক্ষার সময় মেনে চলুন।',
        'symptoms' => ['leaf-white-powder' => 10, 'leaf-yellow' => 3],
    ],
    [
        'slug' => 'root-rot', 'name_bn' => 'শিকড় পচা', 'name_en' => 'Root Rot', 'type' => 'fungal', 'severity' => 'high',
        'description_bn' => 'শিকড় পচা রোগ প্রায় সবসময় বেশি পানি বা খারাপ নিষ্কাশনের কারণে হয়। মাটিতে অক্সিজেন না পৌঁছালে শিকড় পচে যায়, গাছ ধীরে ধীরে নেতিয়ে পড়ে।',
        'identification_bn' => 'গাছ পানি দেওয়ার পরও নেতিয়ে থাকে, মাটি থেকে দুর্গন্ধ আসে। টব থেকে তুলে দেখলে শিকড় বাদামি-কালচে ও নরম।',
        'organic_remedy_bn' => "১. গাছ টব থেকে বের করে পচা শিকড় কেটে ফেলে দিন।\n২. দারুচিনি গুঁড়া ছত্রাকরোধী হিসেবে কাটা অংশে লাগাতে পারেন।\n৩. নতুন, ভালো নিষ্কাশনযোগ্য মাটিতে নতুন টবে বসান।",
        'chemical_remedy_bn' => 'কার্বেন্ডাজিম-জাতীয় ছত্রাকনাশক দিয়ে মাটি ড্রেঞ্চ (ভিজিয়ে) করা যায়, লেবেলের নির্দেশনা মেনে।',
        'prevention_bn' => 'টবের নিচে অবশ্যই ছিদ্র রাখুন, পানি জমতে দেবেন না, মাটি শুকিয়ে গেলে তবেই পানি দিন।',
        'safety_note_bn' => 'বেশি আক্রান্ত গাছ বাঁচানো কঠিন — দ্রুত ব্যবস্থা নিন।',
        'symptoms' => ['root-rot' => 10, 'whole-wilting' => 7, 'soil-smell' => 6, 'leaf-yellow' => 3],
    ],
    [
        'slug' => 'leaf-curl-virus', 'name_bn' => 'পাতা কোঁকড়ানো রোগ', 'name_en' => 'Leaf Curl', 'type' => 'viral', 'severity' => 'high',
        'description_bn' => 'পাতা কোঁকড়ানো রোগ সাধারণত সাদা মাছির মাধ্যমে ছড়ানো ভাইরাসের কারণে হয়, বিশেষ করে মরিচ আর টমেটোতে বেশি দেখা যায়।',
        'identification_bn' => 'নতুন পাতা কুঁচকে, কোঁকড়ে যায়, আকারে ছোট হয়ে যায়। গাছের বৃদ্ধি থেমে যায়।',
        'organic_remedy_bn' => "১. আক্রান্ত গাছ আলাদা করে ফেলুন, অন্য গাছে যেন না ছড়ায়।\n২. সাদা মাছি নিয়ন্ত্রণ করুন — এটাই ভাইরাসের বাহক।\n৩. তীব্র আক্রান্ত গাছ তুলে ফেলে দেওয়াই ভালো।",
        'chemical_remedy_bn' => 'ভাইরাসের সরাসরি কোনো চিকিৎসা নেই — সাদা মাছি দমনে ইমিডাক্লোপ্রিড ব্যবহার করে বাহক পোকা নিয়ন্ত্রণ করুন।',
        'prevention_bn' => 'সাদা মাছি নিয়ন্ত্রণে রাখাই সবচেয়ে বড় প্রতিরোধ। ভাইরাস-প্রতিরোধী জাত ব্যবহার করুন যদি পাওয়া যায়।',
        'safety_note_bn' => 'আক্রান্ত গাছের অংশ কম্পোস্টে দেবেন না, ফেলে দিন।',
        'symptoms' => ['leaf-curl' => 10, 'whole-slow' => 6],
    ],
    [
        'slug' => 'nitrogen-deficiency', 'name_bn' => 'নাইট্রোজেন ঘাটতি', 'name_en' => 'Nitrogen Deficiency', 'type' => 'nutrient', 'severity' => 'low',
        'description_bn' => 'নাইট্রোজেনের অভাবে পুরনো পাতা প্রথমে হলুদ হয়ে যায়, কারণ গাছ সীমিত নাইট্রোজেন নতুন পাতায় পাঠিয়ে দেয়।',
        'identification_bn' => 'নিচের দিকের পুরনো পাতা সমানভাবে হলুদ হয়ে যায়, শিরাসহ। নতুন পাতা তুলনামূলক সবুজ থাকে।',
        'organic_remedy_bn' => "১. পচা গোবর সার বা কেঁচো সার মাটিতে মিশিয়ে দিন।\n২. সরিষার খৈল পানিতে ভিজিয়ে পাতলা করে দিন।",
        'chemical_remedy_bn' => 'ইউরিয়া হালকা মাত্রায় (১ গ্রাম প্রতি লিটার পানি) মাসে একবার দিতে পারেন — বেশি দিলে পাতা পুড়ে যাবে।',
        'prevention_bn' => 'নিয়মিত সার দেওয়ার সময়সূচি মেনে চলুন।',
        'safety_note_bn' => 'ইউরিয়া বেশি দিলে শিকড় পুড়ে যায় — মাত্রা মেনে চলুন।',
        'symptoms' => ['leaf-yellow' => 9, 'whole-slow' => 5],
    ],
    [
        'slug' => 'overwatering', 'name_bn' => 'অতিরিক্ত পানি', 'name_en' => 'Overwatering', 'type' => 'cultural', 'severity' => 'high',
        'description_bn' => 'নতুন বাগানিদের সবচেয়ে সাধারণ ভুল। বেশি পানিতে মাটিতে অক্সিজেন কমে যায়, শিকড় পচে গাছ মরে যায় — শুকিয়ে নয়।',
        'identification_bn' => 'গাছ নেতিয়ে থাকে যদিও মাটি ভেজা। পাতা হলুদ হয়ে ঝরে যায়, মাটি থেকে দুর্গন্ধ আসতে পারে।',
        'organic_remedy_bn' => "১. পানি দেওয়া বন্ধ করে মাটি শুকাতে দিন।\n২. টবের নিচে ছিদ্র আছে কিনা, নিষ্কাশন ঠিক আছে কিনা যাচাই করুন।\n৩. প্রয়োজনে গাছ তুলে শিকড় দেখুন, পচা অংশ কেটে নতুন মাটিতে বসান।",
        'chemical_remedy_bn' => null,
        'prevention_bn' => 'পানি দেওয়ার আগে আঙুল দিয়ে মাটি পরীক্ষা করুন — উপরের ১ ইঞ্চি শুকনো লাগলেই তবে পানি দিন।',
        'safety_note_bn' => 'প্রতিটা গাছের পানির চাহিদা আলাদা — একই নিয়মে সব গাছে পানি দেবেন না।',
        'symptoms' => ['whole-wilting' => 8, 'leaf-yellow' => 6, 'root-rot' => 7, 'soil-smell' => 5],
    ],
    [
        'slug' => 'sunburn', 'name_bn' => 'রোদে পোড়া', 'name_en' => 'Sunburn / Scorch', 'type' => 'environmental', 'severity' => 'medium',
        'description_bn' => 'ছায়াপ্রিয় গাছ হঠাৎ কড়া রোদে রাখলে বা গরমকালে পাতা পুড়ে যেতে পারে।',
        'identification_bn' => 'পাতার কিনারা বা মাঝের অংশ বাদামি-শুকনো হয়ে যায়, বিশেষ করে যেদিকে সরাসরি রোদ লাগে।',
        'organic_remedy_bn' => "১. গাছটিকে আংশিক ছায়ায় সরিয়ে নিন।\n২. দুপুরের কড়া রোদ থেকে ছায়া দেওয়ার ব্যবস্থা করুন (নেট বা অন্য গাছের ছায়া)।\n৩. পোড়া পাতা কেটে ফেলে দিন।",
        'chemical_remedy_bn' => null,
        'prevention_bn' => 'নতুন গাছ বা ইনডোর থেকে বাইরে আনা গাছ ধীরে ধীরে রোদে অভ্যস্ত করান, হঠাৎ পূর্ণ রোদে রাখবেন না।',
        'safety_note_bn' => null,
        'symptoms' => ['whole-sunburn' => 10, 'leaf-brown-tip' => 5],
    ],
    [
        'slug' => 'fungus-gnats', 'name_bn' => 'মাটির ছোট মাছি', 'name_en' => 'Fungus Gnats', 'type' => 'pest', 'severity' => 'low',
        'description_bn' => 'মাটির ছোট মাছি ভেজা মাটিতে ডিম পাড়ে, বাচ্চা পোকা শিকড়ের ক্ষতি করতে পারে। বিরক্তিকর হলেও সাধারণত বড় ক্ষতি করে না।',
        'identification_bn' => 'মাটির কাছে ছোট ছোট কালো মাছি উড়তে দেখা যায়, বিশেষ করে পানি দেওয়ার পর।',
        'organic_remedy_bn' => "১. মাটির উপরিভাগ শুকাতে দিন — এই পোকা ভেজা মাটি পছন্দ করে।\n২. হলুদ আঠালো ফাঁদ ব্যবহার করুন।\n৩. মাটির উপর সামান্য বালি বা নিম খৈল ছড়িয়ে দিন।",
        'chemical_remedy_bn' => 'তীব্র হলে বিটি (Bacillus thuringiensis israelensis) ভিত্তিক মাটি-প্রয়োগ কীটনাশক ব্যবহার করা যায়।',
        'prevention_bn' => 'বেশি পানি দেওয়া এড়িয়ে চলুন, মাটির উপরিভাগ শুকনো রাখুন পানি দেওয়ার মাঝে।',
        'safety_note_bn' => null,
        'symptoms' => ['soil-gnats' => 10, 'soil-mold' => 3],
    ],
    [
        'slug' => 'black-spot', 'name_bn' => 'পাতায় কালো দাগ', 'name_en' => 'Black Spot', 'type' => 'fungal', 'severity' => 'medium',
        'description_bn' => 'পাতায় কালো দাগ রোগ ছত্রাকজনিত, গোলাপ ও অন্যান্য ফুল গাছে বেশি দেখা যায়। আর্দ্র আবহাওয়ায় দ্রুত ছড়ায়।',
        'identification_bn' => 'পাতায় গোলাকার কালো-বাদামি দাগ পড়ে, দাগের চারপাশ হলুদ হয়ে যায়। বেশি আক্রান্ত পাতা ঝরে পড়ে।',
        'organic_remedy_bn' => "১. আক্রান্ত পাতা তুলে ফেলে দিন (মাটিতে ফেলবেন না)।\n২. বেকিং সোডা স্প্রে (১ চা চামচ + ১ লিটার পানি + সামান্য সাবান) সপ্তাহে একবার দিন।\n৩. পাতায় পানি না দিয়ে গোড়ায় পানি দিন।",
        'chemical_remedy_bn' => 'তীব্র হলে ম্যানকোজেব-জাতীয় ছত্রাকনাশক লেবেল অনুযায়ী স্প্রে করুন।',
        'prevention_bn' => 'গাছের মধ্যে ফাঁকা রেখে বাতাস চলাচল ভালো রাখুন, ঝরা পাতা নিয়মিত পরিষ্কার করুন।',
        'safety_note_bn' => 'ছত্রাকনাশক ব্যবহারের পর হাত ধুয়ে নিন।',
        'symptoms' => ['leaf-black-spot' => 10, 'leaf-drop' => 5],
    ],
    [
        'slug' => 'blossom-end-rot', 'name_bn' => 'ফলের নিচে পচন (ক্যালসিয়াম ঘাটতি)', 'name_en' => 'Blossom End Rot', 'type' => 'nutrient', 'severity' => 'medium',
        'description_bn' => 'টমেটো ও মরিচে ফলের নিচের দিকে কালচে-পচা দাগ পড়ে, মূলত ক্যালসিয়ামের ঘাটতি বা অনিয়মিত পানির কারণে হয়।',
        'identification_bn' => 'ফলের নিচের (ফুলের বোঁটার বিপরীত) অংশ কালচে-বাদামি হয়ে ভেতরে দেবে যায়।',
        'organic_remedy_bn' => "১. ডিমের খোসা গুঁড়া করে মাটিতে মিশিয়ে দিন — প্রাকৃতিক ক্যালসিয়ামের উৎস।\n২. পানি নিয়মিত ও সমান পরিমাণে দিন, একদিন খুব বেশি একদিন কম না দিয়ে।",
        'chemical_remedy_bn' => 'ক্যালসিয়াম নাইট্রেট পাতায় স্প্রে করা যায় লেবেল-নির্দেশিত মাত্রায়।',
        'prevention_bn' => 'মাটিতে আগে থেকেই চুন বা ডিমের খোসা মিশিয়ে রাখুন, পানি দেওয়া নিয়মিত রাখুন।',
        'safety_note_bn' => null,
        'symptoms' => ['fruit-crack' => 4, 'whole-slow' => 3],
    ],
];

$problemIds = [];
foreach ($problems as $p) {
    $exists = Db::value('SELECT id FROM problems WHERE slug = ?', [$p['slug']]);
    if ($exists) {
        $problemIds[$p['slug']] = (int) $exists;
        continue;
    }

    $id = Db::insert(
        'INSERT INTO problems (slug, name_bn, name_en, type, severity, description_bn, identification_bn,
            organic_remedy_bn, chemical_remedy_bn, prevention_bn, safety_note_bn, is_published)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)',
        [
            $p['slug'], $p['name_bn'], $p['name_en'], $p['type'], $p['severity'],
            $p['description_bn'], $p['identification_bn'], $p['organic_remedy_bn'],
            $p['chemical_remedy_bn'], $p['prevention_bn'], $p['safety_note_bn'],
        ]
    );
    $problemIds[$p['slug']] = $id;

    foreach ($p['symptoms'] as $symptomSlug => $weight) {
        if (!isset($symptomBySlug[$symptomSlug])) {
            continue;
        }
        Db::exec(
            'INSERT IGNORE INTO problem_symptoms (problem_id, symptom_id, weight) VALUES (?, ?, ?)',
            [$id, $symptomBySlug[$symptomSlug], $weight]
        );
    }
}

fwrite(STDOUT, '  ' . count($problemIds) . " problems ready\n");

// Link plants to their common problems.
$plantProblemLinks = [
    'moric' => ['whitefly', 'aphid', 'leaf-curl-virus'],
    'tomato' => ['blossom-end-rot', 'black-spot', 'whitefly'],
    'lebu' => ['red-spider-mite', 'mealybug', 'nitrogen-deficiency'],
    'golap' => ['black-spot', 'powdery-mildew', 'aphid'],
    'joba' => ['mealybug', 'leaf-curl-virus'],
    'lau' => ['powdery-mildew', 'red-spider-mite'],
    'shosha' => ['powdery-mildew'],
    'begun' => ['whitefly'],
    'money-plant' => ['overwatering'],
    'snake-plant' => ['overwatering', 'root-rot'],
    'aloe-vera' => ['overwatering'],
    'pepe' => ['root-rot', 'black-spot'],
    'palong' => ['aphid'],
    'dherosh' => ['aphid'],
    'pudina' => ['overwatering'],
];
foreach ($plantProblemLinks as $plantSlug => $problemSlugs) {
    if (!isset($plantIds[$plantSlug])) {
        continue;
    }
    foreach ($problemSlugs as $problemSlug) {
        if (!isset($problemIds[$problemSlug])) {
            continue;
        }
        Db::exec(
            'INSERT IGNORE INTO plant_problems (plant_id, problem_id, frequency) VALUES (?, ?, "common")',
            [$plantIds[$plantSlug], $problemIds[$problemSlug]]
        );
    }
}

// ── Guides ────────────────────────────────────────────────────────────

fwrite(STDOUT, "  seeding guides…\n");

$guides = [
    [
        'slug' => 'chadbagan-shuru', 'title_bn' => 'ছাদবাগান কীভাবে শুরু করবেন', 'category' => 'rooftop',
        'excerpt_bn' => 'ছাদবাগান শুরু করার আগে যা যা জানা দরকার — ওজন, পানি নিষ্কাশন, রোদের হিসাব — সব একসাথে।',
        'read_minutes' => 6, 'is_premium' => 1,
        'body_bn' => "ছাদবাগান শুরু করার আগে প্রথমেই ছাদের অবস্থা যাচাই করে নেওয়া জরুরি।\n\n## ওজনের হিসাব\nভেজা মাটি ভরা টব বেশ ভারী হয়। বড় পরিসরে বাগান করার আগে ছাদের ভার-বহন ক্ষমতা সম্পর্কে নিশ্চিত হয়ে নিন, প্রয়োজনে বিল্ডিং ইঞ্জিনিয়ারের পরামর্শ নিন। ভারী টব ছাদের কলাম বা দেয়ালের কাছাকাছি রাখুন, মাঝখানে নয়।\n\n## পানি নিষ্কাশন\nছাদের ড্রেন যেন কখনো টবের মাটি বা পানিতে আটকে না যায়, তা নিশ্চিত করুন। প্রতিটা টবের নিচে থালা বা ট্রে রাখুন, যাতে অতিরিক্ত পানি সরাসরি ছাদে না পড়ে — এতে ছাদ ড্যামেজ হওয়ার ঝুঁকি কমে।\n\n## রোদের হিসাব\nছাদে সাধারণত পূর্ণ রোদ পাওয়া যায়, যা বেশিরভাগ সবজি আর ফুল গাছের জন্য ভালো। তবে গ্রীষ্মের কড়া রোদে কিছু গাছের জন্য হালকা ছায়ার ব্যবস্থা (নেট বা ছাতা) রাখা ভালো।\n\n## শুরুটা ছোট করুন\nএকসাথে অনেক গাছ না লাগিয়ে ৫-৬টা টব দিয়ে শুরু করুন। কোন গাছ আপনার ছাদের পরিবেশে ভালো হয়, সেটা বুঝে ধীরে ধীরে বাড়ান।\n\n## মৌলিক উপকরণ\nভালো মানের টব, নিষ্কাশনযোগ্য পটিং মিক্স, একটা পানি দেওয়ার ক্যান বা হোসপাইপ, আর ছোট একটা বাগান করার কাঁচি — এই কয়েকটা দিয়েই শুরু করা যায়।",
    ],
    [
        'slug' => 'compost-banano', 'title_bn' => 'বাসায় কম্পোস্ট বানানোর সহজ পদ্ধতি', 'category' => 'soil',
        'excerpt_bn' => 'রান্নাঘরের সবজির খোসা আর বাগানের শুকনো পাতা দিয়ে কীভাবে ঘরেই সার বানাবেন।',
        'read_minutes' => 5, 'is_premium' => 1,
        'body_bn' => "কম্পোস্ট হলো রান্নাঘর আর বাগানের জৈব বর্জ্য থেকে তৈরি প্রাকৃতিক সার — কেনা সারের চেয়ে সস্তা আর মাটির জন্য অনেক ভালো।\n\n## কী কী লাগবে\n- সবজির খোসা, ফলের অবশিষ্টাংশ (রান্না করা খাবার নয়)\n- শুকনো পাতা, খড়কুটো\n- একটা বালতি বা ছোট গর্ত, ঢাকনাসহ\n\n## অনুপাত\nভেজা উপকরণ (সবজির খোসা) আর শুকনো উপকরণ (শুকনো পাতা) মোটামুটি ১:২ অনুপাতে মেশান। শুধু ভেজা উপকরণ দিলে দুর্গন্ধ হয়, শুধু শুকনো দিলে পচন ধীর হয়।\n\n## ধাপে ধাপে\n১. একটা স্তরে শুকনো পাতা, তার উপর সবজির খোসা দিন।\n২. প্রতি কয়েকদিনে একবার নাড়িয়ে দিন, যাতে বাতাস ঢোকে।\n৩. মাটি একটু ভেজা রাখুন, কিন্তু ভিজে না রাখুন।\n৪. ৬-৮ সপ্তাহে মিশ্রণ কালচে, মাটির মতো গন্ধযুক্ত হয়ে গেলে বুঝবেন কম্পোস্ট তৈরি।\n\n## যা দেবেন না\nমাংস, তেল, রান্না করা খাবার, বা পোষা প্রাণীর বর্জ্য কম্পোস্টে দেবেন না — এতে দুর্গন্ধ ও পোকা হয়।\n\n## ব্যবহার\nতৈরি কম্পোস্ট গাছের গোড়ার মাটির সাথে মিশিয়ে দিন। এটা রাসায়নিক সারের বিকল্প না হলেও, নিয়মিত ব্যবহারে মাটির স্বাস্থ্য অনেক ভালো হয়।",
    ],
    [
        'slug' => 'toboer-mati', 'title_bn' => 'টবের জন্য সঠিক মাটি তৈরি করবেন কীভাবে', 'category' => 'soil',
        'excerpt_bn' => 'বাগানের সাধারণ মাটি সরাসরি টবে দিলে কেন সমস্যা হয়, আর ঘরে বসে কীভাবে ভালো পটিং মিক্স বানাবেন।',
        'read_minutes' => 4, 'is_premium' => 1,
        'body_bn' => "বাগানের এঁটেল মাটি সরাসরি টবে ভরলে পানি জমে থাকে, শিকড়ে বাতাস ঢোকে না — এটা নতুন বাগানিদের সবচেয়ে সাধারণ ভুলগুলোর একটা।\n\n## ভালো পটিং মিক্সের অনুপাত\n- ৫০% সাধারণ মাটি বা দোআঁশ মাটি\n- ৩০% কম্পোস্ট বা পচা গোবর সার\n- ২০% বালি বা কোকোপিট (নিষ্কাশন বাড়ানোর জন্য)\n\n## কেন এই মিশ্রণ কাজ করে\nমাটি পুষ্টি ধরে রাখে, কম্পোস্ট জৈব উপাদান আর পুষ্টি জোগায়, বালি বা কোকোপিট অতিরিক্ত পানি দ্রুত বের করে দেয় — তিনটে মিলে শিকড়ের জন্য আদর্শ পরিবেশ তৈরি হয়।\n\n## গাছভেদে সামান্য পরিবর্তন\nক্যাকটাস-সাকুলেন্টের জন্য বালির পরিমাণ বাড়ান (৩০-৪০%)। পাতাবাহার বা ফার্নের জন্য কোকোপিট বেশি রাখুন, আর্দ্রতা ধরে রাখতে সাহায্য করে।\n\n## পুরনো মাটি পুনর্ব্যবহার\nআগের টবের মাটি ফেলে না দিয়ে রোদে শুকিয়ে, তার সাথে নতুন কম্পোস্ট মিশিয়ে আবার ব্যবহার করা যায়।",
    ],
    [
        'slug' => 'cutting-theke-chara', 'title_bn' => 'কাটিং থেকে নতুন চারা বানানোর পদ্ধতি', 'category' => 'propagation',
        'excerpt_bn' => 'বীজ ছাড়াই ডাল কেটে কীভাবে নতুন গাছ বানাবেন — মানিপ্ল্যান্ট থেকে গোলাপ পর্যন্ত এই পদ্ধতি কাজ করে।',
        'read_minutes' => 5, 'is_premium' => 1,
        'body_bn' => "অনেক গাছ বীজ ছাড়াই, শুধু একটা ডাল কেটে নতুন করে জন্মানো যায় — একে বলে কাটিং।\n\n## কোন গাছে কাজ করে\nমানিপ্ল্যান্ট, পাথরকুচি, তুলসী, গোলাপ, পুদিনার মতো গাছে কাটিং পদ্ধতি সহজে কাজ করে।\n\n## ধাপে ধাপে\n১. সুস্থ, রোগমুক্ত ডাল থেকে ৪-৬ ইঞ্চি লম্বা টুকরা কাটুন, নিচের দিকে কাত করে কাটুন।\n২. নিচের দিকের পাতা ফেলে দিন, উপরে ২-৩টা পাতা রেখে দিন।\n৩. কাটা অংশ পানিতে বা সরাসরি ভেজা মাটিতে বসিয়ে দিন।\n৪. পানিতে রাখলে ২-৩ সপ্তাহে শিকড় গজাবে, তখন মাটিতে বসাতে পারবেন।\n\n## যত্ন\nনতুন কাটিং সরাসরি কড়া রোদে না রেখে আংশিক ছায়ায় রাখুন, শিকড় শক্ত না হওয়া পর্যন্ত। মাটি সবসময় হালকা ভেজা রাখুন।\n\n## সফলতার হার বাড়াতে\nএকসাথে কয়েকটা কাটিং লাগান — সবগুলো বাঁচবে না, কিন্তু কয়েকটা নিশ্চিতভাবে বাঁচবে।",
    ],
    [
        'slug' => 'npk-bojha', 'title_bn' => 'NPK মানে কী — সারের হিসাব সহজে বোঝা', 'category' => 'fertilizer',
        'excerpt_bn' => 'সারের প্যাকেটে লেখা N-P-K সংখ্যাগুলো আসলে কী বোঝায়, আর কখন কোনটা বেশি দরকার।',
        'read_minutes' => 4, 'is_premium' => 1,
        'body_bn' => "সারের প্যাকেটে ১০-১০-১০ বা ২০-১০-১০-এর মতো সংখ্যা লেখা থাকে — এগুলো নাইট্রোজেন (N), ফসফরাস (P), আর পটাশিয়াম (K)-এর অনুপাত।\n\n## নাইট্রোজেন (N)\nপাতা আর সবুজ অংশের বৃদ্ধির জন্য দরকার। পাতাবাহার গাছ বা শাক-সবজির শুরুর দিকে নাইট্রোজেন বেশি দরকার হয়।\n\n## ফসফরাস (P)\nশিকড়, ফুল আর ফলের জন্য জরুরি। ফুল বা ফল আসার সময় ফসফরাস-সমৃদ্ধ সার দিলে ফলন ভালো হয়।\n\n## পটাশিয়াম (K)\nসামগ্রিক গাছের স্বাস্থ্য আর রোগ প্রতিরোধ ক্ষমতার জন্য দরকার। ফুল বেশি ফোটাতে আর ফলের গুণমান বাড়াতে সাহায্য করে।\n\n## কখন কোনটা দেবেন\n- চারা অবস্থায়: নাইট্রোজেন বেশি (যেমন ২০-১০-১০)\n- ফুল-ফল আসার আগে: ফসফরাস-পটাশিয়াম বেশি (যেমন ১০-২০-২০)\n- সাধারণ যত্নে: সুষম সার (১০-১০-১০) যথেষ্ট\n\n## মনে রাখবেন\nবেশি সার দিলে উপকারের বদলে ক্ষতি হয় — পাতা পুড়ে যেতে পারে, শিকড় ক্ষতিগ্রস্ত হতে পারে। প্যাকেটের নির্দেশিত মাত্রার বেশি কখনো দেবেন না।",
    ],
    [
        'slug' => 'borshay-bagan', 'title_bn' => 'বর্ষাকালে বাগানের যত্ন', 'category' => 'season',
        'excerpt_bn' => 'বর্ষায় অতিরিক্ত পানি আর ছত্রাকের আক্রমণ থেকে গাছ বাঁচানোর ব্যবহারিক উপায়।',
        'read_minutes' => 4, 'is_premium' => 1,
        'body_bn' => "বর্ষাকালে পানি নিয়ে চিন্তা কমে যায়, কিন্তু নতুন সমস্যা শুরু হয় — অতিরিক্ত আর্দ্রতা আর ছত্রাক রোগ।\n\n## নিষ্কাশন নিশ্চিত করুন\nটবের নিচের ছিদ্র বন্ধ হয়ে আছে কিনা পরীক্ষা করুন। প্রয়োজনে টবের নিচে ইট বা কাঠের টুকরা দিয়ে সামান্য উঁচু করে রাখুন, যাতে পানি জমে না থাকে।\n\n## অতিরিক্ত পানি এড়ান\nবৃষ্টি হলে বাড়তি পানি দেওয়ার দরকার নেই। যেসব গাছ ছাদের নিচে বা ছাউনির নিচে আছে, শুধু সেগুলোর জন্য পানি দেওয়ার সময়সূচি ঠিক রাখুন।\n\n## ছত্রাক নিয়ন্ত্রণ\nবর্ষায় পাউডারি মিলডিউ, কালো দাগ রোগের মতো ছত্রাকজনিত সমস্যা বেশি হয়। গাছের মধ্যে ফাঁকা রাখুন, বাতাস চলাচল ভালো রাখুন।\n\n## এই সময় যা লাগাবেন\nবর্ষা আদা, হলুদ, লেবু গাছের জন্য ভালো সময়। নতুন চারা এই সময় সহজে শিকড় ধরে।\n\n## সতর্কতা\nদীর্ঘ সময় পানি জমে থাকলে মশার প্রজনন হতে পারে — টবের নিচের ট্রে নিয়মিত খালি করুন।",
    ],
    [
        'slug' => 'shiter-ful', 'title_bn' => 'শীতকালীন ফুল গাছের পরিচর্যা', 'category' => 'season',
        'excerpt_bn' => 'শীতে কোন ফুল গাছ লাগাবেন, আর ঠান্ডা থেকে গাছকে কীভাবে সুরক্ষা দেবেন।',
        'read_minutes' => 4, 'is_premium' => 1,
        'body_bn' => "শীতকাল বাংলাদেশে ফুল গাছের জন্য সবচেয়ে ভালো সময় — বেশিরভাগ মৌসুমি ফুল এই সময়েই সবচেয়ে ভালো ফোটে।\n\n## জনপ্রিয় শীতকালীন ফুল\nগাঁদা, ডালিয়া, পিটুনিয়া, স্যালভিয়া, ভারবেনার মতো ফুল শীতে সবচেয়ে ভালো হয়। নভেম্বর-ডিসেম্বরে চারা লাগালে জানুয়ারি-ফেব্রুয়ারিতে ফুল পাবেন।\n\n## পানি কমিয়ে দিন\nশীতে বাষ্পীভবন কম হয় বলে মাটি শুকাতে বেশি সময় লাগে। গরমকালের নিয়মে পানি দিলে মাটি বেশি ভেজা থেকে যেতে পারে — মাটি পরীক্ষা করে তবেই পানি দিন।\n\n## রোদের অবস্থান বদলায়\nশীতে সূর্য নিচু কোণে থাকে, তাই আগে যেখানে ছায়া ছিল না সেখানেও ছায়া পড়তে পারে। গাছের অবস্থান প্রয়োজনে বদলে নিন যাতে পর্যাপ্ত রোদ পায়।\n\n## ঠান্ডা থেকে সুরক্ষা\nকিছু ইনডোর গাছ (মানিপ্ল্যান্ট, অ্যালোভেরা) হঠাৎ ঠান্ডায় ক্ষতিগ্রস্ত হতে পারে। রাতে খুব ঠান্ডা হলে বারান্দার ভেতরের দিকে সরিয়ে রাখুন।",
    ],
    [
        'slug' => 'pata-dekhe-shastho', 'title_bn' => 'পাতা দেখে গাছের স্বাস্থ্য বোঝার সহজ উপায়', 'category' => 'basic',
        'excerpt_bn' => 'পাতার রং আর আকৃতি বদলে গেলে গাছ কী বলতে চাইছে — একটা দ্রুত রেফারেন্স গাইড।',
        'read_minutes' => 5, 'is_premium' => 0,
        'body_bn' => "গাছ কথা বলতে পারে না, কিন্তু পাতার মাধ্যমে ঠিকই সংকেত দেয়। কয়েকটা সাধারণ লক্ষণ চিনে রাখলে সমস্যা শুরুতেই ধরা যায়।\n\n## পুরনো পাতা হলুদ, নতুন পাতা সবুজ\nসাধারণত নাইট্রোজেনের ঘাটতি। জৈব সার বা হালকা ইউরিয়া দিন।\n\n## সব পাতা একসাথে ফ্যাকাশে, শিরা সবুজ\nআয়রন বা অন্য মাইক্রোনিউট্রিয়েন্টের ঘাটতির লক্ষণ, বিশেষ করে ক্ষারীয় মাটিতে বেশি হয়।\n\n## পাতার কিনারা বাদামি-শুকনো\nবেশিরভাগ সময় পানির অভাব বা রোদে পোড়ার লক্ষণ। কখনো কখনো অতিরিক্ত সারের কারণেও হয়।\n\n## পাতা নেতিয়ে পড়া\nপানির অভাব অথবা আধিক্য — দুটোই একই রকম দেখায়। মাটি স্পর্শ করে বুঝুন কোনটা কারণ।\n\n## পাতায় সাদা গুঁড়া বা কালো দাগ\nছত্রাক রোগের লক্ষণ — আর্দ্র আবহাওয়ায় বেশি হয়, বাতাস চলাচল বাড়ান।\n\n## পাতায় ছিদ্র বা কাটা দাগ\nপোকামাকড়ের আক্রমণ — রাতে টর্চ দিয়ে দেখুন, অনেক পোকা রাতে সক্রিয় থাকে।\n\nনিয়মিত গাছ পর্যবেক্ষণ করার অভ্যাস করলে বেশিরভাগ সমস্যা বড় হওয়ার আগেই ধরা পড়ে।",
    ],
];

foreach ($guides as $g) {
    $exists = Db::value('SELECT id FROM guides WHERE slug = ?', [$g['slug']]);
    if ($exists) {
        continue;
    }
    Db::insert(
        'INSERT INTO guides (slug, title_bn, category, excerpt_bn, body_bn, read_minutes, is_premium, is_published, published_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())',
        [$g['slug'], $g['title_bn'], $g['category'], $g['excerpt_bn'], $g['body_bn'], $g['read_minutes'], $g['is_premium']]
    );
}

fwrite(STDOUT, '  ' . count($guides) . " guides ready\n");

// ── Admin user ────────────────────────────────────────────────────────

$adminEmail = 'admin@gardenbondhu.test';
$exists = Db::value('SELECT id FROM admins WHERE email = ?', [$adminEmail]);
if (!$exists) {
    Db::insert(
        'INSERT INTO admins (email, password_hash, name, role) VALUES (?, ?, ?, "admin")',
        [$adminEmail, password_hash('ChangeMe123!', PASSWORD_ARGON2ID), 'Admin']
    );
    fwrite(STDOUT, "  admin created: $adminEmail / ChangeMe123!  (CHANGE THIS before launch)\n");
}
