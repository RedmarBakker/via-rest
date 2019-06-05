A Laravel REST Provider. Plug-and-play module for creating REST API routes with models.

## Setting up a Model

`app/Models/Order.php`:
```php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use ViaRest\Models\DynamicModelInterface;
use ViaRest\Models\DynamicModelTrait;

class Order extends Model implements DynamicModelInterface
{
    use DynamicModelTrait;
    
    ...

}


```

## Setting up a Controller

`app/Http/Controllers/Api/OrderController.php`:
```php

namespace App\Http\Controllers\Api;

use ViaRest\Http\Controllers\Api\AbstractRestController;
use ViaRest\Models\DynamicModelInterface;
use App\Models\Order;

class OrdersController extends AbstractRestController
{


    /**
     * @return DynamicModelInterface
     */
    function getModel(): DynamicModelInterface
    {
        return new Order();
    }

}


```

## Configuring your routes

A basic model example. `routes/api.php:`:

```php

ViaRest::handle('v1', [
    /**
     * All api controllers
     *
     * url => model
     * */

    'orders'     => ViaRest::model(Order::class),
]);


```

A basic controller example. `routes/api.php`:

```php

ViaRest::handle('v1', [
    /**
     * All api controllers
     *
     * url => model
     * */

    'orders'     => ViaRest::controller(OrderController::class),
]);


```

### Configuring API routes with a relation

An example where there is an OneToMany relationship with orders. In this case a user can have multiple orders. Requesting this relation can be done with the following endpoint: /api/v1/users/1/orders. In this way orders can be fetched and an order can be created (GET, POST). `routes/api.php`:

```php

ViaRest::handle('v1', [
    /**
     * All api controllers
     *
     * url => model
     * */

    // GET: /api/v1/users/1/orders
    'users'     => ViaRest::model(User::class, [
        'orders' => Order::class
    ]),
    
    // GET: /api/v1/orders/1/products
    'orders'     => ViaRest::controller(OrderController::class, [
        'products' => Product::class
    ]),
]);


```

### Configuring API routes with a custom endpoint

An example where a custom endpoint is added where this second parameter is the endpoint and the last parameter is the action. `routes/api.php`:

```php

ViaRest::handle('v1', [
    /**
     * All api controllers
     *
     * url => model
     * */

    // /api/v1/orders/unhandled
    'orders'     => ViaRest::controller(OrderController::class, [], [
        ViaRest::endpoint(Request::METHOD_GET, 'unhandled', 'unhandled')
    ]),
]);


```

`app/Http/Controllers/Api/OrderController.php`:

```php

namespace App\Http\Controllers\Api;

use ViaRest\Http\Controllers\Api\AbstractRestController;
use ViaRest\Models\DynamicModelInterface;
use App\Model\Order;

class OrdersController extends AbstractRestController
{


    /**
     * @return DynamicModelInterface
     */
    public function getModel(): DynamicModelInterface
    {
        return new Order();
    }
    
    public function unhandled(UnhandledRequest $request)
    {
        ...
    }

}


```

## Validation your requests

In this example we use previous senario where we gonna create a "UnhandledRequest" referenced above. `app/Http/Requests/Api/Orders/UnhandledRequest.php`:

```php

<?php

namespace App\Http\Requests\Api\Orders;

use ViaRest\Http\Requests\Api\CrudRequestInterface;
use ViaRest\Http\Requests\Api\AbstractRequest;

class UnhandledRequest extends AbstractRequest implements CrudRequestInterface
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            
        ];
    }
}



```
