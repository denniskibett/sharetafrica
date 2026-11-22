<?php

namespace App\Http\Controllers\Admin;

use App\Aml\Screening;
use App\Http\Controllers\Controller;
use App\Models\AmlCheck;
use App\Models\AmlWatch;
use App\Ramp\RampOrders;
use App\Security\Lockdown;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Compliance › AML checks: payments held by AML screening wait here for a person (approve → it carries on; stop →
 * nothing moved), blocked attempts are listed, and the watch list is kept. Decisions need a fresh password and are logged.
 */
class AmlController extends Controller
{
    public function index()
    {
        return view('admin.aml.index', [
            'pending' => AmlCheck::with(['order', 'user:id,name,email,phone,kyc_level,created_at'])->where('status', 'pending')->oldest()->get(),
            'recent' => AmlCheck::with(['order', 'user:id,name', 'reviewer:id,name'])->where('status', '!=', 'pending')->latest('updated_at')->limit(30)->get(),
            'watch' => AmlWatch::latest()->get(),
            'rules' => config('aml'),
        ]);
    }

    public function approve(Request $request, AmlCheck $check, RampOrders $orders)
    {
        $this->open($check, $request);
        $check->update(['status' => 'approved', 'reviewed_by' => $request->user()->id, 'reviewed_at' => now(), 'note' => $request->input('note')]);
        try {
            $order = $check->order ? $orders->releaseHeld($check->order) : null;
        } catch (ValidationException $e) {
            // the money moved meanwhile (balance, limits): the payment can't go; nothing was taken
            $check->order && $orders->rejectHeld($check->order, collect($e->errors())->flatten()->first());
            $check->update(['note' => trim(($check->note ?? '').' · Could not go: '.collect($e->errors())->flatten()->first())]);

            return back()->with('status', 'Approved, but the payment could not go: '.collect($e->errors())->flatten()->first());
        }
        Lockdown::log('aml check approved', ['check' => $check->id, 'order' => $check->ramp_order_id]);

        return back()->with('status', 'Approved: the payment '.($order?->status === 'paid' ? 'was sent.' : 'is on its way.'));
    }

    public function reject(Request $request, AmlCheck $check, RampOrders $orders)
    {
        $data = $request->validate(['note' => 'required|string|max:300']);
        $this->open($check, $request);
        $check->update(['status' => 'rejected', 'reviewed_by' => $request->user()->id, 'reviewed_at' => now(), 'note' => $data['note']]);
        $check->order && $orders->rejectHeld($check->order, 'Stopped by Sharet');
        Lockdown::log('aml check rejected', ['check' => $check->id, 'order' => $check->ramp_order_id, 'note' => $data['note']]);

        return back()->with('status', 'Stopped. Nothing left the customer\'s wallet.');
    }

    public function watch(Request $request)
    {
        $data = $request->validate(['type' => ['required', Rule::in(array_keys(AmlWatch::TYPES))], 'value' => 'required|string|max:40', 'reason' => 'required|string|max:200']);
        AmlWatch::updateOrCreate(['type' => $data['type'], 'value' => Screening::norm($data['value'])], ['reason' => $data['reason'], 'added_by' => $request->user()->id]);
        Lockdown::log('aml watch list added', ['type' => $data['type']]);

        return back()->with('status', 'Added to the watch list: payments to or from it are blocked.');
    }

    public function unwatch(Request $request, AmlWatch $watch)
    {
        $watch->delete();
        Lockdown::log('aml watch list removed', ['type' => $watch->type]);

        return back()->with('status', 'Removed from the watch list.');
    }

    private function open(AmlCheck $check, Request $request): void
    {
        abort_unless($check->status === 'pending', 422, 'This check is already decided.');
        abort_if($check->user_id === $request->user()->id, 403, 'You cannot decide on your own payment.');
    }
}
