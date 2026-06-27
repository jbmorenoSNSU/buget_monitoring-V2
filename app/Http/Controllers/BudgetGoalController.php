<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetGoalRequest;
use App\Http\Resources\BudgetGoalResource;
use App\Interfaces\BudgetGoalRepositoryInterface;
use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\PersonRepositoryInterface;
use App\Models\BudgetGoal;
use App\Services\BudgetGoalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles HTTP actions for monthly budget goal management.
 */
class BudgetGoalController extends Controller
{
    public function __construct(
        private BudgetGoalService $service,
        private BudgetGoalRepositoryInterface $repository,
        private CategoryRepositoryInterface $categoryRepository,
        private PersonRepositoryInterface $personRepository,
    ) {}

    /**
     * Display a listing of budget goals for the selected month.
     */
    public function index(Request $request): Response
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        return Inertia::render('BudgetGoals/Index', [
            'goals' => BudgetGoalResource::collection($this->service->get_for_month($month, $year)),
            'month' => $month,
            'year' => $year,
            'categories' => $this->categoryRepository->all_active_expense(),
            'persons' => $this->personRepository->all_active(),
        ]);
    }

    /**
     * Store a newly created budget goal.
     */
    public function store(StoreBudgetGoalRequest $request): RedirectResponse
    {
        $this->authorize('create', BudgetGoal::class);
        $this->repository->create($request->validated());

        return redirect()->back()->with('success', 'Budget goal created successfully.');
    }

    /**
     * Update the specified budget goal.
     */
    public function update(StoreBudgetGoalRequest $request, BudgetGoal $budgetGoal): RedirectResponse
    {
        $this->authorize('update', $budgetGoal);
        $this->repository->update($budgetGoal, $request->validated());

        return redirect()->back()->with('success', 'Budget goal updated successfully.');
    }

    /**
     * Remove the specified budget goal.
     */
    public function destroy(BudgetGoal $budgetGoal): RedirectResponse
    {
        $this->authorize('delete', $budgetGoal);
        $this->repository->delete($budgetGoal);

        return redirect()->back()->with('success', 'Budget goal deleted successfully.');
    }
}
