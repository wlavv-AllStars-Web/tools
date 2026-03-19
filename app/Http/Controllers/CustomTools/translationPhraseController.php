<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

use App\Http\Controllers\Controller;



class translationPhraseController extends Controller
{
    protected string $filePath;

    public function __construct()
    {
        // Adjust path as needed (e.g., public/js/phrases.js or resources/js/phrases.js)
        $this->filePath = public_path('trackingTranslations/phrases.js');
    }

    // Get current phrases as JSON (for frontend editing)
    public function index()
    {
        $content = $this->readPhrases();
        return response()->json($content);
    }
    
    public function create()
    {
        $phrases = $this->readPhrases();
        return view('customTools.trackingTranslations.edit', compact('phrases'));
    }

    // Save updated phrases
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fr' => 'nullable|array',
            'en' => 'nullable|array',
            'es' => 'nullable|array',
        ]);

        // Ensure all languages exist in the structure
        $phrases = [
            'fr' => [],
            'en' => [],
            'es' => [],
        ];

        foreach (['fr', 'en', 'es'] as $lang) {
            if (isset($validated[$lang]) && is_array($validated[$lang])) {
                // Remove empty keys
                $phrases[$lang] = array_filter($validated[$lang], function ($value) {
                    return $value !== null && $value !== '';
                });
            }
        }

        $this->writePhrases($phrases);

        return response()->json(['message' => 'Translations saved successfully.']);
    }

    protected function readPhrases(): array
    {
        if (!File::exists($this->filePath)) {
            return ['fr' => [], 'en' => [], 'es' => []];
        }
    
        $jsContent = File::get($this->filePath);
    
        // Extract everything between "const customPhraseReplacements = {" and "};"
        if (!preg_match('/const\s+customPhraseReplacements\s*=\s*(\{[\s\S]*?\});?\s*$/', $jsContent, $matches)) {
            return ['fr' => [], 'en' => [], 'es' => []];
        }
    
        $jsObject = $matches[1];
    
        // Step 1: Remove all comments (// and /* */)
        $jsObject = preg_replace('#//.*#m', '', $jsObject);
        $jsObject = preg_replace('#/\*.*?\*/#s', '', $jsObject);
    
        // Step 2: Remove trailing commas before closing braces
        $jsObject = preg_replace('/,\s*([}\]])/', '$1', $jsObject);
    
        // Step 3: Quote unquoted keys (like fr:, en:, "key": is already fine)
        // This handles keys that are bare words (no quotes)
        $jsObject = preg_replace('/([{,]\s*)([a-zA-Z$_][a-zA-Z0-9$_]*)\s*:/', '$1"$2":', $jsObject);
    
        // Step 4: Ensure it's valid JSON
        $data = json_decode($jsObject, true);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Log or handle error if needed
            return ['fr' => [], 'en' => [], 'es' => []];
        }
    
        return [
            'fr' => isset($data['fr']) && is_array($data['fr']) ? $data['fr'] : [],
            'en' => isset($data['en']) && is_array($data['en']) ? $data['en'] : [],
            'es' => isset($data['es']) && is_array($data['es']) ? $data['es'] : [],
        ];
    }

    protected function writePhrases(array $phrases): void
    {
        $lines = [
            'const customPhraseReplacements = {',
        ];

        foreach (['fr', 'en', 'es'] as $lang) {
            $lines[] = "    {$lang}: {";
            if (!empty($phrases[$lang])) {
                foreach ($phrases[$lang] as $key => $value) {
                    // Escape quotes in values/keys
                    $safeKey = addcslashes($key, '"\\');
                    $safeValue = addcslashes($value, '"\\');
                    $lines[] = "        \"{$safeKey}\": \"{$safeValue}\",";
                }
            }

            if ($lang === 'fr') {
                $lines[] = "        // Add more French replacements here";
            } elseif ($lang === 'en') {
                $lines[] = "        // Add English corrections if needed";
            } else {
                $lines[] = "        // Add Spanish corrections if needed";
            }

            $lines[] = "    },";
        }

        $lines[] = "};";

        File::put($this->filePath, implode("\n", $lines) . "\n");
    }
}