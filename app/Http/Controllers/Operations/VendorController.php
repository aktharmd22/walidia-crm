<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\ResourceController;
use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use App\Models\VendorCategory;
use App\Models\VendorRating;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * @extends ResourceController<Vendor>
 */
class VendorController extends ResourceController
{
    protected string $model = Vendor::class;

    protected string $name = 'vendors';

    protected string $pages = 'Vendors';

    protected string $resource = VendorResource::class;

    protected ?string $routePrefix = 'vendors';

    protected array $indexWith = ['category:id,name'];

    protected array $showWith = ['category', 'ratings.booking', 'purchaseOrders'];

    protected array $sortable = ['legal_name', 'status', 'rating_avg', 'created_at'];

    protected string $defaultSort = 'legal_name';

    protected array $filterable = ['status', 'vendor_category_id', 'is_approved'];

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Vendor::class);

        $vendor = Vendor::create($this->validated($request));

        return redirect()->route('vendors.show', $vendor)
            ->with('success', "{$vendor->displayName()} added. Approve it before raising a purchase order.");
    }

    public function update(Request $request, Vendor $vendor): RedirectResponse
    {
        $this->authorize('update', $vendor);

        $vendor->update($this->validated($request));

        return back()->with('success', 'Vendor updated.');
    }

    /** Approval is what makes a vendor usable on a charter. */
    public function approve(Request $request, Vendor $vendor): RedirectResponse
    {
        $this->authorize('approve', $vendor);

        $vendor->forceFill([
            'is_approved' => true,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ])->save();

        return back()->with('success', "{$vendor->displayName()} approved.");
    }

    public function rate(Request $request, Vendor $vendor): RedirectResponse
    {
        $this->authorize('update', $vendor);

        $data = $request->validate([
            'booking_id' => ['nullable', 'integer', 'exists:bookings,id'],
            'score' => ['required', 'integer', 'min:1', 'max:5'],
            'punctuality' => ['nullable', 'integer', 'min:1', 'max:5'],
            'quality' => ['nullable', 'integer', 'min:1', 'max:5'],
            'value' => ['nullable', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $vendor->ratings()->create($data + ['rated_by' => $request->user()->id]);

        // The average is what the ops team sorts by when choosing a supplier.
        $vendor->forceFill(['rating_avg' => round((float) $vendor->ratings()->avg('score'), 2)])->save();

        return back()->with('success', 'Rating recorded.');
    }

    public function categories(Request $request): Response
    {
        $this->authorize('viewAny', Vendor::class);

        return Inertia::render('Vendors/Categories', [
            'categories' => VendorCategory::withCount('vendors')->orderBy('name')->get()
                ->map(fn (VendorCategory $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'requires_insurance' => $category->requires_insurance,
                    'requires_licence' => $category->requires_licence,
                    'vendors_count' => $category->vendors_count,
                ]),
        ]);
    }

    protected function formProps(Request $request, ?Model $record = null): array
    {
        return [
            'categories' => VendorCategory::orderBy('name')->get(['id', 'name'])
                ->map(fn (VendorCategory $category): array => ['value' => $category->id, 'label' => $category->name]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'legal_name' => ['required', 'string', 'max:190'],
            'trade_name' => ['nullable', 'string', 'max:190'],
            'vendor_category_id' => ['nullable', 'integer', 'exists:vendor_categories,id'],
            'trn' => ['nullable', 'string', 'max:32'],
            'trade_licence_no' => ['nullable', 'string', 'max:64'],
            'licence_expiry' => ['nullable', 'date'],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email:rfc', 'max:190'],
            'mobile' => ['nullable', 'string', 'max:32'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['active', 'inactive', 'blacklisted'])],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function showProps(Request $request, Model $record): array
    {
        /** @var Vendor $record */
        return [
            'ratings' => $record->ratings()->with('booking:id,reference')->latest()->get()
                ->map(fn (VendorRating $rating): array => [
                    'id' => $rating->id,
                    'score' => $rating->score,
                    'comment' => $rating->comment,
                    'booking' => $rating->booking?->reference,
                    'created_at' => $rating->created_at?->toIso8601String(),
                ]),
            'can' => $this->recordAbilities($request, $record) + [
                'approve' => $request->user()->can('approve', $record),
            ],
        ];
    }
}
