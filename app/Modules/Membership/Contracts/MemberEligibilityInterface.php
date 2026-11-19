<?php

namespace App\Modules\Membership\Contracts;

/**
 * Contract publik modul Membership.
 * Modul lain TIDAK boleh memakai model Member / MembershipType langsung.
 */
interface MemberEligibilityInterface
{
    /** @return array{id:int,member_code:string,name:string,status:string,type:string,max_loans:int,loan_days:int,fine_per_day:int} */
    public function getMemberInfo(int $memberId): array;

    /** @return array<int, array> dikunci berdasarkan id */
    public function getMembersByIds(array $memberIds): array;

    /** @return array{active:int,inactive:int} */
    public function countByStatus(): array;

    /** Daftar anggota berstatus aktif. */
    public function listActive(): array;

    /**
     * Memastikan anggota boleh meminjam.
     *
     * @param  int  $currentActiveItems  jumlah buku yang sedang dipinjam (dihitung oleh Circulation)
     * @param  int  $requestedItems      jumlah buku yang ingin dipinjam sekarang
     * @return array aturan anggota (loan_days, fine_per_day, ...)
     *
     * @throws \App\Modules\Membership\Exceptions\MemberInactiveException
     * @throws \App\Modules\Membership\Exceptions\LoanLimitExceededException
     */
    public function assertCanBorrow(int $memberId, int $currentActiveItems, int $requestedItems): array;
}
