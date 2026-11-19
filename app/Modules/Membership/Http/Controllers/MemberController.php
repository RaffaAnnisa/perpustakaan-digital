<?php

namespace App\Modules\Membership\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Membership\Http\Requests\StoreMemberRequest;
use App\Modules\Membership\Http\Requests\UpdateMemberRequest;
use App\Modules\Membership\Models\Member;
use App\Modules\Membership\Models\MembershipType;

class MemberController extends Controller
{
    public function index()
    {
        return view('membership::members.index', [
            'members' => Member::with('type')->orderBy('name')->get(),
        ]);
    }

    public function card(Member $member)
    {
        return view('membership::members.card', ['member' => $member->load('type')]);
    }

    public function create()
    {
        return view('membership::members.create', ['types' => MembershipType::orderBy('name')->get()]);
    }

    public function store(StoreMemberRequest $request)
    {
        Member::create($request->validated());

        return redirect()->route('members.index')->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function edit(Member $member)
    {
        return view('membership::members.edit', [
            'member' => $member,
            'types' => MembershipType::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateMemberRequest $request, Member $member)
    {
        $member->update($request->validated());

        return redirect()->route('members.index')->with('success', 'Data anggota diperbarui.');
    }

    public function destroy(Member $member)
    {
        $member->delete();

        return redirect()->route('members.index')->with('success', 'Anggota dihapus.');
    }
}
