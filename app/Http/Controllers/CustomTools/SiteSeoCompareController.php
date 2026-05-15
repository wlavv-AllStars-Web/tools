<?php

namespace App\Http\Controllers\CustomTools;

use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class SiteSeoCompareController extends Controller
{
    public function index()
    {
        return view('customTools.SiteSeo.site-seo-compare', [
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
            'de' => '🇩🇪',
        ];

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
                $responseOld = $this->httpClient($urlOld);
                $responseNew = $this->httpClient($urlNew);

                if (!$responseOld->successful()) {
                    throw new \Exception("Old: HTTP {$responseOld->status()} em {$urlOld}");
                }

                if (!$responseNew->successful()) {
                    throw new \Exception("New: HTTP {$responseNew->status()} em {$urlNew}");
                }

                $seoOld = $this->extractSeo($responseOld->body(), $urlOld, $responseOld);
                $seoNew = $this->extractSeo($responseNew->body(), $urlNew, $responseNew);

                $oldWarnings = $this->buildWarnings($seoOld);
                $newWarnings = $this->buildWarnings($seoNew);

                $rows = $this->buildRows($seoOld, $seoNew, $oldWarnings, $newWarnings);

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
                    'rows' => $rows,
                    'summary' => $summary,
                    'error' => '',
                ];
            } catch (\Throwable $e) {
                $results[] = [
                    'lang' => $langKey,
                    'flag' => $flagMap[$langKey] ?? '🏳️',
                    'url_old' => $urlOld,
                    'url_new' => $urlNew,
                    'rows' => [],
                    'summary' => [],
                    'error' => $e->getMessage(),
                ];
            }
        }

        return view('customTools.SiteSeo.site-seo-compare', [
            'input' => $input,
            'results' => $results,
        ]);
    }

    private function httpClient(string $url)
    {
        return Http::timeout(90)
            ->connectTimeout(25)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; LaravelSeoCompareAdvanced/1.1)',
                'Accept-Language' => '*',
                'Cache-Control' => 'no-cache',
            ])
            ->withoutVerifying()
            ->withOptions([
                'allow_redirects' => [
                    'track_redirects' => true,
                    'max' => 8,
                ],
            ])
            ->get($url);
    }

    private function normalizeText(?string $value): string
    {
        $value = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', $value ?? '');
        return trim((string) $value);
    }

    private function normalizeUrl(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $value = preg_replace('/#.*$/', '', $value);

        return rtrim($value, '/');
    }

    private function absUrl(string $baseUrl, string $relativeUrl): string
    {
        $relativeUrl = trim($relativeUrl);

        if ($relativeUrl === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $relativeUrl)) {
            return $this->normalizeUrl($relativeUrl);
        }

        if (str_starts_with($relativeUrl, '//')) {
            $scheme = parse_url($baseUrl, PHP_URL_SCHEME) ?: 'https';
            return $this->normalizeUrl($scheme . ':' . $relativeUrl);
        }

        $parts = parse_url($baseUrl);
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $basePath = $parts['path'] ?? '/';

        if (str_starts_with($relativeUrl, '/')) {
            return $this->normalizeUrl($scheme . '://' . $host . $port . $relativeUrl);
        }

        $dir = preg_replace('#/[^/]*$#', '/', $basePath);
        $path = $dir . $relativeUrl;

        $segments = [];
        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($segments);
                continue;
            }
            $segments[] = $segment;
        }

        return $this->normalizeUrl($scheme . '://' . $host . $port . '/' . implode('/', $segments));
    }

    private function similarity(?string $a, ?string $b): float
    {
        $a = mb_strtolower($this->normalizeText($a), 'UTF-8');
        $b = mb_strtolower($this->normalizeText($b), 'UTF-8');

        if ($a === '' && $b === '') {
            return 100.0;
        }

        if ($a === '' || $b === '') {
            return 0.0;
        }

        similar_text($a, $b, $percent);

        return round($percent, 2);
    }

    private function extractSeo(string $html, string $url, $httpResponse): array
    {
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);

        if (!$loaded) {
            throw new \Exception('Falha ao processar HTML.');
        }

        $xpath = new DOMXPath($dom);

        $firstValue = function (string $query) use ($xpath): string {
            $nodes = $xpath->query($query);
            if (!$nodes || $nodes->length === 0) {
                return '';
            }
            return $this->normalizeText($nodes->item(0)?->nodeValue ?? '');
        };

        $attrValues = function (string $query, ?callable $transform = null) use ($xpath) {
            $nodes = $xpath->query($query);
            $values = [];

            if ($nodes) {
                foreach ($nodes as $node) {
                    $v = $this->normalizeText($node->nodeValue ?? '');
                    if ($transform) {
                        $v = $transform($v);
                    }
                    if ($v !== '') {
                        $values[] = $v;
                    }
                }
            }

            return array_values(array_unique($values));
        };

        $metaByName = function (string $name) use ($xpath): string {
            $query = '//meta[translate(@name,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz")="' . $name . '"]/@content';
            $nodes = $xpath->query($query);
            if (!$nodes || $nodes->length === 0) {
                return '';
            }
            return $this->normalizeText($nodes->item(0)?->nodeValue ?? '');
        };

        $metaByProperty = function (string $property) use ($xpath): string {
            $query = '//meta[@property="' . $property . '"]/@content';
            $nodes = $xpath->query($query);
            if (!$nodes || $nodes->length === 0) {
                return '';
            }
            return $this->normalizeText($nodes->item(0)?->nodeValue ?? '');
        };

        $metaAllByProperty = function (string $property) use ($xpath): array {
            $query = '//meta[@property="' . $property . '"]/@content';
            $nodes = $xpath->query($query);
            $values = [];

            if ($nodes) {
                foreach ($nodes as $node) {
                    $v = $this->normalizeText($node->nodeValue ?? '');
                    if ($v !== '') {
                        $values[] = $v;
                    }
                }
            }

            return array_values(array_unique($values));
        };

        $linkRel = function (string $rel) use ($xpath, $url): string {
            $query = '//link[contains(concat(" ", normalize-space(@rel), " "), " ' . $rel . ' ")]/@href';
            $nodes = $xpath->query($query);
            if (!$nodes || $nodes->length === 0) {
                return '';
            }
            return $this->normalizeUrl($this->absUrl($url, (string) ($nodes->item(0)?->nodeValue ?? '')));
        };

        $title = $firstValue('//title/text()');
        $description = $metaByName('description');
        $robots = $metaByName('robots');
        $canonical = $linkRel('canonical');
        $metaKeywords = $metaByName('keywords');
        $viewport = $metaByName('viewport');
        $author = $metaByName('author');

        $h1s = $attrValues('//h1//text()');
        $h2s = $attrValues('//h2//text()');
        $h3s = $attrValues('//h3//text()');

        $ogTitle = $metaByProperty('og:title');
        $ogDescription = $metaByProperty('og:description');
        $ogImageValues = $metaAllByProperty('og:image');
        $ogImage = $ogImageValues[0] ?? '';
        $ogType = $metaByProperty('og:type');
        $ogUrl = $metaByProperty('og:url');
        $ogSiteName = $metaByProperty('og:site_name');
        $twitterCard = $metaByName('twitter:card');
        $twitterTitle = $metaByName('twitter:title');
        $twitterDescription = $metaByName('twitter:description');
        $twitterImage = $metaByName('twitter:image');

        $hreflangNodes = $xpath->query('//link[@rel="alternate"][@hreflang]');
        $hreflangs = [];
        if ($hreflangNodes) {
            foreach ($hreflangNodes as $node) {
                $lang = mb_strtolower(trim((string) $node->attributes?->getNamedItem('hreflang')?->nodeValue), 'UTF-8');
                $href = $this->normalizeUrl($this->absUrl($url, (string) $node->attributes?->getNamedItem('href')?->nodeValue));
                if ($lang !== '' && $href !== '') {
                    $hreflangs[] = $lang . ' => ' . $href;
                }
            }
        }

        $jsonLdCount = 0;
        $jsonLdTypes = [];
        $jsonLdNodes = $xpath->query('//script[@type="application/ld+json"]');

        if ($jsonLdNodes) {
            $jsonLdCount = $jsonLdNodes->length;

            foreach ($jsonLdNodes as $node) {
                $raw = trim((string) $node->nodeValue);
                if ($raw === '') {
                    continue;
                }

                $decoded = json_decode($raw, true);

                $collectTypes = function ($item) use (&$collectTypes, &$jsonLdTypes) {
                    if (is_array($item)) {
                        if (isset($item['@type'])) {
                            if (is_array($item['@type'])) {
                                foreach ($item['@type'] as $t) {
                                    $jsonLdTypes[] = (string) $t;
                                }
                            } else {
                                $jsonLdTypes[] = (string) $item['@type'];
                            }
                        }

                        foreach ($item as $sub) {
                            $collectTypes($sub);
                        }
                    }
                };

                $collectTypes($decoded);
            }
        }

        $imgNodes = $xpath->query('//img');
        $imgCount = 0;
        $imgMissingAlt = 0;
        $imgEmptyAlt = 0;
        $imgWithAlt = 0;
        $imgLazy = 0;
        $imgSrcset = 0;
        $imgMissingWidth = 0;
        $imgMissingHeight = 0;
        $firstImage = '';
        $imageSamples = [];

        if ($imgNodes) {
            $imgCount = $imgNodes->length;

            foreach ($imgNodes as $idx => $img) {
                $src = trim((string) $img->attributes?->getNamedItem('src')?->nodeValue);
                $dataSrc = trim((string) $img->attributes?->getNamedItem('data-src')?->nodeValue);
                $finalSrc = $src !== '' ? $src : $dataSrc;
                $finalSrc = $finalSrc !== '' ? $this->normalizeUrl($this->absUrl($url, $finalSrc)) : '';

                $alt = (string) $img->attributes?->getNamedItem('alt')?->nodeValue;
                $loading = mb_strtolower(trim((string) $img->attributes?->getNamedItem('loading')?->nodeValue), 'UTF-8');
                $srcset = trim((string) $img->attributes?->getNamedItem('srcset')?->nodeValue);
                $width = trim((string) $img->attributes?->getNamedItem('width')?->nodeValue);
                $height = trim((string) $img->attributes?->getNamedItem('height')?->nodeValue);

                if ($alt === '') {
                    if ($img->attributes?->getNamedItem('alt')) {
                        $imgEmptyAlt++;
                    } else {
                        $imgMissingAlt++;
                    }
                } else {
                    $imgWithAlt++;
                }

                if ($loading === 'lazy') {
                    $imgLazy++;
                }

                if ($srcset !== '') {
                    $imgSrcset++;
                }

                if ($width === '') {
                    $imgMissingWidth++;
                }

                if ($height === '') {
                    $imgMissingHeight++;
                }

                if ($idx === 0 && $finalSrc !== '') {
                    $firstImage = $finalSrc;
                }

                if ($idx < 12) {
                    $imageSamples[] = trim(($finalSrc !== '' ? $finalSrc : '[sem src]') . ' | alt=' . ($alt !== '' ? $this->normalizeText($alt) : '[vazio]'));
                }
            }
        }

        $favicon = $linkRel('icon');
        if ($favicon === '') {
            $favicon = $linkRel('shortcut icon');
        }

        $lang = mb_strtolower($this->normalizeText((string) ($xpath->query('/html/@lang')?->item(0)?->nodeValue ?? '')), 'UTF-8');
        $metaCharset = mb_strtolower($this->normalizeText((string) ($xpath->query('//meta[@charset]/@charset')?->item(0)?->nodeValue ?? '')), 'UTF-8');

        $bodyText = $this->normalizeText($firstValue('//body'));
        $wordCount = $bodyText === '' ? 0 : count(array_filter(preg_split('/\s+/u', $bodyText)));

        $responseHeaders = method_exists($httpResponse, 'headers') ? $httpResponse->headers() : [];
        $statusCode = method_exists($httpResponse, 'status') ? (string) $httpResponse->status() : '';
        $finalUrl = method_exists($httpResponse, 'effectiveUri') && $httpResponse->effectiveUri()
            ? $this->normalizeUrl((string) $httpResponse->effectiveUri())
            : $this->normalizeUrl($url);

        $xRobotsTag = '';
        if (is_array($responseHeaders)) {
            foreach ($responseHeaders as $header => $values) {
                if (mb_strtolower((string) $header, 'UTF-8') === 'x-robots-tag') {
                    $xRobotsTag = is_array($values) ? implode(' | ', $values) : (string) $values;
                }
            }
        }

        return [
            'url' => $this->normalizeUrl($url),
            'final_url' => $finalUrl,
            'status_code' => $statusCode,
            'html_lang' => $lang,
            'charset' => $metaCharset,
            'title' => $title,
            'title_length' => (string) mb_strlen($title, 'UTF-8'),
            'description' => $description,
            'description_length' => (string) mb_strlen($description, 'UTF-8'),
            'robots' => $robots,
            'x_robots_tag' => $this->normalizeText($xRobotsTag),
            'canonical' => $canonical,
            'meta_keywords' => $metaKeywords,
            'viewport' => $viewport,
            'author' => $author,
            'indexability' => str_contains(mb_strtolower($robots . ' ' . $xRobotsTag, 'UTF-8'), 'noindex') ? 'noindex' : 'indexable',
            'h1' => implode(' | ', $h1s),
            'h1_count' => (string) count($h1s),
            'h2' => implode(' | ', array_slice($h2s, 0, 12)),
            'h2_count' => (string) count($h2s),
            'h3_count' => (string) count($h3s),
            'word_count' => (string) $wordCount,
            'og_title' => $ogTitle,
            'og_description' => $ogDescription,
            'og_type' => $ogType,
            'og_url' => $this->normalizeUrl($ogUrl),
            'og_site_name' => $ogSiteName,
            'og_image' => $this->normalizeUrl($this->absUrl($url, $ogImage)),
            'og_image_count' => (string) count($ogImageValues),
            'twitter_card' => $twitterCard,
            'twitter_title' => $twitterTitle,
            'twitter_description' => $twitterDescription,
            'twitter_image' => $this->normalizeUrl($this->absUrl($url, $twitterImage)),
            'hreflang' => implode(' | ', $hreflangs),
            'hreflang_count' => (string) count($hreflangs),
            'structured_data_count' => (string) $jsonLdCount,
            'structured_data_types' => implode(' | ', array_values(array_unique(array_filter($jsonLdTypes)))),
            'favicon' => $favicon,
            'img_count' => (string) $imgCount,
            'img_with_alt' => (string) $imgWithAlt,
            'img_missing_alt' => (string) $imgMissingAlt,
            'img_empty_alt' => (string) $imgEmptyAlt,
            'img_lazy' => (string) $imgLazy,
            'img_srcset' => (string) $imgSrcset,
            'img_missing_width' => (string) $imgMissingWidth,
            'img_missing_height' => (string) $imgMissingHeight,
            'first_image' => $firstImage,
            'image_samples' => implode("\n", $imageSamples),
        ];
    }

    private function buildWarnings(array $seo): array
    {
        $warnings = [];

        $titleLength = (int) ($seo['title_length'] ?? 0);
        $descLength = (int) ($seo['description_length'] ?? 0);
        $h1Count = (int) ($seo['h1_count'] ?? 0);
        $imgCount = (int) ($seo['img_count'] ?? 0);
        $imgMissingAlt = (int) ($seo['img_missing_alt'] ?? 0);
        $imgEmptyAlt = (int) ($seo['img_empty_alt'] ?? 0);
        $imgMissingWidth = (int) ($seo['img_missing_width'] ?? 0);
        $imgMissingHeight = (int) ($seo['img_missing_height'] ?? 0);

        if (($seo['status_code'] ?? '') !== '200') {
            $warnings[] = 'HTTP status diferente de 200.';
        }
        if (($seo['title'] ?? '') === '') {
            $warnings[] = 'Title em falta.';
        } elseif ($titleLength < 30) {
            $warnings[] = 'Title demasiado curto.';
        } elseif ($titleLength > 60) {
            $warnings[] = 'Title demasiado longo.';
        }

        if (($seo['description'] ?? '') === '') {
            $warnings[] = 'Meta description em falta.';
        } elseif ($descLength < 70) {
            $warnings[] = 'Meta description demasiado curta.';
        } elseif ($descLength > 160) {
            $warnings[] = 'Meta description demasiado longa.';
        }

        if (($seo['canonical'] ?? '') === '') {
            $warnings[] = 'Canonical em falta.';
        }

        if (($seo['indexability'] ?? '') === 'noindex') {
            $warnings[] = 'Página marcada como noindex.';
        }

        if ($h1Count === 0) {
            $warnings[] = 'H1 em falta.';
        } elseif ($h1Count > 1) {
            $warnings[] = 'Múltiplos H1.';
        }

        if (($seo['structured_data_count'] ?? '0') === '0') {
            $warnings[] = 'Sem dados estruturados.';
        }

        if (($seo['hreflang_count'] ?? '0') === '0') {
            $warnings[] = 'Sem hreflang.';
        }

        if (($seo['og_title'] ?? '') === '' || ($seo['og_description'] ?? '') === '') {
            $warnings[] = 'Open Graph incompleto.';
        }

        if (($seo['twitter_title'] ?? '') === '' || ($seo['twitter_description'] ?? '') === '') {
            $warnings[] = 'Twitter meta incompleto.';
        }

        if ($imgCount > 0 && ($imgMissingAlt + $imgEmptyAlt) > 0) {
            $warnings[] = 'Existem imagens sem alt ou com alt vazio.';
        }

        if ($imgCount > 0 && ($imgMissingWidth > 0 || $imgMissingHeight > 0)) {
            $warnings[] = 'Existem imagens sem width/height.';
        }

        if (($seo['favicon'] ?? '') === '') {
            $warnings[] = 'Favicon em falta.';
        }

        return $warnings;
    }

    private function buildRows(array $oldSeo, array $newSeo, array $oldWarnings, array $newWarnings): array
    {
        $fields = [
            'status_code' => 'HTTP status',
            'final_url' => 'Final URL',
            'html_lang' => 'HTML lang',
            'charset' => 'Charset',
            'title' => 'Title',
            'title_length' => 'Title length',
            'description' => 'Meta description',
            'description_length' => 'Meta description length',
            'robots' => 'Meta robots',
            'x_robots_tag' => 'X-Robots-Tag',
            'indexability' => 'Indexability',
            'canonical' => 'Canonical',
            'meta_keywords' => 'Meta keywords',
            'viewport' => 'Viewport',
            'author' => 'Author',
            'h1' => 'H1',
            'h1_count' => 'H1 count',
            'h2' => 'H2',
            'h2_count' => 'H2 count',
            'h3_count' => 'H3 count',
            'word_count' => 'Word count',
            'og_title' => 'OG title',
            'og_description' => 'OG description',
            'og_type' => 'OG type',
            'og_url' => 'OG URL',
            'og_site_name' => 'OG site name',
            'og_image' => 'OG image',
            'og_image_count' => 'OG image count',
            'twitter_card' => 'Twitter card',
            'twitter_title' => 'Twitter title',
            'twitter_description' => 'Twitter description',
            'twitter_image' => 'Twitter image',
            'hreflang' => 'Hreflang',
            'hreflang_count' => 'Hreflang count',
            'structured_data_count' => 'Structured data count',
            'structured_data_types' => 'Structured data types',
            'favicon' => 'Favicon',
            'img_count' => 'Images total',
            'img_with_alt' => 'Images with alt',
            'img_missing_alt' => 'Images missing alt',
            'img_empty_alt' => 'Images empty alt',
            'img_lazy' => 'Lazy-loaded images',
            'img_srcset' => 'Images with srcset',
            'img_missing_width' => 'Images missing width',
            'img_missing_height' => 'Images missing height',
            'first_image' => 'First image',
            'image_samples' => 'Image samples',
            'warnings' => 'SEO warnings',
        ];

        $oldSeo['warnings'] = implode("\n", $oldWarnings);
        $newSeo['warnings'] = implode("\n", $newWarnings);

        $urlStrictFields = [
            'final_url', 'canonical', 'og_url', 'og_image', 'twitter_image', 'favicon', 'first_image',
        ];

        $numericFields = [
            'status_code', 'title_length', 'description_length', 'h1_count', 'h2_count', 'h3_count',
            'word_count', 'og_image_count', 'hreflang_count', 'structured_data_count',
            'img_count', 'img_with_alt', 'img_missing_alt', 'img_empty_alt', 'img_lazy',
            'img_srcset', 'img_missing_width', 'img_missing_height',
        ];

        $rows = [];

        foreach ($fields as $key => $label) {
            $oldValue = (string) ($oldSeo[$key] ?? '');
            $newValue = (string) ($newSeo[$key] ?? '');

            if (in_array($key, $urlStrictFields, true)) {
                $score = $this->normalizeUrl($oldValue) === $this->normalizeUrl($newValue)
                    ? 100.0
                    : ($oldValue === '' && $newValue === '' ? 100.0 : 0.0);
            } elseif (in_array($key, $numericFields, true)) {
                $score = $oldValue === $newValue
                    ? 100.0
                    : (($oldValue === '' && $newValue === '') ? 100.0 : 0.0);
            } else {
                $score = $this->similarity($oldValue, $newValue);
            }

            $type = 'different';

            if ($score >= 99) {
                $type = 'same';
            } elseif ($score >= 70) {
                $type = 'similar';
            }

            if ($oldValue !== '' && $newValue === '') {
                $type = 'left_only';
            } elseif ($oldValue === '' && $newValue !== '') {
                $type = 'right_only';
            }

            $rows[] = [
                'field' => $label,
                'left' => $oldValue,
                'right' => $newValue,
                'score' => $score,
                'type' => $type,
            ];
        }

        return $rows;
    }
}
