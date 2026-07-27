<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The application reads assets/history through two SQL VIEWS that join the
     * owner file numbers to a friendly "FILE - First Middle Last" label
     * (App\Models\AssetDisplay maps to `asset_display`, and the history views
     * back the IT dashboard + advanced query). The original project defined
     * these views in MySQL; here we emit dialect-appropriate SQL so the exact
     * same views can be created on MySQL/MariaDB *or* SQLite (demo mode).
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        DB::statement('DROP VIEW IF EXISTS asset_display');
        DB::statement('DROP VIEW IF EXISTS asset_history_display');

        if ($driver === 'sqlite') {
            DB::statement(<<<'SQL'
                CREATE VIEW asset_display AS
                SELECT a.id, a.asset_tag, a.serial, a.status, a.date_of_purchase,
                       a.date_of_issue, a.type, a.description, a.amount, a.location, a.owner,
                       (e.file_no || ' - ' || e.first_name ||
                        CASE WHEN e.middle_name IS NOT NULL AND e.middle_name <> ''
                             THEN ' ' || e.middle_name ELSE '' END
                        || ' ' || e.last_name) AS owner_full_name,
                       a.remarks, a.remarked_by, a.requires_it_remark, a.last_updated_on
                FROM assets a
                LEFT JOIN employees e ON a.owner = e.file_no
                SQL);

            DB::statement(<<<'SQL'
                CREATE VIEW asset_history_display AS
                SELECT h.id, h.asset_tag, h.status, h.description, h.prev_location, h.new_location,
                       h.prev_owner,
                       (e1.file_no || ' - ' || e1.first_name ||
                        CASE WHEN e1.middle_name IS NOT NULL AND e1.middle_name <> ''
                             THEN ' ' || e1.middle_name ELSE '' END
                        || ' ' || e1.last_name) AS prev_owner_full_name,
                       h.new_owner,
                       (e2.file_no || ' - ' || e2.first_name ||
                        CASE WHEN e2.middle_name IS NOT NULL AND e2.middle_name <> ''
                             THEN ' ' || e2.middle_name ELSE '' END
                        || ' ' || e2.last_name) AS new_owner_full_name,
                       h.remarks, h.remarked_by, h.requires_it_remark, h.date
                FROM asset_history h
                LEFT JOIN employees e1 ON h.prev_owner = e1.file_no
                LEFT JOIN employees e2 ON h.new_owner = e2.file_no
                SQL);
        } else {
            // MySQL / MariaDB
            DB::statement(<<<'SQL'
                CREATE VIEW asset_display AS
                SELECT a.id, a.asset_tag, a.serial, a.status, a.date_of_purchase,
                       a.date_of_issue, a.type, a.description, a.amount, a.location, a.owner,
                       CONCAT(e.file_no, ' - ', e.first_name,
                              IF(e.middle_name IS NOT NULL AND e.middle_name <> '',
                                 CONCAT(' ', e.middle_name), ''),
                              ' ', e.last_name) AS owner_full_name,
                       a.remarks, a.remarked_by, a.requires_it_remark, a.last_updated_on
                FROM assets a
                LEFT JOIN employees e ON a.owner = e.file_no
                SQL);

            DB::statement(<<<'SQL'
                CREATE VIEW asset_history_display AS
                SELECT h.id, h.asset_tag, h.status, h.description, h.prev_location, h.new_location,
                       h.prev_owner,
                       CONCAT(e1.file_no, ' - ', e1.first_name,
                              IF(e1.middle_name IS NOT NULL AND e1.middle_name <> '',
                                 CONCAT(' ', e1.middle_name), ''),
                              ' ', e1.last_name) AS prev_owner_full_name,
                       h.new_owner,
                       CONCAT(e2.file_no, ' - ', e2.first_name,
                              IF(e2.middle_name IS NOT NULL AND e2.middle_name <> '',
                                 CONCAT(' ', e2.middle_name), ''),
                              ' ', e2.last_name) AS new_owner_full_name,
                       h.remarks, h.remarked_by, h.requires_it_remark, h.date
                FROM asset_history h
                LEFT JOIN employees e1 ON h.prev_owner = e1.file_no
                LEFT JOIN employees e2 ON h.new_owner = e2.file_no
                SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS asset_display');
        DB::statement('DROP VIEW IF EXISTS asset_history_display');
    }
};
