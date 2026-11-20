<?php

namespace App\Http\Controllers;

use App\Ledger\Money;
use App\Models\ApiPaymentRequest;
use App\Support\PersonalRequests;
use Illuminate\Http\Request;

/** Requests (canvas P05): ask for money, split a bill, see what you owe and what you're owed. Built on payment requests. */
class RequestsController extends Controller
{
    public function index()
    {
        return view('requests.index');
    }

    /** To pay (asked of me) and Sent (I asked), newest first; splits grouped. */
    public function data(Request $request)
    {
        return response()->json(PersonalRequests::lists($request->user()));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['to' => 'required|string|max:40', 'amount' => 'required|string|max:20', 'note' => 'nullable|string|max:140', 'expires' => 'nullable|in:48h,7d']);
        $r = PersonalRequests::ask($request->user(), $data['to'], $data['amount'], $data['note'] ?? null, $data['expires'] ?? '48h');

        return response()->json(['message' => 'Request sent to '.($r->payer?->name ?? $r->payer_phone).'.', 'pay_url' => route('pay-request.show', $r->token)]);
    }

    public function split(Request $request)
    {
        $data = $request->validate(['total' => 'required|string|max:20', 'people' => 'required|array|min:1|max:20', 'people.*' => 'string|max:40',
            'note' => 'nullable|string|max:140', 'include_me' => 'boolean']);
        $requests = PersonalRequests::split($request->user(), $data['total'], $data['people'], $data['note'] ?? null, $data['include_me'] ?? true);

        return response()->json(['message' => 'Split sent: '.count($requests).' requests of about KES '.Money::format($requests[0]->amount).'.']);
    }

    public function remind(Request $request, string $id)
    {
        $r = ApiPaymentRequest::where('kind', 'personal')->where('user_id', $request->user()->id)->where('public_id', $id)->firstOrFail();
        PersonalRequests::remind($r);

        return response()->json(['message' => 'Reminder sent.']);
    }

    public function cancel(Request $request, string $id)
    {
        $r = ApiPaymentRequest::where('kind', 'personal')->where('user_id', $request->user()->id)->where('public_id', $id)->where('status', 'open')->firstOrFail();
        $r->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Request cancelled.']);
    }

    public function decline(Request $request, string $id)
    {
        $r = ApiPaymentRequest::where('kind', 'personal')->where('public_id', $id)->firstOrFail();
        PersonalRequests::decline($r, $request->user());

        return response()->json(['message' => 'Declined. Nothing was paid.']);
    }
}
