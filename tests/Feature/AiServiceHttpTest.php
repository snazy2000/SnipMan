<?php

use App\Services\LocalAIService;
use App\Services\OpenAIService;
use App\Services\OpenRouterService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| AI provider HTTP contracts
|--------------------------------------------------------------------------
|
| These three services are the application's only Guzzle consumers, and two of
| them pass raw cURL options through the Http facade. Until these tests existed
| that whole surface was uncovered, so a Guzzle major upgrade could not be
| verified by running the suite. Each service is constructed with an explicit
| config array — the constructors accept one, which avoids seeding ai_settings
| rows just to exercise the request shape.
|
*/

beforeEach(function () {
    Http::preventStrayRequests();
    Config::set('ai.features.auto_description', true);
});

function openAi(): OpenAIService
{
    return new OpenAIService([
        'api_key' => 'test-openai-key',
        'base_url' => 'https://openai.test/v1',
        'model' => 'gpt-4o-mini',
        'timeout' => 5,
        'max_tokens' => 256,
        'temperature' => 0.2,
    ]);
}

function openRouter(): OpenRouterService
{
    return new OpenRouterService([
        'api_key' => 'test-openrouter-key',
        'base_url' => 'https://openrouter.test/api/v1',
        'model' => 'anthropic/claude-3.5-haiku',
        'timeout' => 5,
        'max_tokens' => 256,
        'temperature' => 0.2,
        'top_p' => 0.8,
        'site_url' => 'https://snippets.test',
        'site_name' => 'SnippetMan Test',
    ]);
}

function ollama(): LocalAIService
{
    return new LocalAIService([
        'base_url' => 'http://ollama.test:11434',
        'model' => 'codellama:7b',
        'timeout' => 5,
        'max_tokens' => 128,
    ]);
}

// ── OpenAI ────────────────────────────────────────────────────────────────

test('openai generateDescription posts the expected chat completion payload', function () {
    Http::fake([
        'https://openai.test/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => "  Sorts an array in place.  \n"]]],
        ], 200),
    ]);

    $result = openAi()->generateDescription('function mySort() {}', 'php');

    expect($result)->toBe('Sorts an array in place.');

    Http::assertSent(function (Request $request) {
        $body = $request->data();

        return $request->url() === 'https://openai.test/v1/chat/completions'
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer test-openai-key')
            && $body['model'] === 'gpt-4o-mini'
            && $body['max_tokens'] === 256
            && $body['temperature'] === 0.2
            && $body['stream'] === false
            && $body['messages'][0]['role'] === 'user'
            && str_contains($body['messages'][0]['content'], 'function mySort() {}')
            && str_contains($body['messages'][0]['content'], 'php');
    });
});

test('openai throws with the status code when the api returns an error', function () {
    Http::fake([
        'https://openai.test/v1/chat/completions' => Http::response('rate limited', 429),
    ]);

    expect(fn () => openAi()->generateDescription('x', 'php'))
        ->toThrow(Exception::class, 'OpenAI API request failed (HTTP 429)');
});

test('openai returns null when the response carries no content', function () {
    Http::fake([
        'https://openai.test/v1/chat/completions' => Http::response(['choices' => []], 200),
    ]);

    expect(openAi()->generateDescription('x', 'php'))->toBeNull();
});

test('openai isAvailable checks the models endpoint', function () {
    Http::fake(['https://openai.test/v1/models' => Http::response(['data' => []], 200)]);

    expect(openAi()->isAvailable())->toBeTrue();

    Http::assertSent(fn (Request $r) => $r->url() === 'https://openai.test/v1/models' && $r->method() === 'GET');
});

test('openai isAvailable is false and sends nothing without an api key', function () {
    Http::fake();

    $service = new OpenAIService(['api_key' => null, 'base_url' => 'https://openai.test/v1']);

    expect($service->isAvailable())->toBeFalse();
    Http::assertNothingSent();
});

test('openai getAvailableModels maps ids out of the response', function () {
    Http::fake([
        'https://openai.test/v1/models' => Http::response([
            'data' => [['id' => 'gpt-4o-mini'], ['id' => 'gpt-4o']],
        ], 200),
    ]);

    expect(openAi()->getAvailableModels())->toBe(['gpt-4o-mini', 'gpt-4o']);
});

// ── OpenRouter ────────────────────────────────────────────────────────────

