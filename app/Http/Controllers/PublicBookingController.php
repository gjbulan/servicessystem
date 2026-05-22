<?php

namespace App\Http\Controllers;

use App\Models\AssetType;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PublicBookingController extends Controller
{
    public function create(Company $company): View
    {
        $this->ensurePublicBookingAvailable($company);

        return view('public-bookings.create', [
            'assetTypes' => $this->assetTypes($company),
            'branches' => $this->branches($company),
            'company' => $company,
            'services' => $this->services($company),
        ]);
    }

    public function store(Request $request, Company $company): RedirectResponse
    {
        $this->ensurePublicBookingAvailable($company);

        $data = $request->validate([
            'branch_id' => ['required', Rule::exists('branches', 'id')->where('company_id', $company->id)->where('status', 'active')],
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['required', Rule::exists('services', 'id')->where('company_id', $company->id)->where('status', 'active')],
            'customer_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'asset_type_id' => ['nullable', Rule::exists('asset_types', 'id')->where('company_id', $company->id)->where('status', 'active')],
            'asset_name' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'string', 'max:50'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'plate_number' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:255'],
            'preferred_datetime' => ['nullable', 'date'],
            'issue_description' => ['nullable', 'string', 'max:3000'],
            'lead_source' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking = DB::transaction(function () use ($company, $data): Booking {
            $assetType = empty($data['asset_type_id'])
                ? null
                : AssetType::query()
                    ->where('company_id', $company->id)
                    ->whereKey($data['asset_type_id'])
                    ->first();

            $booking = Booking::create([
                'company_id' => $company->id,
                'branch_id' => $data['branch_id'],
                'booking_reference' => $this->nextBookingReference($company->id),
                'customer_name' => $data['customer_name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'asset_type_name' => $assetType?->name,
                'asset_details_json' => $this->assetDetails($data),
                'preferred_datetime' => $data['preferred_datetime'] ?? null,
                'issue_description' => $data['issue_description'] ?? null,
                'lead_source' => $data['lead_source'] ?? null,
                'internal_notes' => $data['notes'] ?? null,
                'status' => 'pending',
            ]);

            $services = Service::query()
                ->where('company_id', $company->id)
                ->whereIn('id', collect($data['services'])->map(fn ($id) => (int) $id)->unique())
                ->get();

            foreach ($services as $service) {
                $booking->services()->create([
                    'service_id' => $service->id,
                    'service_name_snapshot' => $service->name,
                    'price_snapshot' => $service->default_price,
                ]);
            }

            return $booking;
        });

        return redirect()
            ->route('public-bookings.create', ['company' => $company->slug])
            ->with('status', "Booking request {$booking->booking_reference} submitted.");
    }

    private function ensurePublicBookingAvailable(Company $company): void
    {
        abort_unless(in_array($company->status, ['active', 'trial'], true), 404);
        abort_unless($company->hasModule('bookings'), 404);
    }

    /**
     * @return array<string, string|null>
     */
    private function assetDetails(array $data): array
    {
        return [
            'name' => $data['asset_name'] ?? null,
            'brand' => $data['brand'] ?? null,
            'model' => $data['model'] ?? null,
            'year' => $data['year'] ?? null,
            'serial_number' => $data['serial_number'] ?? null,
            'plate_number' => $data['plate_number'] ?? null,
            'color' => $data['color'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];
    }

    private function nextBookingReference(int $companyId): string
    {
        $prefix = 'B'.now()->format('Ymd').'-';
        $next = Booking::withTrashed()
            ->where('company_id', $companyId)
            ->where('booking_reference', 'like', $prefix.'%')
            ->count() + 1;

        do {
            $reference = $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $next++;
        } while (Booking::withTrashed()->where('company_id', $companyId)->where('booking_reference', $reference)->exists());

        return $reference;
    }

    private function branches(Company $company)
    {
        return Branch::query()
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    private function services(Company $company)
    {
        return Service::query()
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    private function assetTypes(Company $company)
    {
        return AssetType::query()
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }
}
