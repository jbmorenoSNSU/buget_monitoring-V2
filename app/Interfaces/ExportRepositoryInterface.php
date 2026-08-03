<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Export;
use Illuminate\Contracts\Pagination\CursorPaginator;

interface ExportRepositoryInterface
{
    /**
     * Get paginated exports for a specific user.
     */
    public function paginate_for_user(int $user_id, int $per_page = 20): CursorPaginator;

    /**
     * Delete an export record.
     */
    public function delete(Export $export): void;
}
