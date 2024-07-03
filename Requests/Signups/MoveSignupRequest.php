<?php

namespace Netflex\Actions\Requests\Signups;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property ?int|string $to
 */
class MoveSignupRequest extends FormRequest
{
  public function rules()
  {
    return [
      'to' => ['required', 'numeric']
    ];
  }

}
