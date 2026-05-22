<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait ResolvesTenantCompany
{
    protected function tenantCompany(Request $request): Company
    {
        $company = $request->user()?->company;

        abort_unless($company, 403, 'A company assignment is required for this module.');

        return $company;
    }

    /**
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $modelClass
     * @return TModel
     */
    protected function tenantRecord(Company $company, string $modelClass, int|string $id): Model
    {
        return $modelClass::query()
            ->where('company_id', $company->id)
            ->whereKey($id)
            ->firstOrFail();
    }
}
