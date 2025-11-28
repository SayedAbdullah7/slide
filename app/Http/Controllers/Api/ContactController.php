<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    use ApiResponseTrait;

    /**
     * Store a new contact message
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ], [
            'subject.required' => 'الموضوع مطلوب',
            'subject.max' => 'الموضوع يجب أن يكون أقل من 255 حرف',
            'message.required' => 'الرسالة مطلوبة',
            'message.max' => 'الرسالة يجب أن تكون أقل من 2000 حرف',
        ]);

        if ($validator->fails()) {
            return $this->respondValidationError($validator->errors());
        }

        try {
            // Determine profile type
            $profileType = null;
            if (auth()->check()) {
                $user = auth()->user();
                $profileType = $user->active_profile_type ?? ContactMessage::PROFILE_TYPE_GUEST;
            } else {
                $profileType = ContactMessage::PROFILE_TYPE_GUEST;
            }

            // Create the contact message
            $contactMessage = ContactMessage::create([
                'user_id' => auth()->id(), // Will be null if not authenticated
                'profile_type' => $profileType,
                'subject' => $request->subject,
                'message' => $request->message,
                'status' => ContactMessage::STATUS_PENDING,
            ]);

            // Prepare response data
            $data = [
                'id' => $contactMessage->id,
                'subject' => $contactMessage->subject,
                'message' => $contactMessage->message,
                'profile_type' => $contactMessage->profile_type,
                'profile_type_label' => $contactMessage->profile_type_label,
                'status' => $contactMessage->status,
                'status_label' => $contactMessage->status_label,
                'created_at' => $contactMessage->created_at->format('Y-m-d H:i:s'),
            ];

            return $this->respondSuccessWithData('تم إرسال رسالتك بنجاح. سنتواصل معك قريباً.', $data);

        } catch (\Exception $e) {
            return $this->respondError('حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.', 500);
        }
    }

    /**
     * Get contact messages for authenticated user
     */
    public function index(Request $request)
    {
        if (!auth()->check()) {
            return $this->respondError('يجب تسجيل الدخول لعرض الرسائل', 401);
        }

        $messages = ContactMessage::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'subject' => $message->subject,
                    'message' => $message->message,
                    'profile_type' => $message->profile_type,
                    'profile_type_label' => $message->profile_type_label,
                    'status' => $message->status,
                    'status_label' => $message->status_label,
                    'admin_notes' => $message->admin_notes,
                    'responded_at' => $message->responded_at?->format('Y-m-d H:i:s'),
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return $this->respondSuccessWithData('تم جلب الرسائل بنجاح', [
            'messages' => $messages,
            'total' => $messages->count()
        ]);
    }

    /**
     * Get specific contact message details
     */
    public function show($id)
    {
        if (!auth()->check()) {
            return $this->respondError('يجب تسجيل الدخول لعرض الرسالة', 401);
        }

        $message = ContactMessage::where('user_id', auth()->id())
            ->find($id);

        if (!$message) {
            return $this->respondError('الرسالة غير موجودة', 404);
        }

        $data = [
            'id' => $message->id,
            'subject' => $message->subject,
            'message' => $message->message,
            'profile_type' => $message->profile_type,
            'profile_type_label' => $message->profile_type_label,
            'status' => $message->status,
            'status_label' => $message->status_label,
            'admin_notes' => $message->admin_notes,
            'responded_at' => $message->responded_at?->format('Y-m-d H:i:s'),
            'created_at' => $message->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $message->updated_at->format('Y-m-d H:i:s'),
        ];

        return $this->respondSuccessWithData('تم جلب تفاصيل الرسالة بنجاح', $data);
    }
}
