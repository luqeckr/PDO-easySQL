# PDO-easySQL
A simple PHP Class to use PDO Create, Read, Update and Delete functions. 
Modified from MySQLi-CRUD-PHP-OOP by the original Author Rory Standley <rorystandley@gmail.com>
Also, contain additional sql builder which become it main function, to create a query with very simple and clean code.

The first thing todo is to change database connection in the class file. 
You could easily find once you open in editor:
```php
	private $db_host = "127.0.0.1";  // Change as required
	private $db_user = "username";  // Change as required
	private $db_pass = "password";  // Change as required
	private $db_name = "database";  // Change as required
```
then include the file, and do the initial call:

```php
$db = new Database();
```

to use the builder function
```php
$build = new Builder($db);
```
you can use any variable name you like anyway.

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
## INSERT OR UPDATE
INSERT row to table, but if the row exist then just UPDATE the row.
We are doing `INSERT INTO .. ON DUPLICATE KEY UPDATE ..` query.
It's the same way with INSERT builder, 
the difference is the firt setdata() is the column key

example:
```php
<?php
	$build->setdata('id', 15); /* this is the key */
	$build->setdata('name', 'updated name');
	$build->setdata('age', 27);
	$build->insert_or_update('usertable');
?>
```
it will build query like this:
```sql
	INSERT INTO usertable(id, name, age) VALUES (15, 'updated name', 27) ON DUPLICATE KEY UPDATE name='updated name', age=27
```

## Transaction
```php
<?php
	$db = new Database();
	$build = new Builder($db);

	// begin the transaction
	$build->beginTrx();
	$build->setdata('name', 'another name');
	$build->setdata('email', 'anotheremail@domain.com');
	$build->insert_to('usertable');
	// add more data
	$build = new Builder($db);
	$build->setdata('name', 'another name 2');
	$build->setdata('email', 'anotheremail2@domain.com');
	$build->insert_to('usertable');
	// commit transaction
	$build->endTrx();
	$db->disconnect();
?>
```

To rollback transaction, just do very easy command (without commit transaction - endTrx() ):
```php
<?php
	$build->undoTrx();
?>
```
