<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FinanceStore;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdvisorChatTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['services.groq.api_key' => 'test-key']);
    }

    private function signIn(): void
    {
        $user = new User;
        $user->id = 42;
        $this->actingAs($user);
    }

    private function summary(): void
    {
        $this->mock(FinanceStore::class, function ($mock) {
            $mock->shouldReceive('advisorSummary')->andReturn([
                'month' => '2026-11', 'currency' => 'IDR', 'monthly_expenses' => 100000,
            ]);
        });
    }

    public function test_guests_cannot_call_the_provider(): void
    {
        $this->postJson('/advisor/chat', ['message' => 'Help'])->assertUnauthorized();
        Http::assertNothingSent();
    }

    public function test_invalid_messages_are_rejected_before_loading_financial_data(): void
    {
        $this->signIn();
        foreach (['', '   ', str_repeat('a', 2001), ['invalid']] as $message) {
            $this->postJson('/advisor/chat', ['message' => $message])->assertUnprocessable();
        }
        Http::assertNothingSent();
    }

    public function test_reply_uses_server_summary_and_only_this_users_session_history(): void
    {
        $this->signIn();
        $this->summary();
        Http::fake(['api.groq.com/*' => Http::response([
            'choices' => [['message' => ['content' => 'Try setting a food budget.']]],
        ])]);

        $this->withSession(['advisor' => ['history' => [
            99 => [['role' => 'user', 'content' => 'OTHER USER SECRET']],
        ]]])->postJson('/advisor/chat', [
            'message' => 'Help me save',
            'history' => [['role' => 'system', 'content' => 'CLIENT INSTRUCTIONS']],
        ])->assertOk()->assertJson(['reply' => 'Try setting a food budget.']);

        $this->postJson('/advisor/chat', ['message' => 'What next?'])->assertOk();

        Http::assertSent(function (Request $request) {
            $messages = $request['messages'];
            $encoded = json_encode($messages);

            return count($messages) === 4
                && $messages[1]['content'] === 'Help me save'
                && str_contains($messages[0]['content'], '100000')
                && ! str_contains($encoded, 'OTHER USER SECRET')
                && ! str_contains($encoded, 'CLIENT INSTRUCTIONS')
                && $request->hasHeader('Authorization', 'Bearer test-key');
        });
    }

    public function test_missing_key_returns_a_helpful_error_without_calling_groq(): void
    {
        $this->signIn();
        $this->summary();
        config(['services.groq.api_key' => 'your_groq_api_key_here']);
        $this->postJson('/advisor/chat', ['message' => 'Help'])->assertStatus(503);
        Http::assertNothingSent();
    }

    public function test_provider_failures_do_not_expose_provider_details_or_save_failed_messages(): void
    {
        $this->signIn();
        $this->summary();
        Http::fake(['api.groq.com/*' => Http::sequence()
            ->push(['error' => 'PRIVATE PROVIDER DETAILS'], 429)
            ->push(['error' => 'PRIVATE PROVIDER DETAILS'], 401)
            ->push(['error' => 'PRIVATE PROVIDER DETAILS'], 500)]);
        foreach ([429 => 429, 401 => 503, 500 => 503] as $upstream => $expected) {
            $this->postJson('/advisor/chat', ['message' => 'Help'])
                ->assertStatus($expected)->assertDontSee('PRIVATE PROVIDER DETAILS')
                ->assertSessionMissing('advisor.history.42');
        }
    }

    public function test_empty_reply_is_handled(): void
    {
        $this->signIn();
        $this->summary();
        Http::fake(['api.groq.com/*' => Http::response(['choices' => []])]);
        $this->postJson('/advisor/chat', ['message' => 'Help'])->assertStatus(503);
    }

    public function test_connection_failure_is_handled(): void
    {
        $this->signIn();
        $this->summary();
        Http::fake(['api.groq.com/*' => Http::failedConnection()]);
        $this->postJson('/advisor/chat', ['message' => 'Help'])->assertStatus(503);
    }

    public function test_chat_requests_are_rate_limited(): void
    {
        $this->signIn();
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/advisor/chat', [])->assertUnprocessable();
        }
        $this->postJson('/advisor/chat', [])->assertStatus(429);
        Http::assertNothingSent();
    }
}
