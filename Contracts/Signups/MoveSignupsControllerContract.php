<?php

namespace Netflex\Actions\Contracts\Signups;

use Illuminate\Http\Request;
use Netflex\Actions\Requests\Signups\MoveSignupRequest;

interface MoveSignupsControllerContract
{

  /**
   *
   * Return an array with BookingFormFields
   *
   * @param Request $request
   * @param string $id
   * @return array[BookingFormField]
   */
  public function moveSignupForm(Request $request, string $id): array;

  /**
   * @param MoveSignupRequest $request
   * @param string $id
   * @return mixed
   */
  public function moveSignupAction(MoveSignupRequest $request, string $id);

}
