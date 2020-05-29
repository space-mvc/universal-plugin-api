# universal-plugin-api
This plugin can create an instant Laravel API service controller by controller

- 1) Add the following lines to your parent laravel composer.json file

```
"repositories": [
    {
        "type": "vcs",
         "url":  "git@bitbucket.org:space-mvc/universal-plugin-api.git"
    }
],
```


- 2) Run `composer require centralhubb/universal-plugin-api`
- 3) Create a new controller and extend the Universal\Plugin\Api class 
- 4) Override the $modelName with your selected model name
- 5) Create the standard routes.php or web.php entries as below.

```
$crudApiRoutes = [
    '/api/users' => 'Api\UsersController',
];

if (!empty($crudApiRoutes)) {
    foreach ($crudApiRoutes as $baseUrl => $controller) {
        Route::get($baseUrl, $controller . '@getEntities');
        Route::get($baseUrl . '/{id}', $controller . '@getEntity');
        Route::post($baseUrl, $controller . '@createEntity');
        Route::put($baseUrl . '/{id}', $controller . '@updateEntity');
        Route::delete($baseUrl . '/{id}', $controller . '@deleteEntity');
    }
}
```

- 6) Copy steps 2-4 each time to add a new controller/model/api endopoint

These are example query parameters available on every api call. For Get methods

`Example usage /api/users?fields=id,first_name,last_name`


Available Query Parameters

 * fields = &fields=field1,field2,field3
 * with   = &with=relation1,relation2,relation3
 * wheres = &wheres=[{"key":"field1","operator":"=","value":"123"},{"key":"field2","operator":"=","value":"123"}]
 * likes  = &likes=[{"key":"field1","operator":"=","value":"123"},{"key":"field2","operator":"=","value":"123"}]
 * orders = &orders={"field1":"asc","field2":"asc","field3":"asc","field4":"asc","field5":"asc"}
 * limit  = &limit=(numeric)
 * page   = &page=(numeric)
 * offset = &offset=(numeric)
 
 This plugin will work for get, post, put and delete methods exactly as written above in the routes.
