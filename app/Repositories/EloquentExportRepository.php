<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\ExportRepositoryInterface;
use App\Models\Export;
use Illuminate\Contracts\Pagination\CursorPaginator;

class EloquentExportRepository implements ExportRepositoryInterface
{
    public function paginate_for_user(int $user_id, int $per_page = 20): CursorPaginator
    {
        return Export::where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->cursorPaginate($per_page);
    }

    public function delete(Export $export): void
    {
        $export->delete();
    }
}
