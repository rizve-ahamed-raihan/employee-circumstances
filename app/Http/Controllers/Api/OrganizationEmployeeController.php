<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use App\Models\Organization;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrganizationEmployeeController extends Controller
{
    public function index(Request $request, Organization $organization)
    {
        $employees = $organization->employees()
            ->when($request->filled('term'), fn($q) =>
                $q->where(fn($sub) =>
                    $sub->where('first_name', 'like', "%{$request->term}%")
                        ->orWhere('last_name', 'like', "%{$request->term}%")
                        ->orWhere('email', 'like', "%{$request->term}%")
                )
            )
            ->paginate()
            ->appends($request->all());

        return EmployeeResource::collection($employees);
    }

    public function store(Request $request, Organization $organization)
    {
        $validator = Validator::make($request->all(), [
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'email'       => 'required|email|unique:employees,email',
            'phone'       => 'nullable|string|max:50',
            'designation' => 'nullable|string|max:255',
            'salary'      => 'nullable|numeric',
            'joining_date'=> 'nullable|date',
            'status'      => 'in:active,inactive,terminated',
        ]);

        if ($validator->fails()) {
            return response_unprocessable($validator->errors());
        }

        $employee = $organization->employees()->create($validator->validated());

        return response_created(new EmployeeResource($employee));
    }

    public function show(Organization $organization, Employee $employee)
    {
        if ($employee->organization_id !== $organization->id) {
            return response_forbidden();
        }
        return new EmployeeResource($employee);
    }

    public function update(Request $request, Organization $organization, Employee $employee)
    {
        if ($employee->organization_id !== $organization->id) {
            return response_forbidden();
        }

        $validator = Validator::make($request->all(), [
            'first_name'  => 'sometimes|string|max:255',
            'last_name'   => 'sometimes|string|max:255',
            'email'       => "sometimes|email|unique:employees,email,{$employee->id},id",
            'phone'       => 'nullable|string|max:50',
            'designation' => 'nullable|string|max:255',
            'salary'      => 'nullable|numeric',
            'joining_date'=> 'nullable|date',
            'status'      => 'in:active,inactive,terminated',
        ]);

        if ($validator->fails()) {
            return response_unprocessable($validator->errors());
        }

        $employee->update($validator->validated());

        return response_ok(new EmployeeResource($employee));
    }

    public function destroy(Organization $organization, Employee $employee)
    {
        if ($employee->organization_id !== $organization->id) {
            return response_forbidden();
        }

        $employee->delete();

        return response_no_content();
    }
}
