<?php

namespace Netflex\Actions\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;
use Netflex\Actions\Contracts\Reservations\ActionControllerContract;
use Netflex\Actions\Contracts\Signups\DeleteSignupsControllerContract;
use Netflex\Actions\Contracts\Signups\MoveSignupsControllerContract;
use Netflex\Actions\Controllers\Orders\Refunds\FormController as OrdersRefundsFormController;
use Netflex\Actions\Controllers\Reservations\FormController;
use Netflex\Actions\Exceptions\InvalidActionClassException;
use Netflex\Actions\Exceptions\InvalidFormDescriberException;
use Netflex\Actions\Middlewares\WebhookAuthMiddleware;

abstract class NetflexappConnectionProvider extends RouteServiceProvider
{


  abstract function registerProviders();

  private ?string $reservationsActionController = null;
  private ?string $ordersRefundFormController = null;
  private ?string $ordersRefundActionController = null;
  private ?string $moveSignupsController = null;
  private ?string $deleteSignupsController = null;

  public function register()
  {
    $this->registerProviders();
    parent::register();
  }

  /**
   * Registers which controller is to be used for the reservations functionality
   *
   * @param string $actionController Must be a class string to a class that implements the [ActionControllerContract] for the Reservations module
   * @throws \ReflectionException
   * @throws InvalidActionClassException
   */
  protected function setReservationsActionController(string $actionController)
  {

    $acc = ActionControllerContract::class;
    if ((new \ReflectionClass($actionController))->implementsInterface($acc)) {
      $this->reservationsActionController = $actionController;
    } else {
      throw new InvalidActionClassException($actionController, $acc);
    }
  }

  protected function setMoveSignupsActionController(string $actionController)
  {
    $acc = MoveSignupsControllerContract::class;
    if ((new \ReflectionClass($actionController))->implementsInterface($acc)) {
      $this->moveSignupsController = $actionController;
    } else {
      throw new InvalidActionClassException($actionController, $acc);
    }
  }

  protected function setDeleteSignupsActionController(string $actionController)
  {
    $acc = DeleteSignupsControllerContract::class;
    if ((new \ReflectionClass($actionController))->implementsInterface($acc)) {
      $this->deleteSignupsController = $actionController;
    } else {
      throw new InvalidActionClassException($actionController, $acc);
    }
  }


  /**
   *
   * Registers a handler for the Orders/Refunds action
   *
   * You must supply a form controller that is used to resolve the correct order and cart item
   * As well as an action controller that must implement the action controller interface
   *
   * @param string $formDescriber Must be a class that is a subclass of the [Netflex\Actions\Controllers\Orders\Refunds\FormController] class.
   * @param string $actionController Must be a class that implements the [Netflex\Actions\Contracts\Orders\Refunds\ActionControllerContract::class]
   * @throws InvalidFormDescriberException
   * @throws \ReflectionException
   * @throws InvalidActionClassException
   */
  protected function setOrdersRefundsActionControllers(string $formDescriber, string $actionController)
  {
    if (is_subclass_of($formDescriber, OrdersRefundsFormController::class) == false) {
      $acc = OrdersRefundsFormController::class;
      throw new InvalidFormDescriberException("Class [$formDescriber] should extend [$acc], but it does not");
    }

    if ((new \ReflectionClass($actionController))->implementsInterface(\Netflex\Actions\Contracts\Orders\Refunds\ActionControllerContract::class) == false) {
      $acc = \Netflex\Actions\Contracts\Orders\Refunds\ActionControllerContract::class;
      throw new InvalidActionClassException($actionController, $acc);
    }

    $this->ordersRefundFormController = $formDescriber;
    $this->ordersRefundActionController = $actionController;
  }

  public function map()
  {


    Route::group([
      'middleware' => ['api', WebhookAuthMiddleware::class],
      'prefix' => '.well-known/netflex/actions/'
    ], function () {

      if ($this->reservationsActionController) {
        $actionController = $this->reservationsActionController;
        Route::any('reservations/form', [FormController::class, 'createForm'])->name('netflexapp.actions.reservations.form');
        Route::post("reservations/create", [$actionController, 'create'])->name("netflexapp.actions.reservations.create");
        Route::post("reservations/update", [$actionController, 'update'])->name("netflexapp.actions.reservations.update");
        Route::post("reservations/destroy", [$actionController, 'destroy'])->name("netflexapp.actions.reservations.destroy");
      }

      if ($this->ordersRefundActionController) {
        $formDescriber = $this->ordersRefundFormController;
        $actionController = $this->ordersRefundActionController;
        Route::any('orders/refunds/form/order', [$formDescriber, 'renderRefundOrderForm']);
        Route::any('orders/refunds/form/cartitem', [$formDescriber, 'renderRefundCartItemForm']);
        Route::post('orders/refunds/refund/order', [$actionController, 'refundOrder']);
        Route::post('orders/refunds/refund/cartitem', [$actionController, 'refundCartItem']);
      }

      if ($this->moveSignupsController) {
        Route::get('signups/move/{id}', [$this->moveSignupsController, 'moveSignupForm']);
        Route::post('signups/move/{id}', [$this->moveSignupsController, 'moveSignupAction']);
      }

      if ($this->deleteSignupsController) {
        Route::post('signups/delete', [$this->deleteSignupsController, 'deleteSignups'])
          ->name('netflexapp.actions.signups.delete');
      }
    });


  }
}
