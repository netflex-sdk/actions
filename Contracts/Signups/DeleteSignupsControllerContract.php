<?php

namespace Netflex\Actions\Contracts\Signups;

use Netflex\Actions\Requests\Signups\DeleteSignupsRequest;

interface DeleteSignupsControllerContract
{
  public function deleteSignups(DeleteSignupsRequest $request): void;
}
