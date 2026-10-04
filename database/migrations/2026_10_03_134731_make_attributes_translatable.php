<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($result) > 0;
    }

    private function columnIsJson(string $table, string $column): bool
    {
        $row = DB::selectOne(
            'SELECT DATA_TYPE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column]
        );

        return $row && strtolower($row->DATA_TYPE) === 'json';
    }

    public function up(): void
    {
        // --- attributes.name: string -> JSON -------------------------------
        if (! $this->columnIsJson('attributes', 'name')) {
            if (! Schema::hasColumn('attributes', 'name_translated')) {
                Schema::table('attributes', function (Blueprint $table) {
                    $table->json('name_translated')->nullable()->after('name');
                });
                foreach (DB::table('attributes')->get() as $row) {
                    DB::table('attributes')->where('id', $row->id)->update([
                        'name_translated' => json_encode(['en' => $row->name, 'ar' => $row->name]),
                    ]);
                }
            }
            Schema::table('attributes', function (Blueprint $table) {
                $table->dropColumn('name');
            });
            Schema::table('attributes', function (Blueprint $table) {
                $table->renameColumn('name_translated', 'name');
            });
        }

        // --- attribute_values.value: string -> JSON ------------------------
        if (! $this->columnIsJson('attribute_values', 'value')) {
            // The composite unique index (attribute_id, value) is also the
            // only index covering attribute_id, which the foreign key to
            // 'attributes' depends on. MySQL won't let us drop it until
            // something else covers that column, so add a plain index first.
            if (! $this->indexExists('attribute_values', 'attribute_values_attribute_id_index')) {
                Schema::table('attribute_values', function (Blueprint $table) {
                    $table->index('attribute_id', 'attribute_values_attribute_id_index');
                });
            }

            // Uniqueness on a translatable JSON value isn't meaningful at the
            // DB level anyway; enforce "no duplicate value per attribute" in
            // the Filament form instead, if you need it.
            if ($this->indexExists('attribute_values', 'attribute_values_attribute_id_value_unique')) {
                Schema::table('attribute_values', function (Blueprint $table) {
                    $table->dropUnique('attribute_values_attribute_id_value_unique');
                });
            }

            if (! Schema::hasColumn('attribute_values', 'value_translated')) {
                Schema::table('attribute_values', function (Blueprint $table) {
                    $table->json('value_translated')->nullable()->after('value');
                });
                foreach (DB::table('attribute_values')->get() as $row) {
                    DB::table('attribute_values')->where('id', $row->id)->update([
                        'value_translated' => json_encode(['en' => $row->value, 'ar' => $row->value]),
                    ]);
                }
            }

            Schema::table('attribute_values', function (Blueprint $table) {
                $table->dropColumn('value');
            });
            Schema::table('attribute_values', function (Blueprint $table) {
                $table->renameColumn('value_translated', 'value');
            });
        }
    }

    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->string('name_plain')->nullable()->after('name');
        });
        foreach (DB::table('attributes')->get() as $row) {
            $decoded = json_decode($row->name, true);
            DB::table('attributes')->where('id', $row->id)->update([
                'name_plain' => $decoded['en'] ?? '',
            ]);
        }
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn('name');
        });
        Schema::table('attributes', function (Blueprint $table) {
            $table->renameColumn('name_plain', 'name');
        });

        Schema::table('attribute_values', function (Blueprint $table) {
            $table->string('value_plain')->nullable()->after('value');
        });
        foreach (DB::table('attribute_values')->get() as $row) {
            $decoded = json_decode($row->value, true);
            DB::table('attribute_values')->where('id', $row->id)->update([
                'value_plain' => $decoded['en'] ?? '',
            ]);
        }
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->dropColumn('value');
        });
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->renameColumn('value_plain', 'value');
        });
    }
};