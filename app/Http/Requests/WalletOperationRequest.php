<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WalletOperationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $operation = $this->route()->getActionMethod();

        return match ($operation) {
            'deposit' => $this->getDepositRules(),
            'withdraw' => $this->getWithdrawRules(),
            'transfer' => $this->getTransferRules(),
            'getTransactions' => $this->getTransactionsRules(),
            'createWallet' => $this->getCreateWalletRules(),
            default => [],
        };
    }

    /**
     * Get validation rules for deposit operation
     */
    private function getDepositRules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
            ],
            'description' => [
                'nullable',
                'string',
                'max:255',
            ],
            'reference' => [
                'nullable',
                'string',
                'max:100',
            ],
            'metadata' => [
                'nullable',
                'array',
            ],
            'metadata.*' => [
                'string',
            ],
        ];
    }

    /**
     * Get validation rules for withdraw operation
     */
    private function getWithdrawRules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
            ],
            'description' => [
                'nullable',
                'string',
                'max:255',
            ],
            'reference' => [
                'nullable',
                'string',
                'max:100',
            ],
            'metadata' => [
                'nullable',
                'array',
            ],
            'metadata.*' => [
                'string',
            ],
        ];
    }

    /**
     * Get validation rules for transfer operation
     */
    private function getTransferRules(): array
    {
        return [
            'to_profile_type' => [
                'required',
                Rule::in(['investor', 'owner']),
            ],
            'to_profile_id' => [
                'required',
                'integer',
                'min:1',
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
            ],
            'description' => [
                'nullable',
                'string',
                'max:255',
            ],
            'reference' => [
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }

    /**
     * Get validation rules for get transactions operation
     */
    private function getTransactionsRules(): array
    {
        return [
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'type' => [
                'nullable',
                Rule::in(['deposit', 'withdraw']),
            ],
            'date_from' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],
            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
        ];
    }

    /**
     * Get validation rules for create wallet operation
     */
    private function getCreateWalletRules(): array
    {
        return [
            'name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
            'meta' => [
                'nullable',
                'array',
            ],
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'The amount field is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be at least :min.',
            'amount.max' => 'The amount may not be greater than :max.',
            'to_profile_type.required' => 'The target profile type is required.',
            'to_profile_type.in' => 'The target profile type must be either investor or owner.',
            'to_profile_id.required' => 'The target profile ID is required.',
            'to_profile_id.integer' => 'The target profile ID must be a valid integer.',
            'description.max' => 'The description may not be greater than :max characters.',
            'reference.max' => 'The reference may not be greater than :max characters.',
            'per_page.integer' => 'The per page must be a valid integer.',
            'per_page.min' => 'The per page must be at least :min.',
            'per_page.max' => 'The per page may not be greater than :max.',
            'type.in' => 'The type must be either deposit or withdraw.',
            'date_from.date' => 'The date from must be a valid date.',
            'date_from.before_or_equal' => 'The date from must be before or equal to today.',
            'date_to.date' => 'The date to must be a valid date.',
            'date_to.after_or_equal' => 'The date to must be after or equal to date from.',
        ];
    }

    /**
     * Get custom attribute names for validation errors
     */
    public function attributes(): array
    {
        return [
            'amount' => 'amount',
            'description' => 'description',
            'reference' => 'reference',
            'to_profile_type' => 'target profile type',
            'to_profile_id' => 'target profile ID',
            'per_page' => 'per page',
            'type' => 'transaction type',
            'date_from' => 'start date',
            'date_to' => 'end date',
        ];
    }
}

