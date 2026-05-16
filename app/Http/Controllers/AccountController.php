<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Models\AccountType;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function __construct(private AccountService $service) {}

    public function index(): Response
    {
        return Inertia::render('Accounts/Index', [
            'accounts' => AccountResource::collection($this->service->getAll()),
            'totalBalance' => $this->service->getTotalBalance(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Accounts/Form', [
            'accountTypes' => AccountType::all(),
        ]);
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());
        return redirect()->route('accounts.index')->with('success', 'Account created successfully.');
    }

    public function edit(Account $account): Response
    {
        return Inertia::render('Accounts/Form', [
            'account' => new AccountResource($account->load('accountType')),
            'accountTypes' => AccountType::all(),
        ]);
    }

    public function update(StoreAccountRequest $request, Account $account): RedirectResponse
    {
        $this->service->update($account, $request->validated());
        return redirect()->route('accounts.index')->with('success', 'Account updated successfully.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        if (!$this->service->canDelete($account)) {
            return redirect()->route('accounts.index')->with('error', 'Cannot delete account with transactions. Deactivate it instead.');
        }
        $this->service->delete($account);
        return redirect()->route('accounts.index')->with('success', 'Account deleted successfully.');
    }

    public function toggle(Account $account): RedirectResponse
    {
        $this->service->toggle($account);
        $status = $account->fresh()->is_active ? 'activated' : 'deactivated';
        return redirect()->route('accounts.index')->with('success', "Account {$status} successfully.");
    }
}
