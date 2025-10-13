<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TnaRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
          return [ 
            'employeecode' => 'required',
            'jobcode' => 'required',
            'tas_data_from' => 'required',
            'source'=>'required'
          ];
    }

    public function messages(): array
    {
        return [
            'employeecode.required' => 'Employee Code is required.', 
            'jobcode.required'      => 'Job Code is required.',
            'tas_data_from.required'=> 'Time In is required.',
            'source.required'=> 'Source is  required.',
        ];
    }

}
