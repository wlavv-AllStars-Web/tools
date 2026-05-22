<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Models\modules\payment_links\PaymentLinkRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class PaymentLinkRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function salesIndex()
    {
        $requests = PaymentLinkRequest::query()
            ->with(['approver', 'emailSender'])
            ->where(function ($query) {
                $query->where('status', PaymentLinkRequest::STATUS_APPROVED)
                    ->orWhere(function ($query) {
                        $query->where('status', PaymentLinkRequest::STATUS_PENDING)
                            ->where('requested_by', auth()->id());
                    });
            })
            ->latest()
            ->get();

        return View::make('customTools.paymentLinks.sales', [
            'requests' => $requests,
            'mode' => 'active',
            'actions' => [],
            'breadcrumbs' => $this->breadcrumbs('Payment link', route('sales.tools.payment_links.index')),
        ]);
    }

    public function salesSent()
    {
        $requests = PaymentLinkRequest::query()
            ->with(['approver', 'emailSender'])
            ->where('status', PaymentLinkRequest::STATUS_SENT)
            ->latest('email_sent_at')
            ->get();

        return View::make('customTools.paymentLinks.sales', [
            'requests' => $requests,
            'mode' => 'sent',
            'actions' => [],
            'breadcrumbs' => $this->breadcrumbs('Sent payment links', route('sales.tools.payment_links.sent')),
        ]);
    }

    public function create()
    {
        return View::make('customTools.paymentLinks.create', [
            'stores' => $this->storeOptions(),
            'actions' => [],
            'breadcrumbs' => $this->breadcrumbs('New payment link request', route('sales.tools.payment_links.create')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'store_code' => ['required', 'string', 'in:' . implode(',', PaymentLinkRequest::storeOptions())],
            'order_id' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:30'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'customer_email' => ['required', 'email', 'max:255'],
        ]);

        $storeCode = $this->normalizeStoreCode($data['store_code']);
        $amount = round((float) $data['amount'], 2);
        $amountInCents = (int) round($amount * 100);
        $email = strtolower(trim($data['customer_email']));

        PaymentLinkRequest::create([
            'store_code' => $storeCode,
            'order_id' => trim($data['order_id']),
            'description' => trim($data['description']),
            'amount' => $amount,
            'currency' => 'EUR',
            'customer_email' => $email,
            'request_hash' => Str::random(64),
            'sha_sign' => PaymentLinkRequest::generateShaSign(
                $storeCode,
                trim($data['order_id']),
                trim($data['description']),
                $amountInCents,
                $email
            ),
            'status' => PaymentLinkRequest::STATUS_PENDING,
            'requested_by' => auth()->id(),
            'requested_at' => now(),
        ]);

        return redirect()
            ->route('sales.tools.payment_links.index')
            ->with('success', 'Pedido de link de pagamento criado. Fica disponivel para envio depois da aprovacao de Finance.');
    }

    public function financeIndex()
    {
        $requests = PaymentLinkRequest::query()
            ->with(['requester', 'approver'])
            ->where('status', PaymentLinkRequest::STATUS_PENDING)
            ->latest()
            ->get();

        return View::make('customTools.paymentLinks.finance', [
            'requests' => $requests,
            'mode' => 'pending',
            'actions' => [],
            'breadcrumbs' => $this->breadcrumbs('Payment link requests', route('finance.tools.payment_links.index'), 'finance'),
        ]);
    }

    public function financeArchive()
    {
        $requests = PaymentLinkRequest::query()
            ->with(['requester', 'approver'])
            ->whereIn('status', [PaymentLinkRequest::STATUS_APPROVED, PaymentLinkRequest::STATUS_SENT])
            ->latest('approved_at')
            ->get();

        return View::make('customTools.paymentLinks.finance', [
            'requests' => $requests,
            'mode' => 'archive',
            'actions' => [],
            'breadcrumbs' => $this->breadcrumbs('Archived payment links', route('finance.tools.payment_links.archive'), 'finance'),
        ]);
    }

    public function show(PaymentLinkRequest $paymentLinkRequest)
    {
        $paymentLinkRequest->load(['requester', 'approver', 'emailSender']);

        return View::make('customTools.paymentLinks.show', [
            'requestItem' => $paymentLinkRequest,
            'actions' => [],
            'breadcrumbs' => $this->breadcrumbs('Payment link request #' . $paymentLinkRequest->id, route('finance.tools.payment_links.show', $paymentLinkRequest), 'finance'),
        ]);
    }

    public function approve(PaymentLinkRequest $paymentLinkRequest): RedirectResponse
    {
        if (!$paymentLinkRequest->isApproved()) {
            $paymentLinkRequest->update([
                'status' => PaymentLinkRequest::STATUS_APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        }

        return redirect()
            ->route('finance.tools.payment_links.archive')
            ->with('success', 'Link de pagamento aprovado.');
    }

    public function sendEmail(PaymentLinkRequest $paymentLinkRequest): RedirectResponse
    {
        if (!$paymentLinkRequest->isApproved() || !$paymentLinkRequest->paymentUrl()) {
            return back()->with('error', 'Este link ainda nao foi aprovado por Finance.');
        }

        if ($paymentLinkRequest->email_sent_at && !in_array(auth()->user()?->role, ['admin', 'manager'], true)) {
            return back()->with('error', 'Depois do primeiro envio, apenas users do tipo admin ou manager podem reenviar o email.');
        }

        $subject = 'Payment link - Order ' . $paymentLinkRequest->order_id;
        $html = view('mails.payment_link_request', [
            'requestItem' => $paymentLinkRequest,
            'paymentUrl' => $paymentLinkRequest->paymentUrl(),
        ])->render();

        $recipient = (app()->environment('local') || str_contains(strtolower(base_path()), 'xampp'))
            ? 'bruno.fernandes.asm@gmail.com'
            : $paymentLinkRequest->customer_email;

        $from = $this->salesEmailConfig($paymentLinkRequest->store_code);

        Mail::html($html, function ($message) use ($recipient, $subject, $from) {
            $message
                ->from($from['address'], $from['name'])
                ->to($recipient)
                ->subject($subject);
        });

        $paymentLinkRequest->update([
            'status' => PaymentLinkRequest::STATUS_SENT,
            'email_sent_by' => auth()->id(),
            'email_sent_at' => now(),
        ]);

        return redirect()
            ->route('sales.tools.payment_links.sent')
            ->with('success', 'Email enviado com o link de pagamento.');
    }

    private function normalizeStoreCode(string $storeCode): string
    {
        $storeCode = strtoupper($storeCode);

        abort_unless(in_array($storeCode, PaymentLinkRequest::storeOptions(), true), 404);

        return $storeCode;
    }

    private function storeOptions(): array
    {
        return collect(PaymentLinkRequest::storeOptions())
            ->mapWithKeys(fn (string $code) => [$code => $this->storeName($code)])
            ->all();
    }

    private function storeName(string $storeCode): string
    {
        return (string) config('allstars.payment_links.stores.' . $storeCode . '.name', $storeCode);
    }

    private function salesEmailConfig(string $storeCode): array
    {
        $storeCode = strtoupper($storeCode);
        $fallbackAddress = (string) config('mail.from.address');
        $fallbackName = (string) config('allstars.payment_links.stores.' . $storeCode . '.name', config('mail.from.name'));

        return [
            'address' => (string) config('allstars.emails.sales.' . $storeCode . '.address', $fallbackAddress),
            'name' => (string) config('allstars.emails.sales.' . $storeCode . '.name', $fallbackName),
        ];
    }

    private function breadcrumbs(string $currentName, string $currentUrl, string $area = 'sales'): array
    {
        $areaRoute = $area === 'finance' ? 'finance.index' : 'sales.index';

        return [
            ['name' => $area, 'url' => route($areaRoute)],
            ['name' => $currentName, 'url' => $currentUrl, 'no_translation' => 1],
        ];
    }
}
