<?php

namespace App\Http\Controllers\v1\modules;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Services\OfficeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\V1\Offices\IndexOfficeRequest;
use App\Http\Requests\V1\Offices\StoreOfficeRequest;
use App\Http\Requests\V1\Offices\UpdateOfficeRequest;
use App\Http\Requests\V1\Offices\ChangeOfficeStatusRequest;
use App\Http\Requests\V1\Offices\DeleteOfficeRequest;
use App\Http\Requests\V1\Offices\BulkDeleteOfficeRequest;
use App\Http\Requests\V1\Offices\BulkActivateOfficeRequest;
use App\Http\Requests\V1\Offices\BulkDeactivateOfficeRequest;

class OfficeController extends Controller
{
    protected OfficeService $officeService;

    public function __construct(OfficeService $officeService)
    {
        $this->officeService = $officeService;
    }

    /**
     * Display a listing of the offices.
     */
    public function index(IndexOfficeRequest $request)
    {
        try {
            $filters = $request->validated();
            $offices = $this->officeService->getPaginatedOffices($filters);
            return $this->handleResponse(true, 'Offices retrieved successfully.', $offices, [], 200);
        } catch (\Exception $e) {
            Log::error("Failed to retrieve offices: " . $e->getMessage());
            return $this->handleResponse(false, 'Failed to retrieve offices.', null, [$e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created office.
     */
    public function store(StoreOfficeRequest $request)
    {
        try {
            $office = $this->officeService->createOffice($request->validated());
            return $this->handleResponse(true, 'Office created successfully.', $office, [], 201);
        } catch (\Exception $e) {
            Log::error("Failed to create office: " . $e->getMessage());
            return $this->handleResponse(false, 'Failed to create office.', null, [$e->getMessage()], 500);
        }
    }

    /**
     * Display the specified office.
     */
    public function show(Office $office)
    {
        // No permission needed to view single office as it might be used globally, but can enforce if needed.
        return $this->handleResponse(true, 'Office retrieved successfully.', $office->load('users'), [], 200);
    }

    /**
     * Update the specified office.
     */
    public function update(UpdateOfficeRequest $request, Office $office)
    {
        try {
            $updatedOffice = $this->officeService->updateOffice($office, $request->validated());
            return $this->handleResponse(true, 'Office updated successfully.', $updatedOffice, [], 200);
        } catch (\Exception $e) {
            Log::error("Failed to update office: " . $e->getMessage());
            return $this->handleResponse(false, 'Failed to update office.', null, [$e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified office.
     */
    public function destroy(DeleteOfficeRequest $request, Office $office)
    {
        try {
            $this->officeService->deleteOffice($office);
            return $this->handleResponse(true, 'Office deleted successfully.', null, [], 200);
        } catch (\Exception $e) {
            Log::error("Failed to delete office: " . $e->getMessage());
            return $this->handleResponse(false, 'Failed to delete office.', null, [$e->getMessage()], 500);
        }
    }

    /**
     * Change status of a specific office.
     */
    public function changeOfficeStatus(ChangeOfficeStatusRequest $request, Office $office)
    {
        try {
            $updatedOffice = $this->officeService->changeOfficeStatus($office, $request->input('status'));
            return $this->handleResponse(true, "Office successfully marked as {$request->input('status')}.", $updatedOffice, [], 200);
        } catch (\Exception $e) {
             Log::error("Office status change failed: " . $e->getMessage());
             return $this->handleResponse(false, 'Failed to change office status.', null, [$e->getMessage()], 500);
        }
    }

     /**
     * Bulk activate multiple offices.
     */
    public function activateOffices(BulkActivateOfficeRequest $request)
    {
        try {
            $this->officeService->bulkActivateOffices($request->input('office_ids'));
            return $this->handleResponse(true, 'Offices successfully activated.', null, [], 200);
        } catch (\Exception $e) {
            Log::error("Bulk office activation failed: " . $e->getMessage());
            return $this->handleResponse(false, 'Failed to activate offices.', null, [$e->getMessage()], 500);
        }
    }

    /**
     * Bulk deactivate multiple offices.
     */
    public function deactivateOffices(BulkDeactivateOfficeRequest $request)
    {
        try {
            $this->officeService->bulkDeactivateOffices($request->input('office_ids'));
            return $this->handleResponse(true, 'Offices successfully deactivated.', null, [], 200);
        } catch (\Exception $e) {
            Log::error("Bulk office deactivation failed: " . $e->getMessage());
            return $this->handleResponse(false, 'Failed to deactivate offices.', null, [$e->getMessage()], 500);
        }
    }

    /**
     * Bulk delete multiple offices.
     */
    public function deleteOffices(BulkDeleteOfficeRequest $request)
    {
        try {
            $this->officeService->bulkDeleteOffices($request->input('office_ids'));
            return $this->handleResponse(true, 'Offices successfully deleted in bulk.', null, [], 200);
        } catch (\Exception $e) {
            Log::error("Bulk office deletion failed: " . $e->getMessage());
            return $this->handleResponse(false, 'Failed to delete offices in bulk.', null, [$e->getMessage()], 500);
        }
    }
}
