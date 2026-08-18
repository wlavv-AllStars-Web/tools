<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CustomTools\mailsController;

use App\Models\prestashop\asm_dashboard;
use App\Models\prestashop\product;
use App\Models\prestashop\issues;
use App\Models\prestashop\asm_email_alert;
use App\Models\prestashop\customer;
use App\Models\prestashop\orders;
use App\Models\modules\price_map\price_map;

use App\Models\modules\dashboard\dashboard;
use Throwable;

class salesController extends Controller
{
    public $actions = [];
    public $breadcrumbs = [];
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('sales'), 'url' => route('sales.index')];

    }

    public function index()
    {
        dashboard::getCountersContentOfTabPanel('purchase', 'dashboard_quote_backoffice');
        dashboard::getCountersContentOfTabPanel('sales', 'dashboard_quote_frontoffice');

        $deferredPanels = [
            'dashboard_dropcart_3_days',
            'dashboard_order_reviewed',
            'dashboard_order_reviewed_2',
            'no_instructions',
        ];

        $data = [
            'counters'      => dashboard::calculateAndGetCountersOfTab('sales', $deferredPanels),
            'panels'        => [],
            'accessList'    => $this->accessList(),
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs,
            'racio'         => price_map::getAll()
        ];

        return View::make('areas/sales/index')->with($data);
    }

    private function accessList(){        
        $accessList = array();
        $accessList[]                           = ['name' =>  'YouTube Verify',                       'url' => route('sales.youtube_broken_links.sync'),             'method' => 'post', 'icon' => '<i style="font-size: 40px;" class="fa-brands fa-youtube"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.backorders'),             'url' => route('sales.tools.backorders.index'),              'icon' => '<i style="font-size: 40px;" class="fa-solid fa-business-time"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.PRODUCT ISSUES'),         'url' => route('sales.tools.product_issues.index'),          'icon' => '<i style="font-size: 40px;" class="fa fa-solid fa-box-open"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.quote'),                  'url' => route('sales.tools.quotes.index', ['list' => 1]),    'icon' => '<i style="font-size: 40px;" class="fa-solid fa-bell-concierge"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.returns'),                'url' => route('sales.tools.returns.index'),                 'icon' => '<i style="font-size: 40px;" class="fa-solid fa-person-walking-arrow-loop-left"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.warranties'),             'url' => route('sales.tools.warranties.index'),              'icon' => '<i style="font-size: 40px;" class="fa-solid fa-award"></i>'];
        $accessList[]                           = ['name' =>  'Payment link',                            'url' => route('sales.tools.payment_links.index'),           'icon' => '<i style="font-size: 40px;" class="fa-solid fa-link"></i>'];
        $accessList[]                           = ['name' =>  'Product visibility',                      'url' => route('sales.tools.product_visibility.index'),       'icon' => '<i style="font-size: 40px;" class="fa-solid fa-eye"></i>'];
        return $accessList;
    }

    public function syncYoutubeBrokenLinks(): RedirectResponse
    {
        try {
            Artisan::call('youtube:check-broken-links');

            $output = trim(Artisan::output());

            return redirect()
                ->route('sales.index')
                ->with('success', $output !== '' ? $output : 'YouTube broken links verification completed.');
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('sales.index')
                ->with('error', 'YouTube broken links verification failed: ' . $e->getMessage());
        }
    }

    public function post(Request $request){

        if( $request->action == 'sendNewsletter' ){            
            $requested_product = asm_email_alert::find($request->id);            
            $product_detail = product::with('lang')->where('id_product' , $requested_product->id_product)->first();
            
            $data = [
                'requested' => $requested_product,
                'product_info' => $product_detail
            ];
            
            $email = NEW mailsController();
            $html = $email->createStructure('ASM', 'requestedProducts', trans('mails.Requested products notification'), $data, $requested_product->id_lang);
            $recipient = (app()->environment('local') || str_contains(strtolower(base_path()), 'xampp'))
                ? 'bruno.fernandes.asm@gmail.com'
                : $requested_product->email;
            //$email->send($recipient, $html, trans('mails.Requested products notification'));
            
            return response()->json([ 'result' => 'success' ]);
            
        }elseif( $request->action == 'removeItemForNewsletter' ){
            
            //asm_email_alert::where('id', $request->id)->delete();
            
            return response()->json([ 'result' => 'success' ]);

        }

    }

    public function sendReviewedEmail(Request $request)
    {
        $orderId = $request->get('id');

        if (empty($orderId)) {
            return response()->json([
                'result'  => 'error',
                'message' => 'Missing order id',
            ], 422);
        }

        $order = orders::where('id_order', $orderId)->first();

        if (!$order) {
            return response()->json([
                'result'  => 'error',
                'message' => 'Order not found',
            ], 404);
        }

        $customer = customer::where('id_customer', $order->id_customer)->first();

        if (!$customer) {
            return response()->json([
                'result'  => 'error',
                'message' => 'Customer not found',
            ], 404);
        }

        $data = [
            'firstname' => $customer->firstname,
            'lastname'  => $customer->lastname,
            'id_lang'   => $order->id_lang,
        ];

        $template = $this->resolveReviewedEmailTemplate((int) $order->id_lang);
        $subject = trans('mails.Order review');

        $email = new mailsController();
        $html = $email->createStructure('none', $template, $subject, $data, $order->id_lang);
        $email->send($customer->email, $html, $subject, 'asm_sales');

        asm_dashboard::addException($this->buildReviewedEmailException($order, $customer));

        return response()->json([
            'result' => 'success',
        ]);
    }

    private function resolveReviewedEmailTemplate(int $languageId): string
    {
        if ($languageId === 4) {
            return 'ES_email_reviewed';
        }

        if ($languageId === 5) {
            return 'FR_email_reviewed';
        }

        return 'EN_email_reviewed';
    }

    private function buildReviewedEmailException($order, $customer): object
    {
        return (object) [
            'panel' => 'order_reviewed',
            'var_1' => $order->id_order,
            'var_2' => $order->date_add,
            'var_3' => $customer->email,
        ];
    }
}
