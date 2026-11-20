<?php

namespace App\Modules\Membership\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Membership\Exceptions\MembershipTypeInUseException;
use App\Modules\Membership\Http\Requests\StoreMembershipTypeRequest;
use App\Modules\Membership\Http\Requests\UpdateMembershipTypeRequest;
use App\Modules\Membership\Models\MembershipType;

class MembershipTypeController extends Controller
{
    public function index()
    {
        return view('membership::types.index', [
            'types' => MembershipType::withCount('members')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('membership::types.form');
    }

    public function store(StoreMembershipTypeRequest $request)
    {
        MembershipType::create($request->validated());

        return redirect()->route('membership-types.index')
            ->with('success', 'Jenis keanggotaan berhasil ditambahkan.');
    }

    public function edit(MembershipType $membershipType)
    {
        return view('membership::types.form', ['type' => $membershipType]);
    }

    public function update(UpdateMembershipTypeRequest $request, MembershipType $membershipType)
    {
        // Peminjaman yang sudah berjalan tidak terpengaruh: tarif denda disalin (snapshot) ke tabel loans saat meminjam.
        $membershipType->update($request->validated());

        return redirect()->route('membership-types.index')
            ->with('success', 'Jenis keanggotaan berhasil diperbarui.');
    }

    public function destroy(MembershipType $membershipType)
    {
        $count = $membershipType->members()->count();

        if ($count > 0) {
            throw new MembershipTypeInUseException($membershipType->name, $count);
        }

        $membershipType->delete();

        return redirect()->route('membership-types.index')
            ->with('success', 'Jenis keanggotaan berhasil dihapus.');
    }
}
