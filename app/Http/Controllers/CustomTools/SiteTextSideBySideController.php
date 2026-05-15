<?php

namespace App\Http\Controllers\CustomTools;

use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class SiteTextSideBySideController extends Controller
{
    public function index()
    {
        return view('customTools.rawText.site-text-side-by-side', [
            'input' => '',
            'results' => [],
        ]);
    }

    public function compare(Request $request)
    {
        $input = $request->input('pairs', '');
        $results = [];

        $flagMap = [
            'pt' => '🇵🇹',
            'en' => '🇬🇧',
            'es' => '🇪🇸',
            'fr' => '🇫🇷',
            'it' => '🇮🇹',
        ];

        if ($request->isMethod('post')) {
            $lines = preg_split('/\r\n|\r|\n/', trim($input));

            foreach ($lines as $index => $line) {
                $line = trim($line);

                if ($line === '') {
                    continue;
                }

                $parts = array_map('trim', explode('|', $line));

                if (count($parts) < 3) {
                    $results[] = [
                        'lang' => '',
                        'flag' => '🏳️',
                        'url_old' => '',
                        'url_new' => '',
                        'error' => 'Linha ' . ($index + 1) . ' inválida. Usa: lang|url_old|url_new',
                        'rows' => [],
                        'summary' => [],
                    ];
                    continue;
                }

                [$lang, $urlOld, $urlNew] = $parts;
                $langKey = mb_strtolower(trim($lang), 'UTF-8');

                try {
                    $htmlOld = $this->fetchHtml($urlOld);
                    $htmlNew = $this->fetchHtml($urlNew);

                    $textOld = $this->extractAllVisibleLikeText($htmlOld);
                    $textNew = $this->extractAllVisibleLikeText($htmlNew);

                    $blocksOld = $this->splitIntoBlocks($textOld);
                    $blocksNew = $this->splitIntoBlocks($textNew);

                    $rows = $this->alignBlocks($blocksOld, $blocksNew);

                    foreach ($rows as &$row) {
                        if (($row['left'] ?? '') !== '' && ($row['right'] ?? '') !== '') {
                            [$leftDiffHtml, $rightDiffHtml] = $this->wordDiffHtml($row['left'], $row['right']);
                            $row['left_html'] = $leftDiffHtml;
                            $row['right_html'] = $rightDiffHtml;
                        } else {
                            $row['left_html'] = ($row['left'] ?? '') !== '' ? nl2br(e($row['left'])) : '';
                            $row['right_html'] = ($row['right'] ?? '') !== '' ? nl2br(e($row['right'])) : '';
                        }
                    }
                    unset($row);

                    $summary = [
                        'same' => count(array_filter($rows, fn ($r) => $r['type'] === 'same')),
                        'similar' => count(array_filter($rows, fn ($r) => $r['type'] === 'similar')),
                        'different' => count(array_filter($rows, fn ($r) => $r['type'] === 'different')),
                        'left_only' => count(array_filter($rows, fn ($r) => $r['type'] === 'left_only')),
                        'right_only' => count(array_filter($rows, fn ($r) => $r['type'] === 'right_only')),
                    ];

                    $results[] = [
                        'lang' => $langKey,
                        'flag' => $flagMap[$langKey] ?? '🏳️',
                        'url_old' => $urlOld,
                        'url_new' => $urlNew,
                        'error' => '',
                        'rows' => $rows,
                        'summary' => $summary,
                    ];
                } catch (\Throwable $e) {
                    $results[] = [
                        'lang' => $langKey,
                        'flag' => $flagMap[$langKey] ?? '🏳️',
                        'url_old' => $urlOld,
                        'url_new' => $urlNew,
                        'error' => $e->getMessage(),
                        'rows' => [],
                        'summary' => [],
                    ];
                }
            }
        }

        return view('customTools.rawText.site-text-side-by-side', [
            'input' => $input,
            'results' => $results,
        ]);
    }

    private function fetchHtml(string $url): string
    {
        $response = Http::timeout(60)
            ->connectTimeout(20)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; LaravelTextSideBySide/3.2)',
                'Accept-Language' => '*',
                'Cache-Control' => 'no-cache',
            ])
            ->withoutVerifying()
            ->get($url);

        if (!$response->successful()) {
            throw new \Exception("HTTP {$response->status()} em {$url}");
        }

        return $response->body();
    }

    private function extractAllVisibleLikeText(string $html): string
    {
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);

        if (!$loaded) {
            throw new \Exception('Falha ao processar HTML.');
        }

        $xpath = new DOMXPath($dom);
        $chunks = [];

        $textNodes = $xpath->query('//body//text()[not(ancestor::script) and not(ancestor::style) and not(ancestor::noscript) and not(ancestor::svg)]');
        if ($textNodes) {
            foreach ($textNodes as $node) {
                $value = html_entity_decode($node->nodeValue ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $value = preg_replace('/\s+/u', ' ', $value ?? '');
                $value = trim($value ?? '');
                if ($value !== '') {
                    $chunks[] = $value;
                }
            }
        }

        $attributeQueries = [
            '//body//*[@placeholder]/@placeholder',
            '//body//*[@title]/@title',
            '//body//*[@aria-label]/@aria-label',
            '//body//*[@alt]/@alt',
            '//body//input[@value]/@value',
            '//body//button[@value]/@value',
            '//body//option/@label',
            '//body//*[@data-placeholder]/@data-placeholder',
        ];

        foreach ($attributeQueries as $query) {
            $nodes = $xpath->query($query);
            if ($nodes) {
                foreach ($nodes as $node) {
                    $value = html_entity_decode($node->nodeValue ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $value = preg_replace('/\s+/u', ' ', $value ?? '');
                    $value = trim($value ?? '');
                    if ($value !== '') {
                        $chunks[] = $value;
                    }
                }
            }
        }

        $scriptNodes = $xpath->query('//script/text()');
        if ($scriptNodes) {
            foreach ($scriptNodes as $scriptNode) {
                $script = $scriptNode->nodeValue ?? '';
                if ($script === '') {
                    continue;
                }

                $patterns = [
                    '/\balert\s*\(\s*([\'"])(.*?)\1\s*\)/isu',
                    '/\bconfirm\s*\(\s*([\'"])(.*?)\1\s*\)/isu',
                    '/\bprompt\s*\(\s*([\'"])(.*?)\1\s*(?:,|\))/isu',
                ];

                foreach ($patterns as $pattern) {
                    if (preg_match_all($pattern, $script, $matches)) {
                        foreach (($matches[2] ?? []) as $value) {
                            $value = stripcslashes($value);
                            $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            $value = preg_replace('/\s+/u', ' ', $value ?? '');
                            $value = trim($value ?? '');
                            if ($value !== '') {
                                $chunks[] = $value;
                            }
                        }
                    }
                }
            }
        }

        $seen = [];
        $filtered = [];

        foreach ($chunks as $chunk) {
            $chunk = preg_replace('/\s+/u', ' ', $chunk ?? '');
            $chunk = trim($chunk ?? '');

            if ($chunk === '') {
                continue;
            }

            $key = mb_strtolower($chunk, 'UTF-8');
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $filtered[] = $chunk;
        }

        return implode("\n", $filtered);
    }

    private function normalizeForMatch(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = preg_replace('/[[:punct:]]+/u', '', $text);
        return trim($text);
    }

    private function splitIntoBlocks(string $text): array
    {
        $text = preg_replace('/\r\n|\r/u', "\n", $text);

        $paragraphs = preg_split('/\n+/u', $text);
        $paragraphs = array_map('trim', $paragraphs ?: []);
        $paragraphs = array_filter($paragraphs, fn ($v) => $v !== '');

        $blocks = [];

        foreach ($paragraphs as $paragraph) {
            if (mb_strlen($paragraph, 'UTF-8') > 350) {
                $sentences = preg_split('/(?<=[\.\!\?\:\;])\s+/u', $paragraph);
                $sentences = array_map('trim', $sentences ?: []);
                $sentences = array_filter($sentences, fn ($v) => $v !== '');

                foreach ($sentences as $sentence) {
                    if (mb_strlen($sentence, 'UTF-8') >= 2) {
                        $blocks[] = $sentence;
                    }
                }
            } else {
                $blocks[] = $paragraph;
            }
        }

        return array_values($blocks);
    }

    private function similarity(string $a, string $b): float
    {
        $na = $this->normalizeForMatch($a);
        $nb = $this->normalizeForMatch($b);

        if ($na === '' && $nb === '') {
            return 100.0;
        }

        if ($na === '' || $nb === '') {
            return 0.0;
        }

        similar_text($na, $nb, $percent);
        return round($percent, 2);
    }

    private function alignBlocks(array $leftBlocks, array $rightBlocks): array
    {
        $rows = [];
        $usedRight = [];

        foreach ($leftBlocks as $leftText) {
            $bestIndex = null;
            $bestScore = 0.0;

            foreach ($rightBlocks as $rightIndex => $rightText) {
                if (isset($usedRight[$rightIndex])) {
                    continue;
                }

                $score = $this->similarity($leftText, $rightText);

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestIndex = $rightIndex;
                }
            }

            if ($bestIndex !== null && $bestScore >= 55) {
                $usedRight[$bestIndex] = true;

                $type = 'different';
                if ($bestScore >= 98) {
                    $type = 'same';
                } elseif ($bestScore >= 75) {
                    $type = 'similar';
                }

                $rows[] = [
                    'left' => $leftText,
                    'right' => $rightBlocks[$bestIndex],
                    'score' => $bestScore,
                    'type' => $type,
                ];
            } else {
                $rows[] = [
                    'left' => $leftText,
                    'right' => '',
                    'score' => 0,
                    'type' => 'left_only',
                ];
            }
        }

        foreach ($rightBlocks as $rightIndex => $rightText) {
            if (!isset($usedRight[$rightIndex])) {
                $rows[] = [
                    'left' => '',
                    'right' => $rightText,
                    'score' => 0,
                    'type' => 'right_only',
                ];
            }
        }

        return $rows;
    }

    private function wordDiffHtml(string $leftText, string $rightText): array
    {
        $tokenize = function (string $text): array {
            preg_match_all('/[^\s]+|\s+/u', $text, $matches);
            return $matches[0] ?? [];
        };

        $normalizeWord = function (string $token): string {
            if (preg_match('/^\s+$/u', $token)) {
                return '';
            }

            $token = mb_strtolower($token, 'UTF-8');
            $token = preg_replace('/[[:punct:]]+/u', '', $token);
            return trim($token);
        };

        $leftTokens = $tokenize($leftText);
        $rightTokens = $tokenize($rightText);

        $leftWords = [];
        foreach ($leftTokens as $idx => $token) {
            if (!preg_match('/^\s+$/u', $token)) {
                $leftWords[] = ['idx' => $idx, 'norm' => $normalizeWord($token)];
            }
        }

        $rightWords = [];
        foreach ($rightTokens as $idx => $token) {
            if (!preg_match('/^\s+$/u', $token)) {
                $rightWords[] = ['idx' => $idx, 'norm' => $normalizeWord($token)];
            }
        }

        $m = count($leftWords);
        $n = count($rightWords);
        $lcs = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));

        for ($i = $m - 1; $i >= 0; $i--) {
            for ($j = $n - 1; $j >= 0; $j--) {
                if (($leftWords[$i]['norm'] ?? '') !== '' && ($leftWords[$i]['norm'] ?? '') === ($rightWords[$j]['norm'] ?? '')) {
                    $lcs[$i][$j] = 1 + $lcs[$i + 1][$j + 1];
                } else {
                    $lcs[$i][$j] = max($lcs[$i + 1][$j], $lcs[$i][$j + 1]);
                }
            }
        }

        $leftMatchedWordIndexes = [];
        $rightMatchedWordIndexes = [];
        $i = 0;
        $j = 0;

        while ($i < $m && $j < $n) {
            if (($leftWords[$i]['norm'] ?? '') !== '' && ($leftWords[$i]['norm'] ?? '') === ($rightWords[$j]['norm'] ?? '')) {
                $leftMatchedWordIndexes[$leftWords[$i]['idx']] = true;
                $rightMatchedWordIndexes[$rightWords[$j]['idx']] = true;
                $i++;
                $j++;
            } elseif ($lcs[$i + 1][$j] >= $lcs[$i][$j + 1]) {
                $i++;
            } else {
                $j++;
            }
        }

        $render = function (array $tokens, array $matchedWordIndexes, string $class): string {
            $html = '';

            foreach ($tokens as $idx => $token) {
                if (preg_match('/^\s+$/u', $token)) {
                    $html .= e($token);
                    continue;
                }

                if (isset($matchedWordIndexes[$idx])) {
                    $html .= e($token);
                } else {
                    $html .= '<span class="' . $class . '">' . e($token) . '</span>';
                }
            }

            return $html;
        };

        return [
            $render($leftTokens, $leftMatchedWordIndexes, 'diff-removed'),
            $render($rightTokens, $rightMatchedWordIndexes, 'diff-added'),
        ];
    }
}
