<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\SavingsGoal\SavingsGoalDTO;
use App\Http\Requests\StoreSavingsGoalRequest;
use App\Http\Requests\UpdateSavingsGoalRequest;
use App\Interfaces\AccountRepositoryInterface;
use App\Interfaces\PersonRepositoryInterface;
use App\Models\SavingsGoal;
use App\Services\SavingsGoalService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SavingsGoalController extends Controller
{
    public function __construct(
        private SavingsGoalService $savingsGoalService,
        private AccountRepositoryInterface $accountRepository,
        private PersonRepositoryInterface $personRepository
    ) {}

    /**
     * Display a listing of savings goals.
     */
    public function index(): Response
    {
        return Inertia::render('SavingsGoals/Index', [
            'goals' => $this->savingsGoalService->all(),
            'accounts' => $this->accountRepository->all_active(),
            'persons' => $this->personRepository->all_active(),
        ]);
    }

    /**
     * Store a newly created savings goal.
     */
    public function store(StoreSavingsGoalRequest $request): RedirectResponse
    {
        $this->authorize('create', SavingsGoal::class);
        $this->savingsGoalService->create(SavingsGoalDTO::fromRequest($request->validated()));

        return redirect()->back()->with('success', 'Savings goal created successfully.');
    }

    /**
     * Update the specified savings goal.
     */
    public function update(UpdateSavingsGoalRequest $request, SavingsGoal $savingsGoal): RedirectResponse
    {
        $this->authorize('update', $savingsGoal);
        $this->savingsGoalService->update($savingsGoal, SavingsGoalDTO::fromRequest($request->validated()));

        return redirect()->back()->with('success', 'Savings goal updated successfully.');
    }

    /**
     * Remove the specified savings goal.
     */
    public function destroy(SavingsGoal $savingsGoal): RedirectResponse
    {
        $this->authorize('delete', $savingsGoal);
        $this->savingsGoalService->delete($savingsGoal);

        return redirect()->back()->with('success', 'Savings goal deleted successfully.');
    }
}
