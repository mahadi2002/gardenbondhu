<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;

/**
 * Water/fertilizer/pot-size calculators. All server-side, and every result
 * shows the formula it used — an estimate presented as an oracle is worse
 * than no estimate.
 */
final class ToolController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('app/tools', ['result' => null, 'tool' => null]);
    }

    public function water(Request $request): Response
    {
        $validator = Validator::make($request->body, [
            'diameter'   => 'required|numeric|between:5,120',
            'water_need' => 'required|in:low,medium,high',
            'season'     => 'required|in:1,2,3,4,5,6',
            'location'   => 'required|in:indoor,balcony,rooftop,yard',
        ], [
            'diameter'   => 'টবের ব্যাস',
            'water_need' => 'পানির চাহিদা',
            'season'     => 'ঋতু',
            'location'   => 'জায়গা',
        ]);

        if ($validator->fails()) {
            return $this->view('app/tools', [
                'result' => null,
                'tool'   => 'water',
                'errors' => $validator->errors(),
            ], 422);
        }

        $diameter = (float) $validator->get('diameter');
        $need     = (string) $validator->get('water_need');
        $season   = (int) $validator->get('season');
        $location = (string) $validator->get('location');

        // Surface area of the pot mouth in litres-equivalent. A 25 cm pot holds
        // roughly 8 L of mix; ~20 % of that volume is a full watering.
        $radius   = $diameter / 2;
        $volumeL  = (M_PI * $radius * $radius * ($diameter * 0.85)) / 1000;
        $litres   = $volumeL * 0.20;

        $needFactor = ['low' => 0.7, 'medium' => 1.0, 'high' => 1.3][$need];
        $litres    *= $needFactor;

        $baseInterval   = ['low' => 7, 'medium' => 3, 'high' => 2][$need];
        $seasonFactor   = [1 => 0.8, 2 => 1.6, 3 => 1.1, 4 => 1.2, 5 => 1.4, 6 => 1.0][$season];
        $locationFactor = ['indoor' => 1.3, 'balcony' => 1.0, 'rooftop' => 0.85, 'yard' => 1.1][$location];

        $interval = max(1, (int) round($baseInterval * $seasonFactor * $locationFactor));

        return $this->view('app/tools', [
            'tool'   => 'water',
            'result' => [
                'litres'   => round($litres, 2),
                'interval' => $interval,
                'formula'  => sprintf(
                    'টবের আয়তন ≈ %s লিটার × ২০%% = %s লিটার (পানির চাহিদা %s)। '
                    . 'বিরতি = %s দিন × ঋতু %s × জায়গা %s = %s দিন।',
                    bn_num(round($volumeL, 1)),
                    bn_num(round($litres, 2)),
                    (string) config('content.water.' . $need),
                    bn_num($baseInterval),
                    bn_num($seasonFactor),
                    bn_num($locationFactor),
                    bn_num($interval)
                ),
                'warning' => $need === 'low' && $interval < 4
                    ? 'কম পানির গাছে ঘন ঘন পানি দিলে শিকড় পচে যায়। মাটি শুকিয়েছে কিনা আঙুল দিয়ে দেখে নিন।'
                    : null,
            ],
        ]);
    }

    public function fertilizer(Request $request): Response
    {
        $validator = Validator::make($request->body, [
            'pot_volume' => 'required|numeric|between:1,200',
            'type'       => 'required|in:npk_balanced,npk_bloom,vermicompost,mustard_cake,cow_dung',
        ], [
            'pot_volume' => 'টবের আয়তন',
            'type'       => 'সারের ধরন',
        ]);

        if ($validator->fails()) {
            return $this->view('app/tools', [
                'result' => null,
                'tool'   => 'fertilizer',
                'errors' => $validator->errors(),
            ], 422);
        }

        $volume = (float) $validator->get('pot_volume');
        $type   = (string) $validator->get('type');

        // grams per litre of potting mix, and days between applications
        $table = [
            'npk_balanced' => ['g' => 1.5,  'days' => 21, 'label' => 'NPK ১০-১০-১০',       'note' => 'পানিতে গুলে মাটিতে দিন, পাতায় নয়।'],
            'npk_bloom'    => ['g' => 1.2,  'days' => 21, 'label' => 'NPK ফুলের সার (কম N)', 'note' => 'কুঁড়ি আসার ২ সপ্তাহ আগে থেকে দিন।'],
            'vermicompost' => ['g' => 25.0, 'days' => 30, 'label' => 'কেঁচো সার',           'note' => 'উপরের মাটির সাথে হালকা মিশিয়ে দিন।'],
            'mustard_cake' => ['g' => 8.0,  'days' => 30, 'label' => 'সরিষার খৈল',          'note' => '৩ দিন পানিতে ভিজিয়ে, ১:১০ অনুপাতে পাতলা করে দিন।'],
            'cow_dung'     => ['g' => 40.0, 'days' => 45, 'label' => 'পচা গোবর',            'note' => 'কাঁচা গোবর কখনো দেবেন না — শিকড় পুড়ে যাবে।'],
        ][$type];

        $grams = round($volume * $table['g'], 1);

        // Hard cap: over-fertilising a pot is the fastest way to kill it.
        $cap     = $type === 'npk_balanced' || $type === 'npk_bloom' ? $volume * 3 : $volume * 60;
        $warning = null;
        if ($grams > $cap) {
            $grams   = round($cap, 1);
            $warning = 'হিসাবটা নিরাপদ সীমায় নামিয়ে আনা হয়েছে। এর বেশি সার দিলে শিকড় পুড়ে যেতে পারে।';
        }

        return $this->view('app/tools', [
            'tool'   => 'fertilizer',
            'result' => [
                'grams'    => $grams,
                'interval' => $table['days'],
                'label'    => $table['label'],
                'note'     => $table['note'],
                'formula'  => sprintf(
                    '%s লিটার মাটি × %s গ্রাম/লিটার = %s গ্রাম, প্রতি %s দিনে একবার।',
                    bn_num($volume),
                    bn_num($table['g']),
                    bn_num($grams),
                    bn_num($table['days'])
                ),
                'warning'  => $warning,
            ],
        ]);
    }

    public function pot(Request $request): Response
    {
        $validator = Validator::make($request->body, [
            'maturity'  => 'required|in:seedling,young,mature',
            'root_type' => 'required|in:shallow,medium,deep,tap',
        ], [
            'maturity'  => 'গাছের বয়স',
            'root_type' => 'শিকড়ের ধরন',
        ]);

        if ($validator->fails()) {
            return $this->view('app/tools', [
                'result' => null,
                'tool'   => 'pot',
                'errors' => $validator->errors(),
            ], 422);
        }

        $maturity = (string) $validator->get('maturity');
        $root     = (string) $validator->get('root_type');

        $baseDiameter = ['seedling' => 12, 'young' => 22, 'mature' => 34][$maturity];
        $depthFactor  = ['shallow' => 0.6, 'medium' => 0.9, 'deep' => 1.2, 'tap' => 1.5][$root];

        $diameter = (int) round($baseDiameter);
        $depth    = (int) round($baseDiameter * $depthFactor);

        return $this->view('app/tools', [
            'tool'   => 'pot',
            'result' => [
                'diameter' => $diameter,
                'depth'    => $depth,
                'formula'  => sprintf(
                    'বয়স অনুযায়ী ব্যাস %s সেমি, শিকড়ের ধরন অনুযায়ী গভীরতা = ব্যাস × %s = %s সেমি।',
                    bn_num($diameter),
                    bn_num($depthFactor),
                    bn_num($depth)
                ),
                'note' => 'টবের নিচে অন্তত দুটো ছিদ্র রাখুন। পানি জমে থাকলে যত ভালো মাটিই হোক, শিকড় পচবে।',
            ],
        ]);
    }
}
