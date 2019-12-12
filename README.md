# DataTables server-side processing for CodeIgniter.
## What is it?
A CodeIgniter library for building a Datatables server side processing SQL query.

**Links**

[CodeIgniter](https://codeigniter.com/)

[DataTables](https://datatables.net/)
## Requirements
- PHP Version 7.3.10 or greater
- CodeIgniter 3.1.11+
- Datatables 1.10.20+
## Installation
Download **Datatables_server_side.php** and add it to your *application/libraries* directory.
## Usage
### Initialize library
From within any of your Controller methods, initialize library using the CodeIgniter's standard way. You **MUST** pass data as an array via the second parameter and it will be passed to the library's constructor:
```
$this->load->library('datatables_server_side', array(
	'table' => 'customer',
	'primary_key' => 'customer_id',
	'columns' => array('first_name', 'last_name', 'email'),
	'where' => array()
));
```
## Sample