<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Resources\AccountResource;
use App\Interfaces\AccountRepositoryInterface;
use App\Interfaces\PersonRepositoryInterface;
use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles HTTP actions for financial account management.
 */
class AccountController extends Controller
{
    public function __construct(
        private AccountRepositoryInterface $repository,
        private PersonRepositoryInterface $personRepository
    ) {}

    public function index(): Response
    {
        return Inertia::render('Accounts/Index', [
            'accounts' => AccountResource::collection($this->repository->all()),
            'totalBalance' => $this->repository->total_balance(),
            'accountTypes' => AccountType::select(['id', 'name', 'icon'])->orderBy('name')->get(),
            'persons' => $this->personRepository->all_active(),
        ]);
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $this->authorize('create', Account::class);
        $this->repository->create($request->validated());

        return redirect()->back()->with('success', 'Account created successfully.');
    }

    public function update(StoreAccountRequest $request, Account $account): RedirectResponse
    {
        $this->authorize('update', $account);
        $this->repository->update($account, $request->validated());

        return redirect()->back()->with('success', 'Account updated successfully.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        $this->authorize('delete', $account);
        if ($this->repository->has_transactions($account)) {
            return redirect()->back()->with('error', 'Cannot delete account with transactions. Deactivate it instead.');
        }
        $this->repository->delete($account);

        return redirect()->back()->with('success', 'Account deleted successfully.');
    }

    public function toggle(Account $account): RedirectResponse
    {
        $this->authorize('update', $account);
        $this->repository->update($account, ['is_active' => ! $account->is_active]);
        $status = $account->fresh()->is_active ? 'activated' : 'deactivated';

        return redirect()->back()->with('success', "Account {$status} successfully.");
    }
}
