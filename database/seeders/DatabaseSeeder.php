<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a complete, self-contained demo dataset.
 *
 * IMPORTANT: every record below is fictional. No real Thardeep Microfinance
 * employee, asset, or credential is included. This data exists purely so the
 * public demo (and anyone cloning the repo) has something realistic to explore.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(20250718); // deterministic demo data across seeds

        $this->seedUsers();
        $this->seedReferenceData();
        $employees = $this->seedEmployees();
        $assets = $this->seedAssets($employees);
        $this->seedHistory($assets, $employees);
    }

    /**
     * A random DateTime between two strtotime-parseable expressions.
     * (Plain PHP — no Faker, so it works in --no-dev production builds too.)
     */
    private function randomDate(string $start, string $end): \DateTime
    {
        $from = strtotime($start);
        $to = strtotime($end);
        if ($to < $from) {
            $to = $from;
        }

        return (new \DateTime())->setTimestamp(mt_rand($from, $to));
    }

    private function randomSerial(): string
    {
        $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $pick = fn () => $letters[mt_rand(0, strlen($letters) - 1)];

        return $pick() . $pick()
            . str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT)
            . $pick() . $pick()
            . str_pad((string) mt_rand(0, 99), 2, '0', STR_PAD_LEFT);
    }

    private function seedUsers(): void
    {
        DB::table('users')->insert([
            [
                'full_name' => 'Demo Administrator',
                'email' => 'admin@tmf.demo',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'full_name' => 'Demo IT Officer',
                'email' => 'it@tmf.demo',
                'password' => Hash::make('password'),
                'role' => 'it',
            ],
        ]);
    }

    private function seedReferenceData(): void
    {
        $types = ['Laptop', 'Desktop', 'Printer', 'Monitor', 'Router', 'Scanner', 'UPS', 'Projector', 'IP Phone'];
        DB::table('asset_types')->insert(array_map(fn ($t) => ['type' => $t], $types));

        $departments = ['IT', 'Finance', 'HR', 'Operations', 'Admin', 'InternalAudit', 'Microfinance'];
        DB::table('departments')->insert(array_map(fn ($d) => ['department' => $d], $departments));

        // Hierarchical Region-Branch-Office-Department strings (single-token segments,
        // because the app splits the location on '-').
        $locations = [
            'South-Hyderabad-HeadOffice-IT',
            'South-Hyderabad-HeadOffice-Finance',
            'South-Hyderabad-HeadOffice-HR',
            'South-Karachi-RegionalOffice-Operations',
            'South-Karachi-RegionalOffice-Microfinance',
            'Central-Sukkur-BranchOffice-Operations',
            'Central-Sukkur-BranchOffice-Admin',
            'North-Larkana-BranchOffice-Microfinance',
            'North-Larkana-BranchOffice-InternalAudit',
        ];
        DB::table('locations')->insert(array_map(fn ($l) => ['location' => $l], $locations));
    }

    /**
     * @return array<int, array{file_no:string, department:string}>
     */
    private function seedEmployees(): array
    {
        $people = [
            ['Ayesha', 'Bano', 'Shaikh', 'IT'],
            ['Bilal', null, 'Ahmed', 'IT'],
            ['Sana', 'Fatima', 'Qureshi', 'Finance'],
            ['Danish', null, 'Ali', 'Finance'],
            ['Hina', null, 'Memon', 'HR'],
            ['Farhan', 'Iqbal', 'Khan', 'Operations'],
            ['Zoya', null, 'Rajput', 'Operations'],
            ['Kamran', null, 'Soomro', 'Admin'],
            ['Mahnoor', 'Zainab', 'Chandio', 'Microfinance'],
            ['Usman', null, 'Bhatti', 'Microfinance'],
            ['Rabia', null, 'Lashari', 'InternalAudit'],
            ['Salman', 'Raza', 'Jamali', 'Operations'],
            ['Nida', null, 'Panhwar', 'Finance'],
            ['Owais', null, 'Abbasi', 'IT'],
        ];

        $rows = [];
        $result = [];
        foreach ($people as $i => [$first, $middle, $last, $dept]) {
            $fileNo = sprintf('TMF-%04d', $i + 1);
            $rows[] = [
                'file_no' => $fileNo,
                'first_name' => $first,
                'middle_name' => $middle,
                'last_name' => $last,
                'email' => strtolower($first . '.' . $last) . '@tmf.demo',
                'department' => $dept,
            ];
            $result[] = ['file_no' => $fileNo, 'department' => $dept];
        }

        DB::table('employees')->insert($rows);

        return $result;
    }

    /**
     * @param  array<int, array{file_no:string, department:string}>  $employees
     * @return array<int, array{asset_tag:string, owner:string, location:string}>
     */
    private function seedAssets(array $employees): array
    {
        $typeCatalog = [
            'Laptop' => ['LT', ['Dell Latitude 5440', 'HP ProBook 450 G10', 'Lenovo ThinkPad E14'], 145000, 260000],
            'Desktop' => ['DT', ['Dell OptiPlex 7010', 'HP EliteDesk 800 G9'], 120000, 210000],
            'Printer' => ['PR', ['HP LaserJet Pro M404', 'Canon imageCLASS MF445', 'Epson EcoTank L3250'], 45000, 130000],
            'Monitor' => ['MN', ['Dell 24" P2422H', 'LG 22MK400H', 'Samsung 27" LF27T'], 28000, 65000],
            'Router' => ['RT', ['MikroTik hEX S', 'Cisco RV340', 'TP-Link ER605'], 18000, 90000],
            'Scanner' => ['SC', ['Canon DR-C230', 'Fujitsu ScanSnap iX1600'], 55000, 140000],
            'UPS' => ['UP', ['APC BX1100C', 'CyberPower 1500VA'], 22000, 70000],
            'Projector' => ['PJ', ['Epson EB-X06', 'BenQ MS550'], 85000, 160000],
            'IP Phone' => ['IP', ['Grandstream GXP1625', 'Yealink SIP-T31P'], 12000, 35000],
        ];

        $locations = array_map(fn ($row) => $row->location, DB::table('locations')->get()->all());
        $statuses = ['None', 'Working', 'Working', 'Working', 'Damaged'];

        $rows = [];
        $result = [];
        $counter = [];

        for ($i = 0; $i < 32; $i++) {
            $typeName = array_rand($typeCatalog);
            [$abbr, $models, $min, $max] = $typeCatalog[$typeName];
            $counter[$abbr] = ($counter[$abbr] ?? 0) + 1;

            $assetTag = sprintf('TMF/%s/%04d', $abbr, $counter[$abbr]);
            $owner = $employees[array_rand($employees)];
            $location = $locations[array_rand($locations)];
            $purchase = $this->randomDate('-3 years', '-2 months');
            $issue = $this->randomDate($purchase->format('Y-m-d'), 'now');
            $status = $statuses[array_rand($statuses)];

            // ~1 in 5 assets awaits an IT remark (populates the IT dashboard queue).
            $needsRemark = $i % 5 === 0;

            $rows[] = [
                'asset_tag' => $assetTag,
                'serial' => $this->randomSerial(),
                'date_of_purchase' => $purchase->format('Y-m-d'),
                'date_of_issue' => $issue->format('Y-m-d'),
                'type' => $typeName,
                'description' => $models[array_rand($models)],
                'amount' => mt_rand($min, $max),
                'location' => $location,
                'owner' => $owner['file_no'],
                'remarks' => $needsRemark ? 'Pending' : ($status === 'Damaged' ? 'Screen flickering, needs inspection.' : 'Remark Inapt'),
                'remarked_by' => $needsRemark ? null : ($status === 'Damaged' ? 'Demo IT Officer' : null),
                'requires_it_remark' => $needsRemark,
                'last_updated_on' => $issue->format('Y-m-d H:i:s'),
                'status' => $needsRemark ? 'None' : $status,
            ];

            $result[] = ['asset_tag' => $assetTag, 'owner' => $owner['file_no'], 'location' => $location];
        }

        DB::table('assets')->insert($rows);

        return $result;
    }

    /**
     * @param  array<int, array{asset_tag:string, owner:string, location:string}>  $assets
     * @param  array<int, array{file_no:string, department:string}>  $employees
     */
    private function seedHistory(array $assets, array $employees): void
    {
        $locations = array_map(fn ($row) => $row->location, DB::table('locations')->get()->all());

        $rows = [];
        // Create a shift history for the first 10 assets.
        foreach (array_slice($assets, 0, 10) as $i => $asset) {
            $newOwner = $employees[array_rand($employees)]['file_no'];
            $newLocation = $locations[array_rand($locations)];
            $needsRemark = $i % 3 === 0;

            $rows[] = [
                'asset_tag' => $asset['asset_tag'],
                'description' => 'Reassigned during branch reorganisation.',
                'prev_location' => $asset['location'],
                'new_location' => $newLocation,
                'prev_owner' => $asset['owner'],
                'new_owner' => $newOwner,
                'remarks' => $needsRemark ? 'Pending' : 'Verified on handover.',
                'remarked_by' => $needsRemark ? null : 'Demo IT Officer',
                'requires_it_remark' => $needsRemark,
                'date' => $this->randomDate('-6 months', 'now')->format('Y-m-d H:i:s'),
                'status' => $needsRemark ? 'None' : 'Working',
            ];
        }

        DB::table('asset_history')->insert($rows);
    }
}
