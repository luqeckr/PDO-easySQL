# PDO-CRUD-PHP-OOP
A simple PHP Class to use PDO Create, Read, Update and Delete functions. 
Modified from MySQLi-CRUD-PHP-OOP by the original Author Rory Standley <rorystandley@gmail.com>
Also, contain additional sql builder, to create a query with very simple and clean code.

this is how to use the builder

```php
<?php
$db = new Database();
$build = new Builder($db);

$build->init();
$build->selects('id, name, email');
$build->selects('address');
$build->where('id < 250');
$build->where('email LIKE "b%"', 'AND'); 
$build->join('detail', 'id=detail.id', 'LEFT'); 
$qr = $build->get('usertable');
/* this will run:
SELECT id, name, email, address FROM usertable
WHERE id < 250 AND email LIKE "b%"
LEFT JOIN detail ON id=detail.id
*/

echo $build->getSql();
echo '<pre>Dump result: ';
var_dump($build->getResult());
echo '</pre>';
?>
```
