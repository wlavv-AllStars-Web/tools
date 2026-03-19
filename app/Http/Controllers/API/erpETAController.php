<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\modules\bms_procurement\bms_procurement_purchase_order_product;
use App\Models\modules\shipping_erp\shipping_erp;
use App\Models\modules\shipping\shipping;
use App\Models\modules\shipping\shipping_delay;

use App\Models\prestashop\pack;
use App\Models\prestashop\product;
use App\Models\prestashop\product_attribute;

use App\Models\prestashop\product_lang;

use App\Models\prestashop\stock_available;

use Carbon\Carbon;

class erpETAController extends Controller{
    
    public function getProductFromERP(Request $request){
        
        $idLang = (int)$request->id_lang;            

        if(isset($request->reference)){

            $idProduct = product::where('reference', $request->reference)->value('id_product');
            
            if(isset( $idProduct )){
                $idProduct = $idProduct;
                $idProductAttr = 0;
            }else{
                $attr = product_attribute::where('reference', $request->reference)->first();
                if ( !isset($attr)) {
                    echo json_encode(['waiting' => false, 'quantity' => 0, 'message' => 'Out of stock']);
                    exit;
                }else{
                    $idProduct = $attr->id_product;
                    $idProductAttr = $attr->id_product_attribute;
                }                
            }

        }else{
            $idProduct = (int)$request->id_product;
            $idProductAttr = (int)$request->id_product_attribute;
        }
    
        $product = product::where('id_product', $idProduct)->first();
        if (!$product) {
            echo json_encode(['waiting' => false, 'quantity' => 0, 'message' => 'Product not found!']);
            exit;
        }
    
        $hasCombinations = product_attribute::where('id_product', $idProduct)->count() > 0;
        if ($hasCombinations && $idProductAttr === 0) {
            echo json_encode(['waiting' => false, 'quantity' => 0, 'message' => '']);
            exit;
        }
    
        $labels = [
            'in_stock' =>       [1 => 'In stock: ',         4 => 'En stock: ',          5 => 'En stock: '],
            'shipped_within' => [1 => 'Shipped within: ',   4 => 'Enviado dentro de: ', 5 => 'Expédié sous: '],
            'available_on' =>   [1 => 'Availability: ',     4 => 'Disponibilidad: ',    5 => 'Disponibilité: ']
        ];

        $tooltips = [
            'out_of_stock_generic' => [
                1 => "This product is currently out of stock or requires a specific order. Please check the lead time shown (working days) for an approximate shipping date.",
                4 => "Este producto no está disponible en stock o requiere un pedido específico. Consulta el plazo indicado (días hábiles) para conocer una fecha aproximada de envío.",
                5 => "Ce produit n’est actuellement pas en stock ou nécessite une commande spécifique. Veuillez consulter le délai indiqué (jours ouvrés) pour connaître une date d’expédition approximative."
            ],
            'in_stock_generic' => [
                1 => "This product is in stock in our warehouses and will ship the same day if ordered before 12:30, or the next business day if ordered later.",
                4 => "Este producto está en stock en nuestros almacenes y se enviará el mismo día si se pide antes de las 12:30, o el siguiente día hábil si se pide después.",
                5 => "Ce produit est en stock sur l’une de nos plateformes et sera expédié dans la journée si commandé avant 12h30, ou le prochain jour ouvré si commandé après 12h30."
            ],
            'incoming_with_eta' => [
                1 => "This item is currently in transit to our warehouses. The indicated date is an estimate provided by the carrier and may be subject to delays.",
                4 => "Este artículo está en tránsito hacia nuestros almacenes. La fecha indicada es una estimación del transportista y puede estar sujeta a retrasos.",
                5 => "Article en cours d’acheminement vers nos entrepôts. La date indiquée correspond à l’estimation du transporteur et peut être sujette à des retards."
            ],
        ];

        $packInfo = pack::availablePackQty($idProduct);
        $isPack = (is_array($packInfo) && !empty($packInfo['is_pack']) && $packInfo['is_pack'] === true);
    
        $getProductInfoMessage = function ($messageText) {
            return '<label class="editable">' . $messageText . '</label>';
        };

        $getTooltipIcon = function (string $tooltipText) {
            $safe = htmlspecialchars($tooltipText, ENT_QUOTES, 'UTF-8');
        
            return '<i class="fa fa-question-circle api-tip"
                        title="' . $safe . '"
                        data-tip="' . $safe . '"
                        aria-label="Info"
                        role="button"
                        tabindex="0"
                        style="
                            font-size:18px;
                            line-height:24px;
                            vertical-align:middle;
                            margin:0 8px;
                            cursor:pointer;
                            color:#666;
                        "></i>';
        };


        $formatEtaForTooltip = function (int $idLang, Carbon $date) {
            if ($idLang === 1) return $date->format('d/m/Y');
            if ($idLang === 4) return $date->format('d/m/Y');
            if ($idLang === 5) return $date->format('d/m/Y');
            return $date->format('Y-m-d');
        };
    
        if (!$isPack) {
            $reference = $idProductAttr === 0
                ? $product->reference
                : optional(product_attribute::where('id_product', $idProduct)->where('id_product_attribute', $idProductAttr)->first())->reference;
    
            if (!$reference) {
                echo json_encode(['waiting' => false, 'quantity' => 0, 'message' => 'Product not found!']);
                exit;
            }
    
            $stock = stock_available::where('id_product', $idProduct)->where('id_product_attribute', $idProductAttr)->first();
            $qty = $stock ? (int)$stock->quantity : 0;
    
            if ($qty > 0) {
                // ✅ ALTERADO: inserir tooltip icon ENTRE label e badge
                $tooltip = $tooltips['in_stock_generic'][$idLang] ?? '';
                $message = $getProductInfoMessage(
                    $labels['in_stock'][$idLang] .
                    $getTooltipIcon($tooltip) .
                    ' <span class="label label-success" style="font-size:14px;font-weight:900">' . $qty . '</span>'
                );

                echo json_encode(['waiting' => false, 'quantity' => $qty, 'message' => $message]);
                exit;
            }
    
            $waitingOrders = bms_procurement_purchase_order_product::checkIfWaitingQuantity($reference);
            if (!empty($waitingOrders)) {
                $expectedQuantity = 0;
                $expectedEta = null;
    
                foreach ($waitingOrders as $order) {
                    if ($order->qty_wmfaturado > 0 && $order->qty_received < $order->qty_wmfaturado) {
    
                        $shipping = shipping_erp::where('id_erp', $order->po_id)->orderBy('id', 'DESC')->first();
                        if ($shipping) {
                            $delay = shipping_delay::where('id_shipping', $shipping->id_shipping)->orderBy('id', 'DESC')->first();
                            if ($delay) {
                                $expectedQuantity += $order->qty_wmfaturado - $order->qty_received;
                                if (!$expectedEta || $delay->date < $expectedEta) {
                                    $expectedEta = $delay->date;
                                }
                            }
                        }
                    }
                }
    
                if ($expectedEta) {
                    $date = Carbon::createFromFormat('Y-m-d', $expectedEta);

                    if ($idLang === 1) $dateMsg = $date->format('d/m/Y');
                    if ($idLang === 4) $dateMsg = $date->format('d/m/Y');
                    if ($idLang === 5) $dateMsg = $date->format('d/m/Y');

                    $etaTxt = $formatEtaForTooltip($idLang, $date);
                    $tooltip = $tooltips['incoming_with_eta'][$idLang] ?? '';
                    $tooltip = str_replace('{{ETA}}', $etaTxt, $tooltip);

                    $message = $getProductInfoMessage(
                        $labels['available_on'][$idLang] .
                        $getTooltipIcon($tooltip) .
                        ' <span class="label label-warning" style="color: #333;">' . $dateMsg . '</span>'
                    );

                    echo json_encode(['waiting' => true, 'quantity' => $expectedQuantity, 'message' => $message]);
                    exit;
                } else {
                    $productInfo = product_lang::where('id_lang', $idLang)->where('id_product', $idProduct)->first();

                    $tooltip = $tooltips['out_of_stock_generic'][$idLang] ?? '';

                    $message = $getProductInfoMessage(
                        $labels['shipped_within'][$idLang] .
                        $getTooltipIcon($tooltip) .
                        ' <span class="label label-warning" style="color: #333;">' . ($productInfo ? $productInfo->available_later : '') . '</span>'
                    );

                    echo json_encode(['waiting' => false, 'quantity' => 0, 'message' => $message]);
                    exit;
                }
            }
    
            $productInfo = product_lang::where('id_lang', $idLang)->where('id_product', $idProduct)->first();

            $tooltip = $tooltips['out_of_stock_generic'][$idLang] ?? '';

            $message = $getProductInfoMessage(
                $labels['shipped_within'][$idLang] .
                $getTooltipIcon($tooltip) .
                ' <span class="label label-warning"  style="color: #333;">' . ($productInfo ? $productInfo->available_later : '') . '</span>'
            );

            echo json_encode(['waiting' => false, 'quantity' => 0, 'message' => $message]);
            exit;
        }
    
        $packQty = isset($packInfo['pack_qty']) ? (int)$packInfo['pack_qty'] : 0;
        if ($packQty > 0) {
            $tooltip = $tooltips['in_stock_generic'][$idLang] ?? '';

            $message = $getProductInfoMessage(
                $labels['in_stock'][$idLang] .
                $getTooltipIcon($tooltip) .
                ' <span class="label label-success" style="font-size:14px;font-weight:900">' . $packQty . '</span>'
            );

            echo json_encode(['waiting' => false, 'quantity' => $packQty, 'message' => $message, 'is_pack' => true]);
            exit;
        }
    
        $components = $packInfo['components'] ?? [];
        if (empty($components)) {
            $productInfo = product_lang::where('id_lang', $idLang)->where('id_product', $idProduct)->first();

            $tooltip = $tooltips['out_of_stock_generic'][$idLang] ?? '';

            $message = $getProductInfoMessage(
                $labels['shipped_within'][$idLang] .
                $getTooltipIcon($tooltip) .
                ' <span class="label label-warning"  style="color: #333;">' . ($productInfo ? $productInfo->available_later : '') . '</span>'
            );

            echo json_encode(['waiting' => false, 'quantity' => 0, 'message' => $message, 'is_pack' => true]);
            exit;
        }
    
        $expectedPacks = null;
        $etaCandidates = [];
        $foundAnyWaiting = false;
    
        foreach ($components as $comp) {
            $idCompProduct = (int)$comp['id_product'];
            $idCompAttr = (int)($comp['id_product_attribute'] ?? 0);
            $qtyInPack = max((int)($comp['qty_in_pack'] ?? 1), 1);
    
            $compStock = isset($comp['stock']) 
                ? (int)$comp['stock']
                : (int) optional(stock_available::where('id_product', $idCompProduct)->where('id_product_attribute', $idCompAttr)->first())->quantity;
    
            $compRef = $idCompAttr === 0
                ? optional(product::where('id_product', $idCompProduct)->first())->reference
                : optional(product_attribute::where('id_product', $idCompProduct)->where('id_product_attribute', $idCompAttr)->first())->reference;
    
            $incomingQty = 0;
            $incomingEta = null;
    
            if ($compRef) {
                $waitingOrders = bms_procurement_purchase_order_product::checkIfWaitingQuantity($compRef);
                if (!empty($waitingOrders)) {
                    $foundAnyWaiting = true;
                    foreach ($waitingOrders as $order) {
                        if ($order->qty_wmfaturado > 0 && $order->qty_received < $order->qty_wmfaturado) {
                            $incomingQty += $order->qty_wmfaturado - $order->qty_received;
                            $shipping = shipping_erp::where('id_erp', $order->po_id)->orderBy('id', 'DESC')->first();
                            if ($shipping) {
                                $delay = shipping_delay::where('id_shipping', $shipping->id_shipping)->orderBy('date', 'DESC')->first();
                                if ($delay && (!$incomingEta || $delay->date < $incomingEta)) {
                                    $incomingEta = $delay->date;
                                }
                            }
                        }
                    }
                }
            }
    
            $possiblePacks = (int) floor(($compStock + $incomingQty) / $qtyInPack);
            $expectedPacks = is_null($expectedPacks) ? $possiblePacks : min($expectedPacks, $possiblePacks);
    
            if ($incomingEta) $etaCandidates[] = $incomingEta;
        }
    
        $expectedPacks = $expectedPacks ?? 0;

        $expectedEta = null;
        if (!empty($etaCandidates)) {
            rsort($etaCandidates);
            $expectedEta = $etaCandidates[0];
        }
    
        if ($expectedPacks <= 0) {
            $productInfo = product_lang::where('id_lang', $idLang)->where('id_product', $idProduct)->first();

            $tooltip = $tooltips['out_of_stock_generic'][$idLang] ?? '';

            $message = $getProductInfoMessage(
                $labels['shipped_within'][$idLang] .
                $getTooltipIcon($tooltip) .
                ' <span class="label label-warning" style="color: #333;">' . ($productInfo ? $productInfo->available_later : '') . '</span>'
            );

            echo json_encode(['waiting' => $foundAnyWaiting, 'quantity' => 0, 'message' => $message, 'is_pack' => true]);
            exit;
        }
    
        if ($expectedEta) {
            $date = Carbon::createFromFormat('Y-m-d', $expectedEta);

            if ($idLang === 1) $dateMessage = $date->format('d/m/Y');
            if ($idLang === 4) $dateMessage = $date->format('d/m/Y');
            if ($idLang === 5) $dateMessage = $date->format('d/m/Y');

            $etaTxt = $formatEtaForTooltip($idLang, $date);
            $tooltip = $tooltips['incoming_with_eta'][$idLang] ?? '';
            $tooltip = str_replace('{{ETA}}', $etaTxt, $tooltip);

            $message = $getProductInfoMessage(
                $labels['available_on'][$idLang] .
                $getTooltipIcon($tooltip) .
                ' <span class="label label-warning" style="color: #333;">' . $dateMessage . '</span>'
            );

            echo json_encode(['waiting' => true, 'quantity' => $expectedPacks, 'message' => $message, 'is_pack' => true]);
            exit;
        } else {
            $productInfo = product_lang::where('id_lang', $idLang)->where('id_product', $idProduct)->first();

            $tooltip = $tooltips['out_of_stock_generic'][$idLang] ?? '';

            $message = $getProductInfoMessage(
                $labels['shipped_within'][$idLang] .
                $getTooltipIcon($tooltip) .
                ' <span class="label label-warning" style="color: #333;">' . ($productInfo ? $productInfo->available_later : '') . '</span>'
            );

            echo json_encode(['waiting' => true, 'quantity' => $expectedPacks, 'message' => $message, 'is_pack' => true]);
            exit;
        }
    }