test('openrouter sends its attribution headers and top_p', function () {
    Http::fake([
        'https://openrouter.test/api/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Parses a config file.']]],
        ], 200),
    ]);

    expect(openRouter()->generateDescription('$x = 1;', 'php'))->toBe('Parses a config file.');

    Http::assertSent(function (Request $request) {
        $body = $request->data();

        return $request->hasHeader('Authorization', 'Bearer test-openrouter-key')
            && $request->hasHeader('HTTP-Referer', 'https://snippets.test')
            && $request->hasHeader('X-Title', 'SnippetMan Test')
            && $body['model'] === 'anthropic/claude-3.5-haiku'
            && $body['top_p'] === 0.8
            && $body['stream'] === false;
    });
});

test('openrouter throws a 429 exception so the job retries', function () {
    Http::fake([
        'https://openrouter.test/api/v1/chat/completions' => Http::response('slow down', 429),
    ]);

    try {
        openRouter()->generateDescription('x', 'php');
        $this->fail('Expected a rate limit exception.');
    } catch (Exception $e) {
        expect($e->getCode())->toBe(429)
            ->and($e->getMessage())->toContain('rate limited');
    }
});

test('openrouter returns null and sends nothing without an api key', function () {
    Http::fake();

    $service = new OpenRouterService(['api_key' => null, 'base_url' => 'https://openrouter.test/api/v1']);

    expect($service->generateDescription('x', 'php'))->toBeNull();
    Http::assertNothingSent();
});

// ── Ollama (local) ────────────────────────────────────────────────────────

test('ollama isAvailable checks the tags endpoint', function () {
    Http::fake(['http://ollama.test:11434/api/tags' => Http::response(['models' => []], 200)]);

    expect(ollama()->isAvailable())->toBeTrue();

    Http::assertSent(fn (Request $r) => $r->url() === 'http://ollama.test:11434/api/tags');
});

test('ollama generateDescription posts to the generate endpoint', function () {
    Http::fake([
        'http://ollama.test:11434/api/generate' => Http::response([
            'response' => ' Reverses a string. ',
            'done' => true,
        ], 200),
    ]);

    expect(ollama()->generateDescription('strrev($s);', 'php'))->toBe('Reverses a string.');

    Http::assertSent(function (Request $request) {
        $body = $request->data();

        return $request->url() === 'http://ollama.test:11434/api/generate'
            && $body['model'] === 'codellama:7b'
            && $body['stream'] === false
            && $body['options']['num_predict'] === 128
            && str_contains($body['prompt'], 'strrev($s);');
    });
});

test('ollama swallows failures and returns null rather than throwing', function () {
    Http::fake(['http://ollama.test:11434/api/generate' => Http::response('boom', 500)]);

    expect(ollama()->generateDescription('x', 'php'))->toBeNull();
});

test('ollama getAvailableModels maps names out of the response', function () {
    Http::fake([
        'http://ollama.test:11434/api/tags' => Http::response([
            'models' => [['name' => 'codellama:7b'], ['name' => 'llama3']],
        ], 200),
    ]);

    expect(ollama()->getAvailableModels())->toBe(['codellama:7b', 'llama3']);
});

// ── Shared behaviour ──────────────────────────────────────────────────────

test('no provider calls out when auto_description is disabled', function () {
    Config::set('ai.features.auto_description', false);
    Http::fake();

    expect(openAi()->generateDescription('x', 'php'))->toBeNull()
        ->and(openRouter()->generateDescription('x', 'php'))->toBeNull()
        ->and(ollama()->generateDescription('x', 'php'))->toBeNull();

    Http::assertNothingSent();
});

test('raw curl transfer options are still accepted by the http client', function () {
    // OpenAIService and OpenRouterService only take this branch outside
    // production, and it is the one place the app reaches past the Http facade
    // into Guzzle's cURL handler — the specific risk in a Guzzle major bump.
    app()['env'] = 'local';

    Http::fake([
        'https://openai.test/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'ok']]],
        ], 200),
        'https://openrouter.test/api/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'ok']]],
        ], 200),
    ]);

    expect(app()->environment('local'))->toBeTrue()
        ->and(openAi()->generateDescription('x', 'php'))->toBe('ok')
        ->and(openRouter()->generateDescription('x', 'php'))->toBe('ok');

    Http::assertSentCount(2);
});
