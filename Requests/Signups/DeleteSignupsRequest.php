<?php

namespace Netflex\Actions\Requests\Signups;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property ?int|string $to
 */
class DeleteSignupsRequest extends FormRequest
{
  public function rules()
  {
    return [
      'ids.*' => ['numeric']
    ];
  }

  public function getIds(): array
  {
    return $this->get('ids', []);
  }
}
