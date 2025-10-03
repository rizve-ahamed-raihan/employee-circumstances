<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeCocResource;
use App\Models\Employee;
use App\Models\EmployeeCoc;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\OrganizationNotificationService;

class EmployeeCocController extends Controller
{
    protected OrganizationNotificationService $notifier;

    public function __construct(OrganizationNotificationService $notifier)
    {
        $this->notifier = $notifier;
    }

    // List all CoCs for an employee
    public function index(Request $request, Employee $employee)
    {
        $cocs = $employee->cocs()
            ->orderBy('created_at', 'desc')
            ->paginate()
            ->appends($request->all());

        return EmployeeCocResource::collection($cocs);
    }

    // Store a new CoC
    public function store(Request $request, Employee $employee)
    {
        $validator = Validator::make($request->all(), [
            'type'           => 'required|string|max:255',
            'old_value'      => 'nullable|string',
            'new_value'      => 'required|string',
            'reason'         => 'nullable|string|max:1000',
            'effective_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response_unprocessable($validator->errors());
        }

        $coc = $employee->cocs()->create($validator->validated());

        // 🔔 Notify organization / HR about new CoC request
        $this->notifier->sendToOrganization(
            $employee->organization,
            "New CoC Request",
            "{$employee->first_name} {$employee->last_name} has submitted a change of circumstances: {$coc->type}"
        );

        return response_created(new EmployeeCocResource($coc));
    }

    // Show a single CoC
    public function show(Employee $employee, EmployeeCoc $coc)
    {
        if ($coc->employee_id !== $employee->id) {
            return response_forbidden();
        }

        return new EmployeeCocResource($coc);
    }

    // Update a CoC (status, effective_date)
    public function update(Request $request, Employee $employee, EmployeeCoc $coc)
    {
        if ($coc->employee_id !== $employee->id) {
            return response_forbidden();
        }

        $validator = Validator::make($request->all(), [
            'status'         => 'in:pending,approved,rejected',
            'effective_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response_unprocessable($validator->errors());
        }

        $coc->update(array_merge(
            $validator->validated(),
            ['approved_by' => $request->user()->id]
        ));

        // 🔔 Notify employee about approval/rejection
        $statusMsg = match ($coc->status) {
            'approved' => "Your CoC request for {$coc->type} has been approved.",
            'rejected' => "Your CoC request for {$coc->type} has been rejected.",
            default    => "Your CoC request for {$coc->type} is still pending.",
        };

        $this->notifier->sendToMany(
            collect([$employee->id])
                ->map(fn($id) => User::find($id))
                ->filter(),
            "CoC Update",
            $statusMsg
        );

        // Apply the change if approved and effective date passed
        if ($coc->status === 'approved' && $coc->effective_date <= now()) {
            $this->applyChange($coc);
        }

        return response_ok(new EmployeeCocResource($coc));
    }

    // Delete a CoC
    public function destroy(Employee $employee, EmployeeCoc $coc)
    {
        if ($coc->employee_id !== $employee->id) {
            return response_forbidden();
        }

        $coc->delete();

        return response_no_content();
    }

    // Apply CoC changes to employee
    protected function applyChange(EmployeeCoc $coc): void
    {
        $employee = $coc->employee;

        switch ($coc->type) {
            case 'salary_change':
                $employee->update(['salary' => $coc->new_value]);
                break;
            case 'designation_change':
                $employee->update(['designation' => $coc->new_value]);
                break;
            case 'status_change':
                $employee->update(['status' => $coc->new_value]);
                break;
            // Add more types if needed
        }
    }
}
