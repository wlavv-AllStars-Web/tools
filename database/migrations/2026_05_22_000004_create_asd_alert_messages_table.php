<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asd_alert_messages', function (Blueprint $table) {
            $table->id();
            $table->string('title', 250)->default('');
            $table->string('title_en', 125)->default('');
            $table->string('title_es', 125)->default('');
            $table->string('title_fr', 125)->default('');
            $table->string('title_pt', 125)->default('');
            $table->string('title_it', 125)->default('');
            $table->string('message_type', 50)->default('');
            $table->boolean('message_status')->default(false)->index();
            $table->text('message_en');
            $table->text('message_es');
            $table->text('message_fr');
            $table->text('message_pt');
            $table->text('message_it');
            $table->dateTime('creation_date')->nullable();
            $table->dateTime('expiration_date')->nullable();
            $table->dateTime('deleted_date')->nullable();
            $table->boolean('deleted')->default(false)->index();
        });

        $sourceExists = DB::connection('mysql2')
            ->selectOne("SHOW TABLES LIKE 'ps_asd_alert_messages'");

        if (!$sourceExists) {
            return;
        }

        DB::connection('mysql2')
            ->table('ps_asd_alert_messages')
            ->orderBy('id')
            ->chunk(100, function ($rows) {
                $data = $rows->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'title' => $this->safeString($row->title ?? ''),
                        'title_en' => $this->safeString($row->title_en ?? ''),
                        'title_es' => $this->safeString($row->title_es ?? ''),
                        'title_fr' => $this->safeString($row->title_fr ?? ''),
                        'title_pt' => $this->safeString($row->title_pt ?? ''),
                        'title_it' => $this->safeString($row->title_it ?? ''),
                        'message_type' => $this->safeString($row->message_type ?? ''),
                        'message_status' => (int) ($row->message_status ?? 0),
                        'message_en' => (string) ($row->message_en ?? ''),
                        'message_es' => (string) ($row->message_es ?? ''),
                        'message_fr' => (string) ($row->message_fr ?? ''),
                        'message_pt' => (string) ($row->message_pt ?? ''),
                        'message_it' => (string) ($row->message_it ?? ''),
                        'creation_date' => $this->nullableDate($row->creation_date ?? null),
                        'expiration_date' => $this->nullableDate($row->expiration_date ?? null),
                        'deleted_date' => $this->nullableDate($row->deleted_date ?? null),
                        'deleted' => (int) ($row->deleted ?? 0),
                    ];
                })->all();

                DB::table('asd_alert_messages')->insert($data);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('asd_alert_messages');
    }

    private function nullableDate($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' || $value === '0000-00-00 00:00:00' ? null : $value;
    }

    private function safeString($value): string
    {
        return trim((string) $value);
    }
};
