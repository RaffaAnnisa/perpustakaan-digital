<?php

namespace App\Modules\Membership\Services;

use App\Modules\Membership\Contracts\MemberEligibilityInterface;
use App\Modules\Membership\Exceptions\LoanLimitExceededException;
use App\Modules\Membership\Exceptions\MemberInactiveException;
use App\Modules\Membership\Models\Member;

class MemberEligibilityService implements MemberEligibilityInterface
{
    public function getMemberInfo(int $memberId): array
    {
        return $this->toArray(Member::with('type')->findOrFail($memberId));
    }

    public function getMembersByIds(array $memberIds): array
    {
        return Member::with('type')->whereIn('id', $memberIds)->get()
            ->mapWithKeys(fn (Member $m) => [$m->id => $this->toArray($m)])
            ->all();
    }

    public function countByStatus(): array
    {
        return [
            'active' => Member::where('status', 'active')->count(),
            'inactive' => Member::where('status', 'inactive')->count(),
        ];
    }

    public function listActive(): array
    {
        return Member::with('type')->where('status', 'active')->orderBy('name')->get()
            ->map(fn (Member $m) => $this->toArray($m))
            ->all();
    }

    public function assertCanBorrow(int $memberId, int $currentActiveItems, int $requestedItems): array
    {
        $member = Member::with('type')->findOrFail($memberId);

        if (! $member->isActive()) {
            throw new MemberInactiveException($member->name);
        }

        $max = $member->type->max_loans;
        if ($currentActiveItems + $requestedItems > $max) {
            throw new LoanLimitExceededException($member->name, $max, $currentActiveItems);
        }

        return $this->toArray($member);
    }

    private function toArray(Member $m): array
    {
        return [
            'id' => $m->id,
            'member_code' => $m->member_code,
            'name' => $m->name,
            'status' => $m->status,
            'type' => $m->type->name,
            'max_loans' => $m->type->max_loans,
            'loan_days' => $m->type->loan_days,
            'fine_per_day' => $m->type->fine_per_day,
        ];
    }
}
