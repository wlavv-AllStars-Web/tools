<?php
namespace App\Console\Commands;
use App\Models\modules\moloni_vat_validation\MoloniVatValidation;
use App\Services\MoloniVat\MoloniVatValidationService;
use Illuminate\Console\Command;
class ProcessDueMoloniVatValidationsCommand extends Command {
 protected $signature='moloni-vat:validate-due {--limit=25}'; protected $description='Validate due Moloni VATs through VIES without blocking invoice issuance.';
 public function handle(MoloniVatValidationService $service):int{$expired=$service->expireValidatedVats();$items=MoloniVatValidation::query()->due()->orderBy('next_attempt_at')->orderBy('id')->limit(max(1,(int)$this->option('limit')))->get();foreach($items as $item)$service->process($item);$this->info('Expired: '.$expired.'; processed: '.$items->count());return self::SUCCESS;}
}
