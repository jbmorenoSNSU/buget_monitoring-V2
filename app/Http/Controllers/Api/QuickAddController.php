<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\AccountRepositoryInterface;
use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\DebtRepositoryInterface;
use App\Interfaces\PersonRepositoryInterface;
use Illuminate\Http\JsonResponse;

/**
 * Handles the quick-add form data endpoint for the global transaction modal.
 */
class QuickAddController extends Controller
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private DebtRepositoryInterface $debtRepository,
        private PersonRepositoryInterface $personRepository
    ) {}

    /**
     * Return all reference data needed for the quick-add transaction form.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'accounts'   => $this->accountRepository->all_active(),
            'categories' => $this->categoryRepository->all_active(),
            'debts'      => $this->debtRepository->get_active(),
            'persons'    => $this->personRepository->all_active(),
        ]);
    }
}
