<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Http\Request;

class ClickService
{
    public function handlePrepare(Request $request): array
    {
        $merchantTransId = $request->input('merchant_trans_id');
        $amount = $request->input('amount');

        $payment = Payment::find($merchantTransId);

        if (!$payment) {
            return ['error' => -5, 'error_note' => 'Order not found'];
        }

        if ((float) $amount !== (float) $payment->amount) {
            return ['error' => -2, 'error_note' => 'Invalid amount'];
        }

        $payment->update([
            'status' => 'processing',
            'external_id' => $request->input('click_trans_id'),
        ]);

        return [
            'click_trans_id' => $request->input('click_trans_id'),
            'merchant_trans_id' => $merchantTransId,
            'merchant_prepare_id' => $payment->id,
            'error' => 0,
            'error_note' => 'Success',
        ];
    }

    public function handleComplete(Request $request): array
    {
        $merchantPrepareId = $request->input('merchant_prepare_id');
        $payment = Payment::find($merchantPrepareId);

        if (!$payment) {
            return ['error' => -5, 'error_note' => 'Order not found'];
        }

        if ($request->input('error') < 0) {
            $payment->update(['status' => 'failed']);
            return ['error' => -9, 'error_note' => 'Transaction failed'];
        }

        app(PaymentService::class)->complete($payment, $request->input('click_trans_id'));

        return [
            'click_trans_id' => $request->input('click_trans_id'),
            'merchant_trans_id' => $request->input('merchant_trans_id'),
            'merchant_confirm_id' => $payment->id,
            'error' => 0,
            'error_note' => 'Success',
        ];
    }
}
