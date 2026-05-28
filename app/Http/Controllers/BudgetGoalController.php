<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\BudgetGoal\CreateBudgetGoalAction;
use App\Actions\BudgetGoal\UpdateBudgetGoalAction;
use App\DTOs\BudgetGoalDTO;
use App\Http\Requests\StoreBudgetGoalRequest;
use App\Http\Resources\BudgetGoalResource;
use App\Interfaces\CategoryRepositoryInterface;
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
        private CategoryRepositoryInterface $categoryRepository,
        private CreateBudgetGoalAction $createBudgetGoal,
        private UpdateBudgetGoalAction $updateBudgetGoal,
    ) {}

    public function index(Request $request): Response
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        return Inertia::render('BudgetGoals/Index', [
            'goals' => BudgetGoalResource::collection($this->service->get_for_month($month, $year)),
            'month' => $month,
            'year'  => $year,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('BudgetGoals/Form', [
            'categories' => $this->categoryRepository->all_active_expense(),
        ]);
    }

    public function store(StoreBudgetGoalRequest $request): RedirectResponse
    {
        $this->authorize('create', BudgetGoal::class);
        $this->createBudgetGoal->execute(BudgetGoalDTO::fromArray($request->validated()));
        return redirect()->route('budget-goals.index')->with('success', 'Budget goal created successfully.');
    }

    public function edit(BudgetGoal $budgetGoal): Response
    {
        return Inertia::render('BudgetGoals/Form', [
            'goal'       => new BudgetGoalResource($budgetGoal->load('category:id,name,icon,color')),
            'categories' => $this->categoryRepository->all_active_expense(),
        ]);
    }

    public function update(StoreBudgetGoalRequest $request, BudgetGoal $budgetGoal): RedirectResponse
    {
        $this->authorize('update', $budgetGoal);
        $this->updateBudgetGoal->execute($budgetGoal, BudgetGoalDTO::fromArray($request->validated()));
        return redirect()->route('budget-goals.index')->with('success', 'Budget goal updated successfully.');
    }

    public function destroy(BudgetGoal $budgetGoal): RedirectResponse
    {
        $this->authorize('delete', $budgetGoal);
        $this->service->delete($budgetGoal);
        return redirect()->route('budget-goals.index')->with('success', 'Budget goal deleted successfully.');
    }
}
