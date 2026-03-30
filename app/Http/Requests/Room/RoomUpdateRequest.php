<?php

namespace App\Http\Requests\Room;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoomUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $roomId = $this->route('room')?->id ?? $this->route('room');

        return [
            'number' => [
                'required',
                'string',
                'regex:/^\d{4,}$/',
                Rule::unique('rooms', 'number')->ignore($roomId),
            ],
            'capacity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'integer', 'min:1'],
            'floor_id' => ['required', 'integer', 'exists:floors,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'number.regex' => 'The room number must be at least 4 digits.',
        ];
    }
}
