<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'document_number' => 'required|regex:/[0-9]{11}/i',
            'name'            => 'required',
            'email'           => 'required|email',
            'password'        => 'required',
            'type'            => 'required|in:USER,VIRTUAL,MANAGER' //TODO: poderia ser um enum, pois esses tipos estão espalhados pelo sistema
        ];
    }
}
