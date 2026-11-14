<?php

namespace App\Http\Controllers;

use App\Services\AdvisorService;
use App\Services\FinanceStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdvisorController extends Controller
{
    public function chat(Request $request, FinanceStore $store, AdvisorService $advisor): JsonResponse
    {
        $data = $request->validate(['message' => ['required', 'string', 'max:2000']]);
        $data['message'] = trim($data['message']);
        if ($data['message'] === '') {
            return response()->json(['message' => 'Enter a message first.'], 422);
        }
        $historyKey = 'advisor.history.'.$request->user()->getAuthIdentifier();
        $history = $request->session()->get($historyKey, []);

        try {
            $reply = $advisor->reply($store->advisorSummary(), $history, $data['message']);
        } catch (HttpException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
        }

        $history[] = ['role' => 'user', 'content' => $data['message']];
        $history[] = ['role' => 'assistant', 'content' => $reply];
        $request->session()->put($historyKey, array_slice($history, -10));

        return response()->json(['reply' => $reply]);
    }
}
