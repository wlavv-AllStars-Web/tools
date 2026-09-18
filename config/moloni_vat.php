<?php
return ['token'=>env('ALLSTARS_MOLONI_VAT_API_TOKEN'),'valid_days'=>(int)env('MOLONI_VAT_VALID_DAYS',7),'max_attempts'=>(int)env('MOLONI_VAT_MAX_ATTEMPTS',6),'reconciliation_lookback_days'=>(int)env('MOLONI_VAT_RECONCILIATION_LOOKBACK_DAYS',14)];
