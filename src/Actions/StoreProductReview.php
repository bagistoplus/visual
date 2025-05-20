<?php

namespace BagistoPlus\Visual\Actions;

use Illuminate\Support\Facades\Validator;
use Webkul\Product\Repositories\ProductReviewAttachmentRepository;
use Webkul\Product\Repositories\ProductReviewRepository;
use Webkul\Shop\Http\Controllers\API\ReviewController;

class StoreProductReview
{
    public function __construct(protected ProductReviewRepository $productReviewRepository, protected ProductReviewAttachmentRepository $productReviewAttachmentRepository) {}

    /**
     * Store product review
     */
    public function execute(int $productId, array $data = [])
    {
        $validator = Validator::make($data, [
            'title' => 'required',
            'comment' => 'required',
            'rating' => 'required|numeric|min:1|max:5',
            'attachments' => 'array',
        ]);

        $validated = $validator->validate();

        $validated['product_id'] = $productId;
        $validated['status'] = ReviewController::STATUS_PENDING;
        $validated['name'] = auth('customer')->user()?->name ?? $data['name'];
        $validated['customer_id'] = auth('customer')->id() ?? null;

        $review = $this->productReviewRepository->create($validated);

        $this->productReviewAttachmentRepository->upload($validated['attachments'], $review);

        return $review;
    }
}
