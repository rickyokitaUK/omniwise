<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use Intervention\Image\Laravel\Facades\Image as ImageFacade; // Intervention v3 facade
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ChartAnalysisController extends Controller
{
      public function analyze(Request $request)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $results = [];

        foreach ($request->file('images') as $image) {
            $path = $image->store('charts', 'public');
            $imagePath = storage_path("app/public/{$path}");

              // Step 1: Describe the image using LLaVA
           // $chartSummary = $this->callLLaVA($imagePath);

            // Step 2: Pass summary to Mistral
            $decision = $this->callMistral($imagePath);

            $results[] = [
                'file' => basename($path),
                'text' => $decision ?? 'Error analyzing chart',
            ];
        }

        return view('upload', ['results' => $results]);
    }

      /**
     * Call LLaVA to describe the chart image.
     */
    private function callLLaVA(string $imagePath): ?string
    {
ini_set('max_execution_time', 120);
        
        $endpoint = 'http://localhost:1234/api/v0/chat/completions';

        $imageData = base64_encode(file_get_contents($imagePath));
        $imageMime = mime_content_type($imagePath);

        $payload = [
            'model' => 'llava:7b', // Make sure this matches the vision model loaded in LM Studio
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => 'Please describe the chart image.'],
                        ['type' => 'image_url', 'image_url' => [
                            'url' => "data:$imageMime;base64,$imageData"
                        ]]
                    ]
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1024,
            'stream' => false
        ];


        try {
            $response = Http::timeout(180)->post($endpoint, $payload)->throw();
            Log::debug('LLaVA raw response', ['response' => $response->body()]);

            $data = $response->json();
            Log::info('LLaVA image summary response', $data);
            return $data['choices'][0]['message']['content'] ?? null;
        } catch (\Exception $e) {
    Log::error('LLaVA request failed', [
        'message' => $e->getMessage(),
        'image_size_kb' => round(filesize($imagePath) / 1024),
        'payload_size_kb' => round(strlen(json_encode($payload)) / 1024),
    ]);
    return null;
}
    }


    private function callLMStudioTest(?string $imagePath)
{
    $endpoint = 'http://localhost:1234/api/v0/chat/completions';

    $payload = [
        'model' => 'mistralai/mistral-small-3.2',
      'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
            ['role' => 'user', 'content' => 'What is 2 + 2?']
        ],
        'temperature' => 0.7,
        'max_tokens' => 16,
        'stream' => false,
    ];

    try {
        $response = Http::timeout(60)->post($endpoint, $payload)->throw();
        $data = $response->json();
        Log::info('LLM response', $data);
        return $data['choices'][0]['message']['content'] ?? null;
    } catch (\Exception $e) {
        Log::error('LM Studio request failed', ['exception' => $e]);
        return null;
    }
}

    private function callMistral(?string $imagePath)
    {
        ini_set('max_execution_time', 300);

        $endpoint = config('services.lmstudio.url');
        $imageRaw = base64_encode(file_get_contents($imagePath));
            $imageMime = mime_content_type($imagePath);
            $imageData = "data:$imageMime;base64," . $imageRaw;

            //  Load the system prompt from an external file
            $path = 'C:/prompts/sol_system_prompt.txt';

            if (File::exists($path)) {
                $systemPrompt = trim(File::get($path));
               // echo $systemPrompt;
            } else {
                echo "File not found.";
            }
            //$systemPrompt = trim(Storage::disk('local')->get('prompts/sol_system_prompt.txt'));
            $userPrompt = " Show results only, no explanations.";


        $payload = [
            'model' => 'mistralai/mistral-small-3.2',
            'messages' => [
                ['role' => 'user',
                    'content' => [
                            ['type' => 'text', 'text' => $userPrompt],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => 'data:image/jpeg;base64,' . $imageRaw
                                    //'url' => 'https://invst.ly/1bvrj7'
                                ]
                            ]
                        ]
                ],
                ['role' => 'system', 'content' =>  $systemPrompt],
            ],
            'temperature' => 0.7,
            'max_tokens' => 4096,
            'stream' => false,
        ];

        // Log debugging info
        Log::info('Mistral API Debug', [
            'endpoint'     => $endpoint,
            'systemPrompt' => $systemPrompt,
            'payload'      => $payload
        ]);


        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(300)->post($endpoint, $payload)->throw();
            $data = $response->json();
            Log::info('LLM response', $data);
            return $data['choices'][0]['message']['content'] ?? null;
        } catch (\Exception $e) {
            Log::error('LM Studio request failed', ['exception' => $e]);
            return null;
        }
    }


    public function showForm()
    {
        return view('upload'); // make sure upload.blade.php exists in resources/views
    }

    public function analyzeLink(Request $request)
{
    $validated = $request->validate([
        'url' => ['required', 'url']
    ]);

    $url = $validated['url'];

    // Ensure charts directory exists
    $dir = storage_path('app/public/charts');
    if (!File::exists($dir)) {
        File::makeDirectory($dir, 0755, true);
    }

    $basename = 'chart_' . Str::uuid() . '.png';
    $savePath = $dir . '/' . $basename;

    // Path to Puppeteer script
    $nodeScript = base_path('resources/js/fetch-image.js');

    // Run Puppeteer to fetch the image
    $cmd = sprintf(
        'node "%s" "%s" "%s"',
        $nodeScript,
        $url,
        $savePath
    );

    exec($cmd . ' 2>&1', $output, $returnVar);

    if ($returnVar !== 0 || !file_exists($savePath)) {
        return response()->json([
            'error'  => 'Failed to capture image.'
        ], 500, [], JSON_UNESCAPED_SLASHES);
    }

    // Call Mistral directly with the image path
    $decision = $this->callMistral($savePath);

    // Build public path
    $publicPath = url('storage/charts/' . $basename);

    // Return clean JSON with unescaped slashes
    return response()->json([
        'input_url'     => $url,
        'absolute_path' => $savePath,
        'public_path'   => $publicPath,
        'decision'      => $decision ?? 'Error analyzing chart'
    ], 200, [], JSON_UNESCAPED_SLASHES);
}



}
