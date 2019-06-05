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

In this case we modify our create because we want to do something specific on a create. `app/Http/Controllers/Api/OrderController.php`:
```php

namespace App\Http\Controllers\Api;

use ViaRest\Http\Controllers\Api\AbstractRestController;
use ViaRest\Models\DynamicModelInterface;
use App\Models\Order;

class OrderController extends AbstractRestController
{


    /**
     * @return DynamicModelInterface
     */
    function getModel(): DynamicModelInterface
    {
        return new Order();
    }
    
    /**
     * @param $input array
     * @return JsonResponse
     * */
    public function doCreate(array $input): JsonResponse
    {
    
        ...
    
        return parent::doCreate($input);
    }
    
    ...

}


```

## Configuring your routes

A basic model example. `routes/api.php:`:

```php

ViaRest::handle('v1', [
    /**
     * All REST API routes
     *
     * uri => provider
     * */

    'orders' => ViaRest::model(Order::class),
]);


```

A basic controller example. `routes/api.php`:

```php

ViaRest::handle('v1', [
    /**
     * All REST API routes
     *
     * uri => provider
     * */

    'orders' => ViaRest::controller(OrderController::class),
]);


```

### Configuring API routes with a relation

An example where there is an OneToMany relationship with orders. In this case an user can have multiple orders. Requesting this relation can be done with the following endpoint: /api/v1/users/1/orders. In this way orders can be fetched and an order can be created (GET, POST). `routes/api.php`:

```php

ViaRest::handle('v1', [
    /**
     * All REST API routes
     *
     * uri => provider
     * */

    // GET: /api/v1/users/1/orders
    'users' => ViaRest::model(User::class, [
        'orders' => Order::class
    ]),
    
    // GET: /api/v1/orders/1/products
    'orders' => ViaRest::controller(OrderController::class, [
        'products' => Product::class
    ]),
]);


```

### Configuring API routes with a custom endpoint

An example where a custom endpoint is added where the second parameter is the endpoint and the last parameter is the action. `routes/api.php`:

```php

ViaRest::handle('v1', [
    /**
     * All REST API routes
     *
     * uri => provider
     * */

    // GET: /api/v1/orders/unhandled
    'orders' => ViaRest::controller(OrderController::class, [], [
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

class OrderController extends AbstractRestController
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

Requests will be pickedup automatically. The abstract layer who will simulate the rest structure, will look for the Request with the following namespace: `{ModelPackageName}\Http\Requests\Api\{ModelName}\CreateRequest`, where in this case a create example were given. The following names will be used: `FetchRequest`, `FetchAllRequest`, `CreateRequest`, `UpdateRequest` & `DestroyRequest`.

For example we will create a Request for our Order endpoint we have created. `app/Http/Requests/Api/Orders/CreateRequest.php`:
```php

namespace App\Http\Requests\Api\Orders;

use ViaRest\Http\Requests\Api\CreateRequest as AbstractCreateRequest;
use ViaRest\Http\Requests\Api\CrudRequestInterface;

class CreateRequest extends AbstractCreateRequest implements CrudRequestInterface
{

    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id'    => 'required',
        ];
    }
}


```

For custom routes, we can create Requests like this. In this example we use the previous senario where we gonna create a "UnhandledRequest". `app/Http/Requests/Api/Orders/UnhandledRequest.php`:

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

## Response Examples

`GET: /api/v1/users/1`:

```json

{
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john.doe@domain.com",
        "email_verified_at": null,
        "created_at": "2019-05-29 17:27:44",
        "updated_at": "2019-05-29 17:27:44"
    }
}


```

`GET: /api/v1/users`:

```json

{
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john.doe@domain.com",
            "email_verified_at": null,
            "created_at": "2019-05-29 17:27:44",
            "updated_at": "2019-05-29 17:27:44"
        },
        {
            "id": 2,
            "name": "John Doe",
            "email": "john.doe@domain.com",
            "email_verified_at": null,
            "created_at": "2019-05-29 17:27:44",
            "updated_at": "2019-05-29 17:27:44"
        },
        {
            "id": 3,
            "name": "John Doe",
            "email": "john.doe@domain.com",
            "email_verified_at": null,
            "created_at": "2019-05-29 17:27:44",
            "updated_at": "2019-05-29 17:27:44"
        }
    ]
}


```

`GET: /api/v1/users/1/orders`:

```json

{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "products": [ ... ],
            "created_at": "2019-06-04 23:20:56",
            "updated_at": "2019-06-04 23:20:56"
        },
        {
            "id": 2,
            "user_id": 1,
            "products": [ ... ],
            "created_at": "2019-06-04 23:43:08",
            "updated_at": "2019-06-04 23:43:08"
        }
    ]
}


```
