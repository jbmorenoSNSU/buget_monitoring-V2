<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Account\CreateAccountAction;
use App\Actions\Account\UpdateAccountAction;
use App\DTOs\AccountDTO;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Resources\AccountResource;
use App\Interfaces\AccountTypeRepositoryInterface;
use App\Interfaces\PersonRepositoryInterface;
use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles HTTP actions for financial account management.
 */
class AccountController extends Controller
{
    public function __construct(
        private AccountService $service,
        private PersonRepositoryInterface $personRepository,
        private AccountTypeRepositoryInterface $accountTypeRepository,
        private CreateAccountAction $createAccount,
        private UpdateAccountAction $updateAccount,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Accounts/Index', [
            'accounts' => AccountResource::collection($this->service->get_all()),
            'totalBalance' => $this->service->get_total_balance(),
            'accountTypes' => $this->accountTypeRepository->all(),
            'persons' => $this->personRepository->all_active(),
        ]);
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $this->authorize('create', Account::class);
        $this->createAccount->execute(AccountDTO::fromArray($request->validated()));

        return redirect()->route('accounts.index')->with('success', 'Account created successfully.');
    }

    public function update(StoreAccountRequest $request, Account $account): RedirectResponse
    {
        $this->authorize('update', $account);
        $this->updateAccount->execute($account, AccountDTO::fromArray($request->validated()));

        return redirect()->route('accounts.index')->with('success', 'Account updated successfully.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        $this->authorize('delete', $account);
        if (! $this->service->can_delete($account)) {
            return redirect()->route('accounts.index')->with('error', 'Cannot delete account with transactions. Deactivate it instead.');
        }
        $this->service->delete($account);

        return redirect()->route('accounts.index')->with('success', 'Account deleted successfully.');
    }

    public function toggle(Account $account): RedirectResponse
    {
        $this->authorize('update', $account);
        $this->service->toggle($account);
        $status = $account->fresh()->is_active ? 'activated' : 'deactivated';

        return redirect()->route('accounts.index')->with('success', "Account {$status} successfully.");
    }
}