    public function getEtaBatch(Request $request)
    {
        $references = $request->input('references', []);

        if (!is_array($references) || empty($references)) {
            return response()->json([
                'error' => true,
                'message' => 'references must be a non-empty array'
            ], 422);
        }

        $references = array_slice($references, 0, 25);

        $results = [];
        foreach ($references as $ref) {
            $ref = trim((string)$ref);
            if ($ref === '') continue;

            $results[$ref] = $this->computeEtaOrOutOfStock($ref);
        }

        return response()->json([
            'count'   => count($results),
            'results' => $results,
        ]);
    }


    private function computeEtaOrOutOfStock(string $reference): array
    {

        $idProduct = product::where('reference', $reference)->value('id_product');
        $idProductAttr = 0;

        if ($idProduct) {
            
            $idProduct = (int)$idProduct;
            $idProductAttr = 0;
            
        } else {
            
            $attr = product_attribute::where('reference', $reference)->first();
            if (!$attr) return ['status' => 'not_found', 'quantity' => 'OUT OF STOCK'];
            
            $idProduct = (int)$attr->id_product;
            $idProductAttr = (int)$attr->id_product_attribute;
        }

        $product = product::where('id_product', $idProduct)->first();
        if (!$product) return ['status' => 'not_found', 'quantity' => 'OUT OF STOCK'];

        $hasCombinations = product_attribute::where('id_product', $idProduct)->count() > 0;
        if ($hasCombinations && $idProductAttr === 0 && (string)$product->reference === $reference) return ['status' => 'not_found'];
        
        $stock = stock_available::where('id_product', $idProduct)->where('id_product_attribute', $idProductAttr)->first();

        $qty = $stock ? (int)$stock->quantity : 0;
        if ($qty > 0) return ['status' => 'in_stock', 'quantity' => $qty];


        $packInfo = pack::availablePackQty($idProduct);
        $isPack = (is_array($packInfo) && !empty($packInfo['is_pack']) && $packInfo['is_pack'] === true);

        if (!$isPack) {

            $refToCheck = $idProductAttr === 0
                ? (string)$product->reference
                : (string) optional(
                    product_attribute::where('id_product', $idProduct)
                        ->where('id_product_attribute', $idProductAttr)
                        ->first()
                )->reference;

            if (!$refToCheck) return ['status' => 'not_found', 'quantity' => 'OUT OF STOCK'];

            $waitingOrders = bms_procurement_purchase_order_product::checkIfWaitingQuantity($refToCheck);
            if (empty($waitingOrders)) {
                return ['status' => 'out_of_stock', 'quantity' => 'OUT OF STOCK'];
            }

            $expectedQuantity = 0;
            $expectedEta = null;

            foreach ($waitingOrders as $order) {
                if ($order->qty_wmfaturado > 0 && $order->qty_received < $order->qty_wmfaturado) {
                    $expectedQuantity += ($order->qty_wmfaturado - $order->qty_received);

                    $shipping = shipping_erp::where('id_erp', $order->po_id)->orderBy('id', 'DESC')->first();
                    if ($shipping) {
                        $delay = shipping_delay::where('id_shipping', $shipping->id_shipping)->orderBy('id', 'DESC')->first();
                        if ($delay) {
                            if (!$expectedEta || $delay->date < $expectedEta) {
                                $expectedEta = $delay->date; // mais cedo
                            }
                        }
                    }
                }
            }

            if ($expectedEta) {

                $eta = Carbon::createFromFormat('Y-m-d', $expectedEta)->format('Y-m-d');

                return [
                    'status'   => 'eta',
                    'eta'      => $eta,
                    'quantity' => $expectedQuantity,
                ];
            }

            return ['status' => 'out_of_stock', 'quantity' => 'OUT OF STOCK'];
        }
        
        $components = $packInfo['components'] ?? [];
        if (empty($components)) return ['status' => 'out_of_stock', 'quantity' => 'OUT OF STOCK'];return ['status' => 'out_of_stock'];

        $expectedPacks = null;
        $etaCandidates = [];
        $foundAnyWaiting = false;

        foreach ($components as $comp) {
            $idCompProduct = (int)$comp['id_product'];
            $idCompAttr    = (int)($comp['id_product_attribute'] ?? 0);
            $qtyInPack     = max((int)($comp['qty_in_pack'] ?? 1), 1);

            $compStock = isset($comp['stock'])
                ? (int)$comp['stock']
                : (int) optional(
                    stock_available::where('id_product', $idCompProduct)->where('id_product_attribute', $idCompAttr)->first()
                )->quantity;

            $compRef = $idCompAttr === 0
                ? (string) optional(product::where('id_product', $idCompProduct)->first())->reference
                : (string) optional(
                    product_attribute::where('id_product', $idCompProduct)->where('id_product_attribute', $idCompAttr)->first()
                )->reference;

            $incomingQty = 0;
            $incomingEta = null;

            if ($compRef) {
                $waitingOrders = bms_procurement_purchase_order_product::checkIfWaitingQuantity($compRef);
                if (!empty($waitingOrders)) {
                    $foundAnyWaiting = true;

                    foreach ($waitingOrders as $order) {
                        if ($order->qty_wmfaturado > 0 && $order->qty_received < $order->qty_wmfaturado) {
                            $incomingQty += ($order->qty_wmfaturado - $order->qty_received);

                            $shipping = shipping_erp::where('id_erp', $order->po_id)->orderBy('id', 'DESC')->first();
                            if ($shipping) {
                                $delay = shipping_delay::where('id_shipping', $shipping->id_shipping)->orderBy('date', 'DESC')->first();
                                if ($delay && (!$incomingEta || $delay->date < $incomingEta)) {
                                    $incomingEta = $delay->date;
                                }
                            }
                        }
                    }
                }
            }

            $possiblePacks = (int) floor(($compStock + $incomingQty) / $qtyInPack);
            $expectedPacks = is_null($expectedPacks) ? $possiblePacks : min($expectedPacks, $possiblePacks);

            if ($incomingEta) $etaCandidates[] = $incomingEta;
        }

        $expectedPacks = $expectedPacks ?? 0;

        if ($expectedPacks <= 0) {
            return $foundAnyWaiting ? ['status' => 'out_of_stock'] : ['status' => 'out_of_stock'];
        }

        if (!empty($etaCandidates)) {
            rsort($etaCandidates);             
            $expectedEta = $etaCandidates[0];  

            $eta = Carbon::createFromFormat('Y-m-d', $expectedEta)->format('Y-m-d');

            return [
                'status'   => 'eta',
                'eta'      => $eta,
                'quantity' => $expectedPacks,
                'is_pack'  => true,
            ];
        }

        return ['status' => 'out_of_stock', 'quantity' => 'OUT OF STOCK'];
    }

}
