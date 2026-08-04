<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Whitelist Markdown → HTML renderer (spec §9.4).
 *
 * Permits only: p h2 h3 ul ol li strong em a[href^=https] img[src^=/]
 * blockquote code pre table thead tbody tr th td hr br.
 * Everything else in the source is escaped, so authored content can never
 * inject markup. Runs server-side; the browser never sees raw Markdown.
 */
final class Markdown
{
    public static function render(?string $markdown): string
    {
        if ($markdown === null || trim($markdown) === '') {
            return '';
        }

        $lines = preg_split('/\R/u', str_replace("\t", '    ', $markdown)) ?: [];
        $html  = '';
        $i     = 0;
        $count = count($lines);

        while ($i < $count) {
            $line = $lines[$i];

            // Blank
            if (trim($line) === '') {
                $i++;
                continue;
            }

            // Fenced code
            if (preg_match('/^```/', $line)) {
                $i++;
                $code = [];
                while ($i < $count && !preg_match('/^```/', $lines[$i])) {
                    $code[] = $lines[$i];
                    $i++;
                }
                $i++;
                $html .= '<pre><code>' . e(implode("\n", $code)) . '</code></pre>';
                continue;
            }

            // Horizontal rule
            if (preg_match('/^\s*(---|\*\*\*)\s*$/', $line)) {
                $html .= '<hr>';
                $i++;
                continue;
            }

            // Headings (h2/h3 only — the page owns h1)
            if (preg_match('/^(#{1,6})\s+(.*)$/u', $line, $m)) {
                $tag = strlen($m[1]) <= 2 ? 'h2' : 'h3';
                $html .= '<' . $tag . '>' . self::inline($m[2]) . '</' . $tag . '>';
                $i++;
                continue;
            }

            // Blockquote
            if (preg_match('/^>\s?(.*)$/u', $line)) {
                $buf = [];
                while ($i < $count && preg_match('/^>\s?(.*)$/u', $lines[$i], $m)) {
                    $buf[] = $m[1];
                    $i++;
                }
                $html .= '<blockquote><p>' . self::inline(implode(' ', $buf)) . '</p></blockquote>';
                continue;
            }

            // Table  | a | b |
            if (str_starts_with(trim($line), '|') && isset($lines[$i + 1]) && preg_match('/^\s*\|[\s:\-\|]+\|\s*$/', $lines[$i + 1])) {
                $head = self::tableCells($lines[$i]);
                $i += 2;
                $rows = [];
                while ($i < $count && str_starts_with(trim($lines[$i]), '|')) {
                    $rows[] = self::tableCells($lines[$i]);
                    $i++;
                }
                $html .= '<div class="table-wrap"><table><thead><tr>';
                foreach ($head as $cell) {
                    $html .= '<th>' . self::inline($cell) . '</th>';
                }
                $html .= '</tr></thead><tbody>';
                foreach ($rows as $row) {
                    $html .= '<tr>';
                    foreach ($row as $cell) {
                        $html .= '<td>' . self::inline($cell) . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</tbody></table></div>';
                continue;
            }

            // Unordered list
            if (preg_match('/^\s*[-*•]\s+(.*)$/u', $line)) {
                $html .= '<ul>';
                while ($i < $count && preg_match('/^\s*[-*•]\s+(.*)$/u', $lines[$i], $m)) {
                    $html .= '<li>' . self::inline($m[1]) . '</li>';
                    $i++;
                }
                $html .= '</ul>';
                continue;
            }

            // Ordered list
            if (preg_match('/^\s*\d+[.)]\s+(.*)$/u', $line)) {
                $html .= '<ol>';
                while ($i < $count && preg_match('/^\s*\d+[.)]\s+(.*)$/u', $lines[$i], $m)) {
                    $html .= '<li>' . self::inline($m[1]) . '</li>';
                    $i++;
                }
                $html .= '</ol>';
                continue;
            }

            // Paragraph — consume until a blank line or a block starter
            $buf = [];
            while ($i < $count && trim($lines[$i]) !== '' && !preg_match('/^(\s*[-*•]\s|\s*\d+[.)]\s|#{1,6}\s|>|```|\||\s*---\s*$)/u', $lines[$i])) {
                $buf[] = $lines[$i];
                $i++;
            }
            if ($buf !== []) {
                $html .= '<p>' . self::inline(implode(' ', $buf)) . '</p>';
            } else {
                $i++;
            }
        }

        return $html;
    }

    /** @return string[] */
    private static function tableCells(string $line): array
    {
        return array_map('trim', explode('|', trim(trim($line), '|')));
    }

    /**
     * Inline formatting. The text is escaped FIRST, so any HTML the author
     * typed is inert; only the markers we recognise become tags.
     */
    private static function inline(string $text): string
    {
        $out = e($text);

        // Images: ![alt](/path)  — local paths only
        $out = preg_replace_callback(
            '/!\[([^\]]*)\]\((\/[^\s)]+)\)/u',
            static fn(array $m): string => '<img src="' . $m[2] . '" alt="' . $m[1] . '" loading="lazy">',
            $out
        ) ?? $out;

        // Links: [text](https://…) or [text](/local)
        $out = preg_replace_callback(
            '/\[([^\]]+)\]\((https:\/\/[^\s)]+|\/[^\s)]*)\)/u',
            static function (array $m): string {
                $external = str_starts_with($m[2], 'https://');
                $rel = $external ? ' target="_blank" rel="noopener noreferrer"' : '';
                return '<a href="' . $m[2] . '"' . $rel . '>' . $m[1] . '</a>';
            },
            $out
        ) ?? $out;

        $out = preg_replace('/`([^`]+)`/u', '<code>$1</code>', $out) ?? $out;
        $out = preg_replace('/\*\*([^*]+)\*\*/u', '<strong>$1</strong>', $out) ?? $out;
        $out = preg_replace('/(?<![\w*])\*([^*\n]+)\*(?![\w*])/u', '<em>$1</em>', $out) ?? $out;

        return $out;
    }

    /** Plain-text preview from Markdown, for meta descriptions and teasers. */
    public static function toText(?string $markdown, int $chars = 200): string
    {
        return str_excerpt(strip_tags(self::render($markdown)), $chars);
    }
}
