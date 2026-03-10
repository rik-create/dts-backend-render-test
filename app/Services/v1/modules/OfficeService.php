<?php

namespace App\Services\v1\modules;

use App\Models\Office;
use Illuminate\Pagination\LengthAwarePaginator;

class OfficeService
{
    /**
     * [Read] get a paginated list of all offices, with optional filtering
     */
    public function getPaginatedOffices(array $filters): LengthAwarePaginator
    {
        $query = Office::query();

        // Search logic
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('code', 'like', $searchTerm);
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'id';
        $sortDir = $filters['sort_dir'] ?? 'desc';

        $allowedSortColumns = ['id', 'name', 'code', 'is_active', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortDir);
        }

        $perPage = $filters['per_page'] ?? 10;
        return $query->paginate($perPage);
    }

    /**
     * [Create] generate a new office record
     */
    public function createOffice(array $data): Office
    {
        return Office::create($data);
    }

    /**
     * [Update] modify an existing office record
     */
    public function updateOffice(Office $office, array $data): Office
    {
        $office->update($data);
        return $office->fresh();
    }

    /**
     * [Status Toggle] update the is_active flag for a single office
     */
    public function changeOfficeStatus(Office $office, string $status): Office
    {
        $office->update([
            'is_active' => ($status === 'active')
        ]);
        return $office->fresh();
    }

    /**
     * [Delete] soft delete a single office record
     */
    public function deleteOffice(Office $office): void
    {
        $office->delete();
    }

    /**
     * [Bulk Activate] mark multiple offices as active
     */
    public function bulkActivateOffices(array $officeIds): void
    {
        Office::whereIn('id', $officeIds)->update(['is_active' => true]);
    }

    /**
     * [Bulk Deactivate] mark multiple offices as inactive
     */
    public function bulkDeactivateOffices(array $officeIds): void
    {
        Office::whereIn('id', $officeIds)->update(['is_active' => false]);
    }

    /**
     * [Bulk Delete] soft delete multiple office records
     */
    public function bulkDeleteOffices(array $officeIds): void
    {
        Office::whereIn('id', $officeIds)->delete();
    }
}
