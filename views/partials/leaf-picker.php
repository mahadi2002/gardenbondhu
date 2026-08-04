<?php
/**
 * The signature element: a plant silhouette whose parts map 1:1 to
 * symptoms.body_part. Each hotspot is a real <button> — keyboard reachable,
 * announced by screen readers, and harmless when JS never loads.
 *
 * @var array<string,string> $bodyParts  slug → Bangla label
 */
?>
<figure class="plant-figure" data-diagnoser data-pulse="true">
  <svg viewBox="0 0 320 380" role="img" aria-label="গাছের ছবি — যে অংশে সমস্যা, সেটি বেছে নিন">
    <!-- soil -->
    <path d="M40 330h240l-14 34H54z" fill="#6B5138" opacity=".85"/>
    <path d="M40 330h240l-6 14H46z" fill="#4A3826" opacity=".6"/>

    <!-- roots -->
    <g stroke="#8A6A45" stroke-width="3" fill="none" stroke-linecap="round" opacity=".9">
      <path d="M160 330v20M160 340l-22 16M160 340l22 16M160 350l-11 12M160 350l11 12"/>
    </g>

    <!-- stem -->
    <path class="draw-path" d="M160 330V120" stroke="#256B45" stroke-width="7" stroke-linecap="round" fill="none"/>

    <!-- leaves -->
    <g class="draw-fill">
      <path d="M160 208c0-30 22-52 60-56-2 34-24 56-60 56Z" fill="#256B45"/>
      <path d="M160 246c0-26-20-46-54-50 2 30 22 50 54 50Z" fill="#2F7E52"/>
      <path d="M160 160c0-28 20-48 55-52-2 32-22 52-55 52Z" fill="#357F55" opacity=".9"/>
      <path d="M160 208c14-16 34-28 60-32M160 246c-12-14-30-24-54-28M160 160c13-15 32-26 55-30"
            stroke="#173F2A" stroke-width="1.6" fill="none" opacity=".45"/>
    </g>

    <!-- flower + fruit -->
    <g class="draw-fill">
      <circle cx="160" cy="112" r="15" fill="#E9A227"/>
      <circle cx="160" cy="112" r="6" fill="#C4841A"/>
      <circle cx="214" cy="252" r="13" fill="#A8442C"/>
      <path d="M214 239v-8" stroke="#17452C" stroke-width="3" stroke-linecap="round"/>
    </g>

    <!-- hotspots: one per symptoms.body_part -->
    <g>
      <?php
      $spots = [
          'flower' => ['x' => 132, 'y' => 84,  'w' => 56,  'h' => 56],
          'leaf'   => ['x' => 168, 'y' => 130, 'w' => 118, 'h' => 92],
          'fruit'  => ['x' => 192, 'y' => 230, 'w' => 46,  'h' => 46],
          'stem'   => ['x' => 142, 'y' => 226, 'w' => 36,  'h' => 100],
          'soil'   => ['x' => 44,  'y' => 328, 'w' => 232, 'h' => 26],
          'root'   => ['x' => 116, 'y' => 336, 'w' => 88,  'h' => 40],
          'whole'  => ['x' => 34,  'y' => 66,  'w' => 66,  'h' => 66],
      ];

      foreach ($spots as $part => $box):
          if (!isset($bodyParts[$part])) {
              continue;
          }
      ?>
        <rect class="hotspot" data-part="<?= e($part) ?>" role="button" tabindex="0"
              aria-pressed="false" aria-label="<?= e($bodyParts[$part]) ?>-এর লক্ষণ দেখুন"
              x="<?= e((string) $box['x']) ?>" y="<?= e((string) $box['y']) ?>"
              width="<?= e((string) $box['w']) ?>" height="<?= e((string) $box['h']) ?>" rx="10"></rect>
        <text x="<?= e((string) ($box['x'] + $box['w'] / 2)) ?>"
              y="<?= e((string) ($box['y'] + $box['h'] / 2 + 5)) ?>"
              text-anchor="middle" font-size="13" fill="#0E2119" opacity=".7"
              pointer-events="none"><?= e($bodyParts[$part]) ?></text>
      <?php endforeach; ?>
    </g>
  </svg>

  <figcaption class="small muted center">যে অংশে সমস্যা, সেখানে চাপ দিন</figcaption>
</figure>
