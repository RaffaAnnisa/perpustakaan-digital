<?php

namespace App\Modules\Membership\Database\Seeders;

use App\Modules\Membership\Models\Member;
use App\Modules\Membership\Models\MembershipType;
use Illuminate\Database\Seeder;

class MembershipSeeder extends Seeder
{
    public function run(): void
    {
        $types = collect([
            ['name' => 'Reguler', 'max_loans' => 3, 'loan_days' => 7,  'fine_per_day' => 1000],
            ['name' => 'Premium', 'max_loans' => 5, 'loan_days' => 14, 'fine_per_day' => 500],
            ['name' => 'Pelajar', 'max_loans' => 2, 'loan_days' => 5,  'fine_per_day' => 500],
        ])->map(fn ($t) => MembershipType::firstOrCreate(['name' => $t['name']], $t));

        $people = ['Siti Rahmawati', 'Budi Santoso', 'Dewi Lestari', 'Agus Prasetyo', 'Rina Wulandari', 'Fajar Nugroho', 'Maya Anggraini', 'Dimas Putra'];

        foreach ($people as $i => $name) {
            Member::create([
                'membership_type_id' => $types[$i % $types->count()]->id,
                'member_code' => 'MBR-'.str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)).'@contoh.id',
                'status' => 'active',
            ]);
        }

        // satu anggota nonaktif untuk mendemokan penolakan
        Member::create([
            'membership_type_id' => $types->first()->id,
            'member_code' => 'MBR-00099',
            'name' => 'Anggota Nonaktif',
            'email' => null,
            'status' => 'inactive',
        ]);
    }
}
