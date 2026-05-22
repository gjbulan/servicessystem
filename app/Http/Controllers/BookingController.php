<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesTenantCompany;
use App\Models\AssetType;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAsset;
use App\Models\JobOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    use ResolvesTenantCompany;

    public function index(Request $request): View
    {
        $company = $this->tenantCompany($request);
        $status = $request->query('status');

        return view('bookings.index', [
            'bookings' => Booking::query()
                ->with(['branch', 'services'])
                ->where('company_id', $company->id)
                ->when($status, fn ($query) => $query->where('status', $status))
                ->when(! $status, fn ($query) => $query->whereIn('status', ['pending', 'confirmed']))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'status' => $status,
            'statuses' => Booking::STATUSES,
        ]);
    }

    public function publicInfo(Request $request): View
    {
        $company = $this->tenantCompany($request);

        return view('bookings.public-info', [
            'company' => $company,
            'publicBookingUrl' => route('public-bookings.create', ['company' => $company->slug]),
        ]);
    }

    public function show(Request $request, string $booking): View
    {
        $company = $this->tenantCompany($request);
        $booking = $this->tenantBooking($company, $booking);
        $booking->load(['branch', 'customer', 'customerAsset', 'services', 'jobOrder']);

        return view('bookings.show', [
            'booking' => $booking,
            'statuses' => Booking::STATUSES,
        ]);
    }

    public function confirm(Request $request, string $booking): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $booking = $this->tenantBooking($company, $booking);

        DB::transaction(function () use ($booking, $company, $request): void {
            $booking = Booking::query()
                ->where('company_id', $company->id)
                ->whereKey($booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            $booking->load('services');
            $customer = $this->createOrUpdateCustomer($booking);
            $customerAsset = $this->createOrUpdateCustomerAsset($booking, $customer);
            $jobOrder = $this->createJobOrderFromBooking($booking, $customer, $customerAsset, $request->user()->id);

            $booking->update([
                'customer_id' => $customer->id,
                'customer_asset_id' => $customerAsset->id,
                'status' => 'confirmed',
            ]);

            if ($jobOrder->wasRecentlyCreated) {
                foreach ($booking->services as $service) {
                    $jobOrder->services()->create([
                        'service_id' => $service->service_id,
                        'service_name_snapshot' => $service->service_name_snapshot,
                        'price_snapshot' => $service->price_snapshot,
                    ]);
                }
            }
        });

        return redirect()
            ->route('bookings.show', $booking)
            ->with('status', 'Booking confirmed and job order created.');
    }

    public function cancel(Request $request, string $booking): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $booking = $this->tenantBooking($company, $booking);
        $booking->update(['status' => 'cancelled']);

        return redirect()
            ->route('bookings.show', $booking)
            ->with('status', 'Booking cancelled.');
    }

    public function noShow(Request $request, string $booking): RedirectResponse
    {
        $company = $this->tenantCompany($request);
        $booking = $this->tenantBooking($company, $booking);
        $booking->update(['status' => 'no_show']);

        return redirect()
            ->route('bookings.show', $booking)
            ->with('status', 'Booking marked as no-show.');
    }

    private function tenantBooking(Company $company, int|string $id): Booking
    {
        return Booking::query()
            ->where('company_id', $company->id)
            ->whereKey($id)
            ->firstOrFail();
    }

    private function createOrUpdateCustomer(Booking $booking): Customer
    {
        $customer = Customer::query()
            ->where('company_id', $booking->company_id)
            ->where(function ($query) use ($booking): void {
                $query->where('phone', $booking->phone);

                if ($booking->email) {
                    $query->orWhere('email', $booking->email);
                }
            })
            ->first() ?? new Customer(['company_id' => $booking->company_id]);

        $customer->fill([
            'name' => $booking->customer_name,
            'phone' => $booking->phone,
            'email' => $booking->email ?? $customer->email,
            'status' => 'active',
        ]);

        $customer->save();

        return $customer;
    }

    private function createOrUpdateCustomerAsset(Booking $booking, Customer $customer): CustomerAsset
    {
        $details = $booking->asset_details_json ?? [];
        $assetType = $booking->asset_type_name
            ? AssetType::query()
                ->where('company_id', $booking->company_id)
                ->where('name', $booking->asset_type_name)
                ->first()
            : null;

        $asset = $this->matchingAsset($booking, $customer, $details)
            ?? new CustomerAsset([
                'company_id' => $booking->company_id,
                'customer_id' => $customer->id,
            ]);

        $asset->fill([
            'company_id' => $booking->company_id,
            'customer_id' => $customer->id,
            'asset_type_id' => $assetType?->id,
            'name' => $details['name'] ?? $booking->asset_type_name,
            'brand' => $details['brand'] ?? null,
            'model' => $details['model'] ?? null,
            'year' => $details['year'] ?? null,
            'serial_number' => $details['serial_number'] ?? null,
            'plate_number' => $details['plate_number'] ?? null,
            'color' => $details['color'] ?? null,
            'notes' => $details['notes'] ?? null,
            'status' => 'active',
        ]);

        $asset->save();

        return $asset;
    }

    private function matchingAsset(Booking $booking, Customer $customer, array $details): ?CustomerAsset
    {
        if ($booking->customer_asset_id) {
            return CustomerAsset::query()
                ->where('company_id', $booking->company_id)
                ->where('customer_id', $customer->id)
                ->whereKey($booking->customer_asset_id)
                ->first();
        }

        foreach (['serial_number', 'plate_number'] as $field) {
            if (! empty($details[$field])) {
                return CustomerAsset::query()
                    ->where('company_id', $booking->company_id)
                    ->where('customer_id', $customer->id)
                    ->where($field, $details[$field])
                    ->first();
            }
        }

        return null;
    }

    private function createJobOrderFromBooking(Booking $booking, Customer $customer, CustomerAsset $customerAsset, int $userId): JobOrder
    {
        return JobOrder::query()->firstOrCreate([
            'company_id' => $booking->company_id,
            'booking_id' => $booking->id,
        ], [
            'branch_id' => $booking->branch_id,
            'customer_id' => $customer->id,
            'customer_asset_id' => $customerAsset->id,
            'job_order_number' => $this->nextJobOrderNumber($booking->company_id),
            'status' => 'open',
            'customer_complaint' => $booking->issue_description,
            'internal_notes' => $booking->internal_notes,
            'created_by' => $userId,
        ]);
    }

    private function nextJobOrderNumber(int $companyId): string
    {
        $prefix = 'JO'.now()->format('Ymd').'-';
        $next = JobOrder::withTrashed()
            ->where('company_id', $companyId)
            ->where('job_order_number', 'like', $prefix.'%')
            ->count() + 1;

        do {
            $number = $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $next++;
        } while (JobOrder::withTrashed()->where('company_id', $companyId)->where('job_order_number', $number)->exists());

        return $number;
    }
}
