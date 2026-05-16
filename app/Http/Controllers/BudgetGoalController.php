<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetGoalRequest;
use App\Http\Resources\BudgetGoalResource;
use App\Models\BudgetGoal;
use App\Models\Category;
use App\Services\BudgetGoalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BudgetGoalController extends Controller
{
    public function __construct(private BudgetGoalService $service) {}

    public function index(Request $request): Response
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        return Inertia::render('BudgetGoals/Index', [
            'goals' => BudgetGoalResource::collection($this->service->getForMonth($month, $year)),
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('BudgetGoals/Form', [
            'categories' => Category::active()->expense()->orderBy('name')->get(['id', 'name', 'icon', 'color']),
        ]);
    }

    public function store(StoreBudgetGoalRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());
        return redirect()->route('budget-goals.index')->with('success', 'Budget goal created successfully.');
    }

    public function edit(BudgetGoal $budgetGoal): Response
    {
        return Inertia::render('BudgetGoals/Form', [
            'goal' => new BudgetGoalResource($budgetGoal->load('category')),
            'categories' => Category::active()->expense()->orderBy('name')->get(['id', 'name', 'icon', 'color']),
        ]);
    }

    public function update(StoreBudgetGoalRequest $request, BudgetGoal $budgetGoal): RedirectResponse
    {
        $this->service->update($budgetGoal, $request->validated());
        return redirect()->route('budget-goals.index')->with('success', 'Budget goal updated successfully.');
    }

    public function destroy(BudgetGoal $budgetGoal): RedirectResponse
    {
        $this->service->delete($budgetGoal);
        return redirect()->route('budget-goals.index')->with('success', 'Budget goal deleted successfully.');
    }
}
