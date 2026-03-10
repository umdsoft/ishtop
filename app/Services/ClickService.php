<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Http\Request;

class ClickService
{
    public function handlePrepare(Request $request): array
    {
        if (!$this->verifySignature($request)) {
            return ['error' => -1, 'error_note' => 'Invalid signature'];
        }

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
        if (!$this->verifySignature($request)) {
            return ['error' => -1, 'error_note' => 'Invalid signature'];
        }

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

    /**
     * Click webhook sign_string tekshiruvi.
     * sign_string = md5(click_trans_id + service_id + secret_key + merchant_trans_id + amount + action + sign_time)
     */
    private function verifySignature(Request $request): bool
    {
        $secretKey = config('services.click.secret_key');

        if (empty($secretKey)) {
            return false;
        }

        $signString = md5(
            $request->input('click_trans_id') .
            $request->input('service_id') .
            $secretKey .
            $request->input('merchant_trans_id', $request->input('merchant_prepare_id', '')) .
            $request->input('amount') .
            $request->input('action') .
            $request->input('sign_time')
        );

        return $signString === $request->input('sign_string');
    }
}
