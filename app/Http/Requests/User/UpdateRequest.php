<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'     => 'sometimes|nullable',
            'email'    => 'sometimes|nullable|email',
            'password' => 'sometimes|nullable',
            'type'     => 'sometimes|nullable|in:USER,VIRTUAL,MANAGER' //TODO: poderia ser um enum, pois esses tipos estão espalhados pelo sistema
        ];
    }
}
