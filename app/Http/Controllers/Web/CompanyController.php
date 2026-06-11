<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function __construct(private readonly CompanyService $companies) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Company::class);

        $companies = $this->companies->paginate($request->only(['search', 'is_active']));

        return view('companies.index', compact('companies'));
    }

    public function create(): View
    {
        $this->authorize('create', Company::class);

        return view('companies.create');
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        $this->authorize('create', Company::class);

        $this->companies->create($request->validated());

        return redirect()->route('companies.index')->with('success', 'Company created.');
    }

    public function edit(Company $company): View
    {
        $this->authorize('update', $company);

        return view('companies.edit', compact('company'));
    }

    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $this->authorize('update', $company);

        $this->companies->update($company, $request->validated());

        return redirect()->route('companies.index')->with('success', 'Company updated.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $this->authorize('delete', $company);

        $this->companies->delete($company);

        return redirect()->route('companies.index')->with('success', 'Company deleted.');
    }
}
