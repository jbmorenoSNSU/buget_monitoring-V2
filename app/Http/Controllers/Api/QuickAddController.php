<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\AccountRepositoryInterface;
use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\DebtRepositoryInterface;
use App\Interfaces\PersonRepositoryInterface;
use Illuminate\Http\JsonResponse;

class QuickAddController extends Controller
{
    public function __construct(
        private AccountRepositoryInterface $accountRepo,
        private CategoryRepositoryInterface $categoryRepo,
        private DebtRepositoryInterface $debtRepo,
        private PersonRepositoryInterface $personRepo
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'accounts' => $this->accountRepo->all_active(),
            'categories' => $this->categoryRepo->all_active(),
            'debts' => $this->debtRepo->get_active(),
            'persons' => $this->personRepo->all_active()
        ]);
    }
}
