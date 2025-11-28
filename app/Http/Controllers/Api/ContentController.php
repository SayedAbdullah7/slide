<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\{FAQ, Content};
use Illuminate\Http\Request;

class ContentController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get Privacy Policy content
     */
    public function privacyPolicy()
    {
        $content = Content::active()
            ->byType(Content::TYPE_PRIVACY_POLICY)
            ->first();

        if (!$content) {
            return $this->respondError('Privacy policy not found', 404);
        }

        $data = [
            'title' => $content->title,
            'content' => $content->content,
            'last_updated' => $content->last_updated?->format('Y-m-d') ?? now()->format('Y-m-d')
        ];

        return $this->respondSuccessWithData('Privacy policy retrieved successfully', $data);
    }

    /**
     * Get Terms and Conditions content
     */
    public function termsAndConditions()
    {
        $content = Content::active()
            ->byType(Content::TYPE_TERMS_CONDITIONS)
            ->first();

        if (!$content) {
            return $this->respondError('Terms and conditions not found', 404);
        }

        $data = [
            'title' => $content->title,
            'content' => $content->content,
            'last_updated' => $content->last_updated?->format('Y-m-d') ?? now()->format('Y-m-d')
        ];

        return $this->respondSuccessWithData('Terms and conditions retrieved successfully', $data);
    }

    /**
     * Get About the App content
     */
    public function aboutApp()
    {
        $content = Content::active()
            ->byType(Content::TYPE_ABOUT_APP)
            ->first();

        if (!$content) {
            return $this->respondError('About app content not found', 404);
        }

        $data = [
            'title' => $content->title,
            'content' => $content->content,
            'last_updated' => $content->last_updated?->format('Y-m-d') ?? now()->format('Y-m-d')
        ];

        return $this->respondSuccessWithData('About app content retrieved successfully', $data);
    }

    /**
     * Get FAQ list
     */
    public function faq(Request $request)
    {
        $faqs = FAQ::active()
            ->ordered()
            ->get()
            ->map(function ($faq) {
                return [
                    'id' => $faq->id,
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                    'order' => $faq->order,
                ];
            });

        return $this->respondSuccessWithData('FAQ list retrieved successfully', [
            'faqs' => $faqs,
            'total' => $faqs->count()
        ]);
    }

    /**
     * Get specific FAQ by ID
     */
    public function faqDetails($id)
    {
        $faq = FAQ::active()->find($id);

        if (!$faq) {
            return $this->respondError('FAQ not found', 404);
        }

        $faqData = [
            'id' => $faq->id,
            'question' => $faq->question,
            'answer' => $faq->answer,
            'order' => $faq->order,
        ];

        return $this->respondSuccessWithData('FAQ details retrieved successfully', $faqData);
    }

    /**
     * Get all static content in one response
     */
    public function allContent()
    {
        // Get all content from database
        $contents = Content::active()->get()->keyBy('type');

        // Get FAQs
        $faqs = FAQ::active()
            ->ordered()
            ->get()
            ->map(function ($faq) {
                return [
                    'id' => $faq->id,
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                    'order' => $faq->order,
                ];
            });

        $content = [
            'privacy_policy' => $contents->get(Content::TYPE_PRIVACY_POLICY) ? [
                'title' => $contents->get(Content::TYPE_PRIVACY_POLICY)->title,
                'content' => $contents->get(Content::TYPE_PRIVACY_POLICY)->content,
                'last_updated' => $contents->get(Content::TYPE_PRIVACY_POLICY)->last_updated?->format('Y-m-d') ?? now()->format('Y-m-d')
            ] : null,
            'terms_and_conditions' => $contents->get(Content::TYPE_TERMS_CONDITIONS) ? [
                'title' => $contents->get(Content::TYPE_TERMS_CONDITIONS)->title,
                'content' => $contents->get(Content::TYPE_TERMS_CONDITIONS)->content,
                'last_updated' => $contents->get(Content::TYPE_TERMS_CONDITIONS)->last_updated?->format('Y-m-d') ?? now()->format('Y-m-d')
            ] : null,
            'about_app' => $contents->get(Content::TYPE_ABOUT_APP) ? [
                'title' => $contents->get(Content::TYPE_ABOUT_APP)->title,
                'content' => $contents->get(Content::TYPE_ABOUT_APP)->content,
                'last_updated' => $contents->get(Content::TYPE_ABOUT_APP)->last_updated?->format('Y-m-d') ?? now()->format('Y-m-d')
            ] : null,
            'faqs' => $faqs,
            'faq_count' => $faqs->count()
        ];

        return $this->respondSuccessWithData('All content retrieved successfully', $content);
    }
}
