<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

	<!-- jQuery library -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

	<!-- Latest compiled JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>

	<div class="container">
	<table class="table table-striped">
		<thead>
			<tr>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Student ID</th>
				<th>Email</th>
				<th>Grade</th>
				<th>Designated Responder ID</th>
			</tr>
		</thead>
		<tbody>
			@foreach($user as $u)
			<tr>
				<td>{{$u->id}}</td>
				<td>{{$u->id}}</td>
				<td>{{$u->id}}</td>
				<td>{{$u->id}}</td>
				<td>{{$u->id}}</td>
				<td>{{$u->id}}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	</div>
	
</body>
</html>