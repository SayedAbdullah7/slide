<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvestmentOpportunityRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // make all rules required
        return [
            'company_age' => 'required|string|max:255',
            'commercial_experience' => 'required|string|max:255',
            'net_profit_margins' => 'required|string|max:255',
            'required_amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:5000',
            'guarantee_type' => 'required|string|in:' . implode(',', \App\GuaranteeTypeEnum::values()),
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'company_age.max' => 'عمر الشركة لا يجب أن يتجاوز 255 حرف',
            'commercial_experience.max' => 'الخبرة التجارية لا يجب أن تتجاوز 255 حرف',
            'net_profit_margins.max' => 'هوامش الأرباح لا يجب أن تتجاوز 255 حرف',
            'required_amount.numeric' => 'المبلغ المطلوب يجب أن يكون رقماً',
            'required_amount.min' => 'المبلغ المطلوب لا يمكن أن يكون أقل من 0',
            'description.max' => 'الوصف لا يجب أن يتجاوز 5000 حرف',
            'guarantee_type.in' => 'نوع الرهن المحدد غير صحيح',
        ];
    }
}
