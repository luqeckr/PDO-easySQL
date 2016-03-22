# PDO-easySQL
A simple PHP Class to use PDO Create, Read, Update and Delete functions. 
Modified from MySQLi-CRUD-PHP-OOP by the original Author Rory Standley <rorystandley@gmail.com>
Also, contain additional sql builder which become it main function, to create a query with very simple and clean code.

this is how to use the builder
## SELECT builder
```php
<?php
$db = new Database();
$build = new Builder($db);

$build->selects('id, name, email');
$build->selects('address');
$build->where('id <',250);
$build->where('email LIKE', 'b%', 'AND'); 
$build->join('detail', 'id=detail.id', 'LEFT'); 
$qr = $build->get('usertable');
/* this will run:
SELECT id, name, email, address FROM usertable
WHERE id < 250 AND email LIKE "b%"
LEFT JOIN detail ON id=detail.id
*/

/* for debugging */
echo $build->getSql();

/* result in array */
echo '<pre>Dump result: ';
var_dump($build->getResult());
echo '</pre>';

/* close connection */
$db->disconnect();
?>
```
## UPDATE Builder
To update the table
```php
<?php
$db = new Database();
$build = new Builder($db);
$build->setdata('name', 'another name');
$build->setdata('email', 'anotheremail@domain.com');
$build->where('id >', 280); /* if not supplied with '>', it will assume '=' */
$build->update_to('usertable');
$db->disconnect();
?>
```
this will make the query: 
```sql
UPDATE usertable SET name='another name', email='anotheremail@domain.com' WHERE id > 280
```

## INSERT Builder
```php
<?php
$db = new Database();
$build = new Builder($db);
$build->setdata('name', 'another name');
$build->setdata('email', 'anotheremail@domain.com');
$build->insert_to('usertable');
$db->disconnect();
?>
```
this will make the query: 
```sql
INSERT INTO usertable(name, email) VALUES ('another name', 'anotheremail@domain.com')
```
