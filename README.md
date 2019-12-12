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
	'table' => 'customer', //name of the table to fetch data from
	'primary_key' => 'customer_id', //primary key field name
	'columns' => array('first_name', 'last_name', 'email'), //zero-based array of field names. 
	'where' => array() //associative array or custom where string
));
```
### Access the library
Once the library is loaded, you need to call the following method:
```
$this->datatables_server_side->process();
``` 
The method accepts two parameters which are both optional. These parameters are used to add / modify *tr* tag.

**row_id (String)**

*Possible values: 'id', 'data', 'none'*

*Examples*
```
$this->datatables_server_side->process('id');
//<tr id="10" role="row" class="odd">
``` 
```
$this->datatables_server_side->process('data'); //Default
//<tr data-id="10" role="row" class="odd">
``` 
```
$this->datatables_server_side->process('none');
//<tr role="row" class="odd">
``` 
***row_class (String)***

*Possible values: '', 'class_name'*

*Examples* 
```
$this->datatables_server_side->process('none', ''); //Default
//<tr role="row" class="odd">
``` 
```
$this->datatables_server_side->process('none', 'class_name');
//<tr role="row" class="class_name odd">
``` 
## Sample
The following sample uses sakila dump downloaded from (https://dev.mysql.com/doc/index-other.html). You can also clone this repository and run it on your local machine to see an end to end working example.
### Controller
```
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function index()
	{
		$this->load->helper('url');
		$this->load->view('home');
	}

	public function load_data()
	{
		$this->load->library('datatables_server_side', array(
			'table' => 'customer',
			'primary_key' => 'customer_id',
			'columns' => array('first_name', 'last_name', 'email'),
			'where' => array()
		));

		$this->datatables_server_side->process();
	}
}
```
### View
```
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html>
<head>
	<title>Server-side processing</title>

	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.css">
</head>
<body>
	<table id="users">
		<thead>
			<tr>
				<th>First name</th>
				<th>Last name</th>
				<th>Email</th>
			</tr>
		</thead>
	</table>

	<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
	<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#users').dataTable({
				serverSide: true,
				ajax: '<?php echo base_url('welcome/load_data'); ?>'
			});
		});
	</script>
</body>
</html>
```
## Known issues
* Only zero-based column numbering is currently supported: 0 -> first column, 1 -> second column, etc.
* No data processors / formatters. All data processing / formatting must be done at front end.