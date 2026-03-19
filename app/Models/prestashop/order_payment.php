<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Config;

use App\Models\prestashop\orders;

class order_payment extends Model
{   
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."order_payment";
    }

    public static function objectiveByMonth(){
        
        $objectivosByMonth = array();
        
        for($i=0; $i<12; $i++){

            $dateString = date('Y') . '-' . sprintf('%02d', $i+1) . '-01';
            $lastDateOfMonth = date("Y-m-t", strtotime($dateString));
        
            $lastYearDateString = date('Y', strtotime("-1 years")) . '-' . sprintf('%02d', $i+1) . '-01';
            $lastYearlastDateOfMonth = date("Y-m-t", strtotime($lastYearDateString));
            
            $row_this_year= self::getTotals($dateString, $lastDateOfMonth);
            $row_last_year= self::getTotals($lastYearDateString, $lastYearlastDateOfMonth);
            
            $objectivosByMonth[$i+1] = (object)[ "current" => $row_this_year+0, "lastYear" => $row_last_year+0];
        }
        
        return $objectivosByMonth;
    }

    public static function objectiveByMonthASD(){
        
        $ch = curl_init( "https://www.all-stars-distribution.com/custom/front/getEstadoObjectivoByMonth.php" );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $server_output = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($server_output);
        
        $objectivosByMonth = array();
        
        foreach($data->objectivos AS $month=> $objective){
            $objectivosByMonth[$month] = (object)[ "current" => $objective->current+0, "lastYear" => $objective->lastYear+0];
        }
        
        return $objectivosByMonth;
    }
    
    public static function getCounters(){
        
        return (object)[
            'getActual' => self::getASMActual(),
            'getActualCurrentMonth' => self::getASMActualCurrentMonth(),
            'getAcumuladoAnoAnterior' => self::getASMAcumuladoAnoAnterior(),
            'getAcumuladoAnoAnteriorHomologo' => self::getASMAcumuladoAnoAnteriorHomologo(),
            'getASMAcumuladoAnoAnteriorHomologoCurrentMonth' => self::getASMAcumuladoAnoAnteriorHomologoCurrentMonth()
        ];
    }

    private static function getASMActualCurrentMonth(){
        $current_year = date('Y') . "-" . date('m') . "-01";
        $current_day  = date('Y') . '-' . date('m')  . '-' . date('d');
        return self::getTotals($current_year, $current_day);
    }

    private static function getASMActual(){
    
        $current_year = date('Y') . "-01-01";
        $current_day  = date('Y') . '-' . date('m')  . '-' . date('d');
        return self::getTotals($current_year, $current_day);
    }
    
    private static function getASMAcumuladoAnoAnterior(){
    
        $start_last_year = date('Y', strtotime("-1 years")) . "-01-01";
        $end_last_year   = date('Y', strtotime("-1 years")) . "-12-31";
        return self::getTotals($start_last_year, $end_last_year);
    }
    
    private static function getASMAcumuladoAnoAnteriorHomologo(){
    
        $start_last_year = date('Y', strtotime("-1 years")) . "-01-01";
        $current_day_last_year   = date('Y', strtotime("-1 years")) . "-" . date('m') . "-" . date('d');
        return self::getTotals($start_last_year, $current_day_last_year);
    }
    
    private static function getASMAcumuladoAnoAnteriorHomologoCurrentMonth(){
    
        $start_last_year = date('Y', strtotime("-1 years")) . "-" . date('m') . "-01";
        $current_day_last_year   = date('Y', strtotime("-1 years")) . "-" . date('m') . "-" . date('d');
        return self::getTotals($start_last_year, $current_day_last_year);
    }
    
    private static function getTotals($start_date, $end_date){

        /** Todas as encomendas que tiveram o estado 2 ( Pagamento aceite ) entre as datas recebidas **/
        $ids_order = order_history::select('id_order')
                        ->where('date_add', '>', " $start_date  00:00:00")->where('date_add', '<', " $end_date 23:59:59")
                        ->where('id_order_state', 2)
                        ->groupBy('id_order')
                        ->pluck('id_order')->toArray();
                        
        /** Todas as encomendas que passaram a canceladas ou reembolsadas no intervalo de datas **/                
        $ids_order_refunded = order_history::whereBetween('date_add', ["$start_date 00:00:00", "$end_date 23:59:59"])
            ->where('id_order_state', 7)
            ->distinct()
            ->pluck('id_order')
            ->toArray();
            
        /** Total de descontos **/
        $discount_from_total = orders::select(DB::raw('sum(ps_orders.total_paid_tax_excl) AS total'))->whereIN('id_order', $ids_order_refunded)->value('total');

        /** De todas as encomendas anteriores, devolve apenas as que estao no estados [2, 3, 4, 5, 15, 16, 28, 30, 31] **/
        $total = orders::select(DB::raw('sum(ps_orders.total_paid_tax_excl) AS total'))
                ->whereIN('id_order', $ids_order)
                ->whereIN('current_state', [2, 3, 4, 5, 15, 16, 28, 30, 31])
                ->value('total');

        /** Apenas desconta os refunds do ano atual, não do ano anterior  **/
        if (date('Y', strtotime($start_date)) == date('Y')) {
            return $total - $discount_from_total;
        }else{
            return $total;
        }
    }
    
    public static function projection($expectedEvolution){
    
        $totalMonth = 0;
        $totalMonthObjectivoASM = 0;
        $totalMonthObjectivoASD = 0;
        $totalMonthObjectivo = 0;
        $totalMonthFacturado = 0;
        $totalMonthPercentage= 0;
        $totalMonthLastYear = 0;
        
        $days = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
        $totaisDiarios = array();
        
        for($i = 0; $i < ($days); $i++) {
            
            $day = sprintf('%02d', $i+1);
            $date = date('Y') . '-' . date('m') . '-' . $day;
            $current = self::getTotals($date, $date);
            
            $homologue_day = date('Y', strtotime("-1 years")) . '-' . date('m') . '-' . $day;
            $homologue = self::getTotals($homologue_day, $homologue_day);

            $ASD_stream = self::getASDday($day);

            $ASD= (object)[
                'homologue' => ( isset($ASD_stream->value_objectivo)) ? $ASD_stream->value_objectivo : 0,
                'current' => ( isset($ASD_stream->value)) ? $ASD_stream->value : 0,
            ];

            $percentage =  ( ( $current + $ASD->current ) > 0 ) ? 100 - (( ($homologue + $ASD->homologue) * $expectedEvolution / ( $current + $ASD->current ) ) * 100 ) : 0;
            
            $valorDia['name'] = $day . '-' . date('m') . '-' . date('Y');
            $valorDia['lastyear'] = number_format(($homologue + $ASD->homologue), 2, ',', ' ') . ' €';
            $valorDia['lastyear_asm'] = number_format(($homologue), 2, ',', ' ') . ' €';
            $valorDia['lastyear_asd'] = number_format(($ASD->homologue), 2, ',', ' ') . ' €';
            $valorDia['objective'] = number_format(($homologue + $ASD->homologue) * $expectedEvolution, 2, ',', ' ') . ' €';
            $valorDia['invoiced'] = number_format( ( $current + $ASD->current ), 2, ',', ' ') . ' €';
            $valorDia['invoicedValue'] = ( $current + $ASD->current );
            $valorDia['accomplished'] = ( ($current + $ASD->current) > ( ($homologue + $ASD->homologue) * $expectedEvolution ) ) ? 1 : 0;
            $valorDia['percentage'] = number_format( $percentage, 2, ',', ' ') . ' %';

            $totaisDiarios[] = (object)$valorDia;
            
            $totalMonthObjectivoASM += $homologue/$expectedEvolution;
            $totalMonthObjectivoASD += $ASD->homologue/$expectedEvolution;
            $totalMonthObjectivo += $homologue + $ASD->homologue;
            $totalMonthFacturado += ( $current + $ASD->current );
            $totalMonthLastYear += ($homologue + $ASD->homologue);
        } 
        
        return (object)[
            'dataMonth' => $totaisDiarios,
            'totalMonthObjectivoASM' => number_format($totalMonthObjectivoASM * $expectedEvolution, 2, ',', ' ') . ' €',
            'totalMonthObjectivoASD' => number_format($totalMonthObjectivoASD * $expectedEvolution, 2, ',', ' ') . ' €',
            'totalMonthObjectivo' => number_format($totalMonthObjectivo * $expectedEvolution, 2, ',', ' ') . ' €',
            'totalMonthFacturado' => number_format($totalMonthFacturado, 2, ',', ' ') . ' €',
            'totalMonthObjectivoValue' => $totalMonthObjectivo * $expectedEvolution,
            'totalMonthFacturadoValue' => $totalMonthFacturado,
            'totalMonthLastYear' => number_format($totalMonthLastYear, 2, ',', ' ') . ' €'
        ];
    }
    
    public static function getASDday($day){

        $ch = curl_init( "https://www.all-stars-distribution.com/custom/front/getDayValue.php?day=" . $day );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $server_output = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($server_output);
    }
    
    public static function getASDActual(){
    
        $ch = curl_init( "https://www.all-stars-distribution.com/custom/front/getEstadoObjectivoActual.php" );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $server_output = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($server_output);
        return $data;
    }
    
    public static function getASDHomologo(){
    
        $ch = curl_init( "https://www.all-stars-distribution.com/custom/front/getEstadoObjectivoActualHomologo.php" );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $server_output = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($server_output);
        return $data;
    }
    
    public static function getASMTotals($today){
        
        if($today == 1){
            $homologue_day = date('Y-m-d', strtotime("-1 year"));
            $day = date('Y') . '-' . date('m') . '-' . date('d');
        }else{
            $homologue_day = date('Y-m-d', strtotime("-1 years -1 day"));
            $day= date('Y-m-d', strtotime("-1 day"));
        }

        $homologue = self::getTotals($homologue_day, $homologue_day);
        $current = self::getTotals($day, $day);
        
        return (object)['day' => $current, 'homologue_day' => $homologue];
    }

}
