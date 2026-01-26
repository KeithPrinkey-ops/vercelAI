<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * StreamChatController
 *
 * PURPOSE
 * -------
 * This controller demonstrates how to stream AI responses
 * from a Laravel backend to a frontend UI in real time.
 *
 * IMPORTANT CONCEPT
 * -----------------
 * Even if OpenAI returns an HTTP error (e.g. 429 – no quota),
 * the browser will STILL receive a 200 OK response.
 *
 * Why?
 * Because streaming requires the HTTP connection to remain open.
 * Errors are surfaced *inside the stream*, not as HTTP status codes.
 */
class StreamChatController extends Controller
{
    /**
     * Handle the incoming chat request.
     *
     * This controller is intentionally INVOKABLE (__invoke)
     * to keep the mental model simple for teaching.
     */
    public function __invoke(Request $request): StreamedResponse
    {
        return new StreamedResponse(function () use ($request) {

            /**
             * OpenAI Chat Completions endpoint
             */
            $url = 'https://api.openai.com/v1/chat/completions';

            /**
             * Payload forwarded directly from the frontend.
             * We do NOT reshape messages here — the UI controls that.
             */
            $payload = [
                'model' => 'gpt-4o',
                'messages' => $request->input('messages', []),
                'stream' => true,
            ];

            /**
             * Required OpenAI headers
             */
            $headers = [
                'Authorization: Bearer '.config('services.openai.key'),
                'Content-Type: application/json',
            ];

            /**
             * Track OpenAI’s HTTP status code.
             *
             * NOTE:
             * This status code is NOT sent to the browser.
             * The browser will always see HTTP 200 for streaming.
             */
            $openAiStatusCode = null;

            /**
             * Initialize cURL
             */
            $ch = curl_init($url);

            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => $headers,

                // STREAMING SETTINGS (CRITICAL)
                CURLOPT_RETURNTRANSFER => false, // Do not buffer
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_TIMEOUT => 0,
            ]);

            /**
             * Capture OpenAI HTTP status code from response headers
             */
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $header) use (&$openAiStatusCode) {
                if (preg_match('/HTTP\/\d+\.\d+\s+(\d+)/', $header, $matches)) {
                    $openAiStatusCode = (int) $matches[1];
                }

                return strlen($header);
            });

            /**
             * Handle streamed chunks from OpenAI
             */
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) {

                $line = trim($chunk);

                // Ignore stream terminator
                if ($line === 'data: [DONE]') {
                    return strlen($chunk);
                }

                /**
                 * OpenAI streams data in this format:
                 * data: { JSON }
                 */
                if (str_starts_with($line, 'data: ')) {
                    $json = json_decode(substr($line, 6), true);

                    $token = $json['choices'][0]['delta']['content'] ?? null;

                    if ($token !== null) {
                        /**
                         * 🚨 VERCEL AI SDK STREAM PROTOCOL 🚨
                         *
                         * Each chunk MUST:
                         * - Be prefixed with "0:"
                         * - End with a newline
                         *
                         * This tells the frontend:
                         * "Append this token to the assistant message"
                         */
                        echo '0:'.json_encode($token)."\n";

                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                        flush();
                    }
                }

                return strlen($chunk);
            });

            /**
             * Execute the OpenAI request
             */
            curl_exec($ch);

            /**
             * If OpenAI returned 429 (no quota),
             * we STREAM an error message instead of failing the request.
             *
             * IMPORTANT:
             * The browser still receives HTTP 200.
             * The error lives inside the stream.
             */
            if ($openAiStatusCode === 429) {
                $message = '⚠️ Error code: 429: No tokens available. '
                    .'This demonstrates how streaming UIs can gracefully '
                    .'handle upstream AI failures without breaking the connection.';

                foreach (explode(' ', $message) as $word) {
                    echo '0:'.json_encode($word.' ')."\n";

                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();

                    // Slow it down so streaming is visible
                    usleep(120_000);
                }
            }

            curl_close($ch);

        }, 200, [
            /**
             * RESPONSE HEADERS (REQUIRED FOR STREAMING)
             */
            'Content-Type' => 'text/event-stream; charset=utf-8',

            /**
             * Required by the Vercel AI SDK protocol
             */
            'X-Vercel-AI-Data-Stream' => 'v1',

            /**
             * Prevent buffering by browsers or proxies
             */
            'Cache-Control' => 'no-cache, no-transform',

            /**
             * Keep the connection open
             */
            'Connection' => 'keep-alive',
        ]);
    }
}
